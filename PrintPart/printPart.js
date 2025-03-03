document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, loading part data..."); // Debugging message
    loadPartData(); // Fetch part data when the page loads

    // Attach an event listener to the "Print" button to trigger printing
    document.getElementById("printButton").addEventListener("click", printPartDetails);
});

/*
 * Fetches part data from fetchPart.php and updates HTML with the retrieved information.
 */
function loadPartData() {
    const partId = 3; // Default PartID for testing
    const url = `fetchPart.php?PartID=${partId}`;
    console.log(`Fetching part data from ${url}...`); // Debugging message

    fetch(url) // Request data from the backend
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok: " + response.statusText);
            }
            return response.json(); // Convert response into JSON
        })
        .then(data => {
            console.log("Fetched Data:", data); // Debugging Output in Console

            if (!data || data.error) {    // If an error occurred or no data was found, log an error and stop execution
                console.error("Error loading part data:", data.error);
                return;
            }
             
            document.getElementById("description").innerText = data.description || "N/A";
            document.getElementById("supplier").innerText = data.supplier || "N/A";
            document.getElementById("piecesPurchased").innerText = data.pieces_purchased || "N/A";
            document.getElementById("pricePerPiece").innerText = data.price_per_piece || "N/A";
            document.getElementById("stockQuantity").innerText = data.stock_quantity || "N/A";
            document.getElementById("vat").innerText = data.vat || "N/A";
            document.getElementById("sellingPrice").innerText = data.selling_price || "N/A";
            console.log("Part data updated successfully."); // Debugging message
        })
        .catch(error => console.error("Fetch error:", error)); // Catch and log any errors during fetch
}

/*Triggers the print dialog to print the current page content.*/
function printPartDetails() {
    console.log("Print button clicked, triggering print..."); // Debugging message
    window.print();
}