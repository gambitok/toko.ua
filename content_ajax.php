<?php

set_time_limit(0);

define('RDD', __DIR__);

require_once (RDD . "/vendor/autoload.php");                  // init classes
require_once (RDD . "/lib/access.php");                       // get access site
require_once (RDD . "/js/JsHttpRequest/JsHttpRequest.php");   // ajax requests
require_once (RDD . "/lib/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php");
require_once (RDD . "/lib/UkrPoshtaClass.php");

$up = new UkrPoshtaClass("a979e2d9-d044-3f41-8b8c-099c5879ae32");

if (isset($_POST['foo'])) {

    $list = $up->getCitiesList($_POST['foo']);

    echo json_encode(array('success' => 1, 'text' => $list));
} else {
    echo json_encode(array('success' => 0));
}