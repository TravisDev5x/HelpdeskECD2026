<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::create([
            'department_id' => '1',
            'position_id' => '1',
            'name' => 'Sergio Pérez Ángeles',
            'usuario' => '2978',
            'email' => 'sperez@ecd.mx',
            'password' => '12345678',
        ]);

        $admin->assignRole('Admin');

        $admin = User::create([
            'department_id' => '1',
            'position_id' => '1',
            'name' => 'Jorge Lopez',
            'usuario' => '0050',
            'email' => 'jorgel@ecd.mx',
            'password' => '12345678',
        ]);

        $admin->assignRole('Admin');
    }
}
