<?php

namespace Database\Seeders;

use App\Models\InvMaintenanceModality;
use App\Models\InvMaintenanceOrigin;
use Illuminate\Database\Seeder;

class InvMaintenanceCatalogsSeeder extends Seeder
{
    public function run(): void
    {
        $origins = [
            ['code' => 'INTERNO', 'name' => 'Interno', 'sort_order' => 10, 'is_active' => true],
            ['code' => 'EXTERNO', 'name' => 'Externo', 'sort_order' => 20, 'is_active' => true],
        ];
        foreach ($origins as $row) {
            InvMaintenanceOrigin::query()->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $modalities = [
            ['code' => 'PREVENTIVO', 'name' => 'Preventivo', 'sort_order' => 10, 'is_active' => true],
            ['code' => 'CORRECTIVO', 'name' => 'Correctivo', 'sort_order' => 20, 'is_active' => true],
        ];
        foreach ($modalities as $row) {
            InvMaintenanceModality::query()->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
