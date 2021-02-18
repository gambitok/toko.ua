<?php

/*
* INIT / UPDATE CATALOG LIST
* */
ini_set('memory_limit', '2048M');

$linka = findLinks();
$router = $linka[1];

if ($router == "") {
    $content = str_replace("{main_window}", $catalog_exist->showPartsForm(), $content);
}
if ($router == "cut") {
    $content = str_replace("{main_window}", $catalog_exist->showPartsForm(1), $content);
}

if ($router == "init") {
    $group_link = $linka[2];
    $group_id = $catalog_exist->getGroupExistId($group_link);
    if ($group_id > 0) {
        $content = str_replace("{main_window}", $catalog_exist->getInitForm($group_id), $content);
    }
}

if ($router == "init_mfa") {
    $group_link = $linka[2];
    $group_id = $catalog_exist->getGroupExistId($group_link);
    if ($group_id > 0) {
        $content = str_replace("{main_window}", $catalog_exist->getInitMfaForm($group_id), $content);
    }
}

if ($router == "show") {
    $group_link = $linka[2];
    $group_id = $catalog_exist->getGroupExistId($group_link);
    $page = $catalogue->getUrlNumber($_GET['page']);
    ($page != NULL) ?: $page = 1;
    $content = str_replace("{main_window}", $catalog_exist->showPartsCatalogue($group_id, $page)["form"], $content);
}

if ($router == "show_mfa") {
    $group_link = $linka[2];
    $mfa_link = $linka[3];
    $model_link = $linka[4];

    $group_id = $catalog_exist->getGroupExistId($group_link);
    $mfa_id = 0; $model = "";
    if ($mfa_link != "") {
        $mfa_id = $automan->getMfaLink($mfa_link);
    }
    if ($model_link != "") {
        $model = $automan->getModLink($model_link);
    }

    $page = $catalogue->getUrlNumber($_GET['page']);
    ($page != NULL) ?: $page = 1;


    if ($mfa_link == "") {
        $content = str_replace("{main_window}", $catalog_exist->showPartsCatalogueForm($group_id), $content);
    } else {
        $content = str_replace("{main_window}", $catalog_exist->showPartsCatalogueMfa($group_id, $mfa_id, $model, $page)["form"], $content);
    }
}

