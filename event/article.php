<?php

$linka = findLinks();
$article_search = $linka[1];
$brand_name = $linka[2];
$art_id = $linka[3];

if (!is_numeric($art_id)) {
    $new_art_id = preg_replace('/[^0-9]+/', '', $art_id);
    header("Location: /article/$article_search/$brand_name/$new_art_id/", TRUE, 301);
} else {
    $content = str_replace("{main_window}", $showform->showArticle($art_id), $content);
}

