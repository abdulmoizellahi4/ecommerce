// Media Library JavaScript Functionality
const mediaConfig = window.mediaLibraryConfig || {};

let mediaState = {
    selectedIds: new Set(),
    selectedData: [],
    currentTab: 'library',
    previewPanel: null,
    previewEmpty: null,
    selectionCount: null,
    selectButton: null,
    deleteButton: null,
    updateButton: null,
    deselectButton: null,
    altInput: null,
    descriptionInput: null,
    eventsBound: false,
    isUploading: false
};

document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for all elements to be ready
    setTimeout(() => {
        initializeMediaLibraryModal();
    }, 100);
});

function initializeMediaLibraryModal() {
    const modal = document.getElementById('mediaLibraryModal');
    if (!modal) return;

    mediaState.previewPanel = document.getElementById('wpMediaPreviewPanel');
    mediaState.previewEmpty = document.getElementById('wpMediaPreviewEmpty');
    mediaState.selectionCount = document.getElementById('selectedCount');
    mediaState.selectButton = document.getElementById('selectMediaBtn');
    mediaState.deleteButton = document.getElementById('wpMediaDeleteBtn');
    mediaState.updateButton = document.getElementById('wpMediaUpdateBtn');
    mediaState.deselectButton = document.getElementById('wpMediaDeselectBtn');
    mediaState.altInput = document.getElementById('wpMediaAltInput');
    mediaState.descriptionInput = document.getElementById('wpMediaDescriptionInput');

    modal.addEventListener('shown.bs.modal', () => {
        attachGlobalListeners();
        setActiveTab('library');
        loadMediaLibrary();
        if (mediaState.selectButton) mediaState.selectButton.disabled = true;
        clearMediaSelection();
    });

    modal.addEventListener('hidden.bs.modal', () => {
        clearMediaSelection();
        mediaState.selectedId = null;
        mediaState.selectedData = null;
        detachItemListeners();
    });
}

function attachGlobalListeners() {
    if (mediaState.eventsBound) return; // prevent duplicate bindings on every modal open

    const libraryTab = document.getElementById('wpMediaLibraryTab');
    const uploadTab = document.getElementById('wpMediaUploadTab');

    if (libraryTab) libraryTab.onclick = () => setActiveTab('library');
    if (uploadTab) uploadTab.onclick = () => setActiveTab('upload');

    const uploadArea = document.getElementById('uploadArea');
    const mediaUploadInput = document.getElementById('mediaUploadInput');
    const browseBtn = document.getElementById('wpMediaUploadBrowse');
    const modalBody = document.getElementById('mediaLibraryBody');
    const dragOverlay = document.getElementById('dragOverlay');

    // Add drag and drop to entire modal body
    if (modalBody && dragOverlay) {
        modalBody.addEventListener('dragover', handleModalDragOver);
        modalBody.addEventListener('dragleave', handleModalDragLeave);
        modalBody.addEventListener('drop', handleModalDrop);
    }

    if (uploadArea) {
        uploadArea.addEventListener('dragover', handleUploadDragOver);
        uploadArea.addEventListener('dragleave', handleUploadDragLeave);
        uploadArea.addEventListener('drop', handleUploadDrop);
    }

    if (browseBtn && mediaUploadInput) {
        browseBtn.addEventListener('click', () => mediaUploadInput.click());
        mediaUploadInput.addEventListener('change', handleUploadSelect);
    }

    const urlForm = document.getElementById('urlUploadForm');
    if (urlForm) urlForm.addEventListener('submit', handleUrlUpload);

    const searchInput = document.getElementById('mediaSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadMediaLibrary(), 400);
        });
    }

    ['dateFrom', 'dateTo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', loadMediaLibrary);
    });

    if (mediaState.deselectButton) mediaState.deselectButton.onclick = clearMediaSelection;
    if (mediaState.deleteButton) mediaState.deleteButton.onclick = deleteSelectedMedia;
    if (mediaState.updateButton) mediaState.updateButton.onclick = updateSelectedMedia;
    if (mediaState.selectButton) mediaState.selectButton.onclick = insertSelectedMedia;

    mediaState.eventsBound = true;
}

