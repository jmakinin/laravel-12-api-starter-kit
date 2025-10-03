<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordChangeRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\PasswordVerifyRequest;
use App\Services\Auth\PasswordResetService;
use App\Traits\Response;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    use Response;

    public function __construct(protected PasswordResetService $passwordResetService)
    {
    }

    // Request Password Reset (SMS or Email).
    public function requestReset(PasswordResetRequest $request): JsonResponse
    {
        return $this->passwordResetService->requestReset($request->identifier, $request->reset_channel);

    }

    // Verify Token/Code (SMS code or Email token) and issue temporary API token.
    public function verifyResetToken(PasswordVerifyRequest $request)
    {
        return $this->passwordResetService->verifyResetToken($request->identifier, $request->token, $request->reset_channel);

    }

    // Reset Password (Authenticated using the temporary API token from Step 2).
    public function createNewPassword(PasswordChangeRequest $request): JsonResponse
    {
        return $this->passwordResetService->resetPassword($request->user(), $request->password);
    }
}
