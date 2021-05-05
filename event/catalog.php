<?php

ini_set('memory_limit', '2048M');
$linka = findLinks();

$site_name = $catalogue->getUrlString($linka[0]);
$router = $catalogue->getUrlString($linka[1]);
$router_2 = $catalogue->getUrlString($linka[2]);
$router_3 = $catalogue->getUrlString($linka[3]);
$router_4 = $catalogue->getUrlString($linka[4]);

$path_from = $site_name . "/" . $router . "/";
if ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
    $mfa_link = $router_2;
    $model_link = $router_3;
    $path_to = $catalogue->getCatalogRedirectLink($path_from, $mfa_link, $model_link)["redirect_link"];
    header("Location: $path_to", TRUE, 301);
}

$str_linka = $linka;
unset($str_linka[0]);
$str_linka = implode("/", $str_linka);

/*
 * Catalog
 * */
if ($router == "") {
    $content = str_replace("{main_window}", $catalogue->getCatalogColList(), $content);
}

/*
 * Catalog with Group
 * */
$group_id = $catalog_exist->getGroupExistId($router);
if (!empty($group_id)) {
    $filters = $router_2;
    if ($filters == "auto") {
        $filters = [];
    }
    $mfa_link = $router_3;
    $model_link = $router_4;
    $page = $catalogue->getUrlNumber($_GET['page']);
    ($page != NULL) ?: $page = 1;
    $status_auto_type = $catalogue->getUrlNumber($_COOKIE['status_auto_type']);
    ($status_auto_type != NULL) ?: $status_auto_type = 0;
    $catalog_form = $catalog_exist->showPartsCatalogueParams($group_id, $str_linka, $page, $filters, $status_auto_type, $mfa_link, $model_link);
    $content = str_replace("{main_window}", $catalog_form["form"], $content);
    $content = str_replace("{site_title}", $catalog_form["title"], $content);
}

/*
 * Catalog with Header or Category
 * */
$head_id = $catalog_exist->getGroupHeadExistId($router);
if (!empty($head_id)) {
    $cat_id = $catalog_exist->getGroupCatExistId($router_2);
    if (empty($cat_id)) {
        $catalog_form = $catalog_exist->showGroupHeadForm($head_id);
    } else {
        $catalog_form = $catalog_exist->showGroupCatForm($head_id, $cat_id);
    }
    $content = str_replace("{main_window}", $catalog_form, $content);
}

