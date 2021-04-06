<?php

ini_set('memory_limit', '2048M');

$linka = findLinks();
$router = $linka[1];

if (!empty($catalogue) && !(empty($catalog_exist))) {

    if ($router == "") {
        $content = str_replace("{main_window}", $catalogue->getCatalogColList(), $content);
    }

    if ($router == "show") {
        $content = str_replace("{main_window}", $catalog_exist->showPartsForm(), $content);
    }

    $group_id = $catalog_exist->getGroupExistId($router);

    if (!empty($group_id)) {
        $filters = $linka[2];
        if ($filters == "auto") {
            $filters = "";
        }
        $mfa_link = $linka[3];
        $model_link = $linka[4];
        $page = $catalogue->getUrlNumber($_GET['page']);
        ($page != NULL) ?: $page = 1;
        $status_auto_type = $catalogue->getUrlNumber($_COOKIE['status_auto_type']);
        ($status_auto_type != NULL) ?: $status_auto_type = 0;
        $catalog_form = $catalog_exist->showPartsCatalogueParams($group_id, $page, $filters, $status_auto_type, $mfa_link, $model_link);
        $content = str_replace("{main_window}", $catalog_form["form"], $content);
        $content = str_replace("{site_title}", $catalog_form["title"], $content);
    }

}
