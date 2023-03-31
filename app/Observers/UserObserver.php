<?php

namespace App\Observers;

use App\Events\NewUserEvent;
use App\Models\TicketAgentGroups;
use App\Models\User;

class UserObserver
{

    public function saving(User $user)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($user->isDirty('status') && $user->status == 'deactive') {
                // Remove as ticket agent
                TicketAgentGroups::whereAgentId($user->id)->delete();
            }
        }

        session()->forget('user');
    }

    public function created(User $user)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $sendMail = true;

            if (request()->has('sendMail') && request()->sendMail == 'no') {
                $sendMail = false;
            }

            if ($sendMail && session()->has('auth_pass') && auth()->check() && request()->email != '') {
                event(new NewUserEvent($user, session('auth_pass')));
            }

            session()->forget('auth_pass');
        }
    }

    public function creating(User $model)
    {
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
