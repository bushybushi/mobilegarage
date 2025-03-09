<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tabler Icons via CDN -->
    <!--link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabler-icons-webfont@latest/css/tabler-icons.min.css">-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        function generateRandomPassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let password = "";
            for (let i = 0; i < length; ++i) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById("passwrd").value = password;
        }

        $(document).ready(function() {
            $('#updateForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'AddUser.php', // Ensure this path is correct
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        showPopupMessage(response.status, response.message);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        showPopupMessage('error', 'AJAX error: ' + textStatus + ' - ' + errorThrown);
                    }
                });
            });

            function showPopupMessage(type, message) {
                const popupClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const html = `<div class="alert ${popupClass} mt-4">${message}</div>`;
                $('#popupMessage').html(html).fadeIn();
            }
        });
    </script>
</head>
<body>
    <div class="form-container">
        <div class="top-container d-flex justify-content-center align-items-center">
            <div>User Management</div>
        </div>
        <form id="updateForm" method="POST">
            <input type="hidden" name="action" value="add"> <!-- Specify action -->
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="passwrd">Password</label>
                <input type="text" id="passwrd" name="passwrd" class="form-control" required>
                <a href="javascript:void(0);" onclick="generateRandomPassword()">Generate Random Password</a>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="admin">Admin</label>
                <select id="admin" name="admin" class="form-control" required>
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
        
        <div id="popupMessage" style="display:none;"></div>
    </div>
</body>
</html>