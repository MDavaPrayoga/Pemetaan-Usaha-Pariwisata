<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for users (Admin & Pimpinan).
     */
    public function run(): void
    {
        // 1. Akun Admin (Operator Teknis Dispar)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Administrator Dinas (Operator)',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Pimpinan (Eksekutif Dinas Pariwisata)
        User::updateOrCreate(
            ['username' => 'pimpinan'],
            [
                'nama_lengkap' => 'Pimpinan Dinas Pariwisata',
                'password' => Hash::make('password'),
                'role' => 'pimpinan',
            ]
        );
    }
}

