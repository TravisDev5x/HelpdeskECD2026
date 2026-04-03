<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inv_labels')) {
            return;
        }

        Schema::create('inv_labels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sede_id');
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Regla de negocio: una etiqueta de catálogo por sede.
            $table->unique('sede_id', 'inv_labels_sede_id_unique');
            $table->unique('name', 'inv_labels_name_unique');
            $table->index(['is_active', 'deleted_at'], 'inv_labels_active_deleted_idx');

            $table->foreign('sede_id')
                ->references('id')
                ->on('sedes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_labels');
    }
};
