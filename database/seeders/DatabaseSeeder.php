<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->call([
            DepartmentsSeeder::class,
            PositionsSeeder::class,
            CampaignsSeeder::class,
            CompaniesSeeder::class,
            AreasSeeder::class,
            RolesAndPermissionsSeeder::class,
            UsersTableSeeder::class,
            InventoryV2PermissionsSeeder::class,
            InvMaintenanceCatalogsSeeder::class,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
