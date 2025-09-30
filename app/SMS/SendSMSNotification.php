<?php

namespace App\SMS;

use App\DTO\SMSData;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification to send an SMS. It is queueable by default.
 */
class SendSMSNotification extends Notification
{
    use Queueable;

    /**
     * Store the DTO instance.
     */
    public function __construct(public readonly SmsData $smsData)
    {
    }

    /**
     * Define the delivery channel.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // 'sms' will map to the SmsChannel you're registering
        return ['sms'];
    }

    /**
     * Get the SMS representation of the notification.
     *
     * The Channel will receive this SmsData object.
     */
    public function toSms(object $notifiable): SmsData
    {
        return $this->smsData;
    }
}
