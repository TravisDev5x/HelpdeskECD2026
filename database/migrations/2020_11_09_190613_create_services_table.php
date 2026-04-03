<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->unsignedBigInteger('failure_id');
            $table->text('description');
            $table->text('solution')->nullable();
            $table->text('observations')->nullable();
            $table->string('status')->nullable();
            $table->datetime('fecha_fin')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('responsable_id')->references('id')->on('users');
            $table->foreign('failure_id')->references('id')->on('failures');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
