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


    public function __construct()
    {

        $this->endpoint = config('services.mnotify.endpoint');
        $this->apiKey = config('services.mnotify.api_key');
        $this->sender = config('services.mnotify.sender');
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): bool
    {

        if (!method_exists($notification, 'toSms')) {
            return false;
        }

        /** @var SMSData $smsData */
        $smsData = $notification->toSms($notifiable);


        $recipient = $smsData->number;

        $messageWithCode = $smsData->code
            ? "{$smsData->message}  [{$smsData->code}]"
            : $smsData->message;

        try {

            $urlWithKey = $this->endpoint . '?key=' . $this->apiKey;

            // Prepare the data payload
            $payload = [
                'recipient' => [$recipient],
                'sender' => $this->sender,
                'message' => $messageWithCode,
            ];

            $response = Http::post($urlWithKey, $payload);

            if ($response->successful()) {

                $apiResponse = $response->json();

                if (isset($apiResponse['status']) && $apiResponse['status'] === 'success') {
                    return true;
                }

                \Log::error('SMS API Logical Failure (2xx status): ' . json_encode($apiResponse) . ' To: ' . $recipient);
                return false;
            }

            \Log::error('SMS API HTTP Failed: ' . $response->status() . ' Body: ' . $response->body() . ' To: ' . $recipient);
            return false;

        } catch (Throwable $e) {

            \Log::error('SMS Connection Error: ' . $e->getMessage() . ' To: ' . $recipient);
            return false;
        }
    }
}
