<?php

if ($client->checkUnRegClient()) {
    header("Location: /signin", TRUE, 301);
} else {
    $content = str_replace("{main_window}", $profile->showProfileForm(), $content);
    $content = str_replace("{profile_account}", $profile->showProfileAccount(), $content);
    $content = str_replace("{profile_check}", $profile->showProfileCheck(), $content);
    $content = str_replace("{profile_orders}", $profile->showProfileOrders(), $content);
    $content = str_replace("{profile_file_list}", $profile->showPriceList(), $content);

    $linksData = findLinks();
    $panel = $catalogue->getUrlString($linksData[1]);
    if ($panel == "") {
        $panel = "account";
    }
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

    $content = str_replace("{date_saldo_from}", date("Y-m-01"), $content);
    $content = str_replace("{date_saldo_to}", date("Y-m-d"), $content);
}




