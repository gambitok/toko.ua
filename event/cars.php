<?php

$linka = findLinks();
$mfa_link = $catalogue->getUrlString($linka[1]);
$mod_link = $catalogue->getUrlString($linka[2]);

list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
list($mfa_text, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
$translit = $automan->getCarManufTranslit($mfa_id, $model);

$title = ($mfa_text == "") ? "{spare_parts_catalog_cap}" : $catalogue->replaceLang("{details_on_cap} $mfa_text $model_text $translit");

$form = $catalogue->getHtmlForm("cars/form");
$form = str_replace("{cars_title}", $title, $form);
//$form = str_replace("{cars_list}", $showform->drawLoader(), $form);
$form = str_replace("{cars_list}", $prod->getCarsSearch($mfa_link, $mod_link), $form);
$form = str_replace("{mfa_link}", $mfa_link, $form);
$form = str_replace("{model_link}", $mod_link, $form);
$form = str_replace("{seo_content}", $automan->getSeoContent($mfa_link, $mod_link), $form);

$content = str_replace("{main_window}", $form, $content);

$content = str_replace("{meta_social_tag}", $automan->getCarsMetaTags($mfa_link, $mod_link, $title), $content);

//??
//$content = str_replace("{main_seo_text_cars}", $automan->getSeoCarsLinking($mfa_link, $mod_link), $content);
