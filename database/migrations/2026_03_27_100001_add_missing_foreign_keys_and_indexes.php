<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- failures.area_id ---
        if (Schema::hasTable('failures') && Schema::hasColumn('failures', 'area_id')) {
            Schema::table('failures', function (Blueprint $table) {
                if (! $this->hasIndex('failures', 'failures_area_id_index')) {
                    $table->index('area_id');
                }
                if (
                    ! $this->hasForeignKey('failures', 'failures_area_id_foreign')
                    && $this->canCreateForeignKey('failures', 'area_id', 'areas', 'id')
                ) {
                    $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
                }
            });
        }

        // --- assignments.product_id ---
        if (Schema::hasColumn('assignments', 'product_id')) {
            Schema::table('assignments', function (Blueprint $table) {
                if (! $this->hasIndex('assignments', 'assignments_product_id_index')) {
                    $table->index('product_id');
                }
                if (
                    ! $this->hasForeignKey('assignments', 'assignments_product_id_foreign')
                    && $this->canCreateForeignKey('assignments', 'product_id', 'products', 'id')
                ) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
            });
        }

        // --- products.employee_id ---
        if (Schema::hasColumn('products', 'employee_id')) {
            Schema::table('products', function (Blueprint $table) {
                if (! $this->hasIndex('products', 'products_employee_id_index')) {
                    $table->index('employee_id');
                }
                if (
                    ! $this->hasForeignKey('products', 'products_employee_id_foreign')
                    && $this->canCreateForeignKey('products', 'employee_id', 'users', 'id')
                ) {
                    $table->foreign('employee_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // --- sessions.user_id ---
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (
                    ! $this->hasForeignKey('sessions', 'sessions_user_id_foreign')
                    && $this->canCreateForeignKey('sessions', 'user_id', 'users', 'id')
                ) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // --- chats: sender_userid, reciever_userid ---
        if (Schema::hasTable('chats')) {
            Schema::table('chats', function (Blueprint $table) {
                if (Schema::hasColumn('chats', 'sender_userid') && ! $this->hasIndex('chats', 'chats_sender_userid_index')) {
                    $table->index('sender_userid');
                }
                if (Schema::hasColumn('chats', 'reciever_userid') && ! $this->hasIndex('chats', 'chats_reciever_userid_index')) {
                    $table->index('reciever_userid');
                }
            });
        }

        // --- inv_assets: sede_id, ubicacion_id, current_user_id ---
        if (Schema::hasTable('inv_assets')) {
            Schema::table('inv_assets', function (Blueprint $table) {
                if (Schema::hasColumn('inv_assets', 'sede_id') && ! $this->hasIndex('inv_assets', 'inv_assets_sede_id_index')) {
                    $table->index('sede_id');
                }
                if (Schema::hasColumn('inv_assets', 'ubicacion_id') && ! $this->hasIndex('inv_assets', 'inv_assets_ubicacion_id_index')) {
                    $table->index('ubicacion_id');
                }
            });
        }

        // --- inv_movements: user_id, admin_id ---
        if (Schema::hasTable('inv_movements')) {
            Schema::table('inv_movements', function (Blueprint $table) {
                if (Schema::hasColumn('inv_movements', 'user_id') && ! $this->hasIndex('inv_movements', 'inv_movements_user_id_index')) {
                    $table->index('user_id');
                }
                if (Schema::hasColumn('inv_movements', 'admin_id') && ! $this->hasIndex('inv_movements', 'inv_movements_admin_id_index')) {
                    $table->index('admin_id');
                }
                if (
                    Schema::hasColumn('inv_movements', 'user_id')
                    && ! $this->hasForeignKey('inv_movements', 'inv_movements_user_id_foreign')
                    && $this->canCreateForeignKey('inv_movements', 'user_id', 'users', 'id')
                ) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                }
                if (
                    Schema::hasColumn('inv_movements', 'admin_id')
                    && ! $this->hasForeignKey('inv_movements', 'inv_movements_admin_id_foreign')
                    && $this->canCreateForeignKey('inv_movements', 'admin_id', 'users', 'id')
                ) {
                    $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }

        // --- inv_maintenances: supplier_id, logged_by ---
        if (Schema::hasTable('inv_maintenances')) {
            Schema::table('inv_maintenances', function (Blueprint $table) {
                if (Schema::hasColumn('inv_maintenances', 'supplier_id') && ! $this->hasIndex('inv_maintenances', 'inv_maintenances_supplier_id_index')) {
                    $table->index('supplier_id');
                }
                if (Schema::hasColumn('inv_maintenances', 'logged_by') && ! $this->hasIndex('inv_maintenances', 'inv_maintenances_logged_by_index')) {
                    $table->index('logged_by');
                }
                if (
                    Schema::hasColumn('inv_maintenances', 'logged_by')
                    && ! $this->hasForeignKey('inv_maintenances', 'inv_maintenances_logged_by_foreign')
                    && $this->canCreateForeignKey('inv_maintenances', 'logged_by', 'users', 'id')
                ) {
                    $table->foreign('logged_by')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inv_maintenances')) {
            Schema::table('inv_maintenances', function (Blueprint $table) {
                if ($this->hasForeignKey('inv_maintenances', 'inv_maintenances_logged_by_foreign')) {
                    $table->dropForeign('inv_maintenances_logged_by_foreign');
                }
                if ($this->hasIndex('inv_maintenances', 'inv_maintenances_logged_by_index')) {
                    $table->dropIndex('inv_maintenances_logged_by_index');
                }
                if ($this->hasIndex('inv_maintenances', 'inv_maintenances_supplier_id_index')) {
                    $table->dropIndex('inv_maintenances_supplier_id_index');
                }
            });
        }

        if (Schema::hasTable('inv_movements')) {
            Schema::table('inv_movements', function (Blueprint $table) {
                if ($this->hasForeignKey('inv_movements', 'inv_movements_admin_id_foreign')) {
                    $table->dropForeign('inv_movements_admin_id_foreign');
                }
                if ($this->hasForeignKey('inv_movements', 'inv_movements_user_id_foreign')) {
                    $table->dropForeign('inv_movements_user_id_foreign');
                }
                if ($this->hasIndex('inv_movements', 'inv_movements_admin_id_index')) {
                    $table->dropIndex('inv_movements_admin_id_index');
                }
                if ($this->hasIndex('inv_movements', 'inv_movements_user_id_index')) {
                    $table->dropIndex('inv_movements_user_id_index');
                }
            });
        }

        if (Schema::hasTable('inv_assets')) {
            Schema::table('inv_assets', function (Blueprint $table) {
                if ($this->hasIndex('inv_assets', 'inv_assets_ubicacion_id_index')) {
                    $table->dropIndex('inv_assets_ubicacion_id_index');
                }
                if ($this->hasIndex('inv_assets', 'inv_assets_sede_id_index')) {
                    $table->dropIndex('inv_assets_sede_id_index');
                }
            });
        }

        if (Schema::hasTable('chats')) {
            Schema::table('chats', function (Blueprint $table) {
                if ($this->hasIndex('chats', 'chats_reciever_userid_index')) {
                    $table->dropIndex('chats_reciever_userid_index');
                }
                if ($this->hasIndex('chats', 'chats_sender_userid_index')) {
                    $table->dropIndex('chats_sender_userid_index');
                }
            });
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                if ($this->hasForeignKey('sessions', 'sessions_user_id_foreign')) {
                    $table->dropForeign('sessions_user_id_foreign');
                }
            });
        }

        if (Schema::hasColumn('products', 'employee_id')) {
            Schema::table('products', function (Blueprint $table) {
                if ($this->hasForeignKey('products', 'products_employee_id_foreign')) {
                    $table->dropForeign('products_employee_id_foreign');
                }
                if ($this->hasIndex('products', 'products_employee_id_index')) {
                    $table->dropIndex('products_employee_id_index');
                }
            });
        }

        if (Schema::hasColumn('assignments', 'product_id')) {
            Schema::table('assignments', function (Blueprint $table) {
                if ($this->hasForeignKey('assignments', 'assignments_product_id_foreign')) {
                    $table->dropForeign('assignments_product_id_foreign');
                }
                if ($this->hasIndex('assignments', 'assignments_product_id_index')) {
                    $table->dropIndex('assignments_product_id_index');
                }
            });
        }

        if (Schema::hasTable('failures') && Schema::hasColumn('failures', 'area_id')) {
            Schema::table('failures', function (Blueprint $table) {
                if ($this->hasForeignKey('failures', 'failures_area_id_foreign')) {
                    $table->dropForeign('failures_area_id_foreign');
                }
                if ($this->hasIndex('failures', 'failures_area_id_index')) {
                    $table->dropIndex('failures_area_id_index');
                }
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return ! empty($rows);
    }

    private function hasForeignKey(string $table, string $foreignName): bool
    {
        $rows = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'
               AND CONSTRAINT_NAME = ?",
            [$table, $foreignName]
        );

        return ! empty($rows);
    }

    private function canCreateForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn
    ): bool {
        if (
            ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, $column)
            || ! Schema::hasTable($referencedTable)
            || ! Schema::hasColumn($referencedTable, $referencedColumn)
        ) {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM `{$table}` t
                LEFT JOIN `{$referencedTable}` r ON r.`{$referencedColumn}` = t.`{$column}`
                WHERE t.`{$column}` IS NOT NULL
                  AND r.`{$referencedColumn}` IS NULL";
        $rows = DB::select($sql);
        $invalid = (int) ($rows[0]->total ?? 0);

        return $invalid === 0;
    }
};
