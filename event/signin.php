<?php

if ($client->checkUnRegClient()) {
   // require_once("profile.php");
    $content=str_replace("{main_window}", $catalogue->getHtmlForm("profile/signin"), $content);
} else {
    header("Location: /profile", TRUE, 301);
}


