<?php

$link = $catalogue->getUrlString(findLinks()[1]);
if ($link == "") {
    $content = str_replace("{main_window}", $menu->showReviews(), $content);
} elseif ($link == "state") {
    $state_id = findLinks()[2];
    $content = str_replace("{main_window}", $menu->getReviewsState($state_id), $content);
    $content = str_replace("{meta_social_tag}", $menu->getReviewsMetaTags($state_id), $content);
}
