<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campaign;

class CampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Campaign::create([
            'name' => 'Telcel',
        ]);
        Campaign::create([
            'name' => 'ALDEN VENTAS',
        ]);
        Campaign::create([
            'name' => 'ATENCION A CLIENTES',
        ]);
        Campaign::create([
            'name' => 'BIM',
        ]);
        Campaign::create([
            'name' => 'BIM SELECTO',
        ]);
        Campaign::create([
            'name' => 'CALIDAD',
        ]);
        Campaign::create([
            'name' => 'CAMPAÑAS ESPECIALES',
        ]);
        Campaign::create([
            'name' => 'COBRANZA',
        ]);
        Campaign::create([
            'name' => 'COBRANZA BANCO AZTECA',
        ]);
        Campaign::create([
            'name' => 'COBRANZA CREDIFRANCO',
        ]);
        Campaign::create([
            'name' => 'COBRANZA CSB',
        ]);
        Campaign::create([
            'name' => 'COBRANZA INBURSA,',
        ]);
        Campaign::create([
            'name' => 'COBRANZA SENA TELCEL',
        ]);
        Campaign::create([
            'name' => 'COBRANZA TELCEL',
        ]);
        Campaign::create([
            'name' => 'COBRANZA VIVUS',
        ]);
        Campaign::create([
            'name' => 'CSB CAPTURA',
        ]);
        Campaign::create([
            'name' => 'CSB RETENCION',
        ]);
        Campaign::create([
            'name' => 'CSB TDC NOCTURNO',
        ]);
        Campaign::create([
            'name' => 'CSB VENTAS',
        ]);
        Campaign::create([
            'name' => 'CSB VENTAS SUCURSALES',
        ]);
        Campaign::create([
            'name' => 'DIEMSA',
        ]);
        Campaign::create([
            'name' => 'LEALTAD',
        ]);
        Campaign::create([
            'name' => 'LIBERTAD',
        ]);
        Campaign::create([
            'name' => 'MAP 40',
        ]);
        Campaign::create([
            'name' => 'MAPFRE VALIDACIONES',
        ]);
        Campaign::create([
            'name' => 'PENSIONADOS PLUS',
        ]);
        Campaign::create([
            'name' => 'PRB',
        ]);
        Campaign::create([
            'name' => 'RHEEM',
        ]);
        Campaign::create([
            'name' => 'RHEEM CALL CENTER',
        ]);
        Campaign::create([
            'name' => 'SERVICIO SELECTO',
        ]);
        Campaign::create([
            'name' => 'STORI CARD',
        ]);
        Campaign::create([
            'name' => 'VALIDACIONES',
        ]);
    }
}
