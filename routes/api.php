<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TelegramController;

Route::post('/leads', [LeadController::class, 'store']);
Route::get('/leads', [LeadController::class, 'index']);
Route::put('/leads/{id}', [LeadController::class, 'update']);
Route::post('/leads/{id}/message', [LeadController::class, 'addMessage']);

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
