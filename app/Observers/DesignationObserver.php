<?php

namespace App\Observers;

use App\Models\Designation;

class DesignationObserver
{

    public function creating(Designation $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
