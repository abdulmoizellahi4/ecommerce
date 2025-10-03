<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'gallery_images',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_published',
        'is_featured',
        'published_at',
        'views_count',
        'sort_order'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
        'sort_order' => 'integer',
        'gallery_images' => 'array',
    ];

    // Relationships
    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('published_at', 'desc');
    }

    // Accessors
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }
        return null;
    }

    public function getGalleryImagesUrlsAttribute()
    {
        if ($this->gallery_images) {
            return array_map(function($image) {
                return asset('storage/' . $image);
            }, $this->gallery_images);
        }
        return [];
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutesToRead = round($wordCount / 200); // Average reading speed: 200 words per minute
        return max(1, $minutesToRead);
    }

    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Auto-generate excerpt from content if not provided
        $content = strip_tags($this->content);
        return \Str::limit($content, 160);
    }
}
