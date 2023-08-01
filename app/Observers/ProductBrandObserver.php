<?php

namespace App\Observers;

use App\Models\ProductBrand;

class ProductBrandObserver
{

    public function creating(ProductBrand $productBrand)
    {
        if (company()) {
            $productBrand->company_id = company()->id;
        }
    }

}
