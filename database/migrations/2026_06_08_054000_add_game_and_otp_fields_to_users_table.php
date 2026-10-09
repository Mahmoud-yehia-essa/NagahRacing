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
            if (!Schema::hasColumn('users', 'is_game_free')) {
                $table->string('is_game_free')->default('paid')->after('role');
            }
            if (!Schema::hasColumn('users', 'otp_verification')) {
                $table->string('otp_verification')->nullable()->after('is_game_free');
            }
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('otp_verification');
            }
            if (!Schema::hasColumn('users', 'firebase_token')) {
                $table->text('firebase_token')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('users', 'set_password')) {
                $table->string('set_password')->nullable()->after('firebase_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_game_free')) {
                $table->dropColumn('is_game_free');
            }
            if (Schema::hasColumn('users', 'otp_verification')) {
                $table->dropColumn('otp_verification');
            }
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }
            if (Schema::hasColumn('users', 'firebase_token')) {
                $table->dropColumn('firebase_token');
            }
            if (Schema::hasColumn('users', 'set_password')) {
                $table->dropColumn('set_password');
            }
        });
    }
};
