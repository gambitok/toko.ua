<?php

// REDIRECT OLD TOKO LINKS (dep=23)
$w = $catalogue->getUrlString($_GET["w"]);

if ($w == "modelfind") {
    $art = $catalogue->getUrlString($_GET["art"]);
    header("Location: /search/$art/", TRUE, 301);
}

$content = str_replace("{main_window}", $catalogue->getHtmlForm("main_form"), $content);

// Поиск по автомобилю
//$content = str_replace("{catalogue_tab_search}", $prod->getCarsSearch(), $content);
$content = str_replace("{catalogue_tab_search}", $showform->drawLoader(), $content);

// Баннер
$content = str_replace("{catalogue_banner}", $showform->getCarsBanner(), $content);

// Каталог запчастей
$content = str_replace("{select_det_group}", $catalogue->getCatalogColList(), $content);
$content = str_replace("{select_auto_group}", $automan->getAutoMfaModelList(), $content);

// Контакты
$content = str_replace("{contacts_bottom}", $menu->showContactsBottom(), $content);

