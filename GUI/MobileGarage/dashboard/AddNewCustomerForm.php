<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>


<body>
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            Add New Customer
        </div>
    </div>
        <form action="Customer.php" method="POST">
            <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['firstName']) ? $sanitizedInputs['firstName'] : ''); ?>" required>
                    <?php if (isset($errors['firstName'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['firstName']); ?></div>
                    <?php endif; ?>
               </div>
               <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" id="surname" name="surname" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['surname']) ? $sanitizedInputs['surname'] : ''); ?>" required>
                    <?php if (isset($errors['surname'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['surname']); ?></div>
                    <?php endif; ?> 
            </div>
            <div class="form-group">
                    <label for="companyName">Company Name</label>
                    <input type="text" id="companyName" name="companyName" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['companyName']) ? $sanitizedInputs['companyName'] : ''); ?>">
                    <?php if (isset($errors['companyName'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['companyName']); ?></div>
                    <?php endif; ?>
                    </div>
                    <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['address']) ? $sanitizedInputs['address'] : ''); ?>">
                    <?php if (isset($errors['address'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['address']); ?></div>
                    <?php endif; ?>
            </div>
            <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['phoneNumber']) ? $sanitizedInputs['phoneNumber'] : ''); ?>">
                    <?php if (isset($errors['phoneNumber'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['phoneNumber']); ?></div>
                    <?php endif; ?>
                    </div>
                    <div class="form-group">
                    <label for="emailAddress">Email Address</label>
                    <input type="email" id="emailAddress" name="emailAddress" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['emailAddress']) ? $sanitizedInputs['emailAddress'] : ''); ?>" required>
                    <?php if (isset($errors['emailAddress'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['emailAddress']); ?></div>
                    <?php endif; ?>
            </div>
            <div id="btngroup2">
            <button type="submit" id="bottombtn" class="btn btn-primary">Save
            <span>
                   <i class="ti ti-check"></i>
         </span>
            </button>
            </div>
        </form>
    </div>
</body>
</html>