<?php

global $content, $catalogue, $search, $client;
$httpHost = findLinks();

$brand_link         = $catalogue->getUrlString($httpHost[2]);
$article_nr_search  = $catalogue->getUrlString($httpHost[1]);
$article_nr_search  = rawurldecode($article_nr_search);

$text = $_GET["text"];

if (!empty($text)) {
    $article_nr_search = $text;
    $article_nr_search = rawurldecode($article_nr_search);
    $article_nr_search = rtrim($article_nr_search, "/");
}

if ($article_nr_search === "") {
    $form = $catalogue->getHtmlForm("error/search_unknown");
} else {
    $form = "{search}";

    if ($brand_link === "") {
        $form = str_replace("{search}", $search->getSearchList($article_nr_search), $form);
    } else {
        $brand_id = $catalogue->getCatalogueBrandID($brand_link);
        $form = str_replace("{search}", $catalogue->getCatalogList($catalogue->getFormatArticle($article_nr_search), $brand_id), $form);
        $client->insertHistorySearch($article_nr_search, $brand_id);
    }
}

$client_id = $client->getClient();
$client_category = (int)$client->getClientCategory($client_id);
if ($client_category !== 140) {
    $list = $catalogue->getWarningBlock();
    $form = $list . $form;
}

$content = str_replace("{main_window}", $form, $content);

$content = str_replace("{meta_noindex}", $catalogue->getHtmlForm("seo/noindex_nofollow"), $content);