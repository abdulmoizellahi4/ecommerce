<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'alt_text',
        'description',
        'uploaded_by',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    protected $appends = [
        'file_size_formatted'
    ];

    // Scope for active media
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for images only
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    // Get file size in human readable format
    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Check if file is image
    public function getIsImageAttribute()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'media_id');
    }
}
