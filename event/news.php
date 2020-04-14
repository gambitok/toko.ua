<?php

$user_id=$catalogue->getUser(); $today=date("Y-m-d");
$dbm->query("UPDATE `A_CLIENTS_USERS` SET `update_news`='$today' WHERE `id`='$user_id' LIMIT 1;");

if (findLinks()[1]=="state") {
    $state_id=findLinks()[2];
	$content=str_replace("{main_window}", $menu->getNewsState($state_id), $content);
} else {
    $content=str_replace("{main_window}", $menu->showNews(), $content);
}
