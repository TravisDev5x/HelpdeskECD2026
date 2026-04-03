<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2 — Alinear esquema `users` con el modelo y la app (columnas idempotentes).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'ap_paterno')) {
                $table->string('ap_paterno')->nullable();
            }
            if (! Schema::hasColumn('users', 'ap_materno')) {
                $table->string('ap_materno')->nullable();
            }
            if (! Schema::hasColumn('users', 'sede')) {
                $table->string('sede')->nullable();
            }
            if (! Schema::hasColumn('users', 'password_expires_at')) {
                $table->timestamp('password_expires_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'fecha_baja')) {
                $table->date('fecha_baja')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'fecha_baja')) {
                $table->dropColumn('fecha_baja');
            }
            if (Schema::hasColumn('users', 'password_expires_at')) {
                $table->dropColumn('password_expires_at');
            }
            if (Schema::hasColumn('users', 'sede')) {
                $table->dropColumn('sede');
            }
            if (Schema::hasColumn('users', 'ap_materno')) {
                $table->dropColumn('ap_materno');
            }
            if (Schema::hasColumn('users', 'ap_paterno')) {
                $table->dropColumn('ap_paterno');
            }
        });
    }
};
