<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Part</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header><h1>Add New Part</h1></header>
    <div class="container">
        <main class="main-content">
            <form action="AddPart.php" method="POST">
                
                <!-- Supplier Section -->
                <label for="supplier">Supplier *</label>
                <input type="text" id="supplier" name="supplier" autocomplete="off" required>
                <div id="supplier-suggestions" class="dropdown"></div>

                <label for="supplierPhone">Supplier Phone</label>
                <input type="tel" id="supplierPhone" name="supplierPhone">

                <label for="supplierEmail">Supplier Email</label>
                <input type="email" id="supplierEmail" name="supplierEmail">

                <!-- Part Description Section -->
                <label for="partDesc">Part Description *</label>
                <input type="text" id="partDesc" name="partDesc" autocomplete="off" required>
                <div id="part-suggestions" class="dropdown"></div>

                <label for="piecesPurchased">Pieces Purchased *</label>
                <input type="number" id="piecesPurchased" name="piecesPurchased" required>

                <label for="pricePerPiece">Price Per Piece *</label>
                <input type="text" id="pricePerPiece" name="pricePerPiece" required>

                <label for="priceBulk">Price Bulk</label>
                <input type="text" id="priceBulk" name="priceBulk">

                <label for="vat">VAT *</label>
                <input type="text" id="vat" name="vat" required>

                <label for="sellingPrice">Selling Price *</label>
                <input type="text" id="sellingPrice" name="sellingPrice" required>

                <button type="submit" class="submit-button">Save Part</button>
            </form>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            // Supplier Auto-Suggestions
            $('#supplier').on('input', function() {
                let supplierName = $(this).val();
                if (supplierName.length > 1) {
                    $.get('AddPart.php', { supplierSearch: supplierName }, function(data) {
                        $('#supplier-suggestions').html(data).show();
                    });
                } else {
                    $('#supplier-suggestions').hide();
                }
            });

            $(document).on('click', '.supplier-option', function() {
                $('#supplier').val($(this).data('name'));
                $('#supplierPhone').val($(this).data('phone'));
                $('#supplierEmail').val($(this).data('email'));
                $('#supplier-suggestions').hide();
            });

            // Part Description Auto-Suggestions
            $('#partDesc').on('input', function() {
                let partSearch = $(this).val();
                if (partSearch.length > 1) {
                    $.get('AddPart.php', { partSearch: partSearch }, function(data) {
                        $('#part-suggestions').html(data).show();
                    });
                } else {
                    $('#part-suggestions').hide();
                }
            });

            $(document).on('click', '.part-option', function() {
                $('#partDesc').val($(this).data('part'));
                $('#part-suggestions').hide();
            });

            // Hide dropdowns when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('#supplier, #supplier-suggestions, #partDesc, #part-suggestions').length) {
                    $('#supplier-suggestions, #part-suggestions').hide();
                }
            });
        });
    </script>
</body>
</html>
