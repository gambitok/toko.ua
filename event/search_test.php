<?php

$start = microtime(true);

$content = "<div class='row'><div class='col-12'><h1 class='text-center'>TESTOVA STORINKA</h1></div></div>" . $content;

$linka = findLinks();
$article_nr_search = $catalogue_test->getUrlString($linka[1]);
$brand_link = $catalogue_test->getUrlString($linka[2]);

if ($article_nr_search == "") {
    $content = str_replace("{main_window}", $catalogue_test->getHtmlForm("error/search_unknown"), $content);
} else {
    $content = str_replace("{main_window}", "{search}", $content);
    if ($brand_link == "") {
        $content = str_replace("{search}", $catalogue_test->getSearchList($article_nr_search), $content);
    } else {
        $brand_id = $catalogue_test->getCatalogueBrandID($brand_link);
        $article_nr_search = $catalogue_test->getFormatAticle($article_nr_search);
        $content = str_replace("{search}", $catalogue_test->getCatalogList($article_nr_search, $brand_id), $content);
    }
}

$time = microtime(true) - $start;

var_dump($time);
