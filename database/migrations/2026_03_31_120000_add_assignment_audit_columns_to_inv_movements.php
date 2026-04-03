<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('inv_movements', 'previous_user_id')) {
                $table->unsignedBigInteger('previous_user_id')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('inv_movements', 'batch_uuid')) {
                $table->uuid('batch_uuid')->nullable()->after('reason');
            }
            if (! Schema::hasColumn('inv_movements', 'metadata')) {
                $table->json('metadata')->nullable()->after('batch_uuid');
            }
        });

        if (! Schema::hasColumn('inv_movements', 'batch_uuid')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $needsIndex = true;
        if ($driver === 'mysql') {
            $rows = DB::select('SHOW INDEX FROM `inv_movements` WHERE Key_name = ?', ['inv_movements_batch_uuid_index']);
            $needsIndex = empty($rows);
        }
        if ($needsIndex) {
            Schema::table('inv_movements', function (Blueprint $table) {
                $table->index('batch_uuid', 'inv_movements_batch_uuid_index');
            });
        }
    }

    public function down(): void
    {
        try {
            Schema::table('inv_movements', function (Blueprint $table) {
                $table->dropIndex('inv_movements_batch_uuid_index');
            });
        } catch (\Throwable) {
            // índice inexistente u otro driver
        }

        Schema::table('inv_movements', function (Blueprint $table) {
            if (Schema::hasColumn('inv_movements', 'metadata')) {
                $table->dropColumn('metadata');
            }
            if (Schema::hasColumn('inv_movements', 'batch_uuid')) {
                $table->dropColumn('batch_uuid');
            }
            if (Schema::hasColumn('inv_movements', 'previous_user_id')) {
                $table->dropColumn('previous_user_id');
            }
        });
    }
};
