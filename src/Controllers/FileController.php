<?php

namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use Core\Session;
use App\Repositories\FileRepository;

class FileController
{
    private FileRepository $files;

    public function __construct(App $app)
    {
        $this->files = $app->getService('fileRepository');
    }

    // GET /files/list
    public function list(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }
        $data = $this->files->listByUser($uid);
        return new Response($data);
    }

    // POST /files/add
    public function add(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }
        if (empty($_FILES['file'])) {
            return new Response(['error' => 'No file uploaded'], 422);
        }
        $file = $_FILES['file'];
        $stored = uniqid() . '_' . basename($file['name']);
        $dest = __DIR__ . '/../../storage/' . $stored;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return new Response(['error' => 'Upload failed'], 500);
        }
        $id = $this->files->add($uid, $file['name'], $stored, $file['type'], (int)$file['size']);
        return new Response(['message' => 'File uploaded', 'id' => $id]);
    }

    public function download(Request $request): Response
    {
        $token = $request->getQuery('token');
        $storedName = $request->getQuery('name');

        if (!$storedName) {
            return new Response(['error' => 'Missing file name'], 422);
        }

        $file = null;

        $userId = Session::get('user_id');
        if ($userId) {
            $file = $this->files->findByStoredName($userId, $storedName);
        } elseif ($token) {
            $file = $this->files->findByToken($token, $storedName);
        } else {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        if (!$file) {
            return new Response(['error' => 'File not found'], 404);
        }

        $path = __DIR__ . '/../../storage/' . $storedName;
        if (!file_exists($path)) {
            return new Response(['error' => 'File missing on server'], 410);
        }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
        header('Content-Length: ' . $file['size']);
        readfile($path);
        exit;
    }


    public function rename(Request $request): Response
    {
        $data = $request->getBody();
        $oldStored = $data['old'] ?? '';
        $newName = $data['new'] ?? '';

        if (!$oldStored || !$newName) {
            return new Response(['error' => 'Old and new filenames required'], 422);
        }

        $oldPath = __DIR__ . '/../../storage/' . $oldStored;
        $newStored = uniqid() . '_' . basename($newName);
        $newPath = __DIR__ . '/../../storage/' . $newStored;

        if (!file_exists($oldPath)) {
            return new Response(['error' => 'Original file not found'], 404);
        }

        rename($oldPath, $newPath);

        $this->files->rename($oldStored, $newStored);

        return new Response([
            'message' => 'File renamed successfully',
            'stored_name' => $newStored,
            'original' => $newName
        ]);
    }

    public function delete(Request $request): Response
    {
        $data = $request->getBody();
        $stored = $data['name'] ?? '';

        if (!$stored) {
            return new Response(['error' => 'Filename required'], 422);
        }

        $path = __DIR__ . '/../../storage/' . $stored;
        if (!file_exists($path)) {
            return new Response(['error' => 'File not found'], 404);
        }

        unlink($path);
        $this->files->delete($stored);

        return new Response(['message' => 'File deleted successfully']);
    }

    public function share(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) return new Response(['error' => 'Unauthorized'], 401);

        $data = $request->getBody();
        $stored = $data['name'] ?? '';
        if (!$stored) return new Response(['error' => 'Filename required'], 422);

        $token = bin2hex(random_bytes(8));
        $this->files->setPublicToken($stored, $token);

        return new Response([
            'message' => 'Public link generated',
            'url' => 'http://cloud-storage.local/public/download?token=' . $token,
        ]);
    }

    public function publicDownload(Request $request): Response
    {
        $token = $request->getQuery('token');
        if (!$token) return new Response(['error' => 'Missing token'], 422);

        $file = $this->files->findByPublicToken($token);
        if (!$file) return new Response(['error' => 'Invalid token'], 404);

        $path = __DIR__ . '/../../storage/' . $file['stored_name'];
        if (!file_exists($path)) return new Response(['error' => 'File not found'], 410);

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
        header('Content-Length: ' . $file['size']);
        readfile($path);
        exit;
    }

    public function shared(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $data = $this->files->listSharedByUser($uid);
        return new Response($data);
    }

    public function unshare(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $data = $request->getBody();
        $stored = $data['name'] ?? '';

        if (!$stored) {
            return new Response(['error' => 'Filename required'], 422);
        }

        $success = $this->files->revokeShare($uid, $stored);

        if (!$success) {
            return new Response(['error' => 'Failed to revoke share'], 500);
        }

        return new Response(['message' => 'Public link revoked']);
    }
}
