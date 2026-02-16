<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Services\AIService;
use App\Services\LeadQualificationService;
use App\Services\LeadEventService;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $tenantId = $request->tenant_id ?? 1;
        $phone = $request->phone;

        $lead = Lead::where('tenant_id', $tenantId)
            ->where('phone', $phone)
            ->first();

        $existingConversation = $lead->conversation ?? [];
        $incomingConversation = $request->conversation ?? [];

        if (!empty($incomingConversation)) {
            $conversation = array_merge(
                $existingConversation,
                $incomingConversation
            );
        } else {
            $conversation = $existingConversation;
        }

        if ($lead) {
            $lead->update([
                'interest' => $request->interest ?? $lead->interest,
                'conversation' => $conversation,
                'status' => 'new',
            ]);
        } else {
            $lead = Lead::create([
                'tenant_id' => $tenantId,
                'source' => $request->source,
                'name' => $request->name,
                'phone' => $phone,
                'interest' => $request->interest,
                'conversation' => $conversation,
                'status' => 'new',
            ]);
        }

        LeadEventService::register(
            $lead->id,
            'lead_interaction',
            2,
            ['source' => $request->source]
        );

        $qualifier = new LeadQualificationService();
        $qualifier->calculateScore($lead->id, $conversation);

        $aiService = new AIService();

        $memory = $lead->memory ?? [];

        // IA atualiza memória
        $result = $aiService->generateReply($conversation, $memory);

        $reply = $result['reply'];
        $memory = $result['memory'];

        // salva memória atualizada
        $lead->memory = $memory;
        $lead->save();

        // salva resposta na conversa
        $conversation[] = [
            'role' => 'assistant',
            'message' => $reply,
        ];

        $lead->conversation = $conversation;
        $lead->save();

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
            'reply' => $reply,
        ]);
    }
}