function setActiveTab(tab) {
    mediaState.currentTab = tab;
    const modal = document.querySelector('.wp-media-modal');
    if (modal) modal.setAttribute('data-active-tab', tab);

    document.querySelectorAll('.wp-media-tab').forEach(button => {
        button.classList.toggle('active', button.dataset.tab === tab);
    });

    const librarySection = document.getElementById('wpMediaLibrarySection');
    const uploadSection = document.getElementById('wpMediaUploadSection');
    if (librarySection) librarySection.hidden = tab !== 'library';
    if (uploadSection) uploadSection.hidden = tab !== 'upload';

    if (tab === 'library') {
        loadMediaLibrary();
    }
}

function loadMediaLibrary(page = 1) {
    const search = document.getElementById('mediaSearch')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';

    const params = new URLSearchParams({
        page,
        search,
        date_from: dateFrom,
        date_to: dateTo
    });

    const loading = document.getElementById('mediaLoading');
    const grid = document.getElementById('mediaGrid');
    const pagination = document.getElementById('mediaPagination');

    if (loading) loading.style.display = 'flex';
    if (grid) grid.innerHTML = '<div class="wp-media-loading"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    if (pagination) pagination.innerHTML = '';

    fetch(`${mediaConfig.libraryUrl}?${params}`)
        .then(res => res.json())
        .then(data => {
            if (grid && data.html) grid.innerHTML = data.html;
            if (pagination && data.pagination) pagination.innerHTML = data.pagination;
            bindMediaItems();
        })
        .catch(error => {
            console.error('Error loading media library:', error);
            showMediaNotification('Failed to load media items', 'error');
        })
        .finally(() => {
            if (loading) loading.style.display = 'none';
        });
}

function bindMediaItems() {
    const container = document.getElementById('mediaGrid');
    if (!container) return;

    container.querySelectorAll('.wp-media-item').forEach(item => {
        item.addEventListener('click', () => toggleMediaItem(item));
    });
}

function toggleMediaItem(item) {
    const mediaId = item.dataset.mediaId;
    const isSelected = item.classList.contains('selected');

    if (isSelected) {
        item.classList.remove('selected');
        mediaState.selectedIds.delete(mediaId);
    } else {
        item.classList.add('selected');
        mediaState.selectedIds.add(mediaId);
    }

    buildSelectionData();
}

function buildSelectionData() {
    const selectedItems = [];
    document.querySelectorAll('.wp-media-item.selected').forEach(item => {
        selectedItems.push({
            id: item.dataset.mediaId,
            file_url: item.dataset.mediaUrl,
            original_name: item.dataset.mediaName,
            file_size_formatted: item.dataset.mediaSize,
            mime_type: item.dataset.mediaType,
            alt_text: item.dataset.mediaAlt || '',
            description: item.dataset.mediaDescription || '',
            created_at: item.dataset.mediaCreated || ''
        });
    });
    mediaState.selectedData = selectedItems;
    updatePreviewPanel();
    updateSelectionControls();
}

function clearMediaSelection() {
    mediaState.selectedIds.clear();
    mediaState.selectedData = [];
    document.querySelectorAll('.wp-media-item').forEach(el => el.classList.remove('selected'));
    updatePreviewPanel();
    updateSelectionControls();
}

