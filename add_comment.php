<?php

const RDD = __DIR__;
date_default_timezone_set("Europe/Kiev");

require_once (RDD . "/vendor/autoload.php");

$catalogue = new CatalogueClass();

$comment = $_POST['comment'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$art_id = $_POST['art_id'] ?? '';

$catalogue->addArtReview($user_id, $art_id, $comment);