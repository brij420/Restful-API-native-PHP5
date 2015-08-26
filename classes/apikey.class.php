<?php

class APIKey {

    var $email;
    var $password;
    var $key;

    function __construct() {
        
    }

    function setVariable($email, $password) {
        $this->email = $email;
        $this->password = $password;
        $this->__setKey();
        return;
    }

    function __setKey() {
        $this->key = md5(md5($this->email) . md5($this->password) . time());
    }

    function getKey() {
        return $this->key;
    }

}
