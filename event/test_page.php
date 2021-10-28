<?php

//$form = $catalogue->getHtmlForm("article/thumbnail");
//
//$form = str_replace("{images_range}", $catalogue->getPhotoForm(100053510), $form);

//$result = $catalogue->testLinks();

//$form = $catalogue->getHtmlForm("article/shit");
//$dataPhoto = $catalogue->getSlideProPhoto(100002193);
//$form = str_replace("{images_slide}", $dataPhoto["slide"], $form);
//$form = str_replace("{images_thumbnail}", $dataPhoto["thumbnail"], $form);
//
//$form = str_replace("{images_range}", $catalogue->getSlideProPhoto(100053510), $form);

//$catalogue->initKeywords();

//$catalogue->initKeywords();

//$form = $catalogue->getSearchMatches2("фильтры воздушные");

$content = str_replace("{main_window}", "", $content);

$db = DbSingleton::getTokoDb();
$table = "test_table";
$file   = fopen("test.csv", 'r');
$header = false;
while (($line = fgetcsv($file)) !== FALSE) {
    if (!$header) {
        $header = $line;
        continue;
    }
    $writeLine = [];
    foreach ($line as $item) {
        $writeLine[] = "'{$item}'";
    }
    $writeLine = implode(',', $writeLine);
    $db->query("INSERT IGNORE INTO ". $table . " (". implode(',', $header) . ") VALUES (". $writeLine .")");
}
fclose($file);

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





