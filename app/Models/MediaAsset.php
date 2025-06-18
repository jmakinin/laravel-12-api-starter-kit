<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaAsset extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'size',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
