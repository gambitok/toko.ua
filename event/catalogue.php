<?php

global $catalogue;
$httpHost = findLinks();
$w = $catalogue->getUrlString($httpHost[1]);

if ($w === "" || $w === "finddetail" || $w === "findtec" || $w === "findmodel" || $w === "auto") {
    header("Location: /catalog/", TRUE, 301);
}

if ($w === "search") {
    $result = explode($w . "/", $_SERVER["REQUEST_URI"], 2);
    $link = ltrim($result[1]);
    header("Location: /search/$link", TRUE, 301);
}

if ($w === "article") {
    $result = explode($w . "/", $_SERVER["REQUEST_URI"], 2);
    $link = ltrim($result[1]);
    header("Location: /article/$link", TRUE, 301);
}
