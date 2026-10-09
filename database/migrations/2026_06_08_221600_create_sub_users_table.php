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
        Schema::create('sub_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->nullable(); // يملأ إذا كان المرسل هو المالك
            $table->string('full_name');

            $table->string('phone')->nullable();
    $table->string('login_code')->unique(); // كود دخول العامل الخاص به
                $table->enum('status', ['active', 'inactive'])->default('active');


            $table->timestamps();
            // تعريف قيود العلاقات (Foreign Keys) الصريحة في قاعدة البيانات
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_users');
    }
};
