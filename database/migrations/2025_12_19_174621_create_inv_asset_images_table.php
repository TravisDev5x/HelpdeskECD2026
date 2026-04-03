<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvAssetImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('inv_asset_images', function (Blueprint $table) {
        $table->id();
        $table->foreignId('inv_asset_id')->constrained('inv_assets')->onDelete('cascade');
        $table->string('path');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inv_asset_images');
    }
}
