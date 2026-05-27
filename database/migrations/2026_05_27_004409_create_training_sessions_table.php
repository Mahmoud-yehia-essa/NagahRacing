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
        Schema::create('training_sessions', function (Blueprint $table) {
         

            $table->id();
    // ربط الجلسة بالعامل/المدرب الذي يقودها من الجدول السابق
    $table->unsignedBigInteger('camel_worker_id'); 
    
    $table->string('location_name')->nullable();
    // إحداثيات الموقع بدقة عالية الخانات (خرائط جوجل)
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    
    // حالة الشوط أو الدورة الحالية
    $table->enum('round_status', ['pending', 'working', 'stop', 'end'])->default('pending');
    
    // مؤشرات السرعة والمسافة
    $table->decimal('speed', 5, 2)->default(0.00); // السرعة الحالية (كم/ساعة مثلاً)
    $table->decimal('average_speed', 5, 2)->default(0.00); // متوسط السرعة
    $table->decimal('round_distance_km', 6, 2)->default(0.00); // مسافة الشوط بالكيلومتر
    $table->string('round_time')->nullable(); // مدة الشوط (مثلاً بصيغة 00:15:30)
    
    // تقييم الأداء (نسبة مئوية أو قيمة عشرية تحسبها المعادلات لديك)
    $table->decimal('performance', 5, 2)->default(0.00); 
    
    // وقت نهاية الجلسة
    $table->timestamp('session_ended_at')->nullable();
    
    // العلاقات
    $table->foreign('camel_worker_id')->references('id')->on('camel_workers')->onDelete('cascade');
    
    $table->timestamps();



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
