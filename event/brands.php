<?php

$linka = findLinks();
$brand_link = $catalogue->getUrlString($linka[1]);
$brand_id = $catalogue->getBrandNameLink($brand_link);

if ($brand_link == "") {
    $content = str_replace("{main_window}", "<div class='content'>" . $showform->showBrandRange() . "</div>", $content);
}
if ($brand_id > 0) {
    $content = str_replace("{main_window}", "<div class='content'>" . $showform->showBrandForm($brand_id) . "</div>", $content);
}

$content = str_replace("{site_title}", $title, $content);
$content = str_replace("{site_description}", $description, $content);
