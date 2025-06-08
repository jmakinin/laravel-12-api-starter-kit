<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use App\Constants\AuthConstants;

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
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // Assign default role
            $role = AuthConstants::ADMIN;
            $user->assignRole($role);

            $token = $user->createToken($request->firstname)->plainTextToken;

            activity('new user')
                ->causedBy($user)
                ->withProperties(['ip' => $request->ip()])
                ->log('new user created')
            ;

            event(new Registered($user));

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
            ], AuthConstants::REGISTRATION_SUCCESS, 201);
        } catch (\Throwable $e) {
            Log::error('Registration Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function login($request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $rememberMe = $request->boolean('rememberMe', false);

            if (!Auth::attempt($credentials, $rememberMe)) {
                return $this->errorResponse(AuthConstants::INVALID_CREDENTIALS, 401);
            }

            $user = Auth::user();

            // Set token expiration based on remember_me
            $tokenExpiration = $rememberMe
                ? now()->addWeeks(2)
                : now()->addHours(2);

            $token = $user->createToken('auth_token', ['*'], $tokenExpiration)->plainTextToken;

            $user->getPermissionsViaRoles();

            activity('login')
                ->causedBy($user)
                ->withProperties(['ip' => $request->ip()])
                ->log('User login')
            ;

            event(new Login('api', $user, $rememberMe));

            return $this->successResponse([
                'status' => true,
                'user' => $user,
                'token' => $token,
                'token_expires_at' => $tokenExpiration->toDateTimeString()
            ], message: AuthConstants::LOGIN_SUCCESS, code: 200);
        } catch (\Throwable $e) {

            Log::error(AuthConstants::INVALID_CREDENTIALS . ': ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            Auth::user()->currentAccessToken()->delete();
            return $this->successResponse(null, AuthConstants::LOGOUT_SUCCESS, 200);
        } catch (\Throwable $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
