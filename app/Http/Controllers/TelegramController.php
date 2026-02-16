<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;

class TelegramController extends Controller
{
    public function webhook(Request $request)
    {
        $update = $request->all();

        if (!isset($update['message'])) {
            return response()->json(['ok' => true]);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');

        if (!$text) {
            $this->sendMessage($chatId, "Recebi seu arquivo. Pode me informar os dados do veículo?");
            return response()->json(['ok' => true]);
        }

        $storagePath = storage_path("app/telegram");

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        $convFile = "{$storagePath}/{$chatId}_conversation.json";
        $memoryFile = "{$storagePath}/{$chatId}_memory.json";

        /*
        ==========================================
        REGRA DE TESTE: CLIENTE NOVO
        ==========================================
        */
        if (stripos($text, 'cliente novo') === 0) {

            if (file_exists($convFile)) unlink($convFile);
            if (file_exists($memoryFile)) unlink($memoryFile);

            $this->sendMessage(
                $chatId,
                "✅ Sessão reiniciada para teste. Pode enviar o veículo do novo cliente."
            );

            return response()->json(['ok' => true]);
        }

        $conversation = file_exists($convFile)
            ? json_decode(file_get_contents($convFile), true)
            : [];

        $memory = file_exists($memoryFile)
            ? json_decode(file_get_contents($memoryFile), true)
            : [];

        $conversation[] = [
            'role' => 'client',
            'message' => $text
        ];

        $ai = new AIService();

        $result = $ai->generateReply($conversation, $memory);

        $reply = $result['reply'];
        $memory = $result['memory'];

        $conversation[] = [
            'role' => 'assistant',
            'message' => $reply
        ];

        file_put_contents($convFile, json_encode($conversation));
        file_put_contents($memoryFile, json_encode($memory));

        $this->sendMessage($chatId, $reply);

        return response()->json(['ok' => true]);
    }

    private function sendMessage($chatId, $text)
    {
        $token = env('TELEGRAM_BOT_TOKEN');

        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $payload = [
            'chat_id' => $chatId,
            'text' => $text
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
