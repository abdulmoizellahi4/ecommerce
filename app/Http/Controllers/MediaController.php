<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Services\MediaUploader;

class MediaController extends Controller
{
    public function __construct(private MediaUploader $mediaUploader)
    {
    }

    /**
     * Display the media library
     */
    public function index(Request $request)
    {
        $query = Media::active()->images();
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->search . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter by date
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $media = $query->orderBy('created_at', 'desc')->paginate(20);
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.media.partials.media-grid', compact('media'))->render(),
                'pagination' => $media->links('admin.media.partials.pagination')->render()
            ]);
        }
        
        return view('admin.media.index', compact('media'));
    }

    /**
     * Show standalone upload page (WordPress-like)
     */
    public function create(Request $request)
    {
        $media = Media::active()->images()->orderBy('created_at', 'desc')->paginate(12);
        return view('admin.media.create', compact('media'));
    }

    /**
     * Upload new media files
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'files.*'     => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
                'alt_texts'   => 'nullable|array',
                'alt_texts.*' => 'nullable|string|max:255',
                'descriptions'=> 'nullable|array',
                'descriptions.*' => 'nullable|string|max:1000',
            ]);

            $uploadedFiles = [];
            $errors = [];

            foreach ($request->file('files', []) as $index => $file) {
                try {
                    $alt = $validated['alt_texts'][$index] ?? null;
                    $description = $validated['descriptions'][$index] ?? null;
                    $media = $this->mediaUploader->handleUploadedFile($file, $alt, $description);
                    
                    // Format the response to match what JavaScript expects
                    $uploadedFiles[] = [
                        'id' => $media->id,
                        'file_url' => $media->file_url,
                        'original_name' => $media->original_name,
                        'file_size_formatted' => $media->file_size_formatted,
                        'alt_text' => $media->alt_text,
                        'description' => $media->description,
                        'created_at' => $media->created_at
                    ];
                } catch (\Throwable $e) {
                    $errors[] = [
                        'file' => $file->getClientOriginalName(),
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => count($uploadedFiles) > 0,
                'uploaded' => $uploadedFiles,
                'errors' => $errors,
                'message' => count($uploadedFiles) > 0
                    ? (count($errors) > 0 
                        ? 'Files uploaded successfully with some errors' 
                        : 'Files uploaded successfully')
                    : 'Failed to upload files'
            ]);
        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'errors' => [['file' => 'Unknown', 'error' => $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get media library for modal (AJAX)
     */
    public function library(Request $request): JsonResponse
    {
        $query = Media::active()->images();
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->search . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->search . '%');
            });
        }
        
        $media = $query->orderBy('created_at', 'desc')->paginate(12);
        
        return response()->json([
            'html' => view('admin.media.partials.library-grid', compact('media'))->render(),
            'pagination' => $media->links('admin.media.partials.pagination')->render()
        ]);
    }

    /**
     * Update media details
     */
    public function update(Request $request, Media $media): JsonResponse
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $media->update([
            'alt_text' => $request->alt_text,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Media updated successfully',
            'media' => $media
        ]);
    }

    /**
     * Delete media file
     */
    public function destroy(Media $media): JsonResponse
    {
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
            
            // Delete record
            $media->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload from URL
     */
    public function uploadFromUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url'        => 'required|url',
            'alt_text'   => 'nullable|string|max:255',
            'description'=> 'nullable|string|max:1000',
        ]);

        try {
            $media = $this->mediaUploader->handleRemoteFile(
                $validated['url'],
                $validated['alt_text'] ?? null,
                $validated['description'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully from URL',
                'media' => $media
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload from URL: ' . $e->getMessage()
            ], 400);
        }
    }
}
