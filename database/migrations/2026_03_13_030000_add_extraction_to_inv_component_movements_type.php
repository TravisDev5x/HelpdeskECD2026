<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddExtractionToInvComponentMovementsType extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE inv_component_movements MODIFY COLUMN type ENUM('ASIGNAR', 'RETIRAR', 'BAJA', 'EXTRACCION') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE inv_component_movements MODIFY COLUMN type ENUM('ASIGNAR', 'RETIRAR', 'BAJA') NOT NULL");
    }
}
