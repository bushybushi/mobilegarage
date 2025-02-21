<?php
/**
 * Function to sanitize and validate user inputs.
 * 
 * @param array $inputs An associative array of form inputs.
 * @return array Sanitized and validated inputs.
 */
function sanitizeInputs($inputs) {
    // Initialize an array to store sanitized data
    $sanitized = [];

    // Sanitize and format each input
    $sanitized['firstName'] = ucfirst(strtolower(filter_var(trim($inputs['firstName']), FILTER_SANITIZE_STRING)));
    $sanitized['surname'] = ucfirst(strtolower(filter_var(trim($inputs['surname']), FILTER_SANITIZE_STRING)));
    $sanitized['companyName'] = filter_var(trim($inputs['companyName']), FILTER_SANITIZE_STRING);
    $sanitized['address'] = filter_var(trim($inputs['address']), FILTER_SANITIZE_STRING);
    $sanitized['phoneNumber'] = filter_var(trim($inputs['phoneNumber']), FILTER_SANITIZE_STRING);

    // Sanitize and validate email address
    $email = filter_var(trim($inputs['emailAddress']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("<h1>Error: Invalid Email Address</h1>");
    }
    $sanitized['emailAddress'] = $email;

    return $sanitized;
}
?>