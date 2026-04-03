<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaintenanceDatesToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
          $table->text('maintenance')->after('fecha_ingreso')->nullable();
          $table->date('maintenance_date')->after('maintenance')->nullable();
          $table->date('last_maintenance_date')->after('maintenance_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
          $table->dropColumn('maintenance');
          $table->dropColumn('maintenance_date');
          $table->dropColumn('last_maintenance_date');
        });
    }
}
