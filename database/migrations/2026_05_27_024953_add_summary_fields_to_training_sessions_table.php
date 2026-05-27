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
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->text('summary_text')->nullable()->after('session_ended_at');
            $table->string('summary_audio')->nullable()->after('summary_text');
            $table->string('summary_image')->nullable()->after('summary_audio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn(['summary_text', 'summary_audio', 'summary_image']);
        });
    }
};
