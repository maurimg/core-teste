<?php

namespace App\Services;

use App\Services\SalesAIProfile;
use App\Services\FipeService;
use Illuminate\Support\Facades\Log;

class AIService
{
    public function generateReply($conversation, $memory = [])
    {
        $memory = $this->extractVehicleDataViaAI($conversation, $memory);

        if (isset($memory['fipe_options'])) {
            $memory = $this->resolveFipeAmbiguity($conversation, $memory);
        }

        if (!$this->hasAllRequiredData($memory)) {
            $reply = $this->openAIReply($conversation, $memory);
            return ['reply' => $reply, 'memory' => $memory];
        }

        if (isset($memory['fipe_options']) && !isset($memory['vehicle']['fipe'])) {
            $reply = $this->buildAmbiguityQuestion($memory);
            return ['reply' => $reply, 'memory' => $memory];
        }

        $memory = $this->applyFipeValuation($memory);

        if (isset($memory['fipe_options']) && !isset($memory['vehicle']['fipe'])) {
            $reply = $this->buildAmbiguityQuestion($memory);
            return ['reply' => $reply, 'memory' => $memory];
        }

        $forced = $this->forcedBusinessReplies($conversation, $memory);
        if ($forced) {
            return ['reply' => $forced, 'memory' => $memory];
        }

        $reply = $this->openAIReply($conversation, $memory);
        return ['reply' => $reply, 'memory' => $memory];
    }

    private function hasAllRequiredData($memory)
    {
        if (!isset($memory['vehicle'])) return false;
        $v = $memory['vehicle'];
        return isset(
            $v['brand'],
            $v['model'],
            $v['version'],
            $v['engine'],
            $v['transmission'],
            $v['year'],
            $v['km']
        );
    }

    private function extractVehicleDataViaAI($conversation, $memory)
    {
        $clientMessages = array_filter($conversation, fn($m) => $m['role'] === 'client');
        if (empty($clientMessages)) return $memory;

        $fullText = implode("\n", array_column($clientMessages, 'message'));

        $prompt = "Analise as mensagens abaixo e extraia os dados do veículo mencionado.

Mensagens do cliente:
{$fullText}

Retorne APENAS um JSON válido com os campos encontrados. Use null para campos não mencionados.
Campos:
- brand: marca (ex: Chevrolet, Volkswagen, Fiat, Toyota, Honda, Hyundai, Jeep, Ford, Land Rover, etc)
- model: modelo exato como aparece na tabela FIPE (ex: Onix, HB20X, HB20S, HB20, Gol, Corolla, Discovery Sport, etc)
- version: versão (ex: LT, LTZ, Style, Premium, SE, SE R-Dynamic, Comfort, Trendline, etc)
- engine: motor (ex: 1.0, 1.4, 1.6, 1.8, 2.0, 2.2, 3.0, etc)
- transmission: manual ou automatico
- year: ano com 4 dígitos
- km: quilometragem como número inteiro (ex: 150000)

Regras:
- Se cliente disser 150 mil km retorne km: 150000
- Se cliente disser automatico retorne transmission: automatico
- Modelo deve ser o nome comercial exato (HB20X, nao HB20 X)
- Se nao souber um campo, retorne null

Retorne APENAS o JSON, sem explicacoes.";

        $payload = json_encode([
            'model'    => config('services.openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => 'Extraia dados de veiculos. Retorne APENAS JSON valido, sem markdown.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . config('services.openai.api_key'),
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => $payload
        ]);

        $res  = curl_exec($ch);
        curl_close($ch);

        $data    = json_decode($res, true);
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) return $memory;

        $content   = trim(preg_replace('/```json|```/', '', $content));
        $extracted = json_decode($content, true);

        if (!is_array($extracted)) {
            Log::error('AIService: JSON invalido', ['content' => $content]);
            return $memory;
        }

        Log::info('AIService: Dados extraidos via OpenAI', $extracted);

        foreach (['brand', 'model', 'version', 'engine', 'transmission', 'year', 'km'] as $field) {
            if (!empty($extracted[$field]) && $extracted[$field] !== null) {
                $memory['vehicle'][$field] = $extracted[$field];
            }
        }

        return $memory;
    }

