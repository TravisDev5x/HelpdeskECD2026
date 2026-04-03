<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('components')) {
            return;
        }

        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('name');
            $table->string('serie')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('capacidad')->nullable();
            $table->text('observacion')->nullable();
            $table->decimal('costo', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->string('owner')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('producto_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
