<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginAssetIdToComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('components', function (Blueprint $table) {
            $table->unsignedBigInteger('origin_asset_id')
                  ->nullable()
                  ->after('asset_id')
                  ->comment('Activo del que fue extraído este componente (inmutable)');

            $table->foreign('origin_asset_id')
                  ->references('id')
                  ->on('inv_assets')
                  ->onDelete('set null');

            $table->index('origin_asset_id');
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
            $table->dropForeign(['origin_asset_id']);
            $table->dropIndex(['origin_asset_id']);
            $table->dropColumn('origin_asset_id');
        });
    }
}

