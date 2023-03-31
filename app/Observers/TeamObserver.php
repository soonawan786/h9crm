<?php

namespace App\Observers;

use App\Models\Team;

class TeamObserver
{

    public function creating(Team $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }

    }

}
