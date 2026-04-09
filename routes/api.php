<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is ready for the new project.',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/me', function (Request $request) {
        return response()->json([
            'status' => 'ok',
            'data' => $request->user(),
        ]);
    })->middleware('auth:sanctum');
});
