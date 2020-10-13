<?php

$bonus = 1;

$phone = $_POST['bonus_phone'];

$phone = $client->formatValidPhone($phone);

if ($phone=="") {
        $content = str_replace("{main_window}", $menu->showScanForm(), $content);
} else {

    // check if reg//
    if ($client->checkRegistration($phone)) {
        $clientData = $client->getClientUserbyPhone($phone);
        $client_id = $clientData["client_id"];
        // check if roznica
        if ($client->checkRetailClientCategory($client_id)) {
            if (!$client->checkClientBonus($client_id, $bonus)) {
                // ALL OK
                $client->validatePhone($phone);
                $content = str_replace("{main_window}", $menu->showScanPhoneForm($phone), $content);
            } else {
                $content = str_replace("{main_window}", $menu->getHtmlForm("bonus/phone_bonus_error"), $content);
            }
        } else {
            $content = str_replace("{main_window}", $menu->getHtmlForm("bonus/phone_error"), $content);
        }
    } else {
        $client->validatePhone($phone);
        $content = str_replace("{main_window}", $menu->showScanPhoneForm($phone), $content);
    }

    $_POST['bonus_phone'] = "";
}
