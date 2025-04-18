<?php
/**
 * About Modal Component
 * This file contains the HTML and JavaScript for the About modal
 * Include this file in any page where you want the About modal to be available
 */
?>

<!-- About Modal -->
<div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="aboutModalLabel">About</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <img src="https://mobilegaragelarnaca.com/MGAdmin2025/managements/includes/cutlogo.jpg" alt="Cyprus University of Technology Logo" style="max-width: 200px; margin-bottom: 15px;">
        </div>
        
        <div class="about-content">
          <p>Το σύστημα αναπτύχθηκε από τους: Αντώνης Ανδρέου, Gabriel Vasile, Γεώργιος Αρχιτεκτονίδης, Γιώργος Ξύδιας, Κυριάκος Ανδρέου, Στυλιανός Κυπριανού, τριτοετείς φοιτητές του τμήματος Ηλεκτρολόγων Μηχανικών και Μηχανικών Ηλεκτρονικών Υπολογιστών και Πληροφορικής του Τεχνολογικού Πανεπιστημίου Κύπρου, υπό την επίβλεψη του Καθηγητή Ανδρέα Σ. Ανδρέου στα πλαίσια του μαθήματος "Εργασία Τεχνολογίας Λογισμικού και Επαγγελματική Πρακτική" του πτυχίου Μηχανικών Ηλεκτρονικών Υπολογιστών και Πληροφορικής.</p>
          
          <p class="text-center mt-4">Λεμεσός, Μάιος 2025</p>
          <p class="text-center">Copyright © Cyprus University of Technology</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript to handle the About modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Find all About links in the dropdown
  const aboutLinks = document.querySelectorAll('a[href="about_modal.php"]');
  
  // Add click event to each About link
  aboutLinks.forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      // Use Bootstrap 5 modal API
      const aboutModal = new bootstrap.Modal(document.getElementById('aboutModal'));
      aboutModal.show();
    });
  });
});
</script> 