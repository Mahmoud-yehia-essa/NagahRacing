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
        Schema::create('camel_round_participations', function (Blueprint $table) {
            $table->id();
        $table->foreignId('festival_id')->nullable()->constrained('festivals')->onDelete('cascade');
        $table->foreignId('round_id')->nullable()->constrained('rounds')->onDelete('cascade');
        $table->foreignId('camal_id')->nullable()->constrained('camals')->onDelete('cascade');
        $table->string('registration_number')->nullable();



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camel_round_participations');
    }
};
