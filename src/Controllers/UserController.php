<?php
namespace App\Controllers;

use Core\App;
use Core\Request;
use Core\Response;
use Core\Session;
use App\Repositories\UserRepository;

class UserController
{
    private UserRepository $users;

    public function __construct(App $app)
    {
        $this->users = $app->getService('userRepository');
    }

    public function list(Request $request): Response
    {
        $all = $this->users->findAll();
        return new Response($all);
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
        // hide password
        unset($user['password']);
        return new Response($user);
    }
}
