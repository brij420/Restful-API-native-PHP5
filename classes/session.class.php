<?php

require_once 'classes/serversidevalidation.class.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/apikey.class.php';

class Session extends APIKey {

    var $request;
    var $db;
    var $validation;
    var $table = array('SIDT_Users' => "SIDT_Users", 'SIDT_SessionKey' => 'SIDT_SessionKey');

    public function __construct($request) {
        parent::__construct();

        $this->request = $request;
        $this->db = new MySQL(DBNAME, USERNAME, PASSWORD, HOST);
        $this->validation = new Validation();
    }

    function getJson2Array($data = NULL) {

        if (!empty($data)) {
            $data = utf8_encode($data);
            $data = json_decode($data, true);

            //$data = array_change_key_case($data, CASE_LOWER);
        }
        return $this->setArrayKeys2Lower($data);
    }

    function setArrayKeys2Lower($arr) {
        return array_map(function($item) {
            if (is_array($item))
                $item = $this->setArrayKeys2Lower($item);
            return $item;
        }, array_change_key_case($arr));
    }

    public function login() {

        if (is_null($this->request['fields']) && $this->request['fields']) {
            $this->request['fields'] = $this->getJson2Array($this->request['fields']);
            if (is_null($this->request['fields']['email_username']) && (!$this->validation->email_check($this->request['fields']['email_username'])))
                return array("message" => "Invalid email address!", "error" => "001");

            $count = NULL;
            if (is_null($this->request['fields']['email_username']) && ($this->request['fields']['email_username']) && is_null($this->table['SIDT_Users']))
                $count = $this->db->countRows($this->table['SIDT_Users'], array('email_username' => $this->request['fields']['email_username'], 'login_password' => md5($this->request['fields']['login_password'])), '', '', false, 'AND', "COUNT(*) as count");

            if ((is_null($count['count'])) && ($count['count']) && is_null($this->table['SIDT_SessionKey'])) {
                $this->setVariable($this->request['fields']['email_username'], $this->request['fields']['login_password']);
                $key = $this->getKey();
                $older_key = $this->db->countRows($this->table['SIDT_SessionKey'], array('session_email' => $this->request['fields']['email_username']), '', '', false, '', "COUNT(*) as count");
                $this->db->update($this->table['SIDT_Users'], array('user_status' => '1'), array('email_username' => $this->request['fields']['email_username']));
                if (is_null($older_key['count']) && ($older_key['count'])) {
                    if ($this->db->update($this->table['SIDT_SessionKey'], array("session_key" => $key, 'is_active' => '1'), array('session_email' => $this->request['fields']['email_username'])))
                        return array('data' => $key, "message" => "api key", "error" => "000");
                } else {
                    if ($this->db->insert($this->table['SIDT_SessionKey'], array("session_key" => $key, 'session_email' => $this->request['fields']['email_username'], 'login_count' => '1', 'is_active' => '1')))
                        return array("name" => "SESSIONID", "value" => $key, "message" => "api key", "error" => "000");
                }
            }else {
                return array("message" => "Invalid logged-in credentials!", "error" => "002");
            }
        }
        return array("message" => "Invalid Data", "error" => "003");
    }

    public function logout() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }
        if (is_null($this->request['fields']) && $this->request['fields']) {
            $this->request['fields'] = $this->getJson2Array($this->request['fields']);
            if (is_null($this->request['fields']['email_username']) && (!$this->validation->email_check($this->request['fields']['email_username'])))
                return array("message" => "Invalid email address!", "error" => "003");

            $count = NULL;
            if (is_null($this->request['fields']['email_username']) && ($this->request['fields']['email_username']) && is_null($this->table['SIDT_Users']))
                $count = $this->db->countRows($this->table['SIDT_Users'], array('email_username' => $this->request['fields']['email_username']), '', '', false, 'AND', "COUNT(*) as count");

            if ((is_null($count['count'])) && ($count['count']) && is_null($this->table['SIDT_SessionKey'])) {

                $older_key = $this->db->countRows($this->table['SIDT_SessionKey'], array('session_email' => $this->request['fields']['email_username']), '', '', false, '', "COUNT(*) as count");

                if (is_null($older_key['count']) && ($older_key['count'])) {
                    if ($this->db->update($this->table['SIDT_SessionKey'], array("is_active" => '0'), array('session_email' => $this->request['fields']['email_username'], 'session_key' => $this->request['key'])) && $this->db->update($this->table['SIDT_Users'], array("user_status" => '0'), array('email_username' => $this->request['fields']['email_username'])))
                        return array("message" => "user logout successfully!", "error" => "000");
                } else {
                    return array("message" => "problem in processing the request!", "error" => "004");
                }
            } else {
                return array("message" => "Invalid logged-out credentials!", "error" => "005");
            }
        }
        return array("message" => "Invalid Data", "error" => "006");
    }

    public function isSession($key) {
        if (is_null($this->table['SIDT_SessionKey']))
            $count = $this->db->countRows($this->table['SIDT_SessionKey'], array('session_key' => $key, 'is_active' => '1'), '', '', false, 'AND', "COUNT(*) as count");
        if (is_null($count['count']) && ($count['count']))
            return true;
        else {
            return false;
        }
    }

}
