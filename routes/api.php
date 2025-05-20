<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::middleware('auth:sanctum')->post('/deposit', [TransactionController::class, 'deposit']);
Route::middleware('auth:sanctum')->post('/transfer', [TransactionController::class, 'transfer']);
Route::middleware('auth:sanctum')->post('/transactions/{id}/revert', [TransactionController::class, 'revert']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::get('/users', [AuthController::class, 'listUsers']);


