<?php

namespace App\Services;

use App\Services\LeadEventService;

class LeadQualificationService
{
    public function calculateScore(int $leadId, array $conversation): void
    {
        foreach ($conversation as $msg) {

            if (($msg['role'] ?? '') !== 'client') {
                continue;
            }

            $text = strtolower($msg['message'] ?? '');

            /*
            |--------------------------------------------
            | Intenção clara de compra
            |--------------------------------------------
            */
            if (
                str_contains($text, 'comprar') ||
                str_contains($text, 'fechar') ||
                str_contains($text, 'negociar')
            ) {
                LeadEventService::register(
                    $leadId,
                    'buy_intent',
                    30
                );
            }

            /*
            |--------------------------------------------
            | Financiamento
            |--------------------------------------------
            */
            if (
                str_contains($text, 'financiamento') ||
                str_contains($text, 'parcela')
            ) {
                LeadEventService::register(
                    $leadId,
                    'financing_interest',
                    20
                );
            }

            /*
            |--------------------------------------------
            | Troca de veículo
            |--------------------------------------------
            */
            if (
                str_contains($text, 'troca') ||
                str_contains($text, 'dar meu carro')
            ) {
                LeadEventService::register(
                    $leadId,
                    'vehicle_trade',
                    20
                );
            }

            /*
            |--------------------------------------------
            | Pedido apenas de preço
            |--------------------------------------------
            */
            if (
                str_contains($text, 'preço') ||
                str_contains($text, 'valor')
            ) {
                LeadEventService::register(
                    $leadId,
                    'price_question',
                    5
                );
            }

            /*
            |--------------------------------------------
            | Urgência
            |--------------------------------------------
            */
            if (
                str_contains($text, 'urgente') ||
                str_contains($text, 'essa semana') ||
                str_contains($text, 'hoje ainda')
            ) {
                LeadEventService::register(
                    $leadId,
                    'urgent_purchase',
                    25
                );
            }
        }
    }
}
