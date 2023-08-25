<?php

session_start();

$url = 'https://toko.ua';
$set_lang_id = $_GET['lang_id'];

if (!empty($set_lang_id)) {
    $_SESSION['lang_id'] = $set_lang_id;
    setcookie("lang_id", $set_lang_id, time()+3600);
}

header("Location: " . $url, TRUE, 301);