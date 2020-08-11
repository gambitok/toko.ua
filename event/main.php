<?php

// REDIRECT OLD TOKO LINKS (dep=23)
$w = $_GET["w"];
if ($w=="modelfind") {
    $art = $catalogue->getUrlString($_GET["art"]);
    header("Location: /search/$art/", TRUE, 301);
}

$content = str_replace("{main_window}", $catalogue->getHtmlForm("main_form"), $content);

// Поиск по автомобилю
$content = str_replace("{catalogue_tab_search}", $prod->getCarsSearch(), $content);

// Баннер
$content = str_replace("{catalogue_banner}", $showform->getCarsBanner(), $content);

// Профильные каталоги
$content = str_replace("{select_group}", $catalogue->showCatalogueTemplates(), $content);

// Каталог запчастей
$content = str_replace("{select_det_group}", $prod->getCarDetailsFull(), $content);
$content = str_replace("{select_auto_group}", $automan->getAutoMfaModelList(), $content);

// Рекомендации
$info_block = $catalogue->getHtmlForm("menu/info_block");
$info_block = iconv("UTF-8", "windows-1251", $info_block);
$content = str_replace("{select_recommendations}", $info_block, $content);

// Контакты
$content = str_replace("{contacts_bottom}", $menu->showContactsBottom(), $content);
$content = str_replace("{main_seo_text}", "", $content);
