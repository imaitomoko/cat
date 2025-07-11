<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'user_id' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'min:3'],
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'user_id' => $input['user_id'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
