<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;

Route::get("/", function () {
    return response()->json(['message' => 'Welcome to the File Server API']);
});

Route::prefix('files')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/download/{id}', [FileController::class, 'download']);
    Route::get('/{id}', [FileController::class, 'show']);
    Route::delete('/{id}', [FileController::class, 'delete']);
});
