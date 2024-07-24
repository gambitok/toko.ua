<?php

global $content, $catalogue, $formObj, $client;

$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

if (strpos($actual_link, "%20") !== false) {
    $new_link = str_replace("%20", "-", $actual_link);
    $new_link = mb_strtolower($new_link);
    header("Location: $new_link", TRUE, 301);
}

$httpHost = findLinks();

$art_id = (int)substr($httpHost[1], strrpos($httpHost[1], "-") + 1);

if ($art_id > 0 && $catalogue->checkArticleExist($art_id)) {
    $articleData = $formObj->getArticleForm($art_id, 1);
    $breadcrumbsData = $catalogue->getBreadCrumbForm($articleData["breadcrumbs"]);

    $data = getSeoTitleData();
    if ($data) {
        $description = $data[1];

        if ($description !== "") {
            $description = str_replace('"', "'", $description);
            $content = str_replace("{site_description}", $description, $content);
        }
    }

    $content = str_replace("{main_window}", $articleData["form"], $content);
    $content = str_replace("{main_site_breadcrumbs}", $breadcrumbsData["form"], $content);
    $content = str_replace("{site_script_breadcrumbs}", $breadcrumbsData["script"], $content);
    $content = str_replace("{site_title}", $articleData["title"], $content);
    $content = str_replace("{site_description}", $articleData["description"], $content);
    $content = str_replace("{meta_social_tag}", "", $content);

    if ($articleData["real_stock"] > 0) {
        $client->insertArtsHistory($art_id);
    }
} else {
    header("HTTP/1.0 404 Not Found");

    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404"), $content);
    $content = str_replace("{site_title}", "{seo_404_title}", $content);
    $content = str_replace("{site_description}", "", $content);
}
