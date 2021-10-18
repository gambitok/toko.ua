<?php

// PROFILE CATALOGS

$linka = findLinks();

//$path_from = $linka[0] . "/" . $linka[1] . "/";
//if ($catalogue->getCatalogRedirectLink($path_from)["status"]) {
//    $path_to = $catalogue->getCatalogRedirectLink($path_from)["redirect_link"];
//    header("Location: $path_to", TRUE, 301);
//}

$art_id = intval(substr($linka[1], strrpos($linka[1], "-") + 1));

var_dump($art_id);
