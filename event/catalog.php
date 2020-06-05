<?php

$page=$_GET["page"]; $page!=NULL ?: $page=1;

$linka=findLinks(); $some_link=$linka[1]; $some_link2=$linka[2];

if (preg_match('~^\p{Lu}~u', $some_link)) {
    $new_link=strtolower($some_link);
    header("Location: /catalog/$new_link", TRUE, 301);
}

if (!$catalogue->checkRedirectLink($some_link)) {
    $new_link=$catalogue->getRedirectLink($some_link);
    header("Location: /catalog/$new_link", TRUE, 301);
}

$result=explode($linka[0]."/", $_SERVER["REQUEST_URI"], 2); $link=ltrim($result[1]);

list($form1, $car_content, $pages_count) = $search->catalogRouter($link, $some_link, $some_link2);

if ($page>1) if ($page>$pages_count) {
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (strpos($actual_link,"?")!==false) $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
    header("Location: $actual_link", TRUE, 301);
}

$search_form=$prod->getHtmlForm("car_form_div");
$search_form=str_replace("{car_content}", $car_content, $search_form);

if ($car_content=="") {
    $search_form="";
    $head_id=$automan->getHeadNewLinkStr($some_link);
    if ($linka[2]=="") $content=str_replace("{main_metro}", "<div class='wdt100'>".$automan->getDetailsHeadImage($head_id)."</div>", $content);
}

$content=str_replace("{main_auto_window}", $search_form, $content);
$content=str_replace("{main_window}", $form1, $content);
$content=str_replace("{site_page_pagination}", $pages_count>0 ?  $catalogue->getPagePagination($page, $pages_count) : "", $content);

