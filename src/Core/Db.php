<?php

namespace Core;

use PDO;
use PDOException;

class Db
{
    private PDO $pdo;

    public function __construct(string $host, string $dbname, string $user, string $pass)
    {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception('DB connection error: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
