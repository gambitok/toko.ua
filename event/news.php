<?php

$user_id = $catalogue->getUser(); $today = date("Y-m-d");
$dbm->query("UPDATE `A_CLIENTS_USERS` SET `update_news`='$today' WHERE `id`='$user_id' LIMIT 1;");

if (findLinks()[1] == "") {
    $content = str_replace("{main_window}", $menu->showNews(), $content);
} elseif (findLinks()[1] == "state") {
	$content = str_replace("{main_window}", $menu->getNewsState(findLinks()[2]), $content);
}
