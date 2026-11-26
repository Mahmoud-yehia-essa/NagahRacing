<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'extra_training_sessions' => 'integer',
        'extra_sub_users' => 'integer',
    ];

    /**
     * Get the owner (user) that owns the subscription.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the subscription plan.
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function activationCode()
    {
        return $this->belongsTo(SubscriptionActivationCode::class, 'activation_code_id');
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class, 'user_subscription_id');
    }
}
