<?php

$linka = findLinks();
$article_nr_search = $linka[1];
$brand_link = $linka[2];

if ($article_nr_search == "") {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/search_unknown"), $content);
} else {
    $content = str_replace("{main_window}", "{search}", $content);
    if ($brand_link == "") {
        $content = str_replace("{search}", $catalogue->searchNumber($article_nr_search), $content);
    } else {
        $brand_id = $catalogue->getCatalogueBrandID($brand_link);
        $content = str_replace("{search}", $catalogue->showCatalogueList($article_nr_search, $brand_id), $content);
    }
}

