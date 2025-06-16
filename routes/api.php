<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Http\Controllers\FileUploadController;

Route::get('/api-check', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is operational',
        'sanctum' => class_exists(\Laravel\Sanctum\Sanctum::class),
        'timestamp' => now()->toDateTimeString(),
    ]);
});

Route::get('/db-check', function () {
    try {
        Schema::hasTable('users');
        return response()->json(["message" => "Database connection okay", "status" => 200]);
    } catch (\Throwable $e) {
        return response()->json(["message" => "Database connection failed", "status" => 500]);
    }
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Route::get('/verify-email-token/{token}', [AuthController::class, 'verifyToken']);
// Route::post('/password/request-reset', [PasswordResetController::class, 'requestReset']);
// Route::get('/password/verify-reset-token/{token}', [PasswordResetController::class, 'verifyResetToken']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/file-upload', [FileUploadController::class, 'index']);
    Route::get('/dashboard', function (Request $request) {
        return response()->json(['message' => 'Dashboard accessed', 'user' => $request->user()]);
    });
});


// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'registerUser']);
// Route::get('/verify-email-token/{token}', [AuthController::class, 'verifyToken']);

// Route::post('/password/request-reset', [PasswordResetController::class, 'requestReset']);
// Route::get('/password/verify-reset-token/{token}', [PasswordResetController::class, 'verifyResetToken']);


// //Authenticated Routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/regions', [AuthController::class, 'getRegions']);
//     Route::post('/complete-profile', [AuthController::class, 'completeAccount']);
//     Route::post('/password/update-password', [PasswordResetController::class, 'resetPassword']);
// });


// //Roles and permissions based routes.
// Route::group(['middleware' => ['auth:sanctum', 'role:super_admin|partner_bank_admin|admin'], 'prefix' => 'admin'], function () {

//     Route::get('/dashboard', function () {
//         return response()->json(['admin dashboard accessed']);
//     });

//     Route::post('/invite-user', [AuthController::class, 'adminInviteUser']);
//     Route::get('/users', [UserController::class, 'index']);
//     Route::get('/user/{id}', [UserController::class, 'getSingleUser']);
//     Route::put('/user/{id}', [UserController::class, 'updateUser']);
//     Route::delete('/user/{id}', [UserController::class, 'destroyUser']);
//     Route::get('/destroyed-users', [UserController::class, 'destroyedUsers']);
//     Route::post('/reset-user-password', [AuthController::class, 'adminResetUserPassword']);
// });