<?php


$price = $catalogue->getArticlePrice(100002193);
$price = $kours->getKoursPrice($price, 1);

var_dump($price);

//$group_id = findLinks()[1];
//
//if ($group_id != "") {
//    $result = $catalog_exist->initPartsTable($group_id);
//
//}