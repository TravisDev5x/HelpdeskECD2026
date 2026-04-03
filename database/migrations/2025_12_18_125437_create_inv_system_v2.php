<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvSystemV2 extends Migration
{
    public function up()
    {
        // 1. CATÁLOGO DE CATEGORÍAS
        // Ejemplo: Laptop, Monitor, Vehículo, Licencia Software
        Schema::create('inv_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('type')->default('HARDWARE'); // HARDWARE, SOFTWARE, CONSUMIBLE
            $table->string('prefix')->nullable(); // Ej: 'NB' para generar etiquetas NB-001
            $table->boolean('require_specs')->default(true); // ¿Pide CPU/RAM?
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. CATÁLOGO DE ESTADOS (Status)
        // Ejemplo: DISPONIBLE (Verde), ASIGNADO (Azul), TALLER (Amarillo), BAJA (Rojo), DESMANTELADO (Negro)
        Schema::create('inv_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('badge_class')->default('secondary'); // adminlte: success, warning, danger
            $table->boolean('assignable')->default(true); // ¿Se puede entregar a un usuario?
            $table->timestamps();
        });

        // 3. TABLA MAESTRA DE ACTIVOS (Assets)
        Schema::create('inv_assets', function (Blueprint $table) {
            $table->id();
            
            // --- IDENTIFICACIÓN ---
            $table->string('uuid')->unique()->nullable(); // Identificador Universal (QR)
            $table->string('internal_tag')->unique()->nullable(); // Tu etiqueta física (Activo Fijo)
            $table->string('serial')->nullable(); // Serie del fabricante
            $table->string('name'); // Ej: Dell Latitude 5420
            
            // --- CLASIFICACIÓN ---
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('status_id');
            
            // --- CONDICIÓN FÍSICA (Para equipos viejos/reciclados) ---
            // NUEVO: Caja sellada
            // BUENO: Uso normal
            // REGULAR: Detalles estéticos pero funcional (Equipos viejos)
            // MALO: Funciona con fallas
            // PARA_PIEZAS: Desmantelado/Reciclaje
            $table->enum('condition', ['NUEVO', 'BUENO', 'REGULAR', 'MALO', 'PARA_PIEZAS'])->default('BUENO');

            // --- UBICACIÓN ---
            // Usamos tus tablas existentes de AdminLTE
            $table->unsignedBigInteger('sede_id')->nullable(); 
            $table->unsignedBigInteger('ubicacion_id')->nullable();
            
            // --- ESPECIFICACIONES (JSON) ---
            // Aquí guardamos: { "ram": "16GB", "disco": "512SSD", "mac": "AA:BB:CC...", "ip": "10.0.0.5" }
            // Esto elimina las columnas vacías en la BD.
            $table->json('specs')->nullable(); 
            
            // --- FINANCIERO ---
            $table->decimal('cost', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable(); // Alerta de garantía
            $table->string('supplier')->nullable(); 
            $table->string('invoice_number')->nullable(); // Factura

            // --- CACHÉ DE ASIGNACIÓN ---
            // Solo para saber quién lo tiene AHORA. El historial real está en 'movements'.
            $table->unsignedBigInteger('current_user_id')->nullable(); 
            
            // --- MULTIMEDIA ---
            $table->string('image_path')->nullable(); // Foto del equipo
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes(); // ¡Nunca borramos nada físicamente!

            // Relaciones
            $table->foreign('category_id')->references('id')->on('inv_categories');
            $table->foreign('status_id')->references('id')->on('inv_statuses');
        });

        // 4. BITÁCORA DE MOVIMIENTOS (Auditoría Inmutable)
        // Cada vez que asignas, recibes o das de baja, se crea una fila aquí.
        Schema::create('inv_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            
            // Tipos: 'CHECKOUT' (Entrega), 'CHECKIN' (Devolución), 'TRASLADO', 'MAINTENANCE', 'AUDIT'
            $table->string('type'); 
            
            // Actores
            $table->unsignedBigInteger('user_id')->nullable(); // Empleado afectado (quien recibe/entrega)
            $table->unsignedBigInteger('admin_id'); // Tú (quien opera el sistema)
            
            // Evidencia Legal
            $table->string('responsiva_path')->nullable(); // PDF Firmado de la entrega
            $table->text('notes')->nullable(); // "Se entrega con cargador y mouse"
            
            $table->timestamp('date')->useCurrent();
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('inv_assets');
        });

        // 5. MANTENIMIENTOS (Historial Técnico)
        Schema::create('inv_maintenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('supplier_id')->nullable(); // Proveedor externo o null si es interno
            
            $table->string('title'); // Ej: Cambio de Pantalla
            $table->text('diagnosis'); // Diagnóstico inicial
            $table->text('solution')->nullable(); // Qué se le hizo
            
            $table->decimal('cost', 10, 2)->default(0);
            
            // Archivos adjuntos (Fotos antes/después, Reporte Proveedor)
            $table->json('attachments')->nullable(); 
            
            $table->date('start_date');
            $table->date('end_date')->nullable();
            
            $table->unsignedBigInteger('logged_by'); // Quién registró el ticket

            $table->timestamps();
            $table->foreign('asset_id')->references('id')->on('inv_assets');
        });
    }

    public function down()
    {
        // Orden inverso para evitar errores de llaves foráneas
        Schema::dropIfExists('inv_maintenances');
        Schema::dropIfExists('inv_movements');
        Schema::dropIfExists('inv_assets');
        Schema::dropIfExists('inv_statuses');
        Schema::dropIfExists('inv_categories');
    }
}