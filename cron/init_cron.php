<?php
$start = microtime(true);
define('RDD', dirname (__FILE__));
error_reporting(0);
@ini_set('display_errors', false);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');

require_once (RDD."/../lib/DbSingleton.php");
require_once (RDD."/../lib/helper.php");
require_once (RDD."/../lib/variables.php");
require_once (RDD."/../lib/mysql_class.php");
require_once (RDD."/../lib/catalogue_class.php");
require_once (RDD."/../lib/catalog_exist_class.php");
require_once (RDD."/../lib/class.phpmailer.php");

$dbt = DbSingleton::getTokoDb();
$catalog_exist = new CatalogExistClass();

$result = ""; $result_mfa = ""; $result_params = ""; $list = "";

