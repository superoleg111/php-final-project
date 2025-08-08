<?php

namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use Core\Session;
use App\Repositories\UserRepository;
use App\Repositories\PasswordResetRepository;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController
{
    private App $app;
    private UserRepository $users;
    private PasswordResetRepository $passwordResetRepo;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->users = $app->getService('userRepository');
        $this->passwordResetRepo = $app->getService('passwordResetRepository');
    }

    public function list(Request $request): Response
    {
        $currentUser = $this->app->auth->user();
        if (!$currentUser) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $all = $this->users->findAll();
        return new Response($all);
    }

    public function get(Request $request, int $id): Response
    {
        $currentUser = $this->app->auth->user();
        if (!$currentUser) {
            return new Response(['error' => 'Unauthorized'], 401);
        }

        $user = $this->users->findById($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], 404);
        }
        unset($user['password']);
        return new Response($user);
    }

    public function register(Request $request): Response
    {
        $data = $request->getBody();
        if (empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            return new Response(['error' => 'Email, password & role required'], 422);
        }

        if ($this->users->findByEmail($data['email'])) {
            return new Response(['error' => 'Email already in use'], 409);
        }

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $id = $this->users->create([
            'name' => $data['email'],
            'email' => $data['email'],
            'password' => $hash,
            'role' => $data['role']
        ]);

        return new Response(['message' => 'Registered', 'id' => $id], 201);
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
            'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']],
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
            return new Response(['error' => 'Unauthorized'], 401);
        }
        $user = $this->users->findById($id);
        if (!$user) {
            return new Response(['error' => 'User not found'], 404);
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

    public function resetPassword(Request $request): Response
    {
        $data = $request->getBody();
        $email = $data['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new Response(['error' => 'Valid email required'], 422);
        }

        $user = $this->users->findByEmail($email);
        if (!$user) {
            return new Response(['message' => 'If that email exists, you’ll receive a reset link'], 200);
        }

        $token = bin2hex(random_bytes(16));
        $this->passwordResetRepo->create($email, $token);

        $link = sprintf(
            "%s/do_reset?email=%s&token=%s",
            rtrim($_ENV['APP_URL'], '/'),
            urlencode($email),
            $token
        );

        /** @var PHPMailer $mailer */
        $mailer = $this->app->getService('mailer');
        try {
            $mailer->addAddress($email, $user['name']);
            $mailer->isHTML(true);
            $mailer->Subject = 'Password Reset Request';
            $mailer->Body = "
    You requested a password reset.<br><br>
    Link:<br>
    <a href=\"{$link}\">{$link}</a><br><br>
    <strong>Important:</strong> To complete the reset, you must manually add
    <code>&new_password=your_new_password</code> to the end of the link.
    <br><br>
    Example:<br>
    <code>{$link}&new_password=12345</code>
";
            $mailer->send();
        } catch (Exception $e) {
            return new Response(['error' => 'Failed to send email'], 500);
        }

        return new Response(['message' => 'Reset link sent if that email exists']);
    }

    /**
     * GET /users/do_reset?email=…&token=…&new_password=…
     *   Returns { message: "Password updated" } or error
     */
    public function doReset(Request $request): Response
    {
        $email = $request->getQuery('email', '');
        $token = $request->getQuery('token', '');
        $newPass = $request->getQuery('new_password', '');

        if (!$email || !$token || !$newPass) {
            return new Response(['error' => 'Missing parameters'], 422);
        }

        $pr = $this->app->getService('passwordResetRepository');
        $row = $pr->find($email, $token);
        if (!$row) {
            return new Response(['error' => 'Invalid or expired token'], 400);
        }

        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $this->users->updateByEmail($email, ['password' => $hashed]);

        $pr->delete($email, $token);

        return new Response(['message' => 'Password successfully changed']);
    }
}
