<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Jefe PIGO',
            'email' => 'jefe@pigo.com',
            'password' => Hash::make('12345678'),
            'rol_id' => 1, // Asegúrate que el rol 'Jefe' tenga ID = 1
            'sucursal_id' => 1, // Asegúrate que la sucursal 'Centro' tenga ID = 1
        ]);
    }
}
