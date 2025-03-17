<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   
</head>
<body>
    <!-- Customer Form Container -->
    <div class="form-container">
        <div class="top-container d-flex justify-content-between align-items-center">
            <a href="javascript:void(0);" onclick="window.location.href='customer_main.php'" class="back-arrow">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex-grow-1 text-center">
                <h5>Add Customer</h5>
            </div>
            <div style="width: 30px;"></div>
        </div>
        <form action="../controllers/add_customer_controller.php" method="POST">
            <div class="form-group">
                <div class="form-row">
                <div class="col">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" name="firstName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['firstName']) ? $sanitizedInputs['firstName'] : ''); ?>" required>
                    <?php if (isset($errors['firstName'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['firstName']); ?></div>
                    <?php endif; ?>
                    </div>
                    <div class="col">
                    <label for="surname">Surname *</label>
                    <input type="text" id="surname" name="surname" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['surname']) ? $sanitizedInputs['surname'] : ''); ?>" required>
                    <?php if (isset($errors['surname'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['surname']); ?></div>
                    <?php endif; ?>
                </div>
                </div>
                </div>
                <div class="form-group">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="companyName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['companyName']) ? $sanitizedInputs['companyName'] : ''); ?>">
                    <?php if (isset($errors['companyName'])): ?>
                        <div class="error"><?php echo htmlspecialchars($errors['companyName']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Addresses -->
                <div id="addresses">
                    <div class="form-group">
                        <label for="address[]">Address</label>
                        <div class="input-group">
                            <input type="text" id="address[]" name="address[]" value="" class="form-control" style="padding-right: 40px;">
                            <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Phone Numbers -->
                <div id="phoneNumbers">
                    <div class="form-group">
                        <label for="phoneNumber[]">Phone Number</label>
                        <div class="input-group">
                            <input type="tel" id="phoneNumber[]" name="phoneNumber[]" value="" class="form-control" required style="padding-right: 40px;">
                            <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Addresses -->
                <div id="emailAddresses">
                    <div class="form-group">
                        <label for="emailAddress[]">Email Address</label>
                        <div class="input-group">
                            <input type="email" id="emailAddress[]" name="emailAddress[]" value="" class="form-control" style="padding-right: 40px;">
                            <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cars Container -->
                <div id="carsContainer">
                    <!-- Car forms will be added here -->
                </div>

                <div class="btngroup">
                    <button type="button" class="btn btn-success" onclick="addCarForm()">Add Car</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
             </div>
        </form>
    </div>

    <!-- Car Form Template -->
    <div id="carFormTemplate" style="display: none;">
        <div class="form-container mb-3">
            <div class="top-container d-flex justify-content-between align-items-center mb-3">
                <h5>Car Details</h5>
                <div class="d-flex align-items-center">
                    <span class="car-status text-success mr-3"></span>
                    <button type="button" class="btn btn-link text-danger" onclick="removeCarForm(this)" style="padding: 0;">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label>Brand *</label>
                        <input type="text" name="car[brand][]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Model *</label>
                        <input type="text" name="car[model][]" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label>License Plate *</label>
                        <input type="text" name="car[licenseNr][]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Vehicle Identification Number (VIN) *</label>
                        <input type="text" name="car[vin][]" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label>Manufacture Date *</label>
                        <input type="date" name="car[manuDate][]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Fuel Type *</label>
                        <input type="text" name="car[fuel][]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Kw/Horsepower</label>
                        <input type="number" step="0.1" name="car[kwHorse][]" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="form-row">
                    <div class="col">
                        <label>Engine Type *</label>
                        <input type="text" name="car[engine][]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Km/Miles *</label>
                        <input type="number" step="0.1" name="car[kmMiles][]" class="form-control" required>
                    </div>
                    <div class="col">
                        <label>Color *</label>
                        <input type="text" name="car[color][]" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Comments</label>
                <textarea name="car[comments][]" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </div>

    <script>
        function addAddressField() {
            const container = document.getElementById('addresses');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="address[]">Address</label>
                <div class="input-group">
                    <input type="text" id="address[]" name="address[]" value="" class="form-control" style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addAddressField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newField);
        }

        function addPhoneNumberField() {
            const container = document.getElementById('phoneNumbers');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="phoneNumber[]">Phone Number</label>
                <div class="input-group">
                    <input type="tel" id="phoneNumber[]" name="phoneNumber[]" value="" class="form-control" required style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addPhoneNumberField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newField);
        }

        function addEmailAddressField() {
            const container = document.getElementById('emailAddresses');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="emailAddress[]">Email Address</label>
                <div class="input-group">
                    <input type="email" id="emailAddress[]" name="emailAddress[]" value="" class="form-control" style="padding-right: 80px;">
                    <div class="input-group-append" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                        <button type="button" class="btn btn-link" onclick="addEmailAddressField()" style="padding: 0;">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-link text-danger" onclick="removeField(this)" style="padding: 0; margin-left: 5px;">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newField);
        }

        function removeField(button) {
            button.closest('.form-group').remove();
        }

        function addCarForm() {
            const container = document.getElementById('carsContainer');
            const template = document.getElementById('carFormTemplate');
            const carForm = template.cloneNode(true);
            carForm.style.display = 'block';
            carForm.removeAttribute('id');
            container.appendChild(carForm);
            carForm.scrollIntoView({ behavior: 'smooth' });
        }

        function removeCarForm(button) {
            if (confirm('Are you sure you want to remove this car?')) {
                button.closest('.form-container').remove();
            }
        }
    </script>
</body>
</html>
