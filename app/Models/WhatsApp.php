<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsApp extends Model
{
    use HasFactory;
    protected $table = 'whats_apps';
    protected $fillable=['user_id','api_secret','whatsapp_number','account_id','status','company_id'];
}
