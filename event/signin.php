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
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("profile/signin"), $content);
} else {
    header("Location: /profile", TRUE, 301);
}

if ($red_status) {
    if ($red_type === 404) {
        header("HTTP/1.0 404 Not Found");
    }
    if ($red_type === 301) {
        header("Location: $red_link", TRUE, 301);
    }
}



