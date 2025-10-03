<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'product_type',
        'has_variations',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'manage_stock',
        'in_stock',
        'weight',
        'dimensions',
        'images',
        'media_id',
        'original_image',
        'attributes',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'has_variations' => 'boolean',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'images' => 'array',
            'attributes' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    // Variable Product Relationships
    public function productVariations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function activeProductVariations()
    {
        return $this->hasMany(ProductVariation::class)->where('is_active', true);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
                    ->withPivot(['is_required', 'is_variation', 'is_visible', 'sort_order'])
                    ->withTimestamps();
    }

    public function variationAttributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
                    ->wherePivot('is_variation', true)
                    ->withPivot(['is_required', 'is_variation', 'is_visible', 'sort_order'])
                    ->withTimestamps();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
                    ->withPivot(['sort_order'])
                    ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }

    public function scopeSimple($query)
    {
        return $query->where('product_type', 'simple');
    }

    public function scopeVariable($query)
    {
        return $query->where('product_type', 'variable');
    }

    // Accessors
    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->sale_price && $this->sale_price < $this->price) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

    // Variable Product Methods
    public function isVariable()
    {
        return $this->product_type === 'variable' && $this->has_variations;
    }

    public function isSimple()
    {
        return $this->product_type === 'simple';
    }

    public function getMinPriceAttribute()
    {
        if ($this->isVariable() && $this->productVariations()->exists()) {
            return $this->productVariations()->min('price');
        }
        return $this->price;
    }

    public function getMaxPriceAttribute()
    {
        if ($this->isVariable() && $this->productVariations()->exists()) {
            return $this->productVariations()->max('price');
        }
        return $this->price;
    }

    public function getPriceRangeAttribute()
    {
        if ($this->isVariable() && $this->productVariations()->exists()) {
            $minPrice = $this->min_price;
            $maxPrice = $this->max_price;
            
            if ($minPrice == $maxPrice) {
                return '$' . number_format($minPrice, 2);
            }
            
            return '$' . number_format($minPrice, 2) . ' - $' . number_format($maxPrice, 2);
        }
        
        return '$' . number_format($this->current_price, 2);
    }

    public function getVariationCountAttribute()
    {
        return $this->productVariations()->count();
    }

    public function getAvailableVariationsAttribute()
    {
        return $this->productVariations()->active()->inStock()->get();
    }

    public function getDefaultVariationAttribute()
    {
        return $this->productVariations()->active()->inStock()->first();
    }

    public function generateVariations()
    {
        if (!$this->isVariable()) {
            return false;
        }

        $variationAttributes = $this->variationAttributes()->with('activeAttributeValues')->get();
        
        if ($variationAttributes->isEmpty()) {
            return false;
        }

        // Generate all possible combinations
        $combinations = $this->generateAttributeCombinations($variationAttributes);
        
        foreach ($combinations as $combination) {
            $this->createVariationFromCombination($combination);
        }

        return true;
    }

    private function generateAttributeCombinations($attributes)
    {
        $combinations = [[]];
        
        foreach ($attributes as $attribute) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($attribute->activeAttributeValues as $value) {
                    $newCombination = $combination;
                    $newCombination[] = [
                        'attribute_id' => $attribute->id,
                        'attribute_name' => $attribute->name,
                        'value_id' => $value->id,
                        'value' => $value->value,
                        'slug' => $value->slug,
                    ];
                    $newCombinations[] = $newCombination;
                }
            }
            $combinations = $newCombinations;
        }
        
        return $combinations;
    }

    private function createVariationFromCombination($combination)
    {
        $attributeData = [
            'values' => $combination
        ];

        $variationName = collect($combination)->pluck('value')->join(' - ');

        ProductVariation::create([
            'product_id' => $this->id,
            'name' => $variationName,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'stock_quantity' => $this->stock_quantity,
            'manage_stock' => $this->manage_stock,
            'stock_status' => $this->in_stock,
            'weight' => $this->weight,
            'attribute_data' => $attributeData,
            'is_active' => true,
        ]);
    }
}
