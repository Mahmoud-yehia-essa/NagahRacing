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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_id')->nullable()->constrained('festivals')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('name_en')->nullable();
            $table->text('des')->nullable();
            $table->text('des_en')->nullable();
            $table->integer('round_number')->nullable();
            $table->string('start');
            $table->string('end');
            $table->enum('round_type',['بكار','قعدان'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
