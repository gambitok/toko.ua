<?php

$linka = findLinks();
$mfa_link = $catalogue->getUrlString($linka[1]);
$mod_link = $catalogue->getUrlString($linka[2]);

list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);

$title = $automan->getCarsTitle($mfa_id, $model);

$form = $catalogue->getHtmlForm("cars/form");
$form = str_replace("{cars_title}", $title, $form);
$form = str_replace("{cars_list}", $prod->getCarsSearch($mfa_link, $mod_link), $form);
$form = str_replace("{cars_seo}", $automan->getCarsSeoContent($mfa_link, $mod_link), $form);

$content = str_replace("{main_window}", $form, $content);
$content = str_replace("{meta_social_tag}", $automan->getCarsMetaTags($mfa_id, $model, $title), $content);
