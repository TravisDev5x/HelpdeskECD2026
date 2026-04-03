<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // inv_categories.name — evitar categorías duplicadas
        if (Schema::hasTable('inv_categories') && Schema::hasColumn('inv_categories', 'name')) {
            Schema::table('inv_categories', function (Blueprint $table) {
                if (
                    ! $this->hasIndex('inv_categories', 'inv_categories_name_unique')
                    && ! $this->hasDuplicates('inv_categories', ['name'])
                ) {
                    $table->unique('name');
                }
            });
        }

        // inv_statuses.name — evitar estatus duplicados
        if (Schema::hasTable('inv_statuses') && Schema::hasColumn('inv_statuses', 'name')) {
            Schema::table('inv_statuses', function (Blueprint $table) {
                if (
                    ! $this->hasIndex('inv_statuses', 'inv_statuses_name_unique')
                    && ! $this->hasDuplicates('inv_statuses', ['name'])
                ) {
                    $table->unique('name');
                }
            });
        }

        // asset_users (user_id, asset_id) — evitar filas duplicadas en el pivot
        if (
            Schema::hasTable('asset_users')
            && Schema::hasColumn('asset_users', 'user_id')
            && Schema::hasColumn('asset_users', 'asset_id')
        ) {
            Schema::table('asset_users', function (Blueprint $table) {
                if (
                    ! $this->hasIndex('asset_users', 'asset_users_user_id_asset_id_unique')
                    && ! $this->hasDuplicates('asset_users', ['user_id', 'asset_id'])
                ) {
                    $table->unique(['user_id', 'asset_id']);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('asset_users')) {
            Schema::table('asset_users', function (Blueprint $table) {
                if ($this->hasIndex('asset_users', 'asset_users_user_id_asset_id_unique')) {
                    $table->dropUnique('asset_users_user_id_asset_id_unique');
                }
            });
        }

        if (Schema::hasTable('inv_statuses')) {
            Schema::table('inv_statuses', function (Blueprint $table) {
                if ($this->hasIndex('inv_statuses', 'inv_statuses_name_unique')) {
                    $table->dropUnique('inv_statuses_name_unique');
                }
            });
        }

        if (Schema::hasTable('inv_categories')) {
            Schema::table('inv_categories', function (Blueprint $table) {
                if ($this->hasIndex('inv_categories', 'inv_categories_name_unique')) {
                    $table->dropUnique('inv_categories_name_unique');
                }
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return ! empty($rows);
    }

    private function hasDuplicates(string $table, array $columns): bool
    {
        $columnsSql = implode(', ', array_map(static fn ($c) => "`{$c}`", $columns));
        $notNullSql = implode(' AND ', array_map(static fn ($c) => "`{$c}` IS NOT NULL", $columns));
        $sql = "SELECT 1
                FROM `{$table}`
                WHERE {$notNullSql}
                GROUP BY {$columnsSql}
                HAVING COUNT(*) > 1
                LIMIT 1";
        $rows = DB::select($sql);

        return ! empty($rows);
    }
};
