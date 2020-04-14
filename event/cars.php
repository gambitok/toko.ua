<?php

$linka=findLinks();
$mfa_link=$linka[1];
$mod_link=$linka[2];
$form=$prod->getHtmlForm("cars");

if ($mfa_link!="" && $mod_link!="") {
    $car_content = $prod->showCarsSelected($mfa_link, $mod_link);
} else {
    $car_content = $prod->showCarsSelect("", $mfa_link, $mod_link);
}

$seo_content = $automan->getSeoContent($mfa_link, $mod_link);

list($mfa_id, $model)=$automan->getAutoIdsLink($mfa_link, $mod_link);
list($mfa_text, $model_text)=$automan->getAutoDescrLink($mfa_link, $mod_link);
$translit=$prod->getCarManufTranslit($mfa_id, $model);
$title=$catalogue->replaceLang("{details_on_cap} $mfa_text $model_text $translit");
if ($mfa_text=="") $title="{spare_parts_catalog_cap}";
$form=str_replace("{cars_title}", $title, $form);
$form=str_replace("{cars_list}", $car_content, $form);
$form=str_replace("{seo_content}", $seo_content, $form);

$content=str_replace("{main_window}", $form, $content);