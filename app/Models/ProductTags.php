<?php

namespace App\Models;

use App\Traits\HasCompany;

class ProductTags extends BaseModel
{

    use HasCompany;

    protected $table = 'tags';

    public function products()
{
    return $this->belongsToMany(Product::class, 'product_tag', 'tag_id', 'product_id');
}


}
