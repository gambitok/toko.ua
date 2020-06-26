<?php
$np = new \LisDev\Delivery\NovaPoshtaApi2(
    '656d2934ac1411fdb377a1d6de96fd92',
    'ru',
    FALSE,
    'curl'
);
//$arr = $np->getCities()['data'];
//
//$list="";
//
//foreach ($arr as $val) {
//    $name = iconv("UTF-8", "windows-1251", $val["Description"]);
//    $ref = $val["Ref"];
//    $city_id = $val["CityID"];
//    $list.="$city_id. $name ($ref)<br>";
//}
//
//print_r($list);

//$shop->setCityNovaPoshta();

//$_GET["city_ref"]=="" ? $result=$shop->getNovaPoshtaCities() : $result=$shop->getNovaPoshtaWarehouses($_GET["city_ref"]);
//$_GET["city_name"]=="" ?: $result=$shop->getNovaPoshtaCity($_GET["city_name"]);

//print_r($result);
//die();

$content=str_replace("{main_window}", $shop->getOrderForm(), $content);