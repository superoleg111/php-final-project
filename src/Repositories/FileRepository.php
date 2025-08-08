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
        SET stored_name = :sn
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

//    public function setPublicToken(string $storedName, string $token): void
//    {
//        $stmt = $this->db->prepare("UPDATE files SET public_token = :token WHERE stored_name = :sn");
//        $stmt->execute(['token' => $token, 'sn' => $storedName]);
//    }

//    public function findByPublicToken(string $token): ?array
//    {
//        $stmt = $this->db->prepare("SELECT * FROM files WHERE public_token = :token");
//        $stmt->execute(['token' => $token]);
//        $file = $stmt->fetch(PDO::FETCH_ASSOC);
//        return $file ?: null;
//    }

//    public function listSharedByUser(int $userId): array
//    {
//        $stmt = $this->db->prepare("
//        SELECT f.id, f.filename, f.mime_type, f.size, f.created_at, s.token
//        FROM files f
//        JOIN file_shares s ON f.id = s.file_id
//        WHERE f.user_id = :uid
//        ORDER BY f.created_at DESC
//    ");
//        $stmt->execute(['uid' => $userId]);
//        return $stmt->fetchAll(PDO::FETCH_ASSOC);
//    }

//    public function revokeShare(int $userId, string $storedName): bool
//    {
//        $stmt = $this->db->prepare("
//        DELETE fs FROM file_shares fs
//        JOIN files f ON fs.file_id = f.id
//        WHERE f.user_id = :uid AND f.stored_name = :sn
//    ");
//        return $stmt->execute(['uid' => $userId, 'sn' => $storedName]);
//    }

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
