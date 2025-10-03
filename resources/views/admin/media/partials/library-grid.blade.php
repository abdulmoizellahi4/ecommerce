@if($media->count() > 0)
    @foreach($media as $item)
        @php
            $imageUrl = \Illuminate\Support\Str::startsWith($item->file_url, ['http://', 'https://'])
                ? $item->file_url
                : asset(ltrim($item->file_url, '/'));
        @endphp
        <div class="wp-media-item"
             data-media-id="{{ $item->id }}"
             data-media-url="{{ $imageUrl }}"
             data-media-name="{{ $item->original_name }}"
             data-media-size="{{ $item->file_size_formatted }}"
             data-media-type="{{ $item->mime_type }}"
             data-media-alt="{{ $item->alt_text }}"
             data-media-description="{{ $item->description }}"
             data-media-created="{{ $item->created_at->format('Y-m-d H:i') }}">
            <div class="wp-media-item-thumb">
                <img src="{{ $imageUrl }}" alt="{{ $item->alt_text ?? $item->original_name }}" loading="lazy"
                     onerror="this.style.display='none'; this.parentElement.classList.add('has-error');">
            </div>
            <div class="wp-media-item-info">
                <div class="wp-media-item-title" title="{{ $item->original_name }}">{{ $item->original_name }}</div>
                <div class="wp-media-item-meta">{{ $item->file_size_formatted }}</div>
            </div>
            <div class="wp-media-item-check">
                <i class="ri-check-line"></i>
            </div>
        </div>
    @endforeach
@else
    <div class="col-12">
        <div class="text-center py-5">
            <i class="ri-image-line" style="font-size: 2.5rem; color: #dee2e6;"></i>
            <h5 class="mt-3 text-muted">No images found</h5>
            <p class="text-muted">Upload some images to get started</p>
        </div>
    </div>
@endif
