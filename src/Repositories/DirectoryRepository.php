<?php
namespace App\Repositories;

use Core\App;
use PDO;

class DirectoryRepository
{
    private PDO $db;

    public function __construct(App $app)
    {
        $this->db = $app->getService('db')->getConnection();
    }

    public function add(int $uid, string $name, ?int $parent): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO directories (user_id, name, parent_id)
            VALUES (:uid, :nm, :pid)
        ");
        $stmt->execute([
            'uid' => $uid,
            'nm'  => $name,
            'pid' => $parent
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function rename(int $uid, int $id, string $name): bool
    {
        $stmt = $this->db->prepare("
            UPDATE directories
              SET name = :nm
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute(['nm'=>$name,'id'=>$id,'uid'=>$uid]);
    }

    public function findById(int $uid, int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id,name,parent_id,created_at
            FROM directories
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute(['id'=>$id,'uid'=>$uid]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listChildren(int $uid, int $parent): array
    {
        $stmt = $this->db->prepare("
            SELECT id,name,created_at
            FROM directories
            WHERE parent_id = :pid AND user_id = :uid
        ");
        $stmt->execute(['pid'=>$parent,'uid'=>$uid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listFiles(int $userId, int $directoryId): array
    {
        $stmt = $this->db->prepare("
        SELECT
            id,
            filename     AS original_name,
            stored_name,
            mime_type    AS mime,
            size,
            created_at
        FROM files
        WHERE user_id = :uid
          AND directory_id = :did
        ORDER BY created_at DESC
    ");
        $stmt->execute([
            'uid' => $userId,
            'did' => $directoryId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $uid, int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM directories
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute(['id'=>$id,'uid'=>$uid]);
    }
}
