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
require_once (RDD . "/lib/catalogue_class.php");              //catalogue search
require_once (RDD . "/lib/search_class.php");                 //catalogue search
require_once (RDD . "/lib/form_class.php");                   //show forms
require_once (RDD . "/lib/products_class.php");               //products search
require_once (RDD . "/lib/auto_class.php");                   //auto search
require_once (RDD . "/lib/menu_class.php");                   //site`s menu
require_once (RDD . "/lib/shop_class.php");                   //market operations
require_once (RDD . "/lib/client_class.php");                 //user configuration
require_once (RDD . "/lib/profile_class.php");                //user configuration (profile form)
require_once (RDD . "/lib/lang_class.php");                   //multilingual
require_once (RDD . "/lib/exrate_class.php");                 //exchange rate
require_once (RDD . "/js/JsHttpRequest/JsHttpRequest.php");   //ajax requests
require_once (RDD . "/lib/class.phpmailer.php");
require_once (RDD . "/lib/catalog_exist_class.php");
require_once (RDD . "/lib/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php");

$db = DbSingleton::getTokoDb();
$dbm = DbSingleton::getDbm();
$dbc = DbSingleton::getTokoCacheDb();

$showform       = new FormClass();
$catalogue      = new CatalogueClass();
$search         = new SearchClass();
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



