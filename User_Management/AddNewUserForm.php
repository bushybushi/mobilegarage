<di?php
// AddNewUserForm.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        function generateRandomPassword() {
            var length = 12;
            var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            var password = "";
            for (var i = 0, n = charset.length; i < length; ++i) {
                password += charset.charAt(Math.floor(Math.random() * n));
            }
            document.getElementById("passwrd").value = password;
        }
    </script>

</head>
<body>
<div class="form-container">
    <div class="top-container d-flex justify-content-center align-items-center">
        <div>
            User Management
        </div>
    </div>
    <form action="adduser.php" method="POST">
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
            <label for="security_question">Security Question</label>
            <select id="security_question" name="security_question_id" class="form-control" required>
                <option value="1">What was your first pet's name?</option>
                <option value="2">What is your mother's maiden name?</option>
                <option value="3">What was the name of your first school?</option>
                <option value="4">What city were you born in?</option>
                <option value="5">What is your favorite book?</option>
            </select>
        </div>
        <div class="form-group">
            <label for="security_answer">Answer</label>
            <input type="text" id="security_answer" name="security_answer" class="form-control" required>
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
        <div class="btngroup">
        <button type="submit" class="btn btn-primary">Save</button>
        </div>
        </div>
    </form>
</div>

</body>
</html>
