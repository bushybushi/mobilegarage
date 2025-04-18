<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function showMessage($message) {
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['message']);
    }
}

function showError($error) {
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['error']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['error']);
    }
}

function showMessages() {
    showMessage($_SESSION['message'] ?? null);
    showError($_SESSION['error'] ?? null);
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var alerts = document.querySelectorAll('.alert-success, .alert-danger');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      if (alert.classList.contains('show')) {
        // For Bootstrap 5 fade out
        alert.classList.remove('show');
        setTimeout(function() { alert.style.display = 'none'; }, 150);
      } else {
        alert.style.display = 'none';
      }
    }, 3000);
  });
});
</script> 