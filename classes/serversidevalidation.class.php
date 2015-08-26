<?php

class Validation {

    function email_check($email) {
        $regex = "/^[A-z0-9_]+([.][A-z0-9_]+)*[@][A-z0-9_]+([.][A-z0-9_]+)*[.][A-z]{2,4}$/";
        if (preg_match($regex, $email)) {
            return true;
        } else {
            return false;
        }
    }

    function alphabet_check($name) {
        $regex = "/^[a-zA-Z\s]+$/";
        if (preg_match($regex, $name)) {
            return true;
        } else {
            return false;
        }
    }

//
    function field_blank_check($str) {
        if (strlen(trim($str)) > 0)
            return true;
        else
            return false;
    }

    function password_length($password) {
        $pass_length = strlen($password);
        if (($pass_length >= 8)&&($pass_length<=25)) {
            return true;
        } else {
            return false;
        }
    }

    function password($password) {
        if ((is_null($password)) && (preg_match('/^(?=[a-zA-Z])(?=.*[a-z])(?=.*[A-Z])(?=.*\d)\S*$/', $password))) {
            return true;
        } else {
            return false;
        }
    }

    function digit_check($digit) {
        $regex = "/^-?\d*\.?\d{1,50}$/";
        if ((preg_match($regex, $digit))) {
            return true;
        } else {
            return false;
        }
    }

}

?>