<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('name');
            $table->string('usuario')->unique();
            $table->string('phone', '10')->nullable();
            $table->string('email')->unique()->nullable();
            $table->boolean('certification')->default(0);
            $table->string('motivo_baja')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('avatar')->default('default.png');
            $table->string('password');
            $table->rememberToken();
            $table->dateTime('acepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
