<?php

$start = microtime(true);

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');

define('RDD', __DIR__);
require_once (RDD."/../lib/DbSingleton.php");
require_once (RDD . "/../lib/Traits/Helper.php");
require_once (RDD . "/../lib/Traits/Variables.php");
require_once (RDD."/../lib/CatalogueClass.php");
require_once (RDD."/../lib/CatalogExistClass.php");
require_once (RDD."/../lib/ExRateClass.php");
require_once (RDD."/../lib/ClientClass.php");

$dbt = DbSingleton::getTokoDb();
$dbc = DbSingleton::getTokoCacheDb();
$catalog_exist = new CatalogExistClass();

$where = "1";
$where1 = "1";
//$where = "`GROUP_ID` = 206";
//$where1 = "`group_id` = 206";

$dbc->query("TRUNCATE TABLE `EX_TABLE_TREE_AVAILABLE_MFA`;");
$dbt->query("UPDATE `T2_TREE_GROUP_EXIST` SET `STATUS_CACHE` = 0 WHERE $where;");

print
"============\n
Done with full table \n
============\n";

$r = $dbt->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE $where;");
$n = $dbt->num_rows($r);
for ($i = 1; $i <= $n; $i++) {
    $group_id   = $dbt->result($r, $i - 1, "GROUP_ID");
    $group_link = $catalog_exist->getGroupRowLink($group_id);

    print $catalog_exist->initPartsTable($group_id);
    print $catalog_exist->initPartsMfaTable($group_id);
    print $catalog_exist->initPartsParamsTable($group_id);
    print
"============\n
Done with $group_link (GROUP_ID: $group_id) \n
============\n";
}

$dbc->query("TRUNCATE TABLE `EX_TABLE_TREE_AVAILABLE_GROUP`;");
$dbc->query("TRUNCATE TABLE `EX_TABLE_TREE_AVAILABLE_BRANDS`;");

$r = $dbt->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE $where;");
$n = $dbt->num_rows($r);
for ($i = 1; $i <= $n; $i++) {
    $group_id   = $dbt->result($r, $i - 1, "GROUP_ID");
    $catalog_exist->initPartsAvailableTables($group_id);
}

$dbc->query("TRUNCATE TABLE `EX_TABLE_AVAILABLE_MFA`;");
$dbc->query("INSERT INTO `EX_TABLE_AVAILABLE_MFA` (`group_id`, `mfa_id`, `model`)
SELECT `group_id`, `mfa_id`, `model` FROM `EX_TABLE_TREE_AVAILABLE_MFA` WHERE $where1 GROUP BY `group_id`, `mfa_id`, `model`;");

$time = microtime(true) - $start;

print
"TREE CACHE: \n 
Run time: $time";
