<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class MediaUploader
{
    protected string $disk = 'public';
    protected string $directory = 'media';
    protected int $quality = 80;
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function handleUploadedFile(UploadedFile $file, ?string $altText = null, ?string $description = null): Media
    {
        $slug = $this->generateSlug($file->getClientOriginalName());
        $path = $this->storeAsWebp($file->getRealPath(), $slug);

        return $this->persistMedia($path, $file->getClientOriginalName(), $altText, $description);
    }

    public function handleRemoteFile(string $url, ?string $altText = null, ?string $description = null): Media
    {
        $contents = @file_get_contents($url);
        if ($contents === false) {
            throw new \RuntimeException('Unable to download image from the provided URL.');
        }

        $originalName = basename(parse_url($url, PHP_URL_PATH)) ?: 'image';
        $slug = $this->generateSlug($originalName);

        $temporary = tmpfile();
        fwrite($temporary, $contents);
        $meta = stream_get_meta_data($temporary);
        $tempPath = $meta['uri'];

        $path = $this->storeAsWebp($tempPath, $slug);

        fclose($temporary);

        return $this->persistMedia($path, $originalName, $altText, $description);
    }

    protected function generateSlug(string $originalName): string
    {
        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        if (empty($base)) {
            $base = 'image';
        }

        $slug = $base;
        $suffix = 1;
        while (Media::where('name', $slug . '.webp')->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function storeAsWebp(string $sourcePath, string $slug): string
    {
        $image = $this->imageManager->read($sourcePath);
        $encoded = $image->toWebp($this->quality);
        $relativePath = $this->directory . '/' . $slug . '.webp';

        Storage::disk($this->disk)->put($relativePath, $encoded);

        $absolutePath = Storage::disk($this->disk)->path($relativePath);
        ImageOptimizer::optimize($absolutePath);

        return $relativePath;
    }

    protected function persistMedia(string $path, string $originalName, ?string $altText, ?string $description): Media
    {
        $disk = Storage::disk($this->disk);

        return Media::create([
            'name'          => basename($path),
            'original_name' => $originalName,
            'file_path'     => $path,
            'file_url'      => asset('storage/' . ltrim($path, '/')),
            'mime_type'     => 'image/webp',
            'file_size'     => $disk->size($path),
            'alt_text'      => $altText,
            'description'   => $description,
            'uploaded_by'   => optional(Auth::user())->getAuthIdentifier(),
        ])->fresh();
    }
}
