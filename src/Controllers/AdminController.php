<?php

namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use Core\Session;
use App\Repositories\UserRepository;

class AdminController
{
    private UserRepository $users;
    private App $app;

    public function __construct(App $app)
    {
        $this->app   = $app;
        $this->users = $app->getService('userRepository');
    }

    private function requireAdmin(): ?Response
    {
        $current = $this->app->auth->user();
        if (!$current || ($current['role'] ?? '') !== 'admin') {
            return new Response(['error' => 'Forbidden'], 403);
        }
        return null;
    }

    public function list(Request $request): Response
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }
        $all = $this->users->findAll();
        return new Response($all);
    }

    public function get(Request $request, int $id): Response
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }
        $user = $this->users->findById($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], 404);
        }
        unset($user['password']);
        return new Response($user);
    }

    public function delete(Request $request, int $id): Response
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }
        $current = $this->app->auth->user();
        if ($current['id'] === $id) {
            return new Response(['error' => 'Cannot delete yourself'], 400);
        }
        $user = $this->users->findById($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], 404);
        }
        $ok = $this->users->delete($id);
        if (!$ok) {
            return new Response(['error' => 'Delete failed'], 500);
        }
        return new Response(['message' => 'User deleted']);
    }

    public function update(Request $request, int $id): Response
    {
        if ($resp = $this->requireAdmin()) {
            return $resp;
        }
        $data = $request->getBody();
        $fields = [];
        if (!empty($data['name'])) {
            $fields['name'] = trim($data['name']);
        }
        if (!empty($data['email'])) {
            $fields['email'] = trim($data['email']);
        }
        if (!empty($data['password'])) {
            $fields['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (empty($fields)) {
            return new Response(['error' => 'Nothing to update'], 422);
        }
        $ok = $this->users->update($id, $fields);
        if (!$ok) {
            return new Response(['error' => 'Update failed'], 500);
        }
        return new Response(['message' => 'User updated']);
    }
}
