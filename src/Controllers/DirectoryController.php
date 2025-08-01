<?php
namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use Core\Session;
use App\Repositories\DirectoryRepository;

class DirectoryController
{
    private DirectoryRepository $dirs;

    public function __construct(App $app)
    {
        $this->dirs = $app->getService('directoryRepository');
    }

    public function add(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) return new Response(['error'=>'Unauthorized'],401);

        $data = $request->getBody();
        $name = trim($data['name'] ?? '');
        $parent = isset($data['parent_id']) ? (int)$data['parent_id'] : null;

        if (!$name) {
            return new Response(['error'=>'Name required'],422);
        }

        $id = $this->dirs->add($uid, $name, $parent);
        return new Response(['message'=>'Directory created','id'=>$id],201);
    }

    public function rename(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) return new Response(['error'=>'Unauthorized'],401);

        $data = $request->getBody();
        $id   = (int)($data['id'] ?? 0);
        $name = trim($data['new_name'] ?? '');

        if (!$id || !$name) {
            return new Response(['error'=>'id and name required'],422);
        }

        $success = $this->dirs->rename($uid, $id, $name);
        return $success
            ? new Response(['message'=>'Renamed'])
            : new Response(['error'=>'Not found or forbidden'],404);
    }

    public function get(Request $request, int $id): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) return new Response(['error'=>'Unauthorized'],401);

        $dir = $this->dirs->findById($uid, $id);
        if (!$dir) {
            return new Response(['error'=>'Not found'],404);
        }

        $dir['subdirectories'] = $this->dirs->listChildren($uid, $id);
        $dir['files']          = $this->dirs->listFiles($uid, $id);

        return new Response($dir);
    }

    public function delete(Request $request, int $id): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $dir = $this->dirs->findById($id);
        if (!$dir) {
            return new Response(['error' => 'Directory not found'], 404);
        }

        if ($dir['user_id'] !== $uid) {
            return new Response(['error' => 'You do not have permission to delete this directory'], 403);
        }

        $this->dirs->delete($uid, $id);

        return new Response(['message' => 'Directory deleted successfully']);
    }
}
