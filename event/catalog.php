<?php

ini_set('memory_limit', '2048M');

$linka      = findLinks();
$red_status = 0;
$red_type   = 0;
$red_link   = "";
$sort       = $catalogue->getUrlString($_GET["sort"]);
$site_name  = $catalogue->getUrlString($linka[0]);
$router     = $catalogue->getUrlString($linka[1]);
$router_2   = $catalogue->getUrlString($linka[2]);
$router_3   = $catalogue->getUrlString($linka[3]);
$router_4   = $catalogue->getUrlString($linka[4]);
$router_5   = $catalogue->getUrlString($linka[5]);
$page       = $catalogue->getUrlNumber($_GET["page"]);
$path_from  = $site_name . "/" . $router . "/";
$src_link   = $catalogue->getSiteLink() . implode("/", $linka) . "/";

if ($catalogue->getCatalogOldRedirectLink($linka)["status"] > 0) {
    $red_status = 1;
    $red_type   = 301;
    $red_link   = $catalogue->getCatalogOldRedirectLink($linka)["redirect_link"];
}
elseif ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
    $mfa_link   = $router_2;
    $model_link = $router_3;
    $path_to    = $catalogue->getCatalogRedirectLink($path_from, $mfa_link, $model_link)["redirect_link"];
    $red_status = 1;
    $red_type   = 301;
    $red_link   = "$path_to";
} else {
    $str_linka = $linka;
    unset($str_linka[0]);
    $str_linka = implode("/", $str_linka);

    /*
     * Catalog
     * */
    if ($router == "") {
        $content = str_replace("{main_window}", "<div><h1>{site_catalog}</h1></div>" . $catalogue->getCatalogColList() . $showform->getHistoryArts(), $content);
    } else {
        /*
         * Catalog with Group
         * */
        $group_id = $catalog_exist->getGroupExistId($router);
        if (!empty($group_id)) {
            $group_id       = $catalog_exist->getUrlNumber($group_id);
            $filters        = $linka[2];
            $filters        = ($filters == "auto") ? [] : $filters;
            $mfa_link       = $router_3;
            $model_link     = $router_4;
            $model_id_link  = $router_5;
            $mfa_id         = 0;
            $model          = "";
            $model_id       = 0;
            $params         = [];

            if ($mfa_link != "") {
                $mfa_id = $automan->getMfaLink($mfa_link);

                if ($mfa_id == 0) {
                    $red_status = 1;
                    $red_type   = 404;
                    $content    = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
                }

                if ($model_link != "") {
                    if ($model_link == "rav4") {
                        $red_status = 1;
                        $red_type   = 301;
                        $red_link   = $catalog_exist->getSiteLink() . $catalog_exist->catalog_link . "/$router/" . $linka[2] . "/$router_3/rav-4/";
                    } else {
                        $model = $automan->getModLink($model_link);

                        if ($model == "") {
                            $red_status = 1;
                            $red_type   = 404;
                            $content    = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
                        }

                        if ($model != "") {
                            $model_id = $automan->getModIdLink($model_id_link);

                            if ($model_id_link != "") {
                                if (!$model_id) {
                                    $red_status = 1;
                                    $red_type   = 404;
                                    $content    = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($filters)) {
                list($check_status, $check_link) = $catalog_exist->checRedirects($filters);

                if ($check_status > 0) {
                    $red_status = 1;
                    $red_type   = 301;
                    $group_link = $catalog_exist->getGroupRowLink($group_id);
                    $red_link   = $catalogue->getSiteLink() . $catalogue->catalog_link .  "/$group_link/" . $check_link . "/";

                    if ($mfa_link != "") {
                        $red_link .= "$mfa_link/";
                        if ($model_link != "") {
                            $red_link .= "$model_link/";
                        }
                    }
                }

                $params = $catalog_exist->getCheckedFilters($group_id, $filters);

                list($count_brands, $count_params, $count_values) = $catalog_exist->getCatalogParamsCount($params);
                if ($count_values > 0) {
                    $content = str_replace("{meta_noindex}", '
                        <meta name="robots" content="noindex, nofollow">
                        <meta name="googlebot" content="noindex, nofollow">
                        <meta name="yandex" content="noindex, nofollow">
                    ', $content);
                }
                $content = str_replace("{seoshield_formulas}", "
                    <!--ss_selected_filters_info|FilterName|FilterValue-->
                    <!--seoshield_formulas--fil-traciya-->
                ", $content);
            }

            ($page != NULL) ?: $page = 1;

            $status_auto = $catalog_exist->getGroupExistStatusAuto($group_id);

            $status_auto_type = $catalogue->getUrlNumber($_COOKIE["status_auto_type"]);
            ($status_auto_type != NULL) ?: $status_auto_type = 0;

            $catalog_form = $catalog_exist->showPartsCatalogueParams($group_id, $page, $filters, $params, $mfa_id, $model, $model_id, $status_auto, $status_auto_type, $src_link, $sort);

//            var_dump($catalog_form["time"]);

            if ($page > $catalog_form["pages_count"] && $catalog_form["pages_count"] > 0) {
                $max_page   = $catalog_form["pages_count"];
                $path_to    = $catalog_exist->getSiteLink() . ltrim(findUrl(), "/") . "?page=$max_page";
                $red_status = 1;
                $red_type   = 301;
                $red_link   = "$path_to";
            }

            $content = str_replace("{main_window}", $catalog_form["form"] . $showform->getHistoryArts(), $content);
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
            $cat_id = $catalog_exist->getGroupCatExistId($router_2);
            if (empty($cat_id)) {
                //Header
                $catalogData = $catalog_exist->showGroupHeadForm($head_id);
            } else {
                //Category
                $catalogData = $catalog_exist->showGroupCatForm($head_id, $cat_id);
            }
            $content = str_replace("{main_window}", $catalogData["form"] . $showform->getHistoryArts(), $content);
            $content = str_replace("{site_title}", $catalogData["title"], $content);
            $content = str_replace("{site_description}", $catalogData["description"], $content);

            $content = str_replace("{main_site_breadcrumbs}", $catalogData["breadcrumb"], $content);
            $content = str_replace("{site_script_breadcrumbs}", $catalogData["script"], $content);
        }

        if ($router_2 == "clutch%20" || $router_2 == "clutch ") {
            $red_status = 1;
            $red_type = 301;
            $red_link = $catalogue->getSiteLink() . $catalogue->catalog_link .  "/stceplenie_i_transmissiia/clutch/";
        }

        /*
         * No Head and No Group
         * */
        if (empty($head_id) && empty($group_id)) {
            $red_status = 1;
            $red_type = 404;
            $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
        }
    }
}

if ($red_status) {
    if ($red_type == 404) {
        header("HTTP/1.0 404 Not Found");
    }
    if ($red_type == 301) {
        header("Location: $red_link", TRUE, 301);
    }
}
