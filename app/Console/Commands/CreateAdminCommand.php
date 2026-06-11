<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'arcane:create-admin
                            {--name= : Full name}
                            {--email= : Email address}
                            {--password= : Password (will prompt if omitted)}';

    protected $description = 'Create an Arcane administrator account';

    public function handle(): int
    {
        $name     = $this->option('name')     ?: $this->ask('Name');
        $email    = $this->option('email')    ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 chars)');

        try {
            Validator::make(
                ['name' => $name, 'email' => $email, 'password' => $password],
                [
                    'name'     => ['required', 'string', 'max:255'],
                    'email'    => ['required', 'email', 'unique:users,email'],
                    'password' => ['required', 'string', 'min:8'],
                ],
            )->validate();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->error("{$field}: {$message}");
                }
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('admin');

        $this->info("Admin created: {$user->email}");
        return self::SUCCESS;
    }
}
