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
        // Check if the provided password is already hashed, so we do not run into the behavior we had previously.
        $password = $data['password'];
        if (!(strlen($password) === 60 && preg_match('/^\$2y\$/', $password))) {
            $password = Hash::make($password);
        }

        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $password,
            'roles'    => $data['roles'] ?? [],
        ]);
    }
}
