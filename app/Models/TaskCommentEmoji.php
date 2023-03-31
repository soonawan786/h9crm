<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Scopes\ActiveScope;

class TaskCommentEmoji extends Model
{

    public function taskComment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

}
