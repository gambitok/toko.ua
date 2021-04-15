<?php

ini_set('memory_limit', '2048M');

$linka = findLinks();
$router = $linka[1];
$router_2 = $linka[2];

$str_linka = $linka;
unset($str_linka[0]);
$str_linka = implode("/", $str_linka);

if ($router == "") {
    $content = str_replace("{main_window}", $catalogue->getCatalogColList(), $content);
}

if ($router == "show") {
    $content = str_replace("{main_window}", $catalog_exist->showPartsForm(), $content);
}

$group_id = $catalog_exist->getGroupExistId($router);

if (!empty($group_id)) {
    $filters = $linka[2];
    if ($filters == "auto") {
        $filters = [];
    }
    $mfa_link = $linka[3];
    $model_link = $linka[4];
    $page = $catalogue->getUrlNumber($_GET['page']);
    ($page != NULL) ?: $page = 1;
    $status_auto_type = $catalogue->getUrlNumber($_COOKIE['status_auto_type']);
    ($status_auto_type != NULL) ?: $status_auto_type = 0;
    $catalog_form = $catalog_exist->showPartsCatalogueParams($group_id, $str_linka, $page, $filters, $status_auto_type, $mfa_link, $model_link);

    $content = str_replace("{main_window}", $catalog_form["form"], $content);
    $content = str_replace("{site_title}", $catalog_form["title"], $content);
}

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