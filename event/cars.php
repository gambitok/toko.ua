<?php

global $catalogue, $content, $formObj, $prod;

$httpHost = findLinks();

$mfa_link = $catalogue->getUrlString($httpHost[1]);
$mod_link = $catalogue->getUrlString($httpHost[2]);

$formData = $prod->getCarsForm($mfa_link, $mod_link);

if ($formData["status"] === 1) {
    $content = str_replace("{main_window}", $formData["form"] . $formObj->getHistoryArts(), $content);
    if ($formData["title"] !== "") {
        $content = str_replace("{site_title}", $formData["title"], $content);
    }
    if ($formData["description"] !== "") {
        $content = str_replace("{site_description}", $formData["description"], $content);
    }
    $content = str_replace("{meta_social_tag}", "", $content);
    $content = str_replace("{main_site_breadcrumbs}",
    "<div class=\"container pad0\"
        <div class=\"row cat-products\">
            <div class=\"col-12\">
                " . $formData["breadcrumbs"]["form"] . "
            </div>
        </div>
    </div>", $content);
    $content = str_replace("{site_script_breadcrumbs}", $formData["breadcrumbs"]["script"], $content);
} else {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
    header("HTTP/1.0 404 Not Found");
}
