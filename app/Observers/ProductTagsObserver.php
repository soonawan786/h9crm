<?php

namespace App\Observers;

use App\Models\ProductTags;

class ProductTagsObserver
{

    public function creating(ProductTags $productTag)
    {
        if (company()) {
            $productTag->company_id = company()->id;
        }
    }

}
