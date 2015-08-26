<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define('ROOT_DIR', '/home/brijesh/Desktop/php_projects/SID_test/', FALSE);
define('BASE_URL', 'localhost/SID_test/', FALSE);
define('DIRECTORY_SEPERATOR', '/', FALSE);
define('IMG_BASE_PATH', 'images', FALSE);
define('IMG_UPLOAD_PATH', '/home/brijesh/Desktop/php_projects/SID_test/', FALSE);
define('IMG_UPLOAD_DIR', 'upload', FALSE);
define('SPP_DIR_PATH', '/home/brijesh/Desktop/php_projects/SID_test/upload/spp', FALSE);
define('TPP_DIR_PATH', '/home/brijesh/Desktop/php_projects/SID_test/upload/tpp', FALSE);
define('MPP_DIR_PATH', '/home/brijesh/Desktop/php_projects/SID_test/upload/mpp', FALSE);
define('LPP_DIR_PATH', '/home/brijesh/Desktop/php_projects/SID_test/upload/lpp', FALSE);
define('SPP_URL', 'localhost/SID_test/upload/spp/', FALSE);
define('TPP_URL', 'localhost/SID_test/upload/tpp/', FALSE);
define('MPP_URL', 'localhost/SID_test/upload/mpp/', FALSE);
define('LPP_URL', 'localhost/SID_test/upload/lpp/', FALSE);


setlocale(LC_ALL, "hu_HU.UTF8");
global $category_list;
global $image_format;

$category_list = array('0' => "Social", '1' => "Food and Dining", '2' => "Restaurants");

$image_format = array("600 × 900", "750 × 1050", "974 × 1417", "1050 × 1500", "1200 × 1800",
    "1350 × 1800", "1500 × 2100", "1800 × 2400", "2400 × 3000", "2400 × 3600", "3000 × 3600",
    "3000 × 4500", "3300 × 4200", "3300 × 5100", "3600 × 4500", "3600 × 5400");
