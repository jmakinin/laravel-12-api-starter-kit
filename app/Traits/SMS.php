<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

trait SMS
{
    protected mixed $endPoint;
    protected mixed $apiKey;
    protected mixed $sender;

    /**
     * Initialize SMS configuration
     */
    protected function initializeSMS(): void
    {
        $this->endPoint = env('SMS_ENDPOINT');
        $this->apiKey = env('SMS_API_KEY');
        $this->sender = env('SMS_SENDER');
    }

    /**
     * Send SMS to recipients
     *
     * @param array $recipient recipient phone numbers
     * @param string $message SMS message string
     * @param string $pin SMS pin (optional)
     * @return bool|object
     */
    public function sendSMS(array $recipient, string $message, string $pin = ''): bool|object
    {
        $this->initializeSMS();

        $messageWithCode = $message . $pin;

        try {
            $response = Http::withoutVerifying()->post(
                $this->endPoint .
                    "?key=" . $this->apiKey .
                    "&to=" . $recipient[0] .
                    "&msg=" . $messageWithCode .
                    "&sender_id=" . $this->sender
            );

            if ($response->status() === 200) {
                $this->logSMS($recipient[0], $messageWithCode, 'sent');
                return true;
            } else {
                $this->logSMS($recipient[0], $messageWithCode, 'failed', $response->body());
                return $response;
            }
        } catch (\Exception $e) {
            $this->logSMS($recipient[0], $messageWithCode, 'error', $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Send bulk SMS to multiple recipients
     *
     * @param array $recipients Array of phone numbers
     * @param string $message SMS message
     * @param string $pin Optional PIN
     * @return array Results for each recipient
     */
    public function sendBulkSMS(array $recipients, string $message, string $pin = ''): array
    {
        $results = [];

        foreach ($recipients as $recipient) {
            $result = $this->sendSMS([$recipient], $message, $pin);
            $results[$recipient] = [
                'success' => $result === true,
                'response' => $result
            ];

            // Add small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }

        return $results;
    }

    /**
     * Send OTP SMS
     *
     * @param string $phoneNumber
     * @param int $otpLength
     * @param int $expiryMinutes
     * @return array
     */
    public function sendOTP(string $phoneNumber, int $otpLength = 6, int $expiryMinutes = 5): array
    {
        $otp = $this->generateOTP($otpLength);
        $message = "Your OTP is: {$otp}. Valid for {$expiryMinutes} minutes. Do not share with anyone.";

        // Store OTP in cache for verification
        $cacheKey = "otp_{$phoneNumber}";
        Cache::put($cacheKey, $otp, now()->addMinutes($expiryMinutes));

        $result = $this->sendSMS([$phoneNumber], $message);

        return [
            'success' => $result === true,
            'otp' => $otp, // Remove this in production for security
            'expires_at' => now()->addMinutes($expiryMinutes)->toDateTimeString(),
            'response' => $result
        ];
    }

    /**
     * Verify OTP
     *
     * @param string $phoneNumber
     * @param string $otp
     * @return bool
     */
    public function verifyOTP(string $phoneNumber, string $otp): bool
    {
        $cacheKey = "otp_{$phoneNumber}";
        $storedOtp = Cache::get($cacheKey);

        if ($storedOtp && $storedOtp === $otp) {
            Cache::forget($cacheKey); // Remove OTP after successful verification
            return true;
        }

        return false;
    }

    /**
     * Send scheduled SMS
     *
     * @param array $recipients
     * @param string $message
     * @param Carbon $scheduleTime
     * @param string $pin
     * @return bool
     */
    public function scheduleSMS(array $recipients, string $message, Carbon $scheduleTime, string $pin = ''): bool
    {
        // Store in database or queue for later processing
        // This is a basic implementation - you might want to use Laravel's job queue

        $scheduledData = [
            'recipients' => $recipients,
            'message' => $message,
            'pin' => $pin,
            'scheduled_at' => $scheduleTime->toDateTimeString(),
            'status' => 'scheduled'
        ];

        $cacheKey = "scheduled_sms_" . uniqid();
        Cache::put($cacheKey, $scheduledData, $scheduleTime);

        return true;
    }

    /**
     * Get SMS balance/credits
     *
     * @return array
     */
    public function getSMSBalance(): array
    {
        $this->initializeSMS();

        try {
            $response = Http::withoutVerifying()->get(
                $this->endPoint . "/balance?key=" . $this->apiKey
            );

            if ($response->status() === 200) {
                return [
                    'success' => true,
                    'balance' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch balance',
                'response' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check SMS delivery status
     *
     * @param string $messageId
     * @return array
     */
    public function checkDeliveryStatus(string $messageId): array
    {
        $this->initializeSMS();

        try {
            $response = Http::withoutVerifying()->get(
                $this->endPoint . "/status?key=" . $this->apiKey . "&id=" . $messageId
            );

            if ($response->status() === 200) {
                return [
                    'success' => true,
                    'status' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch status',
                'response' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Basic phone number validation (adjust pattern as needed)
        $pattern = '/^\+?[1-9]\d{1,14}$/';
        return preg_match($pattern, $phoneNumber);
    }

    /**
     * Format phone number for SMS gateway
     *
     * @param string $phoneNumber
     * @param string $countryCode
     * @return string
     */
    public function formatPhoneNumber(string $phoneNumber, string $countryCode = '233'): string
    {
        // Remove any non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if not present
        if (!str_starts_with($cleaned, $countryCode)) {
            // Remove leading zero if present
            $cleaned = ltrim($cleaned, '0');
            $cleaned = $countryCode . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Generate random OTP
     *
     * @param int $length
     * @return string
     */
    protected function generateOTP(int $length = 6): string
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }

    /**
     * Log SMS activity
     *
     * @param string $recipient
     * @param string $message
     * @param string $status
     * @param string|null $response
     */
    protected function logSMS(string $recipient, string $message, string $status, ?string $response = null): void
    {
        Log::info('SMS Activity', [
            'recipient' => $recipient,
            'message_length' => strlen($message),
            'status' => $status,
            'response' => $response,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Check if phone number is blacklisted
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function isBlacklisted(string $phoneNumber): bool
    {
        // Check against blacklisted numbers (implement your logic)
        $blacklistedNumbers = Cache::get('sms_blacklist', []);
        return in_array($phoneNumber, $blacklistedNumbers);
    }

    /**
     * Add phone number to blacklist
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function addToBlacklist(string $phoneNumber): bool
    {
        $blacklistedNumbers = Cache::get('sms_blacklist', []);
        if (!in_array($phoneNumber, $blacklistedNumbers)) {
            $blacklistedNumbers[] = $phoneNumber;
            Cache::put('sms_blacklist', $blacklistedNumbers, now()->addDays(30));
            return true;
        }
        return false;
    }

    /**
     * Remove phone number from blacklist
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function removeFromBlacklist(string $phoneNumber): bool
    {
        $blacklistedNumbers = Cache::get('sms_blacklist', []);
        $key = array_search($phoneNumber, $blacklistedNumbers);

        if ($key !== false) {
            unset($blacklistedNumbers[$key]);
            Cache::put('sms_blacklist', array_values($blacklistedNumbers), now()->addDays(30));
            return true;
        }
        return false;
    }

    /**
     * Get SMS templates
     *
     * @return array
     */
    public function getSMSTemplates(): array
    {
        return [
            'welcome' => 'Welcome to {app_name}! Your account has been created successfully.',
            'otp' => 'Your OTP is: {otp}. Valid for {minutes} minutes. Do not share with anyone.',
            'password_reset' => 'Your password reset code is: {code}. Valid for 10 minutes.',
            'appointment_reminder' => 'Reminder: You have an appointment on {date} at {time}.',
            'payment_confirmation' => 'Payment of {amount} has been received. Transaction ID: {transaction_id}',
            'account_verification' => 'Please verify your account by entering this code: {code}'
        ];
    }

    /**
     * Send SMS using template
     *
     * @param array $recipients
     * @param string $templateName
     * @param array $variables
     * @return array
     */
    public function sendSMSFromTemplate(array $recipients, string $templateName, array $variables = []): array
    {
        $templates = $this->getSMSTemplates();

        if (!isset($templates[$templateName])) {
            return [
                'success' => false,
                'message' => 'Template not found'
            ];
        }

        $message = $templates[$templateName];

        // Replace variables in template
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $this->sendBulkSMS($recipients, $message);
    }
}
