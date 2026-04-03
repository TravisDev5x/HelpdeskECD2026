<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('historical_services')) {
            return;
        }

        Schema::table('historical_services', function (Blueprint $table) {
            if (! Schema::hasColumn('historical_services', 'event_type')) {
                $table->string('event_type', 32)->nullable();
            }
            if (! Schema::hasColumn('historical_services', 'previous_failure_id')) {
                $table->foreignId('previous_failure_id')->nullable()->constrained('failures')->nullOnDelete();
            }
            if (! Schema::hasColumn('historical_services', 'escalation_reason')) {
                $table->string('escalation_reason', 2000)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('historical_services')) {
            return;
        }

        Schema::table('historical_services', function (Blueprint $table) {
            if (Schema::hasColumn('historical_services', 'previous_failure_id')) {
                $table->dropForeign(['previous_failure_id']);
            }
        });

        Schema::table('historical_services', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('historical_services', 'escalation_reason')) {
                $drops[] = 'escalation_reason';
            }
            if (Schema::hasColumn('historical_services', 'previous_failure_id')) {
                $drops[] = 'previous_failure_id';
            }
            if (Schema::hasColumn('historical_services', 'event_type')) {
                $drops[] = 'event_type';
            }
            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
