<?php
// Takes a multi-dimensional array and converts it into a single-level array
// This is useful when you need to process nested data structures as a simple list
function flattenArray(array $array): array {
    // Create an array to store the flattened values
    $flattened = [];
    
    // Walk through the array recursively, adding each value to our flattened array
    // This will go through all nested levels and extract each value
    array_walk_recursive($array, function($value) use (&$flattened) {
        $flattened[] = $value;
    });
    
    return $flattened;
}

?>