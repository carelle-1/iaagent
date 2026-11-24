<?php

namespace App\Jobs;

use App\Models\UserAgentPublicationVisual;
use App\Services\ComfyUIClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreComfyVisualImage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    public array $backoff = [15, 30, 60];

    public function __construct(public int $visualId)
    {
    }

    public function handle(ComfyUIClient $comfyui): void
    {
        $visual = UserAgentPublicationVisual::find($this->visualId);

        if (! $visual || $visual->status === 'generated') {
            return;
        }

        if (! $visual->comfy_filename) {
            throw new \RuntimeException('Le nom du fichier SaveImage manque pour le visuel ' . $visual->id . '.');
        }

        $result = $comfyui->downloadAndStoreImage(
            $visual->comfy_filename,
            (string) $visual->comfy_subfolder,
            (string) ($visual->comfy_type ?: 'output'),
            (string) $visual->entry->user_agent_id,
            (string) $visual->prompt_id
        );

        $visual->update([
            'status' => 'generated',
            'image_path' => $result['path'],
            'image_name' => $result['filename'],
            'error' => null,
            'completed_at' => now(),
        ]);

        Log::info('ComfyUI image saved and linked to its publication.', [
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
            'path' => $result['path'],
            'bytes' => $result['bytes'],
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $visual = UserAgentPublicationVisual::find($this->visualId);
        if (! $visual || $visual->status === 'generated') {
            return;
        }

        $visual->update([
            'status' => 'failed',
            'error' => 'Impossible de récupérer ou d’enregistrer le PNG : ' . $exception->getMessage(),
            'completed_at' => now(),
        ]);

        Log::error('ComfyUI image retrieval failed after queue retries.', [
            'visual_id' => $visual->id,
            'prompt_id' => $visual->prompt_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
