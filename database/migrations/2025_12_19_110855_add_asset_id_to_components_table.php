<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetIdToComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function (Blueprint $table) {
       
            $table->unsignedBigInteger('asset_id')->nullable()->after('producto_id');
            $table->foreign('asset_id')->references('id')->on('inv_assets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('components', function (Blueprint $table) {
            
            $table->dropForeign(['asset_id']);
            $table->dropColumn('asset_id');
        });
    }
}

