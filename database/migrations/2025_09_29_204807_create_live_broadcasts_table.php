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
        Schema::create('live_broadcasts', function (Blueprint $table) {

              $table->id();

               $table->string('title')->nullable();
             $table->string('title_en')->nullable();


            $table->text('more_des')->nullable();
            $table->text('more_des_en')->nullable();

            $table->enum('status',['active','inactive'])->default('active');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_broadcasts');
    }
};
