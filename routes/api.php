<?php

use App\Http\Controllers\SocietyController;
use App\Http\Middleware\EnsureUserIsValidator;
use App\Models\InstallmentApplySocieties;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\DataValidationController;

Route::prefix('/v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::group(['prefix' => '/v1'], function () {
    Route::post('/validation', [DataValidationController::class, 'requestValidation']);
    Route::get('/validations', [DataValidationController::class, 'getValidationStatus']);

    Route::get('/instalment_cars', [InstallmentController::class, 'getCars']);
    Route::get('/instalment_cars/{id}', [InstallmentController::class, 'findCar']);

    Route::post('/applications', [InstallmentController::class, 'applyForInstallment']);
    Route::get('/applications', [InstallmentController::class, 'getInstallment']);
});

Route::group(['prefix' => '/v1/validators', 'middleware' => ['auth:sanctum', EnsureUserIsValidator::class]], function () {
    Route::get("/societies/{id_card_number}", [SocietyController::class, "findByIdCardNumber"]);

    Route::get('/validations', [DataValidationController::class, "getValidationRequests"]);
    Route::post('/validations/{validation}', [DataValidationController::class, "setValidationStatus"]);

    Route::get('/installments', [InstallmentController::class, "getInstallmentApplicationRequests"]);
    Route::post("/installments/{installment}", [InstallmentController::class, "setInstallmentApplicationStatus"]);
});