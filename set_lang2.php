<?php

session_start();

$url = $_POST['set_lang_url'];
$set_lang_id = $_POST['set_lang_id'];

if (!empty($set_lang_id)) {
    $_SESSION['lang_id'] = $set_lang_id;
    setcookie("lang_id", $set_lang_id, time()+3600);
}

header("Location: " . $url, TRUE, 301);