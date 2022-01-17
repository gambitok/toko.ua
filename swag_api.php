<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');
define('RDD', dirname (__FILE__));

require(RDD . "/swagger-generator/vendor/autoload.php");
$generate = new \OpenApi\Generator();
$openapi = $generate->scan([RDD . "/swag_test"]);
echo $openapi->toYaml();

