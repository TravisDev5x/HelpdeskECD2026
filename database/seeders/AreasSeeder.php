<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Area::create([
            'name' => 'Soporte',
        ]);
        Area::create([
            'name' => 'Metricas',
        ]);
        Area::create([
            'name' => 'Telecomunicaciones',
        ]);
    }
}
