<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
header('Content-Type: text/html; charset=windows-1251');

$content = null;
$title = "{site_title}";

define('RDD', __DIR__);
require_once (RDD . "/lib/DbSingleton.php");                  //database
require_once (RDD . "/lib/access.php");                       //get access site
require_once (RDD . "/lib/Traits/Helper.php");
require_once (RDD . "/lib/Traits/Variables.php");
require_once (RDD . "/lib/CatalogueClass.php");              //catalogue search
require_once (RDD . "/lib/FormClass.php");                   //show forms
require_once (RDD . "/lib/ProductsClass.php");               //products search
require_once (RDD . "/lib/AutoClass.php");                   //auto search
require_once (RDD . "/lib/MenuClass.php");                   //site`s menu
require_once (RDD . "/lib/ShopClass.php");                   //market operations
require_once (RDD . "/lib/ClientClass.php");                 //user configuration
require_once (RDD . "/lib/ProfileClass.php");                //user configuration (profile form)
require_once (RDD . "/lib/LangClass.php");                   //multilingual
require_once (RDD . "/lib/ExRateClass.php");                 //exchange rate
require_once (RDD . "/js/JsHttpRequest/JsHttpRequest.php");   //ajax requests
require_once (RDD . "/lib/CatalogExistClass.php");
require_once (RDD . "/lib/Plugins/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php");

$db = DbSingleton::getTokoDb();
$dbm = DbSingleton::getDbm();
$dbc = DbSingleton::getTokoCacheDb();

$showform       = new FormClass();
$catalogue      = new CatalogueClass();
$prod           = new ProductsClass();
$automan        = new AutoClass();
$menu           = new MenuClass();
$shop           = new ShopClass();
$client         = new ClientClass();
$kours          = new ExRateClass();
$profile        = new ProfileClass();
$language       = new LangClass();
$catalog_exist  = new CatalogExistClass();

//set cookies for user
setCookies();

//comment this for access all ips
//if (in_array($_SERVER['REMOTE_ADDR'], getAccess()))
require_once (RDD . "/out.php");



