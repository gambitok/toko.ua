<?php

global $catalogue, $content, $formObj;

$httpHost = findLinks();

$brand_link = $catalogue->getUrlString($httpHost[1]);

if ($brand_link === "") {
    $content = str_replace("{main_window}", "<div class='content'>" . $formObj->showBrandRange() . "</div>", $content);
}

$brand_id = $catalogue->getBrandNameLink($brand_link);

if ($brand_id > 0) {
    $brand_name = $catalogue->getBrandName($brand_id);

    $title = $catalogue->replaceLang("{site_brands_select}");
    $title = str_replace("{brand_text}", $brand_name, $title);

    $description = str_replace("{brand_text}", $brand_name, $catalogue->replaceLang("{site_brands_description_select}"));

    $content = str_replace(
        array("{main_window}", "{site_title}", "{site_description}"),
        array("<div class='content'>" . $formObj->showBrandSelect($brand_id) . "</div>", $title, $description),
    $content);
}

