<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

/* ----------------- Autenticação (rotas públicas) ----------------- */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/* ----------------- Rotas protegidas por JWT ----------------- */
Route::middleware('jwt')->group(function () {
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    Route::patch('/user/emailname', [AuthController::class, 'updateProfile']);

    /* Despesas */
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::get('/expenses/{monthYear}', [ExpenseController::class, 'indexByMonth']);
    Route::get('/expenses/{monthYear}/{expenseId}', [ExpenseController::class, 'show']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{monthYear}/{expenseId}', [ExpenseController::class, 'update']);
    Route::patch('/expenses/{monthYear}/{expenseId}', [ExpenseController::class, 'patch']);
    Route::delete('/expenses/{monthYear}/{expenseId}', [ExpenseController::class, 'destroy']);
    Route::post('/expenses/{monthYear}/{expenseId}/replicate', [ExpenseController::class, 'replicate']);

    /* Receitas */
    Route::get('/revenues', [RevenueController::class, 'index']);
    Route::get('/revenues/{monthYear}', [RevenueController::class, 'indexByMonth']);
    Route::get('/revenues/{monthYear}/{revenueId}', [RevenueController::class, 'show']);
    Route::post('/revenues', [RevenueController::class, 'store']);
    Route::put('/revenues/{monthYear}/{revenueId}', [RevenueController::class, 'update']);
    Route::delete('/revenues/{monthYear}/{revenueId}', [RevenueController::class, 'destroy']);
    Route::post('/revenues/{monthYear}/{revenueId}/replicate', [RevenueController::class, 'replicate']);

    /* Suporte (Sistema de Chamados) */
    Route::get('/support/tickets', [SupportController::class, 'index']);
    Route::post('/support/tickets', [SupportController::class, 'store']);
    Route::post('/support/tickets/{id}/reply', [SupportController::class, 'reply']);
    Route::patch('/support/tickets/{id}/status', [SupportController::class, 'updateStatus']);
});