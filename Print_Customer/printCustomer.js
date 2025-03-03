document.addEventListener("DOMContentLoaded", function () {
    loadCustomerData(); // Fetch customer data when the page loads

    // Attach an event listener to the "Print" button to trigger printing
    document.getElementById("printButton").addEventListener("click", printCustomerDetails);
});

/*
 * Fetches customer and car data from fetchCustomer.php and updates HTML with the retrieved information.
 */
function loadCustomerData() {
    fetch("http://localhost/Print_Customer/fetchCustomer.php") // Request data from the backend
        .then(response => response.json()) // Convert response into JSON
        .then(data => {
            console.log("Fetched Data:", data); // Debugging Output in Console

            if (!data || data.error) {    // If an error occurred or no data was found, log an error and stop execution
                console.error("Error loading customer data:", data.error);
                return;
            }
             
            document.getElementById("firstName").innerText = data.first_name || "N/A";
            document.getElementById("lastName").innerText = data.last_name || "N/A";
            document.getElementById("companyName").innerText = data.company_name || "N/A";

            // Format and display addresses
            document.getElementById("address").innerHTML = data.addresses.length 
                ? data.addresses.map(addr => `<p>${addr}</p>`).join("") 
                : "<p>No Address Found</p>";

            // Format and display phone numbers
            document.getElementById("phone").innerHTML = data.phones.length 
                ? data.phones.map(ph => `<p>${ph}</p>`).join("") 
                : "<p>No Phone Number Found</p>";

            // Format and display emails
            document.getElementById("email").innerHTML = data.emails.length 
                ? data.emails.map(em => `<p>${em}</p>`).join("") 
                : "<p>No Email Found</p>";

            console.log("Car Data:", data.cars);// Log retrieved car data for debugging

            let carSection = document.getElementById("carDetails");// Update car information section in the HTML
            carSection.innerHTML = ""; // Clear previous content
            
            // Check if car data exists
            if (Array.isArray(data.cars) && data.cars.length > 0) {
                data.cars.forEach((car, index) => {
                    console.log(`Rendering Car ${index + 1}:`, car); // Debugging Output in Console

                    // Create car container with formatted car details
                    carSection.innerHTML += `
                        <div class="car-container">
                            <h4>🚗 Car ${index + 1}</h4>
                            <table>
                                <tr><td><strong>Brand:</strong></td> <td>${car.Brand || "N/A"}</td></tr>
                                <tr><td><strong>Model:</strong></td> <td>${car.Model || "N/A"}</td></tr>
                                <tr><td><strong>License Plate:</strong></td> <td>${car.LicenseNr || "N/A"}</td></tr>
                                <tr><td><strong>VIN:</strong></td> <td>${car.VIN || "N/A"}</td></tr>
                                <tr><td><strong>Manufacture Date:</strong></td> <td>${car.ManuDate || "N/A"}</td></tr>
                                <tr><td><strong>Fuel Type:</strong></td> <td>${car.Fuel || "N/A"}</td></tr>
                                <tr><td><strong>Horsepower:</strong></td> <td>${car.KWHorse || "N/A"}</td></tr>
                                <tr><td><strong>Engine:</strong></td> <td>${car.Engine || "N/A"}</td></tr>
                                <tr><td><strong>Kilometers/Miles:</strong></td> <td>${car.KMMiles || "N/A"}</td></tr>
                                <tr><td><strong>Color:</strong></td> <td>${car.Color || "N/A"}</td></tr>
                                <tr><td><strong>Comments:</strong></td> <td>${car.Comments || "N/A"}</td></tr>
                            </table>
                        </div>
                        <hr>
                    `;
                });
            } else {
      
                carSection.innerHTML = "<p>No Cars Found</p>"; // If no car data exists, display a message
            }
        })
        .catch(error => console.error("Fetch error:", error)); // Catch and log any errors during fetch
}

/*Triggers the print dialog to print the current page content.*/
function printCustomerDetails() {
    window.print();
}
