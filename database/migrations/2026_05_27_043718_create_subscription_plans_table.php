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
        Schema::create('subscription_plans', function (Blueprint $table) {
          

            $table->id();
    $table->string('name'); // اسم الباقة (مثلاً: الباقة البرونزية، الباقة السنوية)
    $table->text('description')->nullable(); // وصف الباقة ومميزاتها
    
    // إدارة السعر
    $table->decimal('price', 8, 2)->default(0.00); // سعر الباقة (0.00 تعني مجانية)
    
    // مرونة المدة الزمنية
    $table->unsignedInteger('plan_duration')->default(1); // عدد الوحدات الزمنية (مثلاً: 1, 3, 6, 12)
    $table->enum('plan_interval', ['day', 'month', 'year'])->default('month'); // نوع الوحدة (يوم، شهر، سنة)
    
    // حقول التحكم والخصائص الإضافية
    $table->boolean('is_trial')->default(false); // هل الباقة تجريبية مجانية؟
    $table->enum('status', ['active', 'inactive'])->default('active'); // حالة الباقة (نشطة أو متوقفة)
    
    $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
