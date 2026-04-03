<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tras mover User a App\Models\User, Spatie Permission y activity_log siguen
 * guardando model_type / subject_type como "App\User". Hay que actualizarlos
 * o el usuario pierde roles y permisos.
 */
class UpdateMorphModelTypeAppUserToAppModelsUser extends Migration
{
    private const OLD = 'App\\User';
    private const NEW = 'App\\Models\\User';

    public function up()
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_type', self::OLD)
                ->update(['model_type' => self::NEW]);
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', self::OLD)
                ->update(['model_type' => self::NEW]);
        }

        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')
                ->where('subject_type', self::OLD)
                ->update(['subject_type' => self::NEW]);
            DB::table('activity_log')
                ->where('causer_type', self::OLD)
                ->update(['causer_type' => self::NEW]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')
                ->where('model_type', self::NEW)
                ->update(['model_type' => self::OLD]);
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', self::NEW)
                ->update(['model_type' => self::OLD]);
        }

        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')
                ->where('subject_type', self::NEW)
                ->update(['subject_type' => self::OLD]);
            DB::table('activity_log')
                ->where('causer_type', self::NEW)
                ->update(['causer_type' => self::OLD]);
        }
    }
}
