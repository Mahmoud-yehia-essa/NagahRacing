<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_versions')) {
            return;
        }

        Schema::table('app_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('app_versions', 'ios_version')) {
                $table->string('ios_version', 50)->nullable()->after('version');
            }
            if (!Schema::hasColumn('app_versions', 'android_version')) {
                $table->string('android_version', 50)->nullable()->after('ios_version');
            }
            if (!Schema::hasColumn('app_versions', 'ios_update_enabled')) {
                $table->boolean('ios_update_enabled')->default(true)->after('update_required');
            }
            if (!Schema::hasColumn('app_versions', 'android_update_enabled')) {
                $table->boolean('android_update_enabled')->default(true)->after('ios_update_enabled');
            }
            if (!Schema::hasColumn('app_versions', 'ios_update_required')) {
                $table->string('ios_update_required', 10)->default('no')->after('android_update_enabled');
            }
            if (!Schema::hasColumn('app_versions', 'android_update_required')) {
                $table->string('android_update_required', 10)->default('no')->after('ios_update_required');
            }
            if (!Schema::hasColumn('app_versions', 'ios_des')) {
                $table->text('ios_des')->nullable()->after('des');
            }
            if (!Schema::hasColumn('app_versions', 'android_des')) {
                $table->text('android_des')->nullable()->after('ios_des');
            }
        });

        // Seed per-platform fields from existing legacy columns so current settings stay intact
        $rows = DB::table('app_versions')->get();
        foreach ($rows as $row) {
            DB::table('app_versions')->where('id', $row->id)->update([
                'ios_version' => $row->ios_version ?: ($row->version ?: '1.0.0'),
                'android_version' => $row->android_version ?: ($row->version ?: '1.0.0'),
                'ios_update_required' => in_array($row->ios_update_required, ['yes', 'no'], true)
                    ? $row->ios_update_required
                    : (($row->update_required === 'yes') ? 'yes' : 'no'),
                'android_update_required' => in_array($row->android_update_required, ['yes', 'no'], true)
                    ? $row->android_update_required
                    : (($row->update_required === 'yes') ? 'yes' : 'no'),
                'ios_des' => $row->ios_des ?: $row->des,
                'android_des' => $row->android_des ?: $row->des,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('app_versions')) {
            return;
        }

        Schema::table('app_versions', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('app_versions', 'ios_version') ? 'ios_version' : null,
                Schema::hasColumn('app_versions', 'android_version') ? 'android_version' : null,
                Schema::hasColumn('app_versions', 'ios_update_enabled') ? 'ios_update_enabled' : null,
                Schema::hasColumn('app_versions', 'android_update_enabled') ? 'android_update_enabled' : null,
                Schema::hasColumn('app_versions', 'ios_update_required') ? 'ios_update_required' : null,
                Schema::hasColumn('app_versions', 'android_update_required') ? 'android_update_required' : null,
                Schema::hasColumn('app_versions', 'ios_des') ? 'ios_des' : null,
                Schema::hasColumn('app_versions', 'android_des') ? 'android_des' : null,
            ]);
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
