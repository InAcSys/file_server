<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;

Route::get("/", function () {
    return response()->json(['message' => 'Welcome to the File Server API']);
});

Route::prefix('files')->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
    Route::get('/', [FileController::class, 'list']);
    Route::get('/download/{filename}', [FileController::class, 'download']);
    Route::get('/view/{filename}', [FileController::class, 'view']);
    Route::delete('/{filename}', [FileController::class, 'delete']);
});
