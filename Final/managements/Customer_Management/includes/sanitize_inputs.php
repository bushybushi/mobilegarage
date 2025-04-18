<?php

function sanitizeInputs($data) {
    // Check if $data is an array
    if (is_array($data)) {
        // Loop through each element of the array
        foreach ($data as $key => $value) {
            // Recursively sanitize each value if it is an array
            if (is_array($value)) {
                $data[$key] = sanitizeInputs($value);
            } else {
                // If it's a string, sanitize it by trimming and encoding special characters
                $data[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
        }
    } else {
        // If it's a string, sanitize it by trimming and encoding special characters
        $data = trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
    }
    
    return $data;
}
?>
