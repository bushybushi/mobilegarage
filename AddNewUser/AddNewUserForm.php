<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>


<body>
    <div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            Add New User
        </div>
    </div>
        <form action="User.php" method="POST">
            <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['username']) ? $sanitizedInputs['username'] : ''); ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['username']); ?></div>
                    <?php endif; ?>
               </div>
               <div class="form-group">
                    <label for="passwrd">Password</label>
                    <input type="password" id="passwrd" name="passwrd" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['passwrd']) ? $sanitizedInputs['passwrd'] : ''); ?>" required>
                    <?php if (isset($errors['passwrd'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['passwrd']); ?></div>
                    <?php endif; ?> 
            </div>
            <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['email']) ? $sanitizedInputs['email'] : ''); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
                    </div>
                    <div class="form-group">
                    <label for="admin">Admin</label>
                    <select id="admin" name="admin" class="form-control" required>
                        <option value="no" <?php echo (isset($sanitizedInputs['admin']) && $sanitizedInputs['admin'] == 'no') ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo (isset($sanitizedInputs['admin']) && $sanitizedInputs['admin'] == 'yes') ? 'selected' : ''; ?>>Yes</option>
                    </select>
                    <?php if (isset($errors['admin'])): ?>
                        <div class="text-danger"><?php echo htmlspecialchars($errors['admin']); ?></div>
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