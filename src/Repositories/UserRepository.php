<?php

namespace App\Repositories;

use Core\App;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct(App $app)
    {
        $this->db = $app->getService('db')->getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT id,name,email FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
