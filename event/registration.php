<?php

if ($client->checkUnRegClient()) {
    $content = str_replace("{main_window}", $profile->showRegistrationForm(), $content);
} else {
    require_once("profile.php");
}