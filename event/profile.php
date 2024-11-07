<?php

global $content, $client, $profile, $catalogue;

if ($client->checkUnRegClient()) {
    header("Location: /signin", TRUE, 301);
} else {
    $content = str_replace("{main_window}", $profile->showProfileForm(), $content);
    $content = str_replace("{profile_account}", $profile->showProfileAccount(), $content);
    $content = str_replace("{profile_check}", $profile->showProfileCheckForm(), $content);
    $content = str_replace("{profile_orders}", $profile->showProfileOrders(), $content);
    $content = str_replace("{profile_file_list}", $profile->showPriceList(), $content);

    $panel = $catalogue->getUrlString(findLinks()[1]);
    $panel = ($panel === "") ? "account" : $panel;

    $content = str_replace("{profile-$panel}", "in active", $content);

    if ($client->getClientPriceList()) {
        $content = str_replace("{price_visible}", "block", $content);
    } else {
        $content = str_replace("{price_visible}", "none", $content);
    }

    if ($client->getClientCheckList()) {
        $content = str_replace("{check_visible}", "block", $content);
    } else {
        $content = str_replace("{check_visible}", "none", $content);
    }

    $content = str_replace(array("{date_saldo_from}", "{date_saldo_to}"), array(date("Y-m-01"), date("Y-m-d")), $content);
}

$content = str_replace("{meta_noindex}", $catalogue->getHtmlForm("seo/noindex"), $content);




