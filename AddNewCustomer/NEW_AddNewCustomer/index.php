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
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars(isset($sanitizedInputs['address']) ? $sanitizedInputs['address'] : ''); ?>">
            <?php if (isset($errors['address'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['address']); ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="phoneNumber">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars(isset($sanitizedInputs['phoneNumber']) ? $sanitizedInputs['phoneNumber'] : ''); ?>">
            <?php if (isset($errors['phoneNumber'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['phoneNumber']); ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="emailAddress">Email Address</label>
            <input type="email" id="emailAddress" name="emailAddress" value="<?php echo htmlspecialchars(isset($sanitizedInputs['emailAddress']) ? $sanitizedInputs['emailAddress'] : ''); ?>" required>
            <?php if (isset($errors['emailAddress'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['emailAddress']); ?></div>
            <?php endif; ?>
        </div>
        <input type="submit" value="Add Customer">
    </form>
</body>
</html>