<?php
namespace App\Repositories;

use Core\App;
use PDO;

class PasswordResetRepository
{
    private PDO $db;

    public function __construct(App $app)
    {
        $this->db = $app->getService('db')->getConnection();
    }

    public function create(string $email, string $token): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (email, token) VALUES (:email, :token)"
        );
        return $stmt->execute(['email'=>$email, 'token'=>$token]);
    }

    public function find(string $email, string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM password_resets WHERE email=:email AND token=:token"
        );
        $stmt->execute(['email'=>$email, 'token'=>$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function delete(string $email, string $token): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM password_resets WHERE email=:email AND token=:token"
        );
        return $stmt->execute(['email'=>$email, 'token'=>$token]);
    }
}
