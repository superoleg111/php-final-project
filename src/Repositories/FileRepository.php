<?php

namespace App\Repositories;

use Core\App;
use PDO;

class FileRepository
{
    private PDO $db;

    public function __construct(App $app)
    {
        $this->db = $app->getService('db')->getConnection();
    }

    public function listByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
        SELECT id,
               filename AS original_name,
               stored_name,
               mime_type AS mime,
               size,
               created_at
        FROM files
        WHERE user_id = :uid
        ORDER BY created_at DESC
    ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add(int $userId, string $originalName, string $storedName, ?string $mime, int $size, ?int $directoryId = null): int
    {
        $sql = "
        INSERT INTO files
            (user_id, filename, stored_name, mime_type, size, directory_id)
        VALUES
            (:uid, :fn, :sn, :mt, :sz, :did)
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid' => $userId,
            'fn'  => $originalName,
            'sn'  => $storedName,
            'mt'  => $mime,
            'sz'  => $size,
            'did' => $directoryId,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function rename(int $id, string $newStoredName, string $newName): bool
    {
        $stmt = $this->db->prepare("
        UPDATE files
        SET stored_name = :sn, filename = :fn
        WHERE id = :id
    ");
        return $stmt->execute([
            'sn' => $newStoredName,
            'fn' => $newName,
            'id' => $id
        ]);
    }

    public function removeById(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
        SELECT 
            id,
            user_id,
            filename        AS original_name,
            stored_name,
            mime_type       AS mime,
            size,
            created_at
        FROM files
        WHERE id = :id
        LIMIT 1
    ");
        $stmt->execute(['id' => $id]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        return $file ?: null;
    }
}
