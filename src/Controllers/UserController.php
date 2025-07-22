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

    /**
     * POST /users/reset_password
     *   Body: { "email": "user@example.com" }
     *   Returns { message: "Password reset link sent" }
     */
    public function resetPassword(Request $request): Response
    {
        $data = $request->getBody();
        $email = trim($data['email'] ?? '');
        if (!$email) {
            return new Response(['error'=>'Email is required'], 422);
        }

        $user = $this->users->findByEmail($email);
        if (!$user) {
            return new Response(['message'=>'If that email exists, a reset link was sent']);
        }

        $token = bin2hex(random_bytes(16));
        $this->app->getService('passwordResetRepository')->create($email, $token);

        $link = "http://cloud-storage.local/users/do_reset?email="
            . urlencode($email) . "&token=$token";

        return new Response(['message'=>'Password reset link', 'reset_link'=>$link]);
    }

    /**
     * GET /users/do_reset?email=…&token=…&new_password=…
     *   Returns { message: "Password updated" } or error
     */
    public function doReset(Request $request): Response
    {
        $email    = $request->getQuery('email', '');
        $token    = $request->getQuery('token', '');
        $newPass  = $request->getQuery('new_password', '');

        if (!$email || !$token || !$newPass) {
            return new Response(['error'=>'Missing parameters'], 422);
        }

        $pr = $this->app->getService('passwordResetRepository');
        $row = $pr->find($email, $token);
        if (!$row) {
            return new Response(['error'=>'Invalid or expired token'], 400);
        }

        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $this->users->updateByEmail($email, ['password' => $hashed]);

        $pr->delete($email, $token);

        return new Response(['message'=>'Password successfully reset']);
    }
}
