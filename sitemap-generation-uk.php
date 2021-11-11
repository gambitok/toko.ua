<?php

$start = microtime(true);

define('RDD', dirname (__FILE__));
error_reporting(0); @ini_set('display_errors', false);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");

require_once (RDD . "/lib/DbSingleton.php");
require_once (RDD . "/lib/mysql_class.php");
require_once (RDD . "/lib/helper.php");
require_once (RDD . "/lib/variables.php");
require_once (RDD . "/lib/catalogue_class.php");
require_once (RDD . "/lib/catalog_exist_class.php");

$link = "https://toko.ua/uk/";

$db = DbSingleton::getTokoDb();
$dbc = DbSingleton::getTokoCacheDb();
$catalog = new CatalogueClass();
$catalog_exist = new CatalogExistClass();

$xmlWriter = new XMLWriter();
$xmlWriter->openMemory();
$max_tags_count = 15000;
$doc_nom = 0;
$doc_nom_params = 0;

$mask = RDD . "/uk/sitemap-manufactures-*.*";
array_map('unlink', glob($mask));
$mask = RDD . "/uk/sitemap-manufactures-params-*.*";
array_map('unlink', glob($mask));
unlink(RDD . "/uk/sitemap.xml");
unlink(RDD . "/uk/sitemap-pages.xml");
unlink(RDD . "/uk/sitemap-cars.xml");
unlink(RDD . "/uk/sitemap-categories.xml");
unlink(RDD . "/uk/sitemap-categories-params.xml");

