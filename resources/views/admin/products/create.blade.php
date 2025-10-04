@extends('admin.layouts.app')

@section('title', 'Add New Product')

<script>
// Global functions for onclick handlers - defined at the top
function showSimpleSection() {
    const simpleFields = document.getElementById('simpleProductFields');
    const variableFields = document.getElementById('variableProductFields');
    const generateVariationsCard = document.getElementById('generateVariationsCard');
    const variationsTableCard = document.getElementById('variationsTableCard');
    
    if (simpleFields) simpleFields.style.display = 'block';
    if (variableFields) variableFields.style.display = 'none';
    if (generateVariationsCard) generateVariationsCard.style.display = 'none';
    if (variationsTableCard) variationsTableCard.style.display = 'none';
}

function showVariableSection() {
    const simpleFields = document.getElementById('simpleProductFields');
    const variableFields = document.getElementById('variableProductFields');
    const variationsTableCard = document.getElementById('variationsTableCard');
    
    if (simpleFields) simpleFields.style.display = 'none';
    if (variableFields) variableFields.style.display = 'block';
    if (variationsTableCard) variationsTableCard.style.display = 'none';
}

// Global variables for Shopify-style variants
let optionCounter = 0;
let allVariants = [];

// Shopify-style option management
function addOption() {
    const container = document.getElementById('optionsContainer');
    const currentOptionId = optionCounter;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-group mb-3 p-3 border rounded';
    optionDiv.setAttribute('data-option-id', currentOptionId);
    optionDiv.innerHTML = `
        <div class="d-flex align-items-center mb-2">
            <i class="ri-drag-move-2-line text-muted me-2" style="cursor: move;"></i>
            <label class="form-label mb-0 fw-bold">Option name</label>
        </div>
        <div class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control option-name" placeholder="e.g., Color, Size" onchange="updateVariants()" list="existing-attributes-${currentOptionId}">
                <datalist id="existing-attributes-${currentOptionId}">
                    <!-- Existing attributes will be loaded here -->
                </datalist>
                <button type="button" class="btn btn-outline-secondary" onclick="loadExistingAttributes(${currentOptionId})" title="Load saved attributes">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
            <small class="text-muted d-block mt-1">Type new or select from saved attributes</small>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="ri-drag-move-2-line text-muted me-2" style="cursor: move;"></i>
            <label class="form-label mb-0 fw-bold">Option values</label>
        </div>
        <div class="option-values mb-3">
            <div class="d-flex align-items-center mb-2 option-value-wrapper">
                <input type="text" class="form-control option-value me-2" placeholder="Add value" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
                <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOption(this)">
                Delete
            </button>
            <button type="button" class="btn btn-sm btn-dark" onclick="doneOption(this)">
                Done
            </button>
        </div>
    `;
    
    container.appendChild(optionDiv);
    optionCounter++;
    
    // Load existing attributes
    loadExistingAttributes(currentOptionId);
    
    // Focus on the option name input
    const optionNameInput = optionDiv.querySelector('.option-name');
    optionNameInput.focus();
    
    // Add event listener for attribute selection
    optionNameInput.addEventListener('change', function() {
        const selectedValue = this.value;
        if (selectedValue) {
            loadAttributeValues(currentOptionId, selectedValue);
        }
    });
}

// Load existing attributes from database
function loadExistingAttributes(optionId) {
    console.log('Loading existing attributes for option:', optionId);
    
    fetch('{{ route("admin.attributes.index") }}?ajax=1', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Attributes response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Attributes data received:', data);
        const datalist = document.getElementById(`existing-attributes-${optionId}`);
        if (datalist && data.success && data.attributes) {
            datalist.innerHTML = '';
            data.attributes.forEach(attr => {
                const option = document.createElement('option');
                option.value = attr.name;
                option.setAttribute('data-attribute-id', attr.id);
                datalist.appendChild(option);
            });
            console.log(`Loaded ${data.attributes.length} attributes for option ${optionId}`);
        } else {
            console.log('No attributes found or invalid response');
        }
    })
    .catch(error => {
        console.error('Error loading attributes:', error);
        console.log('Attributes loaded from defaults');
    });
}

// Auto-save attribute and values when option is completed
function doneOption(button) {
    console.log('=== doneOption function called ===');
    const optionGroup = button.closest('.option-group');
    const optionName = optionGroup.querySelector('.option-name').value.trim();
    const optionValues = Array.from(optionGroup.querySelectorAll('.option-value')).map(input => input.value.trim()).filter(value => value);
    
    console.log('Option Name:', optionName);
    console.log('Option Values:', optionValues);
    
    if (!optionName) {
        showNotification('❌ Please enter an option name', 'error');
        return;
    }
    
    if (optionValues.length === 0) {
        showNotification('❌ Please add at least one option value', 'error');
        return;
    }
    
    // First check if attribute already exists
    checkAttributeExists(optionName, optionValues, () => {
        // Success callback - mark as completed
        optionGroup.classList.add('completed');
        button.textContent = 'Edit';
        button.setAttribute('onclick', 'editOption(this)');
        
        updateVariants();
        
        // Show success message
        showNotification(`✅ "${optionName}" ready with ${optionValues.length} values!`, 'success');
    });
}

// Check if attribute already exists before saving
function checkAttributeExists(attributeName, values, successCallback = null) {
    console.log('=== checkAttributeExists function called ===');
    console.log('Checking if attribute exists:', attributeName);
    
    // Fetch existing attributes to check
    fetch('{{ route("admin.attributes.index") }}?ajax=1', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Existing attributes data:', data);
        
        if (data.success && data.attributes) {
            // Check if attribute already exists
            const existingAttribute = data.attributes.find(attr => 
                attr.name.toLowerCase() === attributeName.toLowerCase()
            );
            
            if (existingAttribute) {
                console.log('Attribute already exists:', existingAttribute);
                showNotification(`✅ Using existing "${attributeName}" attribute`, 'info');
                
                // Check if we need to add new values
                checkAndAddNewValues(existingAttribute.id, values, () => {
                    if (successCallback) successCallback();
                });
            } else {
                console.log('Attribute does not exist, creating new one');
                showNotification(`🔄 Creating new "${attributeName}" attribute...`, 'info');
                
                // Create new attribute
                saveAttributeAndValues(attributeName, values, () => {
                    if (successCallback) successCallback();
                });
            }
        } else {
            console.log('No attributes data received, creating new attribute');
            saveAttributeAndValues(attributeName, values, () => {
                if (successCallback) successCallback();
            });
        }
    })
    .catch(error => {
        console.error('Error checking existing attributes:', error);
        console.log('Creating new attribute due to error');
        saveAttributeAndValues(attributeName, values, () => {
            if (successCallback) successCallback();
        });
    });
}

// Check and add new values to existing attribute
function checkAndAddNewValues(attributeId, newValues, successCallback = null) {
    console.log('=== checkAndAddNewValues function called ===');
    console.log('Attribute ID:', attributeId);
    console.log('New values:', newValues);
    
    // Fetch existing values for this attribute
    fetch(`{{ route("admin.attributes.values", ":id") }}`.replace(':id', attributeId), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Existing values data:', data);
        
        if (data.success && data.values) {
            const existingValues = data.values.map(v => v.value.toLowerCase());
            const valuesToAdd = newValues.filter(value => 
                !existingValues.includes(value.toLowerCase())
            );
            
            if (valuesToAdd.length > 0) {
                console.log('Adding new values:', valuesToAdd);
                showNotification(`🔄 Adding ${valuesToAdd.length} new values to existing attribute...`, 'info');
                
                // Add new values to existing attribute
                addValuesToAttribute(attributeId, valuesToAdd, () => {
                    if (successCallback) successCallback();
                });
            } else {
                console.log('All values already exist, no need to add');
                showNotification(`✅ All values already exist for this attribute`, 'success');
                if (successCallback) successCallback();
            }
        } else {
            console.log('No existing values found, adding all values');
            addValuesToAttribute(attributeId, newValues, () => {
                if (successCallback) successCallback();
            });
        }
    })
    .catch(error => {
        console.error('Error checking existing values:', error);
        console.log('Adding all values due to error');
        addValuesToAttribute(attributeId, newValues, () => {
            if (successCallback) successCallback();
        });
    });
}

// Add values to existing attribute
function addValuesToAttribute(attributeId, values, successCallback = null) {
    console.log('=== addValuesToAttribute function called ===');
    console.log('Adding values to attribute ID:', attributeId);
    console.log('Values to add:', values);
    
    const formData = new FormData();
    formData.append('attribute_id', attributeId);
    
    // Add values
    values.forEach((value, index) => {
        formData.append(`values[${index}][value]`, value);
    });
    
    fetch('{{ route("admin.attributes.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Add values response:', data);
        if (data.success) {
            showNotification(`✅ Added ${values.length} new values successfully!`, 'success');
        } else {
            showNotification(`⚠️ Some values may already exist`, 'warning');
        }
        
        if (successCallback) successCallback();
    })
    .catch(error => {
        console.error('Error adding values:', error);
        showNotification(`❌ Error adding values`, 'error');
        if (successCallback) successCallback();
    });
}

// Save attribute and values to database
function saveAttributeAndValues(attributeName, values, successCallback = null) {
    console.log('=== saveAttributeAndValues function called ===');
    console.log('Attribute Name:', attributeName);
    console.log('Values:', values);
    
    const formData = new FormData();
    formData.append('name', attributeName);
    formData.append('type', 'text');
    formData.append('is_active', '1');
    formData.append('is_variation', '1');
    formData.append('is_filterable', '1');
    
    // Add values
    values.forEach((value, index) => {
        formData.append(`values[${index}][value]`, value);
        console.log(`Added value[${index}][value]:`, value);
    });
    
    console.log('FormData contents:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    // Show loading notification
    showNotification(`Saving "${attributeName}" with ${values.length} values...`, 'info');
    
    console.log('Making fetch request to:', '{{ route("admin.attributes.store") }}');
    
    fetch('{{ route("admin.attributes.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification(`✅ "${attributeName}" saved successfully with ${values.length} values!`, 'success');
            console.log(`Attribute "${attributeName}" saved successfully with ${values.length} values`);
            
            // Refresh all attribute dropdowns
            refreshAllAttributeDropdowns();
            
            // Call success callback if provided
            if (successCallback && typeof successCallback === 'function') {
                successCallback();
            }
        } else {
            showNotification(`⚠️ "${attributeName}" already exists or error occurred`, 'warning');
            console.log('Attribute already exists or error occurred');
            
            // Still call callback for existing attributes (they can be used)
            if (successCallback && typeof successCallback === 'function') {
                successCallback();
            }
        }
    })
    .catch(error => {
        showNotification(`❌ Error saving "${attributeName}"`, 'error');
        console.error('Error saving attribute:', error);
    });
}

