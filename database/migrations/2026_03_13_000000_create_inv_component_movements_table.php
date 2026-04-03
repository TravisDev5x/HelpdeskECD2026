<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvComponentMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_component_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id');
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->enum('type', ['ASIGNAR', 'RETIRAR', 'BAJA']);
            $table->timestamp('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('component_id')
                  ->references('id')->on('components')
                  ->onDelete('cascade');

            $table->foreign('asset_id')
                  ->references('id')->on('inv_assets')
                  ->nullOnDelete();

            $table->foreign('admin_id')
                  ->references('id')->on('users')
                  ->onDelete('restrict');

            // Índices útiles desde el inicio
            $table->index(['component_id', 'type']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_component_movements');
    }
}

