<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Passport extends Model
{
    use HasCompany;

    protected $table = 'passport_details';
    const FILE_PATH = 'passport';
    protected $appends = ['image_url'];
    protected $dates = ['issue_date', 'expiry_date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute()
    {
        // return asset_url_local_s3('passport/' . $this->file);
        return asset_url(Passport::FILE_PATH . '/'  . $this->file);
    }

    public function country(): HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

}
