<?php

namespace App\Models\SuperAdmin;

use App\Models\Invoice;
use App\Observers\SuperAdmin\InvoicePaymentReceivedObserver;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $table = 'payments';

    protected $dates = ['paid_on'];

    protected static function boot()
    {
        parent::boot();
        static::observe(InvoicePaymentReceivedObserver::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

}
