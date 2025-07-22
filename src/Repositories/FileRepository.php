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

    public function add(
        int $userId,
        ?int $directoryId,
        string $originalName,
        string $storedName,
        string $mime,
        int $size
    ): int {
        $stmt = $this->db->prepare("
        INSERT INTO files
            (user_id, directory_id, filename, stored_name, mime_type, size)
        VALUES
            (:uid, :did, :fn, :sn, :mt, :sz)
    ");
        $stmt->execute([
            'uid' => $userId,
            'did' => $directoryId,
            'fn'  => $originalName,
            'sn'  => $storedName,
            'mt'  => $mime,
            'sz'  => $size,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function rename(string $oldStoredName, string $newStoredName): void
    {
        $stmt = $this->db->prepare("
        UPDATE files
        SET stored_name = :new
        WHERE stored_name = :old
    ");
        $stmt->execute(['new' => $newStoredName, 'old' => $oldStoredName]);
    }

    public function remove(string $storedName): void
    {
        $stmt = $this->db->prepare("
        DELETE FROM files
        WHERE stored_name = :stored
    ");
        $stmt->execute(['stored' => $storedName]);
    }

    public function findByStoredName(int $userId, string $storedName): ?array
    {
        $stmt = $this->db->prepare("
        SELECT * FROM files
        WHERE user_id = :uid AND stored_name = :sn
        LIMIT 1
    ");
        $stmt->execute(['uid' => $userId, 'sn' => $storedName]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        return $file ?: null;
    }

    public function setPublicToken(string $storedName, string $token): void
    {
        $stmt = $this->db->prepare("UPDATE files SET public_token = :token WHERE stored_name = :sn");
        $stmt->execute(['token' => $token, 'sn' => $storedName]);
    }

    public function findByPublicToken(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM files WHERE public_token = :token");
        $stmt->execute(['token' => $token]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        return $file ?: null;
    }

    public function listSharedByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
        SELECT f.id, f.filename, f.mime_type, f.size, f.created_at, s.token
        FROM files f
        JOIN file_shares s ON f.id = s.file_id
        WHERE f.user_id = :uid
        ORDER BY f.created_at DESC
    ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function revokeShare(int $userId, string $storedName): bool
    {
        $stmt = $this->db->prepare("
        DELETE fs FROM file_shares fs
        JOIN files f ON fs.file_id = f.id
        WHERE f.user_id = :uid AND f.stored_name = :sn
    ");
        return $stmt->execute(['uid' => $userId, 'sn' => $storedName]);
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
