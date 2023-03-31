<?php

namespace App\Observers;

use App\Models\TicketAgentGroups;

class TicketAgentGroupsObserver
{

    public function creating(TicketAgentGroups $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
