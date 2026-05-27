<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing records to avoid duplicates
        \Schema::disableForeignKeyConstraints();
        SubscriptionPlan::truncate();
        UserSubscription::truncate();
        \Schema::enableForeignKeyConstraints();

        // 1. Create Default Plans
        $bronze = SubscriptionPlan::create([
            'name' => 'الباقة البرونزية',
            'description' => 'باقة أساسية تتيح تتبع هجن واحد، سجل سرعات مبسط ومحادثات غير محدودة مع العامل.',
            'price' => 150.00,
            'plan_duration' => 1,
            'plan_interval' => 'month',
            'is_trial' => false,
            'status' => 'active',
        ]);

        $silver = SubscriptionPlan::create([
            'name' => 'الباقة الفضية المتميزة',
            'description' => 'باقة متقدمة تتيح تتبع حتى 5 هجن، خرائط تفاعلية لحظية دقيقة (Google Maps)، وتتبع السرعة ومراجعة ملخصات التدريب الصوتية والمرئية.',
            'price' => 400.00,
            'plan_duration' => 3,
            'plan_interval' => 'month',
            'is_trial' => false,
            'status' => 'active',
        ]);

        $gold = SubscriptionPlan::create([
            'name' => 'الباقة الذهبية للمحترفين',
            'description' => 'الباقة الشاملة والكاملة لجميع مزايا التطبيق دون قيود، تتبع غير محدود للهجن، أولوية الدعم الفني، وربط كامل مع أجهزة التعقب وسجلات التدريب التاريخية.',
            'price' => 1200.00,
            'plan_duration' => 1,
            'plan_interval' => 'year',
            'is_trial' => false,
            'status' => 'active',
        ]);

        $trial = SubscriptionPlan::create([
            'name' => 'الباقة التجريبية المجانية',
            'description' => 'باقة تجريبية للملاك الجدد للتعرف على مزايا لوحة التحكم وتتبع الجلسات لمدة أسبوعين مجاناً.',
            'price' => 0.00,
            'plan_duration' => 14,
            'plan_interval' => 'day',
            'is_trial' => true,
            'status' => 'active',
        ]);

        $inactive = SubscriptionPlan::create([
            'name' => 'باقة الرواد (عرض منتهي)',
            'description' => 'باقة قديمة غير نشطة حالياً مخصصة للمشتركين الأوائل.',
            'price' => 90.00,
            'plan_duration' => 1,
            'plan_interval' => 'month',
            'is_trial' => false,
            'status' => 'inactive',
        ]);

        // 2. Create Dummy Subscriptions for Salem Owner
        $owner = User::where('role', 'owner')->first();
        if ($owner) {
            // Subscription 1: Active Silver plan
            $startDate = Carbon::now()->subDays(10);
            $endDate = clone $startDate;
            $endDate->addMonths(3); // Silver plan is 3 months

            UserSubscription::create([
                'owner_id' => $owner->id,
                'subscription_plan_id' => $silver->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'amount_paid' => 400.00,
                'transaction_id' => 'TXN-982348571',
                'status' => 'active',
            ]);
        }
    }
}
