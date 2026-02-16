<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FipeService
{
    private string $baseUrl = 'https://fipe.parallelum.com.br/api/v2';

    private function request(string $url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    // Retorna o melhor resultado único (quando score é conclusivo)
    public function getFipePriceByName(
        string $brand,
        string $model,
        string $version,
        string $year
    ): ?float {
        $results = $this->getFipeMatches($brand, $model, $version, $year);

        if (empty($results)) return null;

        // Só retorna se o primeiro tem score muito maior que o segundo
        if (count($results) === 1) {
            return $results[0]['price'];
        }

        $first  = $results[0]['score'];
        $second = $results[1]['score'];

        // Se o melhor tem score claramente superior (diferença >= 10), retorna ele
        if (($first - $second) >= 10) {
            Log::info('FipeService: Resultado conclusivo', $results[0]);
            return $results[0]['price'];
        }

        // Se os dois primeiros têm preços iguais ou muito próximos (diferença < 1%)
        $priceDiff = abs($results[0]['price'] - $results[1]['price']) / max($results[0]['price'], 1);
        if ($priceDiff < 0.01) {
            Log::info('FipeService: Preços praticamente iguais, retorna o primeiro');
            return $results[0]['price'];
        }

        // Ambíguo - retorna null para forçar esclarecimento
        Log::info('FipeService: Resultado ambíguo, múltiplos candidatos', [
            'count' => count($results),
            'top2'  => array_slice($results, 0, 2)
        ]);

        return null;
    }

    // Retorna TODOS os candidatos com preço (para resolução de ambiguidade)
    public function getFipeMatches(
        string $brand,
        string $model,
        string $version,
        string $year,
        int $limit = 5
    ): array {

        Log::info('FipeService: getFipeMatches', [
            'brand'   => $brand,
            'model'   => $model,
            'version' => $version,
            'year'    => $year
        ]);

        // 1. MARCA
        $brands = $this->request("{$this->baseUrl}/cars/brands");
        if (!is_array($brands)) return [];

        $brandCode = null;
        foreach ($brands as $b) {
            if (stripos($b['name'], $brand) !== false) {
                $brandCode = $b['code'];
                break;
            }
        }

        if (!$brandCode) {
            Log::error('FipeService: Marca não encontrada', ['brand' => $brand]);
            return [];
        }

        // 2. MODELOS
        $modelsData = $this->request("{$this->baseUrl}/cars/brands/{$brandCode}/models");
        if (!is_array($modelsData)) return [];

        $modelsList = isset($modelsData['models']) ? $modelsData['models'] : $modelsData;

        // 3. SCORE
        $searchModel   = strtolower(trim($model));
        $searchVersion = strtolower(trim($version));
        $versionParts  = !empty($searchVersion)
            ? array_filter(explode(' ', $searchVersion))
            : [];

        $candidates = [];

        foreach ($modelsList as $m) {
            $name = strtolower($m['name']);
            if (!str_contains($name, $searchModel)) continue;

            $score        = 10;
            $matchedParts = 0;

            foreach ($versionParts as $part) {
                if (str_contains($name, strtolower($part))) {
                    $score += 5;
                    $matchedParts++;
                }
            }

            if (!empty($versionParts) && $matchedParts === count($versionParts)) {
                $score += 10;
            }

            $candidates[] = [
                'code'  => $m['code'],
                'name'  => $m['name'],
                'score' => $score,
            ];
        }

        if (empty($candidates)) return [];

        usort($candidates, fn($a, $b) => $b['score'] - $a['score']);

        // 4. BUSCA PREÇO DOS TOP CANDIDATOS COM ANO DISPONÍVEL
        $matches = [];

        foreach ($candidates as $candidate) {
            if (count($matches) >= $limit) break;

            $years = $this->request(
                "{$this->baseUrl}/cars/brands/{$brandCode}/models/{$candidate['code']}/years"
            );

            if (!is_array($years)) continue;

            $yearCode = null;
            foreach ($years as $y) {
                if (str_contains($y['name'], $year)) {
                    $yearCode = $y['code'];
                    break;
                }
            }

            if (!$yearCode) continue;

            $data = $this->request(
                "{$this->baseUrl}/cars/brands/{$brandCode}/models/{$candidate['code']}/years/{$yearCode}"
            );

            if (!isset($data['price'])) continue;

            $price = str_replace(['R$', '.', ' '], '', $data['price']);
            $price = str_replace(',', '.', $price);
            $price = floatval($price);

            $matches[] = [
                'code'     => $candidate['code'],
                'name'     => $data['model'] ?? $candidate['name'],
                'score'    => $candidate['score'],
                'price'    => $price,
                'yearCode' => $yearCode,
            ];

            Log::info('FipeService: Match encontrado', [
                'name'  => $candidate['name'],
                'score' => $candidate['score'],
                'price' => $price,
            ]);
        }

        return $matches;
    }
}
