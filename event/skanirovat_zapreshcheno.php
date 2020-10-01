<?php

$phone = $_POST['phone'];

$phone = $client->formatValidPhone($phone);

if ($phone!="") {
    $client->validatePhone($phone);
    $content = str_replace("{main_window}", $menu->showScanPhoneForm($phone), $content);
    $_POST['phone'] = "";
} else {
    $content = str_replace("{main_window}", $menu->showScanForm(), $content);
}



