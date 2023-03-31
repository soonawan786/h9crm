<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $currency_name
 * @property string|null $currency_symbol
 * @property string $currency_code
 * @property float|null $exchange_rate
 * @property string $is_cryptocurrency
 * @property float|null $usd_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereIsCryptocurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUsdPrice($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCompanyId($value)
 */
class Currency extends BaseModel
{
    use HasCompany;

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

}
