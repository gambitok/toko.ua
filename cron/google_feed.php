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

$r = $dbt->query("SELECT * FROM `site_info` LIMIT 1;");
$n = $dbt->num_rows($r);
$siteTitle = $siteDescription = "";
for ($i = 1; $i <= $n; $i++) {
    $siteTitle = $dbt->result($r, $i - 1, "site_name");
    $siteDescription = $dbt->result($r, $i - 1, "site_description");
}

$data = $catalog_exist->getCatalogCron();

if (file_exists(RDD . '/../google_feed.xml')) {
    unlink(RDD . '/../google_feed.xml');
}

$xml = new SimpleXMLElement('<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"></rss>');
$xml->addChild('channel');
$xml->channel->addChild('title', $siteTitle);
$xml->channel->addChild('link', 'https://toko.ua/');
$xml->channel->addChild('description', $siteDescription);

foreach ($data as $itemData) {
    $item = $xml->channel->addChild('item');
    $item->addChild('g:g:id', $itemData['id']);
    $item->addChild('g:g:title', $itemData['title']);
    $item->addChild('g:g:description', $itemData['description']);
    $item->addChild('g:g:availability', $itemData['availability']);
    $item->addChild('g:g:link', $itemData['link']);
    $item->addChild('g:g:image_link', $itemData['image_link']);
    $item->addChild('g:g:price', $itemData['price']);
    $item->addChild('g:g:identifier_exists', $itemData['identifier_exists']);
    $item->addChild('g:g:gtin', $itemData['gtin']);
    $item->addChild('g:g:mpn', $itemData['mpn']);
    $item->addChild('g:g:brand', $itemData['brand']);
}

$xml->asXML(RDD . '/../google_feed.xml');

$time = microtime(true) - $start;

print
    "GOOGLE FEED: \n 
Run time: $time";