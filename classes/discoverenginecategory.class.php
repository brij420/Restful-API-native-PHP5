<?php

require_once 'classes/serversidevalidation.class.php';
#require_once '../config/db_cred.inc.php';
require_once 'classes/MySQL_connection_class.inc.php';
require_once 'classes/session.class.php';

class DiscoverEngineCategory extends Session {
	
	var $request;
    var $db;

    public function __construct($request) {
       	// parent::__construct();
        $this->request = $request;
        $this->db = new MySQL(DBNAME, USERNAME, PASSWORD, HOST);
    }

    public function get() {
		if (!isset($this->request['key'])) {
            return array("message" => "api key required!", "error" => "001");
        }
        if (isset($this->request['key']) && (!$this->isSession($this->request['key']))) {
            return array("message" => "Session expired!", "error" => "002");
        }
		
		$last_update = intval($this->request['updated_on']);

		$SIDT_TagTimeMap = $this->db->select('SIDT_TagTimeMap','', '', '', '', '', 'time_tag_id,tag_name,from_hour,to_hour,de_category,de_categoryid,de_category_disp,tag_day,UNIX_TIMESTAMP(`updated_on`) as updated_on','','', array('updated_on' => $last_update), '>');
		foreach ($SIDT_TagTimeMap as $key => $value){
			$days = str_split($value['tag_day']);
			foreach ($days as $dkey => $dvalue){
				$isOpened = intval($dvalue);
				if($isOpened == 1){
					$days[$dkey] = $dkey;
				}else{
					unset($days[$dkey]);
				}
			}
			$value['tag_day'] = array_values($days);
			$value['from_hour'] = intval($value['from_hour']);
			$value['to_hour'] = intval($value['to_hour']);
			$SIDT_TagTimeMap[$key] = $value;
		}
		
		$SIDT_DeMap = $this->db->select('SIDT_DeMap', '', '', '', '', '', 'de_one_cat_id,de_two_cat,de_two_cat_id,bg_color,factual_cat,UNIX_TIMESTAMP(`updated_on`) as updated_on', array('updated_on' => $last_update), '>');
		foreach($SIDT_DeMap as $key => $value){
			$factual_cat = is_null($value['factual_cat']) ? array() : explode(',', $value['factual_cat']);
			foreach ($factual_cat as $fkey => $fvalue){
				$factual_cat[$fkey] = intval($fvalue);
			}
			$value['factual_cat'] = array_values($factual_cat);
			$SIDT_DeMap[$key] = $value;
		}
		
		$SIDT_BizCategoryMapping = $this->db->select('SIDT_BizCategoryMapping', '', '', '', '', '', 'external_category_id,external_category_labels,sid_category,sid_category_label,catgory_image_dir,image_count,UNIX_TIMESTAMP(`updated_on`) as updated_on', array('updated_on' => $last_update), '>');
		foreach($SIDT_BizCategoryMapping as $key => $value){
			$ext_cat_labels = is_null($value['external_category_labels']) ? array() : array_map('trim', explode(',', $value['external_category_labels']));
			$value['external_category_labels'] = $ext_cat_labels;
			$SIDT_BizCategoryMapping[$key] = $value;
		}
		return array('SIDT_TagTimeMap' => $SIDT_TagTimeMap, 'SIDT_DeMap' => $SIDT_DeMap, 'SIDT_BizCategoryMapping' => $SIDT_BizCategoryMapping);
	}
}