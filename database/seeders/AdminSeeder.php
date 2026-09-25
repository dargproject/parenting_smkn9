<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Guru::updateOrCreate(
            ['nip' => env('ADMIN_NIP', 'admin')],
            [
                'nama' => env('ADMIN_NAME', 'Administrator Sistem'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ],
        );

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->roles()->sync([$adminRole->id]);
    }
}
