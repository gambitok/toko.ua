<?php

// PROFILE CATALOGS

$linka = findLinks();

$path_from = $linka[0] . "/" . $linka[1] . "/";
if ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
    $path_to = $catalogue->getCatalogRedirectLink($path_from)["redirect_link"];
    header("Location: $path_to", TRUE, 301);
}

$w = $catalogue->getUrlString($linka[1]);
$template_id = $pattern->getTemplateID($w);
$page = $catalogue->getUrlNumber($_GET["page"]);
($page != NULL) ?: $page = 1;
$result = explode($w . "/", $_SERVER["REQUEST_URI"], 2);
$link = ltrim($result[1]);

if ($w == "") {
    $form = $catalogue->getHtmlForm("template/templates");
    $form = str_replace("{select_group}", $catalogue->showCatalogueTemplates(), $form);
    $content = str_replace("{main_window}", $form, $content);
} elseif ($template_id == "" || $template_id == 0) {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404"), $content);
    $content = str_replace("{main_site_breadcrumbs}", "", $content);
    $content = str_replace("{site_page_pagination}", "", $content);
} else {
    list($list, $max_page) = $pattern->showProductsForm($template_id, $page, $link);
    $content = str_replace("{main_window}", $list, $content);
    $content = str_replace("{site_page_pagination}", $catalogue->getPagePagination($page, $max_page), $content);
}

