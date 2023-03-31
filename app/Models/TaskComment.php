<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * App\Models\TaskComment
 *
 * @property int $id
 * @property string $comment
 * @property int $user_id
 * @property int $task_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment query()
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TaskComment whereUserId($value)
 * @mixin \Eloquent
 */
class TaskComment extends BaseModel
{

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function commentEmoji(): HasMany
    {
        return $this->hasMany(TaskCommentEmoji::class, 'comment_id');
    }

    public function like(): HasMany
    {
        return $this->hasMany(TaskCommentEmoji::class, 'comment_id')->where('emoji_name', 'thumbs-up');
    }

    public function dislike(): HasMany
    {
        return $this->hasMany(TaskCommentEmoji::class, 'comment_id')->where('emoji_name', 'thumbs-down');
    }

    public function likeUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            TaskCommentEmoji::class,
            'comment_id', // Foreign key on the task comment emoji table...
            'id', // Foreign key on the user table...
            'id', // Local key on the task comment table...
            'user_id' // Local key on the task comment emoji table...
        )->where('task_comment_emoji.emoji_name', 'thumbs-up');
    }

    public function dislikeUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            TaskCommentEmoji::class,
            'comment_id', // Foreign key on the task comment emoji table...
            'id', // Foreign key on the user table...
            'id', // Local key on the task comment table...
            'user_id' // Local key on the task comment emoji table...
        )->where('task_comment_emoji.emoji_name', 'thumbs-down');
    }

}
