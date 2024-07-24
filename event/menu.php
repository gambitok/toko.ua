<?php

global $content, $catalogue, $language, $profile, $menu, $client;

$client->getSalePointRetail();

$content = str_replace("{profile_info}", $profile->getProfileInfo(), $content);
$content = str_replace("{special_offers}", $profile->getSpecialOffers(), $content);
$content = str_replace("{news_info}", $profile->getNewsInfo(), $content);
$content = str_replace("{current_language}", $language->getLangCap($catalogue->getLanguage()), $content);
$content = str_replace("{menu_language}", $language->getLanguageMenuList($catalogue->getLanguage()), $content);
$content = str_replace("{garage_link}", $menu->getGarageLink(), $content);
$content = str_replace("{site_menu_bar}", $menu->getMenuBar(), $content);
$content = str_replace("{region_select}", "", $content);

if (!$profile->getProfileClientInfo()) {
    $content = str_replace("{login_info}", "", $content);
} else {
    $content = str_replace("{login_info}", $profile->getProfileClientInfo(), $content);
}





