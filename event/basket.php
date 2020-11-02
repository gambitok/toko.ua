<?php

$where = $client->getClientWhere();
$db->query("UPDATE `basket` SET `status_checked`=1 WHERE $where;");

$form = $shop->getHtmlForm("basket/basket_content");
$form = str_replace("{basket_block}", $shop->showBasketForm($kours->getCurrentKours()), $form);
$form = str_replace("{banner_block}", "", $form); //$menu->showBannerBottom()

$content = str_replace("{main_window}", $form, $content);
