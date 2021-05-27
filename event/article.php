<?php

$linka = findLinks();
$article_search = $catalogue->getUrlString($linka[1]);
$brand_name = $catalogue->getUrlString($linka[2]);
$art_id = $catalogue->getUrlNumber($linka[3]);

if ($catalogue->checkArticleExist($art_id)) {
    $content = str_replace("{main_window}", $showform->showArticle($art_id), $content);
} else {
    header("HTTP/1.0 404 Not Found");
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404"), $content);
}




