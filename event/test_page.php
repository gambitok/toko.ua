<?php

//$price = $catalog_exist->getArticlePriceStorage(361155);
//$price = $kours->getKoursPrice($price, 2);

$art_id = 361155;
$suppl_id = 6;
$storage_id = 4;

$cur = $catalog_exist->getCurrentExrate();
$price = $catalog_exist->getArticlePrice($art_id);
if ($suppl_id != 0) {
    $price = $catalog_exist->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
}
$price = $kours->getKoursPrice($price, $cur);
if ($cur == 1) {
    $price = $client->getClientPriceRounding($catalog_exist->getClient(), $price);
}

print($price);



