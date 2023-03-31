<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\EstimateTemplate
 *
 * @property int|null $unit_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Estimate whereCompanyId($value)
 */


class EstimateTemplate extends BaseModel
{
    use HasCompany;

    protected $table = 'estimate_templates';

    public function items(): HasMany
    {
        return $this->hasMany(EstimateTemplateItem::class, 'estimate_template_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function clients(): BelongsTo
    {
        return $this->belongsTo(ClientDetails::class);
    }

    public function units(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_id');
    }

}
