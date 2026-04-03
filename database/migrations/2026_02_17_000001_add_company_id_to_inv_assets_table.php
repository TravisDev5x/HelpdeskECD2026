<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToInvAssetsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('inv_assets') && !Schema::hasColumn('inv_assets', 'company_id')) {
            Schema::table('inv_assets', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('status_id');
                if (Schema::hasTable('companies')) {
                    $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('inv_assets') && Schema::hasColumn('inv_assets', 'company_id')) {
            Schema::table('inv_assets', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
}
