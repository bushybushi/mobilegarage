<?php

function sanitizeInputs($data) {
    foreach ($data as $key => $value) {
        $data[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
    return $data;
}
?>