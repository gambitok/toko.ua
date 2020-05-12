<?php
error_reporting( E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
define('RD', dirname (__FILE__));
require_once (RD."/lib/DbSingleton.php");$db = DbSingleton::getTokoDb();
require_once (RD."/lib/slave_class.php");$slave = new slave;

$usr=$_REQUEST["usr"];$pwd=$_REQUEST["pwd"];
if ($usr=="import_info" && $pwd="kuyASEf8714rfqd"){
	$w=$_REQUEST["w"];
	if ($w=="nusr"){
		$login=$_REQUEST["login"];$email=urldecode(iconv("utf-8","windows-1251",$_REQUEST["email"]));$pass=urldecode(iconv("utf-8","windows-1251",$_REQUEST["pass"]));$name=$slave->qq(urldecode(iconv("utf-8","windows-1251",$_REQUEST["name"])));$city=$slave->qq(urldecode(iconv("utf-8","windows-1251",$_REQUEST["city"])));$phone=$slave->qq(urldecode(iconv("utf-8","windows-1251",$_REQUEST["phone"])));$price_lvl=$_REQUEST["price_lvl"];$rating=$_REQUEST["rating"];$email_order=$slave->qq(urldecode(iconv("utf-8","windows-1251",$_REQUEST["email_order"])));$branch=$_REQUEST["branch"];$price_eu=$_REQUEST["price_eu"];
		
		$r=$db->query("SELECT max(`id`) as mid FROM `clients`;");$client_id=$db->result($r,0,"mid")+1;$remip=$_SERVER['REMOTE_ADDR'];
		$db->query("INSERT INTO `clients` (`id`,`code`,`nip`,`login`,`email`,`pass`,`name`,`city`,`phone`,`price_lvl`,`rating`,`email_order`,`branch`,`branch_view`,`price_ue`,`remip`,`ison`) 
		VALUES ('$client_id','$code','$nip','$login','$email','$pass','$name','$city','$phone','$price_lvl','$rating','$email_order','$branch','','$price_eu','$remip','1');");

		print "import client ok";
	}
	if ($w=="rest"){
		$branch=$_REQUEST["branch"];$file=$_REQUEST["file"];
		$db->query("INSERT INTO `cron_api` (`branch`,`file_name`,`type`) VALUES ('$branch','$file','1');");
		print "import rest for branch:$branch added to cron. Wait";
	}
	if ($w=="price"){
		$branch=$_REQUEST["branch"];$file=$_REQUEST["file"];
		$db->query("INSERT INTO `cron_api` (`branch`,`file_name`,`type`) VALUES ('$branch','$file','1');");

		print "import price for branch:$branch ok";
	}
}

?>