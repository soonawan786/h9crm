<?php

namespace App\Models\SuperAdmin;

use App\Models\User;
use App\Traits\HasCompany;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\SuperAdmin\SupportTicketObserver;

/**
 * App\Models\SuperAdmin\SupportTicket
 *
 * @property-read User $agent
 * @property-read User $client
 * @property-read mixed $created_on
 * @property-read mixed $updated_on
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuperAdmin\SupportTicketReply[] $reply
 * @property-read int|null $reply_count
 * @property-read User $requester
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket newQuery()
 * @method static \Illuminate\Database\Query\Builder|SupportTicket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket query()
 * @method static \Illuminate\Database\Query\Builder|SupportTicket withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SupportTicket withoutTrashed()
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $created_by
 * @property string $subject
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property int|null $agent_id
 * @property int|null $support_ticket_type_id
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereSupportTicketTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTicket whereUserId($value)
 */
class SupportTicket extends Model
{
    use HasCompany;

    use SoftDeletes;
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    protected $appends = ['created_on', 'updated_on'];

    protected static function boot()
    {
        parent::boot();

        static::observe(SupportTicketObserver::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active', CompanyScope::class]);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id')->withoutGlobalScopes(['active', CompanyScope::class]);
    }

    public function reply()
    {
        return $this->hasMany(SupportTicketReply::class, 'support_ticket_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

    public function getCreatedOnAttribute()
    {
        if (!is_null($this->created_at)) {
            return $this->created_at->format('d M Y H:i');
        }

        return '';
    }

    public function getUpdatedOnAttribute()
    {
        if (!is_null($this->updated_at)) {
            return $this->updated_at->format('Y-m-d H:i a');
        }

        return '';
    }

}
