<?php

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

if ($router == "init_params") {
    $group_link = $linka[2];
    $group_id = $catalog_exist->getGroupExistId($group_link);
    if ($group_id > 0) {
        $content = str_replace("{main_window}", $catalog_exist->getInitParamsForm($group_id), $content);
    }
}

if ($router == "show_params") {
    $group_link = $linka[2];
    $filters = $linka[3];
    $mfa_link = $linka[4];
    $model_link = $linka[5];
    $group_id = $catalog_exist->getGroupExistId($group_link);
    $page = $catalogue->getUrlNumber($_GET['page']);
    ($page != NULL) ?: $page = 1;
    $status_auto_type = $catalogue->getUrlNumber($_COOKIE['status_auto_type']);
    ($status_auto_type != NULL) ?: $status_auto_type = 0;

    if (empty($group_id)) {
        header("Location: /catalog_exist/", TRUE, 301);
    } else {
        $content = str_replace("{main_window}", $catalog_exist->showPartsCatalogueParams($group_id, $page, $filters, $status_auto_type, $mfa_link, $model_link)["form"], $content);
    }
}

