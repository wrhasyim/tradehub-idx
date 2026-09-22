<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin 1: Totoro
        User::updateOrCreate(
            ['email' => 'totoro@tradehubidx.id'],
            [
                'tradehub_id' => 'TH-00001',
                'name' => 'Totoro',
                'password' => Hash::make('password123'), // Ubah sesuai kebutuhan
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );

        // Superadmin 2: Eja
        User::updateOrCreate(
            ['email' => 'eja@tradehubidx.id'],
            [
                'tradehub_id' => 'TH-00002',
                'name' => 'Eja',
                'password' => Hash::make('password123'), // Ubah sesuai kebutuhan
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );
    }
}