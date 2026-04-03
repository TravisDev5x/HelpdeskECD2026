<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tipo');
            $table->string('sistema');
            $table->string('causa');
            $table->string('responsable')->nullable();
            $table->tinyInteger('criticidad')->default(1);
            $table->text('acciones')->nullable();
            $table->text('observations')->nullable();
            $table->text('notas')->nullable();
            $table->datetime('disqualification_date');
            $table->datetime('enablement_date')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('incidents');
    }
}
