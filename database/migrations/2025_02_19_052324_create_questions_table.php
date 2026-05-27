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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('qu_title')->nullable();;
            $table->string('qu_image')->nullable();
            $table->string('qu_sound')->nullable();
            $table->string('qu_video')->nullable();
            $table->integer('qu_points')->nullable();;
            $table->integer('time_counter')->nullable();;

            $table->string('questions_type')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
