<?php

$red_status = 0;
$red_type   = 0;
$red_link   = "";

if ($client->checkUnRegClient()) {
    if (count(findLinks()) > 1) {
        $red_status = 1;
        $red_type   = 404;
        $content    = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
    }
    $content = str_replace("{main_window}", $profile->showRegistrationForm(), $content);
} else {
    require_once("profile.php");
}

$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta name="yandex" content="noindex">
', $content);

if ($red_status) {
    if ($red_type === 404) {
        header("HTTP/1.0 404 Not Found");
    }
    if ($red_type === 301) {
        header("Location: $red_link", TRUE, 301);
    }
}
