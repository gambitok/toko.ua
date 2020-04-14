<?php

$theme_htm = RDD . "/main.htm"; if (file_exists("$theme_htm")) { $content = file_get_contents($theme_htm); }

$path = getPath();

if ($path=="seoshield-client") {
    include RDD . "/seoshield-client/index.php";
    include RDD . "/seoshield-client/main.php";
    $content="";
} else {
    if ($path=="" || $path=="/") {
        include_once RDD . "/event/main.php";
    }
    elseif ($path=="/uk/" || $path=="/en/"){
        session_start();
        if ($path=="uk") $_SESSION["lang"]=2;
        if ($path=="en") $_SESSION["lang"]=3;
        include_once RDD . "/event/main.php";
    }
    elseif (file_exists(RDD . "/event/$path.php")) {
        include_once RDD . "/event/$path.php";
    } else {
        include RDD . "/event/404.php";
    }
}

include_once(RDD . "/event/menu.php");
$content = str_replace("{site_title}", getTitle($path), $content);
$content = str_replace("{site_breadcrumbs}", printBreadcrumbs($path)[0], $content);
$content = str_replace("{script_breadcrumbs}", printBreadcrumbs($path)[1], $content);
$content = str_replace("{main_auto_window}", "", $content);
$content = str_replace("{site_description}", getDescription($path), $content);
$content = str_replace("{site_keywords}", getKeywords($path), $content);
$content = str_replace("{site_lang_html}", getSiteLang(), $content);
$content = str_replace("{site_lang_prefix}", $language->getLangPrefix(), $content);
$content = str_replace("{site_page_pagination}", "", $content);
$seo_text = "<!--seo_text_start--><!--seo_text_end-->";

if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin') === false && $_SERVER['REQUEST_METHOD'] === 'GET'){
    if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')){
        if(file_exists(RDD."/seoshield-client/main.php"))
        {
            include_once(RDD."/seoshield-client/main.php");
            if(function_exists('seo_shield_start_cms')){
                seo_shield_start_cms();
            }
            if(function_exists('seo_shield_out_buffer')){
                $content = seo_shield_out_buffer($content);
                $seo_text = seo_shield_out_buffer($seo_text);
            }
        }
    }
}

$content = str_replace("{main_seo_text}", $seo_text=="" || $seo_text=="<!--seo_text_start--><!--seo_text_end-->" ? "" : getSeoText($seo_text), $content);

$content = getContent($content);
$content = translateContent($content);
$content = $menu->getImages($content);

print $content;


