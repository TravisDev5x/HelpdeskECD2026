<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inv_import_batches')) {
            Schema::create('inv_import_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('file_name')->nullable();
                $table->string('mode')->default('upsert_by_serial');
                $table->json('defaults')->nullable();
                $table->json('summary')->nullable();
                $table->string('status', 32)->default('previewed');
                $table->timestamps();

                $table->index('status', 'inv_import_batches_status_idx');
            });
        }

        if (! Schema::hasTable('inv_import_rows')) {
            Schema::create('inv_import_rows', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedInteger('row_number');
                $table->json('payload');
                $table->json('parsed')->nullable();
                $table->json('errors')->nullable();
                $table->json('warnings')->nullable();
                $table->string('action', 16)->nullable();
                $table->string('status', 32)->default('pending');
                $table->unsignedBigInteger('processed_asset_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();

                $table->index(['batch_id', 'status'], 'inv_import_rows_batch_status_idx');
                $table->index('status', 'inv_import_rows_status_idx');
                $table->foreign('batch_id')
                    ->references('id')
                    ->on('inv_import_batches')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inv_import_rows')) {
            Schema::table('inv_import_rows', function (Blueprint $table) {
                try {
                    $table->dropForeign(['batch_id']);
                } catch (\Throwable) {
                    // no-op
                }
            });
            Schema::dropIfExists('inv_import_rows');
        }

        Schema::dropIfExists('inv_import_batches');
    }
};
