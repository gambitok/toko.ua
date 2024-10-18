<?php

global $content, $db, $shop, $client, $exRate;

$where = $client->getClientWhere();
$db->query("UPDATE `basket` SET `status_checked` = 1 WHERE $where;");

$form = str_replace(
    array("{basket_block}", "{banner_block}"),
    array($shop->showBasketForm($exRate->getCurrentExRate()), ""),
$shop->getHtmlForm("basket/basket_content"));

$content = str_replace(
    array("{main_window}", "{meta_noindex}"),
    array($form, $shop->getHtmlForm("seo/noindex")),
$content);
