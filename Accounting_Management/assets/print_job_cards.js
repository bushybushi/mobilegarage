document.addEventListener('DOMContentLoaded', function () {
    // Attach event listener to the Print button
    const printButton = document.querySelector('.btn-success');
    if (printButton) {
        printButton.addEventListener('click', function () {
            printFilteredJobCards();
        });
    }
});

function printFilteredJobCards() {
    // Get the table containing the job cards
    const jobCardsTable = document.getElementById('jobCardsTable');
    if (!jobCardsTable) {
        alert('No job cards table found to print.');
        return;
    }

    // Clone the table to avoid modifying the original
    const clonedTable = jobCardsTable.cloneNode(true);

    // Remove rows that are hidden (filtered out)
    const rows = clonedTable.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (row.style.display === 'none') {
            row.remove();
        }
    });

    // Count the visible rows (filtered job cards)
    const visibleJobCardsCount = clonedTable.querySelectorAll('tbody tr').length;

    // Calculate total profit from the visible job cards
    let totalProfit = 0;
    const profitCells = clonedTable.querySelectorAll('td.profit');
    profitCells.forEach(cell => {
        const profit = parseFloat(cell.dataset.profit) || 0;
        totalProfit += profit;
    });

    // Create a printable HTML structure
    const printContent = `
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Print Job Cards</title>
            <style>
                @media print {
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    .header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 20px;
                        padding-bottom: 20px;
                        border-bottom: 2px solid #ddd;
                    }
                    .logo {
                        width: 250px;
                        height: auto;
                    }
                    .header-text {
                        text-align: right;
                        flex-grow: 1;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        page-break-inside: auto;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f8f9fa;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }
                    thead {
                        display: table-header-group;
                    }
                    tfoot {
                        display: table-footer-group;
                    }
                    .total-profit {
                        margin-top: 20px;
                        font-weight: bold;
                        font-size: 18px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="../assets/logo.png" alt="Mobile Garage Larnaca Logo" class="logo">
                <div class="header-text">
                    <h1>Job Cards</h1>
                    <p>Total Job Cards: ${visibleJobCardsCount}</p>
                    <p>Generated on: ${new Date().toLocaleString()}</p>
                </div>
            </div>
            <table>
                <thead>
                    ${clonedTable.querySelector('thead').innerHTML}
                </thead>
                <tbody>
                    ${clonedTable.querySelector('tbody').innerHTML}
                </tbody>
            </table>
            <div class="total-profit">Total Profit: €${totalProfit.toFixed(2)}</div>
        </body>
        </html>
    `;

    // Replace the current page's content with the printable content
    const originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;

    // Trigger the print dialog
    window.print();

    // Restore the original content after printing
    document.body.innerHTML = originalContent;
    location.reload(); // Reload the page to restore event listeners
}