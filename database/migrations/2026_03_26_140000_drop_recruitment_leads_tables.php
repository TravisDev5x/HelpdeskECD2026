<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('historical_leads');
        Schema::dropIfExists('leads');
    }

    public function down(): void
    {
        // Eliminación definitiva del módulo legado de reclutamiento.
        // No se recrean tablas porque la estructura original no está versionada en migraciones.
    }
};

