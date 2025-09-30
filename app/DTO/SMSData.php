<?php

namespace App\DTO;

/**
 * Clean DTO using constructor property promotion for message details.
 */
class SMSData
{
    /**
     * @param string $number The recipient's phone number.
     * @param string $message The main message content.
     * @param string|null $code An optional short code (e.g., OTP) to prepend.
     */
    public function __construct(
        public readonly string $number,
        public readonly string $message,
        public readonly ?string $code = null,
    ) {}
}
