<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'phone')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $indexName = 'users_phone_unique';

        if ($this->hasIndex('users', $indexName) || $this->hasDuplicatePhones()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($indexName) {
            $table->unique('phone', $indexName);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if ($this->hasIndex('users', 'users_phone_unique')) {
                $table->dropUnique('users_phone_unique');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return ! empty($rows);
    }

    private function hasDuplicatePhones(): bool
    {
        $rows = DB::select(
            'SELECT 1 FROM `users` WHERE `phone` IS NOT NULL AND `phone` != \'\' GROUP BY `phone` HAVING COUNT(*) > 1 LIMIT 1'
        );

        return ! empty($rows);
    }
};
