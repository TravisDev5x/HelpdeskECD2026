<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBitacorasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('bitacoras')) {
            Schema::create('bitacoras', function (Blueprint $table) {
                    $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('actividad');
                $table->string('descripcion')->nullable();
                $table->date('fecha')->nullable();
                $table->float('duracion')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('user_id')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitacoras');
    }
}
