<?php

error_reporting(0);
@ini_set('display_errors', false);
define('RD', __DIR__);
date_default_timezone_set("Europe/Kiev");

require_once (RD . "/vendor/autoload.php");

$db = DbSingleton::getDbm();
$catalogue = new CatalogueClass();

if (!empty($_FILES)) {
    $cookie_id  = $catalogue->getSessionID();
    $targetDir  = RD . "/uploads/suppliers/";
    $fileName   = $_FILES['file']['name'];
    $realFile   = $catalogue->getIconv($fileName);
    $fileName   = "$cookie_id." . pathinfo($fileName, PATHINFO_EXTENSION);
    $targFile   = $targetDir . $fileName;
    $size       = filesize($targFile);
    $info       = new SplFileInfo($targFile);
    $type       = $info->getExtension();

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targFile)) {
        $db->query("INSERT INTO `J_SUPPLIERS_COOPERATION_FILES` (`file_name`,`real_file_name`,`cookie_id`,`size`,`type`) 
        VALUES ('$fileName','$realFile','$cookie_id','$size','$type');");
    }
}
