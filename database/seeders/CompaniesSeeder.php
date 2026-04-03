<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'name' => 'ECD',
        ]);
        Company::create([
            'name' => 'CONSUPAGO',
        ]);
        Company::create([
            'name' => 'CONSUBANCO',
        ]);
        Company::create([
            'name' => 'ALESTRA',
        ]);
        Company::create([
            'name' => 'E GLOBAL',
        ]);
        Company::create([
            'name' => 'GENESIS',
        ]);
        Company::create([
            'name' => 'INCONCERT',
        ]);
        Company::create([
            'name' => 'SEGURIDAD',
        ]);
        Company::create([
            'name' => 'TOTALPLAY',
        ]);
        Company::create([
            'name' => 'CCC',
        ]);
        Company::create([
            'name' => 'ISSABEL',
        ]);
        Company::create([
            'name' => 'GENESYS',
        ]);
    }
}