// Refresh all attribute dropdowns
function refreshAllAttributeDropdowns() {
    const optionGroups = document.querySelectorAll('.option-group');
    optionGroups.forEach(group => {
        const optionId = group.getAttribute('data-option-id');
        if (optionId) {
            loadExistingAttributes(optionId);
        }
    });
}

// Load attribute values when user selects from dropdown
function loadAttributeValues(optionId, attributeName) {
    console.log('Loading attribute values for:', attributeName);
    
    // Find the attribute by name
    fetch('{{ route("admin.attributes.index") }}?ajax=1', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Attributes data for values:', data);
        if (data.success && data.attributes) {
            const attribute = data.attributes.find(attr => attr.name === attributeName);
            if (attribute) {
                console.log('Found attribute:', attribute);
                // Load attribute values
                fetch(`{{ route("admin.attributes.values", ":id") }}`.replace(':id', attribute.id), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(values => {
                    console.log('Attribute values received:', values);
                    if (values.success && values.values && values.values.length > 0) {
                        const optionGroup = document.querySelector(`[data-option-id="${optionId}"]`);
                        const optionValuesContainer = optionGroup.querySelector('.option-values');
                        
                        // Clear existing values
                        optionValuesContainer.innerHTML = '';
                        
                        // Add loaded values
                        values.values.forEach(value => {
                            const valueWrapper = document.createElement('div');
                            valueWrapper.className = 'd-flex align-items-center mb-2 option-value-wrapper';
                            valueWrapper.innerHTML = `
                                <input type="text" class="form-control option-value me-2" placeholder="Add value" value="${value.value}" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            `;
                            optionValuesContainer.appendChild(valueWrapper);
                        });
                        
                        updateVariants();
                        console.log(`Loaded ${values.values.length} values for attribute "${attributeName}"`);
                    } else {
                        console.log('No values found for attribute:', attributeName);
                    }
                })
                .catch(error => {
                    console.error('Error loading attribute values:', error);
                });
            } else {
                console.log('Attribute not found:', attributeName, '- This is a new attribute, values will be added manually');
                // This is a new attribute, don't try to load values
                // User will add values manually
            }
        } else {
            console.log('No attributes data received');
        }
    })
    .catch(error => {
        console.error('Error loading attributes for values:', error);
    });
}

// Simple notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 5000);
}

// Test function to manually save data
function testSaveAttribute() {
    console.log('=== Testing checkAttributeExists function ===');
    checkAttributeExists('Test Brand', ['Nike', 'Adidas', 'Puma'], () => {
        console.log('Test attribute check completed!');
    });
}

// Test function to check existing attributes
function testExistingAttribute() {
    console.log('=== Testing with existing attribute ===');
    checkAttributeExists('Color', ['Red', 'Blue', 'Green'], () => {
        console.log('Existing attribute test completed!');
    });
}

// Test function to check current form data
function testCurrentData() {
    console.log('=== Testing current form data ===');
    const optionGroups = document.querySelectorAll('.option-group');
    optionGroups.forEach((group, index) => {
        const optionName = group.querySelector('.option-name')?.value;
        const optionValues = Array.from(group.querySelectorAll('.option-value')).map(input => input.value).filter(value => value.trim());
        console.log(`Option Group ${index + 1}:`);
        console.log('  Name:', optionName);
        console.log('  Values:', optionValues);
    });
}

// Quick test function for custom attributes
function testCustomAttribute() {
    console.log('=== Testing Custom Attribute Creation ===');
    
    // Create a test option
    const container = document.getElementById('optionsContainer');
    const currentOptionId = optionCounter;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-group mb-3 p-3 border rounded';
    optionDiv.setAttribute('data-option-id', currentOptionId);
    
    optionDiv.innerHTML = `
        <div class="d-flex align-items-center mb-2">
            <i class="ri-drag-move-2-line text-muted me-2" style="cursor: move;"></i>
            <label class="form-label mb-0 fw-bold">Option name</label>
        </div>
        <div class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control option-name" placeholder="e.g., Color, Size" value="Test" onchange="updateVariants()" list="existing-attributes-${currentOptionId}">
                <datalist id="existing-attributes-${currentOptionId}">
                    <!-- Existing attributes will be loaded here -->
                </datalist>
                <button type="button" class="btn btn-outline-secondary" onclick="loadExistingAttributes(${currentOptionId})" title="Load saved attributes">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
            <small class="text-muted d-block mt-1">Type new or select from saved attributes</small>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="ri-drag-move-2-line text-muted me-2" style="cursor: move;"></i>
            <label class="form-label mb-0 fw-bold">Option values</label>
        </div>
        <div class="option-values mb-3">
            <div class="d-flex align-items-center mb-2 option-value-wrapper">
                <input type="text" class="form-control option-value me-2" placeholder="Add value" value="Value1" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
                <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
            <div class="d-flex align-items-center mb-2 option-value-wrapper">
                <input type="text" class="form-control option-value me-2" placeholder="Add value" value="Value2" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
                <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOption(this)">
                Delete
            </button>
            <button type="button" class="btn btn-sm btn-dark" onclick="doneOption(this)">
                Done
            </button>
        </div>
    `;
    
    container.appendChild(optionDiv);
    optionCounter++;
    
    // Load existing attributes
    loadExistingAttributes(currentOptionId);
    
    console.log('Test custom attribute option created!');
}

// Simple test function to create "Test" attribute
function createTestAttribute() {
    console.log('=== Creating Test Attribute ===');
    saveAttributeAndValues('Test', ['Value1', 'Value2'], () => {
        console.log('Test attribute created successfully!');
        showNotification('✅ Test attribute created successfully!', 'success');
    });
}

// Test function for variant image functionality
function testVariantImage() {
    console.log('=== Testing Variant Image Functionality ===');
    
    // Create a test variant
    const container = document.getElementById('optionsContainer');
    if (container.children.length === 0) {
        console.log('No variants available. Creating test variant...');
        addOption();
        
        // Fill with test data
        setTimeout(() => {
            const optionName = container.querySelector('.option-name');
            const optionValues = container.querySelectorAll('.option-value');
            
            if (optionName) optionName.value = 'Test Color';
            if (optionValues[0]) optionValues[0].value = 'Red';
            if (optionValues[1]) optionValues[1].value = 'Blue';
            
            // Click done to generate variants
            const doneBtn = container.querySelector('button[onclick*="doneOption"]');
            if (doneBtn) doneBtn.click();
            
            console.log('Test variant created! Now you can test image upload.');
        }, 500);
    } else {
        console.log('Variants already exist. You can test image upload by clicking on variant images.');
    }
}

// Test function for stock functionality
function testStockFunctionality() {
    console.log('=== Testing Stock Functionality ===');
    
    // Toggle stock for first variant
    const stockBtn = document.getElementById('stockBtn_0');
    if (stockBtn) {
        console.log('Toggling stock for first variant...');
        toggleStockField(0);
        
        // After 2 seconds, toggle it back to test both directions
        setTimeout(() => {
            console.log('Toggling stock back to hidden...');
            toggleStockField(0);
        }, 2000);
    } else {
        console.log('No variants available. Create variants first.');
    }
}

// Simple form submission test
function testSimpleFormSubmission() {
    console.log('=== SIMPLE FORM SUBMISSION TEST ===');
    
    // Fill required fields
    const nameField = document.getElementById('name');
    const categoryField = document.getElementById('category_id');
    const priceField = document.getElementById('price');
    
    if (nameField) nameField.value = 'Test Product';
    if (categoryField) categoryField.value = '1';
    if (priceField) priceField.value = '299.99';
    
    console.log('Fields filled:', {
        name: nameField ? nameField.value : 'Not found',
        category: categoryField ? categoryField.value : 'Not found',
        price: priceField ? priceField.value : 'Not found'
    });
    
    // Try to submit form
    const form = document.querySelector('form[action*="products"]');
    if (form) {
        console.log('Attempting form submission...');
        form.submit();
    } else {
        console.error('Form not found');
    }
}

// Test function to check form submission
function testFormSubmissionDebug() {
    console.log('=== FORM SUBMISSION DEBUG TEST ===');
    
    // Check if form exists
    const form = document.querySelector('form[action*="products"]');
    console.log('Form found:', !!form);
    if (form) {
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        console.log('Form enctype:', form.enctype);
    }
    
    // Check if event listener is attached
    console.log('Form event listeners:', form ? form.onsubmit : 'No form');
    
    // Check required fields
    const productName = document.getElementById('name');
    const categoryId = document.getElementById('category_id');
    const price = document.getElementById('price');
    
    console.log('Required fields:', {
        name: productName ? productName.value : 'Not found',
        category: categoryId ? categoryId.value : 'Not found',
        price: price ? price.value : 'Not found'
    });
    
    // Check product type
    const productType = document.querySelector('input[name="product_type"]:checked');
    console.log('Product type:', productType ? productType.value : 'None selected');
    
    // Test form submission manually
    if (form) {
        console.log('Testing manual form submission...');
        try {
            form.submit();
            console.log('Form submitted successfully');
        } catch (error) {
            console.error('Form submission error:', error);
        }
    }
    
    console.log('=== DEBUG TEST COMPLETED ===');
}

