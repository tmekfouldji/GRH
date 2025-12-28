<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = 'admin@talentee.com';

        if (!User::where('email', $adminEmail)->exists()) {
            User::create([
                'name' => 'Administrateur',
                'email' => $adminEmail,
                'password' => 'admin123',
                'role' => 'admin',
                'is_active' => true,
            ]);

            $this->command->info('Admin créé: admin@talentee.com / admin123');
        } else {
            $this->command->info('Admin existe déjà, pas de création.');
        }
    }
}
