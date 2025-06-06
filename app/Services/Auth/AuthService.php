<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Response;

class AuthService
{
    use Response;

    public function register($request)
    {

        try {
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign default role
            //$user->assignRole('user');

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], 'Registration successful', 201);
        } catch (\Throwable $e) {
            // Log the error for debugging purposes
            Log::error('Registration Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    // public function login($request): JsonResponse
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (!Auth::attempt($credentials)) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Invalid credentials',
    //         ], 401);
    //     }

    //     $user = Auth::user();
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Login successful',
    //         'user' => $user,
    //         'token' => $token,
    //     ]);
    // }

    // public function logout(): JsonResponse
    // {
    //     Auth::user()->currentAccessToken()->delete();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Logged out successfully',
    //     ]);
    // }

    // public function me(): JsonResponse
    // {
    //     return response()->json([
    //         'status' => true,
    //         'user' => Auth::user(),
    //     ]);
    // }
}
