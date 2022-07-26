<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
header('Content-Type: text/html; charset=windows-1251');

define('RDD', __DIR__);
require_once (RDD . "/vendor/autoload.php");                  // init classes
require_once (RDD . "/lib/access.php");                       // get access site
require_once (RDD . "/js/JsHttpRequest/JsHttpRequest.php");   // ajax requests
require_once (RDD . "/out.php");



