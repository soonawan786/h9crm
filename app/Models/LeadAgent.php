<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LeadAgent
 *
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @property-read mixed $icon
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|LeadAgent whereCompanyId($value)
 */
class LeadAgent extends BaseModel
{

    use HasCompany;

    protected $table = 'lead_agents';
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope(ActiveScope::class);
    }

}
