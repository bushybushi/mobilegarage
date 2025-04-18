<!-- JavaScript to handle AJAX request and update results dynamically -->
   
        $(document).ready(function() {
            // Detect when user types in the search input field
            $("#searchInput").keyup(function() {
                var query = $(this).val(); // Get the value typed by the user
                
                if (query != "") {
                    // Make an AJAX request to the search.php file
                    $.ajax({
                        url: "search.php", // PHP file to process the search
                        method: "GET",
                        data: { query: query }, // Send the query to the PHP file
                        success: function(response) {
                            // Display the search results
                            $("#searchResults").html(response).show();
                        }
                    });
                } else {
                    // If no query, clear the search results
                    $("#searchResults").html("").hide();
                }
            });
        });

    
 <!-- AJAX Script -->

        $(document).ready(function() {
            // Handle form submission via AJAX
            $("#registerForm").submit(function(event) {
                event.preventDefault(); // Prevent the default form submission (no page reload)

                // Send the form data via AJAX
                $.ajax({
                    url: "register.php", // PHP file that processes the form
                    type: "POST",
                    data: $(this).serialize(), // Serialize the form data
                    success: function(response) {


                        // Show the success message in the modal
                        $("#successMessage").html(response); // Display the response from PHP
                        $("#successModal").modal("show"); // Show the modal

                        // Optionally, hide the modal after a few seconds
                        setTimeout(function() {
                            $("#successModal").modal("hide");
                        }, 5000); // Hide after 5 seconds


                        
                    }
                });
            });
        });
   