<?php

$theme_htm = RDD . "/main.htm";
if (file_exists("$theme_htm")) {
    $content = file_get_contents($theme_htm);
}

$path = getPath();
if ($path == "seoshield-client") {
    include RDD . "/seoshield-client/index.php";
    include RDD . "/seoshield-client/main.php";
    $content = "";
} elseif ($path == "" || $path == "/") {
    include_once RDD . "/event/main.php";
} elseif ($path == "/uk/" || $path == "/en/") {
    if ($path == "uk") {
        $language->setLangID(2);
    }
    if ($path == "en") {
        $language->setLangID(3);
    }
    include_once RDD . "/event/main.php";
} elseif (file_exists(RDD . "/event/$path.php")) {
    include_once RDD . "/event/$path.php";
} else {
    include RDD . "/event/404.php";
}
include_once(RDD . "/event/menu.php");

// Main HEAD HTML
$content = str_replace("{site_lang_html}", getSiteLang(), $content);
$content = str_replace("{site_google_conversation}", "", $content);
$content = str_replace("{site_title}", getTitle($path), $content);
$content = str_replace("{site_description}", getDescription($path), $content);
$content = str_replace("{site_keywords}", getKeywords($path), $content);
$content = str_replace("{site_script_breadcrumbs}", printBreadcrumbs($path)[1], $content);
$content = str_replace("{site_page_pagination}", "", $content);
$content = str_replace("{site_lang_prefix}", $language->getLangPrefix(), $content);

// Main SEO BLOCK
$seo_text = "<!--seo_text_start--><!--seo_text_end-->";
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin') === false && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')) {
        if (file_exists(RDD."/seoshield-client/main.php")) {
            include_once(RDD."/seoshield-client/main.php");
            if (function_exists('seo_shield_start_cms')) {
                seo_shield_start_cms();
            }
            if (function_exists('seo_shield_out_buffer')) {
                $content = seo_shield_out_buffer($content);
                $seo_text = seo_shield_out_buffer($seo_text);
            }
        }
    }
}

// Main HTML
$content = str_replace("{main_seo_text}", ($seo_text == "" || $seo_text == "<!--seo_text_start--><!--seo_text_end-->") ? "" : getSeoText($seo_text), $content);
$content = str_replace("{main_auto_window}", "", $content);
$content = str_replace("{main_site_breadcrumbs}", printBreadcrumbs($path)[0], $content);
$content = str_replace("{main_window}", "", $content);
$content = str_replace("{main_metro}", "", $content);
$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="yandex" content="index, follow">
', $content);

$linka = findLinks();

$url = $catalogue->getUrlString($linka[0]);

if ($url == "cars") {
    $mfa_link = $catalogue->getUrlString($linka[1]);
    $mod_link = $catalogue->getUrlString($linka[2]);
    list($mfa_text, ) = $automan->getAutoDescrLink($mfa_link, $mod_link);
    if ($mfa_text != "") {
        $content = str_replace("{main_seo_text_cars}", $search->getSeoCarsLinking($mfa_link, $mod_link), $content);
    }
}

//if ($url == "catalog") {
//    $str_id = $str_link = $mfa_link = $mod_link = $filters = "";
//
//    $some = $catalogue->getUrlString($linka[0]);
//
//    $result = explode($some . "/", $_SERVER["REQUEST_URI"], 2);
//    $link = ltrim($result[1]);
//
//    $arr = explode("/", $link);
//    if (!empty($arr[0])) {
//        $str_link = $arr[0];
//    }
//    if (!empty($arr[3])) {
//        ((strpos($arr[4], "=") !== false)) ? $filters = $arr[4] : $filters = "";
//    }
//    if (!empty($arr[3])) {
//        ((strpos($arr[3], "=") !== false)) ? $filters = $arr[3] : $mod_id_link = $arr[3];
//    }
//    if (!empty($arr[2])) {
//        ((strpos($arr[2], "=") !== false)) ? $filters = $arr[2] : $mod_link = $arr[2];
//    }
//    if (!empty($arr[1])) {
//        ((strpos($arr[1], "=") !== false)) ? $filters = $arr[1] : $mfa_link = $arr[1];
//    }
//    $where_arts = $parts->initPartsArts($str_id);
//    $brand_ids = $this->getBrandIds($where_arts);
//    $active_brands = array_unique($brand_ids);
//
//    $content = str_replace("{main_seo_text_cars}", $search->getSeoMfaLinking($str_id, "H1", $where_arts, $active_brands, $mfa_link, $mod_link), $content);
//}

if ($url == "catalog") {
    $str_link = $mfa_link = $mod_link = $mod_id_link = $filters = "";

    $some = $catalogue->getUrlString($linka[0]);

    $result = explode($some . "/", $_SERVER["REQUEST_URI"], 2);
    $link = ltrim($result[1]);

    $arr = explode("/", $link);
    if (!empty($arr[0])) {
        $str_link = $arr[0];
    }
    if (!empty($arr[3])) {
        ((strpos($arr[4], "=") !== false)) ? $filters = $arr[4] : $filters = "";
    }
    if (!empty($arr[3])) {
        ((strpos($arr[3], "=") !== false)) ? $filters = $arr[3] : $mod_id_link = $arr[3];
    }
    if (!empty($arr[2])) {
        ((strpos($arr[2], "=") !== false)) ? $filters = $arr[2] : $mod_link = $arr[2];
    }
    if (!empty($arr[1])) {
        ((strpos($arr[1], "=") !== false)) ? $filters = $arr[1] : $mfa_link = $arr[1];
    }
    // check brandy
    $brand_filters = $search->getActiveFilters($filters);
    $active_filters = $brand_filters[0];
    if (count($active_filters) == 1) {

        $str_id = $automan->getStrNewLinkStr($str_link);
        $str_name = $automan->getStrDescr($str_id);
        $mfa_id = $automan->getMfaLink($mfa_link);
        $mod = $automan->getModLink($mod_link);
        $mod_id = $automan->getModIdCode($mod_id_link);
        $brand_name = $catalogue->getBrandName($active_filters[0]);

        $content .= "<!--seoshield_formulas--fil-traciya-->";
        $content .= "<!--ss_selected_filters_info|brandy|$brand_name-->";
//        $content .= "<!--product_in_listingEX-->";
//        $content .= "<!--ss_pagination_page-->";
//        $content .= "<!--ss_category_name:$str_name-->";

        $seo_breadcrumb = getSeoBreadcrumbs($str_id, $mfa_id, $mod, $mod_id);
        $seo_breadcrumb = $automan->replaceLang($seo_breadcrumb);
        $content .= "<!--ss_breadcrums_list:$seo_breadcrumb-->";
    }

}

$content = str_replace("{main_seo_text_cars}", "", $content);

$content = getContent($content);
$content = translateContent($content);
$content = $menu->getImages($content);

print $content;
