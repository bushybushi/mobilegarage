<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <h2 class="mb-0">Add Car</h2>
    </div>
    <form action="../controllers/add_car_controller.php" method="POST">
    <div class="form-group">
        <div class="form-row">
        <div class="col">
            <label for="brand">Brand *</label>
            <input type="text" id="brand" name="brand" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['brand']) ? $sanitizedInputs['brand'] : ''); ?>" required>
            <?php if (isset($errors['brand'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['brand']); ?></div>
            <?php endif; ?>
            </div>
            <div class="col">
            <label for="model">Model *</label>
            <input type="text" id="model" name="model" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['model']) ? $sanitizedInputs['model'] : ''); ?>" required>
            <?php if (isset($errors['model'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['model']); ?></div>
            <?php endif; ?>
        </div>
        </div>
        </div>
        <div class="form-group">
        <div class="form-row">
        <div class="col">
            <label for="licenseNr">License Plate *</label>
            <input type="text" id="licenseNr" name="licenseNr" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['licenseNr']) ? $sanitizedInputs['licenseNr'] : ''); ?>" required>
            <?php if (isset($errors['licenseNr'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['licenseNr']); ?></div>
            <?php endif; ?>
            </div>

            <div class="col">
            <label for="vin">Vehicle Identification Number (VIN) *</label>
            <input type="text" id="vin" name="vin" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['vin']) ? $sanitizedInputs['vin'] : ''); ?>" required>
            <?php if (isset($errors['vin'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['vin']); ?></div>
            <?php endif; ?>
            </div>
        </div>
        </div>

        <div class="form-group">
        <div class="form-row">
        <div class="col">
            <label for="manuDate">Manufacture Date *</label>
            <input type="date" id="manuDate" name="manuDate" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['manuDate']) ? $sanitizedInputs['manuDate'] : ''); ?>" required>
            <?php if (isset($errors['manuDate'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['manuDate']); ?></div>
            <?php endif; ?>
            </div>

            <div class="col">
            <label for="fuel">Fuel Type *</label>
            <input type="text" id="fuel" name="fuel" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['fuel']) ? $sanitizedInputs['fuel'] : ''); ?>" required>
            <?php if (isset($errors['fuel'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['fuel']); ?></div>
            <?php endif; ?>
            </div>

            <div class="col">
            <label for="kwHorse">Kw/Horsepower</label>
            <input type="number" step="0.1" id="kwHorse" name="kwHorse" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['kwHorse']) ? $sanitizedInputs['kwHorse'] : ''); ?>">
            <?php if (isset($errors['kwHorse'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['kwHorse']); ?></div>
            <?php endif; ?>
            </div>
        </div>
        </div>

        <div class="form-group">
        <div class="form-row">
        <div class="col">
            <label for="engine">Engine Type *</label>
            <input type="text" id="engine" name="engine" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['engine']) ? $sanitizedInputs['engine'] : ''); ?>" required>
            <?php if (isset($errors['engine'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['engine']); ?></div>
            <?php endif; ?>
            </div>

            <div class="col">
            <label for="kmMiles">Km/Miles *</label>
            <input type="number" step="0.1" id="kmMiles" name="kmMiles" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['kmMiles']) ? $sanitizedInputs['kmMiles'] : ''); ?>" required>
            <?php if (isset($errors['kmMiles'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['kmMiles']); ?></div>
            <?php endif; ?>
            </div>

            <div class="col">
            <label for="color">Color *</label>
            <input type="text" id="color" name="color" class="form-control" value="<?php echo htmlspecialchars(isset($sanitizedInputs['color']) ? $sanitizedInputs['color'] : ''); ?>" required>
            <?php if (isset($errors['color'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['color']); ?></div>
            <?php endif; ?>
            </div>
        </div>
        </div>

        <div class="form-group">
            <label for="comments">Comments</label>
            <textarea id="comments" name="comments" class="form-control" rows="3"><?php echo htmlspecialchars(isset($sanitizedInputs['comments']) ? $sanitizedInputs['comments'] : ''); ?></textarea>
            <?php if (isset($errors['comments'])): ?>
                <div class="error"><?php echo htmlspecialchars($errors['comments']); ?></div>
            <?php endif; ?>
        </div>

        <div class="btngroup">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
     </div>
    </form>

</body>
</html>
