@extends('admin.layouts.app')

@section('title', 'Attributes Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Attributes
    </h4>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Attributes List</h5>
            <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add New Attribute
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="myTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Values Count</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($attributes as $attribute)
                            <tr>
                                <td><strong>{{ $attribute->id }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="ri-settings-3-line"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $attribute->name }}</h6>
                                            <small class="text-muted">{{ $attribute->slug }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ ucfirst($attribute->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary">{{ $attribute->attribute_values_count }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if($attribute->is_variation)
                                            <span class="badge bg-label-success">Variation</span>
                                        @endif
                                        @if($attribute->is_filterable)
                                            <span class="badge bg-label-warning">Filter</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($attribute->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $attribute->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="ri-more-2-line"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin.attributes.show', $attribute->id) }}">
                                                <i class="ri-eye-line me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.attributes.edit', $attribute->id) }}">
                                                <i class="ri-pencil-line me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <button type="button" class="dropdown-item text-primary" onclick="manageValues({{ $attribute->id }}, '{{ $attribute->name }}')">
                                                <i class="ri-list-check me-1"></i> Manage Values
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('admin.attributes.destroy', $attribute->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this attribute?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No attributes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $attributes->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Manage Values Modal -->
<div class="modal fade" id="manageValuesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Values for <span id="attributeName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6>Attribute Values</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addNewValue()">
                        <i class="ri-add-line me-1"></i> Add Value
                    </button>
                </div>
                <div id="valuesList">
                    <!-- Values will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Value Modal -->
<div class="modal fade" id="valueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="valueModalTitle">Add New Value</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="valueForm">
                <div class="modal-body">
                    <input type="hidden" id="valueId" name="id">
                    <input type="hidden" id="attributeId" name="attribute_id">
                    
                    <div class="mb-3">
                        <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="value" name="value" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color_code" class="form-label">Color Code (for color attributes)</label>
                        <input type="color" class="form-control" id="color_code" name="color_code">
                    </div>
                    
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL (for image attributes)</label>
                        <input type="url" class="form-control" id="image_url" name="image_url">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Value</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentAttributeId = null;

function manageValues(attributeId, attributeName) {
    currentAttributeId = attributeId;
    document.getElementById('attributeName').textContent = attributeName;
    
    // Load values
    loadAttributeValues(attributeId);
    
    // Show modal
    new bootstrap.Modal(document.getElementById('manageValuesModal')).show();
}

function loadAttributeValues(attributeId) {
    fetch(`{{ url('/admin/attributes') }}/${attributeId}/values`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(values => {
            const valuesList = document.getElementById('valuesList');
            valuesList.innerHTML = '';
            
            if (values.length === 0) {
                valuesList.innerHTML = '<p class="text-muted">No values found.</p>';
                return;
            }
            
            values.forEach(value => {
                const valueRow = createValueRow(value);
                valuesList.appendChild(valueRow);
            });
        })
        .catch(error => {
            console.error('Error loading values:', error);
            document.getElementById('valuesList').innerHTML = '<p class="text-danger">Error loading values. Please try again.</p>';
        });
}

function createValueRow(value) {
    const row = document.createElement('div');
    row.className = 'd-flex justify-content-between align-items-center border rounded p-3 mb-2 value-row';
    row.dataset.valueId = value.id;
    row.dataset.valueData = JSON.stringify(value);
    row.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-3">
                ${value.color_code ? `<div class="rounded-circle" style="width: 20px; height: 20px; background-color: ${value.color_code};"></div>` : ''}
                ${value.image_url ? `<img src="${value.image_url}" alt="${value.value}" style="width: 20px; height: 20px; object-fit: cover;" class="rounded">` : ''}
            </div>
            <div>
                <strong>${value.value}</strong>
                ${value.description ? `<br><small class="text-muted">${value.description}</small>` : ''}
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editValue(${value.id})">
                <i class="ri-pencil-line"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteValue(${value.id})">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    `;
    return row;
}

function addNewValue() {
    document.getElementById('valueModalTitle').textContent = 'Add New Value';
    document.getElementById('valueForm').reset();
    document.getElementById('attributeId').value = currentAttributeId;
    document.getElementById('valueId').value = '';
    new bootstrap.Modal(document.getElementById('valueModal')).show();
}

function editValue(valueId) {
    // Find the value in the current loaded values
    const valuesList = document.getElementById('valuesList');
    const valueRows = valuesList.querySelectorAll('.value-row');
    
    let valueData = null;
    valueRows.forEach(row => {
        if (row.dataset.valueId == valueId) {
            valueData = JSON.parse(row.dataset.valueData);
        }
    });
    
    if (valueData) {
        document.getElementById('valueModalTitle').textContent = 'Edit Value';
        document.getElementById('valueForm').reset();
        document.getElementById('attributeId').value = currentAttributeId;
        document.getElementById('valueId').value = valueData.id;
        document.getElementById('value').value = valueData.value;
        document.getElementById('color_code').value = valueData.color_code || '';
        document.getElementById('image_url').value = valueData.image_url || '';
        document.getElementById('description').value = valueData.description || '';
        document.getElementById('is_active').checked = valueData.is_active;
        
        new bootstrap.Modal(document.getElementById('valueModal')).show();
    } else {
        alert('Value data not found. Please refresh and try again.');
    }
}

function deleteValue(valueId) {
    if (confirm('Are you sure you want to delete this value?')) {
        fetch(`{{ url('/admin/attribute-values') }}/${valueId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadAttributeValues(currentAttributeId);
            } else {
                alert(data.message || 'Error deleting value');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting value');
        });
    }
}

// Handle value form submission
document.getElementById('valueForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    const url = data.id ? `{{ url('/admin/attribute-values') }}/${data.id}` : `{{ url('/admin/attributes') }}/${currentAttributeId}/values`;
    const method = data.id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('valueModal')).hide();
            loadAttributeValues(currentAttributeId);
        } else {
            alert(data.message || 'Error saving value');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving value');
    });
});
</script>
@endsection
