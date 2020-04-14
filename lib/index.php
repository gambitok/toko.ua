<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);@ini_set('display_errors', true);
//error_reporting(E_ALL);
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

header('Content-Type: text/html; charset=windows-1251');
$content=null; $title="{site_title}";
define('RDD', dirname (__FILE__));

require_once (RDD."/lib/DbSingleton.php");
require_once (RDD."/lib/access.php");                       //get access
require_once (RDD."/lib/helper.php");                       //helper
require_once (RDD."/lib/variables.php");                    //variables
require_once (RDD."/lib/form_class.php");                   //show forms class
require_once (RDD."/lib/catalogue_class.php");              //catalogue search
require_once (RDD."/lib/auto_class.php");                   //auto search
require_once (RDD."/lib/menu_class.php");                   //site`s menu
require_once (RDD."/lib/shop_class.php");                   //market operations
require_once (RDD."/lib/client_class.php");                 //user configuration
require_once (RDD."/lib/profile_class.php");                 //user configuration
require_once (RDD."/lib/lang_class.php");                   //language
require_once (RDD."/lib/exrate_class.php");                 //exchange rate
require_once (RDD."/js/JsHttpRequest/JsHttpRequest.php");   //ajax requests
require_once (RDD."/lib/class.phpmailer.php");

$db=DbSingleton::getTokoDb(); $dbm=DbSingleton::getDbm(); $language=new LangClass;  $menu=new MenuClass;  $client=new ClientClass; $showform=new FormClass; $automan=new AutoClass; $catalogue=new CatalogueClass; $shop=new ShopClass; $profile=new ProfileClass;

//set cookies for user
setCookies();

//comment this for access all ips
//if (in_array($_SERVER['REMOTE_ADDR'], getAccess()))
require_once (RDD."/out.php");


