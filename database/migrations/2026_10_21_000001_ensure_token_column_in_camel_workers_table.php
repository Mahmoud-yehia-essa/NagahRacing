<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('camel_workers', function (Blueprint $table) {
            if (!Schema::hasColumn('camel_workers', 'token')) {
                $table->text('token')->nullable()->after('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camel_workers', function (Blueprint $table) {
            if (Schema::hasColumn('camel_workers', 'token')) {
                $table->dropColumn('token');
            }
        });
    }
};
