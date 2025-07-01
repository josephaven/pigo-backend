<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('rols')->insert([
            ['nombre' => 'Jefe'],
            ['nombre' => 'Gerencia'],
            ['nombre' => 'Atención al cliente'],
        ]);
    }
}
