<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if this is an admin request
        if ($request->routeIs('admin.*')) {
            return $this->adminIndex($request);
        }

        $query = Product::active()->inStock()->with('category');

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        
        switch ($sort) {
            case 'price':
                $query->orderBy('price', $direction);
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'popular':
                $query->withCount('reviews')->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('name', $direction);
        }

        $products = $query->paginate(12);
        $categories = Category::active()->ordered()->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Admin index method
     */
    private function adminIndex(Request $request)
    {
        $query = Product::with(['category', 'productVariations']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by product type
        if ($request->has('type') && $request->type) {
            $query->where('product_type', $request->type);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        $attributes = Attribute::active()->variation()->with('activeAttributeValues')->ordered()->get();
        $vendors = collect(); // Empty collection for now, you can add vendor logic later
        
        return view('admin.products.create', compact('categories', 'attributes', 'vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('Product store method called', [
            'request_data' => $request->all(),
            'has_variants' => $request->has('variants'),
            'variants_count' => count($request->get('variants', [])),
            'product_type' => $request->get('product_type')
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'product_type' => 'required|in:simple,variable',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:255|unique:products', // SKU can be null for variable products
            'stock_quantity' => 'nullable|integer|min:0',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'media_id' => 'nullable|exists:media,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            // Variable product fields
            'variation_attributes' => 'nullable|array',
            'variation_attributes.*' => 'exists:attributes,id',
            'variation_values' => 'nullable|array',
            'variation_values.*' => 'array',
            'variation_values.*.*' => 'exists:attribute_values,id',
            // Variation data
            'variation_names' => 'nullable|array',
            'variation_names.*' => 'string|max:255',
            'variation_prices' => 'nullable|array',
            'variation_prices.*' => 'numeric|min:0',
            'variation_stock' => 'nullable|array',
            'variation_stock.*' => 'integer|min:0',
            'variation_skus' => 'nullable|array',
            'variation_skus.*' => 'nullable|string|max:255',
            'variation_images' => 'nullable|array',
            'variation_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Frontend variant data
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.sku' => 'nullable|string|max:255',
            'variants.*.image' => 'nullable|string',
            'variants.*.attributes' => 'nullable|array',
            'variant_images' => 'nullable|array',
            'variant_images.*' => 'nullable|string',
        ]);

        \Log::info('Validation passed', ['validated_data' => $validated]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Set boolean fields
        $validated['manage_stock'] = $request->has('manage_stock');
        $validated['in_stock'] = $request->has('in_stock');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');
        $validated['has_variations'] = $validated['product_type'] === 'variable';
        
        // Set default values for optional fields
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['sku'] = $validated['sku'] ?? null;

        if ($request->filled('media_id')) {
            $validated['media_id'] = $request->input('media_id');
        }

        $product = Product::create($validated);

        \Log::info('Product created', ['product_id' => $product->id, 'product_name' => $product->name]);

        // Handle variable product attributes and variations
        if ($validated['product_type'] === 'variable') {
            // Handle new variant data from frontend
            if ($request->has('variants')) {
                $this->createVariantsFromFrontend($product, $request);
            } elseif ($request->has('variation_attributes')) {
                $this->handleVariableProductAttributes($product, $request);
                
                // Handle variation data from the form
                if ($request->has('variation_names')) {
                    $this->createVariationsFromForm($product, $request);
                } else {
                    $this->generateProductVariations($product, $request);
                }
            }
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
                         ->active()
                         ->with(['category', 'reviews.user', 'productVariations', 'attributes.activeAttributeValues'])
                         ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
                                 ->where('id', '!=', $product->id)
                                 ->active()
                                 ->inStock()
                                 ->take(4)
                                 ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->ordered()->get();
        $attributes = Attribute::active()->variation()->with('activeAttributeValues')->ordered()->get();
        $product->load(['attributes', 'attributeValues', 'productVariations']);
        
        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'product_type' => 'required|in:simple,variable',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'stock_quantity' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'in_stock' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            // Variable product fields
            'variation_attributes' => 'nullable|array',
            'variation_attributes.*' => 'exists:attributes,id',
            'variation_values' => 'nullable|array',
            'variation_values.*' => 'array',
            'variation_values.*.*' => 'exists:attribute_values,id',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique (excluding current product)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Set boolean fields
        $validated['manage_stock'] = $request->has('manage_stock');
        $validated['in_stock'] = $request->has('in_stock');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');
        $validated['has_variations'] = $validated['product_type'] === 'variable';

        $product->update($validated);

        // Handle variable product attributes and variations
        if ($validated['product_type'] === 'variable' && $request->has('variation_attributes')) {
            // Clear existing variations and attributes
            $product->productVariations()->delete();
            $product->attributes()->detach();
            $product->attributeValues()->detach();
            
            $this->handleVariableProductAttributes($product, $request);
            $this->generateProductVariations($product, $request);
        } else {
            // Clear variations if switching to simple product
            $product->productVariations()->delete();
            $product->attributes()->detach();
            $product->attributeValues()->detach();
        }

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Delete associated images
        if ($product->images) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        // Delete variation images
        foreach ($product->productVariations as $variation) {
            if ($variation->image_url) {
                Storage::disk('public')->delete($variation->image_url);
            }
            if ($variation->gallery_images) {
                foreach ($variation->gallery_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
                        ->with('success', 'Product deleted successfully.');
    }

    /**
     * Handle variable product attributes
     */
    private function handleVariableProductAttributes(Product $product, Request $request)
    {
        if ($request->has('variation_attributes')) {
            foreach ($request->variation_attributes as $attributeId) {
                $product->attributes()->attach($attributeId, [
                    'is_required' => false,
                    'is_variation' => true,
                    'is_visible' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        if ($request->has('variation_values')) {
            foreach ($request->variation_values as $attributeId => $valueIds) {
                foreach ($valueIds as $valueId) {
                    $product->attributeValues()->attach($valueId, [
                        'attribute_id' => $attributeId,
                        'sort_order' => 0,
                    ]);
                }
            }
        }
    }

    /**
     * Generate product variations
     */
    private function generateProductVariations(Product $product, Request $request)
    {
        $variationAttributes = $product->variationAttributes()->with('activeAttributeValues')->get();
        
        if ($variationAttributes->isEmpty()) {
            return;
        }

        // Generate all possible combinations
        $combinations = $this->generateAttributeCombinations($variationAttributes);
        
        foreach ($combinations as $combination) {
            $this->createVariationFromCombination($product, $combination);
        }
    }

    /**
     * Generate attribute combinations
     */
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

    /**
     * Create variation from combination
     */
    private function createVariationFromCombination(Product $product, $combination)
    {
        $attributeData = [
            'values' => $combination
        ];

        $variationName = collect($combination)->pluck('value')->join(' - ');

        ProductVariation::create([
            'product_id' => $product->id,
            'name' => $variationName,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'stock_quantity' => $product->stock_quantity,
            'manage_stock' => $product->manage_stock,
            'stock_status' => $product->in_stock,
            'weight' => $product->weight,
            'attribute_data' => $attributeData,
            'is_active' => true,
        ]);
    }

    /**
     * Create variants from frontend data
     */
    private function createVariantsFromFrontend(Product $product, Request $request)
    {
        $variants = $request->get('variants', []);
        $variantImages = $request->get('variant_images', []);

        \Log::info('Creating variants from frontend', [
            'product_id' => $product->id,
            'variants_count' => count($variants),
            'variant_images_count' => count($variantImages),
            'variants_data' => $variants
        ]);

        foreach ($variants as $index => $variantData) {
            $variationData = [
                'product_id' => $product->id,
                'name' => $variantData['name'] ?? "Variant " . ($index + 1),
                'price' => $variantData['price'] ?? $product->price,
                'sale_price' => $product->sale_price,
                'stock_quantity' => $variantData['stock'] ?? 0,
                'manage_stock' => true,
                'stock_status' => ($variantData['stock'] ?? 0) > 0 ? 'in_stock' : 'out_of_stock',
                'weight' => $product->weight,
                'is_active' => true,
            ];

            // Handle SKU
            if (isset($variantData['sku']) && !empty($variantData['sku'])) {
                $variationData['sku'] = $variantData['sku'];
            } else {
                // Auto-generate SKU
                $variationData['sku'] = ($product->sku ?: 'PROD') . '-' . strtoupper(substr(md5($variantData['name'] ?? $index), 0, 6));
            }

            // Handle variant image
            if (isset($variantImages[$index]) && !empty($variantImages[$index])) {
                $variationData['image_url'] = $variantImages[$index];
            }

            // Create attribute data for this variation
            $attributeData = ['values' => []];
            if (isset($variantData['attributes'])) {
                foreach ($variantData['attributes'] as $attrName => $attrValue) {
                    // Find attribute and value
                    $attribute = \App\Models\Attribute::where('name', $attrName)->first();
                    if ($attribute) {
                        $attributeValue = \App\Models\AttributeValue::where('attribute_id', $attribute->id)
                            ->where('value', $attrValue)->first();
                        
                        if ($attributeValue) {
                            $attributeData['values'][] = [
                                'attribute_id' => $attribute->id,
                                'attribute_name' => $attribute->name,
                                'value_id' => $attributeValue->id,
                                'value' => $attributeValue->value,
                                'slug' => $attributeValue->slug,
                            ];
                        }
                    }
                }
            }
            $variationData['attribute_data'] = $attributeData;

            ProductVariation::create($variationData);
        }
    }

    /**
     * Create variations from form data
     */
    private function createVariationsFromForm(Product $product, Request $request)
    {
        $variationNames = $request->get('variation_names', []);
        $variationPrices = $request->get('variation_prices', []);
        $variationStock = $request->get('variation_stock', []);
        $variationSkus = $request->get('variation_skus', []);
        $variationImages = $request->file('variation_images', []);

        foreach ($variationNames as $index => $variationName) {
            $variationData = [
                'product_id' => $product->id,
                'name' => $variationName,
                'price' => $variationPrices[$index] ?? $product->price,
                'sale_price' => $product->sale_price,
                'stock_quantity' => $variationStock[$index] ?? $product->stock_quantity,
                'manage_stock' => true,
                'stock_status' => ($variationStock[$index] ?? $product->stock_quantity) > 0 ? 'in_stock' : 'out_of_stock',
                'weight' => $product->weight,
                'is_active' => true,
            ];

            // Handle SKU
            if (isset($variationSkus[$index]) && !empty($variationSkus[$index])) {
                $variationData['sku'] = $variationSkus[$index];
            } else {
                // Auto-generate SKU
                $variationData['sku'] = $product->sku . '-' . strtoupper(substr(md5($variationName), 0, 6));
            }

            // Handle variation image
            if (isset($variationImages[$index]) && $variationImages[$index]) {
                $path = $variationImages[$index]->store('variations', 'public');
                $variationData['image_url'] = $path;
            }

            // Create attribute data for this variation
            $attributeData = $this->generateAttributeDataForVariation($variationName, $product);
            $variationData['attribute_data'] = $attributeData;

            ProductVariation::create($variationData);
        }
    }

    /**
     * Generate attribute data for a variation
     */
    private function generateAttributeDataForVariation($variationName, Product $product)
    {
        $attributeData = ['values' => []];
        
        // Parse variation name to extract attribute values
        $parts = explode(' - ', $variationName);
        
        foreach ($parts as $part) {
            $part = trim($part);
            
            // Find matching attribute value
            $attributeValue = \App\Models\AttributeValue::where('value', $part)->first();
            
            if ($attributeValue) {
                $attributeData['values'][] = [
                    'attribute_id' => $attributeValue->attribute_id,
                    'attribute_name' => $attributeValue->attribute->name,
                    'value_id' => $attributeValue->id,
                    'value' => $attributeValue->value,
                    'slug' => $attributeValue->slug,
                ];
            }
        }
        
        return $attributeData;
    }

    /**
     * Update variation data from form
     */
    private function updateVariationData(Product $product, Request $request)
    {
        $variations = $product->productVariations()->orderBy('id')->get();
        $prices = $request->get('variation_prices', []);
        $stock = $request->get('variation_stock', []);
        $skus = $request->get('variation_skus', []);

        foreach ($variations as $index => $variation) {
            $updateData = [];
            
            if (isset($prices[$index])) {
                $updateData['price'] = $prices[$index];
            }
            
            if (isset($stock[$index])) {
                $updateData['stock_quantity'] = $stock[$index];
            }
            
            if (isset($skus[$index]) && !empty($skus[$index])) {
                $updateData['sku'] = $skus[$index];
            }
            
            if (!empty($updateData)) {
                $variation->update($updateData);
            }
        }
    }

    /**
     * Update variation
     */
    public function updateVariation(Request $request, ProductVariation $variation)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'manage_stock' => 'boolean',
            'stock_status' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image uploads
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('variations', 'public');
            $validated['image_url'] = $path;
        }

        if ($request->hasFile('gallery_images')) {
            $galleryPaths = [];
            foreach ($request->file('gallery_images') as $image) {
                $path = $image->store('variations', 'public');
                $galleryPaths[] = $path;
            }
            $validated['gallery_images'] = $galleryPaths;
        }

        $validated['manage_stock'] = $request->has('manage_stock');
        $validated['stock_status'] = $request->has('stock_status');
        $validated['is_active'] = $request->has('is_active');

        $variation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Variation updated successfully.',
            'variation' => $variation->fresh()
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return redirect()->route('products.index');
        }

        $products = Product::active()->inStock()
                          ->where(function($q) use ($query) {
                              $q->where('name', 'like', '%' . $query . '%')
                                ->orWhere('description', 'like', '%' . $query . '%')
                                ->orWhere('sku', 'like', '%' . $query . '%');
                          })
                          ->with('category')
                          ->paginate(12);

        $categories = Category::active()->ordered()->get();

        return view('products.index', compact('products', 'categories', 'query'));
    }

    /**
     * Show products by category
     */
    public function category($slug)
    {
        $category = Category::where('slug', $slug)->active()->firstOrFail();
        
        $products = Product::where('category_id', $category->id)
                          ->active()
                          ->inStock()
                          ->with('category')
                          ->paginate(12);

        $categories = Category::active()->ordered()->get();

        return view('products.index', compact('products', 'categories', 'category'));
    }
}