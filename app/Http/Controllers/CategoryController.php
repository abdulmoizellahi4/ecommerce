<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.categories.form', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'parent_id' => 'nullable|exists:categories,id',
            'meta_title' => 'nullable|string|max:60',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'robots' => 'nullable|string|in:index,follow,index,nofollow,noindex,follow,noindex,nofollow',
            'schema_markup' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Handle media library image
        if ($request->has('media_ids') && !empty($request->input('media_ids'))) {
            $mediaId = $request->input('media_ids')[0]; // Take first media ID
            $media = \App\Models\Media::find($mediaId);
            if ($media) {
                $data['image'] = $media->file_url;
            }
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['products' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->get();
        return view('admin.categories.form', compact('category', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'parent_id' => 'nullable|exists:categories,id',
            'meta_title' => 'nullable|string|max:60',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
            'canonical_url' => 'nullable|url|max:500',
            'robots' => 'nullable|string|in:index,follow,index,nofollow,noindex,follow,noindex,nofollow',
            'schema_markup' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure slug is unique (excluding current category)
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Category::where('slug', $data['slug'])->where('id', '!=', $category->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Handle media library image
        if ($request->has('media_ids') && !empty($request->input('media_ids'))) {
            $mediaId = $request->input('media_ids')[0]; // Take first media ID
            $media = \App\Models\Media::find($mediaId);
            if ($media) {
                $data['image'] = $media->file_url;
            }
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing products.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}