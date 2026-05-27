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
        Schema::create('session_chats', function (Blueprint $table) {
            $table->id();
            // ربط الرسالة بالجلسة التدريبية الحالية
            $table->unsignedBigInteger('training_session_id'); 
            
            // العلاقات الصريحة والمباشرة (جُعلت nullable لأن الرسالة يرسلها طرف واحد فقط في السطر الواحد)
            $table->unsignedBigInteger('owner_id')->nullable(); // يملأ إذا كان المرسل هو المالك
            $table->unsignedBigInteger('camel_worker_id')->nullable(); // يملأ إذا كان المرسل هو العامل
            
            // محتوى الرسالة ونوعها
            $table->text('content'); // نص الرسالة أو مسار الملف المرفوع (صوت/صورة)
            $table->enum('content_type', ['text', 'sound', 'image'])->default('text');

            // تعريف قيود العلاقات (Foreign Keys) الصريحة في قاعدة البيانات
            $table->foreign('training_session_id')->references('id')->on('training_sessions')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('camel_worker_id')->references('id')->on('camel_workers')->onDelete('cascade');
            
            $table->timestamps(); // الحقل created_at هنا يمثل وقت إرسال الرسالة بالثانية لترتيب الشات
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_chats');
    }
};