// Test function for form submission
function testFormSubmission() {
    console.log('=== Testing Form Submission ===');
    
    // Test variant data collection
    const variants = collectVariantData();
    console.log('Collected variants:', variants);
    
    // Test adding data to form
    addVariantDataToForm();
    
    // Test adding images to form
    addVariantImagesToForm();
    
    // Show form data preview
    const form = document.querySelector('form[action*="products"]');
    if (form) {
        const formData = new FormData(form);
        console.log('Form data preview:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
    }
    
    showNotification('✅ Form submission test completed! Check console for details.', 'success');
}

// Test function for complete workflow
function testCompleteWorkflow() {
    console.log('=== Testing Complete Workflow ===');
    
    // Step 1: Create test options
    console.log('Step 1: Creating test options...');
    addDemoOption('Color', ['Red', 'Blue', 'Green']);
    
    // Step 2: Wait and set prices
    setTimeout(() => {
        console.log('Step 2: Setting prices...');
        const firstPriceField = document.getElementById('price_0');
        if (firstPriceField) {
            firstPriceField.value = '299.99';
            applyPriceToAll(0);
        }
    }, 2000);
    
    // Step 3: Wait and test form submission
    setTimeout(() => {
        console.log('Step 3: Testing form submission...');
        testFormSubmission();
    }, 4000);
}

// Test function for price functionality
function testPriceFunctionality() {
    console.log('=== Testing Price Functionality ===');
    
    // Set a test price for first variant
    const firstPriceField = document.getElementById('price_0');
    if (firstPriceField) {
        console.log('Setting test price for first variant...');
        firstPriceField.value = '299.99';
        
        // Apply to all variants
        setTimeout(() => {
            console.log('Applying price to all variants...');
            applyPriceToAll(0);
        }, 1000);
    } else {
        console.log('No variants available. Create variants first.');
    }
}

// Demo function to quickly add common options
function addDemoOption(optionName, values) {
    console.log('Adding demo option:', optionName, values);
    
    const container = document.getElementById('optionsContainer');
    const currentOptionId = optionCounter;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'option-group mb-3 p-3 border rounded';
    optionDiv.setAttribute('data-option-id', currentOptionId);
    
    // Create option values HTML
    let valuesHtml = '';
    values.forEach((value, index) => {
        valuesHtml += `
            <div class="d-flex align-items-center mb-2 option-value-wrapper">
                <input type="text" class="form-control option-value me-2" placeholder="Add value" value="${value}" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
                <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        `;
    });
    
    optionDiv.innerHTML = `
        <div class="d-flex align-items-center mb-2">
            <i class="ri-drag-move-2-line text-muted me-2" style="cursor: move;"></i>
            <label class="form-label mb-0 fw-bold">Option name</label>
        </div>
        <div class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control option-name" placeholder="e.g., Color, Size" value="${optionName}" onchange="updateVariants()" list="existing-attributes-${currentOptionId}">
                <datalist id="existing-attributes-${currentOptionId}">
                    <!-- Existing attributes will be loaded here -->
                </datalist>
                <button type="button" class="btn btn-outline-secondary" onclick="loadExistingAttributes(${currentOptionId})" title="Load saved attributes">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
            <small class="text-muted d-block mt-1">Type new or select from saved attributes</small>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="ri-drag-move-2-line text-muted me-2" style="cursor: move;"></i>
            <label class="form-label mb-0 fw-bold">Option values</label>
        </div>
        <div class="option-values mb-3">
            ${valuesHtml}
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeOption(this)">
                Delete
            </button>
            <button type="button" class="btn btn-sm btn-dark" onclick="doneOption(this)">
                Done
            </button>
        </div>
    `;
    
    container.appendChild(optionDiv);
    optionCounter++;
    
    // Load existing attributes
    loadExistingAttributes(currentOptionId);
    
    // Auto-save the demo option
    setTimeout(() => {
        saveAttributeAndValues(optionName, values);
    }, 500);
    
    // Show success message
    showNotification(`✅ Demo "${optionName}" option added with ${values.length} values!`, 'success');
}

function removeOption(button) {
    const optionGroup = button.closest('.option-group');
    optionGroup.remove();
    updateVariants();
}

function editOption(button) {
    const optionGroup = button.closest('.option-group');
    optionGroup.classList.remove('completed');
    button.textContent = 'Done';
    button.setAttribute('onclick', 'doneOption(this)');
}

function updateVariants() {
    const optionsContainer = document.getElementById('optionsContainer');
    const optionGroups = optionsContainer.querySelectorAll('.option-group.completed');
    
    if (optionGroups.length === 0) {
        hideVariationsTable();
        return;
    }
    
    // Collect all options and their values
    const options = [];
    optionGroups.forEach(group => {
        const optionName = group.querySelector('.option-name').value;
        const optionValues = Array.from(group.querySelectorAll('.option-value'))
            .map(input => input.value)
            .filter(value => value.trim());
        
        if (optionName.trim() && optionValues.length > 0) {
            options.push({
                name: optionName.trim(),
                values: optionValues
            });
        }
    });
    
    if (options.length === 0) {
        hideVariationsTable();
        return;
    }
    
    // Generate all combinations
    const combinations = generateCombinations(options);
    displayVariants(combinations);
}

function generateCombinations(options) {
    if (options.length === 0) return [];
    
    let combinations = [[]];
    
    for (const option of options) {
        const newCombinations = [];
        for (const combination of combinations) {
            for (const value of option.values) {
                newCombinations.push([...combination, { name: option.name, value: value }]);
            }
        }
        combinations = newCombinations;
    }
    
    return combinations;
}

function displayVariants(combinations) {
    const variationsTableCard = document.getElementById('variationsTableCard');
    const variationsTableBody = document.getElementById('variationsTableBody');
    const totalInventory = document.getElementById('totalInventory');
    
    if (!variationsTableCard || !variationsTableBody) return;
    
    // Show the variations table
    variationsTableCard.style.display = 'block';
    
    // Clear existing rows
    variationsTableBody.innerHTML = '';
    
    // Create rows for each combination
    combinations.forEach((combination, index) => {
        const variantName = combination.map(c => c.value).join(' / ');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="py-3 px-4">
                <input type="checkbox" class="form-check-input variant-checkbox" value="${index}">
            </td>
            <td class="py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="variant-image me-3" style="width: 50px; height: 50px; border: 2px dashed #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease; background: #f8f9fa;" onclick="addVariantImage(${index})" title="Click to select image from media library">
                        <i class="ri-image-add-line text-muted" style="font-size: 20px;"></i>
                    </div>
                    <span class="variant-name fw-medium">${variantName}</span>
                </div>
            </td>
            <td class="py-3 px-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rs</span>
                    <input type="number" class="form-control variant-price" value="0.00" step="0.01" min="0" onchange="updateTotalInventory()" id="price_${index}">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyPriceToAll(${index})" title="Apply this price to all variants">
                        <i class="ri-equalizer-line"></i>
                    </button>
                </div>
            </td>
            <td class="py-3 px-4">
                <div class="d-flex align-items-center">
                    
                    <input type="number" class="form-control form-control-sm variant-stock" value="0" min="0" onchange="updateTotalInventory()" style="display: none;" id="stockField_${index}">
                    <button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="toggleStockField(${index})" id="stockBtn_${index}">
                        <i class="ri-add-line me-1"></i>Add Stock
                    </button>
                </div>
            </td>
        `;
        
        variationsTableBody.appendChild(row);
    });
    
    // Update total inventory
    updateTotalInventory();
    
    // Store variants data
    allVariants = combinations;
}

function updateTotalInventory() {
    const stockInputs = document.querySelectorAll('.variant-stock:not([style*="display: none"])');
    const totalInventory = document.getElementById('totalInventory');
    
    if (!totalInventory) return;
    
    let total = 0;
    stockInputs.forEach(input => {
        if (input.style.display !== 'none') {
            total += parseInt(input.value) || 0;
        }
    });
    
    totalInventory.textContent = `Total inventory: ${total} available`;
}

function hideVariationsTable() {
    const variationsTableCard = document.getElementById('variationsTableCard');
    if (variationsTableCard) {
        variationsTableCard.style.display = 'none';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function addVariantImage(index) {
    console.log('Opening media library for variant:', index);
    
    // Store the current variant index for the media library callback
    window.currentVariantIndex = index;
    
    // Open media library modal
    try {
        const modalElement = document.getElementById('mediaLibraryModal');
        if (!modalElement) {
            console.error('Media library modal not found');
            showNotification('Media library not available', 'error');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        // Set callback for when media is selected
        window.selectMediaFromLibraryItems = function(items) {
            if (items && items.length > 0) {
                const selectedMedia = items[0]; // Take first selected item
                updateVariantImage(index, selectedMedia);
                modal.hide();
                showNotification(`✅ Image selected for variant ${index + 1}`, 'success');
            }
        };
        
    } catch (error) {
        console.error('Error opening media library:', error);
        showNotification('Error opening media library', 'error');
    }
}

// Update variant image with selected media
function updateVariantImage(variantIndex, media) {
    console.log('Updating variant image:', variantIndex, media);
    
    const imageContainer = document.querySelector(`[onclick="addVariantImage(${variantIndex})"]`);
    if (imageContainer) {
        // Create image element
        const img = document.createElement('img');
        img.src = media.file_url;
        img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 4px;';
        img.alt = media.original_name;
        
        // Clear container and add image
        imageContainer.innerHTML = '';
        imageContainer.appendChild(img);
        
        // Add remove button
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger position-absolute';
        removeBtn.style.cssText = 'top: -5px; right: -5px; width: 20px; height: 20px; padding: 0; font-size: 10px;';
        removeBtn.innerHTML = '<i class="ri-close-line"></i>';
        removeBtn.onclick = function(e) {
            e.stopPropagation();
            removeVariantImage(variantIndex);
        };
        
        // Make container relative positioned for absolute button
        imageContainer.style.position = 'relative';
        imageContainer.appendChild(removeBtn);
        
        // Store media ID for form submission
        imageContainer.setAttribute('data-media-id', media.id);
        
        console.log(`Variant ${variantIndex} image updated with media ID: ${media.id}`);
    }
}

// Collect variant images for form submission
function collectVariantImages() {
    console.log('Collecting variant images for form submission');
    
    const variantImages = {};
    const imageContainers = document.querySelectorAll('[onclick*="addVariantImage"]');
    
    imageContainers.forEach((container, index) => {
        const mediaId = container.getAttribute('data-media-id');
        if (mediaId) {
            variantImages[index] = mediaId;
            console.log(`Variant ${index} has image with media ID: ${mediaId}`);
        }
    });
    
    return variantImages;
}

// Collect all variant data for form submission
function collectVariantData() {
    console.log('=== Collecting variant data for form submission ===');
    
    const variants = [];
    const optionGroups = document.querySelectorAll('.option-group.completed');
    
    if (optionGroups.length === 0) {
        console.log('No completed option groups found');
        return variants;
    }
    
    // Collect all options and their values
    const options = [];
    optionGroups.forEach(group => {
        const optionName = group.querySelector('.option-name').value.trim();
        const optionValues = Array.from(group.querySelectorAll('.option-value'))
            .map(input => input.value.trim())
            .filter(value => value);
        
        if (optionName.trim() && optionValues.length > 0) {
            options.push({
                name: optionName.trim(),
                values: optionValues
            });
        }
    });
    
    if (options.length === 0) {
        console.log('No valid options found');
        return variants;
    }
    
    // Generate all combinations
    const combinations = generateCombinations(options);
    console.log('Generated combinations:', combinations);
    
    // Collect variant data from table
    combinations.forEach((combination, index) => {
        const variantData = {
            name: combination.map(c => c.value).join(' / '),
            attributes: {},
            price: 0,
            stock: 0,
            sku: '',
            image: null
        };
        
        // Add attribute data
        combination.forEach(attr => {
            variantData.attributes[attr.name] = attr.value;
        });
        
        // Get data from table if it exists
        const priceField = document.getElementById(`price_${index}`);
        const stockField = document.getElementById(`stockField_${index}`);
        
        if (priceField) {
            variantData.price = parseFloat(priceField.value) || 0;
        }
        
        if (stockField && stockField.style.display !== 'none') {
            variantData.stock = parseInt(stockField.value) || 0;
        }
        
        // Generate SKU if not provided
        variantData.sku = `SKU-${index + 1}`;
        
        // Get variant image
        const imageContainer = document.querySelector(`[onclick="addVariantImage(${index})"]`);
        if (imageContainer && imageContainer.getAttribute('data-media-id')) {
            variantData.image = imageContainer.getAttribute('data-media-id');
        }
        
        variants.push(variantData);
    });
    
    console.log('Collected variants:', variants);
    return variants;
}

// Add variant data to form before submission
function addVariantDataToForm() {
    console.log('=== Adding variant data to form ===');
    
    const variants = collectVariantData();
    
    // Remove existing variant inputs
    const existingInputs = document.querySelectorAll('input[name*="variants"]');
    existingInputs.forEach(input => input.remove());
    
    // Add variant data as hidden inputs
    variants.forEach((variant, index) => {
        // Add variant name
        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = `variants[${index}][name]`;
        nameInput.value = variant.name;
        document.querySelector('form').appendChild(nameInput);
        
        // Add variant price
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = `variants[${index}][price]`;
        priceInput.value = variant.price;
        document.querySelector('form').appendChild(priceInput);
        
        // Add variant stock
        const stockInput = document.createElement('input');
        stockInput.type = 'hidden';
        stockInput.name = `variants[${index}][stock]`;
        stockInput.value = variant.stock;
        document.querySelector('form').appendChild(stockInput);
        
        // Add variant SKU
        const skuInput = document.createElement('input');
        skuInput.type = 'hidden';
        skuInput.name = `variants[${index}][sku]`;
        skuInput.value = variant.sku;
        document.querySelector('form').appendChild(skuInput);
        
        // Add variant image
        if (variant.image) {
            const imageInput = document.createElement('input');
            imageInput.type = 'hidden';
            imageInput.name = `variants[${index}][image]`;
            imageInput.value = variant.image;
            document.querySelector('form').appendChild(imageInput);
        }
        
        // Add variant attributes
        Object.entries(variant.attributes).forEach(([attrName, attrValue]) => {
            const attrInput = document.createElement('input');
            attrInput.type = 'hidden';
            attrInput.name = `variants[${index}][attributes][${attrName}]`;
            attrInput.value = attrValue;
            document.querySelector('form').appendChild(attrInput);
        });
    });
    
    console.log(`Added ${variants.length} variants to form`);
}

// Add variant images to form before submission
function addVariantImagesToForm() {
    const variantImages = collectVariantImages();
    
    // Remove existing variant image inputs
    const existingInputs = document.querySelectorAll('input[name*="variant_images"]');
    existingInputs.forEach(input => input.remove());
    
    // Add new inputs for each variant image
    Object.entries(variantImages).forEach(([variantIndex, mediaId]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `variant_images[${variantIndex}]`;
        input.value = mediaId;
        document.querySelector('form').appendChild(input);
    });
    
    console.log('Variant images added to form:', variantImages);
}

// Apply price from one variant to all variants
function applyPriceToAll(sourceVariantIndex) {
    console.log('Applying price to all variants from variant:', sourceVariantIndex);
    
    const sourcePriceField = document.getElementById(`price_${sourceVariantIndex}`);
    if (!sourcePriceField) {
        console.error('Source price field not found');
        return;
    }
    
    const sourcePrice = parseFloat(sourcePriceField.value) || 0;
    
    if (sourcePrice <= 0) {
        showNotification('Please enter a valid price first', 'warning');
        return;
    }
    
    // Get all price fields
    const allPriceFields = document.querySelectorAll('.variant-price');
    let updatedCount = 0;
    
    allPriceFields.forEach((priceField, index) => {
        if (index !== sourceVariantIndex) { // Don't update the source field
            priceField.value = sourcePrice.toFixed(2);
            updatedCount++;
        }
    });
    
    // Show success notification
    showNotification(`✅ Price Rs ${sourcePrice.toFixed(2)} applied to ${updatedCount} variants`, 'success');
    
    // Update total inventory
    updateTotalInventory();
    
    console.log(`Applied price ${sourcePrice} to ${updatedCount} variants`);
}

// Toggle stock field for specific variant
function toggleStockField(variantIndex) {
    console.log('Toggling stock field for variant:', variantIndex);
    
    const stockBtn = document.getElementById(`stockBtn_${variantIndex}`);
    const stockField = document.getElementById(`stockField_${variantIndex}`);
    
    if (stockBtn && stockField) {
        if (stockField.style.display === 'block') {
            // Currently visible, so hide it
            stockField.style.display = 'none';
            stockField.value = '0'; // Reset stock to 0 when hidden
            stockBtn.innerHTML = '<i class="ri-add-line me-1"></i>Add Stock';
            stockBtn.className = 'btn btn-sm btn-outline-success me-2';
            showNotification(`Stock field disabled for variant ${variantIndex + 1}`, 'info');
        } else {
            // Currently hidden, so show it
            stockField.style.display = 'block';
            stockField.focus();
            stockBtn.innerHTML = '<i class="ri-check-line me-1"></i>Stock Added';
            stockBtn.className = 'btn btn-sm btn-success me-2';
            showNotification(`Stock field enabled for variant ${variantIndex + 1}`, 'success');
        }
        
        // Update total inventory after change
        updateTotalInventory();
    }
}

// Disable stock field (optional - for reset functionality)
function disableStockField(variantIndex) {
    console.log('Disabling stock field for variant:', variantIndex);
    
    const stockBtn = document.getElementById(`stockBtn_${variantIndex}`);
    const stockField = document.getElementById(`stockField_${variantIndex}`);
    
    if (stockBtn && stockField) {
        // Show button and hide input field
        stockBtn.style.display = 'inline-block';
        stockField.style.display = 'none';
        
        // Reset button to original state
        stockBtn.innerHTML = '<i class="ri-add-line me-1"></i>Add Stock';
        stockBtn.className = 'btn btn-sm btn-outline-success me-2';
        
        // Reset stock value
        stockField.value = '0';
        
        // Update total inventory
        updateTotalInventory();
    }
}

function showAddAnotherValue(optionGroup) {
    const optionValues = optionGroup.querySelector('.option-values');
    const allInputWrappers = optionValues.querySelectorAll('.option-value-wrapper');
    const lastInputWrapper = allInputWrappers[allInputWrappers.length - 1];
    const lastInput = lastInputWrapper ? lastInputWrapper.querySelector('.option-value') : null;
    
    // If the last input has a value, create a new input field
    if (lastInput && lastInput.value.trim()) {
        const newInputWrapper = document.createElement('div');
        newInputWrapper.className = 'd-flex align-items-center mb-2 option-value-wrapper';
        newInputWrapper.innerHTML = `
            <input type="text" class="form-control option-value me-2" placeholder="Add another value" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
            <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                <i class="ri-delete-bin-line"></i>
            </button>
        `;
        optionValues.appendChild(newInputWrapper);
        // Don't focus on new input - keep focus on current input
    }
}

function removeOptionValue(button) {
    const optionValueWrapper = button.closest('.option-value-wrapper');
    const optionGroup = button.closest('.option-group');
    
    if (optionValueWrapper) {
        optionValueWrapper.remove();
        updateVariants(); // Update variants after removing a value
        
        // Ensure at least one empty input field remains if all are removed
        const optionValuesContainer = optionGroup.querySelector('.option-values');
        if (optionValuesContainer && optionValuesContainer.querySelectorAll('.option-value-wrapper').length === 0) {
            // If no value inputs are left, add a new empty one with "Add value" placeholder
            const newInputWrapper = document.createElement('div');
            newInputWrapper.className = 'd-flex align-items-center mb-2 option-value-wrapper';
            newInputWrapper.innerHTML = `
                <input type="text" class="form-control option-value me-2" placeholder="Add value" onchange="updateVariants()" oninput="showAddAnotherValue(this.closest('.option-group'))">
                <button type="button" class="btn btn-sm btn-outline-danger remove-option-value" onclick="removeOptionValue(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            `;
            optionValuesContainer.appendChild(newInputWrapper);
            newInputWrapper.querySelector('.option-value').focus();
        }
    }
}

function searchVariants() {
    // Implementation for search functionality
    alert('Search functionality will be implemented');
}

function sortVariants() {
    // Implementation for sort functionality
    alert('Sort functionality will be implemented');
}

// Global function for generating variations
function generateVariations() {
    console.log('=== generateVariations function called ===');
    const selectedAttributes = document.querySelectorAll('.attribute-checkbox:checked');
    console.log('Selected attributes:', selectedAttributes.length);
    
    // Debug: Show all checked checkboxes on the page
    const allCheckedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    console.log('All checked checkboxes on page:', allCheckedCheckboxes.length);
    allCheckedCheckboxes.forEach((cb, index) => {
        console.log(`Checkbox ${index}:`, {
            id: cb.id,
            name: cb.name,
            value: cb.value,
            className: cb.className,
            parentElement: cb.parentElement?.tagName
        });
    });
    
    if (selectedAttributes.length === 0) {
        alert('Please select at least one attribute.');
        return;
    }
    
    // Get selected values for each attribute
    const attributeValues = {};
    let totalCombinations = 1;

    selectedAttributes.forEach(attr => {
        const attributeId = attr.value;
        console.log(`Processing attribute ${attributeId}`);
        
        // Try multiple selectors to find the values
        const selectedValues1 = document.querySelectorAll(`input[name="variation_values[${attributeId}][]"]:checked`);
        const selectedValues2 = document.querySelectorAll(`#values_${attributeId} .attribute-value-checkbox:checked`);
        const selectedValues3 = document.querySelectorAll(`#values_${attributeId} input[type="checkbox"]:checked`);
        
        console.log(`Attribute ${attributeId} - Method 1 (name selector): ${selectedValues1.length} values`);
        console.log(`Attribute ${attributeId} - Method 2 (class selector): ${selectedValues2.length} values`);
        console.log(`Attribute ${attributeId} - Method 3 (type selector): ${selectedValues3.length} values`);
        
        // Use whichever method finds values
        const selectedValues = selectedValues1.length > 0 ? selectedValues1 : 
                              selectedValues2.length > 0 ? selectedValues2 : 
                              selectedValues3;
        
        console.log(`Using ${selectedValues.length} selected values for attribute ${attributeId}`);
        
        if (selectedValues.length > 0) {
            attributeValues[attributeId] = Array.from(selectedValues).map(v => ({
                id: v.value,
                name: v.nextElementSibling ? v.nextElementSibling.textContent.trim() : v.value
            }));
            totalCombinations *= selectedValues.length;
            console.log(`Attribute ${attributeId} values:`, attributeValues[attributeId]);
        }
    });

    console.log('Total combinations:', totalCombinations);
    console.log('Attribute values:', attributeValues);

    if (totalCombinations > 50) {
        if (!confirm(`This will create ${totalCombinations} variations. This might take a while. Continue?`)) {
            return;
        }
    }
        
    // Generate all combinations
    const combinations = generateCombinations(attributeValues);
        
    console.log('Generated combinations:', combinations);
    
    if (combinations.length > 0) {
        console.log('Calling displayVariations with', combinations.length, 'combinations');
        try {
            displayVariations(combinations);
            console.log('displayVariations completed successfully');
        } catch (error) {
            console.error('Error in displayVariations:', error);
        }
        alert(`Successfully generated ${combinations.length} variations!`);
    } else {
        alert('No variations could be generated. Please select attribute values.');
    }
}


function displayVariations(combinations) {
    const variationsTableBody = document.getElementById('variationsTableBody');
    const variationsTableCard = document.getElementById('variationsTableCard');
    
    console.log('displayVariations called with', combinations.length, 'combinations');
    console.log('variationsTableBody found:', !!variationsTableBody);
    console.log('variationsTableCard found:', !!variationsTableCard);
    
    if (!variationsTableBody || !variationsTableCard) {
        console.error('Variations table elements not found');
        return;
    }
    
    // Clear existing content
    variationsTableBody.innerHTML = '';
    
    // Create rows for each combination
    combinations.forEach((combination, index) => {
        const row = document.createElement('tr');
        const variationName = Object.values(combination).map(v => v.name).join(' - ');
        
        row.className = 'align-middle';
        row.innerHTML = `
                      <td class="py-3 px-4">
                          <div class="d-flex align-items-center">
                              <span class="badge bg-primary me-2">${index + 1}</span>
                              <input type="text" class="form-control form-control-sm border-0 bg-light" 
                                     name="variations[${index}][name]" 
                                     value="${variationName}" placeholder="Enter variation name">
                          </div>
                      </td>
            <td class="py-3 px-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0">$</span>
                    <input type="number" class="form-control border-0 bg-light" 
                           name="variations[${index}][price]" 
                           step="0.01" placeholder="0.00">
                </div>
            </td>
            <td class="py-3 px-4">
                <input type="number" class="form-control form-control-sm border-0 bg-light" 
                       name="variations[${index}][stock]" 
                       placeholder="0" min="0">
            </td>
            <td class="py-3 px-4">
                <input type="text" class="form-control form-control-sm border-0 bg-light" 
                       name="variations[${index}][sku]" 
                       placeholder="SKU-${index + 1}">
            </td>
            <td class="py-3 px-4">
                <div class="d-flex align-items-center">
                    <input type="file" class="form-control form-control-sm border-0 bg-light" 
                           name="variations[${index}][image]" 
                           accept="image/*">
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                            onclick="previewImage(this)" title="Preview">
                        <i class="ri-eye-line"></i>
                    </button>
                </div>
            </td>
            <td class="py-3 px-4 text-center">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-success" 
                            onclick="duplicateVariation(${index})" title="Duplicate">
                        <i class="ri-file-copy-line"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger" 
                            onclick="removeVariation(${index})" title="Remove">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </td>
        `;
        
        // Add hidden inputs for attribute values
        Object.entries(combination).forEach(([attrId, value]) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `variations[${index}][attributes][${attrId}]`;
            hiddenInput.value = value.id;
            row.appendChild(hiddenInput);
        });
        
        variationsTableBody.appendChild(row);
    });
    
    console.log('Setting variationsTableCard to visible');
    variationsTableCard.style.display = 'block';
    console.log('variationsTableCard display style:', variationsTableCard.style.display);
    
    // Update variations count
    const variationsCount = document.getElementById('variationsCount');
    if (variationsCount) {
        variationsCount.textContent = `${combinations.length} variation${combinations.length !== 1 ? 's' : ''}`;
    }
}

