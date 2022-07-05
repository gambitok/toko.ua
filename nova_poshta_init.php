<?php

define('RDD', __DIR__);
require_once (RDD . "/lib/DbSingleton.php");
require_once (RDD . "/lib/mysql_class.php");
require_once (RDD . "/lib/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php");

use LisDev\Delivery\NovaPoshtaApi2;

$np = new NovaPoshtaApi2('e52c020f392e0da179684b87cdbbbf05');

$db = DbSingleton::getTokoDb();

$list = "";
$arr = $np->getCities(0, '’мельницький');
var_dump($arr);

foreach ($arr as $val) {
    $name   = $val["Description"];
    $ref    = $val["Ref"];

    $city_id = 0;
    $city_name = $name;
    $city_ref = $ref;
    $area_name = "";
    $area_ref = "";

    $db->query("INSERT INTO `T2_CITY_NOVA_INIT` (`CITY_ID`, `CITY_NAME`, `CITY_REF`, `AREA_NAME`, `AREA_REF`) VALUES ($city_id, '$city_name', '$city_ref', '$area_name', '$area_ref');");
}

// CITY REF

// CITY NAME

// AREA REF

// AREA NAME