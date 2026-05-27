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
        Schema::create('festivals', function (Blueprint $table) {
            $table->id();
                $table->string('name')->nullable();
                $table->string('name_en')->nullable();

            $table->text('location')->nullable();

            $table->string('start');
            $table->string('end');

            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();



            $table->text('photo')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festivals');
    }
};