    private function resolveFipeAmbiguity($conversation, $memory)
    {
        $lastMsg = '';
        for ($i = count($conversation) - 1; $i >= 0; $i--) {
            if ($conversation[$i]['role'] === 'client') {
                $lastMsg = strtolower($conversation[$i]['message']);
                break;
            }
        }

        $options = $memory['fipe_options'];

        foreach ($options as $idx => $option) {
            $nameLower = strtolower($option['name']);
            $number    = (string)($idx + 1);

            if (
                str_contains($lastMsg, $number) ||
                str_contains($lastMsg, $nameLower) ||
                similar_text($lastMsg, $nameLower) > 15
            ) {
                $km = $memory['vehicle']['km'] ?? 0;
                $memory['vehicle']['fipe']  = $option['price'];
                $memory['vehicle']['offer'] = round($option['price'] * (1 - $this->getDiscount($km)));
                unset($memory['fipe_options']);

                Log::info('AIService: Ambiguidade resolvida', [
                    'chosen' => $option['name'],
                    'price'  => $option['price']
                ]);

                return $memory;
            }
        }

        return $memory;
    }

    private function buildAmbiguityQuestion($memory)
    {
        $options = $memory['fipe_options'];
        $count   = count($options);
        $model   = $memory['vehicle']['model'] ?? 'veiculo';

        $msg = "Encontrei {$count} versoes do {$model} na tabela FIPE com valores diferentes. Para garantir o valor correto, qual das opcoes abaixo corresponde ao seu carro?\n\n";

        foreach ($options as $idx => $option) {
            $num   = $idx + 1;
            $name  = $option['name'];
            $price = number_format($option['price'], 0, ',', '.');
            $msg  .= "{$num}) {$name} - R$ {$price}\n";
        }

        $msg .= "\nResponda com o numero da opcao ou o nome que melhor descreve o seu carro.";

        return $msg;
    }

    private function getDiscount(int $km): float
    {
        if ($km <= 50000)  return 0.12;
        if ($km <= 100000) return 0.15;
        if ($km <= 120000) return 0.20;
        if ($km <= 150000) return 0.25;
        if ($km <= 180000) return 0.35;
        if ($km <= 200000) return 0.40;
        if ($km <= 250000) return 0.50;
        if ($km <= 300000) return 0.60;
        if ($km <= 500000) return 0.70;
        return 0.75;
    }

    private function applyFipeValuation($memory)
    {
        if (!isset($memory['vehicle'])) return $memory;

        $v = $memory['vehicle'];

        if (!$this->hasAllRequiredData($memory)) return $memory;

        if (isset($memory['vehicle']['fipe'])) return $memory;

        $fipe     = new FipeService();
        $transKey = ($v['transmission'] === 'manual') ? 'Mec' : 'Aut';

        $searches = [
            "{$v['version']} {$v['engine']} {$transKey}",
            "{$v['version']} {$v['engine']}",
            "{$v['engine']} {$transKey}",
            $v['version'],
            '',
        ];

        $allMatches = [];

        foreach ($searches as $search) {
            $matches = $fipe->getFipeMatches($v['brand'], $v['model'], $search, $v['year'], 5);

            if (!empty($matches)) {
                $allMatches = $matches;
                Log::info('AIService: Matches encontrados', [
                    'search' => $search,
                    'count'  => count($matches)
                ]);
                break;
            }
        }

        if (empty($allMatches)) {
            Log::error('AIService: Nenhum match FIPE encontrado');
            $memory['vehicle']['fipe_error'] = true;
            return $memory;
        }

        // Apenas um resultado - usa direto
        if (count($allMatches) === 1) {
            $memory['vehicle']['fipe']  = $allMatches[0]['price'];
            $memory['vehicle']['offer'] = round($allMatches[0]['price'] * (1 - $this->getDiscount($v['km'])));
            unset($memory['fipe_options']);
            return $memory;
        }

        $scoreDiff  = $allMatches[0]['score'] - $allMatches[1]['score'];
        $priceDiff  = abs($allMatches[0]['price'] - $allMatches[1]['price']);
        $priceRatio = $priceDiff / max($allMatches[0]['price'], 1);

        // Score conclusivo OU precos praticamente iguais
        if ($scoreDiff >= 10 || $priceRatio < 0.01) {
            $memory['vehicle']['fipe']  = $allMatches[0]['price'];
            $memory['vehicle']['offer'] = round($allMatches[0]['price'] * (1 - $this->getDiscount($v['km'])));
            unset($memory['fipe_options']);
            Log::info('AIService: Match conclusivo', $allMatches[0]);
            return $memory;
        }

        // Ambiguo - salva opcoes para perguntar
        $memory['fipe_options'] = array_slice($allMatches, 0, 4);
        Log::info('AIService: FIPE ambiguo', ['options' => $memory['fipe_options']]);

        return $memory;
    }

