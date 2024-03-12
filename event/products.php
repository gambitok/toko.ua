<?php

$linka = findLinks();

//if ($linka[1] === 'adm55340-BLUE%20PRINT-3424881') {
//    $red_link = $catalogue->getSiteLink() . $catalogue->products_link . '/os3568-calorstat-by-vernet-2794889/';
//    header("Location: $red_link", TRUE, 301);
//}

$art_id = (int)substr($linka[1], strrpos($linka[1], "-") + 1);

if ($art_id > 0 && $catalogue->checkArticleExist($art_id)) {
    $articleData = $showform->getArticleForm($art_id, 1);
    $breadcrumbsData = $catalogue->getBreadCrumbForm($articleData["breadcrumbs"]);

    $data = getSeoTitleData();
    if ($data) {
        $descr = $data[1];

        if ($descr !== "") {
            $descr = str_replace('"', "'", $descr);
            $content = str_replace("{site_description}", $descr, $content);
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
