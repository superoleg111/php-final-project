<?php
namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use Core\Session;
use App\Repositories\UserRepository;

class UserController
{
    private App $app;
    private UserRepository $users;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->users = $app->getService('userRepository');
    }

    public function list(Request $request): Response
    {
        $all = $this->users->findAll();
        return new Response($all);
    }

    public function get(Request $request, int $id): Response
    {
        $user = $this->users->findById($id);
        if (! $user) {
            return new Response(['error' => 'User not found'], 404);
        }
        unset($user['password']);
        return new Response($user);
    }

    public function login(Request $request): Response
    {
        $data = $request->getBody();
        if (empty($data['email']) || empty($data['password'])) {
            return new Response(['error' => 'Email and password required'], 422);
        }

        $user = $this->users->findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return new Response(['error' => 'Invalid credentials'], 401);
        }

        Session::set('user_id', $user['id']);
        return new Response([
            'message' => 'Login successful',
            'user'    => ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']],
        ]);
    }

    public function logout(Request $request): Response
    {
        Session::remove('user_id');
        return new Response(['message' => 'Logged out']);
    }

    public function me(Request $request): Response
    {
        $id = Session::get('user_id');
        if (!$id) {
            return new Response(['error'=>'Unauthorized'],401);
        }
        $user = $this->users->findById($id);
        if (!$user) {
            return new Response(['error'=>'User not found'],404);
        }

        unset($user['password']);
        return new Response($user);
    }

    public function update(Request $request): Response
    {
        $uid = Session::get('user_id');
        if (!$uid) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $data = $request->getBody();

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? null;

        if (!$name || !$email) {
            return new Response(['error' => 'Name and email are required'], 422);
        }

        $updated = [
            'name' => $name,
            'email' => $email,
        ];

        if (!empty($password)) {
            $updated['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $success = $this->users->update($uid, $updated);

        if (!$success) {
            return new Response(['error' => 'Update failed'], 500);
        }

        return new Response(['message' => 'Profile updated']);
    }

    public function adminList(Request $request): Response
    {
        $currentUser = $this->app->auth->user();

        if (!$currentUser || !$currentUser['is_admin']) {
            return new Response(['error' => 'Forbidden'], 403);
        }

        $users = $this->app->userRepository->findAll();
        return new Response($users);
    }

    public function adminDelete(Request $request, int $id): Response
    {
        $currentUser = $this->app->auth->user();

        if (!$currentUser || !$currentUser['is_admin']) {
            return new Response(['error' => 'Forbidden'], 403);
        }

        // Prevent admin from deleting themselves
        if ($currentUser['id'] === $id) {
            return new Response(['error' => 'You cannot delete yourself'], 400);
        }

        $user = $this->users->findById($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], 404);
        }

        $success = $this->users->delete($id);
        if (!$success) {
            return new Response(['error' => 'Failed to delete user'], 500);
        }

        return new Response(['message' => 'User deleted']);
    }
}
