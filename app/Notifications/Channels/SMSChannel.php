<?php

namespace App\Notifications\Channels;

use App\DTO\SMSData;
use Illuminate\Support\Facades\Http;
use Illuminate\Notifications\Notification;
use Throwable;

class SMSChannel
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $sender;

    // Use the config helper to fetch service keys
    public function __construct()
    {
        // IMPORTANT: Ensure your config/services.php has the 'mnotify' section
        $this->endpoint = config('services.mnotify.endpoint');
        $this->apiKey = config('services.mnotify.api_key');
        $this->sender = config('services.mnotify.sender');
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): bool
    {
        // 1. Check if the notification implements the toSms method
        if (!method_exists($notification, 'toSms')) {
            return false;
        }

        /** @var SMSData $smsData */
        $smsData = $notification->toSms($notifiable);

        if (!$smsData instanceof SMSData) {
            throw new \InvalidArgumentException('toSms must return an App\Data\SmsData object.');
        }

        // 2. Format the message
        $recipient = $smsData->number;

        // Prepend code if it exists, otherwise just use the message
        $messageWithCode = $smsData->code
            ? "{$smsData->message}[{$smsData->code}] "
            : $smsData->message;

        try {
            // 3. Make the API request using Laravel's clean HTTP client
            $response = Http::withoutVerifying()
                ->post($this->endpoint, [
                    'key' => $this->apiKey,
                    'recipient' => [$recipient],
                    'message' => $messageWithCode,
                    'sender' => $this->sender,
                ]);

            if ($response->successful()) {
                return true;
            }

            // If the request didn't return a 2xx status, log the failure
            \Log::error('SMS API Failed: ' . $response->status() . ' Body: ' . $response->body() . ' To: ' . $recipient);
            return false;

        } catch (Throwable $e) {
            // Log any connection or Guzzle exceptions
            \Log::error('SMS Connection Error: ' . $e->getMessage() . ' To: ' . $recipient);
            return false;
        }
    }
}
