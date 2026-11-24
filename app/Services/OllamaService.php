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

    public function generate(
        string $model,
        string $prompt,
        ?int $timeout = null,
        ?int $numPredict = null,
        array|string $format = 'json',
        ?float $temperature = null
    ): array
    {
        @set_time_limit(0);

        $timeout = $timeout ?: (int) config('services.ollama.timeout', 420);

        $options = [
            'temperature' => $temperature ?? 0.6,
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
                    'format' => $format,
                    'think' => false,
                    'options' => $options,
                ]);

            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'Le moteur de génération a retourné une erreur HTTP ' . $response->status() . '.',
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
                    ? 'Le moteur de génération a mis trop de temps à répondre. Réduisez le nombre de publications ou réessayez.'
                    : 'Connexion au moteur de génération impossible. Réessayez dans quelques instants.',
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'error' => 'Erreur du moteur de génération.',
            ];
        }
    }
}