<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Otp extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = true;
    protected $table = 'otps';
    protected $guarded = [];

    protected $fillable = [
        'user_id',
        'otp',
        'otp_expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
