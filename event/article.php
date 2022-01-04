<?php

$linka = findLinks();

$article_search = $catalogue->getUrlString($linka[1]);
$brand          = $catalogue->getUrlString($linka[2]);
$art_id         = $catalogue->getUrlNumber($linka[3]);
$new_link       = $catalogue->getSiteLink() . "products/" . $article_search . "-" . $brand . "-" . $art_id . "/";

header("Location: $new_link", TRUE, 301);
