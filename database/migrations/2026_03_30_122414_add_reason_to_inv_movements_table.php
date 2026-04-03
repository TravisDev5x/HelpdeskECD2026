<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_movements', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('inv_movements', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
