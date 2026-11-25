<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_settings', 'label')) {
                $table->string('label', 150)->default('حساب نجاح ريسنج الرئيسي')->after('id');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'session_id')) {
                $table->string('session_id', 80)->nullable()->unique()->after('label');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'plan_type')) {
                $table->string('plan_type', 30)->default('yearly')->after('api_key'); // monthly, yearly
            }
            if (!Schema::hasColumn('whatsapp_settings', 'subscription_price')) {
                $table->decimal('subscription_price', 10, 2)->default(70.00)->after('plan_type');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'currency')) {
                $table->string('currency', 10)->default('USD')->after('subscription_price');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('starts_at');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('expires_at');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'is_primary_otp')) {
                $table->boolean('is_primary_otp')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('whatsapp_settings', 'notes')) {
                $table->text('notes')->nullable()->after('is_primary_otp');
            }
        });

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_messages', 'whatsapp_setting_id')) {
                $table->unsignedBigInteger('whatsapp_setting_id')->nullable()->index()->after('id');
            }
        });

        // ترقية السجل الحالي (إن وُجد) ليحمل الجلسة الافتراضية 'default' ويبقى الحساب الرئيسي لـ OTP
        $first = DB::table('whatsapp_settings')->orderBy('id')->first();
        if ($first) {
            DB::table('whatsapp_settings')
                ->where('id', $first->id)
                ->update([
                    'label'              => $first->label ?: 'حساب نجاح ريسنج الرئيسي',
                    'session_id'         => $first->session_id ?: 'default',
                    'plan_type'          => $first->plan_type ?: 'yearly',
                    'subscription_price' => $first->subscription_price ?: 70.00,
                    'currency'           => $first->currency ?: 'USD',
                    'starts_at'          => $first->starts_at ?: now(),
                    'expires_at'         => $first->expires_at ?: now()->addYear(),
                    'is_active'          => 1,
                    'is_primary_otp'     => 1,
                ]);

            DB::table('whatsapp_messages')
                ->whereNull('whatsapp_setting_id')
                ->update(['whatsapp_setting_id' => $first->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_messages', 'whatsapp_setting_id')) {
                $table->dropColumn('whatsapp_setting_id');
            }
        });

        Schema::table('whatsapp_settings', function (Blueprint $table) {
            $cols = [
                'label',
                'session_id',
                'plan_type',
                'subscription_price',
                'currency',
                'starts_at',
                'expires_at',
                'is_active',
                'is_primary_otp',
                'notes',
            ];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('whatsapp_settings', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
