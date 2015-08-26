<?php
require_once 'include/include_files.php';


if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $resource = new Resource($_REQUEST['action'], $_SERVER['HTTP_ORIGIN']);
    echo $resource->resourceResponse();
} catch (Exception $e) {
    echo json_encode(Array('error' => $e->getMessage()));
}
