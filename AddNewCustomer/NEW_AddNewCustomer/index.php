<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Add New Customer</h1>
    <form action="AddNewCustomer.php" method="POST">
        <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars(isset($sanitizedInputs['firstName']) ? $sanitizedInputs['firstName'] : ''); ?>" required>
            <?php if (isset($errors['firstName'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['firstName']); ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="surname">Surname</label>
            <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars(isset($sanitizedInputs['surname']) ? $sanitizedInputs['surname'] : ''); ?>" required>
            <?php if (isset($errors['surname'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['surname']); ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="companyName">Company Name</label>
            <input type="text" id="companyName" name="companyName" value="<?php echo htmlspecialchars(isset($sanitizedInputs['companyName']) ? $sanitizedInputs['companyName'] : ''); ?>">
            <?php if (isset($errors['companyName'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['companyName']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Addresses -->
        <div id="addresses">
            <div class="form-group">
                <label for="address[]">Address</label>
                <input type="text" id="address[]" name="address[]" value="">
            </div>
        </div>
        <button type="button" onclick="addAddressField()">Add Another Address</button>

        <!-- Phone Numbers -->
        <div id="phoneNumbers">
            <div class="form-group">
                <label for="phoneNumber[]">Phone Number</label>
                <input type="tel" id="phoneNumber[]" name="phoneNumber[]" value="" required>
            </div>
        </div>
        <button type="button" onclick="addPhoneNumberField()">Add Another Phone Number</button>

        <!-- Email Addresses -->
        <div id="emailAddresses">
            <div class="form-group">
                <label for="emailAddress[]">Email Address</label>
                <input type="email" id="emailAddress[]" name="emailAddress[]" value="">
            </div>
        </div>
        <button type="button" onclick="addEmailAddressField()">Add Another Email Address</button>

        <input type="submit" value="Add Customer">
    </form>

    <script>
        function addAddressField() {
            const container = document.getElementById('addresses');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="address[]">Address</label>
                <input type="text" id="address[]" name="address[]" value="">
                <button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
            `;
            container.appendChild(newField);
        }

        function addPhoneNumberField() {
            const container = document.getElementById('phoneNumbers');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="phoneNumber[]">Phone Number</label>
                <input type="tel" id="phoneNumber[]" name="phoneNumber[]" value="" required>
                <button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
            `;
            container.appendChild(newField);
        }

        function addEmailAddressField() {
            const container = document.getElementById('emailAddresses');
            const newField = document.createElement('div');
            newField.className = 'form-group';
            newField.innerHTML = `
                <label for="emailAddress[]">Email Address</label>
                <input type="email" id="emailAddress[]" name="emailAddress[]" value="">
                <button type="button" onclick="removeField(this)" class="remove-btn">Remove</button>
            `;
            container.appendChild(newField);
        }

        function removeField(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>
