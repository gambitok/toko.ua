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

//$data = $up->getCitiesListAll();
//foreach ($data as $value)
//{
//    $city_id    = $value['CITY_ID'];
//    $city_name  = $value['CITY_UA'];
//    $region_id  = $value['REGION_ID'];
//
//    $dbt->query("INSERT INTO `UP_CITIES` (`ID`, `REGION_ID`, `NAME`) VALUES ($city_id, $region_id, \"$city_name\");");
//    print("$city_id - $city_name");
//}

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

//$r = $dbt->query("SELECT `ID` FROM `UP_CITIES` WHERE `REGION_ID` = 2;");
//$n = $dbt->num_rows($r);
//for($i = 1; $i <= $n; $i++) {
//    $city_id = $dbt->result($r, $i - 1, "ID");
//    $data = $up->getDistrictsList($city_id);
//    $up->add_table($data, "UP_DISTRICTS", $city_id);
//    print "$city_id added\n";
//}

//$up->write_file($regions_list, "up_regions.csv");
//print("done with regions : " . count($regions_list) . "added \n");

//$count_cities = 0;
//$count_districts = 0;

//foreach(array_keys($regions_list) as $key) {
//    $cities_list = $up->getCitiesList($key);
//    $up->write_file($cities_list, "up_cities.csv");
//    $count_cities += count($cities_list);

//    foreach(array_keys($cities_list) as $k) {
//        $districts_list = $up->getDistrictsList($k);
//        $up->write_file($districts_list, "up_districts.csv");
//        $count_districts += count($districts_list);
//    }
//}

//print("done with cities : " . $count_cities . "added \n");
//print("done with districts : " . $count_districts . "added \n");