<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_settings')) {
            Schema::table('subscription_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_settings', 'show_subscriptions_in_app')) {
                    $table->boolean('show_subscriptions_in_app')->default(true)->after('enable_activation_codes');
                }
                if (!Schema::hasColumn('subscription_settings', 'show_subscriptions_on_ios')) {
                    $table->boolean('show_subscriptions_on_ios')->default(true)->after('show_subscriptions_in_app');
                }
                if (!Schema::hasColumn('subscription_settings', 'show_subscriptions_on_android')) {
                    $table->boolean('show_subscriptions_on_android')->default(true)->after('show_subscriptions_on_ios');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_settings')) {
            Schema::table('subscription_settings', function (Blueprint $table) {
                $cols = array_filter([
                    Schema::hasColumn('subscription_settings', 'show_subscriptions_in_app') ? 'show_subscriptions_in_app' : null,
                    Schema::hasColumn('subscription_settings', 'show_subscriptions_on_ios') ? 'show_subscriptions_on_ios' : null,
                    Schema::hasColumn('subscription_settings', 'show_subscriptions_on_android') ? 'show_subscriptions_on_android' : null,
                ]);
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
