<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nama_role' => 'admin',
                'deskripsi' => 'Administrator dengan akses penuh ke sistem',
            ],
            [
                'nama_role' => 'manager',
                'deskripsi' => 'Manager dengan akses master data dan laporan',
            ],
            [
                'nama_role' => 'kasir',
                'deskripsi' => 'Kasir dengan akses transaksi POS',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
