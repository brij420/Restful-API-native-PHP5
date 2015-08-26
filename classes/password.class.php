<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class Password extends Session {

    var $request;
    var $resource;
    var $db;
    var $validation;
    var $table = array('SIDT_Users' => "SIDT_Users");
    

    public function __construct($request, $resource) {
        // parent::__construct();
        $this->resource = $resource;
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

    /**/

    public function send_email($to = NULL, $from = NULL, $subject = NULL, $body = NULL, $link = NULL) {

        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "Reply-To: " . strip_tags($to) . "\r\n";
        $headers .= "CC: " . strip_tags($from) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


        $message = '<html><body>';

        $message .= '<div>Please <a href="' . $link . '">click here!</a> to reset the password!</div>';

        $message .= "</body></html>";

        return mail($to, $subject, $message, $headers);
    }

    public function forgot() {

        if (is_null($this->request['cond']) && $this->request['cond']) {
            $this->request['cond'] = $this->getJson2Array($this->request['cond']);
            if (is_null($this->request['cond']['email_username'])) {
                if (!$this->validation->email_check($this->request['cond']['email_username']))
                    return array("message" => "Invalid email address!", "error" => "001");
                $random_string = md5(md5($this->request['cond']['email_username']) . time());

                $link = BASE_URL . is_null($this->resource) && ($this->resource) ? $this->resource : NULL . $random_string;



                $count = NULL;
                if (is_null($this->request['cond']['email_username']) && ($this->request['cond']['email_username']) && is_null($this->table['SIDT_Users']))
                    $count = $this->db->countRows($this->table['SIDT_Users'], array('email_username' => $this->request['cond']['email_username']), '', '', false, '', "COUNT(*) as count");


                if ((is_null($count['count'])) && ($count['count']) && is_null($this->table['SIDT_Users']) && $this->db->update($this->table['SIDT_Users'], array("forgot_passwd_link" => $random_string), $this->request['cond'])) {
                    if (is_null($this->db->lastError) && ($this->db->lastError)) {
                        return array("message" => "query error due to Invalid parameter!", "error" => "111");
                    } else {
                        if ($this->send_email($this->request['cond']['email_username'], "abir@devintstudentideals.com", "reset password", '', $link)) {
                            return array("message" => "email sent successfully!", "error" => "000");
                        } else {
                            return array("message" => "problem in sending meail!", "error" => "011");
                        }
                    }
                    if ($this->db->get_affected_rows() > 0) {
                        return array('message' => "email sent successfully", "error" => "000");
                    } elseif ($this->db->get_affected_rows() == 0) {
                        return array('message' => "no change, same data updation!", "error" => "000");
                    } else {
                        return array('message' => "query error,invalid parameter updation!", "error" => "007");
                    }
                } else
                    return array('message' => "Problem in updation!", "error" => "008");
            }
        }
        return array("message" => "Invalid Data", "error" => "007");
    }

    public function reset() {
        if (is_null($this->request['reset_link']) && ($this->request['reset_link'])) {
            $this->request['fields'] = $this->getJson2Array($this->request['fields']);
            if (is_null($this->request['fields']['reset_password']) && (!$this->validation->password_length($this->request['fields']['reset_password'])))
                return array("message" => "Password should contain 6-30 character length!", "error" => "001");
            if (is_null($this->request['fields']['reset_password']) && (!$this->validation->password($this->request['fields']['reset_password'])))
                return array("message" => "Password should contain at-least one lowercase letter,one uppercase letter and at least one number!", "error" => "002");


            if (!empty($this->request['fields']['reset_password']) && ($this->request['fields']['reset_password']) && is_null($this->table['SIDT_Users']) && $this->db->update($this->table['SIDT_Users'], array("forgot_passwd_link" => $this->request['reset_link']), array("login_password" => $this->request['fields']['reset_password'] = is_null($this->request['fields']['reset_password']) ? md5($this->request['fields']['reset_password']) : NULL))) {
                if (is_null($this->db->lastError) && ($this->db->lastError)) {
                    return array("message" => "query error due to Invalid parameter!", "error" => "111");
                }
                if ($this->db->get_affected_rows() > 0) {
                    return array('message' => "password updated successfully", "error" => "000");
                } elseif ($this->db->get_affected_rows() == 0) {
                    return array('message' => "same password updation!", "error" => "000");
                } else {
                    return array('message' => "query error,invalid parameter updation!", "error" => "003");
                }
            } else {
                return array('message' => "Problem in updation!", "error" => "004");
            }
        } else {
            return array("message" => "Invalid Data", "error" => "005");
        }
    }

}

/*



{  
   "data":{  
      "List":[{
            "biz_id":"1",
            "external_id":"cb6928ab-0d7c-41f5-a63a-6f1ddece05f8",
            "biz_name":"Bada Seafood Family Restauant",
            "biz_address":"3377 Wilshire Blvd",
            "address_extended":"",
            "locality":"Los Angeles",
            "region":"CA",
            "post_code":"90010",
            "country":"US",
            "neighborhood":"Koreatown",
            "telephone":"(213) 389-8802",
            "fax":null,
            "website":"",
            "latitude":"34.06177500",
            "longitude":"-118.29751500",
            "chain_name":null,
            "post_town":null,
            "email":null,
            "chain_id":null,
            "admin_region":null,
            "sid_category":"0",
            "sid_category_label":"Food and Drink",
            "category_ids":"347",
            "category_labels":"Social,Food and Dining,Restaurants",
            "po_box":null,
            "biz_hours":null,
            "hours_display":"",
            "verified_phone":null,
            "verified_address":null,
            "update_date":"2014-09-24 12:26:59",
            "creation_date":"2014-09-24 12:26:47",
            "biz_status":null,
            "source":"Factual",
            "cuisine":null,
            "price":"0",
            "rating":null,
            "payment_cashonly":null,
            "reservations":null,
            "open_24hrs":null,
            "attire":null,
            "attire_required":null,
            "attire_prohibited":null,
            "parking":null,
            "parking_valet":null,
            "parking_garage":null,
            "parking_street":null,
            "parking_lot":null,
            "parking_validated":null,
            "parking_free":null,
            "smoking":null,
            "meal_breakfast":null,
            "meal_lunch":null,
            "meal_dinner":null,
            "meal_deliver":null,
            "meal_takeout":null,
            "meal_cater":null,
            "alcohol":null,
            "alcohol_bar":null,
            "alcohol_beer_wine":null,
            "alcohol_byob":null,
            "groups_goodfor":null,
            "accessible_wheelchair":null,
            "seating_outdoor":null,
            "wifi":null,
            "options_vegetarian":null,
            "options_vegan":null,
            "options_glutenfree":null,
            "options_organic":null,
            "options_healthy":null,
            "options_lowfat":null,
            "deals_redeemed":null,
            "active_deals":null,
            "sid_editorial":null,
            "distance":"11.70451480052251"
         },
         {  
            "biz_id":"2",
            "external_id":"5e9a9e8d-917a-4f9f-9864-3c7355ab220f",
            "biz_name":"Denny's",
            "biz_address":"3750 Wilshire Blvd",
            "address_extended":"",
            "locality":"LAS",
            "region":"CA",
            "post_code":"90010",
            "country":"US",
            "neighborhood":"Koreatown,Central LA,Wilshire Center \/ Koreatown,midwilshire,Wilshire Center,Sanford",
            "telephone":"(213) 384-1621",
            "fax":null,
            "website":"http:\/\/www.dennys.com\/",
            "latitude":"34.06165300",
            "longitude":"-118.30796500",
            "chain_name":null,
            "post_town":null,
            "email":null,
            "chain_id":null,
            "admin_region":null,
            "sid_category":"0",
            "sid_category_label":"Food and Drink",
            "category_ids":"347",
            "category_labels":"Social,Food and Dining,Restaurants",
            "po_box":null,
            "biz_hours":null,
            "hours_display":"Open Daily 12:00 AM-12:00 AM",
            "verified_phone":null,
            "verified_address":null,
            "update_date":null,
            "creation_date":"2014-09-24 12:26:47",
            "biz_status":null,
            "source":"Factual",
            "cuisine":null,
            "price":"0",
            "rating":null,
            "payment_cashonly":null,
            "reservations":null,
            "open_24hrs":null,
            "attire":null,
            "attire_required":null,
            "attire_prohibited":null,
            "parking":null,
            "parking_valet":null,
            "parking_garage":null,
            "parking_street":null,
            "parking_lot":null,
            "parking_validated":null,
            "parking_free":null,
            "smoking":null,
            "meal_breakfast":null,
            "meal_lunch":null,
            "meal_dinner":null,
            "meal_deliver":null,
            "meal_takeout":null,
            "meal_cater":null,
            "alcohol":null,
            "alcohol_bar":null,
            "alcohol_beer_wine":null,
            "alcohol_byob":null,
            "groups_goodfor":null,
            "accessible_wheelchair":null,
            "seating_outdoor":null,
            "wifi":null,
            "options_vegetarian":null,
            "options_vegan":null,
            "options_glutenfree":null,
            "options_organic":null,
            "options_healthy":null,
            "options_lowfat":null,
            "deals_redeemed":null,
            "active_deals":null,
            "sid_editorial":null,
            "distance":"12.23458252026044"
         }],
         "count":2
      },
      "count":2,
      "message":"List",
      "error":"000"
   
} *  */
