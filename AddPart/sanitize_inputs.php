<?php
function sanitizeInputs($data, &$errors) {
    $sanitized = [];

    // ✅ Sanitize Part Description
    if (!empty($data['partDesc'])) {
        $sanitized['partDesc'] = htmlspecialchars(trim($data['partDesc']), ENT_QUOTES, 'UTF-8');
    } else {
        $errors['partDesc'] = "Part Description is required.";
    }

    // ✅ Sanitize Supplier Name
    if (!empty($data['supplier'])) {
        $sanitized['supplier'] = htmlspecialchars(trim($data['supplier']), ENT_QUOTES, 'UTF-8');
    } else {
        $errors['supplier'] = "Supplier name is required.";
    }

    // ✅ Validate Pieces Purchased (must be an integer)
    if (!empty($data['piecesPurchased']) && filter_var($data['piecesPurchased'], FILTER_VALIDATE_INT)) {
        $sanitized['piecesPurchased'] = (int) $data['piecesPurchased'];
    } else {
        $errors['piecesPurchased'] = "Pieces Purchased must be a valid integer.";
    }

    // ✅ Validate Price Per Piece (must be a float)
    if (!empty($data['pricePerPiece']) && filter_var($data['pricePerPiece'], FILTER_VALIDATE_FLOAT)) {
        $sanitized['pricePerPiece'] = (float) $data['pricePerPiece'];
    } else {
        $errors['pricePerPiece'] = "Price Per Piece must be a valid number.";
    }

    // ✅ Validate Price Bulk (optional but must be a float if provided)
    if (!empty($data['priceBulk'])) {
        if (filter_var($data['priceBulk'], FILTER_VALIDATE_FLOAT)) {
            $sanitized['priceBulk'] = (float) $data['priceBulk'];
        } else {
            $errors['priceBulk'] = "Price Bulk must be a valid number.";
        }
    }

    // ✅ Validate VAT (must be a float)
    if (!empty($data['vat']) && filter_var($data['vat'], FILTER_VALIDATE_FLOAT)) {
        $sanitized['vat'] = (float) $data['vat'];
    } else {
        $errors['vat'] = "VAT must be a valid number.";
    }

    // ✅ Validate Selling Price (must be a float)
    if (!empty($data['sellingPrice']) && filter_var($data['sellingPrice'], FILTER_VALIDATE_FLOAT)) {
        $sanitized['sellingPrice'] = (float) $data['sellingPrice'];
    } else {
        $errors['sellingPrice'] = "Selling Price must be a valid number.";
    }

    // ✅ Validate Supplier Email (optional but must be valid if provided)
    if (!empty($data['supplierEmail'])) {
        if (filter_var($data['supplierEmail'], FILTER_VALIDATE_EMAIL)) {
            $sanitized['supplierEmail'] = trim($data['supplierEmail']);
        } else {
            $errors['supplierEmail'] = "Invalid email format.";
        }
    }

    // ✅ Validate Supplier Phone Number (optional but sanitized)
    if (!empty($data['supplierPhone'])) {
        $sanitized['supplierPhone'] = preg_replace('/[^0-9+]/', '', trim($data['supplierPhone'])); // Remove non-numeric characters
    }

    // ✅ Ensure at least one contact detail (email or phone) is provided
    if (empty($sanitized['supplierEmail']) && empty($sanitized['supplierPhone'])) {
        $errors['supplierContact'] = "You must provide either a Supplier Email or a Phone Number.";
    }

    return $sanitized;
}
?>
