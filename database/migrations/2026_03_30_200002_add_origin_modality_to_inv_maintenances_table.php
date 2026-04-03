<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_maintenances', function (Blueprint $table) {
            $table->foreignId('origin_id')->nullable()->after('supplier_id')->constrained('inv_maintenance_origins');
            $table->foreignId('modality_id')->nullable()->after('origin_id')->constrained('inv_maintenance_modalities');
        });
    }

    public function down(): void
    {
        Schema::table('inv_maintenances', function (Blueprint $table) {
            $table->dropForeign(['origin_id']);
            $table->dropForeign(['modality_id']);
            $table->dropColumn(['origin_id', 'modality_id']);
        });
    }
};
