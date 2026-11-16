<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
                            {--name= : The administrator name}
                            {--email= : The administrator email}
                            {--password= : The administrator password}';

    protected $description = 'Create an administrator account for the store';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Administrator name');
        $email = $this->option('email') ?? $this->ask('Administrator email');
        $password = $this->option('password') ?? $this->secret('Administrator password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $this->info("Administrator [{$email}] created successfully.");

        return self::SUCCESS;
    }
}