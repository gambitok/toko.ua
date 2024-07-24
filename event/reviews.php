<?php

global $content, $catalogue, $menu, $formObj;

$red_status = 0;
$red_type   = 0;
$red_link   = "";

$link = $catalogue->getUrlString(findLinks()[1]);
$title = $description = "";
$state_id = findLinks()[2];
$state_name = findLinks()[3];

if ($link === "") {
    $content = str_replace("{main_window}", $menu->showReviews() . $formObj->getHistoryArts(), $content);
    $title = $catalogue->replaceLang("{site_reviews}");
    $title = str_replace("{h1_text}", "{review_state_cap}", $title);
}
elseif ($link === "state") {

    $true_state_name = $menu->getReviewsData($state_id)["transcript"];
    if (!empty($state_id) && $state_name !== $true_state_name) {
        header("Location: " . $menu->getReviewsData($state_id)["url"], TRUE, 301);
    }

    $content = str_replace("{main_window}", $menu->getReviewsState($state_id) . $formObj->getHistoryArts(), $content);
    $content = str_replace("{meta_social_tag}", $menu->getReviewsMetaTags($state_id), $content);
    $dataReview = $menu->getReviewsData($state_id);
    $title = $catalogue->replaceLang("{site_reviews}");
    $title = str_replace("{h1_text}", ($dataReview["site_title"] === "") ? $dataReview["title"] : $dataReview["site_title"], $title);
    $description = $dataReview["site_description"];
}
else {
    $red_status = 1;
    $red_type   = 404;
    $content    = str_replace("{main_window}", $catalogue->getHtmlForm("error/404_catalog"), $content);
}

$content = str_replace("{site_title}", $title, $content);
$content = str_replace("{site_description}", $description, $content);
$content = str_replace("{meta_social_tag}", $menu->getReviewsMetaTags($state_id), $content);