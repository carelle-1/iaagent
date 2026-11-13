<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ComfyUIClient
{
    public function generate(string $prompt, string $negativePrompt, string $agentId, array $overrides = []): array
    {
        @set_time_limit(0);

        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ComfyUI est indisponible : URL non configurée.');
        }

        $workflow = $this->workflow($prompt, $negativePrompt, $overrides);
        $clientId = (string) Str::uuid();
        $timeout = (int) config('services.comfyui.timeout', 300);

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($baseUrl . '/prompt', [
                'prompt' => $workflow,
                'client_id' => $clientId,
            ]);

        if ($response->failed()) {
            $error = $response->json('error');

            if (is_array($error)) {
                $error = $error['message'] ?? json_encode($error);
            }

            throw new RuntimeException('ComfyUI a refusé la requête : ' . ($error ?: $response->body()));
        }

        $promptId = $response->json('prompt_id');

        if (!$promptId) {
            throw new RuntimeException('ComfyUI n\'a pas retourné de prompt_id.');
        }

        $image = $this->waitForImage($baseUrl, $promptId, $timeout);

        $extension = strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION)) ?: 'png';

        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $extension = 'png';
        }

        $path = 'user-agent-publication-visuals/' . $agentId . '/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $image['contents']);

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $image['filename'],
            'prompt_id' => $promptId,
            'model' => $overrides['checkpoint'] ?? config('services.comfyui.checkpoint'),
        ];
    }

    public function submit(string $prompt, string $negativePrompt, array $overrides = []): array
    {
        @set_time_limit(0);

        $baseUrl = rtrim((string) config('services.comfyui.url'), '/');

        if ($baseUrl === '') {
            throw new RuntimeException('ComfyUI est indisponible : URL non configurée.');
        }

        $workflow = $this->workflow($prompt, $negativePrompt, $overrides);
        $clientId = (string) Str::uuid();
        $timeout = (int) config('services.comfyui.timeout', 300);

        $response = Http::timeout($timeout)
            ->acceptJson()
            ->post($baseUrl . '/prompt', [
                'prompt' => $workflow,
                'client_id' => $clientId,
            ]);

        if ($response->failed()) {
            $error = $response->json('error');

            if (is_array($error)) {
                $error = $error['message'] ?? json_encode($error);
            }

            throw new RuntimeException('ComfyUI a refusé la requête : ' . ($error ?: $response->body()));
        }

        $promptId = $response->json('prompt_id');

        if (!$promptId) {
            throw new RuntimeException('ComfyUI n\'a pas retourné de prompt_id.');
        }

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

        $response = Http::timeout(30)->acceptJson()->get($baseUrl . '/history/' . $promptId);

        if ($response->failed()) {
            return null;
        }

        $history = $response->json($promptId) ?? [];

        if (($history['status']['status_str'] ?? null) === 'error') {
            throw new RuntimeException('ComfyUI a signalé une erreur pendant la génération.');
        }

        foreach ($history['outputs'] ?? [] as $output) {
            $image = $output['images'][0] ?? null;

            if (!$image || empty($image['filename'])) {
                continue;
            }

            $query = http_build_query([
                'filename' => $image['filename'],
                'subfolder' => $image['subfolder'] ?? '',
                'type' => $image['type'] ?? 'output',
            ]);

            $download = Http::timeout(60)->get($baseUrl . '/view?' . $query);

            if ($download->failed()) {
                throw new RuntimeException('Impossible de télécharger le visuel généré par ComfyUI.');
            }

            $extension = strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION)) ?: 'png';

            if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $extension = 'png';
            }

            $path = null;

            if ($agentId !== null) {
                $path = 'user-agent-publication-visuals/' . $agentId . '/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($path, $download->body());
            }

            return [
                'path' => $path,
                'url' => $path ? Storage::disk('public')->url($path) : null,
                'filename' => $image['filename'],
                'contents' => $download->body(),
            ];
        }

        return null;
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

    private function waitForImage(string $baseUrl, string $promptId, int $timeout): array
    {
        $deadline = microtime(true) + $timeout;

        while (microtime(true) < $deadline) {
            $response = Http::timeout(30)->acceptJson()->get($baseUrl . '/history/' . $promptId);

            if ($response->failed()) {
                sleep(2);
                continue;
            }

            $history = $response->json($promptId) ?? [];
            $status = $history['status']['status_str'] ?? null;

            if ($status === 'error') {
                throw new RuntimeException('ComfyUI a signalé une erreur pendant la génération.');
            }

            foreach ($history['outputs'] ?? [] as $output) {
                $image = $output['images'][0] ?? null;

                if (!$image || empty($image['filename'])) {
                    continue;
                }

                $query = http_build_query([
                    'filename' => $image['filename'],
                    'subfolder' => $image['subfolder'] ?? '',
                    'type' => $image['type'] ?? 'output',
                ]);

                $download = Http::timeout(60)->get($baseUrl . '/view?' . $query);

                if ($download->failed()) {
                    throw new RuntimeException('Impossible de télécharger le visuel généré par ComfyUI.');
                }

                return [
                    'filename' => $image['filename'],
                    'contents' => $download->body(),
                ];
            }

            sleep(2);
        }

        throw new RuntimeException('ComfyUI a mis trop de temps à générer le visuel.');
    }
}