@extends('admin.layouts.app')

@section('title', 'Add New Attribute')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Attributes /</span> Add New
    </h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Attribute Information</h5>
                    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">Back to Attributes</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.attributes.store') }}" method="POST" id="attributeForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="name" class="form-label">Attribute Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}" placeholder="auto-generated">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to auto-generate from name</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="type" class="form-label">Attribute Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    @foreach($typeOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_required" name="is_required" 
                                           value="1" {{ old('is_required') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_required">
                                        Required
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3 col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_filterable" name="is_filterable" 
                                           value="1" {{ old('is_filterable', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_filterable">
                                        Filterable
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3 col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_variation" name="is_variation" 
                                           value="1" {{ old('is_variation', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_variation">
                                        Used for Variations
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        <!-- Attribute Values Section -->
                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Attribute Values</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addValueRow()">
                                    <i class="ri-add-line me-1"></i> Add Value
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="valuesContainer">
                                    <!-- Values will be added here -->
                                </div>
                                <small class="text-muted">Add at least one value for this attribute.</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri-save-line me-1"></i> Create Attribute
                            </button>
                            <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attribute Types</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Dropdown Select</h6>
                        <p class="small text-muted">Standard dropdown with predefined values</p>
                    </div>
                    <div class="mb-3">
                        <h6>Color Swatch</h6>
                        <p class="small text-muted">Color picker with hex codes</p>
                    </div>
                    <div class="mb-3">
                        <h6>Image Swatch</h6>
                        <p class="small text-muted">Image-based selection</p>
                    </div>
                    <div class="mb-3">
                        <h6>Text Input</h6>
                        <p class="small text-muted">Free text input field</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let valueIndex = 0;

// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value || slugField.value === '') {
        slugField.value = this.value.toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
    }
});

// Add value row
function addValueRow() {
    const container = document.getElementById('valuesContainer');
    const valueRow = document.createElement('div');
    valueRow.className = 'row mb-3 value-row';
    valueRow.innerHTML = `
        <div class="col-md-4">
            <label class="form-label">Value <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="values[${valueIndex}][value]" required>
        </div>
        <div class="col-md-3 color-field">
            <label class="form-label">Color Code</label>
            <input type="color" class="form-control color-input" name="values[${valueIndex}][color_code]">
        </div>
        <div class="col-md-3 image-field">
            <div class="image-url-section">
                <label class="form-label">Image URL</label>
                <input type="url" class="form-control image-url-input" name="values[${valueIndex}][image_url]">
            </div>
            <div class="image-upload-section" style="display: none;">
                <label class="form-label">Upload Image</label>
                <input type="file" class="form-control image-upload-input" name="values[${valueIndex}][image_upload]" accept="image/*" onchange="previewImage(this)">
                <small class="text-muted">Upload an image file</small>
                <div class="image-preview mt-2" style="display: none;">
                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-outline-danger d-block" onclick="removeValueRow(this)">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="values[${valueIndex}][description]" rows="2"></textarea>
        </div>
    `;
    container.appendChild(valueRow);
    
    // Apply current attribute type settings to the new row
    applyAttributeTypeSettings();
    
    valueIndex++;
}

// Remove value row
function removeValueRow(button) {
    button.closest('.value-row').remove();
}

// Preview uploaded image
function previewImage(input) {
    const preview = input.closest('.image-upload-section').querySelector('.image-preview');
    const previewImg = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// Add initial value row
document.addEventListener('DOMContentLoaded', function() {
    addValueRow();
});

// Apply attribute type settings to all value rows
function applyAttributeTypeSettings() {
    const selectedType = document.getElementById('type').value;
    const colorFields = document.querySelectorAll('.color-field');
    const imageFields = document.querySelectorAll('.image-field');
    const imageUrlSections = document.querySelectorAll('.image-url-section');
    const imageUploadSections = document.querySelectorAll('.image-upload-section');
    const imageUploadInputs = document.querySelectorAll('.image-upload-input');
    const colorInputs = document.querySelectorAll('.color-input');
    
    if (selectedType === 'color') {
        // Show color fields, hide image fields
        colorFields.forEach(field => {
            field.style.display = 'block';
        });
        colorInputs.forEach(input => {
            input.required = true;
        });
        imageFields.forEach(field => {
            field.style.display = 'none';
        });
    } else if (selectedType === 'image') {
        // Hide color fields, show image upload fields
        colorFields.forEach(field => {
            field.style.display = 'none';
        });
        colorInputs.forEach(input => {
            input.required = false;
        });
        imageFields.forEach(field => {
            field.style.display = 'block';
        });
        
        // Show image upload section, hide URL section
        imageUrlSections.forEach(section => {
            section.style.display = 'none';
        });
        imageUploadSections.forEach(section => {
            section.style.display = 'block';
        });
        imageUploadInputs.forEach(input => {
            input.required = true;
        });
    } else {
        // Show all fields for other types
        colorFields.forEach(field => {
            field.style.display = 'block';
        });
        colorInputs.forEach(input => {
            input.required = false;
        });
        imageFields.forEach(field => {
            field.style.display = 'block';
        });
        
        // Show URL section, hide upload section
        imageUrlSections.forEach(section => {
            section.style.display = 'block';
        });
        imageUploadSections.forEach(section => {
            section.style.display = 'none';
        });
        imageUploadInputs.forEach(input => {
            input.required = false;
        });
    }
}

// Show/hide fields based on attribute type
document.getElementById('type').addEventListener('change', function() {
    applyAttributeTypeSettings();
});
</script>
@endsection
