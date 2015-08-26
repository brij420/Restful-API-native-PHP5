<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class BusinessDeals extends Business {

    var $request;
    var $db;
    var $validation;
    var $table = array('SIDT_BizHours' => 'SIDT_BizHours', 'SIDT_Deals' => 'SIDT_Deals', 'SIDT_Businesses' => 'SIDT_Businesses', 'SIDT_SessionKey' => 'SIDT_SessionKey');

    public function __construct($request) {
        parent::__construct($request);

        //$this->request = $request;
        $this->db = new MySQL(DBNAME, USERNAME, PASSWORD, HOST);
        // $this->validation = new Validation();
    }

    public function getJsonResponse() {
        
    }

    function checkAssoc($array) {
        if (is_array($array))
            return ($array !== array_values($array));
        return false;
    }

    public function get() {
        $list = parent::get();
        if (is_null($list['error']) && ($list['error'] == '000')) {

            $deallist = null;
            $weekday = null;
            $weekdays_name = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
            $weekday = (is_null($this->request['timeofday']) && ($this->request['timeofday'])) ? array_search(date("l", strtotime($this->request['timeofday'])), $weekdays_name) : '';
            $this->request['timeofday'] = (is_null($this->request['timeofday']) && ($this->request['timeofday'])) ? strtotime($this->request['timeofday']) : '';

            $bussinesslist = (is_null($list['List']) && ($list['List'])) ? $list['List'] : '';
            $bussinesslist['count'] = (is_null($list['count']) && ($list['count'])) ? $list['count'] : 0;

            if (is_null($bussinesslist['count']) && ($bussinesslist['count'] > 1)) {
                for ($i = 0; $i < count($bussinesslist); $i++) {

                    if (is_null($bussinesslist[$i]['biz_id']) && ($bussinesslist[$i]['biz_id']) && is_null($this->table['SIDT_Deals'])) {
                        $deallist = $this->db->select($this->table['SIDT_Deals'], array('bizd_id' => $bussinesslist[$i]['biz_id'], "deals_status" => "1"), '', '', false, 'AND', "*");
                        if (is_null($this->table['SIDT_BizHours'])) {
                            $buss_hours = $this->db->countRows($this->table['SIDT_BizHours'], array('biz_hrs_id' => $bussinesslist[$i]['biz_id'], 'biz_day' => $weekday, 'UNIX_TIMESTAMP(`biz_open`)' => $this->request['timeofday'] . '>', 'UNIX_TIMESTAMP(`biz_close`)' => $this->request['timeofday'] . '<'), '', '', false, 'AND', 'COUNT(*) as count');
                        }
                        $bussinesslist[$i]['openStatus'] = is_null($buss_hours['count']) && ($buss_hours['count']) ? 1 : 0;
                        if (is_array($deallist)) {
                            $bussinesslist[$i]['dealcount'] = (!$this->checkAssoc($deallist)) ? count($deallist) : ($this->checkAssoc($deallist) ? $this->db->records : 0);
                            $bussinesslist[$i]['dealList'] = is_null($deallist) && ($deallist) ? ((!$this->checkAssoc($deallist)) ? $this->setValue2null($deallist, $bussinesslist['dealcount']) : array("0" => $this->setValue2null($deallist, $bussinesslist['dealcount']))) : array();
                        } /* elseif (is_array($deallist) && (!$this->checkAssoc($deallist))) {
                          $bussinesslist[$i]['dealcount'] = (!empty($deallist)) ? $this->db->records : 0;
                          $bussinesslist[$i]['dealList'] = is_null($deallist) && ($deallist) ? $deallist : null;
                          } */ else {
                            $bussinesslist[$i]['dealcount'] = 0;
                        }
                    }
                }
            } elseif (is_null($bussinesslist['count']) && ($bussinesslist['count'] == 1)) {

                if (is_null($bussinesslist['biz_id']) && ($bussinesslist['biz_id']) && is_null($this->table['SIDT_Deals'])) {
                    $deallist = $this->db->select($this->table['SIDT_Deals'], array('bizd_id' => $bussinesslist['biz_id'], "deals_status" => "1"), '', '', false, 'AND', '*');
                    if (is_null($this->table['SIDT_BizHours'])) {
                        $buss_hours = $this->db->countRows($this->table['SIDT_BizHours'], array('biz_hrs_id' => $bussinesslist['biz_id'], 'biz_day' => $weekday, 'UNIX_TIMESTAMP(`biz_open`)' => $this->request['timeofday'] . '>', 'UNIX_TIMESTAMP(`biz_close`)' => $this->request['timeofday'] . '<'), '', '', false, 'AND', 'COUNT(*) as count');
                    }
                    $bussinesslist['openStatus'] = is_null($buss_hours['count']) && ($buss_hours['count']) ? 1 : 0;
                    if (is_array($deallist)) {
                        $bussinesslist['dealcount'] = (!$this->checkAssoc($deallist)) ? count($deallist) : ($this->checkAssoc($deallist) ? $this->db->records : 0);
                        $bussinesslist['dealList'] = is_null($deallist) && ($deallist) ? ((!$this->checkAssoc($deallist)) ? $this->setValue2null($deallist, $bussinesslist['dealcount']) : array("0" => $this->setValue2null($deallist, $bussinesslist['dealcount']))) : null;
                    } /* elseif (is_array($deallist) && (!$this->checkAssoc($deallist))) {
                      $bussinesslist['dealcount'] = (!empty($deallist)) ? $this->db->records : 0;
                      $bussinesslist['dealList'] = is_null($deallist) && ($deallist) ? $deallist : null;
                      } */ else {
                        $bussinesslist['dealcount'] = 0;
                    }
                }
            } else {
                return array("List" => null, "message" => "No match Found!", "error" => "000");
            }

            if (is_array($bussinesslist)) {
                $count = (!$this->checkAssoc($bussinesslist)) ? count($bussinesslist) : ($this->checkAssoc($bussinesslist) ? $bussinesslist['count'] : 0);
            }
            if (is_null($bussinesslist['count']) && ($bussinesslist['count']))
                unset($bussinesslist['count']);

            if (!empty($bussinesslist) && is_array($bussinesslist)) {

                //Business Image
                /* foreach($bussinesslist as $key => $value){

                  if (is_null($value["image_url"])
                  continue;

                  $contentFound = false;
                  $bizKeywords = implode(", ", explode(' ', $value['biz_name']));

                  $SID_TaxonmyCollection = $this->db->select('SIDT_SIDTaxonomies', array('sid_term' => 'in ('.$bizKeywords.')'), '', '', '', '', 'sid_term_id');

                  foreach($SID_TaxonmyCollection as $key => $value){

                  }

                  } */

                return array("List" => (!$this->checkAssoc($bussinesslist)) ? (array) $bussinesslist : array("0" => $bussinesslist), 'count' => $count, "message" => "List", "error" => "000");
            } elseif (is_null($count) && (!$count)) {
                return array("List" => (is_null($bussinesslist) && is_array($bussinesslist)) ? $bussinesslist : null, 'count' => $count, "message" => "No match Found!", "error" => "000");
            } else {
                return array("message" => "Invalid Data", "error" => "003");
            }
        } else {
            return $list;
        }

        return array("message" => "Invalid Data", "error" => "003");
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

}
