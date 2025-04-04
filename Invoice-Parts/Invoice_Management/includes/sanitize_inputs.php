<?php
/* CODE CREATED BY JORGOS XIDIAS AND TEAM
  AI HAS BEEN USED TO BEAUTIFY AND ADD COMMENTS*/

/**
 * Cleans and secures user input to prevent security vulnerabilities
 * 
 * This function:
 * - Removes whitespace from start and end
 * - Converts special characters to HTML entities to prevent XSS
 * - Removes null bytes that could be used for attacks
 * 
 * @param string $input The raw user input to clean
 * @return string The sanitized, safe version of the input
 */
function sanitize_input($input) {
    if (empty($input)) {
        return '';
    }
    
    // Remove any extra spaces at start or end of input
    $input = trim($input);
    
    // Convert special characters like < > & " ' to their safe HTML versions
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    // Remove null bytes that could be used in attacks
    $input = str_replace("\0", '', $input);
    
    return $input;
}

/**
 * Sanitizes an entire array of inputs recursively
 * 
 * This is useful for cleaning $_POST or $_GET arrays that contain
 * multiple values that all need to be sanitized
 * 
 * @param array $inputs Array of values to sanitize
 * @return array The sanitized array with all values cleaned
 */
function sanitize_array($inputs) {
    // If input isn't an array, return empty array to prevent errors
    if (!is_array($inputs)) {
        return [];
    }
    
    // Create a new array to store sanitized values
    $sanitized = [];
    foreach ($inputs as $key => $value) {
        // If the value is an array, recursively sanitize it
        if (is_array($value)) {
            $sanitized[$key] = sanitize_array($value);
        } else {
            // If it's a single value, sanitize it directly
            $sanitized[$key] = sanitize_input($value);
        }
    }
    
    return $sanitized;
}
?>
