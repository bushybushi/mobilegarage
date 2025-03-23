<?php
/**
 * Sanitizes user input to prevent XSS and other security issues
 * 
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitize_input($input) {
    if (empty($input)) {
        return '';
    }
    
    // Remove leading/trailing whitespace
    $input = trim($input);
    
    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    // Remove any null bytes
    $input = str_replace("\0", '', $input);
    
    return $input;
}

/**
 * Sanitizes an array of inputs
 * 
 * @param array $inputs The array of inputs to sanitize
 * @return array The sanitized array
 */
function sanitize_array($inputs) {
    if (!is_array($inputs)) {
        return [];
    }
    
    $sanitized = [];
    foreach ($inputs as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_array($value);
        } else {
            $sanitized[$key] = sanitize_input($value);
        }
    }
    
    return $sanitized;
}
?>
