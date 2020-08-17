<?php

$group_id = findLinks()[1];
$filters = findLinks(); array_splice($filters, 0, 2);
$page = $_GET["page"]; $page!=NULL ?: $page=1;

if ($group_id==null) {
    $content = str_replace("{main_window}", $template->getCatalogParamForm(), $content);
} else {
    $content = str_replace("{main_window}", $template->getCatalogParamGroupForm($group_id, $page, $filters), $content);
}
