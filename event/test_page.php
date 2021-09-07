<?php

//$form = $catalogue->getHtmlForm("article/thumbnail");
//
//$form = str_replace("{images_range}", $catalogue->getPhotoForm(100053510), $form);

//$result = $catalogue->testLinks();

$form = $catalogue->getHtmlForm("article/shit");
$dataPhoto = $catalogue->getSlideProPhoto(100002193);
$form = str_replace("{images_slide}", $dataPhoto["slide"], $form);
$form = str_replace("{images_thumbnail}", $dataPhoto["thumbnail"], $form);

$form = str_replace("{images_range}", $catalogue->getSlideProPhoto(100053510), $form);

$content = str_replace("{main_window}", $form, $content);

//$r = $dbm->query("SELECT `id`, `name` FROM `A_CLIENTS` WHERE 1 LIMIT 10;");
//$n = $dbm->num_rows($r);
//
//for ($i = 1; $i <= $n; $i++) {
//    $id = $dbm->result($r, $i - 1, "id");
//    $result[$id][$i] = mysqli_fetch_assoc($r);
//}
//var_dump($result);
//
//$content = str_replace("{main_window}", "", $content);





