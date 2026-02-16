<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\InventoryPublicController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Site simples de teste para listagem de veículos
| e captura de interesse.
|
*/

Route::get('/', function () {
    return redirect()->route('estoque.index');
});

Route::prefix('estoque')->group(function () {
    Route::get('/', [InventoryPublicController::class, 'index'])->name('estoque.index');
    Route::get('/{id}', [InventoryPublicController::class, 'show'])->name('estoque.show');
});

Route::post('/interesse', [InventoryPublicController::class, 'interesse'])->name('estoque.interesse');
