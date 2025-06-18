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
        'uploaded_by',
        'file_type',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    //get human readable file size
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Helper method to check if file is an image
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    // Helper method to check if file is a document
    public function getIsDocumentAttribute(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword'
        ]);
    }
}
