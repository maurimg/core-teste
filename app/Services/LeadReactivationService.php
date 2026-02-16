<?php

namespace App\Services;

use App\Models\Lead;

class LeadReactivationService
{
    /**
     * Gera mensagem automática de reativação
     */
    public static function generateMessage(Lead $lead): string
    {
        $name = $lead->name ?? 'amigo';

        $messages = [

            "Olá {$name}, tudo bem? Vi que você estava procurando um veículo conosco recentemente. Ainda está buscando carro?",

            "Oi {$name}! Recebemos novos veículos que podem combinar com o que você procurava. Quer que eu te envie algumas opções?",

            "Olá {$name}, passando para avisar que surgiram oportunidades com parcelas melhores. Quer dar uma olhada?",

            "Oi {$name}! Ainda posso te ajudar a encontrar seu carro ideal? Temos novidades no estoque.",

            "Olá {$name}, apareceu condição especial em veículos similares ao que você buscava. Quer conferir?"
        ];

        return $messages[array_rand($messages)];
    }
}
