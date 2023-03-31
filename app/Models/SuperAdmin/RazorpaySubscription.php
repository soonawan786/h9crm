<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;

class RazorpaySubscription extends Model
{
    protected $dates = ['created_at'];

    protected $table = 'razorpay_subscriptions';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

}
