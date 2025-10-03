<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_required',
        'is_filterable',
        'is_variation',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_variation' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
            if ($attribute->isDirty('name') && empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    /**
     * Get the attribute values for this attribute
     */
    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Get active attribute values
     */
    public function activeAttributeValues()
    {
        return $this->hasMany(AttributeValue::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get products that use this attribute
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
                    ->withPivot(['is_required', 'is_variation', 'is_visible', 'sort_order'])
                    ->withTimestamps();
    }

    /**
     * Scope for active attributes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for variation attributes
     */
    public function scopeVariation($query)
    {
        return $query->where('is_variation', true);
    }

    /**
     * Scope for filterable attributes
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get attribute type options
     */
    public static function getTypeOptions()
    {
        return [
            'select' => 'Dropdown Select',
            'color' => 'Color Swatch',
            'image' => 'Image Swatch',
            'text' => 'Text Input',
        ];
    }

    /**
     * Check if attribute is used in variations
     */
    public function isUsedInVariations()
    {
        return $this->products()->wherePivot('is_variation', true)->exists();
    }
}