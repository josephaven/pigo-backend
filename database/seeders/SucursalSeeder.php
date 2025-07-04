<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalSeeder extends Seeder
{
    public function run()
    {
        Sucursal::insert([
            ['nombre' => 'Centro', 'direccion' => 'Calle Principal #123', 'numero_whatsapp' => '9999999999'],
            ['nombre' => 'Transismica', 'direccion' => 'Av. Industrial #456', 'numero_whatsapp' => '8888888888'],
        ]);
    }
}

