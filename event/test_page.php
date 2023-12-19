<?php
//$form = $catalogue->getHtmlForm("test_page");
//$form = str_replace("{test_page_content}", $showform->getTestPageContent(), $form);
//
//$content = str_replace("{main_window}", $form, $content);
//error_reporting(E_ERROR);
//@ini_set('display_errors', true);
//@ini_set('html_errors', false);
//define('RDD', dirname(__FILE__));
//header('Content-Type: text/html; charset=windows-1251');
//date_default_timezone_set("Europe/Kiev");
//ini_set('memory_limit', '4096M');
//
//
//$str = "asd";
//
//$code = mb_detect_encoding($str);
//$str = trim(iconv($code, "Windows-1251", $str));
//$str = iconv("Windows-1251", "UTF-8", $str);
//var_dump(mb_detect_encoding($str));
//var_dump($str);

$dbe = DbSingleton::getTokoEmojiDb();

$r = $dbe->query("SELECT * FROM `SEO_TEXT_EMOJI` WHERE 1;");
$text = $dbe->result($r, 0, "TEXT");
$text = iconv("Windows-1251", "UTF-8", $text);
var_dump($text);