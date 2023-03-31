<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use App\Models\OfflinePaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class GlobalInvoice extends Model
{
    protected $dates = ['pay_date', 'next_pay_date'];
    protected $appends = ['invoice_number'];

    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function currency()
    {
        return $this->belongsTo(GlobalCurrency::class);
    }

    public function subscription()
    {
        return $this->belongsTo(GlobalSubscription::class);
    }

    public function offlinePaymentMethod()
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id')->withoutGlobalScopes()->whereNull('company_id');
    }

    protected function invoiceNumber(): Attribute
    {
        return Attribute::make(
            get: function () {
                if($this->gateway == 'stripe')
                {
                    $invoiceNumber = $this->stripe_invoice_number;
                }
                else
                {
                    $invoiceNumber = $this->id;
                }

                return str($invoiceNumber)->padLeft(2, '0');
                // $invoiceNumber = $this->invoice_id;
            },
        );
    }

}
