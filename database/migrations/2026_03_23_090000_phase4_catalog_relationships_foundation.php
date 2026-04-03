<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureSedesTable();
        $this->ensureNullableUserCatalogColumns();
        $this->sanitizeUserForeignKeyValues();
        $this->ensureUsersAreaRelation();
        $this->ensureCatalogHierarchy();
        $this->ensureUserSedesPivot();
        $this->migrateLegacyUserSedeToPivot();
    }

    public function down(): void
    {
        $this->dropForeignIfExists('users', 'fk_users_area_id');
        $this->dropForeignIfExists('users', 'fk_users_department_id');
        $this->dropForeignIfExists('users', 'fk_users_position_id');
        $this->dropForeignIfExists('users', 'fk_users_campaign_id');

        $this->dropForeignIfExists('departments', 'fk_departments_area_id');
        $this->dropForeignIfExists('positions', 'fk_positions_department_id');
        $this->dropForeignIfExists('campaigns', 'fk_campaigns_area_id');
        $this->dropForeignIfExists('campaigns', 'fk_campaigns_sede_id');

        if (Schema::hasTable('sede_user')) {
            $this->dropForeignIfExists('sede_user', 'fk_sede_user_user_id');
            $this->dropForeignIfExists('sede_user', 'fk_sede_user_sede_id');
            Schema::dropIfExists('sede_user');
        }

        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) {
                if (Schema::hasColumn('campaigns', 'area_id')) {
                    $table->dropColumn('area_id');
                }
                if (Schema::hasColumn('campaigns', 'sede_id')) {
                    $table->dropColumn('sede_id');
                }
            });
        }

        if (Schema::hasTable('positions') && Schema::hasColumn('positions', 'department_id')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->dropColumn('department_id');
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'area_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('area_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'area_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('area_id');
            });
        }
    }

    private function ensureSedesTable(): void
    {
        if (Schema::hasTable('sedes')) {
            return;
        }

        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('sede')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureUsersAreaRelation(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('campaign_id');
                $table->index('area_id', 'idx_users_area_id');
            }
        });

        if (Schema::hasTable('areas')) {
            $this->addForeignIfMissing(
                table: 'users',
                column: 'area_id',
                referencedTable: 'areas',
                referencedColumn: 'id',
                constraint: 'fk_users_area_id',
                onDelete: 'set null'
            );
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('users', 'department_id')) {
            $this->addForeignIfMissing(
                table: 'users',
                column: 'department_id',
                referencedTable: 'departments',
                referencedColumn: 'id',
                constraint: 'fk_users_department_id',
                onDelete: 'restrict'
            );
        }

        if (Schema::hasTable('positions') && Schema::hasColumn('users', 'position_id')) {
            $this->addForeignIfMissing(
                table: 'users',
                column: 'position_id',
                referencedTable: 'positions',
                referencedColumn: 'id',
                constraint: 'fk_users_position_id',
                onDelete: 'restrict'
            );
        }

        if (Schema::hasTable('campaigns') && Schema::hasColumn('users', 'campaign_id')) {
            $this->addForeignIfMissing(
                table: 'users',
                column: 'campaign_id',
                referencedTable: 'campaigns',
                referencedColumn: 'id',
                constraint: 'fk_users_campaign_id',
                onDelete: 'set null'
            );
        }
    }

    private function ensureNullableUserCatalogColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Permite usar NULL para catálogos opcionales o registros heredados huérfanos.
        if (Schema::hasColumn('users', 'department_id')) {
            DB::statement('ALTER TABLE users MODIFY department_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('users', 'position_id')) {
            DB::statement('ALTER TABLE users MODIFY position_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('users', 'campaign_id')) {
            DB::statement('ALTER TABLE users MODIFY campaign_id BIGINT UNSIGNED NULL');
        }
    }

    private function sanitizeUserForeignKeyValues(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (Schema::hasColumn('users', 'department_id') && Schema::hasTable('departments')) {
            DB::table('users')
                ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                ->whereNotNull('users.department_id')
                ->whereNull('departments.id')
                ->update(['users.department_id' => null]);
        }

        if (Schema::hasColumn('users', 'position_id') && Schema::hasTable('positions')) {
            DB::table('users')
                ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                ->whereNotNull('users.position_id')
                ->whereNull('positions.id')
                ->update(['users.position_id' => null]);
        }

        if (Schema::hasColumn('users', 'campaign_id') && Schema::hasTable('campaigns')) {
            DB::table('users')
                ->leftJoin('campaigns', 'users.campaign_id', '=', 'campaigns.id')
                ->whereNotNull('users.campaign_id')
                ->whereNull('campaigns.id')
                ->update(['users.campaign_id' => null]);
        }

        if (Schema::hasColumn('users', 'area_id') && Schema::hasTable('areas')) {
            DB::table('users')
                ->leftJoin('areas', 'users.area_id', '=', 'areas.id')
                ->whereNotNull('users.area_id')
                ->whereNull('areas.id')
                ->update(['users.area_id' => null]);
        }
    }

    private function ensureCatalogHierarchy(): void
    {
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (! Schema::hasColumn('departments', 'area_id')) {
                    $table->unsignedBigInteger('area_id')->nullable()->after('name');
                    $table->index('area_id', 'idx_departments_area_id');
                }
            });

            if (Schema::hasTable('areas')) {
                $this->addForeignIfMissing(
                    table: 'departments',
                    column: 'area_id',
                    referencedTable: 'areas',
                    referencedColumn: 'id',
                    constraint: 'fk_departments_area_id',
                    onDelete: 'set null'
                );
            }
        }

        if (Schema::hasTable('positions')) {
            Schema::table('positions', function (Blueprint $table) {
                if (! Schema::hasColumn('positions', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('name');
                    $table->index('department_id', 'idx_positions_department_id');
                }
            });

            if (Schema::hasTable('departments')) {
                $this->addForeignIfMissing(
                    table: 'positions',
                    column: 'department_id',
                    referencedTable: 'departments',
                    referencedColumn: 'id',
                    constraint: 'fk_positions_department_id',
                    onDelete: 'set null'
                );
            }
        }

        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) {
                if (! Schema::hasColumn('campaigns', 'area_id')) {
                    $table->unsignedBigInteger('area_id')->nullable()->after('name');
                    $table->index('area_id', 'idx_campaigns_area_id');
                }
                if (! Schema::hasColumn('campaigns', 'sede_id')) {
                    $table->unsignedBigInteger('sede_id')->nullable()->after('area_id');
                    $table->index('sede_id', 'idx_campaigns_sede_id');
                }
            });

            if (Schema::hasTable('areas')) {
                $this->addForeignIfMissing(
                    table: 'campaigns',
                    column: 'area_id',
                    referencedTable: 'areas',
                    referencedColumn: 'id',
                    constraint: 'fk_campaigns_area_id',
                    onDelete: 'set null'
                );
            }

            if (Schema::hasTable('sedes')) {
                $this->addForeignIfMissing(
                    table: 'campaigns',
                    column: 'sede_id',
                    referencedTable: 'sedes',
                    referencedColumn: 'id',
                    constraint: 'fk_campaigns_sede_id',
                    onDelete: 'set null'
                );
            }
        }
    }

    private function ensureUserSedesPivot(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('sedes') || Schema::hasTable('sede_user')) {
            return;
        }

        Schema::create('sede_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sede_id');
            $table->timestamps();

            $table->unique(['user_id', 'sede_id'], 'uq_sede_user_user_sede');
            $table->index('user_id', 'idx_sede_user_user_id');
            $table->index('sede_id', 'idx_sede_user_sede_id');
        });

        $this->addForeignIfMissing(
            table: 'sede_user',
            column: 'user_id',
            referencedTable: 'users',
            referencedColumn: 'id',
            constraint: 'fk_sede_user_user_id',
            onDelete: 'cascade'
        );

        $this->addForeignIfMissing(
            table: 'sede_user',
            column: 'sede_id',
            referencedTable: 'sedes',
            referencedColumn: 'id',
            constraint: 'fk_sede_user_sede_id',
            onDelete: 'cascade'
        );
    }

    private function migrateLegacyUserSedeToPivot(): void
    {
        if (
            ! Schema::hasTable('users') ||
            ! Schema::hasTable('sedes') ||
            ! Schema::hasTable('sede_user') ||
            ! Schema::hasColumn('users', 'sede')
        ) {
            return;
        }

        DB::statement("
            INSERT IGNORE INTO sede_user (user_id, sede_id, created_at, updated_at)
            SELECT u.id, s.id, NOW(), NOW()
            FROM users u
            INNER JOIN sedes s ON TRIM(u.sede) = TRIM(s.sede)
            WHERE u.sede IS NOT NULL
              AND TRIM(u.sede) <> ''
        ");
    }

    private function addForeignIfMissing(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $constraint,
        string $onDelete = 'restrict'
    ): void {
        if (
            ! Schema::hasTable($table) ||
            ! Schema::hasTable($referencedTable) ||
            ! Schema::hasColumn($table, $column) ||
            $this->foreignKeyExists($table, $constraint)
        ) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use (
            $column,
            $referencedTable,
            $referencedColumn,
            $constraint,
            $onDelete
        ) {
            $foreign = $tableBlueprint
                ->foreign($column, $constraint)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->onUpdate('cascade');

            match ($onDelete) {
                'set null' => $foreign->nullOnDelete(),
                'cascade' => $foreign->cascadeOnDelete(),
                default => $foreign->restrictOnDelete(),
            };
        });
    }

    private function dropForeignIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table) || ! $this->foreignKeyExists($table, $constraint)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($constraint) {
            $tableBlueprint->dropForeign($constraint);
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = "FOREIGN KEY"
               AND CONSTRAINT_NAME = ?
             LIMIT 1',
            [$database, $table, $constraint]
        );

        return $result !== null;
    }
};
