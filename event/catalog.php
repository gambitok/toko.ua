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

if ($router == "") {
    $content = str_replace("{main_window}", $catalogue->getCatalogColList(), $content);
}

if ($router == "show") {
    $content = str_replace("{main_window}", $catalog_exist->showPartsForm(), $content);
}

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

//
//
//$linka = findLinks();
//
//$path_from = $linka[0] . "/" . $linka[1] . "/";
//if ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
//    $mfa_link = $linka[2];
//    $model_link = $linka[3];
//    $path_to = $catalogue->getCatalogRedirectLink($path_from, $mfa_link, $model_link)["redirect_link"];
//    header("Location: $path_to", TRUE, 301);
//}
//
//$some = $catalogue->getUrlString($linka[0]);
//$some_link = $catalogue->getUrlString($linka[1]);
//$some_link2 = $catalogue->getUrlString($linka[2]);
//
//$page = $catalogue->getUrlNumber($_GET["page"]);
//($page != NULL) ?: $page = 1;
//
//$result = explode($some . "/", $_SERVER["REQUEST_URI"], 2);
//$link = ltrim($result[1]);
//
///*
// * redirect new Links
// * */
////if ($catalogue->checkRedirectStr($some_link)["status"]) {
////    $group_link = $catalogue->checkRedirectStr($some_link)["group_link"];
////    $except_some_link = explode("/", $link, 2)[1];
////    header("Location: /catalog/$group_link/$except_some_link", TRUE, 301);
////}
//
///*
// * redirect Uppercase
// * */
//if (preg_match('~^\p{Lu}~u', $some_link)) {
//    $new_link = strtolower($some_link);
//    header("Location: /catalog/$new_link", TRUE, 301);
//}
//
///*
// * reditect .html, .php
// * */
//if (!$catalogue->checkRedirectLink($some_link)) {
//    $new_link = $catalogue->getRedirectLink($some_link);
//    header("Location: /catalog/$new_link", TRUE, 301);
//}
//
///*
// * catalog Routing
// * */
//list($form1, $car_content, $pages_count, $filters_count) = $search->catalogRouter($link, $some_link, $page, $some_link2);
//
//if ($page > 1) {
//    if ($page > $pages_count) {
//        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//        if (strpos($actual_link,"?") !== false) {
//            $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
//        }
//        header("Location: $actual_link", TRUE, 301);
//    }
//}
//
//$car_form = $prod->getHtmlForm("car_form_div");
//$car_form = str_replace("{car_content}", $car_content, $car_form);
//
//if ($car_content == "") {
//    $car_form = "";
//    $head_id = $automan->getHeadNewLinkStr($some_link);
//    if ($some_link2 == "") {
//        $content = str_replace("{main_metro}", $automan->showDetailsHeader($head_id), $content);
//    }
//}
//
//if ($filters_count > 1) {
//    $content = str_replace("{meta_noindex}", '<meta name="robots" content="noindex, nofollow" />', $content);
//}
//
//$content = str_replace("{main_auto_window}", $car_form, $content);
//$content = str_replace("{main_window}", $form1, $content);
//$content = str_replace("{site_page_pagination}", ($pages_count > 0) ? $catalogue->getPagePagination($page, $pages_count) : "", $content);
//
