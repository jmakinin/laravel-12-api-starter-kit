<?php

namespace App\Services\Auth;

use App\Models\User;
use App\DTO\MailData;
use App\DTO\SMSData;
use App\Mail\PasswordResetMail;
use App\SMS\SendSMSNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Traits\Response;

class PasswordResetService
{
    use Response;

    private const VERIFICATION_TOKEN_LIFETIME_HOURS = 2;
    private const RESET_TOKEN_LIFETIME_MINUTES = 30;

    /**
     * Initiates the password reset process by generating a token/code and sending it.
     *
     * @param string $identifier User email or phone number.
     * @param string $reset_channel 'email' or 'sms'.
     * @return JsonResponse
     */
    public function requestReset(string $identifier, string $reset_channel): JsonResponse
    {
        $user = $this->findUserByIdentifier($identifier, $reset_channel);

        if (!$user) {
            $this->errorResponse("user not found, or method is invalid for this user");
        }

        DB::beginTransaction();

        try {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            
            if ($reset_channel === "email") {
                $token = Str::random(60);
                $this->sendResetEmail($user, $token);
            } elseif ($reset_channel === "sms") {
                $token = str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
                $this->sendSMSReset($user, $token);
            } else {
                $this->errorResponse("method is invalid for this user");
            }

            DB::table("password_reset_tokens")->insert([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            DB::commit();
            $responseMessage =
                $reset_channel === "email" ?
                    "Email has been sent to your registered email address" :
                    "A code has been sent to your registered phone number";

            return $this->successResponse(message: $responseMessage);

        } catch (\Exception $th) {
            DB::rollBack();
            Log::error('Error initiating password reset', ['error' => $th->getMessage(), 'user' => $user->email]);
            // Re-throw or throw a generic exception
            return $this->errorResponse('Failed to initiate reset. Please try again.');
        }
    }

    /**
     * Verifies the provided token/code and issues a temporary API token for final reset.
     *
     * @param string $identifier User email (for email flow) or phone number (for sms flow).
     * @param string $token The code (for sms) or the token string (for email).
     * @param string $reset_channel 'email' or 'sms'.
     * @return JsonResponse for success or failed attempt
     */
    public function verifyResetToken(string $identifier, string $token, string $reset_channel)
    {
        $user = $this->findUserByIdentifier($identifier, $reset_channel);

        if (!$user) {
            return $this->errorResponse("No account found for the provided identifier.");
        }

        //find the token record
        $resetRecord = DB::table("password_reset_tokens")
            ->where('email', $user->email)
            ->first();

        if (!$resetRecord) {
            return $this->errorResponse("Token not found. Please try again.");
        }

        //verify token/code expiration
        $createdAt = Carbon::parse($resetRecord->created_at);
        if (now()->diffInMinutes($createdAt) > self::RESET_TOKEN_LIFETIME_MINUTES) {
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            return $this->errorResponse("Token/Code has expired. Please try again.");
        }

        // Verify the token/code hash
        if (!Hash::check($token, $resetRecord->token)) {
            return $this->errorResponse("Invalid token/code provided. Please try again.");
        }

        //token/code verified, delete token and issue api token
        DB::table("password_reset_tokens")->where("email", $user->email)->delete();
        $tempTokenExpiration = now()->addHours(self::VERIFICATION_TOKEN_LIFETIME_HOURS);
        $tempTokenName = 'reset_temp_token_' . $user->email;

        // Revoke any previous temp tokens
        $user->tokens()->where('name', $tempTokenName)->delete();

        $token = $user->createToken($tempTokenName, ['reset-password'], $tempTokenExpiration)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_expiration' => $tempTokenExpiration
        ], message: "Token successfully verified.");

    }

    /**
     * Resets the user's password using the temporary API token.
     *
     * @param User $user The user authenticated via the temporary API token.
     * @param string $newPassword The new password.
     * @return JsonResponse
     */
    public function resetPassword(User $user, string $newPassword): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user->forceFill([
                'password' => Hash::make($newPassword),
            ])->save();

            DB::commit();
            $user->currentAccessToken()->delete();

            return $this->successResponse([], message: "Password successfully reset.");

        } catch (\Exception $th) {
            DB::rollBack();
            Log::error('Error resetting password', ['error' => $th->getMessage(), 'user' => $user->email]);
            return $this->errorResponse("Failed to reset password. Please try again.");
        }
    }

    /**
     * Finds a user by email or phone.
     */
    private function findUserByIdentifier(string $identifier, string $reset_channel): ?User
    {
        if ($reset_channel === 'email') {
            return User::where("email", $identifier)->first();
        } elseif ($reset_channel === 'sms') {
            return User::where("phone", $identifier)->first();
        }
        return null;
    }

    /**
     * Sends the password reset email.
     */
    protected function sendResetEmail(User $user, string $token): void
    {
        $frontendURL = config('app.frontend_urls.web');
        $tokenURL = $frontendURL . '/password-reset?token=' . $token . '&email=' . urlencode($user->email);
        $mailData = new MailData(
            "Password Reset Request",
            "Follow the link to reset your password.",
            ["tokenURL" => $tokenURL]
        );
        Mail::to($user->email)->send(new PasswordResetMail($mailData));
    }

    /**
     * Sends the password reset SMS code.
     */
    protected function sendSMSReset(User $user, string $code): void
    {
        $SMSData = new SMSData(
            code: $code,
            message: "Your password reset code for Tenet Digital is:",
            number: $user->phone
        );

        $user->notifyNow(new SendSMSNotification($SMSData));

    }

}