/*
 * INIT `sitemap-manufactures`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

$col = 0;

$ggroups = [];
$r3 = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE `status` = 1 GROUP BY `group_id`;");
$n3 = $dbc->num_rows($r3);
for ($j = 1; $j <= $n3; $j++) {
    $group_id = intval($db->result($r3, $j - 1, "group_id"));
    $tex_link = $catalog->getGroupRowLink($group_id);

    if ($group_id > 0) {
        if ($catalog_exist->checkTable($group_id)) {
            $ggroups[] = $group_id;
        }
    }

    if ($catalog_exist->checkTableMfa($group_id) > 0) {

        $r1 = $db->query("SELECT `MFA_ID`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_ID` ASC;");
        $n1 = $db->num_rows($r1);
        for ($l = 1; $l <= $n1; $l++) {
            $mfa_id = $db->result($r1, $l - 1, "MFA_ID") + 0;
            $mfa_link = $db->result($r1, $l - 1, "MFA_BRAND_LINK");

            $xmlWriter->setIndent(2);
            $xmlWriter->startElement('url');
            $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/auto/$mfa_link/");
            $xmlWriter->writeElement('changefreq', 'weekly');
            $xmlWriter->writeElement('priority', '0.9');
            $xmlWriter->endElement();
            $col++;

            if (($col % $max_tags_count) == 0) {
                $xmlWriter->endElement();
                $doc_nom++;
                file_put_contents(RDD . "/uk/sitemap-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
                $xmlWriter->startElement('urlset');
                $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
                $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
                $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
            }

            $r = $db->query("SELECT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 GROUP BY `Model` ORDER BY `Model` ASC;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model = $db->result($r, $i - 1, "Model");
                $model_link = $db->result($r, $i - 1, "Model_Link");

                $rrrr = $dbc->query("SELECT `id` FROM `EX_TABLE_TREE_MFA_$group_id` WHERE `mfa_id` = $mfa_id AND `model` = '$model' LIMIT 1;");
                $nnnn = $dbc->num_rows($rrrr);
                if ($nnnn > 0) {
                    $xmlWriter->setIndent(2);
                    $xmlWriter->startElement('url');
                    $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/auto/$mfa_link/$model_link/");
                    $xmlWriter->writeElement('changefreq', 'weekly');
                    $xmlWriter->writeElement('priority', '0.9');
                    $xmlWriter->endElement();
                    $col++;

                    if (($col % $max_tags_count) == 0) {
                        $xmlWriter->endElement();
                        $doc_nom++;
                        file_put_contents(RDD . "/uk/sitemap-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
                        $xmlWriter->startElement('urlset');
                        $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
                        $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
                        $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
                    }
                }

            }
        }
    }
}

$xmlWriter->endElement();
$doc_nom++;
file_put_contents(RDD . "/uk/sitemap-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

/*
 * INIT `sitemap-categories`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

foreach ($ggroups as $group_id) {
    $tex_link = $catalog->getGroupRowLink($group_id);
    $xmlWriter->setIndent(2);
    $xmlWriter->startElement('url');
    $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/");
    $xmlWriter->writeElement('changefreq', 'weekly');
    $xmlWriter->writeElement('priority', '1');
    $xmlWriter->endElement();
}

$xmlWriter->endElement();
file_put_contents(RDD . "/uk/sitemap-categories.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

/*
 * INIT `sitemap-categories-params`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

foreach ($ggroups as $group_id) {
    $tex_link = $catalog->getGroupRowLink($group_id);
    $r3 = $db->query("SELECT tv.`VALUE_ID`, tv.`PARAM_ID`, tv.`VALUE_LINK`, tp.`PARAM_LINK`
    FROM `T2_TREE_VALUE_EXIST` tv
        LEFT JOIN `T2_TREE_PARAMS_EXIST` tp ON (tp.`PARAM_ID` = tv.`PARAM_ID`)
    WHERE tv.`GROUP_ID` = $group_id AND tv.`SITEMAP_STATUS` = 1;");
    $n3 = $db->num_rows($r3);
    for ($l = 1; $l <= $n3; $l++) {
        $value_id = $db->result($r3, $l - 1, "VALUE_ID");
        $value_link = $db->result($r3, $l - 1, "VALUE_LINK");
        $param_id = $db->result($r3, $l - 1, "PARAM_ID");
        $param_link = $db->result($r3, $l - 1, "PARAM_LINK");
        $xmlWriter->setIndent(2);
        $xmlWriter->startElement('url');
        $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/$param_link=$value_link/");
        $xmlWriter->writeElement('changefreq', 'weekly');
        $xmlWriter->writeElement('priority', '1');
        $xmlWriter->endElement();
    }
}

$xmlWriter->endElement();
file_put_contents(RDD . "/uk/sitemap-categories-params.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

/*
 * INIT `sitemap-categories-params-manufactures`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

$col = 0;

foreach ($ggroups as $group_id) {
    $tex_link = $catalog->getGroupRowLink($group_id);

    $r3 = $db->query("SELECT tv.`VALUE_ID`, tv.`PARAM_ID`, tv.`VALUE_LINK`, tp.`PARAM_LINK`
    FROM `T2_TREE_VALUE_EXIST` tv
        LEFT JOIN `T2_TREE_PARAMS_EXIST` tp ON (tp.`PARAM_ID` = tv.`PARAM_ID`)
    WHERE tv.`GROUP_ID` = $group_id AND tv.`SITEMAP_STATUS` = 1;");
    $n3 = $db->num_rows($r3);
    for ($l = 1; $l <= $n3; $l++) {
        $value_id = $db->result($r3, $l - 1, "VALUE_ID");
        $value_link = $db->result($r3, $l - 1, "VALUE_LINK");
        $param_id = $db->result($r3, $l - 1, "PARAM_ID");
        $param_link = $db->result($r3, $l - 1, "PARAM_LINK");

        $r1 = $db->query("SELECT `MFA_ID`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_ID` ASC;");
        $n1 = $db->num_rows($r1);
        for ($l1 = 1; $l1 <= $n1; $l1++) {
            $mfa_id = $db->result($r1, $l1 - 1, "MFA_ID") + 0;
            $mfa_link = $db->result($r1, $l1 - 1, "MFA_BRAND_LINK");
            $xmlWriter->setIndent(2);
            $xmlWriter->startElement('url');
            $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/$param_link=$value_link/$mfa_link/");
            $xmlWriter->writeElement('changefreq', 'weekly');
            $xmlWriter->writeElement('priority', '1');
            $xmlWriter->endElement();
            $col++;

            if (($col % $max_tags_count) == 0) {
                $xmlWriter->endElement();
                $doc_nom_params++;
                file_put_contents(RDD . "/uk/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
                $xmlWriter->startElement('urlset');
                $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
                $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
                $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
            }

            $r = $db->query("SELECT `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 GROUP BY `Model` ORDER BY `Model` ASC;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model_link = $db->result($r, $i - 1, "Model_Link");
                $xmlWriter->setIndent(2);
                $xmlWriter->startElement('url');
                $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/$param_link=$value_link/$mfa_link/$model_link/");
                $xmlWriter->writeElement('changefreq', 'weekly');
                $xmlWriter->writeElement('priority', '1');
                $xmlWriter->endElement();
                $col++;

                if (($col % $max_tags_count) == 0) {
                    $xmlWriter->endElement();
                    $doc_nom_params++;
                    file_put_contents(RDD . "/uk/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
                    $xmlWriter->startElement('urlset');
                    $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
                    $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
                    $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
                }
            }
        }

    }
}

$xmlWriter->endElement();
$doc_nom_params++;
file_put_contents(RDD . "/uk/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

/*
 * INIT `sitemap-cars`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

$r1 = $db->query("SELECT `MFA_ID`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_ID` ASC;");
$n1 = $db->num_rows($r1);
for ($l = 1; $l <= $n1; $l++) {
    $mfa_id = $db->result($r1, $l - 1, "MFA_ID") + 0;
    $mfa_link = $db->result($r1, $l - 1, "MFA_BRAND_LINK");
    $xmlWriter->setIndent(2);
    $xmlWriter->startElement('url');
    $xmlWriter->writeElement('loc', $link . "cars/$mfa_link/");
    $xmlWriter->writeElement('changefreq', 'weekly');
    $xmlWriter->writeElement('priority', '0.9');
    $xmlWriter->endElement();
    $r = $db->query("SELECT `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 GROUP BY `Model` ORDER BY `Model` ASC;");
    $n = $db->num_rows($r);
    for ($i = 1; $i <= $n; $i++) {
        $model_link = $db->result($r, $i - 1, "Model_Link");
        $xmlWriter->setIndent(2);
        $xmlWriter->startElement('url');
        $xmlWriter->writeElement('loc', $link . "cars/$mfa_link/$model_link/");
        $xmlWriter->writeElement('changefreq', 'weekly');
        $xmlWriter->writeElement('priority', '0.9');
        $xmlWriter->endElement();
    }
}

$xmlWriter->endElement();
file_put_contents(RDD . "/uk/sitemap-cars.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

/*
 * INIT `pages`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

// ==> MAIN PAGE
$xmlWriter->startElement('url');
$xmlWriter->writeElement('loc', $link);
$xmlWriter->writeElement('changefreq', 'weekly');
$xmlWriter->writeElement('priority', '1');
$xmlWriter->endElement();

// == > MODULES
$r = $db->query("SELECT `MODULE` FROM `T2_MODULES` WHERE `STATUS` = 1;");
$n = $db->num_rows($r);
for ($i = 1; $i <= $n; $i++) {
    $module = $db->result($r, $i - 1, "MODULE");
    $xmlWriter->startElement('url');
    $xmlWriter->writeElement('loc', $link . $module . "/");
    $xmlWriter->writeElement('changefreq', 'weekly');
    $xmlWriter->writeElement('priority', '0.9');
    $xmlWriter->endElement();
}

$xmlWriter->endElement();
file_put_contents(RDD . "/uk/sitemap-pages.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

/*
 * INIT `sitemap`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('sitemapindex');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");

$xmlWriter->startElement('sitemap');
$xmlWriter->writeElement('loc', $link . "sitemap-pages.xml");
$xmlWriter->endElement();

$xmlWriter->startElement('sitemap');
$xmlWriter->writeElement('loc', $link . "sitemap-cars.xml");
$xmlWriter->endElement();

$xmlWriter->startElement('sitemap');
$xmlWriter->writeElement('loc', $link . "sitemap-categories.xml");
$xmlWriter->endElement();

$xmlWriter->startElement('sitemap');
$xmlWriter->writeElement('loc', $link . "sitemap-categories-params.xml");
$xmlWriter->endElement();

for ($i = 1; $i <= $doc_nom; $i++) {
    $xmlWriter->startElement('sitemap');
    $xmlWriter->writeElement('loc', $link . "sitemap-manufactures-$i.xml");
    $xmlWriter->endElement();
}

for ($i = 1; $i <= $doc_nom_params; $i++) {
    $xmlWriter->startElement('sitemap');
    $xmlWriter->writeElement('loc', $link . "sitemap-manufactures-params-$i.xml");
    $xmlWriter->endElement();
}

$xmlWriter->endElement();
file_put_contents(RDD . "/uk/sitemap.xml", $xmlWriter->flush(true), FILE_APPEND);
$xmlWriter->endDocument();

$time = microtime(true) - $start;
print "RUN TIME: " . $time;