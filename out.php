<?php

$language->setLangID(findLanguageID(findLanguage()));

$theme_htm = RDD . "/main.htm";
if (file_exists("$theme_htm")) {
    $content = file_get_contents($theme_htm);
}

$path = getPath();
if ($path == "seoshield-client") {
    include RDD . "/seoshield-client/index.php";
    include RDD . "/seoshield-client/main.php";
    $content = "";
}
elseif ($path == "" || $path == "/") {
    include_once RDD . "/event/main.php";
}
elseif ($path == "/uk/" || $path == "/en/") {
    include_once RDD . "/event/main.php";
}
elseif (file_exists(RDD . "/event/$path.php")) {
    include_once RDD . "/event/$path.php";
} else {
    include RDD . "/event/404.php";
}
include_once(RDD . "/event/menu.php");

// Main HEAD HTML
$content = str_replace("{navigation_content}", $catalogue->getSiteNavigation(), $content);
$content = str_replace("{footer_content}", getHtmlForm("main/footer"), $content);
$content = str_replace("{anchor_contacts_content}", getHtmlForm("main/anchor-contacts"), $content);

$content = str_replace("{site_main_link}", $catalogue->getSiteLink(), $content);
$content = str_replace("{site_lang_html}", getSiteLang(), $content);
$content = str_replace("{site_google_conversation}", "", $content);
$content = str_replace("{meta_social_tag}", getMetaTag(), $content);
$content = str_replace("{site_title}", getTitle($path), $content);
$content = str_replace("{site_description}", getDescription($path), $content);
$content = str_replace("{site_keywords}", getKeywords($path), $content);
$breadData = printBreadcrumbs($path);
$content = str_replace("{site_script_breadcrumbs}", $breadData[1], $content);
$content = str_replace("{main_site_breadcrumbs}", $breadData[0], $content);
$content = str_replace("{site_page_pagination}", "", $content);
$content = str_replace("{site_warning_message}", $menu->getSiteWarningMessage(), $content);
$content = str_replace("{seo_footers_block}", "<!--footers_block-->", $content);
$content = str_replace("{site_console}", "", $content);
$content = str_replace("{seoshield_formulas}", "", $content);

$site_link = getSiteCurentLink();
$content = str_replace("{site_link_ru}", $site_link["ru"], $content);
$content = str_replace("{site_link_uk}", $site_link["uk"], $content);
$content = str_replace("{site_link_en}", $site_link["en"], $content);

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
//$content = str_replace("{main_seo_text}", ($seo_text == "" || $seo_text == "<!--seo_text_start--><!--seo_text_end-->") ? "" : getSeoText($seo_text), $content);
$content = str_replace("{main_seo_text}", getSeoTextForm(), $content);
$content = str_replace("{main_window}", "", $content);
$lang_postfix = findLanguage();
if ($lang_postfix != "") {
    $content = str_replace("{meta_noindex}", '
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
        <meta name="yandex" content="noindex, nofollow">
    ', $content);
}
$no_index = findNoIndex();
if ($no_index) {
    $content = str_replace("{meta_noindex}", '
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
        <meta name="yandex" content="noindex, nofollow">
    ', $content);
}
$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="yandex" content="index, follow">
', $content);

$linka = findLinks();

$content = str_replace("{main_seo_products_content}", "", $content);

$content = str_replace("{main_seo_text_cars}", "", $content);

$content = getContent($content);
$content = translateContent($content);
$content = $menu->getImages($content);

print $content;
