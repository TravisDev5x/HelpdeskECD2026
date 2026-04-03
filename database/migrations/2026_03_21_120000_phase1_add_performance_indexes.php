<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1 — Índices de rendimiento (añadir columnas indexadas; no altera resultados de consultas).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'sessions', 'user_id', 'idx_sessions_user_id');
                $this->addIndexIfMissing($table, 'sessions', 'last_activity', 'idx_sessions_last_activity');
            });
        }

        if (Schema::hasTable('authentication_logs')) {
            Schema::table('authentication_logs', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'authentication_logs', ['user_id', 'login_at'], 'idx_auth_logs_user_login_at');
            });
        }

        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'services', 'status', 'idx_services_status');
                $this->addIndexIfMissing($table, 'services', ['status', 'created_at'], 'idx_services_status_created_at');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'owner')) {
                    $this->addIndexIfMissing($table, 'products', 'owner', 'idx_products_owner');
                }
                if (Schema::hasColumn('products', 'status')) {
                    $this->addIndexIfMissing($table, 'products', 'status', 'idx_products_status');
                }
                if (Schema::hasColumn('products', 'employee_id')) {
                    $this->addIndexIfMissing($table, 'products', 'employee_id', 'idx_products_employee_id');
                }
            });
        }

        if (Schema::hasTable('inv_assets')) {
            Schema::table('inv_assets', function (Blueprint $table) {
                if (Schema::hasColumn('inv_assets', 'current_user_id')) {
                    $this->addIndexIfMissing($table, 'inv_assets', 'current_user_id', 'idx_inv_assets_current_user_id');
                }
            });
        }

        if (Schema::hasTable('inv_movements')) {
            Schema::table('inv_movements', function (Blueprint $table) {
                if (Schema::hasColumn('inv_movements', 'date')) {
                    $this->addIndexIfMissing($table, 'inv_movements', 'date', 'idx_inv_movements_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'sessions', 'idx_sessions_user_id');
                $this->dropIndexIfExists($table, 'sessions', 'idx_sessions_last_activity');
            });
        }

        if (Schema::hasTable('authentication_logs')) {
            Schema::table('authentication_logs', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'authentication_logs', 'idx_auth_logs_user_login_at');
            });
        }

        if (Schema::hasTable('services')) {
            Schema::table('services', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'services', 'idx_services_status');
                $this->dropIndexIfExists($table, 'services', 'idx_services_status_created_at');
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'products', 'idx_products_owner');
                $this->dropIndexIfExists($table, 'products', 'idx_products_status');
                $this->dropIndexIfExists($table, 'products', 'idx_products_employee_id');
            });
        }

        if (Schema::hasTable('inv_assets')) {
            Schema::table('inv_assets', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'inv_assets', 'idx_inv_assets_current_user_id');
            });
        }

        if (Schema::hasTable('inv_movements')) {
            Schema::table('inv_movements', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'inv_movements', 'idx_inv_movements_date');
            });
        }
    }

    private function addIndexIfMissing(Blueprint $table, string $tableName, string|array $columns, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }
        $table->index($columns, $indexName);
    }

    private function dropIndexIfExists(Blueprint $table, string $tableName, string $indexName): void
    {
        if (! $this->indexExists($tableName, $indexName)) {
            return;
        }
        $table->dropIndex($indexName);
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $tableName, $indexName]
        );

        return isset($result->c) && (int) $result->c > 0;
    }
};
