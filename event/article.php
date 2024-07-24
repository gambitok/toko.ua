<?php

global $catalogue;

$httpHost = findLinks();

$article_search = $catalogue->getUrlString($httpHost[1]);
$brand          = $catalogue->getUrlString($httpHost[2]);
$art_id         = $catalogue->getUrlNumber($httpHost[3]);
$new_link       = $catalogue->getSiteLink() . "products/" . $article_search . "-" . $brand . "-" . $art_id . "/";

header("Location: $new_link", TRUE, 301);
