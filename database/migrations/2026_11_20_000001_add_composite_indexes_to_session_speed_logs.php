<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('session_speed_logs')) {
            return;
        }

        $indexes = collect(DB::select('SHOW INDEX FROM session_speed_logs'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('session_speed_logs', function (Blueprint $table) use ($indexes) {
            if (!in_array('ssl_session_created_id_idx', $indexes, true)) {
                $table->index(['training_session_id', 'created_at', 'id'], 'ssl_session_created_id_idx');
            }
            if (!in_array('ssl_session_id_idx', $indexes, true)) {
                $table->index(['training_session_id', 'id'], 'ssl_session_id_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('session_speed_logs')) {
            return;
        }

        Schema::table('session_speed_logs', function (Blueprint $table) {
            try {
                $table->dropIndex('ssl_session_created_id_idx');
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('ssl_session_id_idx');
            } catch (\Throwable $e) {
            }
        });
    }
};