// Helper functions for variation actions
function previewImage(button) {
    const fileInput = button.previousElementSibling;
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create a modal or tooltip to show the image
            alert('Image preview: ' + file.name);
        };
        reader.readAsDataURL(file);
    } else {
        alert('Please select an image first');
    }
}

function duplicateVariation(index) {
    console.log('Duplicating variation:', index);
    
    const variationsTableBody = document.getElementById('variationsTableBody');
    const existingRow = variationsTableBody.children[index];
    
    if (existingRow) {
        // Create a new row by cloning the existing one
        const newRow = existingRow.cloneNode(true);
        
        // Update the index for the new row
        const newIndex = variationsTableBody.children.length;
        
        // Update all input names and IDs
        newRow.querySelectorAll('input').forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, `[${newIndex}]`);
            }
            if (input.id) {
                input.id = input.id.replace(/\d+/, newIndex);
            }
        });
        
        // Update the variation number badge
        const badge = newRow.querySelector('.badge');
        if (badge) {
            badge.textContent = newIndex + 1;
        }
        
        // Clear the values (except variation name)
        const nameInput = newRow.querySelector('input[name*="[name]"]');
        const priceInput = newRow.querySelector('input[name*="[price]"]');
        const stockInput = newRow.querySelector('input[name*="[stock]"]');
        const skuInput = newRow.querySelector('input[name*="[sku]"]');
        const imageInput = newRow.querySelector('input[name*="[image]"]');
        
        if (nameInput) {
            const originalName = nameInput.value;
            nameInput.value = originalName + ' (Copy)';
        }
        if (priceInput) priceInput.value = '';
        if (stockInput) stockInput.value = '';
        if (skuInput) skuInput.value = `SKU-${newIndex + 1}`;
        if (imageInput) imageInput.value = '';
        
        // Update onclick handlers for action buttons
        const duplicateBtn = newRow.querySelector('button[onclick*="duplicateVariation"]');
        const removeBtn = newRow.querySelector('button[onclick*="removeVariation"]');
        
        if (duplicateBtn) {
            duplicateBtn.setAttribute('onclick', `duplicateVariation(${newIndex})`);
        }
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeVariation(${newIndex})`);
        }
        
        // Add the new row
        variationsTableBody.appendChild(newRow);
        
        // Update variations count
        updateVariationsCount();
        
        // Scroll to the new row
        newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        console.log('Variation duplicated successfully');
    }
}

function removeVariation(index) {
    const variationsTableBody = document.getElementById('variationsTableBody');
    const row = variationsTableBody.children[index];
    
    if (row) {
        const variationName = row.querySelector('input[name*="[name]"]')?.value || `Variation ${index + 1}`;
        
        if (confirm(`Are you sure you want to remove "${variationName}"?`)) {
            row.remove();
            
            // Update indices for remaining rows
            updateRowIndices();
            
            // Update variations count
            updateVariationsCount();
            
            console.log('Variation removed successfully');
        }
    }
}

function addCustomVariation() {
    console.log('Adding custom variation');
    
    const variationsTableBody = document.getElementById('variationsTableBody');
    const newIndex = variationsTableBody.children.length;
    
    // Create a new row
    const row = document.createElement('tr');
    row.className = 'align-middle';
    
    row.innerHTML = `
        <td class="py-3 px-4">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2">${newIndex + 1}</span>
                <input type="text" class="form-control form-control-sm border-0 bg-light" 
                       name="variations[${newIndex}][name]" 
                       placeholder="Enter variation name" required>
            </div>
        </td>
        <td class="py-3 px-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0">$</span>
                <input type="number" class="form-control border-0 bg-light" 
                       name="variations[${newIndex}][price]" 
                       step="0.01" placeholder="0.00">
            </div>
        </td>
        <td class="py-3 px-4">
            <input type="number" class="form-control form-control-sm border-0 bg-light" 
                   name="variations[${newIndex}][stock]" 
                   placeholder="0" min="0">
        </td>
        <td class="py-3 px-4">
            <input type="text" class="form-control form-control-sm border-0 bg-light" 
                   name="variations[${newIndex}][sku]" 
                   placeholder="SKU-${newIndex + 1}">
        </td>
        <td class="py-3 px-4">
            <div class="d-flex align-items-center">
                <input type="file" class="form-control form-control-sm border-0 bg-light" 
                       name="variations[${newIndex}][image]" 
                       accept="image/*">
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" 
                        onclick="previewImage(this)" title="Preview">
                    <i class="ri-eye-line"></i>
                </button>
            </div>
        </td>
        <td class="py-3 px-4 text-center">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-success" 
                        onclick="duplicateVariation(${newIndex})" title="Duplicate">
                    <i class="ri-file-copy-line"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" 
                        onclick="removeVariation(${newIndex})" title="Remove">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </td>
    `;
    
    // Add the new row
    variationsTableBody.appendChild(row);
    
    // Update variations count
    updateVariationsCount();
    
    // Focus on the name input
    const nameInput = row.querySelector('input[name*="[name]"]');
    if (nameInput) {
        nameInput.focus();
    }
    
    // Scroll to the new row
    row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    console.log('Custom variation added successfully');
}

function updateVariationsCount() {
    const rows = document.querySelectorAll('#variationsTableBody tr');
    const variationsCount = document.getElementById('variationsCount');
    if (variationsCount) {
        variationsCount.textContent = `${rows.length} variation${rows.length !== 1 ? 's' : ''}`;
    }
}

function updateRowIndices() {
    const variationsTableBody = document.getElementById('variationsTableBody');
    const rows = variationsTableBody.children;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        
        // Update badge number
        const badge = row.querySelector('.badge');
        if (badge) {
            badge.textContent = i + 1;
        }
        
        // Update input names and IDs
        row.querySelectorAll('input').forEach(input => {
            if (input.name) {
                input.name = input.name.replace(/\[\d+\]/, `[${i}]`);
            }
            if (input.id) {
                input.id = input.id.replace(/\d+/, i);
            }
        });
        
        // Update SKU placeholder
        const skuInput = row.querySelector('input[name*="[sku]"]');
        if (skuInput && !skuInput.value) {
            skuInput.placeholder = `SKU-${i + 1}`;
        }
        
        // Update onclick handlers for action buttons
        const duplicateBtn = row.querySelector('button[onclick*="duplicateVariation"]');
        const removeBtn = row.querySelector('button[onclick*="removeVariation"]');
        
        if (duplicateBtn) {
            duplicateBtn.setAttribute('onclick', `duplicateVariation(${i})`);
        }
        if (removeBtn) {
            removeBtn.setAttribute('onclick', `removeVariation(${i})`);
        }
    }
}

// Function to toggle attribute values visibility
function toggleAttributeValues(attributeId, checked) {
    console.log('toggleAttributeValues called:', attributeId, checked);
    const valuesDiv = document.getElementById('values_' + attributeId);
    console.log('valuesDiv found:', !!valuesDiv);
    
    if (valuesDiv) {
        if (checked) {
            valuesDiv.style.display = 'block';
            console.log('Showing values for attribute:', attributeId);
        } else {
            valuesDiv.style.display = 'none';
            // Uncheck all values for this attribute
            const valueCheckboxes = valuesDiv.querySelectorAll('.attribute-value-checkbox');
            valueCheckboxes.forEach(checkbox => checkbox.checked = false);
            console.log('Hiding values for attribute:', attributeId);
        }
    } else {
        console.error('valuesDiv not found for attribute:', attributeId);
    }
}
</script>

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">Admin / Products /</span> Add New
    </h4>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Left Column - Main Product Info -->
            <div class="col-md-8">
                <!-- Product Type Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Product Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="simple_product" value="simple" {{ old('product_type', 'simple') == 'simple' ? 'checked' : '' }} onclick="showSimpleSection();">
                                    <label class="form-check-label" for="simple_product">
                                        <strong>Simple Product</strong>
                                        <small class="d-block text-muted">Single product with fixed price and stock</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="product_type" id="variable_product" value="variable" {{ old('product_type') == 'variable' ? 'checked' : '' }} onclick="showVariableSection();">
                                    <label class="form-check-label" for="variable_product">
                                        <strong>Variable Product</strong>
                                        <small class="d-block text-muted">Product with variations (size, color, etc.)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Product Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="Product Name" required>
                                <label for="name">Product Name *</label>
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                                               id="sku" name="sku" value="{{ old('sku') }}" 
                                               placeholder="SKU">
                                        <label for="sku">SKU</label>
                                    </div>
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                               id="barcode" name="barcode" value="{{ old('barcode') }}"
                                               placeholder="Barcode">
                                        <label for="barcode">Barcode</label>
                                    </div>
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4" 
                                          placeholder="Description">{{ old('description') }}</textarea>
                                <label for="description">Description</label>
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="card mb-4" id="simpleProductFields">
                    <div class="card-header">
                        <h5 class="mb-0">Pricing & Inventory</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price') }}" 
                                               placeholder="Regular Price" required>
                                        <label for="price">Regular Price *</label>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" 
                                               id="sale_price" name="sale_price" value="{{ old('sale_price') }}"
                                               placeholder="Sale Price">
                                        <label for="sale_price">Sale Price</label>
                                    </div>
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                                               id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" 
                                               placeholder="Stock Quantity">
                                        <label for="stock_quantity">Stock Quantity</label>
                                    </div>
                                    @error('stock_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="charge_tax" name="charge_tax" {{ old('charge_tax', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="charge_tax">
                                    Charge tax on this product
                                </label>
                            </div>
                        </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" {{ old('in_stock', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="in_stock">
                                    In stock
                                </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simple Variable Product Section -->
                <div class="card mb-4" id="variableProductFields" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Product Variations</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success mb-4">
                            <i class="ri-lightbulb-line me-2"></i>
                            <strong>Easy Way:</strong> Just add your product options below (like Color, Size) and their values. 
                            The system will automatically create all possible combinations for you!
                        </div>

                        <!-- Instructions -->
                        <div class="alert alert-info mb-4">
                            <h6 class="mb-2"><i class="ri-information-line me-1"></i> How to Create Custom Attributes:</h6>
                            <ol class="mb-0 small">
                                <li><strong>Click "Add Product Option"</strong> to create a new option</li>
                                <li><strong>Type option name</strong> (e.g., "Brand", "Material", "Style")</li>
                                <li><strong>Add option values</strong> (e.g., "Nike", "Adidas", "Puma" for Brand)</li>
                                <li><strong>Click "Done"</strong> - Your custom attribute will be automatically saved!</li>
                                <li><strong>Next time</strong> you can reuse the same attribute from the dropdown</li>
                            </ol>
                                                    </div>
                                                    
                        <!-- Simple Options Section -->
                        <div class="variants-options mb-4">
                            <div id="optionsContainer">
                                <!-- Options will be dynamically added here -->
                </div>

                            <button type="button" class="btn btn-primary mt-3" onclick="addOption()">
                                <i class="ri-add-line me-1"></i>Add Product Option
                        </button>
                        </div>
                    </div>
                </div>

                <!-- Generated Variations Table -->
                <div class="card mb-4" id="variationsTableCard" style="display: none;">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Generated Product Variations</h5>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="searchVariants()">
                                    <i class="ri-search-line"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortVariants()">
                                    <i class="ri-sort-desc"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="variationsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 py-3 px-4" style="width: 50px;">
                                            <input type="checkbox" class="form-check-input" id="selectAllVariants">
                                        </th>
                                        <th class="border-0 py-3 px-4">Variant</th>
                                        <th class="border-0 py-3 px-4">Price</th>
                                        <th class="border-0 py-3 px-4">Available</th>
                                    </tr>
                                </thead>
                                <tbody id="variationsTableBody">
                                    <!-- Variations will be auto-generated here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-light border-top">
                            <div class="text-center">
                                <small class="text-muted" id="totalInventory">
                                    Total inventory: 0 available
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Images -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Product Image</h5>
                            <button type="button" class="btn btn-link p-0 fw-semibold text-primary" onclick="openMediaLibrary(event)">
                                Add Media
                            </button>
                        </div>
                        
                        @php
                            $initialMedia = [];
                            $oldMediaIds = old('media_ids', []);
                            if (is_array($oldMediaIds) && count($oldMediaIds) > 0) {
                                $initialMedia = \App\Models\Media::whereIn('id', $oldMediaIds)->get()->map(function ($media) {
                                    $url = \Illuminate\Support\Str::startsWith($media->file_url, ['http://', 'https://'])
                                        ? $media->file_url
                                        : asset(ltrim($media->file_url, '/'));
                                    return [
                                        'id' => $media->id,
                                        'file_url' => $url,
                                        'original_name' => $media->original_name,
                                        'file_size_formatted' => $media->file_size_formatted
                                    ];
                                })->values();
                            }
                        @endphp

                        <div class="product-media-picker" data-initial-media='@json($initialMedia)'>
                            <div class="product-media-dropzone" id="productMediaDropzone" role="button" tabindex="0" aria-label="Select product images">
                                <div class="product-media-empty" id="productMediaEmpty">
                                    <i class="ri-upload-cloud-2-line"></i>
                                    <p class="mb-1">Drop images here or click to open media library</p>
                                    <small class="text-muted">Select one or more images to build your gallery</small>
                        </div>

                                <div class="row g-3 product-media-gallery" id="productMediaGallery"></div>
                            </div>

                            <input type="hidden" name="media_id" id="primaryMediaId" value="{{ old('media_id') }}">
                            <div id="selectedMediaInputs">
                                @foreach(old('media_ids', []) as $id)
                                    <input type="hidden" name="media_ids[]" value="{{ $id }}">
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="col-md-4">
                <!-- Organize -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Organization</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('vendor_id') is-invalid @enderror" 
                                        id="vendor_id" name="vendor_id">
                                    <option value="">Select Vendor</option>
                                    @if(isset($vendors) && $vendors)
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <label for="vendor_id">Vendor</label>
                            </div>
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @if(isset($categories) && $categories)
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <label for="category_id">Category *</label>
                            </div>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                </select>
                                <label for="status">Status</label>
                            </div>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control @error('tags') is-invalid @enderror" 
                                       id="tags" name="tags" value="{{ old('tags') }}" 
                                       placeholder="Tags">
                                <label for="tags">Tags</label>
                            </div>
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Inventory -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Inventory & Shipping</h5>
                    </div>
                    <div class="card-body">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                               id="weight" name="weight" value="{{ old('weight') }}"
                                               placeholder="Weight">
                                        <label for="weight">Weight (kg)</label>
                                    </div>
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                    <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" step="0.01" class="form-control @error('length') is-invalid @enderror" 
                                               id="length" name="length" value="{{ old('length') }}"
                                               placeholder="Length">
                                        <label for="length">Length (cm)</label>
                                    </div>
                                    @error('length')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" step="0.01" class="form-control @error('width') is-invalid @enderror" 
                                               id="width" name="width" value="{{ old('width') }}"
                                               placeholder="Width">
                                        <label for="width">Width (cm)</label>
                                    </div>
                                    @error('width')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <div class="form-floating form-floating-outline">
                                        <input type="number" step="0.01" class="form-control @error('height') is-invalid @enderror" 
                                               id="height" name="height" value="{{ old('height') }}"
                                               placeholder="Height">
                                        <label for="height">Height (cm)</label>
                                    </div>
                                    @error('height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="featured" name="featured" {{ old('featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">
                                Featured Product
                            </label>
                            </div>
                            
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="active" name="active" {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">
                                Active
                            </label>
                                </div>
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="status" value="draft" class="btn btn-outline-secondary">
                                <i class="ri-save-line me-1"></i> Save as Draft
                            </button>
                            <div>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-2">
                                    <i class="ri-close-line me-1"></i> Cancel
                                </a>
                                <button type="submit" name="status" value="active" class="btn btn-primary">
                                    <i class="ri-check-line me-1"></i> Publish Product
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Create Attribute Modal -->
<div class="modal fade" id="createAttributeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Attribute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
                <div class="modal-body">
                <form id="createAttributeForm">
                    @csrf
                    <div class="mb-3">
                        <div class="form-floating form-floating-outline">
                            <input type="text" class="form-control" id="attribute_name" name="name" 
                                   placeholder="Attribute Name" required>
                            <label for="attribute_name">Attribute Name</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-floating form-floating-outline">
                            <select class="form-select" id="attribute_type" name="type" required>
                                <option value="text">Text</option>
                                <option value="color">Color</option>
                                <option value="image">Image</option>
                            </select>
                            <label for="attribute_type">Attribute Type</label>
                        </div>
                    </div>
                </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createAttribute()">Create Attribute</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Shopify-style variants */
.option-group {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.option-group:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
}

.option-group.completed {
    border-color: #28a745;
    background: #f8fff9;
}

.option-group .form-control {
    border-radius: 6px;
    border: 1px solid #e1e5e9;
    transition: all 0.2s ease;
}

.option-group .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.variant-image {
    background: #f8f9fa;
    border: 1px dashed #dee2e6;
    transition: all 0.2s ease;
}

.variant-image:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.variant-image img {
    border-radius: 4px;
}

.variations-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.variations-table td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.variations-table tr:hover {
    background: #f8f9fa;
}

.option-name, .option-value {
    font-size: 14px;
}

.btn-dark {
    background-color: #343a40;
    border-color: #343a40;
}

.btn-dark:hover {
    background-color: #23272b;
    border-color: #1d2124;
}

/* Drag handle styling */
.ri-drag-move-2-line {
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.option-group:hover .ri-drag-move-2-line {
    opacity: 1;
}

/* Animation for new inputs */
.option-value {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Variant image styling */
.variant-image {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    transition: all 0.2s ease;
}

.variant-image:hover {
    border-color: #007bff;
    background: #f0f8ff;
    transform: scale(1.05);
}

.variant-image img {
    border-radius: 6px;
}

.variant-image .btn {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.variant-image:hover .btn {
    opacity: 1;
}

/* Total inventory styling */
#totalInventory {
    font-weight: 500;
    color: #6c757d;
}

/* Price apply button styling */
.btn-outline-primary {
    border-color: #007bff;
    color: #007bff;
    transition: all 0.2s ease;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    transform: scale(1.05);
}

.btn-outline-primary:active {
    transform: scale(0.95);
}

/* Price input group styling */
.input-group .btn {
    border-left: 0;
}

.input-group .form-control:focus + .btn {
    border-color: #007bff;
}
</style>
@endpush

<!-- Include Media Library Modal -->
@include('admin.media.partials.media-library-modal')

@push('styles')
<style>
.product-media-picker {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px;
    background: #fafbff;
}

.product-media-dropzone {
    border: 2px dashed #d5d9ff;
    border-radius: 12px;
    background: #ffffff;
    padding: 32px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    position: relative;
}

.product-media-dropzone:hover {
    border-color: #6366f1;
    background: #f3f4ff;
}

.product-media-empty i {
    font-size: 32px;
    color: #6366f1;
    display: block;
    margin-bottom: 10px;
}

.product-media-empty p {
    color: #6b7280;
    font-size: 14px;
}


.product-media-gallery {
    width: 100%;
}

.product-media-card {
    background: #ffffff;
    border: 1px solid #eceffc;
    border-radius: 18px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    padding: 18px 0px;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 180px;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-media-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 32px rgba(79, 70, 229, 0.15);
}

.product-media-card .product-media-thumb {
    width: 100%;
    height: 100px;
    /* border-radius: 50%; */
    background: linear-gradient(180deg, #f3f5ff 0%, #e9ecff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    overflow: hidden;
}

.product-media-card .product-media-thumb img {
    width: 100%;
    height: 100px;
}

.product-media-card .product-media-meta {
    width: 100%;
    border-top: 1px solid #eceffc;
    padding-top: 12px;
    margin-top: auto;
}

.product-media-card .product-media-name {
    font-weight: 600;
    font-size: 14px;
    color: #121826;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-media-card .product-media-size {
    font-size: 12px;
    color: #8c91a7;
    font-style: italic;
    margin-top: 2px;
}

.product-media-card .product-media-actions {
    margin-top: 12px;
}

.product-media-card .product-media-remove {
    font-size: 13px;
    color: #ef4444;
}

.product-media-dropzone.dragover {
    border-color: #4338ca;
    background: rgba(99, 102, 241, 0.08);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/media-library.js') }}"></script>
<script>
// Media Library Configuration - Check if already exists
if (typeof window.mediaLibraryConfig === 'undefined') {
window.mediaLibraryConfig = {
    uploadUrl: '{{ route("admin.media.upload") }}',
    libraryUrl: '{{ route("admin.media.library") }}',
    updateUrl: '{{ route("admin.media.update", ":id") }}',
    deleteUrl: '{{ route("admin.media.destroy", ":id") }}',
    uploadUrlUrl: '{{ route("admin.media.upload-url") }}',
    csrf: '{{ csrf_token() }}'
};
}

let productMedia = {
    dropzone: null,
    emptyState: null,
    gallery: null,
    primaryInput: null,
    hiddenInputsWrapper: null,
    selectedItems: [],
    eventListenersAdded: false
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM LOADED - INITIALIZING ===');
    console.log('DOM loaded, initializing product type handler...');
    
    const productTypeRadios = document.querySelectorAll('input[name="product_type"]');
    const simpleFields = document.getElementById('simpleProductFields');
    const variableFields = document.getElementById('variableProductFields');
    const generateVariationsCard = document.getElementById('generateVariationsCard');
    const variationsTableCard = document.getElementById('variationsTableCard');

    function toggleProductType(type) {
        if (type === 'simple') {
            if (simpleFields) simpleFields.style.display = 'block';
            if (variableFields) variableFields.style.display = 'none';
            if (generateVariationsCard) generateVariationsCard.style.display = 'none';
            if (variationsTableCard) variationsTableCard.style.display = 'none';
        } else {
            if (simpleFields) simpleFields.style.display = 'none';
            if (variableFields) variableFields.style.display = 'block';
            if (generateVariationsCard) generateVariationsCard.style.display = 'block';
            if (variationsTableCard) variationsTableCard.style.display = 'none';
        }
    }


    productTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleProductType(this.value);
        });
    });

    const checkedRadio = document.querySelector('input[name="product_type"]:checked');
    toggleProductType(checkedRadio ? checkedRadio.value : 'simple');

    // Add event listeners for Enter key to add new values
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('option-value')) {
            e.preventDefault();
            const optionGroup = e.target.closest('.option-group');
            if (optionGroup && e.target.value.trim()) {
                showAddAnotherValue(optionGroup);
            }
        }
    });

    initializeProductMediaPicker();
    
    // Add form submission handler for complete CRUD
    const form = document.querySelector('form[action*="products"]');
    console.log('Form found for submission handler:', !!form);
    
    if (form) {
        console.log('Attaching form submission handler...');
        
        form.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMISSION EVENT TRIGGERED ===');
            console.log('Event type:', e.type);
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            // Check if this is a variable product
            const productType = document.querySelector('input[name="product_type"]:checked');
            console.log('Product type:', productType ? productType.value : 'none');
            
            if (productType && productType.value === 'variable') {
                console.log('Variable product detected - collecting variant data');
                
                // Validate that at least one option is completed
                const completedOptions = document.querySelectorAll('.option-group.completed');
                console.log('Completed options count:', completedOptions.length);
                
                if (completedOptions.length === 0) {
                    console.log('No completed options found - preventing submission');
                    e.preventDefault();
                    showNotification('❌ Please complete at least one product option for variable products', 'error');
                    return false;
                }
                
                // Add variant data to form
                console.log('Adding variant data to form...');
                addVariantDataToForm();
                
                // Add variant images to form
                console.log('Adding variant images to form...');
                addVariantImagesToForm();
                
                // Show loading notification
                showNotification('🔄 Preparing product with variants for submission...', 'info');
            } else {
                console.log('Simple product detected - no variant data needed');
            }
            
            // Validate required fields
            const productName = document.getElementById('name');
            const categoryId = document.getElementById('category_id');
            const price = document.getElementById('price');
            
            console.log('Validation elements found:', {
                productName: !!productName,
                categoryId: !!categoryId,
                price: !!price
            });
            
            if (!productName || !productName.value.trim()) {
                console.log('Product name validation failed');
                e.preventDefault();
                showNotification('❌ Product name is required', 'error');
                if (productName) productName.focus();
                return false;
            }
            
            if (!categoryId || !categoryId.value) {
                console.log('Category validation failed');
                e.preventDefault();
                showNotification('❌ Category is required', 'error');
                if (categoryId) categoryId.focus();
                return false;
            }
            
            if (!price || !price.value || parseFloat(price.value) <= 0) {
                console.log('Price validation failed');
                e.preventDefault();
                showNotification('❌ Valid price is required', 'error');
                if (price) price.focus();
                return false;
            }
            
            console.log('All validations passed - allowing form submission');
            console.log('=== FORM SUBMISSION EVENT COMPLETED ===');
            return true;
        });
        
        console.log('Form submission handler attached successfully');
    } else {
        console.error('Form not found for submission handler');
    }
    
    // Final debug check
    console.log('=== FINAL DEBUG CHECK ===');
    console.log('All elements found:', {
        form: !!document.querySelector('form[action*="products"]'),
        nameField: !!document.getElementById('name'),
        categoryField: !!document.getElementById('category_id'),
        priceField: !!document.getElementById('price'),
        publishButton: !!document.querySelector('button[type="submit"]')
    });
    
    // Add click listener to publish button for debugging
    const publishButton = document.querySelector('button[type="submit"]');
    if (publishButton) {
        publishButton.addEventListener('click', function(e) {
            console.log('=== PUBLISH BUTTON CLICKED ===');
            console.log('Button clicked:', this);
            console.log('Form:', this.form);
            console.log('Event:', e);
        });
    }
    
    console.log('=== DOM INITIALIZATION COMPLETED ===');
});

function initializeProductMediaPicker() {
    productMedia.dropzone = document.getElementById('productMediaDropzone');
    productMedia.emptyState = document.getElementById('productMediaEmpty');
    productMedia.gallery = document.getElementById('productMediaGallery');
    productMedia.primaryInput = document.getElementById('primaryMediaId');
    productMedia.hiddenInputsWrapper = document.getElementById('selectedMediaInputs');

    const picker = document.querySelector('.product-media-picker');
    if (picker && picker.dataset.initialMedia) {
        try {
            const initialMedia = JSON.parse(picker.dataset.initialMedia);
            initialMedia.forEach(item => addMediaToGallery(item));
        } catch (error) {
            console.warn('Failed to parse initial media', error);
        }
    }

    if (!productMedia.dropzone) return;

    // Only add event listeners once to prevent duplicates
    if (!productMedia.eventListenersAdded) {
        productMedia.dropzone.addEventListener('click', openMediaLibrary);

        productMedia.dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            productMedia.dropzone.classList.add('dragover');
        });

        productMedia.dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            productMedia.dropzone.classList.remove('dragover');
        });

        productMedia.dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            productMedia.dropzone.classList.remove('dragover');
            
            const files = Array.from(e.dataTransfer.files).filter(file => 
                file.type.startsWith('image/')
            );
            
            if (files.length > 0) {
                // Show images immediately instead of uploading to media library
                files.forEach(file => showDirectImagePreview(file));
            } else {
                // If no image files, open media library as fallback
                openMediaLibrary();
            }
        });
        
        productMedia.eventListenersAdded = true;
    }
}

function openMediaLibrary(event = null) {
    if (event) event.stopPropagation();
    
    try {
        const modalElement = document.getElementById('mediaLibraryModal');
        if (!modalElement) {
            console.error('Media library modal not found');
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } catch (error) {
        console.error('Error opening media library:', error);
    }
}

function showDirectImagePreview(file) {
    // Create a preview URL for the file
    const previewUrl = URL.createObjectURL(file);
    
    // Create a temporary media object for the gallery
    const tempMedia = {
        id: 'temp_' + Date.now(),
        file_url: previewUrl,
        original_name: file.name,
        file_size_formatted: formatFileSize(file.size),
        file: file // Store the actual file object
    };
    
    // Add to gallery immediately
    addMediaToGallery(tempMedia);
    
    // Upload to media library in the background
    uploadToMediaLibrary(file, tempMedia.id);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function uploadToMediaLibrary(file, tempId) {
    console.log('Starting upload to media library:', file.name, tempId);
    const formData = new FormData();
    formData.append('files[0]', file);

    fetch(window.mediaLibraryConfig.uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.mediaLibraryConfig.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: formData
    })
    .then(async (res) => {
        console.log('Upload response status:', res.status);
        const status = res.status;
        let data = null;
        try {
            data = await res.json();
            console.log('Upload response data:', data);
        } catch (e) {
            console.log('Non-JSON response:', e);
            // Non-JSON response
        }

        if (res.ok && data && (data.success === true || (Array.isArray(data.uploaded) && data.uploaded.length > 0))) {
            console.log('Upload successful, replacing temporary media');
            // Success - replace temporary media with real media library data
            if (data.uploaded && data.uploaded.length > 0) {
                const uploadedMedia = {
                    id: data.uploaded[0].id,
                    file_url: data.uploaded[0].file_url,
                    original_name: data.uploaded[0].original_name,
                    file_size_formatted: data.uploaded[0].file_size_formatted || 'Unknown'
                };
                
                // Replace the temporary media with the real media library data
                replaceTemporaryMedia(tempId, uploadedMedia);
                showNotification(`Image uploaded to media library successfully`, 'success');
            }
        } else {
            console.log('Upload failed:', status, data);
            // Error handling
            if (data && Array.isArray(data.errors) && data.errors.length) {
                const firstErr = data.errors[0]?.error || 'Upload failed';
                console.log('Upload error:', firstErr);
                showNotification(firstErr, 'error');
            } else if (status === 419 || status === 401) {
                console.log('Session expired');
                showNotification('Session expired. Please refresh the page and try again.', 'error');
            } else {
                console.log('Generic upload error:', (data && data.message) || 'Upload failed');
                showNotification((data && data.message) || 'Upload failed', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Upload failed', 'error');
    });
}

function replaceTemporaryMedia(tempId, realMedia) {
    // Find and replace the temporary media item
    const tempIndex = productMedia.selectedItems.findIndex(item => item.id === tempId);
    if (tempIndex !== -1) {
        // Clean up the temporary object URL
        const tempItem = productMedia.selectedItems[tempIndex];
        if (tempItem.file_url && tempItem.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(tempItem.file_url);
        }
        
        // Replace with real media library data
        productMedia.selectedItems[tempIndex] = realMedia;
        renderMediaGallery();
    }
}

function addMediaToGallery(media) {
    if (!media || productMedia.selectedItems.some(item => item.id == media.id)) {
        return;
    }

    // Clean up any existing object URLs to prevent memory leaks
    productMedia.selectedItems.forEach(item => {
        if (item.file_url && item.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(item.file_url);
        }
    });

    productMedia.selectedItems.push(media);
    renderMediaGallery();
}

function removeMediaFromGallery(id) {
    // Find the item to remove and clean up its object URL
    const itemToRemove = productMedia.selectedItems.find(item => item.id == id);
    if (itemToRemove && itemToRemove.file_url && itemToRemove.file_url.startsWith('blob:')) {
        URL.revokeObjectURL(itemToRemove.file_url);
    }
    
    productMedia.selectedItems = productMedia.selectedItems.filter(item => item.id != id);
    renderMediaGallery();
}

function renderMediaGallery() {
    if (!productMedia.gallery || !productMedia.hiddenInputsWrapper) return;

    productMedia.gallery.innerHTML = '';
    productMedia.hiddenInputsWrapper.innerHTML = '';

    if (productMedia.selectedItems.length === 0) {
        productMedia.emptyState.classList.remove('d-none');
        if (productMedia.primaryInput) productMedia.primaryInput.value = '';
        return;
    }

    productMedia.emptyState.classList.add('d-none');

    productMedia.selectedItems.forEach((media, index) => {
        const col = document.createElement('div');
        col.className = 'col-lg-4 col-md-6 col-12';

        const card = document.createElement('div');
        card.className = 'product-media-card';
        card.innerHTML = `
            <div class="product-media-thumb">
                <img src="${media.file_url}" alt="${media.original_name}">
            </div>
            <div class="product-media-meta">
                <div class="product-media-name">${media.original_name}</div>
                <div class="product-media-size">${media.file_size_formatted || ''}</div>
            </div>
            <div class="product-media-actions">
                <button type="button" class="btn text-danger p-0 product-media-remove" data-id="${media.id}">Remove</button>
            </div>
        `;

        card.querySelector('.product-media-remove').addEventListener('click', (e) => {
            e.stopPropagation();
            removeMediaFromGallery(media.id);
        });

        col.appendChild(card);
        productMedia.gallery.appendChild(col);

        // Add media ID to hidden inputs for media library items only
        if (!String(media.id).startsWith('temp_')) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'media_ids[]';
            hidden.value = media.id;
            productMedia.hiddenInputsWrapper.appendChild(hidden);
        }

        if (index === 0 && productMedia.primaryInput) {
            if (String(media.id).startsWith('temp_')) {
                // For temporary files being uploaded, keep the temp value
                productMedia.primaryInput.value = 'uploading';
            } else {
                // For media library items, store the media ID
                productMedia.primaryInput.value = media.id;
            }
        }
    });
}

function selectMediaFromLibraryItems(items) {
    if (!items || !items.length) return;
    items.forEach(item => addMediaToGallery(item));
    const modal = bootstrap.Modal.getInstance(document.getElementById('mediaLibraryModal'));
    if (modal) modal.hide();
}

function uploadFilesToMediaLibrary(files) {
    const formData = new FormData();
    files.forEach(file => formData.append('files[]', file));

    // Show loading state
    const dropzone = productMedia.dropzone;
    const originalContent = dropzone.innerHTML;
    dropzone.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
            <p class="mb-0">Uploading ${files.length} image(s)...</p>
        </div>
    `;
    dropzone.style.pointerEvents = 'none';

    fetch(window.mediaLibraryConfig.uploadUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.mediaLibraryConfig.csrf,
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
            // Non-JSON response
        }

        if (res.ok && data && (data.success === true || (Array.isArray(data.uploaded) && data.uploaded.length > 0))) {
            // Success - add uploaded images to gallery
            if (data.uploaded && data.uploaded.length > 0) {
                data.uploaded.forEach(media => {
                    addMediaToGallery({
                        id: media.id,
                        file_url: media.file_url,
                        original_name: media.original_name,
                        file_size_formatted: media.file_size_formatted || 'Unknown'
                    });
                });
                showNotification(`Successfully uploaded ${data.uploaded.length} image(s)`, 'success');
            }
        } else {
            // Error handling
            if (data && Array.isArray(data.errors) && data.errors.length) {
                const firstErr = data.errors[0]?.error || 'Upload failed';
                showNotification(firstErr, 'error');
            } else if (status === 419 || status === 401) {
                showNotification('Session expired. Please refresh the page and try again.', 'error');
            } else {
                showNotification((data && data.message) || 'Upload failed', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Upload failed', 'error');
    })
    .finally(() => {
        // Restore original content
        dropzone.innerHTML = originalContent;
        dropzone.style.pointerEvents = 'auto';
        
        // Note: Event listeners are already initialized in initializeProductMediaPicker()
        // No need to re-add them here to avoid duplicates
    });
}

function showNotification(message, type = 'info') {
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
</script>
@endpush
