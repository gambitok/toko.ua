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

$content = str_replace("{main_window}", $menu->showSellBlock() . $formObj->getHistoryArts(), $content);
