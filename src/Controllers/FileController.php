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
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->files = $app->getService('fileRepository');
    }

    public function list(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }
        $data = $this->files->listByUser($uid);
        return new Response($data);
    }

    public function get(Request $request, int $id): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $file = $this->files->findById($id);
        if (!$file || $file['user_id'] !== $uid) {
            return new Response(['error' => 'File not found'], 404);
        }

        unset($file['user_id']);
        return new Response($file);
    }

    public function add(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $data = $request->getBody();
        $isJson = isset($data['content']) && isset($data['name']);

        if ($isJson) {
            $originalName = $data['name'];
            $directoryId = $data['directory_id'] ?? null;
            $content = $data['content'];
            $storedName = uniqid() . '_' . basename($originalName);
            $dest = __DIR__ . '/../../storage/' . $storedName;

            if (file_put_contents($dest, $content) === false) {
                return new Response(['error' => 'Write failed'], 500);
            }

            if ($directoryId !== null) {
                $dirRepo = $this->app->getService('directoryRepository');
                $directory = $dirRepo->findById($uid, $directoryId);
                if (!$directory) {
                    return new Response(['error' => 'Directory not found'], 404);
                }
            }

            $size = strlen($content);
            $id = $this->files->add($uid, $originalName, $storedName, null, $size, $directoryId);

            return new Response([
                'message' => 'File stored from content',
                'id' => $id
            ]);
        }

        if (empty($_FILES['file'])) {
            return new Response(['error' => 'No file provided'], 422);
        }

        $file = $_FILES['file'];
        $originalName = $file['name'];
        $mime = $file['type'];
        $size = (int)$file['size'];
        $directoryId = null;
        $storedName = uniqid() . '_' . basename($originalName);
        $dest = __DIR__ . '/../../storage/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return new Response(['error' => 'Upload failed'], 500);
        }

        $id = $this->files->add($uid, $originalName, $storedName, $mime, $size, $directoryId);

        return new Response([
            'message' => 'File uploaded',
            'id' => $id
        ]);
    }

//    public function download(Request $request): Response
//    {
//        $token = $request->getQuery('token');
//        $storedName = $request->getQuery('name');
//
//        if (!$storedName) {
//            return new Response(['error' => 'Missing file name'], 422);
//        }
//
//        $file = null;
//
//        $userId = Session::get('user_id');
//        if ($userId) {
//            $file = $this->files->findByStoredName($userId, $storedName);
//        } elseif ($token) {
//            $file = $this->files->findByToken($token, $storedName);
//        } else {
//            return new Response(['error' => 'Unauthorized'], 401);
//        }
//
//        if (!$file) {
//            return new Response(['error' => 'File not found'], 404);
//        }
//
//        $path = __DIR__ . '/../../storage/' . $storedName;
//        if (!file_exists($path)) {
//            return new Response(['error' => 'File missing on server'], 410);
//        }
//
//        header('Content-Type: ' . $file['mime_type']);
//        header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
//        header('Content-Length: ' . $file['size']);
//        readfile($path);
//        exit;
//    }


    public function rename(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $data = $request->getBody();
        $fileId = isset($data['id']) ? (int)$data['id'] : null;
        $newName = trim($data['new_name'] ?? '');

        if (!$fileId || !$newName) {
            return new Response(['error' => 'id and new_name required'], 422);
        }

        $file = $this->files->findById($fileId);
        if (!$file || $file['user_id'] !== $uid) {
            return new Response(['error' => 'File not found'], 404);
        }

        $oldStored = $file['stored_name'];
        $oldPath = __DIR__ . '/../../storage/' . $oldStored;
        if (!file_exists($oldPath)) {
            return new Response(['error' => 'File missing on server'], 410);
        }

        $newStored = uniqid() . '_' . basename($newName);
        $newPath = __DIR__ . '/../../storage/' . $newStored;

        if (!rename($oldPath, $newPath)) {
            return new Response(['error' => 'Rename failed'], 500);
        }

        $this->files->rename($fileId, $newStored);

        return new Response([
            'message' => 'File renamed successfully',
            'stored_name' => $newStored,
            'original' => $newName
        ]);
    }


//    public function remove(Request $request): Response
//    {
//        $data = $request->getBody();
//        $stored = $data['name'] ?? '';
//
//        if (!$stored) {
//            return new Response(['error' => 'Filename required'], 422);
//        }
//
//        $path = __DIR__ . '/../../storage/' . $stored;
//        if (!file_exists($path)) {
//            return new Response(['error' => 'File not found'], 404);
//        }
//
//        unlink($path);
//        $this->files->remove($stored);
//
//        return new Response(['message' => 'File removed successfully']);
//    }

    public function removeById(Request $request, int $id): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $file = $this->files->findById($id);
        if (!$file || $file['user_id'] !== $uid) {
            return new Response(['error' => 'File not found'], 404);
        }

        $path = __DIR__ . '/../../storage/' . $file['stored_name'];
        if (file_exists($path)) {
            unlink($path);
        }

        $this->files->removeById($id);
        return new Response(['message' => 'File removed successfully']);
    }

//    public function share(Request $request): Response
//    {
//        $uid = Session::get('user_id');
//        if (!$uid) return new Response(['error' => 'Unauthorized'], 401);
//
//        $data = $request->getBody();
//        $stored = $data['name'] ?? '';
//        if (!$stored) return new Response(['error' => 'Filename required'], 422);
//
//        $token = bin2hex(random_bytes(8));
//        $this->files->setPublicToken($stored, $token);
//
//        return new Response([
//            'message' => 'Public link generated',
//            'url' => 'http://cloud-storage.local/public/download?token=' . $token,
//        ]);
//    }

//    public function publicDownload(Request $request): Response
//    {
//        $token = $request->getQuery('token');
//        if (!$token) return new Response(['error' => 'Missing token'], 422);
//
//        $file = $this->files->findByPublicToken($token);
//        if (!$file) return new Response(['error' => 'Invalid token'], 404);
//
//        $path = __DIR__ . '/../../storage/' . $file['stored_name'];
//        if (!file_exists($path)) return new Response(['error' => 'File not found'], 410);
//
//        header('Content-Type: ' . $file['mime_type']);
//        header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
//        header('Content-Length: ' . $file['size']);
//        readfile($path);
//        exit;
//    }

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
