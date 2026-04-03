<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('calendars')) {
            return;
        }

        Schema::table('calendars', function (Blueprint $table) {
            if (! Schema::hasColumn('calendars', 'scope')) {
                $table->string('scope', 20)->default('personal')->after('user_id');
            }
            if (! Schema::hasColumn('calendars', 'start_date')) {
                $table->date('start_date')->nullable()->after('descripcion');
            }
            if (! Schema::hasColumn('calendars', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('calendars', 'status')) {
                $table->string('status', 32)->nullable()->after('end_date');
            }
        });

        if (Schema::hasColumn('calendars', 'scope')) {
            DB::table('calendars')->whereNull('scope')->update(['scope' => 'personal']);
        }

        if (Schema::hasColumn('calendars', 'date') && Schema::hasColumn('calendars', 'start_date')) {
            DB::table('calendars')
                ->whereNull('start_date')
                ->whereNotNull('date')
                ->update(['start_date' => DB::raw('`date`')]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('calendars')) {
            return;
        }

        Schema::table('calendars', function (Blueprint $table) {
            if (Schema::hasColumn('calendars', 'scope')) {
                $table->dropColumn('scope');
            }
        });
    }
};
