// Function to add a new address field dynamically to the form
function addAddressField() {
    const container = document.getElementById('addresses');
    const newField = document.createElement('div');
    newField.className = 'form-group';
    newField.innerHTML = `
        <label for="address[]">Address</label>
        <div class="input-group">
            <input type="text" name="address[]" class="form-control" style="padding-right: 80px;">
            <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newField);
}

// Function to add a new phone number field dynamically to the form
function addPhoneNumberField() {
    const container = document.getElementById('phoneNumbers');
    const newField = document.createElement('div');
    newField.className = 'form-group';
    newField.innerHTML = `
        <label for="phoneNumber[]">Phone Number</label>
        <div class="input-group">
            <input type="tel" name="phoneNumber[]" class="form-control" required style="padding-right: 80px;">
            <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newField);
}

// Function to add a new email address field dynamically to the form
function addEmailAddressField() {
    const container = document.getElementById('emailAddresses');
    const newField = document.createElement('div');
    newField.className = 'form-group';
    newField.innerHTML = `
        <label for="emailAddress[]">Email Address</label>
        <div class="input-group">
            <input type="email" name="emailAddress[]" class="form-control" style="padding-right: 80px;">
            <div class="input-group-append" style="position: absolute; right: 0px; top: 50%; transform: translateY(-50%); z-index: 10;">
                <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 0px;">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newField);
}

// Function to remove a field (address, phone, or email) from the form
function removeField(button) {
    button.closest('.form-group').remove();
}

// Function to show error messages
function showErrorMessage(message) {
    const popup = document.createElement('div');
    popup.className = 'alert alert-danger alert-dismissible fade show';
    popup.innerHTML = `
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span>${message}</span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    document.querySelectorAll('.alert-danger').forEach(el => el.remove());
    document.querySelector('.form-container').insertBefore(popup, document.querySelector('form'));
    popup.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        $(popup).alert('close');
    }, 5000);
}

// Function to show success messages
function showSuccessMessage(message) {
    const successAlert = document.getElementById('successAlert');
    document.getElementById('successMessage').textContent = message;
    successAlert.classList.add('show');
    successAlert.style.display = 'block';
    successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        successAlert.classList.remove('show');
        setTimeout(() => {
            successAlert.style.display = 'none';
        }, 150);
    }, 3000);
} 