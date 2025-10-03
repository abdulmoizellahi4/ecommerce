<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogCategories = BlogCategory::withCount('blogs')->orderBy('sort_order')->get();
        return view('admin.blog-categories.index', compact('blogCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.blog-categories.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories',
            'slug' => 'nullable|string|max:255|unique:blog_categories',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'meta_title' => 'nullable|string|max:60',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
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
        while (BlogCategory::where('slug', $data['slug'])->exists()) {
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

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BlogCategory $blogCategory)
    {
        $blogCategory->load(['blogs' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('admin.blog-categories.show', compact('blogCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogCategory $blogCategory)
    {
        return view('admin.blog-categories.form', compact('blogCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlogCategory $blogCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $blogCategory->id,
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $blogCategory->id,
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'meta_title' => 'nullable|string|max:60',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
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
        while (BlogCategory::where('slug', $data['slug'])->where('id', '!=', $blogCategory->id)->exists()) {
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

        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogCategory $blogCategory)
    {
        // Check if category has blogs
        if ($blogCategory->blogs()->count() > 0) {
            return redirect()->route('admin.blog-categories.index')
                ->with('error', 'Cannot delete category with existing blogs.');
        }

        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')
            ->with('success', 'Blog category deleted successfully.');
    }
}
