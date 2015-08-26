<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class Profile extends Session {

    var $request;
    var $db;
    var $validation;
    var $table = array('SIDT_Profiles' => 'SIDT_Profiles', 'SIDT_SessionKey' => 'SIDT_SessionKey');

    public function __construct($request) {
        // parent::__construct();

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

    function checkAssoc($array) {
        if (is_array($array))
            return ($array !== array_values($array));
        return false;
    }

    public function create() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }
        if (is_null($this->request['fields']) && $this->request['fields']) {
            $this->request['fields'] = $this->getJson2Array($this->request['fields']);

            if (is_null($this->request['fields']['pemail_username']) && (!$this->validation->email_check($this->request['fields']['pemail_username'])))
                return array("message" => "Invalid email address!", "error" => "003");
            $count = NULL;
            if (is_null($this->request['fields']['pemail_username']) && ($this->request['fields']['pemail_username']) && is_null($this->table['SIDT_Profiles']))
                $count = $this->db->countRows($this->table['SIDT_Profiles'], array('pemail_username' => $this->request['fields']['pemail_username']), '', '', false, '', "COUNT(*) as count");

            if (is_null($count['count']) && (!$count['count'])) {
                if ($this->db->insert($this->table['SIDT_Profiles'], $this->request['fields'])) {
                    if (is_null($this->db->lastError) && ($this->db->lastError)) {
                        return array("message" => "query error due to Invalid parameter!", "error" => "111");
                    }
                    return array("message" => "user profile saved successfully!", "error" => "000");
                } else {
                    return array("message" => "problem in saving data!", "error" => "004");
                }
            } else {
                return array("message" => "duplicate data entry!", "error" => "005");
            }
        }
        return array("message" => "Invalid Data", "error" => "006");
    }

    public function get() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }
        //if (is_null($this->request['select'])) {
        $this->request['filter'] = !empty($this->request['filter']) ? $this->getJson2Array($this->request['filter']) : NULL;
        $this->request['select'] = (is_null($this->request['select']) && ($this->request['select'])) ? $this->request['select'] : '*';
        $this->request['conj'] = ((is_null($this->request['conj'])) && ($this->request['conj'])) ? $this->request['conj'] : 'AND';
        $this->request['limit'] = (is_null($this->request['limit']) && ($this->request['limit'])) ? $this->request['limit'] : '';
        $this->request['offset'] = (is_null($this->request['offset']) && ($this->request['offset'])) ? $this->request['offset'] : '';
        $this->request['sortby'] = (is_null($this->request['sortby']) && ($this->request['sortby'])) ? $this->request['sortby'] : '';
        $list = null;
        if (!empty($this->request['filter']) && is_null($this->table['SIDT_Profiles'])) {
            $list = $this->db->select($this->table['SIDT_Profiles'], $this->request['filter'], $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], $this->request['select'], $this->request['offset']);
        } else {
            if (is_null($this->table['SIDT_Profiles']))
                $list = $this->db->select($this->table['SIDT_Profiles'], '', $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], $this->request['select'], $this->request['offset']);
        }
        // }
        $count = '';
        if ((is_array($list))) {
            $count = (!$this->checkAssoc($list)) ? count($list) : ($this->checkAssoc($list) ? $this->db->records : 0);
            $list = $this->setValue2null($list, $count);
        }
        if (!empty($list) && is_array($list)) {
            return array("List" => (!$this->checkAssoc($list)) ? (array) $list : array("0" => $list), 'count' => $count, "message" => "List", "error" => "000");
        } elseif (empty($count) && (!$count) && (!empty($list) || empty($list))) {
            return array("List" => (is_null($list) && is_array($list)) ? $list : NULL, 'count' => $count, "message" => "No match Found!", "error" => "000");
        } else {
            return array("message" => "Invalid Data", "error" => "003");
        }
    }

    function setValue2null($array = array(), $count = 0) {

        if ($count > 1) {
            for ($i = 0; $i < $count; $i++) {
                $array[$i]['tiny_profile_pic'] = (isset($array[$i]['tiny_profile_pic']) && ($array[$i]['tiny_profile_pic'])) ? TPP_URL . $array[$i]['tiny_profile_pic'] : null;
                $array[$i]['small_profie_pic'] = (isset($array[$i]['small_profie_pic']) && ($array[$i]['small_profie_pic'])) ? SPP_URL . $array[$i]['small_profie_pic'] : null;
                $array[$i]['medium_profile_pic'] = (isset($array[$i]['medium_profile_pic']) && ($array[$i]['medium_profile_pic'])) ? MPP_URL . $array[$i]['medium_profile_pic'] : null;
                $array[$i]['large_profile_pic'] = (isset($array[$i]['large_profile_pic']) && ($array[$i]['large_profile_pic'])) ? LPP_URL . $array[$i]['large_profile_pic'] : null;
                if (isset($array[$i]) && is_array($array[$i]))
                    foreach ($array[$i] as $key => $value) {
                        if (isset($value) && preg_match("/^[\d]+$/", $value))
                            $value = (int) $value;
                        if (isset($value) && is_null($value))
                            $value = null;
                    }
            }
        } else {
            $array['tiny_profile_pic'] = (isset($array['tiny_profile_pic']) && ($array['tiny_profile_pic'])) ? TPP_URL . $array['tiny_profile_pic'] : null;
            $array['small_profie_pic'] = (isset($array['small_profie_pic']) && ($array['small_profie_pic'])) ? SPP_URL . $array['small_profie_pic'] : null;
            $array['medium_profile_pic'] = (isset($array['medium_profile_pic']) && ($array['medium_profile_pic'])) ? MPP_URL . $array['medium_profile_pic'] : null;
            $array['large_profile_pic'] = (isset($array['large_profile_pic']) && ($array['large_profile_pic'])) ? LPP_URL . $array['large_profile_pic'] : null;

            foreach ($array as $key => $value) {
                if (isset($value) && preg_match("/^[\d]+$/", $value))
                    $value = (int) $value;
                if (isset($value) && is_null($value))
                    $value = null;
            }
        }
        return $array;
    }


    public function update() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }

        if (is_null($this->request['set']) && $this->request['set']) {

            $this->request['set'] = $this->getJson2Array($this->request['set']);
            $this->request['cond'] = $this->getJson2Array($this->request['cond']);
            if (is_null($this->request['set']['pemail_username']) && (!$this->validation->email_check($this->request['set']['pemail_username']))) {
                return array("message" => "Invalid email!", "error" => "003");
            }
            if (is_null($this->request['cond']['pemail_username']) && (!$this->validation->email_check($this->request['cond']['pemail_username']))) {
                return array("message" => "Invalid email!", "error" => "004");
            }



            if (is_null($this->table['SIDT_Profiles']) && $this->db->update($this->table['SIDT_Profiles'], $this->request['set'], $this->request['cond'])) {
                if (is_null($this->db->lastError) && ($this->db->lastError)) {
                    return array("message" => "query error due to Invalid parameter!", "error" => "111");
                }
                if ($this->db->get_affected_rows() > 0) {
                    return array('message' => "data updated successfully", "error" => "000");
                } elseif ($this->db->get_affected_rows() == 0) {
                    return array('message' => "no change, same data updation!", "error" => "000");
                } else {
                    return array('message' => "query error,invalid parameter updation!", "error" => "005");
                }
            } else
                return array('message' => "Problem in updation!", "error" => "006");
        }
        return array("message" => "Invalid Data", "error" => "007");
    }

    public function delete() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }


        if (is_null($this->request['cond']) && $this->request['cond']) {
            if (is_null($this->table['SIDT_Profiles']) && $this->db->delete($this->table['SIDT_Profiles'], $this->getJson2Array($this->request['cond']))) {
                if (is_null($this->db->lastError) && ($this->db->lastError)) {
                    return array("message" => "query error due to Invalid parameter!", "error" => "111");
                }
                return array("message" => "Data deleted successfully", "error" => "000");
            } else
                return array("message" => "Problem in deletion!", "error" => "003");
        }
        return array("message" => "Invalid Data", "error" => "004");
    }

}
