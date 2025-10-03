<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::with(['blogCategory', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        return view('admin.blogs.index', compact('blogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $blogCategories = BlogCategory::active()->ordered()->get();
        return view('admin.blogs.form', compact('blogCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'meta_title' => 'nullable|string|max:60',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Blog::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['user_id'] = Auth::id();
        $data['is_published'] = $request->has('is_published');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Set published_at if publishing
        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Handle media library images
        if ($request->has('media_ids') && !empty($request->input('media_ids'))) {
            $mediaIds = $request->input('media_ids');
            $media = \App\Models\Media::whereIn('id', $mediaIds)->get();
            
            if ($media->count() > 0) {
                // Set first image as featured image
                $data['featured_image'] = $media->first()->file_url;
                
                // Set all images as gallery images
                $data['gallery_images'] = $media->pluck('file_url')->toArray();
            }
        }

        Blog::create($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        $blog->load(['blogCategory', 'user']);
        
        // Increment view count
        $blog->increment('views_count');
        
        return view('admin.blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        $blogCategories = BlogCategory::active()->ordered()->get();
        return view('admin.blogs.form', compact('blog', 'blogCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $blog->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'meta_title' => 'nullable|string|max:60',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Ensure slug is unique (excluding current blog)
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Blog::where('slug', $data['slug'])->where('id', '!=', $blog->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data['is_published'] = $request->has('is_published');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Set published_at if publishing and not already published
        if ($data['is_published'] && !$blog->is_published && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Handle media library images
        if ($request->has('media_ids') && !empty($request->input('media_ids'))) {
            $mediaIds = $request->input('media_ids');
            $media = \App\Models\Media::whereIn('id', $mediaIds)->get();
            
            if ($media->count() > 0) {
                // Set first image as featured image
                $data['featured_image'] = $media->first()->file_url;
                
                // Set all images as gallery images
                $data['gallery_images'] = $media->pluck('file_url')->toArray();
            }
        }

        $blog->update($data);

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        $blog->delete();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post deleted successfully.');
    }
}
