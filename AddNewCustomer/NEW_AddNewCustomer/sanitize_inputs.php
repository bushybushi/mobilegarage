<?php

function sanitizeInputs($inputs, &$errors) {
    // Initialize an array to store sanitized data
    $sanitized = [];

    // Sanitize and validate first name
    $firstName = filter_var(trim($inputs['firstName']), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    if (!preg_match('/^[A-Za-z\s]+$/', $firstName)) {
        $errors['firstName'] = "First Name can only contain alphabetic characters and spaces.";
    } else {
        $sanitized['firstName'] = ucfirst(strtolower($firstName));
    }

    // Sanitize and validate surname
    $surname = filter_var(trim($inputs['surname']), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    if (!preg_match('/^[A-Za-z\s]+$/', $surname)) {
        $errors['surname'] = "Surname can only contain alphabetic characters and spaces.";
    } else {
        $sanitized['surname'] = ucfirst(strtolower($surname));
    }

    // Sanitize and validate company name
    $companyName = filter_var(trim($inputs['companyName']), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    if (!preg_match('/^[A-Za-z0-9\s\-\.\']+$/u', $companyName)) {
        $errors['companyName'] = "Company Name can only contain alphanumeric characters, spaces, hyphens, periods, and apostrophes.";
    } else {
        $sanitized['companyName'] = $companyName;
    }

    // Sanitize and validate address
    $address = filter_var(trim($inputs['address']), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    if (!preg_match('/^[A-Za-z0-9\s\,\-\.\']+$/u', $address)) {
        $errors['address'] = "Address can only contain alphanumeric characters, spaces, commas, hyphens, periods, and apostrophes.";
    } else {
        $sanitized['address'] = $address;
    }

    // Sanitize and validate phone number
    $phoneNumber = filter_var(trim($inputs['phoneNumber']), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    if (!preg_match('/^\+?[0-9\s\-\(\)]{7,15}$/', $phoneNumber)) {
        $errors['phoneNumber'] = "Phone Number is invalid. It should be a valid phone number format.";
    } else {
        $sanitized['phoneNumber'] = $phoneNumber;
    }

    // Sanitize and validate email address
    $email = filter_var(trim($inputs['emailAddress']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['emailAddress'] = "Invalid Email Address";
    } else {
        $sanitized['emailAddress'] = $email;
    }

    return $sanitized;
}
?>
