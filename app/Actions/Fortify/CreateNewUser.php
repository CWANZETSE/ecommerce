<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Events\UserRegisteredEvent;
use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'phone' => [
                'required',
                'string',
                'regex:/^(?:0\d{9}|254\d{9})$/',
                'unique:users,phone',
            ],
            'password' => ['required', 'min:5', 'confirmed'],
        ])->validate();

        // Convert Kenyan numbers starting with 0 to international format.
        $phone = str_starts_with($input['phone'], '0')
            ? '254' . substr($input['phone'], 1)
            : $input['phone'];

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $phone,
            'password' => $input['password'],
        ]);

        UserRegisteredEvent::dispatch($user);

        return $user;
    }
}