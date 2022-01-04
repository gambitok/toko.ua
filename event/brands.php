<?php

$linka = findLinks();

$brand_link = $catalogue->getUrlString($linka[1]);
$brand_id   = $catalogue->getBrandNameLink($brand_link);

if ($brand_link == "") {
    $content = str_replace("{main_window}", "<div class='content'>" . $showform->showBrandRange() . "</div>", $content);
}

if ($brand_id > 0) {
    $brand_name = $catalogue->getBrandName($brand_id);

    $title = $catalogue->replaceLang("{site_brands_select}");
    $title = str_replace("{brand_text}", $brand_name, $title);

    $description = $catalogue->replaceLang("{site_brands_description_select}");
    $description = str_replace("{brand_text}", $brand_name, $description);

    $content = str_replace("{main_window}", "<div class='content'>" . $showform->showBrandSelect($brand_id) . "</div>", $content);
    $content = str_replace("{site_title}", $title, $content);
    $content = str_replace("{site_description}", $description, $content);
}

