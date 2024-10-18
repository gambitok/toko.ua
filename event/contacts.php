<?php

global $content, $catalogue, $menu, $formObj;

$red_status = 0;
$red_type   = 0;
$red_link   = "";

if (count(findLinks()) > 1) {
    $red_status = 1;
    $red_type   = 404;
    $content    = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
}

if ($red_status) {
    if ($red_type === 404) {
        header("HTTP/1.0 404 Not Found");
    }
    if ($red_type === 301) {
        header("Location: $red_link", TRUE, 301);
    }
}

$content = str_replace(
    array("{main_window}", "{site_title}", "{site_description}"),
    array($menu->showContacts() . $formObj->getHistoryArts(), "{site_contacts}", ""),
$content);
