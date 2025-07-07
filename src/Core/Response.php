<?php

namespace Core;

class Response
{
    private mixed $body;
    private int $status;

    public function __construct(mixed $body, int $status = 200)
    {
        $this->body = $body;
        $this->status = $status;
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->body);
    }
}
