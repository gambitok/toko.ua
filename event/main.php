<?php

$content=str_replace("{main_window}", $catalogue->getHtmlForm("main_window"), $content);
// Поиск по автомобилю
$content=str_replace("{catalogue_tab_search}", $prod->getCarManufList("cars/"), $content);
//$content=str_replace("{cat_tab_title}", $automan->getHtmlForm("cat_tab_title"), $content);
// Профильные каталоги
$content=str_replace("{select_group}", $catalogue->showCatalogueTemplates(), $content);
// Каталог запчастей
$content=str_replace("{select_det_group}", $prod->getCarDetailsFull(), $content);
//$content=str_replace("{select_auto_group}", $automan->getAutoMfaModList(), $content);
$content=str_replace("{select_auto_group}", $automan->getAutoMfaModelList(), $content);
// Рекомендации
$info_block=$catalogue->getHtmlForm("menu/info_block"); $info_block = iconv("UTF-8", "windows-1251", $info_block);
$content=str_replace("{select_recommendations}", $info_block, $content);
// Bottom
//$content=str_replace("{banner_bottom}", $menu->showBannerBottom(), $content);
$content=str_replace("{contacts_bottom}", $menu->showContactsBottom(), $content);
$content=str_replace("{main_seo_text}", "", $content);







