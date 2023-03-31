<?php

namespace App\Models;

use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EstimateTemplateItemImage extends BaseModel
{
    use IconTrait;

    const FILE_PATH = 'estimate-files';

    protected $appends = ['file_url', 'icon'];
    protected $fillable = ['estimate_template_item_id', 'filename', 'hashname', 'size', 'external_link'];

    public function getFileUrlAttribute()
    {
        if (empty($this->external_link)) {
            return asset_url_local_s3('estimate-files/' . $this->estimate_template_item_id . '/' . $this->hashname);
        }
        elseif (!empty($this->external_link)) {
            return $this->external_link;
        }
        else {
            return '';
        }

    }
}