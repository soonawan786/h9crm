<?php

namespace App\Models;

use App\Traits\HasCompany;

class QuickBooksSetting extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];
}
