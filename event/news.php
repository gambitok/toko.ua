<?php

$user_id = $catalogue->getUser();
$today = date("Y-m-d");
$dbm->query("UPDATE `A_CLIENTS_USERS` SET `update_news` = '$today' WHERE `id` = $user_id LIMIT 1;");

$link = $catalogue->getUrlString(findLinks()[1]);
$title = "";

if (findLinks()[3] == "grafС–k-roboti-na-novorС–chnС–-svyata" || findLinks()[3] == "graf%D0%A1%E2%80%93k-roboti-na-novor%D0%A1%E2%80%93chn%D0%A1%E2%80%93-svyata") {
    $link = $catalogue->getSiteLink() . $catalogue->news_link . "/state/" . findLinks()[2] . "/grafik-roboti-na-novorichni-svyata/";
    header("Location: $link", TRUE, 301);
}

if ($link == "") {
    $content = str_replace("{main_window}", $menu->showNews() . $showform->getHistoryArts(), $content);

    $title = $catalogue->replaceLang("{site_news}");
    $title = str_replace("{h1_text}", "{news_cap}", $title);
}
elseif ($link == "state") {
    $state_id = findLinks()[2];

	$content = str_replace("{main_window}", $menu->showNewsState($state_id) . $showform->getHistoryArts(), $content);
    $content = str_replace("{meta_social_tag}", $menu->getNewsMetaTags($state_id), $content);

    $title = $catalogue->replaceLang("{site_news}");
    $title = str_replace("{h1_text}", $menu->getNewsData($state_id)["title"], $title);
}
$content = str_replace("{site_title}", $title, $content);
$content = str_replace("{site_description}", "", $content);
