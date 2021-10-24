<?php

$user_id = $catalogue->getUser();

if ($user_id == 0) {
    header("Location: /signin", TRUE, 301);
} else {
    $today = date("Y-m-d");
    $r = $dbm->query("SELECT `update_actions` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
    $update_actions = $dbm->result($r, 0, "update_actions");
    $content = str_replace("{main_window}", $menu->showSpecialOffers($update_actions) . $showform->getHistoryArts(), $content);
    $dbm->query("UPDATE `A_CLIENTS_USERS` SET `update_actions`='$today' WHERE `id` = $user_id LIMIT 1;");
}


