<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstimateTemplateItem extends BaseModel
{

    // protected $table = 'estimate_template_items';

    protected $guarded = ['id'];

    protected $with = ['EstimateTemplateItemImage'];

    public function estimateTemplateItemImage(): HasOne
    {
        return $this->hasOne(EstimateTemplateItemImage::class, 'estimate_template_item_id');
    }

    public static function taxbyid($id)
    {
        return Tax::where('id', $id)->withTrashed();
    }

    public function getTaxListAttribute()
    {
        $estimateItemTax = $this->taxes;
        $taxes = '';

        if ($estimateItemTax) {
            $numItems = count(json_decode($estimateItemTax));

            if (!is_null($estimateItemTax)) {
                foreach (json_decode($estimateItemTax) as $index => $tax) {
                    $tax = $this->taxbyid($tax)->first();
                    $taxes .= $tax->tax_name . ': ' . $tax->rate_percent . '%';

                    $taxes = ($index + 1 != $numItems) ? $taxes . ', ' : $taxes;
                }
            }
        }

        return $taxes;
    }

}