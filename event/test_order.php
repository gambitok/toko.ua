<?php
//$np = new \LisDev\Delivery\NovaPoshtaApi2(
//    '656d2934ac1411fdb377a1d6de96fd92',
//    'ru',
//    FALSE,
//    'curl'
//);
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

$order_id = $_GET["order_id"];
$user_id = $_GET["user_id"];
$user_status = $_GET["user_status"];

if ($order_id=="") {
    $content = $shop->getHtmlForm("orders/template");
    $content = str_replace("{main_window}", $shop->getOrderForm(), $content);
} else {
    $content = str_replace("{main_window}", $shop->getOrderContentForm($order_id, $user_id, $user_status), $content);
}
