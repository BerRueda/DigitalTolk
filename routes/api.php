<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth');
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:auth');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/translations', [TranslationController::class, 'index'])
        ->middleware('abilities:translations:read');
    Route::post('/translations', [TranslationController::class, 'store'])
        ->middleware('abilities:translations:write');
    Route::get('/translations/{translation}', [TranslationController::class, 'show'])
        ->middleware('abilities:translations:read');
    Route::put('/translations/{translation}', [TranslationController::class, 'update'])
        ->middleware('abilities:translations:write');
    Route::delete('/translations/{translation}', [TranslationController::class, 'destroy'])
        ->middleware('abilities:translations:write');

    Route::get('/export/translations', [ExportController::class, 'export'])
        ->middleware(['abilities:export:read', 'throttle:export']);
});
