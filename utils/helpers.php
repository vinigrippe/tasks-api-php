<?php
function hash_string($input_string) {
    $hashed_string = hash('sha256', $input_string);
    return $hashed_string;
} 