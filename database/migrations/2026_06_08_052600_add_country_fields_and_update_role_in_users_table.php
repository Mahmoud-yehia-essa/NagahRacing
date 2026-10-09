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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'country_flag')) {
                $table->string('country_flag')->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('users', 'country_name')) {
                $table->string('country_name')->nullable()->after('country_flag');
            }
            
            // Update role column to a simple string to safely support 'owner' and other roles
            $table->string('role')->default('user')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_code')) {
                $table->dropColumn('country_code');
            }
            if (Schema::hasColumn('users', 'country_flag')) {
                $table->dropColumn('country_flag');
            }
            if (Schema::hasColumn('users', 'country_name')) {
                $table->dropColumn('country_name');
            }
            
            $table->enum('role', ['admin', 'user'])->default('user')->change();
        });
    }
};
