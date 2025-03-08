function loadParts() {
    fetch('fetch_parts.php')
        .then(response => response.json())
        .then(parts => {
            console.log("Fetched Parts:", parts); // Debugging log

            const tableBody = document.getElementById('partsTable');
            tableBody.innerHTML = '';

            if (parts.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="5">No parts found.</td></tr>`;
                return;
            }

            parts.forEach(part => {
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td><input type="checkbox" class="part-checkbox" value="${part.PartDesc}"></td>
                    <td>${part.PartDesc}</td>
                    <td>${part.Suppliers}</td>
                    <td>${part.Contacts}</td>
                    <td>${part.TotalStock}</td>
                `;

                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error loading parts:', error));
}

// Function to generate the print table
function generatePrintTable(parts) {
    let tableHTML = `<table border="1" style="width:100%;border-collapse:collapse;text-align:left;">
                        <thead>
                            <tr>
                                <th>Part Description</th>
                                <th>Suppliers</th>
                                <th>Contacts</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>`;

    parts.forEach(row => {
        tableHTML += `<tr>
                        <td>${row.children[1].innerText}</td>
                        <td>${row.children[2].innerText}</td>
                        <td>${row.children[3].innerText}</td>
                        <td>${row.children[4].innerText}</td>
                      </tr>`;
    });

    tableHTML += `</tbody></table>`;
    return tableHTML;
}

// Function to print without opening a new page
function printContent(printTableHTML) {
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'absolute';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = 'none';

    document.body.appendChild(printFrame);

    const doc = printFrame.contentDocument || printFrame.contentWindow.document;
    doc.open();
    doc.write(`
        <html>
            <head>
                <style>
                    table, th, td {
                        border: 1px solid black;
                        border-collapse: collapse;
                        padding: 10px;
                    }
                </style>
            </head>
            <body>
                ${printTableHTML}
            </body>
        </html>`);
    doc.close();

    printFrame.contentWindow.focus();
    printFrame.contentWindow.print();

    setTimeout(() => {
        document.body.removeChild(printFrame);
    }, 1000);
}

// Function to print selected parts
function printSelectedParts() {
    const selectedRows = Array.from(document.querySelectorAll('.part-checkbox:checked'))
        .map(cb => cb.parentElement.parentElement);

    if (selectedRows.length === 0) {
        alert("No parts selected.");
        return;
    }

    const printTableHTML = generatePrintTable(selectedRows);
    printContent(printTableHTML);
}

// Function to print all parts
function printAllParts() {
    const allRows = document.querySelectorAll('#partsTable tr');
    const printTableHTML = generatePrintTable(Array.from(allRows));
    printContent(printTableHTML);
}

// Ensure parts load when page opens
document.addEventListener("DOMContentLoaded", loadParts);
