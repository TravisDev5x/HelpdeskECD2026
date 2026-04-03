<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_maintenances', function (Blueprint $table) {
            $table->index(['asset_id', 'end_date'], 'inv_maintenances_asset_id_end_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('inv_maintenances', function (Blueprint $table) {
            $table->dropIndex('inv_maintenances_asset_id_end_date_index');
        });
    }
};
