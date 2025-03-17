<?php
function flattenArray(array $array): array {
    $flattened = [];
    array_walk_recursive($array, function($value) use (&$flattened) {
        $flattened[] = $value;
    });
    return $flattened;
}

?>