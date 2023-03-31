<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $dates = ['created_at'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
