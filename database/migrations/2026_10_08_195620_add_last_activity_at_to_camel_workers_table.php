<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('camel_workers', 'last_activity_at')) {
            Schema::table('camel_workers', function (Blueprint $table) {
                $table->timestamp('last_activity_at')->nullable()->after('is_online');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('camel_workers', 'last_activity_at')) {
            Schema::table('camel_workers', function (Blueprint $table) {
                $table->dropColumn('last_activity_at');
            });
        }
    }
};
