<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\IconTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveFile extends Model
{

    use IconTrait;

    use HasFactory;

    const FILE_PATH = 'leave-files';

    protected $appends = ['file_url', 'icon'];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3(LeaveFile::FILE_PATH . '/' . $this->leave_id . '/' . $this->hashname);
    }

}
