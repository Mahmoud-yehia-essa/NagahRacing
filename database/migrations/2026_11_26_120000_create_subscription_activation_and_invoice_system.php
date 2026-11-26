<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subscription_settings')) {
            Schema::create('subscription_settings', function (Blueprint $table) {
                $table->id();
                $table->string('admin_whatsapp', 30)->nullable();
                $table->string('admin_phone', 30)->nullable();
                $table->text('whatsapp_message_template')->nullable();
                $table->text('support_note')->nullable();
                $table->string('currency_label', 20)->default('ر.س');
                $table->boolean('enable_activation_codes')->default(true);
                $table->boolean('show_whatsapp_on_ios')->default(true);
                $table->boolean('show_whatsapp_on_android')->default(true);
                $table->timestamps();
            });

            DB::table('subscription_settings')->insert([
                'admin_whatsapp' => '',
                'admin_phone' => '',
                'whatsapp_message_template' => "السلام عليكم إدارة تطبيق نجاح، أرغب في تفعيل أو تجديد اشتراكي.\nالاسم: {owner_name}\nرقم الحساب: #{owner_id}\nالهاتف: {owner_phone}\nالباقة المطلوبة: {plan_name}",
                'support_note' => 'عند انتهاء الباقة يمكنك التواصل مع الإدارة للحصول على كود التفعيل وإدخاله هنا لتفعيل باقتك فوراً.',
                'currency_label' => 'ر.س',
                'enable_activation_codes' => true,
                'show_whatsapp_on_ios' => true,
                'show_whatsapp_on_android' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('subscription_activation_codes')) {
            Schema::create('subscription_activation_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('code_type', 30)->default('plan'); // plan | custom_addon | plan_plus_addon
                $table->unsignedBigInteger('subscription_plan_id')->nullable();
                $table->unsignedBigInteger('owner_id')->nullable(); // null = any owner once
                $table->unsignedInteger('extra_days')->default(0);
                $table->unsignedInteger('extra_training_sessions')->default(0);
                $table->unsignedInteger('extra_sub_users')->default(0);
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->string('payment_method', 60)->nullable();
                $table->string('payment_reference', 120)->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('active'); // active | used | revoked | expired
                $table->timestamp('expires_at')->nullable();
                $table->unsignedBigInteger('used_by_owner_id')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->unsignedBigInteger('created_by_admin_id')->nullable();
                $table->timestamps();

                $table->index(['status', 'owner_id']);
                $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->nullOnDelete();
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('used_by_owner_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('created_by_admin_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (Schema::hasTable('user_subscriptions')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('user_subscriptions', 'extra_training_sessions')) {
                    $table->unsignedInteger('extra_training_sessions')->default(0)->after('status');
                }
                if (!Schema::hasColumn('user_subscriptions', 'extra_sub_users')) {
                    $table->unsignedInteger('extra_sub_users')->default(0)->after('extra_training_sessions');
                }
                if (!Schema::hasColumn('user_subscriptions', 'activation_code_id')) {
                    $table->unsignedBigInteger('activation_code_id')->nullable()->after('extra_sub_users');
                }
                if (!Schema::hasColumn('user_subscriptions', 'payment_method')) {
                    $table->string('payment_method', 60)->nullable()->after('activation_code_id');
                }
                if (!Schema::hasColumn('user_subscriptions', 'notes')) {
                    $table->text('notes')->nullable()->after('payment_method');
                }
            });
        }

        if (!Schema::hasTable('subscription_invoices')) {
            Schema::create('subscription_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number', 50)->unique();
                $table->unsignedBigInteger('owner_id');
                $table->unsignedBigInteger('user_subscription_id')->nullable();
                $table->unsignedBigInteger('subscription_plan_id')->nullable();
                $table->unsignedBigInteger('activation_code_id')->nullable();
                $table->string('action_type', 40)->default('plan_activation'); // plan_activation | plan_renewal | addon_topup | admin_direct | trial_activation
                $table->string('title', 255);
                $table->string('plan_name', 255)->nullable();
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->string('currency', 20)->default('ر.س');
                $table->string('payment_method', 80)->nullable();
                $table->string('payment_reference', 120)->nullable();
                $table->string('activation_code_used', 40)->nullable();
                $table->integer('days_added')->default(0);
                $table->integer('training_sessions_added')->default(0);
                $table->integer('sub_users_added')->default(0);
                $table->timestamp('period_start')->nullable();
                $table->timestamp('period_end')->nullable();
                $table->string('status', 20)->default('confirmed'); // confirmed | canceled | refunded
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('issued_by_admin_id')->nullable();
                $table->timestamp('issued_at')->useCurrent();
                $table->timestamps();

                $table->index(['owner_id', 'issued_at']);
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('user_subscription_id')->references('id')->on('user_subscriptions')->nullOnDelete();
                $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->nullOnDelete();
                $table->foreign('activation_code_id')->references('id')->on('subscription_activation_codes')->nullOnDelete();
                $table->foreign('issued_by_admin_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');

        if (Schema::hasTable('user_subscriptions')) {
            Schema::table('user_subscriptions', function (Blueprint $table) {
                $cols = array_filter([
                    Schema::hasColumn('user_subscriptions', 'extra_training_sessions') ? 'extra_training_sessions' : null,
                    Schema::hasColumn('user_subscriptions', 'extra_sub_users') ? 'extra_sub_users' : null,
                    Schema::hasColumn('user_subscriptions', 'activation_code_id') ? 'activation_code_id' : null,
                    Schema::hasColumn('user_subscriptions', 'payment_method') ? 'payment_method' : null,
                    Schema::hasColumn('user_subscriptions', 'notes') ? 'notes' : null,
                ]);
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        Schema::dropIfExists('subscription_activation_codes');
        Schema::dropIfExists('subscription_settings');
    }
};
