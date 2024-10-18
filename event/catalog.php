<?php

global $content, $catalogue, $catalog_exist, $formObj, $autoObj;

ini_set('memory_limit', '2048M');

$httpHost   = findLinks();
$red_status = 0;
$red_type   = 0;
$red_link   = "";

$sort       = $catalogue->getUrlString($_GET["sort"]);
$city_link  = $catalogue->getUrlString($_GET["city"]);
$page       = $catalogue->getUrlNumber($_GET["page"]);

$site_name  = $catalogue->getUrlString($httpHost[0]);
$router     = $catalogue->getUrlString($httpHost[1]);
$router_2   = $catalogue->getUrlString($httpHost[2]);
$router_3   = $catalogue->getUrlString($httpHost[3]);
$router_4   = $catalogue->getUrlString($httpHost[4]);
$router_5   = $catalogue->getUrlString($httpHost[5]);

$path_from  = $site_name . "/" . $router . "/";
$site_link  = $catalogue->getSiteLink();
$catalog_link = $catalogue->catalog_link;
$src_link   = $site_link . implode("/", $httpHost) . "/";

if ($httpHost[2] === "%22") {
    $red_status = 1;
    $red_type   = 301;
    $red_link   = $site_link . $catalog_link . "/" .  $router;
}

$error_form = $catalogue->getHtmlForm("error/404_catalog");
$history_form = $formObj->getHistoryArts();
$index_form = $catalogue->getHtmlForm("seo/noindex_follow");
$noindex_form = $catalogue->getHtmlForm("seo/noindex_nofollow");

