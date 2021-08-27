<?php

$redirect_status = 0;
$redirect_type = 0;
$redirect_link = "";

ini_set('memory_limit', '2048M');
$linka = findLinks();

$site_name = $catalogue->getUrlString($linka[0]);
$router = $catalogue->getUrlString($linka[1]);
$router_2 = $catalogue->getUrlString($linka[2]);
$router_3 = $catalogue->getUrlString($linka[3]);
$router_4 = $catalogue->getUrlString($linka[4]);
$page = $catalogue->getUrlNumber($_GET["page"]);

$path_from = $site_name . "/" . $router . "/";

$source_link = $catalogue->getSiteLink() . implode("/", $linka) . "/";

if ($catalogue->getCatalogOldRedirectLink($linka)["status"] > 0) {
    $redirect_status = 1;
    $redirect_type = 301;
    $redirect_link = $catalogue->getCatalogOldRedirectLink($linka)["redirect_link"];
}
elseif ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
    $mfa_link = $router_2;
    $model_link = $router_3;
    $path_to = $catalogue->getCatalogRedirectLink($path_from, $mfa_link, $model_link)["redirect_link"];
    $redirect_status = 1;
    $redirect_type = 301;
    $redirect_link = "$path_to";
} else {
    $str_linka = $linka;
    unset($str_linka[0]);
    $str_linka = implode("/", $str_linka);
    /*
     * Catalog
     * */
    if ($router == "") {
        $content = str_replace("{main_window}", "<div><h1>{site_catalog}</h1></div>" . $catalogue->getCatalogColList(), $content);
    } else {
        /*
         * Catalog with Group
         * */
        $group_id = $catalog_exist->getGroupExistId($router);
        if (!empty($group_id)) {
            $group_id = $catalog_exist->getUrlNumber($group_id);

            $filters = $linka[2];
            if ($filters == "auto") {
                $filters = [];
            }
            $mfa_link = $router_3;
            $model_link = $router_4;

            $mfa_id = 0;
            $model = "";
            if ($mfa_link != "") {
                $mfa_id = $automan->getMfaLink($mfa_link);
                if ($mfa_id == 0) {
                    $redirect_status = 1;
                    $redirect_type = 404;
                    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
                }
                if ($model_link != "") {
                    if ($model_link == "rav4") {
                        $redirect_status = 1;
                        $redirect_type = 301;
                        $redirect_link = $catalog_exist->getSiteLink() . $catalog_exist->catalog_link . "/$router/" . $linka[2] . "/$router_3/rav-4/";
                    } else {
                        $model = $automan->getModLink($model_link);
                        if ($model == "") {
                            $redirect_status = 1;
                            $redirect_type = 404;
                            $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
                        }
                    }
                }
            }

            $params = [];
            if (!empty($filters)) {
                $params = $catalog_exist->getCheckedFilters($group_id, $filters);
                list($count_brands, $count_params, $count_values) = $catalog_exist->getCatalogParamsCount($params);
                if ($count_values > 0) {
                    $content = str_replace("{meta_noindex}", '
                        <meta name="robots" content="noindex, nofollow">
                        <meta name="googlebot" content="noindex, nofollow">
                        <meta name="yandex" content="noindex, nofollow">
                    ', $content);
                }
                $content = str_replace("{seoshield_formulas}", $catalog_exist->getCatalogFilterSeo(), $content);
            }

            ($page != NULL) ?: $page = 1;

            $status_auto = $catalog_exist->getGroupExistStatusAuto($group_id);

            $status_auto_type = $catalogue->getUrlNumber($_COOKIE["status_auto_type"]);
            ($status_auto_type != NULL) ?: $status_auto_type = 0;

            $catalog_form = $catalog_exist->showPartsCatalogueParams($group_id, $page, $filters, $params, $mfa_id, $model, $status_auto, $status_auto_type, $str_linka, $source_link);

            if ($page > $catalog_form["pages_count"] && $catalog_form["pages_count"] > 0) {
                $max_page = $catalog_form["pages_count"];
                $path_to = $catalog_exist->getSiteLink() . ltrim(findUrl(), "/") . "?page=$max_page";
                $redirect_status = 1;
                $redirect_type = 301;
                $redirect_link = "$path_to";
            }

            $content = str_replace("{main_window}", $catalog_form["form"], $content);
            $content = str_replace("{site_title}", $catalog_form["title"], $content);
            $content = str_replace("{site_description}", $catalog_form["description"], $content);
            $content = str_replace("{meta_social_tag}", $catalog_exist->getCatalogMetaTags($group_id, $catalog_form["h1"]), $content);
            $content = str_replace("{site_script_breadcrumbs}", $catalog_form["script"], $content);

            //$content = str_replace("{site_console}", $catalog_exist->getSiteConsole($catalog_form["time"]), $content);
        }

        /*
         * Catalog with Header or Category
         * */
        $head_id = $catalog_exist->getGroupHeadExistId($router);
        if (!empty($head_id)) {
            $cat_id = $catalog_exist->getGroupCatExistId($router_2);
            if (empty($cat_id)) {
                //Header
//                $catalog_form = $catalog_exist->showGroupHeadForm($head_id);
                $catalogData = $catalog_exist->showGroupHeadForm($head_id);
            } else {
                //Category
  //              $catalog_form = $catalog_exist->showGroupCatForm($head_id, $cat_id);
                $catalogData = $catalog_exist->showGroupCatForm($head_id, $cat_id);
            }
            $content = str_replace("{main_window}", $catalogData["form"], $content);
            $content = str_replace("{site_title}", $catalogData["title"], $content);
            $content = str_replace("{site_description}", $catalogData["description"], $content);
        }

        /*
         * No Head and No Group
         * */
        if (empty($head_id) && empty($group_id)) {
            $redirect_status = 1;
            $redirect_type = 404;
            $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
        }
    }
}

if ($redirect_status) {
    if ($redirect_type == 404) {
        header("HTTP/1.0 404 Not Found");
    }
    if ($redirect_type == 301) {
        header("Location: $redirect_link", TRUE, 301);
    }
}
