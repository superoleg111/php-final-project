<?php

namespace Core;

use App\Repositories\UserRepository;

class Auth
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function user(): ?array
    {
        $id = Session::get('user_id');
        if (!$id) {
            return null;
        }

        $user = $this->users->findById($id);
        if (!$user) {
            return null;
        }

        unset($user['password']);
        return $user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}
