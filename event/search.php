<?php

$linka = findLinks();

$brand_link         = $catalogue->getUrlString($linka[2]);
$article_nr_search  = $catalogue->getUrlString($linka[1]);
$article_nr_search  = rawurldecode($article_nr_search);
$article_nr_search  = iconv("UTF-8", "windows-1251", $article_nr_search);

if ($article_nr_search == "") {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/search_unknown"), $content);
} else {
    $content = str_replace("{main_window}", "{search}", $content);
    if ($brand_link == "") {
        $content = str_replace("{search}", $catalogue->getSearchList($article_nr_search), $content);
    } else {
        $content = str_replace("{search}", $catalogue->getCatalogList($catalogue->getFormatAticle($article_nr_search), $catalogue->getCatalogueBrandID($brand_link)), $content);
    }
}

$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">
    <meta name="yandex" content="noindex, nofollow">
', $content);