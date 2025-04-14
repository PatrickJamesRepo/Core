<?php

namespace Tests\Fakes;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;

class FakeUserService extends UserService
{
    /**
     * Create a new user instance.
     * This fake simply creates the user using the provided data,
     * including wallet fields.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    public function create(array $data): User
    {
        return User::create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => Hash::make($data['password']),
            'wallet_address'   => $data['wallet_address'] ?? null,
            'wallet_signature' => $data['wallet_signature'] ?? null,
            'roles'            => $data['roles'] ?? [],
        ]);
    }

}
