<?php

namespace App\Models\SuperAdmin;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SuperAdmin\PaystackInvoice
 *
 * @property int $id
 * @property int $company_id
 * @property int $package_id
 * @property string|null $transaction_id
 * @property string|null $amount
 * @property \Illuminate\Support\Carbon $pay_date
 * @property \Illuminate\Support\Carbon|null $next_pay_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Company $company
 * @property-read \App\Models\SuperAdmin\Package $package
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereNextPayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice wherePayDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaystackInvoice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaystackInvoice extends Model
{
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
