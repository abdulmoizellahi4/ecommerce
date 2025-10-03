<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Handle AJAX requests for loading attributes
        if ($request->ajax() || $request->has('ajax')) {
            $attributes = Attribute::with('activeAttributeValues')
                                  ->where('is_active', true)
                                  ->where('is_variation', true)
                                  ->ordered()
                                  ->get();
            
            return response()->json([
                'success' => true,
                'attributes' => $attributes
            ]);
        }

        $attributes = Attribute::withCount('attributeValues')
                              ->with('activeAttributeValues')
                              ->ordered()
                              ->paginate(15);

        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $typeOptions = Attribute::getTypeOptions();
        return view('admin.attributes.create', compact('typeOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:select,color,image,text',
                'is_required' => 'boolean',
                'is_filterable' => 'boolean',
                'is_variation' => 'boolean',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'values' => 'nullable|array',
                'values.*.value' => 'required_with:values|string|max:255',
                'values.*.color_code' => 'nullable|string|max:7',
                'values.*.image_url' => 'nullable|url',
                'values.*.description' => 'nullable|string',
            ]);

            $data = $request->all();
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Ensure slug is unique
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Attribute::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $data['is_required'] = $request->has('is_required');
            $data['is_filterable'] = $request->has('is_filterable');
            $data['is_variation'] = $request->has('is_variation');
            $data['is_active'] = $request->has('is_active');
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $attribute = Attribute::create($data);

            // Create attribute values if provided
            if ($request->has('values') && is_array($request->values)) {
                foreach ($request->values as $index => $valueData) {
                    if (!empty($valueData['value'])) {
                        AttributeValue::create([
                            'attribute_id' => $attribute->id,
                            'value' => $valueData['value'],
                            'slug' => Str::slug($valueData['value']),
                            'color_code' => $valueData['color_code'] ?? null,
                            'image_url' => $valueData['image_url'] ?? null,
                            'description' => $valueData['description'] ?? null,
                            'sort_order' => $index,
                            'is_active' => true,
                        ]);
                    }
                }
            }

            // Handle AJAX requests
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attribute created successfully',
                    'attribute' => $attribute->load('activeAttributeValues')
                ]);
            }

            return redirect()->route('admin.attributes.index')
                ->with('success', 'Attribute created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating attribute: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $attribute->load(['activeAttributeValues', 'products']);
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attribute $attribute)
    {
        $typeOptions = Attribute::getTypeOptions();
        $attribute->load('attributeValues');
        return view('admin.attributes.edit', compact('attribute', 'typeOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            'slug' => 'nullable|string|max:255|unique:attributes,slug,' . $attribute->id,
            'description' => 'nullable|string',
            'type' => 'required|in:select,color,image,text',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_variation' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'values' => 'nullable|array',
            'values.*.id' => 'nullable|exists:attribute_values,id',
            'values.*.value' => 'required_with:values|string|max:255',
            'values.*.color_code' => 'nullable|string|max:7',
            'values.*.image_url' => 'nullable|url',
            'values.*.description' => 'nullable|string',
            'values.*.is_active' => 'boolean',
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique (excluding current attribute)
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Attribute::where('slug', $data['slug'])->where('id', '!=', $attribute->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_required'] = $request->has('is_required');
        $data['is_filterable'] = $request->has('is_filterable');
        $data['is_variation'] = $request->has('is_variation');
        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $attribute->update($data);

        // Update attribute values
        if ($request->has('values') && is_array($request->values)) {
            $existingValueIds = [];
            
            foreach ($request->values as $index => $valueData) {
                if (!empty($valueData['value'])) {
                    $valueData['attribute_id'] = $attribute->id;
                    $valueData['slug'] = Str::slug($valueData['value']);
                    $valueData['sort_order'] = $index;
                    $valueData['is_active'] = isset($valueData['is_active']);

                    if (isset($valueData['id']) && $valueData['id']) {
                        // Update existing value
                        $attributeValue = AttributeValue::find($valueData['id']);
                        if ($attributeValue) {
                            $attributeValue->update($valueData);
                            $existingValueIds[] = $attributeValue->id;
                        }
                    } else {
                        // Create new value
                        $attributeValue = AttributeValue::create($valueData);
                        $existingValueIds[] = $attributeValue->id;
                    }
                }
            }

            // Delete values that were removed
            AttributeValue::where('attribute_id', $attribute->id)
                        ->whereNotIn('id', $existingValueIds)
                        ->delete();
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        // Check if attribute is used in variations
        if ($attribute->isUsedInVariations()) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Cannot delete attribute that is used in product variations.');
        }

        $attribute->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Get attribute values via AJAX
     */
    public function getValues(Request $request, $id)
    {
        try {
            $attribute = Attribute::findOrFail($id);
            $values = $attribute->activeAttributeValues()->ordered()->get();
            
            return response()->json([
                'success' => true,
                'values' => $values
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading attribute values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create attribute value via AJAX
     */
    public function storeValue(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'image_url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $attributeValue = AttributeValue::create([
            'attribute_id' => $attribute->id,
            'value' => $request->value,
            'slug' => Str::slug($request->value),
            'color_code' => $request->color_code,
            'image_url' => $request->image_url,
            'description' => $request->description,
            'sort_order' => $attribute->attributeValues()->max('sort_order') + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'value' => $attributeValue
        ]);
    }

    /**
     * Update attribute value via AJAX
     */
    public function updateValue(Request $request, AttributeValue $attributeValue)
    {
        $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'image_url' => 'nullable|url',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['value']);
        $data['is_active'] = $request->has('is_active');

        $attributeValue->update($data);

        return response()->json([
            'success' => true,
            'value' => $attributeValue
        ]);
    }

    /**
     * Delete attribute value via AJAX
     */
    public function destroyValue(AttributeValue $attributeValue)
    {
        // Check if value is used in variations
        if ($attributeValue->isUsedInVariations()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete value that is used in product variations.'
            ], 400);
        }

        $attributeValue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute value deleted successfully.'
        ]);
    }
}