<?php

$linka = findLinks();

$mfa_link = $catalogue->getUrlString($linka[1]);
$mod_link = $catalogue->getUrlString($linka[2]);

$formData = $prod->getCarsForm($mfa_link, $mod_link);

$form   = $formData["form"];
$title  = $formData["title"];
$descr  = $formData["descr"];
$meta   = $formData["meta_tag"];

if ($formData["status"] == 1) {
    $content = str_replace("{main_window}", $form . $showform->getHistoryArts(), $content);
    if ($title != "") {
        $content = str_replace("{site_title}", $title, $content);
    }
    if ($descr != "") {
        $content = str_replace("{site_description}", $descr, $content);
    }
    $content = str_replace("{meta_social_tag}", $meta, $content);
} else {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
    header("HTTP/1.0 404 Not Found");
}





