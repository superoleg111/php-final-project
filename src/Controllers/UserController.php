<?php

namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use PDO;

class UserController
{
    private PDO $db;

    public function __construct(private App $app)
    {
        $this->db = $app->getService('db')->getConnection();
    }

    public function list(Request $request): Response
    {
        $stmt = $this->db->query('SELECT id, name, email FROM users');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // DEBUG: log to Apache error log
        error_log('DEBUG: fetched ' . count($users) . ' users');

        return new Response($users);
    }

    public function login(Request $request): Response
    {
        // Placeholder logic
        return new Response(['message' => 'Login endpoint not yet implemented']);
    }
}
