<?php
/*
 * INIT / UPDATE CATALOG LIST
 * */
ini_set('memory_limit', '2048M');

$linka = findLinks();
$str_text = $linka[1];
$page = $catalogue->getUrlNumber($_GET['page']);
($page != NULL) ?: $page = 1;

$str_id = $automan->getStrNewLinkStr($str_text);

if ($linka[1] == "init") {
    if ($linka[2] != "")
        $content = str_replace("{main_window}", $parts->getInitForm($linka[2]), $content);
}

if ($str_id == "") {
    $content = str_replace("{main_window}", $parts->showPartsForm(), $content);
} else {
    $content = str_replace("{main_window}", $parts->showPartsCatalogue($str_id, $page)["form"], $content);
}

