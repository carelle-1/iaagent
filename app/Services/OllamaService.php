<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class OllamaService
{
    public function baseUrl(): string
    {
        return rtrim((string) config('services.ollama.url', 'http://127.0.0.1:11434'), '/');
    }

    public function models(): array
    {
        try {
            $response = Http::acceptJson()->timeout(8)->get($this->baseUrl() . '/api/tags');

            if (!$response->successful()) {
                return [];
            }

            $models = $response->json('models', []);

            return collect($models)
                ->pluck('name')
                ->filter()
                ->sort()
                ->values()
                ->all();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function generate(string $model, string $prompt, ?int $timeout = null, ?int $numPredict = null): array
    {
        @set_time_limit(0);

        $timeout = $timeout ?: (int) config('services.ollama.timeout', 420);

        $options = [
            'temperature' => 0.6,
        ];

        if ($numPredict) {
            $options['num_predict'] = $numPredict;
        }

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->post($this->baseUrl() . '/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json',
                    'think' => false,
                    'options' => $options,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Ollama a retourné HTTP ' . $response->status() . '.',
                ];
            }

            $responseData = $response->json('response', '');

            if (is_array($responseData)) {
                $responseData = json_encode($responseData);
            }

            return [
                'ok' => true,
                'raw' => (string) $responseData,
            ];
        } catch (ConnectionException $e) {
            $message = $e->getMessage();
            $isTimeout = str_contains($message, 'timed out') || str_contains($message, 'timeout') || str_contains($message, 'cURL error 28');

            return [
                'ok' => false,
                'error' => $isTimeout
                    ? 'Ollama a mis trop de temps à répondre. Essayez un modèle plus petit (llama3.2:1b) ou réduisez le nombre de publications.'
                    : 'Connexion à Ollama impossible (' . $this->baseUrl() . '). Vérifiez qu\'Ollama est démarré.',
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => 'Erreur Ollama : ' . $e->getMessage(),
            ];
        }
    }
}