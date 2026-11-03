<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin.email');
        $password = config('app.admin.password');

        if (! $email || ! $password) {
            $this->command->error('Set ADMIN_EMAIL and ADMIN_PASSWORD in .env first.');
            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('app.admin.name'),
                'password' => $password, // auto-hashed by the 'hashed' cast
                'role' => Role::Admin,
            ]
        );

        Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'title' => 'Full-Stack Developer',
                'email' => $user->email,
            ]
        );

        $this->command->info("Admin ready: {$user->email}");
    }
}