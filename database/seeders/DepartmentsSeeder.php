<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::create([
            'name' => 'DIRECCION',
        ]);
        Department::create([
            'name' => 'FINANZAS',
        ]);
        Department::create([
            'name' => 'RECURSOS HUMANOS',
        ]);
        Department::create([
            'name' => 'ATENCION A CLIENTES',
        ]);
        Department::create([
            'name' => 'PROYECTOS',
        ]);
        Department::create([
            'name' => 'VENTAS',
        ]);
        Department::create([
            'name' => 'COBRANZA',
        ]);
        Department::create([
            'name' => 'CAPTURA',
        ]);
        Department::create([
            'name' => 'MARKETING DIGITAL',
        ]);
        Department::create([
            'name' => 'SISTEMAS',
        ]);
        Department::create([
            'name' => 'CALIDAD',
        ]);
        Department::create([
            'name' => 'ECD ASSISTANCE',
        ]);
    }
}
