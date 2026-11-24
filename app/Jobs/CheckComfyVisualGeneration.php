<?php

namespace App\Jobs;

use App\Exceptions\ComfyUIGenerationException;
use App\Models\UserAgentPublicationVisual;
use App\Services\ComfyUIClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckComfyVisualGeneration implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public int $backoff = 15;

    public function __construct(public int $visualId)
    {
    }

    public function handle(ComfyUIClient $comfyui): void
    {
        $visual = UserAgentPublicationVisual::find($this->visualId);

        if (! $visual || in_array($visual->status, ['generated', 'failed', 'downloading'], true)) {
            return;
        }

        Log::info('Checking ComfyUI queue and generation history.', [
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
        ]);

        try {
            $history = $comfyui->history($visual->prompt_id);
        } catch (ConnectionException $exception) {
            Log::warning('ComfyUI history is temporarily unreachable; the check will be retried.', [
                'visual_id' => $visual->id,
                'prompt_id' => $visual->prompt_id,
                'error' => $exception->getMessage(),
            ]);
            $this->scheduleNextCheck($visual, 'pending');

            return;
        } catch (ComfyUIGenerationException $exception) {
            $this->failVisual($visual, $exception->getMessage());

            return;
        }

        if (isset($history['_transient_http_status'])) {
            Log::warning('ComfyUI history returned a transient server error.', [
                'visual_id' => $visual->id,
                'prompt_id' => $visual->prompt_id,
                'http_status' => $history['_transient_http_status'],
            ]);
            $this->scheduleNextCheck($visual, 'pending');

            return;
        }

        $status = $history['status']['status_str'] ?? null;
        $image = $this->firstImage($history['outputs'] ?? []);

        if ($image !== null) {
            $visual->update([
                'status' => 'downloading',
                'comfy_filename' => $image['filename'],
                'comfy_subfolder' => $image['subfolder'] ?? '',
                'comfy_type' => $image['type'] ?? 'output',
                'error' => null,
            ]);

            Log::info('ComfyUI SaveImage output detected; dispatching image retrieval.', [
                'visual_id' => $visual->id,
                'prompt_id' => $visual->prompt_id,
                'filename' => $image['filename'],
                'subfolder' => $image['subfolder'] ?? '',
                'type' => $image['type'] ?? 'output',
            ]);

            StoreComfyVisualImage::dispatch($visual->id);

            return;
        }

        if ($status === 'error') {
            $this->failVisual($visual, $comfyui->generationError($history));

            return;
        }

        if ($status === 'success') {
            $this->failVisual($visual, 'ComfyUI a terminé le workflow sans produire de fichier SaveImage.');

            return;
        }

        $timeout = max(1, (int) config('services.comfyui.timeout', 14400));
        $generationStartedAt = $visual->started_at ?? $visual->created_at;
        if ($generationStartedAt && $generationStartedAt->copy()->addSeconds($timeout)->isPast()) {
            $this->failVisual($visual, 'ComfyUI a dépassé le délai maximal de génération de ' . $timeout . ' secondes.');

            return;
        }

        $nextStatus = in_array($status, ['running', 'executing'], true) ? 'running' : 'pending';
        $this->scheduleNextCheck($visual, $nextStatus);
    }

    public function failed(Throwable $exception): void
    {
        $visual = UserAgentPublicationVisual::find($this->visualId);
        if (! $visual || in_array($visual->status, ['generated', 'failed'], true)) {
            return;
        }

        $this->failVisual($visual, 'Échec du suivi ComfyUI : ' . $exception->getMessage());
    }

    private function firstImage(array $outputs): ?array
    {
        foreach ($outputs as $output) {
            foreach ($output['images'] ?? [] as $image) {
                if (is_array($image) && ! empty($image['filename'])) {
                    return $image;
                }
            }
        }

        return null;
    }

    private function scheduleNextCheck(UserAgentPublicationVisual $visual, string $status): void
    {
        $visual->update(['status' => $status]);

        Log::info('ComfyUI generation is not complete; scheduling another history check.', [
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
            'status' => $status,
            'delay_seconds' => 30,
        ]);

        self::dispatch($visual->id)->delay(now()->addSeconds(30));
    }

    private function failVisual(UserAgentPublicationVisual $visual, string $error): void
    {
        $visual->update([
            'status' => 'failed',
            'error' => $error,
            'completed_at' => now(),
        ]);

        Log::error('ComfyUI visual generation failed.', [
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
            'error' => $error,
        ]);
    }
}
