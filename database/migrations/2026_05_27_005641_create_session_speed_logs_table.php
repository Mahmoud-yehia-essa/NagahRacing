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
        Schema::create('session_speed_logs', function (Blueprint $table) {
         

            $table->id();
    // ربط السجل بالجلسة التدريبية
    $table->unsignedBigInteger('training_session_id'); 
    
    // تسجيل السرعة في هذه اللحظة (مثلاً: 45.20 كم/ساعة)
    $table->decimal('speed', 5, 2)->default(0.00); 
    
    // إحداثيات الموقع اللحظية بدقة عالية لرسم المسار على الخريطة
    $table->decimal('latitude', 10, 8);
    $table->decimal('longitude', 11, 8);
    $table->string('location_name')->nullable(); // اسم أو وصف الموقع (اختياري)

    // العلاقات
    $table->foreign('training_session_id')->references('id')->on('training_sessions')->onDelete('cascade');
    
    $table->timestamps(); // الحقل created_at سيمثل وقت تسجيل هذه السرعة بدقة


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_speed_logs');
    }
};
