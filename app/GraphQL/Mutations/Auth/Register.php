<?php

namespace App\GraphQL\Mutations\Auth;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Register
{
    public function __invoke($_, array $args)
    {
        try {
            $user = User::create([
                'first_name' => $args['input']['first_name'],
                'last_name' => $args['input']['last_name'],
                'email' => $args['input']['email'],
                'username' => $args['input']['username'],
                'password' => bcrypt($args['input']['password']),
            ]);

            return [
                'access_token' => $user->createToken('auth')->plainTextToken,
                'token_type' => 'Bearer',
                'user' => $user,
            ];
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new \Exception("This email is already registered.");
            }
            throw $e;
        }
    }
}

