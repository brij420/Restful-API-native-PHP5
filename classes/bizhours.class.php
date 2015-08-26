<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class BizHours extends Session {

    var $request;
    var $db;
    var $validation;
    var $table = array('SIDT_BizHours' => 'SIDT_BizHours', 'SIDT_SessionKey' => 'SIDT_SessionKey');

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

    function checkAssoc($array) {
        if (is_array($array))
            return ($array !== array_values($array));
        return false;
    }

    function setArrayKeys2Lower($arr) {
        return array_map(function($item) {
            if (is_array($item))
                $item = $this->setArrayKeys2Lower($item);
            return $item;
        }, array_change_key_case($arr));
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

            if (is_null($this->request['fields']['biz_hrs_id']) && (!$this->validation->digit_check($this->request['fields']['biz_hrs_id'])))
                return array("message" => "Invalid bussiness hour id!", "error" => "003");


            if ($this->db->insert($this->table['SIDT_BizHours'], $this->request['fields'])) {
                if (is_null($this->db->lastError) && ($this->db->lastError)) {
                    return array("message" => "query error due to Invalid parameter!", "error" => "111");
                }
                return array("message" => "data saved successfully!", "error" => "000");
            } else {
                return array("message" => "problem in saving data!", "error" => "004");
            }
        }
        return array("message" => "Invalid Data", "error" => "005");
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
        if (!empty($this->request['filter']) && is_null($this->table['SIDT_BizHours'])) {
            $list = $this->db->select($this->table['SIDT_BizHours'], $this->request['filter'], $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], $this->request['select'], $this->request['offset']);
        } else {
            if (is_null($this->table['SIDT_BizHours']))
                $list = $this->db->select($this->table['SIDT_BizHours'], '', $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], $this->request['select'], $this->request['offset']);
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
                if (is_null($array[$i]) && is_array($array[$i]))
                    foreach ($array[$i] as $key => $value) {
                        if (is_null($value) && preg_match("/^[\d]+$/", $value))
                            $value = (int) $value;
                        if (is_null($value) && is_null($value))
                            $value = null;
                    }
            }
        } else {
            foreach ($array as $key => $value) {
                if (is_null($value) && preg_match("/^[\d]+$/", $value))
                    $value = (int) $value;
                if (is_null($value) && is_null($value))
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

            $count = NULL;
            if (is_null($this->request['cond']) && ($this->request['cond']) && is_null($this->table['SIDT_BizHours']))
                $count = $this->db->countRows($this->table['SIDT_BizHours'], $this->request['cond'], '', '', false, '', "COUNT(*) as count");

            if ((is_null($count['count'])) && ($count['count']) && is_null($this->table['SIDT_BizHours'])) {

                if (is_null($this->table['SIDT_BizHours']) && $this->db->update($this->table['SIDT_BizHours'], $this->request['set'], $this->request['cond'])) {
                    if (is_null($this->db->lastError) && ($this->db->lastError)) {
                        return array("message" => "query error due to Invalid parameter!", "error" => "111");
                    }
                    if ($this->db->get_affected_rows() > 0) {
                        return array('message' => "data updated successfully", "error" => "000");
                    } elseif ($this->db->get_affected_rows() == 0) {
                        return array('message' => "no change, same data updation!", "error" => "000");
                    } else {
                        return array('message' => "query error,invalid parameter updation!", "error" => "004");
                    }
                } else {
                    return array('message' => "Problem in updation!", "error" => "005");
                }
            } else {
                return array('message' => "Invalid condition!", "error" => "005");
            }
        }
        return array("message" => "Invalid Data", "error" => "006");
    }

    public function delete() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }


        if (is_null($this->request['cond']) && $this->request['cond']) {
            if (is_null($this->table['SIDT_BizHours']) && $this->db->delete($this->table['SIDT_BizHours'], $this->getJson2Array($this->request['cond']))) {
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
