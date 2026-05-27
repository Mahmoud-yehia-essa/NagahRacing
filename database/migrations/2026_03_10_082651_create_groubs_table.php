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
        Schema::create('groubs', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
              $table->string('name')->nullable();
               $table->text('code_number')->nullable();
                $table->text('des')->nullable();
              $table->text('photo')->nullable();





            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groubs');
    }
};
