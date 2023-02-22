<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', false);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');

require_once (RDD."/../vendor/autoload.php");

$dbt = DbSingleton::getTokoDb();
$dbc = DbSingleton::getTokoCacheDb();
$catalog_exist = new CatalogExistClass();

$start = microtime(true);

$group_id = (int)$_GET["group_id"];

$where = "`GROUP_ID` = $group_id";
$where1 = "`group_id` = $group_id";

if ($group_id > 0) {

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

//$dbc->query("TRUNCATE TABLE `EX_TABLE_TREE_AVAILABLE_GROUP`;");
//$dbc->query("TRUNCATE TABLE `EX_TABLE_TREE_AVAILABLE_BRANDS`;");

$r = $dbt->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE $where;");
$n = $dbt->num_rows($r);
for ($i = 1; $i <= $n; $i++) {
    $group_id   = $dbt->result($r, $i - 1, "GROUP_ID");
    $catalog_exist->initPartsAvailableTables($group_id, 1);
}

//$dbc->query("TRUNCATE TABLE `EX_TABLE_AVAILABLE_MFA`;");
$dbc->query("DELETE FROM `EX_TABLE_AVAILABLE_MFA` WHERE $where1 ;");
$dbc->query("INSERT INTO `EX_TABLE_AVAILABLE_MFA` (`group_id`, `mfa_id`, `model`)
SELECT `group_id`, `mfa_id`, `model` FROM `EX_TABLE_TREE_AVAILABLE_MFA` WHERE $where1 GROUP BY `group_id`, `mfa_id`, `model`;");

$time = microtime(true) - $start;

print
    "TREE CACHE: \n 
Run time: $time";
}