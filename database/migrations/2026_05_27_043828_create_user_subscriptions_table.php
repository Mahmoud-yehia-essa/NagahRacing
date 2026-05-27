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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
    // العلاقات الأساسية
    $table->unsignedBigInteger('owner_id'); // المالك المشترك (مرتبط بجدول users)
    $table->unsignedBigInteger('subscription_plan_id'); // الباقة التي تم اختيارها
    
    // تواريخ الصلاحية
    $table->timestamp('start_date')->useCurrent(); // تاريخ بداية الاشتراك (تلقائياً وقت الإنشاء)
    $table->timestamp('end_date')->nullable(); // تاريخ نهاية الاشتراك (يُحسب برمجياً بناءً على مدة الباقة)
    
    // بيانات الدفع المالي
    $table->decimal('amount_paid', 8, 2)->default(0.00); // المبلغ الفعلي الذي دفعه المالك
    $table->string('transaction_id')->nullable(); // الرقم المرجعي لعملية الدفع من بوابة الدفع
    
    // حالة الاشتراك الحالية
    $table->enum('status', ['active', 'expired', 'canceled', 'pending_payment'])->default('active');
    
    // تعريف قيود العلاقات (Foreign Keys)
    $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
    
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
