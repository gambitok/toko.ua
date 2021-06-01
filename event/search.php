<?php

$linka = findLinks();
$article_nr_search = $catalogue->getUrlString($linka[1]);
$brand_link = $catalogue->getUrlString($linka[2]);

if ($article_nr_search == "") {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/search_unknown"), $content);
} else {
    $content = str_replace("{main_window}", "{search}", $content);
    if ($brand_link == "") {
        $content = str_replace("{search}", $catalogue->getSearchList($article_nr_search), $content);
    } else {
        $brand_id = $catalogue->getCatalogueBrandID($brand_link);
        $article_nr_search = $catalogue->getFormatAticle($article_nr_search);
        $content = str_replace("{search}", $catalogue->getCatalogList($article_nr_search, $brand_id), $content);
    }
}

$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="yandex" content="noindex, nofollow">
', $content);