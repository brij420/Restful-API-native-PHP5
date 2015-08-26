<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class BusinessImage extends Session {

    var $request;
    var $db;
    var $validation;
    var $table = array('SIDT_Businesses' => 'SIDT_Businesses', 'SIDT_SessionKey' => 'SIDT_SessionKey');

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

    function scanDirectories($rootDir, $allData = array()) {

        $invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd");

        $dirContent = scandir($rootDir);
        foreach ($dirContent as $key => $content) {

            $path = $rootDir . '/' . $content;
            if (!in_array($content, $invisibleFileNames)) {

                if (is_file($path) && is_readable($path)) {

                    $allData[] = $path;
                } elseif (is_dir($path) && is_readable($path)) {

                    $allData = $this->scanDirectories($path, $allData);
                }
            }
        }
        return $allData;
    }

    public function get() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }

        $this->request['filter'] = !empty($this->request['filter']) ? $this->getJson2Array($this->request['filter']) : NULL;
        $sid_category_id = is_null($this->request['filter']['sid_category_id']) && ($this->request['filter']['sid_category_id']) ? explode(',', $this->request['filter']['sid_category_id']) : NULL;
        $image_size = is_null($this->request['filter']['imagesize']) && ($this->request['filter']['imagesize']) ? preg_split('/[*|x|X]+/', $this->request['filter']['imagesize']) : NULL;
        $width = (is_null($image_size[0]) && ($image_size[0])) ? $image_size[0] : 0;
        $height = (is_null($image_size[1]) && ($image_size[1])) ? $image_size[1] : 0;
        $this->request['select'] = " category_labels,sid_category ";
        $this->request['conj'] = ((is_null($this->request['conj'])) && ($this->request['conj'])) ? $this->request['conj'] : '';
        $this->request['count'] = ((is_null($this->request['count'])) && ($this->request['count'])) ? $this->request['count'] : 0;
        //$dir = IMG_BASE_PATH;
        // $scanned_directory = $this->scanDirectories('./' . IMG_BASE_PATH);


        /*
          $count = 0;
          if (!empty($this->request['filter']) && is_null($this->table['SIDT_Businesses'])) {
          for ($i = 0; $i < count($sid_category_id); $i++) {
          $list_labels[] = $this->db->select($this->table['SIDT_Businesses'], array("sid_category" => $sid_category_id[$i]), '', '', false, $this->request['conj'], $this->request['select']);
          $count = is_null($this->db->records) ? $this->db->records : 0;
          }
          };
          $temp_array = array();
          for ($i = 0; $i < (count($list_labels) - 1); $i++) {
          $temp_array = array_merge($list_labels[$i], $list_labels[$i + 1]);
          }
          $list_labels = !empty($temp_array) ? $temp_array : $list_labels;
          $response_array = array();
          if ($count > 1) {
          for ($i = 0; $i < count($list_labels); $i++) {
          if (is_null($list_labels[$i]['sid_category']) && ($list_labels[$i]['sid_category'])) {
          $response_array[$i]['category_images'] = $this->get_images($scanned_directory, $width, $height, explode(',', $list_labels[$i]['category_labels']), $this->request['count']);
          $response_array[$i]['sid_category'] = $list_labels[$i]['sid_category'];
          }
          }
          } else {
          if (is_null($list_labels['sid_category']) && ($list_labels['sid_category'])) {
          $response_array['category_images'] = $this->get_images($scanned_directory, $width, $height, explode(',', $list_labels['category_labels']), $this->request['count']);
          $response_array['sid_category'] = $list_labels['sid_category'];
          }
          }
         */
        //$response_array[$i]['category_images'] = $this->get_images($scanned_directory, $width, $height, explode(',', $list_labels[$i]['category_labels']), $this->request['count']);
        global $category_list;
        $temp_category_list = array();
        if (is_array($category_list)) {
            for ($i = 0; $i < count($sid_category_id); $i++) {

                foreach ($category_list as $key => $value) {
                    if ($sid_category_id[$i] == $key) {
                        $response_array[$i]['sid_category'] = $sid_category_id[$i];
                        $path = ROOT_DIR . IMG_BASE_PATH . DIRECTORY_SEPERATOR . trim($category_list[$sid_category_id[$i]]) . '/';
                        if (is_dir($path)) {
                            $scanned_directory = null;
                            $scanned_directory = $this->scanDirectories($path);
                            $response_array[$i]['category_images'] = $this->get_images($scanned_directory, $width, $height, $category_list[$sid_category_id[$i]], $this->request['filter']['count']);
                        } else {
                            $response_array[$i]['category_images'] = "No Image";
                        }
                    }
                }
            }

            /* if (is_null($sid_category_id) && (count($sid_category_id) > 1)) {
              for ($i = 0; $i < count($sid_category_id); $i++) {
              $response_array[$i]['category_images'] = $this->get_images($scanned_directory, $width, $height, $temp_category_list, $this->request['filter']['count']);
              $response_array[$i]['sid_category'] = $sid_category_id[$i];
              }
              } else {
              $response_array['category_images'] = $this->get_images($scanned_directory, $width, $height, $temp_category_list, $this->request['filter']['count']);
              $response_array['sid_category'] = $sid_category_id['sid_category'];
              } */
        }

        if (!empty($response_array) && is_array($response_array)) {
            return array("ImageList" => (array)$response_array, 'count' => count($response_array), "message" => "List", "error" => "000");
        } elseif (empty($response_array)) {
            return array("ImageList" => "No match Found", "message" => "List!", "error" => "000");
        } else {
            return array("message" => "Invalid Data", "error" => "003");
        }
    }

    function get_images($dirlist = array(), $req_width = NULL, $req_height = NULL, $labels = array(), $random_num = 4) {

        $required_list = array();
        if (!empty($dirlist)) {
            foreach ($dirlist as $key => $value) {
                if (is_file($value) && is_readable($value)) {
                    list($width, $height) = getimagesize($value);
                    $image = explode('/', $value);
                    if ($width <= $req_width && $height <= $req_height && (preg_match('/' . $labels . '/', $value))) {
                        $required_list[] = $image[count($image) - 1];
                    }
                }
            }
        }
        $required_list_random = array_rand(array_unique($required_list), $random_num);
        foreach ($required_list_random as $key => $value) {
            $response[] = $required_list[$value];
        }
        return $response;
    }

}

/*function get_labels_1d($arr = array()) {
        $tem_result = NULL;
        $result = array();
        if (!empty($arr)) {
            foreach ($arr as $key => $value) {
                $tem_result = explode(',', $value);

                for ($i = 0, $j = count($result); $i < count($tem_result); $i++) {
                    ($j > 0) ? ($result[++$j] = $tem_result[$i]) : ($result[$j++] = $tem_result[$i]);
                }
            }
        }
        return array_unique($result);
    }*/

  /*
http://localhost/SID/ImageUpload?key=710579cf8d0ae41beb510bbd90f0c454&
action=Upload&Process=Profile&fields={"puser_id":"10"}
 
 *  */