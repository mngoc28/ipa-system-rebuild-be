<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Starter web routes for a cleaned project template.
|
*/

Route::get("/", function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Starter project is running.',
    ]);
});

Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Authentication endpoint has not been implemented yet.',
        'data'    => null,
    ], 401);
})->name('login');
