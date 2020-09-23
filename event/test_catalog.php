<?php

ini_set('memory_limit', '2048M');

$group_link = findLinks()[1];

$group_id = $automan->getGroupLinkID($group_link);

$cookie_typ_id = $prod->getCookieAuto();

$filters = findLinks(); array_splice($filters, 0, 2);
$page = $_GET["page"]; $page!=NULL ?: $page=1;

if ($group_id==null) {
    $content = str_replace("{main_window}", $template->getCatalogParamForm(), $content);
} else {

    $car_form = $prod->getHtmlForm("car_form_div");
    if ($cookie_typ_id=="") {
        $car_range = $prod->getCarsSearch();
    } else {
        $car_range = $prod->getCarsGarage();
    }
    $car_form = str_replace("{car_content}", $car_range, $car_form);

    $content = str_replace("{main_window}", $template->getCatalogForm($group_id, $page, $filters), $content);
    $content = str_replace("{main_auto_window}", $car_form, $content);
}
