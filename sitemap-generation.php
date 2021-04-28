<?php

$start = microtime(true);
define('RDD', dirname (__FILE__));
error_reporting(0); @ini_set('display_errors', false);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");

require_once (RDD . "/lib/DbSingleton.php");
require_once (RDD . "/lib/mysql_class.php");

$db = DbSingleton::getTokoDb();
$xmlWriter = new XMLWriter();
$xmlWriter->openMemory();
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

$col = 0; $doc_nom = 0;

$r2 = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_GROUP_EXIST` WHERE 1;");
$n2 = $db->num_rows($r2);
for ($j = 1; $j <= $n2; $j++) {
    $tex_link = $db->result($r2, $j - 1, "TEX_LINK");

    $r1 = $db->query("SELECT `MFA_ID`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE`=1 ORDER BY `MFA_ID`;");
    $n1 = $db->num_rows($r1);
    for ($l = 1; $l <= $n1; $l++) {
        $mfa_id = $db->result($r1, $l - 1, "MFA_ID");
        $mfa_link = $db->result($r1, $l - 1, "MFA_BRAND_LINK");

        $xmlWriter->setIndent(2);
        $xmlWriter->startElement('url');
        $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/$mfa_link/");
        $xmlWriter->writeElement('changefreq', 'weekly');
        $xmlWriter->writeElement('priority', '0.9');
        $xmlWriter->endElement();
        $col++;

        if (($col % 15000) == 0) {
            $xmlWriter->endElement();
            $doc_nom++;
            file_put_contents("sitemap-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
            $xmlWriter->startElement('urlset');
            $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
            $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
            $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
        }

        $r = $db->query("SELECT `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model` ORDER BY `Model`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $model_link = $db->result($r, $i - 1, "Model_Link");

            $xmlWriter->setIndent(2);
            $xmlWriter->startElement('url');
            $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/$mfa_link/$model_link/");
            $xmlWriter->writeElement('changefreq', 'weekly');
            $xmlWriter->writeElement('priority', '0.9');
            $xmlWriter->endElement();
            $col++;

            if (($col % 15000) == 0) {
                $xmlWriter->endElement();
                $doc_nom++;
                file_put_contents("sitemap-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
                $xmlWriter->startElement('urlset');
                $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
                $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
                $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
            }
        }
    }
}

$xmlWriter->endElement();

$doc_nom++;
file_put_contents("sitemap-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);

$time = microtime(true) - $start;

print "RUN TIME: " . $time;
