<?php

$user_id = $catalogue->getUser(); $today = date("Y-m-d");
$dbm->query("UPDATE `A_CLIENTS_USERS` SET `update_news`='$today' WHERE `id`='$user_id' LIMIT 1;");

$link = $catalogue->getUrlString(findLinks()[1]);

if ($link == "") {
    $content = str_replace("{main_window}", $menu->showNews(), $content);
} elseif ($link == "state") {
    $state_id = findLinks()[2];
	$content = str_replace("{main_window}", $menu->showNewsState($state_id), $content);
    $content = str_replace("{meta_social_tag}", $menu->getNewsMetaTags($state_id), $content);
}
