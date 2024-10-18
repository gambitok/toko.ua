<?php

global $catalogue, $content, $formObj, $prod;

$httpHost = findLinks();

$mfa_link = $catalogue->getUrlString($httpHost[1]);
$mod_link = $catalogue->getUrlString($httpHost[2]);

$formData = $prod->getCarsForm($mfa_link, $mod_link);

if ($formData["status"] === 1) {
    $content = str_replace("{main_window}", $formData["form"] . $formObj->getHistoryArts(), $content);

    if (!empty($formData["title"])) {
        $content = str_replace("{site_title}", $formData["title"], $content);
    }

    if (!empty($formData["description"])) {
        $content = str_replace("{site_description}", $formData["description"], $content);
    }

    $content = str_replace(
        array("{meta_social_tag}", "{main_site_breadcrumbs}", "{site_script_breadcrumbs}"),
        array("", str_replace("{form}", $formData["breadcrumbs"]["form"],
    $catalogue->getHtmlForm("cars/container")), $formData["breadcrumbs"]["script"]), $content);
} else {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
    header("HTTP/1.0 404 Not Found");
}
