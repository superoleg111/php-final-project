<?php

namespace App\Controllers;

use Core\Db;
use Core\Session;
use Core\Request;
use Core\Response;
use Core\App;

class FileShareController
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function share(int $file_id, int $user_id): Response
    {
        $db   = $this->app->getService('db')->getConnection();
        $file = $this->app->getService('fileRepository')->findById($file_id);
        $user = $this->app->getService('userRepository')->findById($user_id);

        if (!$file) {
            return new Response(['error' => 'File not found'], 404);
        }
        if (!$user) {
            return new Response(['error' => 'User not found'], 404);
        }

        $stmt = $db->prepare("SELECT 1 FROM file_user_access WHERE file_id = ? AND user_id = ?");
        $stmt->execute([$file_id, $user_id]);
        if ($stmt->fetch()) {
            return new Response(['error' => 'Already shared with this user'], 409);
        }

        $stmt = $db->prepare("INSERT INTO file_user_access (file_id, user_id) VALUES (?, ?)");
        $stmt->execute([$file_id, $user_id]);

        return new Response(['message' => 'Access granted'], 201);
    }

    public function list($file_id): Response
    {
        $db = $this->app->getService('db')->getConnection();

        $stmt = $db->prepare("
        SELECT users.id, users.name, users.email, file_user_access.granted_at
        FROM file_user_access
        JOIN users ON users.id = file_user_access.user_id
        WHERE file_user_access.file_id = ?
    ");
        $stmt->execute([$file_id]);
        $sharedUsers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new Response($sharedUsers, 200);
    }

    public function revoke($file_id, $user_id): Response
    {
        $db = $this->app->getService('db')->getConnection();

        $stmt = $db->prepare("DELETE FROM file_user_access WHERE file_id = ? AND user_id = ?");
        $stmt->execute([$file_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            return new Response(['message' => 'Access revoked'], 200);
        } else {
            return new Response(['error' => 'No such sharing found'], 404);
        }
    }
}
