<?php
error_reporting(0);
@ini_set('display_errors', false);
define('RD', dirname (__FILE__));
date_default_timezone_set("Europe/Kiev");
require_once (RD."/lib/DbSingleton.php");
$db=DbSingleton::getDbm();

if (!empty($_FILES)) {
    $cookie_id=$_COOKIE["session_id"];
    $targetDir = RD."/uploads/suppliers/";
    $fileName = $_FILES['file']['name'];
    $real_file_name = iconv("utf-8","windows-1251",$fileName);
    $fileName = "$cookie_id.".pathinfo($fileName, PATHINFO_EXTENSION);
    $targetFile = $targetDir.$fileName;
    $size = filesize($targetFile);
    $info = new SplFileInfo($targetFile);
    $type = $info->getExtension();
    if (move_uploaded_file($_FILES['file']['tmp_name'],$targetFile)) {
        $db->query("INSERT INTO `J_SUPPLIERS_COOPERATION_FILES` (`file_name`,`real_file_name`,`cookie_id`,`size`,`type`) 
        VALUES ('$fileName','$real_file_name','$cookie_id','$size','$type');");
    }
}