function updatePreviewPanel() {
    if (!mediaState.previewPanel || !mediaState.previewEmpty) return;

    if (!mediaState.selectedData || mediaState.selectedData.length === 0) {
        mediaState.previewPanel.classList.add('d-none');
        mediaState.previewEmpty.classList.remove('d-none');
        if (mediaState.altInput) mediaState.altInput.value = '';
        if (mediaState.descriptionInput) mediaState.descriptionInput.value = '';
        return;
    }

    const primary = mediaState.selectedData[0];
    mediaState.previewPanel.classList.remove('d-none');
    mediaState.previewEmpty.classList.add('d-none');

    const previewImage = document.getElementById('wpMediaPreviewImage');
    const previewTitle = document.getElementById('wpMediaPreviewTitle');
    const previewDetails = document.getElementById('wpMediaPreviewDetails');
    
    if (previewImage) previewImage.src = primary.file_url;
    if (previewImage) previewImage.alt = primary.alt_text || primary.original_name;
    if (previewTitle) previewTitle.textContent = primary.original_name;
    if (previewDetails) previewDetails.innerHTML = `
        <div>${primary.file_size_formatted || ''}</div>
        <div>${primary.mime_type || ''}</div>
        <div>${primary.created_at || ''}</div>
        <div class="mt-2 fw-semibold">Selected: ${mediaState.selectedData.length}</div>
    `;

    if (mediaState.altInput) mediaState.altInput.value = primary.alt_text || '';
    if (mediaState.descriptionInput) mediaState.descriptionInput.value = primary.description || '';
}

function updateSelectionControls() {
    const count = mediaState.selectedData.length;
    if (mediaState.selectionCount) mediaState.selectionCount.textContent = count;
    const disabled = count === 0;
    if (mediaState.selectButton) mediaState.selectButton.disabled = disabled;
    if (mediaState.deleteButton) mediaState.deleteButton.disabled = disabled;
    if (mediaState.updateButton) mediaState.updateButton.disabled = disabled;
    if (mediaState.deselectButton) mediaState.deselectButton.disabled = disabled;
}

// Modal-wide drag and drop handlers
function handleModalDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const dragOverlay = document.getElementById('dragOverlay');
    const modal = document.querySelector('.wp-media-modal');
    
    if (dragOverlay) dragOverlay.classList.add('active');
    if (modal) modal.classList.add('drag-over');
}

function handleModalDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Only hide overlay if we're actually leaving the modal
    if (!e.currentTarget.contains(e.relatedTarget)) {
        const dragOverlay = document.getElementById('dragOverlay');
        const modal = document.querySelector('.wp-media-modal');
        
        if (dragOverlay) dragOverlay.classList.remove('active');
        if (modal) modal.classList.remove('drag-over');
    }
}

function handleModalDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const dragOverlay = document.getElementById('dragOverlay');
    const modal = document.querySelector('.wp-media-modal');
    
    if (dragOverlay) dragOverlay.classList.remove('active');
    if (modal) modal.classList.remove('drag-over');
    
    const files = Array.from(e.dataTransfer.files).filter(file => 
        file.type.startsWith('image/')
    );
    
    if (files.length > 0) {
        uploadMediaFiles(files);
    } else {
        showMediaNotification('Please drop only image files', 'error');
    }
}

// Original upload area handlers
function handleUploadDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('dragover');
}

function handleUploadDragLeave(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
}

function handleUploadDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
    const files = Array.from(e.dataTransfer.files);
    if (files.length) uploadMediaFiles(files);
}

function handleUploadSelect(e) {
    const files = Array.from(e.target.files);
    if (files.length) uploadMediaFiles(files);
    e.target.value = '';
}

function uploadMediaFiles(files) {
    if (mediaState.isUploading) return;
    mediaState.isUploading = true;
    const formData = new FormData();
    files.forEach(file => formData.append('files[]', file));

    const progress = document.getElementById('uploadProgress');
    const progressBar = progress?.querySelector('.progress-bar');

    if (progress) progress.style.display = 'block';
    if (progressBar) progressBar.style.width = '0%';

    fetch(mediaConfig.uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': mediaConfig.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: formData
    })
        .then(async (res) => {
            const status = res.status;
            let data = null;
            try {
                data = await res.json();
            } catch (e) {
                // Non-JSON response (e.g., 419/302 HTML). We'll treat below.
            }

            // Success path: prefer explicit success flag, but accept uploaded items fallback
            if (res.ok && data && (data.success === true || (Array.isArray(data.uploaded) && data.uploaded.length > 0))) {
                const uploadedFirst = Array.isArray(data.uploaded) && data.uploaded.length ? data.uploaded[0] : null;
                showMediaNotification(data.message || 'Files uploaded successfully', 'success');
                setActiveTab('library');
                loadMediaLibrary();
                if (uploadedFirst) selectMediaFromResponse(uploadedFirst);
                return;
            }

            // Handle validation/partial failures with helpful message
            if (data && Array.isArray(data.errors) && data.errors.length) {
                const firstErr = data.errors[0]?.error || 'Upload failed';
                showMediaNotification(firstErr, 'error');
                return;
            }

            // Session/CSRF issues
            if (status === 419 || status === 401) {
                showMediaNotification('Session expired. Please refresh the page and try again.', 'error');
                return;
            }

            // Generic error fallback
            showMediaNotification((data && data.message) || 'Upload failed', 'error');
        })
        .catch(error => {
            console.error('Upload error:', error);
            showMediaNotification('Upload failed', 'error');
        })
        .finally(() => {
            if (progress) progress.style.display = 'none';
            mediaState.isUploading = false;
        });
}

