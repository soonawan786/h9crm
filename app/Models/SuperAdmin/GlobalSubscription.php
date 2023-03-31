<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class GlobalSubscription extends Model
{
    protected $table = 'global_subscriptions';
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
