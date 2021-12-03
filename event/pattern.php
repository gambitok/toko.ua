<?php

$link = $catalogue->getUrlString(findLinks()[0]);
$result = explode($link . "/", $_SERVER["REQUEST_URI"], 2);
$link = ltrim($result[1]);
header("Location: /catalog/$link", TRUE, 301);