function selectMediaFromResponse(media) {
    mediaState.selectedId = String(media.id);
    mediaState.selectedData = [{
        id: media.id,
        file_url: media.file_url,
        original_name: media.original_name,
        file_size_formatted: media.file_size_formatted || 'Unknown',
        mime_type: media.mime_type,
        alt_text: media.alt_text || '',
        description: media.description || '',
        created_at: media.created_at || new Date().toISOString()
    }];
    updatePreviewPanel();
    updateSelectionControls();
}

function handleUrlUpload(e) {
    e.preventDefault();

    const url = document.getElementById('imageUrl').value.trim();
    const altText = document.getElementById('urlAltText').value.trim();

    if (!url) {
        showMediaNotification('Please enter a valid URL', 'error');
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Adding...';
    submitBtn.disabled = true;

    fetch(mediaConfig.uploadFromUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': mediaConfig.csrf
        },
        body: JSON.stringify({
            url,
            alt_text: altText,
            description: ''
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.media) {
                showMediaNotification('Image added from URL', 'success');
                document.getElementById('imageUrl').value = '';
                document.getElementById('urlAltText').value = '';
                setActiveTab('library');
                loadMediaLibrary();
                selectMediaFromResponse(data.media);
            } else {
                showMediaNotification(data.message || 'Failed to add image', 'error');
            }
        })
        .catch(() => showMediaNotification('Failed to add image from URL', 'error'))
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function updateSelectedMedia() {
    if (!mediaState.selectedId) return;

    const payload = {
        alt_text: mediaState.altInput?.value || '',
        description: mediaState.descriptionInput?.value || ''
    };

    fetch(mediaConfig.updateUrl.replace(':id', mediaState.selectedId), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': mediaConfig.csrf
        },
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.media) {
                showMediaNotification('Media details updated', 'success');
                mediaState.selectedData.alt_text = data.media.alt_text;
                mediaState.selectedData.description = data.media.description;
                updatePreviewPanel();
                loadMediaLibrary();
            } else {
                showMediaNotification(data.message || 'Failed to update media', 'error');
            }
        })
        .catch(() => showMediaNotification('Failed to update media', 'error'));
}

function deleteSelectedMedia() {
    if (!mediaState.selectedId) return;

    if (!confirm('Are you sure you want to delete this media item?')) {
        return;
    }

    fetch(mediaConfig.deleteUrl.replace(':id', mediaState.selectedId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': mediaConfig.csrf
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMediaNotification('Media deleted successfully', 'success');
                clearMediaSelection();
                loadMediaLibrary();
            } else {
                showMediaNotification(data.message || 'Failed to delete media', 'error');
            }
        })
        .catch(() => showMediaNotification('Failed to delete media', 'error'));
}

function insertSelectedMedia() {
    if (!mediaState.selectedData || mediaState.selectedData.length === 0) {
        showMediaNotification('Please select at least one image', 'error');
        return;
    }

    if (typeof selectMediaFromLibraryItems === 'function') {
        selectMediaFromLibraryItems(mediaState.selectedData);
    }

    const modalElement = document.getElementById('mediaLibraryModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if (modal) modal.hide();
}

function showMediaNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 280px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 4000);
}
