<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', false);
date_default_timezone_set("Europe/Kiev");
header('Content-Type: text/html; charset=utf-8');

require_once (RDD."/../vendor/autoload.php");

$catalogue = new CatalogueClass();

$dbm = DbSingleton::getDbm();

$list       = $catalogue->getPriceList(22);

print_r($list);