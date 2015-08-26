<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class ImageUpload extends Session {

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

    public function upload() {
        if (!is_null($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (is_null($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }
        //$this->request['file_name'] = $_FILES['file_name']['name'];
        $this->request['filter'] = !empty($this->request['filter']) ? $this->getJson2Array($this->request['filter']) : NULL;
        //$sid_category_id = is_null($this->request['filter']['sid_category_id']) && ($this->request['filter']['sid_category_id']) ? explode(',', $this->request['filter']['sid_category_id']) : NULL;
        $user_id = is_null($this->request['filter']['user_id']) && ($this->request['filter']['user_id']) ? $this->request['filter']['user_id'] : NULL;
        $folder_name = is_null($this->request['process']) && ($this->request['process']) ? $this->request['process'] : NULL;
        $file_name = is_null($this->request['file_name']) && ($this->request['file_name']) ? $this->request['file_name'] : NULL;
        $image_size = is_null($this->request['filter']['imagesize']) && ($this->request['filter']['imagesize']) ? preg_split('/[*|x|X]+/', $this->request['filter']['imagesize']) : NULL;
        $width = (is_null($image_size[0]) && ($image_size[0])) ? $image_size[0] : 0;
        $height = (is_null($image_size[1]) && ($image_size[1])) ? $image_size[1] : 0;


        //global $category_list;
        if ($folder_name)
            $uploaddir = ROOT_DIR . IMG_UPLOAD_PATH . DIRECTORY_SEPARATOR . $folder_name . DIRECTORY_SEPARATOR;
        else {

            $uploaddir = ROOT_DIR . IMG_UPLOAD_PATH . DIRECTORY_SEPARATOR;
        }

        $tmpfilename = $user_id . "_" . $file_name . date("YmdHis") . ".txt";


        $uptmpfile = basename($_FILES['file_name']['name']);
        $uploadtmpfile = $uploaddir . $uptmpfile;

        if (move_uploaded_file($_FILES['file_name']['tmp_name'], $uploadtmpfile)) {
            $binaryData = file_get_contents($uploadtmpfile);
        } else {
            $binaryData = file_get_contents($_FILES['file_name']['tmp_name']);
        }


        $filename = $uploaddir . $tmpfilename;

        $rand = rand(0, 10000);
        $file_extension = substr($file_name, strrpos($file_name, '.') + 1);

        $newfilename = $uploaddir . md5($user_id . time() . $rand) . "." . $file_extension;



        if ($this->request['currentChunk'] == $this->request['totalChunks']) {

            $data = ($binaryData);

            $writefile_status = file_put_contents($filename, $data, FILE_APPEND);
            if ($writefile_status === FALSE) {
                return array("message" => "Failed to write image", "error" => "003");
            } else {
                $imageData = (file_get_contents($filename));

                if (file_put_contents($newfilename, $imageData)) {

                    // if (is_null($file_name)) {

                    $old = $newfilename;


                    $th = md5($user_id . time() . $rand) . "th";
                    $b_th = md5($user_id . time() . $rand) . "bth";
                    $bb_th = md5($user_id . time() . $rand) . "bbth";

                    $newname_th = $th;
                    $newname_b_th = $b_th;
                    $newname_bb_th = $bb_th;

                    $thumb1 = $uploaddir . "th" . DIRECTORY_SEPARATOR . $newname_th . "." . $file_extension;
                    $thumb2 = $uploaddir . "bth" . DIRECTORY_SEPARATOR . $newname_b_th . "." . $file_extension;
                    $thumb3 = $uploaddir . "bbth" . DIRECTORY_SEPARATOR . $newname_bb_th . "." . $file_extension;


                    $sizee = getimagesize($newfilename);
                    $srcwidth = $sizee[0];
                    $srcheight = $sizee[1];
                    switch ($sizee['mime']) {
                        case "image/jpeg" :
                            $srcImage = imagecreatefromjpeg($old);
                            break;
                        case "image/png":
                            $srcImage = imagecreatefrompng($old);
                            break;
                        case "image/gif":
                            $srcImage = imagecreatefromgif($old);
                            break;
                    }

                    if ($srcwidth > $srcheight) {
                        $destwidth1 = 65;
                        $rat = $destwidth1 / $srcwidth;
                        $destheight1 = (int) ($srcheight * $rat);
                        $destwidth2 = 150;
                        $rat2 = $destwidth2 / $srcwidth;
                        $destheight2 = (int) ($srcheight * $rat2);
                    } elseif ($srcwidth < $srcheight) {
                        $destheight1 = 65; //100;
                        $rat = $destheight1 / $srcheight;
                        $destwidth1 = 65;
                        $destheight2 = 150;
                        $rat = $destheight2 / $srcheight;
                        $destwidth2 = (int) ($srcwidth * $rat);
                    } elseif ($srcwidth == $srcheight) {
                        $destwidth1 = 65;
                        $destheight1 = 65;
                        $destwidth2 = 150;
                        $destheight2 = 150;
                    }

                    $sizee = getimagesize($old);
                    $new_width = $sizee[0];
                    $new_height = $sizee[1];

                    if ($new_width >= "200" || $new_height >= "180") {
                        $ratio = (float) ($new_height / $new_width);
                        if ($new_width >= "200") {
                            $new_width = "200";
                            $new_height = $new_width * $ratio;
                        }
                        if ($new_height >= "180" and $new_width >= "200") {
                            $new_height = "180";
                            $new_width = $new_width / $ratio;
                        }
                        if ($new_height >= "180") {
                            $new_height = "180";
                            $new_width = $new_width / $ratio;
                        }
                    }

                    $destImage1 = imageCreateTrueColor($destwidth1, $destheight1);
                    $destImage2 = imageCreateTrueColor($destwidth2, $destheight2);
                    $destImage3 = imageCreateTrueColor($new_width, $new_height);

                    imagecopyresampled($destImage1, $srcImage, 0, 0, 0, 0, $destwidth1, $destheight1, $srcwidth, $srcheight);
                    imagecopyresampled($destImage2, $srcImage, 0, 0, 0, 0, $destwidth2, $destheight2, $srcwidth, $srcheight);
                    imagecopyresampled($destImage3, $srcImage, 0, 0, 0, 0, $new_width, $new_height, $srcwidth, $srcheight);


                    if ($sizee['mime'] == "image/jpeg") {
                        imageJpeg($destImage1, $thumb1, 80);
                        imageJpeg($destImage2, $thumb2, 80);
                        imageJpeg($destImage3, $thumb3, 80);
                    } elseif ($sizee['mime'] == "image/png") {
                        imagepng($destImage1, $thumb1, 80);
                        imagepng($destImage2, $thumb2, 80);
                        imagepng($destImage3, $thumb3, 80);
                    } elseif ($sizee['mime'] == "image/gif") {
                        imagegif($destImage1, $thumb1, 80);
                        imagegif($destImage2, $thumb2, 80);
                        imagegif($destImage3, $thumb3, 80);
                    }


                    //ImageDestroy($srcImage);
                    imageDestroy($destImage1);
                    imageDestroy($destImage2);
                    imageDestroy($destImage3);
                    unlink($filename);

                    if (is_null($this->table['SIDT_Profiles']) && $this->db->update($this->table['SIDT_Profiles'], array("tiny_profile_pic" => $newname_th . "." . $file_extension, "small_profie_pic" => $newname_b_th . "." . $file_extension, "medium_profile_pic" => $newname_bb_th . "." . $file_extension, "large_profile_pic" => str_replace($uploaddir, '', $newfilename)), array("pemail_username" => $user_id))) {
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
                    } else
                        return array('message' => "Problem in updation!", "error" => "005");


                    return array("message" => "Successful to write image", "error" => "000");
                }
                //}
            }
        } else {

            $data = ($binaryData);

            $data = urldecode($binaryData);
            if (file_put_contents($filename, $data, FILE_APPEND) === FALSE) {
                return array("message" => "Succesful to write image data for part" . $this->request['currentChunk'], "error" => "006");
            } else {
                return array("message" => "Succesful to write image data for part" . $this->request['currentChunk'], "error" => "000");
            }
        }
        return array("message" => "Invalid Data!" . $this->request['currentChunk'], "error" => "007");
    }

    function thumbanail_for_image($Id = NULL, $folder = NULL, $newfilename = NULL, $size = NULL) {

        $file_extension = substr($newfilename, strrpos($newfilename, '.') + 1);
        $arr = explode('.', $newfilename);


        $thumb1 = LOCAL_FOLDER . $arr[0] . "_" . $Id . "." . $file_extension;
        $thumb2 = LOCAL_FOLDER . $arr[0] . "_" . $Id . "b" . "." . $file_extension;


        $old = LOCAL_FOLDER . $newfilename;

        $newfilename = LOCAL_FOLDER . $newfilename;

        $srcImage = "";

        $sizee = getimagesize($newfilename);

        switch ($sizee['mime']) {
            case "image/jpeg" :
                $srcImage = imagecreatefromjpeg($old);
                break;
            case "image/png":
                $srcImage = imagecreatefrompng($old);
                break;
            case "image/gif":
                $srcImage = imagecreatefromgif($old);
                break;
        }


        $srcwidth = $sizee[0];
        $srcheight = $sizee[1];


        if ($srcwidth > $srcheight || $srcwidth < $srcheight) {
            $destwidth1 = 65;
            $rat = $destwidth1 / $srcwidth;
            $destheight1 = (int) ($srcheight * $rat);
        } elseif ($srcwidth == $srcheight) {
            $destwidth1 = 65;
            $destheight1 = 65;
        }

        if ($srcwidth > $srcheight || $srcwidth < $srcheight) {
            $destwidth2 = 300;
            $rat = $destwidth2 / $srcwidth;
            $destheight2 = (int) ($srcheight * $rat);
        } elseif ($srcwidth == $srcheight) {
            $destwidth2 = 300;
            $destheight2 = 300;
        }

        $destImage1 = imagecreatetruecolor($destwidth1, $destheight1);
        $destImage2 = imagecreatetruecolor($destwidth2, $destheight2);

        imagecopyresampled($destImage1, $srcImage, 0, 0, 0, 0, $destwidth1, $destheight1, $srcwidth, $srcheight);
        imagecopyresampled($destImage2, $srcImage, 0, 0, 0, 0, $destwidth2, $destheight2, $srcwidth, $srcheight);

        if ($sizee['mime'] == "image/jpeg") {
            imagejpeg($destImage1, $thumb1, 80);
            imagejpeg($destImage2, $thumb2, 80);
        } elseif ($sizee['mime'] == "image/png") {
            imagepng($destImage1, $thumb1, 80);
            imagepng($destImage2, $thumb2, 80);
        } elseif ($sizee['mime'] == "image/gif") {
            imagegif($destImage1, $thumb1, 80);
            imagegif($destImage2, $thumb2, 80);
        }


        //ImageDestroy($srcImage);
        imagedestroy($destImage1);
        imagedestroy($destImage2);
        chmod($destImage1, 0777);
        chmod($destImage2, 0777);
        return $destImage1;
    }

}

//http://localhost/SID/ImageUpload?key=710579cf8d0ae41beb510bbd90f0c454&action=Upload&Process=Profile&fields={"puser_id":"10"}


    /* function get_labels_1d($arr = array()) {
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
      } */

    /*
      http://localhost/SID/ImageUpload?key=710579cf8d0ae41beb510bbd90f0c454&
      action=Upload&Process=Profile&fields={"puser_id":"10"}

     *  */    
