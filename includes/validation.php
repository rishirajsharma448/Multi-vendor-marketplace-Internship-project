<?php
// Vyapar Setu Form Input Validation Helper

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password, $minLength = 6) {
    return strlen($password) >= $minLength;
}

function validatePhone($phone) {
    return preg_match('/^[0-9\-\+\s\(\)]{8,20}$/', $phone);
}
