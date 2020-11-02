<?php

$linka = findLinks();
$type_id = $catalogue->getUrlNumber($linka[1]);
$user_id = $catalogue->getUrlNumber($linka[2]);

if ($_SESSION["user"] == 0) {
    require_once("profile.php");
} elseif ($_SESSION["user"] == $user_id) {
    if ($type_id == 1) {
        $dbm->query("UPDATE `A_CLIENTS_USERS` SET `price_status`=0 WHERE `id`='$user_id';");
    }
    if ($type_id == 2) {
        $dbm->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `price_status`=0 WHERE `id`='$user_id';");
    }
    $content = str_replace("{main_window}", "<div class=\"content\">{done_cap}</div>", $content);
} else {
    $content = str_replace("{main_window}", "<div class=\"content\">{first_change_user}</div>", $content);
}
