<?php

namespace App\Services;

use App\Exceptions\ComfyUIGenerationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ComfyUIClient
{
    public function isAvailable(): bool
    {
        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            Log::warning('ComfyUI health check failed: URL is not configured.');

            return false;
        }

        try {
            $response = Http::timeout(5)->get($baseUrl . '/system_stats');
        } catch (ConnectionException $e) {
            Log::warning('ComfyUI health check could not connect.', [
                'url' => $baseUrl,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('ComfyUI health check returned an unsuccessful response.', [
                'url' => $baseUrl,
                'http_status' => $response->status(),
            ]);

            return false;
        }

        return true;
    }

    public function submit(string $prompt, string $negativePrompt, array $overrides = []): array
    {
        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ComfyUI est indisponible : URL non configurée.');
        }

        if (!$this->isAvailable()) {
            throw new RuntimeException('ComfyUI est indisponible : serveur non accessible.');
        }

        $workflow = $this->workflow($prompt, $negativePrompt, $overrides);
        $clientId = (string) Str::uuid();
        $promptId = $this->queuePrompt($baseUrl, $workflow, $clientId);

        return [
            'prompt_id' => $promptId,
            'client_id' => $clientId,
            'model' => $overrides['checkpoint'] ?? config('services.comfyui.checkpoint'),
        ];
    }

    public function fetchImage(string $promptId, ?string $agentId = null): ?array
    {
        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ComfyUI est indisponible : URL non configurée.');
        }

        $history = $this->history($promptId);

        if ($history === [] || isset($history['_transient_http_status'])) {
            return null;
        }

        $status = $history['status']['status_str'] ?? null;
        Log::debug('ComfyUI history response received.', [
            'prompt_id' => $promptId,
            'status' => $status,
            'output_nodes' => array_keys($history['outputs'] ?? []),
        ]);

        if ($status === 'error') {
            $message = $this->generationError($history);
            Log::error('ComfyUI generation failed.', [
                'prompt_id' => $promptId,
                'error' => $message,
            ]);

            throw new ComfyUIGenerationException($message);
        }

        foreach ($history['outputs'] ?? [] as $output) {
            foreach ($output['images'] ?? [] as $image) {
                if (empty($image['filename'])) {
                    continue;
                }

                $contents = $this->downloadImage($baseUrl, $image, $promptId);
                $path = $agentId !== null
                    ? $this->storeImage($agentId, $image['filename'], $contents)
                    : null;

                return [
                    'path' => $path,
                    'url' => $path ? Storage::disk('public')->url($path) : null,
                    'filename' => $image['filename'],
                    'contents' => $contents,
                ];
            }
        }

        if ($status === 'success') {
            $message = 'ComfyUI a terminé le workflow sans produire d’image SaveImage.';
            Log::error($message, ['prompt_id' => $promptId]);

            throw new ComfyUIGenerationException($message);
        }

        return null;
    }

    private function queuePrompt(string $baseUrl, array $workflow, string $clientId): string
    {
        Log::info('Submitting workflow to ComfyUI.', [
            'url' => $baseUrl . '/prompt',
            'client_id' => $clientId,
            'checkpoint' => $workflow['4']['inputs']['ckpt_name'] ?? null,
            'nodes' => array_map(fn (array $node) => $node['class_type'] ?? 'unknown', $workflow),
        ]);

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($baseUrl . '/prompt', [
                    'prompt' => $workflow,
                    'client_id' => $clientId,
                ]);
        } catch (ConnectionException $e) {
            Log::error('ComfyUI prompt submission could not connect.', [
                'url' => $baseUrl . '/prompt',
                'client_id' => $clientId,
                'exception' => $e->getMessage(),
            ]);

            throw new RuntimeException('ComfyUI est indisponible : impossible de se connecter au serveur.', 0, $e);
        }

        $promptId = $response->json('prompt_id');
        $nodeErrors = $response->json('node_errors');
        Log::info('ComfyUI prompt submission response received.', [
            'http_status' => $response->status(),
            'prompt_id' => $promptId,
            'node_errors' => $nodeErrors,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('ComfyUI a refusé la requête : ' . $this->responseError($response->json() ?? [], $response->body()));
        }

        if (! is_string($promptId) || $promptId === '') {
            throw new RuntimeException('ComfyUI n’a pas retourné de prompt_id : ' . $this->responseError($response->json() ?? [], $response->body()));
        }

        Log::info('ComfyUI generation queued.', ['prompt_id' => $promptId]);

        return $promptId;
    }

    private function responseError(array $body, string $fallback): string
    {
        $details = array_filter([
            'error' => $body['error'] ?? null,
            'node_errors' => $body['node_errors'] ?? null,
        ]);

        return $details
            ? (string) json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $fallback;
    }

    public function history(string $promptId): array
    {
        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ComfyUI est indisponible : URL non configurée.');
        }

        Log::debug('Checking ComfyUI generation history.', ['prompt_id' => $promptId]);

        try {
            $response = Http::timeout(15)->acceptJson()->get($baseUrl . '/history/' . $promptId);
        } catch (ConnectionException $e) {
            Log::warning('ComfyUI history request failed to connect.', [
                'prompt_id' => $promptId,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        if ($response->serverError()) {
            return ['_transient_http_status' => $response->status()];
        }

        if ($response->failed()) {
            $message = 'ComfyUI history request failed (HTTP ' . $response->status() . '): '
                . $this->responseError($response->json() ?? [], $response->body());
            Log::error($message, ['prompt_id' => $promptId]);

            throw new ComfyUIGenerationException($message);
        }

        $history = $response->json($promptId);

        if (! is_array($history)) {
            Log::debug('ComfyUI has no history entry yet.', ['prompt_id' => $promptId]);

            return [];
        }

        Log::debug('ComfyUI history response received.', [
            'prompt_id' => $promptId,
            'status' => $history['status']['status_str'] ?? null,
            'output_nodes' => array_keys($history['outputs'] ?? []),
        ]);

        return $history;
    }

    public function generationError(array $history): string
    {
        $messages = [];

        foreach ($history['status']['messages'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $details = $item[1] ?? [];
            if (is_array($details)) {
                $message = $details['exception_message'] ?? $details['message'] ?? null;
                if (is_string($message) && $message !== '') {
                    $messages[] = $message;
                }
            }
        }

        if ($messages === [] && ! empty($history['status']['node_errors'])) {
            $messages[] = (string) json_encode($history['status']['node_errors'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $messages !== []
            ? 'ComfyUI a échoué : ' . implode(' ; ', array_unique($messages))
            : 'ComfyUI a signalé une erreur pendant la génération.';
    }

    private function downloadImage(string $baseUrl, array $image, string $promptId): string
    {
        $parameters = [
            'filename' => $image['filename'],
            'subfolder' => $image['subfolder'] ?? '',
            'type' => $image['type'] ?? 'output',
        ];

        Log::info('Downloading ComfyUI SaveImage output.', [
            'prompt_id' => $promptId,
            'filename' => $parameters['filename'],
            'subfolder' => $parameters['subfolder'],
            'type' => $parameters['type'],
        ]);

        $response = Http::timeout(60)->get($baseUrl . '/view?' . http_build_query($parameters));

        if ($response->failed()) {
            throw new RuntimeException('Impossible de télécharger le visuel généré par ComfyUI (HTTP ' . $response->status() . ').');
        }

        $contents = $response->body();

        if (! str_starts_with($contents, "\x89PNG\r\n\x1a\n")) {
            throw new RuntimeException('ComfyUI n’a pas retourné une image PNG valide.');
        }

        Log::info('ComfyUI image downloaded.', [
            'prompt_id' => $promptId,
            'filename' => $parameters['filename'],
            'bytes' => strlen($contents),
        ]);

        return $contents;
    }

    public function downloadAndStoreImage(
        string $filename,
        string $subfolder,
        string $type,
        string $agentId,
        string $promptId
    ): array {
        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ComfyUI est indisponible : URL non configurée.');
        }

        if ($filename === '') {
            throw new RuntimeException('ComfyUI n’a fourni aucun nom de fichier image.');
        }

        $contents = $this->downloadImage($baseUrl, [
            'filename' => $filename,
            'subfolder' => $subfolder,
            'type' => $type,
        ], $promptId);
        $path = $this->storeImage($agentId, $filename, $contents);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'bytes' => strlen($contents),
        ];
    }

    private function storeImage(string $agentId, string $filename, string $contents): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)) ?: 'png';

        if (! in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $extension = 'png';
        }

        $path = 'user-agent-publication-visuals/' . $agentId . '/' . Str::uuid() . '.' . $extension;
        $disk = Storage::disk('public');
        $written = $disk->put($path, $contents);

        if (! $written || ! $disk->exists($path) || $disk->size($path) <= 0) {
            $disk->delete($path);

            throw new RuntimeException('Le visuel ComfyUI n’a pas pu être enregistré dans le stockage public Laravel.');
        }

        Log::info('ComfyUI image stored in Laravel public storage.', [
            'path' => $path,
            'bytes' => $disk->size($path),
        ]);

        return $path;
    }

    private function workflow(string $prompt, string $negativePrompt, array $overrides): array
    {
        $checkpoint = (string) ($overrides['checkpoint'] ?? config('services.comfyui.checkpoint'));
        $steps = (int) ($overrides['steps'] ?? config('services.comfyui.steps', 25));
        $cfg = (float) ($overrides['cfg'] ?? config('services.comfyui.cfg', 7));
        $width = (int) ($overrides['width'] ?? config('services.comfyui.width', 1024));
        $height = (int) ($overrides['height'] ?? config('services.comfyui.height', 1024));
        $sampler = (string) ($overrides['sampler'] ?? config('services.comfyui.sampler', 'euler'));
        $scheduler = (string) ($overrides['scheduler'] ?? config('services.comfyui.scheduler', 'normal'));

        return [
            '3' => [
                'class_type' => 'KSampler',
                'inputs' => [
                    'seed' => random_int(1, 2147483647),
                    'steps' => $steps,
                    'cfg' => $cfg,
                    'sampler_name' => $sampler,
                    'scheduler' => $scheduler,
                    'denoise' => 1,
                    'model' => ['4', 0],
                    'positive' => ['6', 0],
                    'negative' => ['7', 0],
                    'latent_image' => ['5', 0],
                ],
            ],
            '4' => [
                'class_type' => 'CheckpointLoaderSimple',
                'inputs' => [
                    'ckpt_name' => $checkpoint,
                ],
            ],
            '5' => [
                'class_type' => 'EmptyLatentImage',
                'inputs' => [
                    'width' => $width,
                    'height' => $height,
                    'batch_size' => 1,
                ],
            ],
            '6' => [
                'class_type' => 'CLIPTextEncode',
                'inputs' => [
                    'text' => $prompt,
                    'clip' => ['4', 1],
                ],
            ],
            '7' => [
                'class_type' => 'CLIPTextEncode',
                'inputs' => [
                    'text' => $negativePrompt,
                    'clip' => ['4', 1],
                ],
            ],
            '8' => [
                'class_type' => 'VAEDecode',
                'inputs' => [
                    'samples' => ['3', 0],
                    'vae' => ['4', 2],
                ],
            ],
            '9' => [
                'class_type' => 'SaveImage',
                'inputs' => [
                    'images' => ['8', 0],
                    'filename_prefix' => 'IA_Agent_Studio',
                ],
            ],
        ];
    }

}