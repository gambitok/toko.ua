<?php

if ($client->checkUnRegClient()) {
    $content = str_replace("{main_window}", $profile->showRegistrationForm(), $content);
} else {
    require_once("profile.php");
}

$content = str_replace("{meta_noindex}", '
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta name="yandex" content="noindex">
', $content);