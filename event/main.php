<?php

global $catalogue, $content, $formObj, $autoObj, $menu;

// REDIRECT OLD TOKO LINKS (dep=23)
//$w = $catalogue->getUrlString($_GET["w"]);
//
//if ($w === "modelfind") {
//    $art = $catalogue->getUrlString($_GET["art"]);
//    header("Location: /search/$art/", TRUE, 301);
//}

$content = str_replace("{main_window}", $catalogue->getHtmlForm("main_form"), $content);

$content = str_replace("{history_arts}", $formObj->getHistoryArts(), $content);

// CARS FORM
$content = str_replace("{catalogue_tab_search}", $formObj->drawLoader(), $content);

// BANNER FORM
//$content = str_replace("{catalogue_banner}", $formObj->getCarsBanner(), $content);
$content = str_replace("{catalogue_banner}", $formObj->drawLoader(), $content);

// CATALOG GROUPS FORM
$content = str_replace("{select_det_group}", $catalogue->getCatalogColList(), $content);

// CARS FORM
$content = str_replace("{select_auto_group}", $autoObj->getAutoMfaModelList(), $content);

// POPULAR BRANDS FORM
$content = str_replace("{popular_brands}", $menu->showPopularBrands(), $content);

// CONTACTS FOOTER
$content = str_replace("{contacts_bottom}", $menu->showContactsBottom(), $content);

// LANGUAGE META INDEX
if (findLanguage() !== "" && findLanguage() !== "uk") {
    $content = str_replace("{meta_noindex}", $menu->getHtmlForm("seo/noindex"), $content);
}
