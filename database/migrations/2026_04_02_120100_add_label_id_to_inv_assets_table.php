<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_assets')) {
            return;
        }

        Schema::table('inv_assets', function (Blueprint $table) {
            if (! Schema::hasColumn('inv_assets', 'label_id')) {
                $table->unsignedBigInteger('label_id')->nullable()->after('status_id');
            }
        });

        if (! Schema::hasColumn('inv_assets', 'label_id')) {
            return;
        }

        Schema::table('inv_assets', function (Blueprint $table) {
            if (! $this->hasIndex('inv_assets', 'inv_assets_label_id_index')) {
                $table->index('label_id', 'inv_assets_label_id_index');
            }
            if (! $this->hasForeign('inv_assets', 'inv_assets_label_id_foreign')) {
                $table->foreign('label_id', 'inv_assets_label_id_foreign')
                    ->references('id')
                    ->on('inv_labels')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inv_assets')) {
            return;
        }

        Schema::table('inv_assets', function (Blueprint $table) {
            if ($this->hasForeign('inv_assets', 'inv_assets_label_id_foreign')) {
                $table->dropForeign('inv_assets_label_id_foreign');
            }
            if ($this->hasIndex('inv_assets', 'inv_assets_label_id_index')) {
                $table->dropIndex('inv_assets_label_id_index');
            }
            if (Schema::hasColumn('inv_assets', 'label_id')) {
                $table->dropColumn('label_id');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return false;
        }

        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return ! empty($rows);
    }

    private function hasForeign(string $table, string $constraintName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return false;
        }

        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ? AND CONSTRAINT_NAME = ?',
            [$table, 'FOREIGN KEY', $constraintName]
        );

        return ! empty($rows);
    }
};
