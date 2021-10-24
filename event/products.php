<?php

// PROFILE CATALOGS

$linka = findLinks();

//$path_from = $linka[0] . "/" . $linka[1] . "/";
//if ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
//    $path_to = $catalogue->getCatalogRedirectLink($path_from)["redirect_link"];
//    header("Location: $path_to", TRUE, 301);
//}

$art_id = intval(substr($linka[1], strrpos($linka[1], "-") + 1));

//$brand_redirect = $catalogue->getBrandNameLink($brand);
// redirect from brand_name to brand_link
//if ($brand_redirect > 0) {
//    $brand_link = $catalogue->getBrandLink($brand_redirect);
//    $link = $catalogue->getSiteLink() . $catalogue->article_link . "/" . $article_search . "/" . $brand_link . "/" . $art_id . "/";
//    header("Location: $link", TRUE, 301);
//}

if ($catalogue->checkArticleExist($art_id) && $art_id > 0) {
    $client->insertArtsHistory($art_id);
    $articleData = $showform->getArticleForm($art_id);
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
    $content = str_replace("{site_title}", "{seo_404_title}", $content);
    $content = str_replace("{site_description}", "", $content);
}
