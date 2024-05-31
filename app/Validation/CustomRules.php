<?php
namespace App\Validation;

class CustomRules
{
    public function validatePasswordStrength(string $str, string &$error = null): bool
    {
        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*\W).+$/';
        if (preg_match($pattern, $str)) {
            return true;
        } else {
            $error = 'The password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.';
            return false;
        }
    }
}

?>