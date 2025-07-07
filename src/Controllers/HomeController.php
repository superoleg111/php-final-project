<?php

namespace App\Controllers;

use Core\Response;
use Core\Request;

class HomeController
{
    public function index(Request $request): array
    {
        return ['message' => 'Welcome to the Home Page'];
    }
}