<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get all subscriptions associated with this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'subscription_plan_id');
    }
}
