<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('arcane:update-password {user_id : The ID of the user to update} {new_password : The new password for the user}')]
#[Description('Update the password for a specific user')]
class UpdatePassword extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $newPassword = $this->argument('new_password');

        $user = \App\Models\User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return self::FAILURE;
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        $this->info("Password for user ID {$userId} has been updated successfully.");
        return self::SUCCESS;
    }
}
