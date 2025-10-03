<?php

namespace App\DTO;

class MailData
{

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly array  $data = []
    )
    {
    }
}
