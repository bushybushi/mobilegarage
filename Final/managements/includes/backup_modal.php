<div class="modal fade" id="backupRestoreModal" tabindex="-1" aria-labelledby="backupRestoreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow p-3" style="background-color: #0A58CA; color: white;">
      <div class="modal-body text-center">
        <h5 class="mb-3">Backup</h5>
        <p>
		Last time Backup: 
		<?php 
			$file = '/MGAdmin2025/backup/last_backup.txt';
			echo file_exists($file) ? file_get_contents($file) : "No backup yet.";
		?>
		<p id="backupResult" class="mt-2 text-warning"></p>
		</p>
        <form id="backupForm">
    <button type="submit" class="btn btn-light text-primary w-50 rounded-pill mb-2">Backup</button>
  </form>

  <form id="restoreForm">
    <button type="submit" class="btn btn-light text-primary w-50 rounded-pill mb-2">Restore</button>
  </form>
      </div>
    </div>
  </div>
</div>

<script>
// Backup dynamic display for errors handling
$(document).ready(function () {
  $('#backupForm').on('submit', function (e) {
    e.preventDefault(); // Prevent default form submit

    // Disable button and show "Backing up..." text
    const $btn = $(this).find('button');
	const $result = $('#backupResult');
    $btn.prop('disabled', true).text('Backing up...');

    $.ajax({
      url: '/MGAdmin2025/managements/includes/backup.php',
      type: 'POST',
      success: function (response) {
        $result.text(response).css('color', '#90ee90'); // light green
      },
      error: function (xhr, status, error) {
        $result.text("Backup failed: " + error).css('color', '#ffcccb'); // light red
      },
      complete: function () {
        $btn.prop('disabled', false).text('Backup');
      }
    });
  });
});
// Restore dynamic display for errors handling
$(document).ready(function () {
  $('#restoreForm').on('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const $btn = $(this).find('button');
    const $modal = $('#restoreModal');
    const $result = $('#backupResult');

    $btn.prop('disabled', true).text('Restoring...');

    $.ajax({
      url: '/MGAdmin2025/managements/includes/restore.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        $modal.modal('show');
        $result.text(response).css('color', '#90ee90');
      },
      error: function (xhr, status, error) {
        $modal.modal('show');
        $result.text("Restore failed: " + error).css('color', '#ffcccb');
      },
      complete: function () {
        $btn.prop('disabled', false).text('Restore');
      }
    });
  });
});
</script>