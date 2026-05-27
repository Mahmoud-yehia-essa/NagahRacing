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
        Schema::create('camel_workers', function (Blueprint $table) {
         

            $table->id();
    // ربط العامل بالمالك (الذي هو مستخدم في النظام)
    $table->unsignedBigInteger('owner_id'); 
    
    $table->string('full_name');
    $table->string('login_code')->unique(); // كود دخول العامل الخاص به
    $table->text('photo_path')->nullable();
    $table->string('phone');
    $table->enum('status', ['active', 'inactive'])->default('active');
    
    // العلاقة: ربط حقل owner_id بجدول المستخدمين (الملاك)
    $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
    
    $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camel_workers');
    }
};
