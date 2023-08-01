<?php

namespace App\Models;

use App\Traits\HasCompany;

class ProductBrand extends BaseModel
{

    use HasCompany;

    protected $table = 'product_brands';

}
