<?php

$linka = findLinks();
$mfa_link = $linka[1];
$mod_link = $linka[2];

list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
list($mfa_text, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
$translit = $prod->getCarManufTranslit($mfa_id, $model);

$form = $prod->getHtmlForm("cars/form");

$mfa_text=="" ? $title = "{spare_parts_catalog_cap}" : $title = $catalogue->replaceLang("{details_on_cap} $mfa_text $model_text $translit");

$form = str_replace("{cars_list}", $prod->getCarsSearch("", $mfa_link, $mod_link), $form);
$form = str_replace("{seo_content}", $automan->getSeoContent($title, $mfa_link, $mod_link), $form);

if($mfa_text!="") $form = str_replace("{cars_listing}", $search->getSeoCarsLinking($mfa_link, $mod_link), $form);
$form = str_replace("{cars_listing}", "", $form);

$content = str_replace("{main_window}", $form, $content);