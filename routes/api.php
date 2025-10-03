<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\PasswordResetController;

Route::get('/api-check', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is operational',
        'sanctum' => class_exists(Sanctum::class),
        'timestamp' => now()->toDateTimeString(),
    ]);
});

Route::get('/db-check', function () {
    try {
        Schema::hasTable('users');
        return response()->json(["message" => "Database connection okay", "status" => 200]);
    } catch (Throwable $e) {
        return response()->json(["message" => "Database connection failed", "status" => 500, "Actual" => $e->getMessage()]);
    }
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::prefix('password')->group(function () {

    Route::post('/request-reset', [PasswordResetController::class, 'requestReset'])
        ->name('password.request');

    Route::post('/verify-token', [PasswordResetController::class, 'verifyResetToken'])
        ->name('password.verify');
    
    Route::post('/create-new-password', [PasswordResetController::class, 'createNewPassword'])
        ->middleware(['auth:sanctum', 'abilities:reset-password'])
        ->name('password.new_password');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/file-upload', [FileUploadController::class, 'index']);
    Route::get('/dashboard', function (Request $request) {
        return response()->json(['message' => 'Dashboard accessed', 'user' => $request->user()]);
    });


});
