<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class Schools extends Session {

    var $request;
    var $db;
    var $validation;
    var $table = array('SIDT_Schools' => 'SIDT_Schools', 'SIDT_SessionKey' => 'SIDT_SessionKey');

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


            if ($this->db->insert($this->table['SIDT_Schools'], $this->request['fields'])) {
                if (is_null($this->db->lastError) && ($this->db->lastError)) {
                    return array("message" => "query error due to Invalid parameter!", "error" => "111");
                }
                return array("message" => "data saved successfully!", "error" => "000");
            } else {
                return array("message" => "problem in saving data!", "error" => "003");
            }
        }
        return array("message" => "Invalid Data", "error" => "004");
    }

    public function get() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }
        //if (is_null($this->request['select'])) {
        $this->request['geo'] = !empty($this->request['geo']) ? $this->getJson2Array($this->request['geo']) : NULL;
        $this->request['filter'] = !empty($this->request['filter']) ? $this->getJson2Array($this->request['filter']) : NULL;
        $this->request['select'] = (is_null($this->request['select']) && ($this->request['select'])) ? $this->request['select'] : '*';
        $this->request['conj'] = ((is_null($this->request['conj'])) && ($this->request['conj'])) ? $this->request['conj'] : 'AND';
        $this->request['limit'] = (is_null($this->request['limit']) && ($this->request['limit'])) ? $this->request['limit'] : '';
        $this->request['offset'] = (is_null($this->request['offset']) && ($this->request['offset'])) ? $this->request['offset'] : '';
        $this->request['sortby'] = (is_null($this->request['sortby']) && ($this->request['sortby'])) ? $this->request['sortby'] : '';
        $list = null;
        if (((!empty($this->request['filter'])) || (!empty($this->request['geo']))) && is_null($this->table['SIDT_Schools'])) {
            if (!empty($this->request['geo'])) {
                $this->request['geo']['circle']['meters'] = (is_null($this->request['geo']['circle']['meters']) && ($this->request['geo']['circle']['meters'])) ? $this->request['geo']['circle']['meters'] : 0;
                $this->request['geo']['circle']['center'] = (is_null($this->request['geo']['circle']['center']) && ($this->request['geo']['circle']['center'])) ? explode(',', $this->request['geo']['circle']['center']) : 0;
                $latitude = (is_null($this->request['geo']['circle']['center'][0]) && ($this->request['geo']['circle']['center'][0])) ? $this->request['geo']['circle']['center'][0] : 0;
                $longitude = (is_null($this->request['geo']['circle']['center'][1]) && ($this->request['geo']['circle']['center'][1])) ? $this->request['geo']['circle']['center'][1] : 0;
                $select = $this->request['select'];
                //$query = "SELECT *, ( 3956 *2 * ASIN( SQRT( POWER( SIN( ( $latitude - abs( latitude ) ) * pi( ) /180 /2 ) , 2 ) +  COS($latitude * pi( ) /180 ) * COS( abs( latitude ) * pi( ) /180 ) * POWER( SIN( ( abs($longitude) - abs( longitude ) ) * pi( ) /180 /2 ) , 2 ) ) ) ) AS distance FROM " . $this->table['SIDT_Businesses'] . " HAVING distance < " . $this->request['geo']['circle']['meters'] . " ";
                $list = $this->db->select($this->table['SIDT_Schools'], $this->request['filter'], $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], "$select, ( 6371*1000 *2 * ASIN( SQRT( POWER( SIN( ( $latitude - abs( latitude ) ) * pi( ) /180 /2 ) , 2 ) +  COS($latitude * pi( ) /180 ) * COS( abs( latitude ) * pi( ) /180 ) * POWER( SIN( ( abs($longitude) - abs( longitude ) ) * pi( ) /180 /2 ) , 2 ) ) ) ) AS distance ", $this->request['offset'], '', array('distance' => $this->request['geo']['circle']['meters']), '<');
                // return array("List" => $this->db->executeSQL($query), "message" => "List", "error" => "000");
            } else {
                $list = $this->db->select($this->table['SIDT_Schools'], $this->request['filter'], $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], $this->request['select'], $this->request['offset']);
            }
        } else {
            if (is_null($this->table['SIDT_Schools']))
                $list = $this->db->select($this->table['SIDT_Schools'], '', $this->request['sortby'], $this->request['limit'], false, $this->request['conj'], $this->request['select'], $this->request['offset']);
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
            if (is_null($this->request['cond']) && ($this->request['cond']) && is_null($this->table['SIDT_Schools']))
                $count = $this->db->countRows($this->table['SIDT_Schools'], $this->request['cond'], '', '', false, '', "COUNT(*) as count");

            if ((is_null($count['count'])) && ($count['count']) && is_null($this->table['SIDT_Schools'])) {



                if (is_null($this->table['SIDT_Schools']) && $this->db->update($this->table['SIDT_Schools'], $this->request['set'], $this->request['cond'])) {
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
            if (is_null($this->table['SIDT_Schools']) && $this->db->delete($this->table['SIDT_Schools'], $this->getJson2Array($this->request['cond']))) {
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
