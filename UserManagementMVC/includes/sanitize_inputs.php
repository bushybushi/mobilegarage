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
    if (!empty($companyName) && !preg_match('/^[A-Za-z0-9\s\-\.\']+$/u', $companyName)) {
        $errors['companyName'] = "Company Name can only contain alphanumeric characters, spaces, hyphens, periods, and apostrophes.";
    } else {
        $sanitized['companyName'] = $companyName;
    }

    // Sanitize and validate addresses
    $sanitized['address'] = [];
    if (isset($inputs['address']) && is_array($inputs['address'])) {
        foreach ($inputs['address'] as $address) {
            $address = filter_var(trim($address), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            if (!empty($address) && !preg_match('/^[A-Za-z0-9\s\,\-\.\']+$/u', $address)) {
                $errors['address'][] = "Address can only contain alphanumeric characters, spaces, commas, hyphens, periods, and apostrophes.";
            } else {
                $sanitized['address'][] = $address;
            }
        }
    }

    // Sanitize and validate phone numbers
    $sanitized['phoneNumber'] = [];
    if (isset($inputs['phoneNumber']) && is_array($inputs['phoneNumber'])) {
        foreach ($inputs['phoneNumber'] as $phoneNumber) {
            $phoneNumber = filter_var(trim($phoneNumber), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            if (!empty($phoneNumber) && !preg_match('/^\+?[0-9\s\-\(\)]{7,15}$/', $phoneNumber)) {
                $errors['phoneNumber'][] = "Phone Number is invalid. It should be a valid phone number format.";
            } else {
                $sanitized['phoneNumber'][] = $phoneNumber;
            }
        }
    }

    // Sanitize and validate email addresses
    $sanitized['emailAddress'] = [];
    if (isset($inputs['emailAddress']) && is_array($inputs['emailAddress'])) {
        foreach ($inputs['emailAddress'] as $email) {
            $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['emailAddress'][] = "Invalid Email Address";
            } else {
                $sanitized['emailAddress'][] = $email;
            }
        }
    }

    return $sanitized;
}
?>