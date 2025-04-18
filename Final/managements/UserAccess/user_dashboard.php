<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify user authentication
if (!isset($_SESSION['user'])) {
    // Redirect unauthenticated users to login page
    header('Location: index.php');
    exit;
}

// Load required files and initialize view
require_once 'UserSystem.php';
$view = new UserView();
// Display dashboard for authenticated user
$view->showDashboard($_SESSION['user']); 
?>
<script>
  // Auto-hide success messages after 3 seconds
  document.addEventListener('DOMContentLoaded', function() {
    var msg = document.querySelector('.message.success');
    if (msg) {
      setTimeout(function() {
        msg.style.display = 'none';
      }, 3000);
    }
  });
</script> 