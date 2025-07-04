<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run()
    {
        Rol::insert([
            ['nombre' => 'Jefe'],
            ['nombre' => 'Gerencia'],
            ['nombre' => 'Atención al cliente'],
        ]);
    }
}
