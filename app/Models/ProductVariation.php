<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'manage_stock',
        'stock_status',
        'weight',
        'length',
        'width',
        'height',
        'image_url',
        'gallery_images',
        'attribute_data',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'stock_quantity' => 'integer',
        'manage_stock' => 'boolean',
        'stock_status' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'gallery_images' => 'array',
        'attribute_data' => 'array',
    ];

    /**
     * Get the product that owns this variation
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for active variations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for in-stock variations
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('stock_status', true)
              ->orWhere(function ($subQ) {
                  $subQ->where('manage_stock', true)
                       ->where('stock_quantity', '>', 0);
              });
        });
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the current price (sale price if available, otherwise regular price)
     */
    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?: $this->price;
    }

    /**
     * Get the discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price || !$this->price) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Check if variation is in stock
     */
    public function getIsInStockAttribute()
    {
        if (!$this->manage_stock) {
            return $this->stock_status;
        }

        return $this->stock_quantity > 0;
    }

    /**
     * Get variation name from attribute data
     */
    public function getVariationNameAttribute()
    {
        if ($this->name) {
            return $this->name;
        }

        if (!$this->attribute_data || !isset($this->attribute_data['values'])) {
            return 'Default Variation';
        }

        $names = [];
        foreach ($this->attribute_data['values'] as $value) {
            $names[] = $value['value'];
        }

        return implode(' - ', $names);
    }

    /**
     * Get attribute values for this variation
     */
    public function getAttributeValues()
    {
        if (!$this->attribute_data || !isset($this->attribute_data['values'])) {
            return collect();
        }

        $attributeValues = collect();
        foreach ($this->attribute_data['values'] as $valueData) {
            $attributeValue = AttributeValue::find($valueData['value_id']);
            if ($attributeValue) {
                $attributeValues->push($attributeValue);
            }
        }

        return $attributeValues;
    }

    /**
     * Generate SKU if not provided
     */
    public function generateSku()
    {
        if ($this->sku) {
            return $this->sku;
        }

        $productSku = $this->product->sku ?: 'PRD';
        $variationSuffix = '';

        if ($this->attribute_data && isset($this->attribute_data['values'])) {
            foreach ($this->attribute_data['values'] as $value) {
                $variationSuffix .= '-' . strtoupper(substr($value['value'], 0, 3));
            }
        }

        $this->sku = $productSku . $variationSuffix . '-' . $this->id;
        $this->save();

        return $this->sku;
    }

    /**
     * Get the main image URL
     */
    public function getMainImageAttribute()
    {
        return $this->image_url ?: $this->product->image_url;
    }

    /**
     * Get all images (variation + product images)
     */
    public function getAllImagesAttribute()
    {
        $images = [];

        if ($this->image_url) {
            $images[] = $this->image_url;
        }

        if ($this->gallery_images) {
            $images = array_merge($images, $this->gallery_images);
        }

        if ($this->product->gallery_images) {
            $images = array_merge($images, $this->product->gallery_images);
        }

        return array_unique($images);
    }
}