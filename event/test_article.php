<?php

$linka = findLinks();
$article_search = $catalogue->getUrlString($linka[1]);
$brand_name = $catalogue->getUrlString($linka[2]);
$art_id = $catalogue->getUrlNumber($linka[3]);

if ($catalogue->checkArticleExist($art_id)) {
    $articleData = $showform->getArticleForm2($art_id);
    $content = str_replace("{main_window}", $articleData["form"], $content);
    $breadcrumbsData = $catalogue->getBreadCrumbForm($articleData["breadcrumbs"]);
    $content = str_replace("{main_site_breadcrumbs}", $breadcrumbsData["form"], $content);
    $content = str_replace("{site_script_breadcrumbs}", $breadcrumbsData["script"], $content);
    $content = str_replace("{site_title}", $articleData["title"], $content);
    $content = str_replace("{site_description}", $articleData["description"], $content);
    $content = str_replace("{meta_social_tag}", "", $content);
} else {
    header("HTTP/1.0 404 Not Found");
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404"), $content);
}

