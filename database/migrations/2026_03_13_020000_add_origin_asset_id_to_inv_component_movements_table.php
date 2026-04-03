<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginAssetIdToInvComponentMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inv_component_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('origin_asset_id')
                  ->nullable()
                  ->after('asset_id');

            $table->foreign('origin_asset_id')
                  ->references('id')
                  ->on('inv_assets')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inv_component_movements', function (Blueprint $table) {
            $table->dropForeign(['origin_asset_id']);
            $table->dropColumn('origin_asset_id');
        });
    }
}