    private function forcedBusinessReplies($conversation, $memory)
    {
        if (!isset($memory['vehicle']['offer'])) return null;

        $last = strtolower(end($conversation)['message'] ?? '');

        if (str_contains($last, 'fipe') || str_contains($last, 'tabela')) {
            return "O valor FIPE do seu {$memory['vehicle']['model']} e R$ "
                . number_format($memory['vehicle']['fipe'], 0, ',', '.') . ".";
        }

        if (
            str_contains($last, 'quanto') ||
            str_contains($last, 'valor')  ||
            str_contains($last, 'pagam')  ||
            str_contains($last, 'oferta')
        ) {
            return "Pela tabela FIPE o valor e R$ "
                . number_format($memory['vehicle']['fipe'], 0, ',', '.')
                . " e conseguimos pagar aproximadamente R$ "
                . number_format($memory['vehicle']['offer'], 0, ',', '.')
                . ". Esse valor pode melhorar na avaliacao presencial se o veiculo estiver em boas condicoes mecanicas e visuais, e com pneus em bom estado. Voce consegue passar aqui amanha de manha ou prefere a tarde?";
        }

        return null;
    }

    private function openAIReply($conversation, $memory)
    {
        $systemPrompt = SalesAIProfile::systemPrompt();

        if (isset($memory['vehicle'])) {
            $v = $memory['vehicle'];

            $systemPrompt .= "\n\n=== DADOS JA COLETADOS ===\n";
            if (isset($v['brand']))        $systemPrompt .= "Marca: {$v['brand']}\n";
            if (isset($v['model']))        $systemPrompt .= "Modelo: {$v['model']}\n";
            if (isset($v['version']))      $systemPrompt .= "Versao: {$v['version']}\n";       else $systemPrompt .= "Versao: FALTA\n";
            if (isset($v['engine']))       $systemPrompt .= "Motor: {$v['engine']}\n";          else $systemPrompt .= "Motor: FALTA\n";
            if (isset($v['year']))         $systemPrompt .= "Ano: {$v['year']}\n";              else $systemPrompt .= "Ano: FALTA\n";
            if (isset($v['transmission'])) $systemPrompt .= "Cambio: {$v['transmission']}\n";  else $systemPrompt .= "Cambio: FALTA\n";
            if (isset($v['km']))           $systemPrompt .= "KM: " . number_format($v['km'], 0, ',', '.') . "\n"; else $systemPrompt .= "KM: FALTA\n";

            if (isset($v['fipe_error'])) {
                $systemPrompt .= "\nATENCAO: Nao foi possivel localizar este veiculo na tabela FIPE.\n";
                $systemPrompt .= "Peca ao cliente para confirmar versao e motor com mais detalhes.\n";
            }

            if (isset($v['fipe'])) {
                $systemPrompt .= "\n=== VALORES CALCULADOS ===\n";
                $systemPrompt .= "FIPE: R$ " . number_format($v['fipe'], 0, ',', '.') . "\n";
                $systemPrompt .= "Oferta: R$ " . number_format($v['offer'], 0, ',', '.') . "\n";
            }
        }

        $messages[] = ['role' => 'system', 'content' => $systemPrompt];

        foreach ($conversation as $msg) {
            $messages[] = [
                'role'    => $msg['role'] === 'client' ? 'user' : 'assistant',
                'content' => $msg['message']
            ];
        }

        $payload = json_encode([
            'model'       => config('services.openai.model', 'gpt-4o-mini'),
            'messages'    => $messages,
            'temperature' => 0.7
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . config('services.openai.api_key'),
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => $payload
        ]);

        $res  = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($res, true);
        return $data['choices'][0]['message']['content'] ?? 'Desculpe, tive um problema tecnico. Pode repetir?';
    }
}
