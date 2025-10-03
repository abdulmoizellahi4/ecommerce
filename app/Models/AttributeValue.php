<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'color_code',
        'image_url',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attributeValue) {
            if (empty($attributeValue->slug)) {
                $attributeValue->slug = Str::slug($attributeValue->value);
            }
        });

        static::updating(function ($attributeValue) {
            if ($attributeValue->isDirty('value') && empty($attributeValue->slug)) {
                $attributeValue->slug = Str::slug($attributeValue->value);
            }
        });
    }

    /**
     * Get the attribute that owns this value
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get products that use this attribute value
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
                    ->withPivot(['sort_order'])
                    ->withTimestamps();
    }

    /**
     * Get product variations that use this attribute value
     */
    public function productVariations()
    {
        return $this->hasMany(ProductVariation::class, 'attribute_data->value_id');
    }

    /**
     * Scope for active attribute values
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('value');
    }

    /**
     * Get display value based on attribute type
     */
    public function getDisplayValueAttribute()
    {
        switch ($this->attribute->type) {
            case 'color':
                return $this->color_code ?: $this->value;
            case 'image':
                return $this->image_url ?: $this->value;
            default:
                return $this->value;
        }
    }

    /**
     * Check if this value is used in any variations
     */
    public function isUsedInVariations()
    {
        return ProductVariation::whereJsonContains('attribute_data->values', [
            'attribute_id' => $this->attribute_id,
            'value_id' => $this->id,
            'value' => $this->value
        ])->exists();
    }
}