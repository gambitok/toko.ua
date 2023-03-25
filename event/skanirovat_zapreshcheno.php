<?php

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$phone = $client->formatValidPhone($_POST['bonus_phone']);

if ($phone === "") {
        $content = str_replace("{main_window}", $menu->getHtmlForm("bonus/scan"), $content);
} else {
    if ($client->checkRegistration($phone)) {
        $clientData = $client->getClientUserbyPhone($phone);
        $client_id  = $clientData["client_id"];

        if ($client->checkRetailClientCategory($client_id)) {
            if (!$client->checkClientBonus($client_id, 1)) {

                $client->validatePhone($phone, $ip, "google");
                $content = str_replace("{main_window}", $menu->showScanPhoneForm($phone), $content);
            } else {
                $content = str_replace("{main_window}", $menu->getHtmlForm("bonus/phone_bonus_error"), $content);
            }
        } else {
            $content = str_replace("{main_window}", $menu->getHtmlForm("bonus/phone_error"), $content);
        }
    } else {
        $client->validatePhone($phone, $ip, "google");
        $content = str_replace("{main_window}", $menu->showScanPhoneForm($phone), $content);
    }

    $_POST['bonus_phone'] = "";
}
