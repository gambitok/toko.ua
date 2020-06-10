<?php

$client->setTpointRetail();

$content=str_replace("{profile_info}", $profile->getProfileInfo(), $content);
$content=str_replace("{special_offers}", $profile->getSpecialOffers(), $content);
$content=str_replace("{news_info}", $profile->getNewsInfo(), $content);

$content=str_replace("{details_info}", $catalogue->getDetailsList(), $content);

$content=str_replace("{current_language}",$language->getLangCap($language->getLanguage()), $content);
$content=str_replace("{language_dropdown}",$language->getLanguageSelectList($language->getLanguage()), $content);
$content=str_replace("{garage_link}", $menu->getGarageLink(), $content);
$content=str_replace("{site_mobile_panel}", $menu->getMediaNavPanel(), $content);

if (!$profile->getClientInfo()) {
    $content=str_replace("{region_select}", $menu->getRegionSelect(), $content);
    $content=str_replace("{login_info}", "", $content);
} else {
    $content=str_replace("{region_select}", "", $content);
    $content=str_replace("{login_info}", $profile->getClientInfo(), $content);
}





