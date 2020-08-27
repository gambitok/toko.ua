<?php

if ($client->checkUnRegClient()) {
    $content = str_replace("{main_window}", $catalogue->getHtmlForm("profile/signin"), $content);
} else {
    header("Location: /profile", TRUE, 301);
}


