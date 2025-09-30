<?php

namespace App\DTO;

class MailData
{
    public string $title;
    public string $body;
    public array $data;

    public function __construct(string $title, string $body, array $data)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }
}
