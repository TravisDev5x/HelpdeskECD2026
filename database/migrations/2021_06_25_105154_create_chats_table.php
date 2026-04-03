<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
                $table->id();
                $table->BigInteger('sender_userid');
                $table->BigInteger('reciever_userid');
                $table->unsignedBigInteger('department_id');
                $table->text('message')->nullable();
                $table->tinyInteger('status')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('department_id')->references('id')->on('departments');
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
        Schema::dropIfExists('chats');
    }
}
