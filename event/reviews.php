<?php

if (findLinks()[1]=="") {
    $content = str_replace("{main_window}", $menu->showReviews(), $content);
}
elseif (findLinks()[1]=="state") {
    $content = str_replace("{main_window}", $menu->getReviewsState(findLinks()[2]), $content);
}
