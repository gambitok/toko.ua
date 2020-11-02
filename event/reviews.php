<?php

$link = $catalogue->getUrlString(findLinks()[1]);
if ($link == "") {
    $content = str_replace("{main_window}", $menu->showReviews(), $content);
} elseif ($link == "state") {
    $content = str_replace("{main_window}", $menu->getReviewsState(findLinks()[2]), $content);
}
