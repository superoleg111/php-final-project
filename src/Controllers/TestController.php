<?php

namespace App\Controllers;

use Core\Response;
use Core\Request;

class TestController
{
    public function ping(Request $request): array
    {
        return ['pong' => true];
    }
}
