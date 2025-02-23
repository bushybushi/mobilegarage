// Function to collect selected customer IDs
function getSelectedCustomers() {
    const selectedCustomers = [];
    const checkboxes = document.querySelectorAll('.customer-checkbox:checked');
    checkboxes.forEach((checkbox) => {
        selectedCustomers.push(checkbox.value);
    });
    return selectedCustomers;
}

// Function to generate the customer table for printing
function generatePrintTable(customers) {
    let tableHTML = `<table id="print-customer-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>`;

    customers.forEach((customer) => {
        tableHTML += `<tr>
                        <td>${customer.firstName} ${customer.lastName}</td>
                        <td>${customer.email}</td>
                        <td>${customer.phone}</td>
                        <td>${customer.address}</td>
                      </tr>`;
    });

    tableHTML += `</tbody></table>`;
    return tableHTML;
}

// Function to handle printing logic
function openPrintPopup(customersData) {
    const selectedCustomers = getSelectedCustomers();

    if (selectedCustomers.length === 0) {
        alert("No customers selected.");
        return;
    }

    const userChoice = confirm("Do you want to print the information of selected customers?");

    if (userChoice) {
        // Create a list of selected customer data
        const selectedCustomerDetails = selectedCustomers.map((id) => {
            const customer = customersData.find(cust => cust.CustomerID == id);
            return {
                firstName: customer.FirstName,
                lastName: customer.LastName,
                email: customer.Emails,
                phone: customer.Nr,
                address: customer.Address
            };
        });

        // Generate the HTML table for the selected customers
        const printTableHTML = generatePrintTable(selectedCustomerDetails);

        // Create a hidden iframe and add the table to it
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write('<html><head><style>table,th,td{border-collapse:collapse;border:1px solid;width:100%;text-align:left;padding:5px;}</style>');
        iframeDoc.write('<title>Print Customer Information</title></head><body>');
        iframeDoc.write(printTableHTML);
        iframeDoc.write('</body></html>');
        iframeDoc.close();

        // Trigger the print dialog for the iframe content
        iframe.contentWindow.focus();
        iframe.contentWindow.print();

        // Remove the iframe after printing
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    } else {
        alert("Printing is canceled.");
    }
}
