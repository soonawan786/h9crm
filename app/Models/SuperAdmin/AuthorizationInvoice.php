<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class AuthorizationInvoice extends Model
{
    protected $table = 'authorize_invoices';

    protected $dates = [
        'pay_date',
        'next_pay_date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

}
