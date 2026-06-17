<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateSellerCommand extends Command
{
    protected $signature = 'arcane:create-seller
                            {--store= : Store ID to attach this seller to}
                            {--name= : Full name}
                            {--email= : Email address}
                            {--password= : Password (will prompt if omitted)}';

    protected $description = 'Create a seller user and attach them to a store';

    public function handle(): int
    {
        $storeId = $this->option('store')
            ?: $this->ask('Store ID (see /admin/stores for IDs)');

        $store = Store::find($storeId);
        if (! $store) {
            $this->error("Store {$storeId} not found.");
            return self::FAILURE;
        }

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
            'password' => bcrypt($password),
        ]);

        $user->assignRole('seller');

        // Link seller to the store (assuming one store per seller for now)
        $store->user_id = $user->id;
        $store->save();

        $this->info("Seller created: {$user->email} for store {$store->name}");

        return self::SUCCESS;
    }
}
