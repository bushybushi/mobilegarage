// Filter button functionality
document.getElementById('filterButton').addEventListener('click', function() {
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;

    if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
        alert('Start Date cannot be greater than End Date.');
        return;
    }

    var url = new URL(window.location.href);
    var params = new URLSearchParams(url.search);
    if (startDate) params.set('startDate', startDate);
    if (endDate) params.set('endDate', endDate);

    url.search = params.toString();
    window.location.href = url.toString();
});

// Print functionality
document.getElementById('printButton').addEventListener('click', function() {
    const tableRows = document.querySelectorAll('#jobCardsTable tbody tr');
    let totalProfit = 0;
    
    // Create the print content
    let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                }
                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 30px;
                }
                .logo {
                    max-height: 80px;
                }
                .title {
                    text-align: right;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f8f9fa;
                }
                .total-profit {
                    text-align: right;
                    font-weight: bold;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div>
                    <img src="../assets/logo.png" alt="Logo" style="max-height: 80px;">
                </div>
                <div class="title">
                    <h2>Job Cards</h2>
                    <p>Total Job Cards: ${tableRows.length}</p>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Job Start/End Date</th>
                        <th>Expenses</th>
                        <th>Income</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>`;

    // Add table rows
    tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        printContent += '<tr>';
        // Skip the first cell (icon) and add the rest
        for (let i = 1; i < cells.length; i++) {
            printContent += `<td>${cells[i].textContent}</td>`;
        }
        printContent += '</tr>';
        
        // Add to total profit
        const profit = parseFloat(cells[5].getAttribute('data-profit')) || 0;
        totalProfit += profit;
    });

    // Complete the HTML content
    printContent += `
                </tbody>
            </table>
            <div class="total-profit">
                Total Profit: ${totalProfit.toFixed(2)}
            </div>
        </body>
        </html>`;

    // Get the iframe
    const frame = document.getElementById('printFrame');
    
    // Write the content to the iframe
    frame.contentWindow.document.open();
    frame.contentWindow.document.write(printContent);
    frame.contentWindow.document.close();
    
    // Wait for images to load then print
    frame.contentWindow.onload = function() {
        frame.contentWindow.print();
    };
});

// Popup functionality
setTimeout(function() {
    let popup = document.getElementById("customPopup");
    if (popup) {
        popup.style.animation = "fadeOut 0.5s ease-in-out";
        setTimeout(() => popup.remove(), 500);
    }
}, 3000);

// Open form functionality
function openForm(jobId) {
    window.location.href = '../../JobCard_Management/views/job_card_view.php?id=' + jobId;
} 