if ($catalogue->getCatalogOldRedirectLink($httpHost)["status"] > 0) {
    $red_status = 1;
    $red_type   = 301;
    $red_link   = $catalogue->getCatalogOldRedirectLink($httpHost)["redirect_link"];
}
elseif ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
    $mfa_link   = $router_2;
    $model_link = $router_3;
    $red_status = 1;
    $red_type   = 301;
    $red_link   = $catalogue->getCatalogRedirectLink($path_from, $mfa_link, $model_link)["redirect_link"];
}
else {
    $httpHostString = $httpHost;
    unset($httpHostString[0]);
    $httpHostString = implode("/", $httpHostString);

    /*
     * Catalog
     * */

    if ($router === "") {
        $h1 = $catalogue->getHtmlForm("catalog_exist/h1");

        if ($city_link !== "") {

            // redirect /?city=kiev/
            $check_link = $_SERVER['REQUEST_URI'];
            $s = substr($check_link, -1);

            if ($s === "/") {
                $check_link = ltrim($check_link, "/");
                $check_link = rtrim($check_link, "/");
                $red_status = 1;
                $red_type   = 301;
                $red_link   = $site_link . $check_link;
            }

            if ($catalogue->checkCityLink($city_link)) {
                $city_name_in   = $catalogue->getCityNameIn($city_link, "CITY_NAME_IN_");
                $city_name      = $catalogue->getCityNameIn($city_link);
                $h1             = str_replace("{city_name_in}", $city_name_in, $catalogue->getHtmlForm("catalog_exist/h1_city"));
                $title          = str_replace("{CITY_NAME_RU}", $city_name, $catalogue->replaceLang("{site_catalog_title_city}"));
                $description    = str_replace("{CITY_NAME_IN_RU}", $city_name_in, $catalogue->replaceLang("{site_catalog_descr_city}"));

                $content = str_replace(array("{site_title}", "{site_description}"), array($title, $description), $content);
            } else {
                $red_status = 1;
                $red_type = 404;
                $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
            }
        }

        $content = str_replace("{main_window}", $h1 . $catalogue->getCatalogColList() . $history_form, $content);
    } else {

        // GROUP_ID + CITY
        if ($city_link !== "") {
            $red_status = 1;
            $red_type = 404;
            $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
        }

        /*
         * Catalog with Group
         * */
        $group_id = $catalog_exist->getGroupExistId($router);

        if (!empty($group_id)) {
            $group_id = $catalog_exist->getUrlNumber($group_id);
            $_SESSION['group_id'] = $group_id;
            $filters  = $httpHost[2];
            $f1 = $filters;

            $filters        = ($filters === "auto") ? [] : $filters;
            $mfa_link       = $router_3;
            $model_link     = $router_4;
            $model_id_link  = $router_5;
            $mfa_id         = 0;
            $model          = "";
            $model_id       = 0;
            $params         = [];

            if ($f1 === "auto" && empty($filters) && empty($mfa_link)) {
                $red_status = 1;
                $red_type   = 301;
                $red_link   = $site_link . $catalog_link . '/' . $router;
            }

            if ($mfa_link !== "") {
                $mfa_id = $autoObj->getMfaLink($mfa_link);

                if ($mfa_id === 0) {
                    $red_status = 1;
                    $red_type = 404;
                    $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
                }

                if ($model_link !== "") {

                    if ($model_link === "rav4") {
                        $red_status = 1;
                        $red_type = 301;
                        $red_link = $site_link . $catalog_link . "/$router/" . $httpHost[2] . "/$router_3/rav-4/";
                    } else {
                        $model = $autoObj->getModLink($model_link);

                        if ($model === "") {
                            $red_status = 1;
                            $red_type = 404;
                            $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
                        }

                        if ($model !== "") {
                            $model_id = $autoObj->getModIdLink($model_id_link);

                            if (($model_id_link !== "") && !$model_id) {
                                $red_status = 1;
                                $red_type = 404;
                                $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
                            }
                        }
                    }
                }
            }

            $count_brands = 0;

            if (!empty($filters)) {
                list($check_status, $check_link, $check_status_error) = $catalog_exist->checkRedirects($filters);

                if ($check_status_error > 0) {
                    $red_status = 1;
                    $red_type = 404;
                    $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
                }

                if ($f1 === "uk" || $f1 === "en") {
                    $red_status = 1;
                    $red_type = 301;
                    $red_link = $site_link . $catalog_link . "/$router/";
                }

                if ($check_status > 0) {
                    $red_status = 1;
                    $red_type = 301;
                    $group_link = $catalog_exist->getGroupRowLink($group_id);
                    $red_link = $site_link . $catalog_link .  "/$group_link/" . $check_link . "/";

                    if ($mfa_link !== "") {
                        $red_link .= "$mfa_link/";

                        if ($model_link !== "") {
                            $red_link .= "$model_link/";
                        }
                    }
                }

                $params = $catalog_exist->getCheckedFilters($group_id, $filters);

                if ($filters !== "" && empty($params)) {
                    $red_status = 1;
                    $red_type = 301;
                    $red_link = $site_link . $path_from;
                }

                list($count_brands, $count_params, $count_values, $real_count_params, $real_count_brands) = $catalog_exist->getCatalogParamsCount($params);

                if ($real_count_brands >= 2) {
                    $content = str_replace("{meta_noindex}", $noindex_form, $content);
                }

                if ($real_count_params > 1) {
                    $content = str_replace("{meta_noindex}", $noindex_form, $content);
                }

                $content = str_replace("{seoshield_formulas}", $catalogue->getHtmlForm("seo/shield"), $content);
            }

            (!empty($page)) ?: $page = 1;

            $status_auto = $catalog_exist->getGroupExistStatusAuto($group_id);

            $status_auto_type = $catalogue->getUrlNumber($_COOKIE["status_auto_type"]);
            (!empty($status_auto_type)) ?: $status_auto_type = 0;

            $catalog_form = $catalog_exist->showPartsCatalogueParams($group_id, $page, $filters, $params, $mfa_id, $model, $model_id, $status_auto, $status_auto_type, $src_link, $sort, $count_brands);

            if ($page > $catalog_form["pages_count"] && $catalog_form["pages_count"] > 0) {
                $max_page   = $catalog_form["pages_count"];
                $path_to    = $site_link . ltrim(findUrl(), "/") . "?page=$max_page";
                $red_status = 1;
                $red_type   = 301;
                $red_link   = $path_to;
            }

            if ($page > 1) {
                $content = str_replace("{meta_noindex}", $index_form, $content);
            }

            $content = str_replace("{main_window}", $catalog_form["form"] . $history_form, $content);
            $content = str_replace("{site_title}", $catalog_form["title"], $content);
            $content = str_replace("{site_description}", $catalog_form["description"], $content);
            $content = str_replace("{meta_social_tag}", $catalog_exist->getCatalogMetaTags($group_id, $catalog_form["h1"]), $content);
            $content = str_replace("{site_script_breadcrumbs}", $catalog_form["script"], $content);
        }

        /*
         * Catalog with Header or Category
         * */
        $head_id = $catalog_exist->getGroupHeadExistId($router);

        if (!empty($head_id)) {
            $_SESSION['head_id'] = (int)$head_id;
            $cat_id = $catalog_exist->getGroupCatExistId($router_2);

            if (empty($cat_id)) {
                // Header
                $catalogData = $catalog_exist->showGroupHeadForm($head_id);

                if ($router_2 !== "") {
                    $red_status = 1;
                    $red_type   = 301;
                    $red_link   = $site_link . $catalog_link .  "/$router/";
                }
            } else {
                // Category
                $_SESSION['cat_id'] = (int)$cat_id;
                $catalogData = $catalog_exist->showGroupCatForm($head_id, $cat_id);

                if ($router_3 !== "") {
                    $red_status = 1;
                    $red_type   = 301;
                    $red_link   = $site_link . $catalog_link .  "/$router/$router_2/";
                }
            }

            $content = str_replace("{main_window}", $catalogData["form"] . $history_form, $content);
            $content = str_replace("{site_title}", $catalogData["title"], $content);
            $content = str_replace("{site_description}", $catalogData["description"], $content);

            $content = str_replace("{main_site_breadcrumbs}", $catalogData["breadcrumb"], $content);
            $content = str_replace("{site_script_breadcrumbs}", $catalogData["script"], $content);
        }

        if ($router_2 === "clutch%20" || $router_2 === "clutch ") {
            $red_status = 1;
            $red_type   = 301;
            $red_link   = $site_link . $catalog_link .  "/stceplenie_i_transmissiia/clutch/";
        }

        /*
         * No Head and No Group
         * */
        if (empty($head_id) && empty($group_id)) {
            $red_status = 1;
            $red_type   = 404;
            $content = str_replace(array("{main_window}", "{meta_noindex}"), array($error_form, $noindex_form), $content);
        }
    }
}

if ($red_status) {

    if ($red_type === 404) {
        header("HTTP/1.0 404 Not Found");
    }

    if ($red_type === 301) {
        header("Location: $red_link", TRUE, 301);
    }
}
