<?php

global $content, $db, $shop, $client, $exRate;

$where = $client->getClientWhere();
$db->query("UPDATE `basket` SET `status_checked`=1 WHERE $where;");

$form = $shop->getHtmlForm("basket/basket_content");
$form = str_replace("{basket_block}", $shop->showBasketForm($exRate->getCurrentExRate()), $form);
$form = str_replace("{banner_block}", "", $form);

$content = str_replace("{main_window}", $form, $content);

$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta name="yandex" content="noindex">
', $content);
