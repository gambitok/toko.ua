<?php

session_start();

$url = $_SERVER['HTTP_REFERER'];
$lang_id_prefer = $_GET['status'];

setcookie("lang_id_prefer", $lang_id_prefer, time()+3600);
if ((int)$lang_id_prefer === 1) {
    $_SESSION['lang_id'] = 2;
    setcookie("lang_id", 2, time()+3600);
}

header("Location: " . $url, TRUE, 301);