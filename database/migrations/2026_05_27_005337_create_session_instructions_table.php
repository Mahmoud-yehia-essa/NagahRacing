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
        Schema::create('session_instructions', function (Blueprint $table) {
           

            $table->id();
    $table->unsignedBigInteger('training_session_id'); // ربط التعليمات بالجلسة الحالية
    $table->unsignedBigInteger('owner_id'); // المالك المرسل أو المستقبل
    $table->unsignedBigInteger('camel_worker_id'); // العامل المرسل أو المستقبل

    $table->boolean('is_from_owner')->default(true); // true: من المالك إلى العامل | false: من العامل إلى المالك
    
    // قسم التوجيهات / التعليمات
    $table->text('instruction')->nullable(); // نص التوجيه أو مسار الملف (صوت/صورة)
    $table->enum('instruction_type', ['text', 'sound', 'image'])->default('text');

    // قسم الردود (تكون فارغة في البداية ولها نوع منفصل)
    $table->text('reply')->nullable(); // نص الرد أو مسار الملف (صوت/صورة) من الطرف الآخر
    $table->enum('reply_type', ['text', 'sound', 'image'])->nullable();

    // العلاقات
    $table->foreign('training_session_id')->references('id')->on('training_sessions')->onDelete('cascade');
    $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('camel_worker_id')->references('id')->on('camel_workers')->onDelete('cascade');

    $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_instructions');
    }
};
