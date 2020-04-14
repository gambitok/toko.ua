<?php

if ($client->checkUnRegClient()) {
    $content = str_replace("{main_window}", $menu->getHtmlForm("profile/recover_password"), $content);
} else {
    require_once("profile.php");
}
