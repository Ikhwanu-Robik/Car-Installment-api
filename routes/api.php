<?php

use App\Http\Controllers\DataValidationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('/v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::group(['prefix' => '/v1'], function () {
    Route::post('/validation', [DataValidationController::class, 'requestValidation']);

    Route::get('/validations', [DataValidationController::class, 'getValidationStatus']);
});