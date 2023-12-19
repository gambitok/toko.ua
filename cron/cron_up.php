<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', false);
date_default_timezone_set("Europe/Kiev");
header('Content-Type: text/html; charset=windows-1251');
ini_set('memory_limit', '2048M');

require_once (RDD."/../vendor/autoload.php");
$dbt = DbSingleton::getTokoDb();
$dbc = DbSingleton::getTokoCacheDb();
$catalog_exist = new CatalogExistClass();

require_once RDD . "/../lib/UkrPoshtaClass.php";
$up = new UkrPoshtaClass("a979e2d9-d044-3f41-8b8c-099c5879ae32");

$data = $up->getDistrictsListAll();
foreach ($data as $value)
{
    $disctrict_id   = $value['DISTRICT_ID'];
    $disctrict_name = $value['DISTRICT_NAME'];
    $city_id        = $value['CITY_ID'];

    $dbt->query("INSERT INTO `UP_DISTRICTS` (`DISTRICT_ID`, `CITY_ID`, `NAME`) VALUES ($disctrict_id, $city_id, \"$disctrict_name\");");
    $disctrict_name = iconv("windows-1251", "UTF-8", $disctrict_name);
    print("$disctrict_id - $disctrict_name \n");
}