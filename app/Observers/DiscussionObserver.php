<?php

namespace App\Observers;

use App\Events\DiscussionEvent;
use App\Models\Discussion;
use App\Models\Notification;

class DiscussionObserver
{

    public function saving(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding() && user()) {
            $discussion->last_updated_by = user()->id;
        }
    }

    public function creating(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if (user()) {
                $discussion->last_updated_by = user()->id;
                $discussion->added_by = user()->id;
            }
        }

        if (company()) {
            $discussion->company_id = company()->id;
        }
    }

    public function created(Discussion $discussion)
    {
        if (!isRunningInConsoleOrSeeding()) {
            event(new DiscussionEvent($discussion));
        }
    }

    public function deleting(Discussion $discussion)
    {
        $notifyData = ['App\Notifications\NewDiscussion', 'App\Notifications\NewDiscussionReply'];
        \App\Models\Notification::deleteNotification($notifyData, $discussion->id);

    }

}
