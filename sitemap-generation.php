<?php

$start = microtime(true);
ob_clean();

define('RDD', dirname (__FILE__));
error_reporting(0);
@ini_set('display_errors', false);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');

require_once (RDD . "/lib/DbSingleton.php");
require_once (RDD . "/lib/mysql_class.php");
require_once (RDD . "/lib/helper.php");
require_once (RDD . "/lib/variables.php");
require_once (RDD . "/lib/catalogue_class.php");
require_once (RDD . "/lib/catalog_exist_class.php");

$link = "https://toko.ua/";
$link_ua = "https://toko.ua/uk/";

$db = DbSingleton::getTokoDb();
$dbc = DbSingleton::getTokoCacheDb();
$catalog = new CatalogueClass();
$catalog_exist = new CatalogExistClass();

$xmlWriter = new XMLWriter();
$xmlWriter->openMemory();
$max_tags_count = 15000;
$doc_nom = 0;
$doc_nom_params = 0;

$mask = RDD . "/sitemap-groups-manufactures-*.*";
array_map('unlink', glob($mask));
$mask = RDD . "/sitemap-groups-manufactures-params-*.*";
array_map('unlink', glob($mask));
unlink(RDD . "/sitemap.xml");
unlink(RDD . "/sitemap-pages.xml");
unlink(RDD . "/sitemap-cars.xml");
unlink(RDD . "/sitemap-groups.xml");
unlink(RDD . "/sitemap-groups-params.xml");

$mask = RDD . "/ua-uk/sitemap-groups-manufactures-*.*";
array_map('unlink', glob($mask));
$mask = RDD . "/ua-uk/sitemap-groups-manufactures-params-*.*";
array_map('unlink', glob($mask));
unlink(RDD . "/ua-uk/sitemap.xml");
unlink(RDD . "/ua-uk/sitemap-pages.xml");
unlink(RDD . "/ua-uk/sitemap-cars.xml");
unlink(RDD . "/ua-uk/sitemap-groups.xml");
unlink(RDD . "/ua-uk/sitemap-groups-params.xml");

/*======================================================================================================================
 * INIT `sitemap-manufactures`
 * */
$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('urlset');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");

$dataArray                  = $catalog_exist->getSitemapArray();
$arr_modules                = $dataArray["arr_modules"];
$arr_cars                   = $dataArray["arr_cars"];
$arr_groups                 = $dataArray["arr_groups"];
$arr_groups_params          = $dataArray["arr_groups_params"];
$arr_groups_models          = $dataArray["arr_groups_models"];
$arr_groups_models_params   = $dataArray["arr_groups_models_params"];


print("pages: " . count($arr_modules));
print("; cars: " . count($arr_cars));
print("; groups: " . count($arr_groups));
print("; groups-params: " . count($arr_groups_params));
print("; groups-manufactures: " . count($arr_groups_models));
print("; groups-manufactures-params: " . count($arr_groups_models_params) . "\n");
//
///*RU==================================================================================================================*/
//
///*======================================================================================================================
// * OUTPUT sitemap-pages
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//// ==> MAIN PAGE
//$xmlWriter->startElement('url');
//$xmlWriter->writeElement('loc', $link);
//$xmlWriter->writeElement('changefreq', 'weekly');
//$xmlWriter->writeElement('priority', '1');
//$xmlWriter->endElement();
//
//// == > MODULES
//foreach ($arr_modules as $module) {
//    $xmlWriter->startElement('url');
//    $xmlWriter->writeElement('loc', $link . $module . "/");
//    $xmlWriter->writeElement('changefreq', 'weekly');
//    $xmlWriter->writeElement('priority', '0.9');
//    $xmlWriter->endElement();
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/sitemap-pages.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-cars
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_cars as $mfa_id => $models) {
//    $mfa_link = $catalog->getManufactureLink($mfa_id);
//    $xmlWriter->setIndent(2);
//    $xmlWriter->startElement('url');
//    $xmlWriter->writeElement('loc', "https://toko.ua/cars/$mfa_link/");
//    $xmlWriter->writeElement('changefreq', 'weekly');
//    $xmlWriter->writeElement('priority', '0.9');
//    $xmlWriter->endElement();
//    foreach ($models as $model) {
//        $model_link = $catalog->getModelLink($model);
//        $xmlWriter->setIndent(2);
//        $xmlWriter->startElement('url');
//        $xmlWriter->writeElement('loc', "https://toko.ua/cars/$mfa_link/$model_link/");
//        $xmlWriter->writeElement('changefreq', 'weekly');
//        $xmlWriter->writeElement('priority', '0.9');
//        $xmlWriter->endElement();
//    }
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/sitemap-cars.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups as $group_id) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//    $xmlWriter->setIndent(2);
//    $xmlWriter->startElement('url');
//    $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/");
//    $xmlWriter->writeElement('changefreq', 'weekly');
//    $xmlWriter->writeElement('priority', '1');
//    $xmlWriter->endElement();
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/sitemap-groups.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups-params
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups_params as $group_id => $params) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//    foreach ($params as $param_id => $values) {
//        $param_link = $catalog->getParamLink($param_id);
//        foreach ($values as $value_id) {
//            $value_link = $catalog->getValueLink($value_id);
//            $xmlWriter->setIndent(2);
//            $xmlWriter->startElement('url');
//            $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/$param_link=$value_link/");
//            $xmlWriter->writeElement('changefreq', 'weekly');
//            $xmlWriter->writeElement('priority', '1');
//            $xmlWriter->endElement();
//        }
//    }
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/sitemap-groups-params.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups-manufactures
// * */
//
//$col = 0;
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups_models as $group_id => $mfas) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//    foreach ($mfas as $mfa_id => $models) {
//        $mfa_link = $catalog->getManufactureLink($mfa_id);
//        $xmlWriter->setIndent(2);
//        $xmlWriter->startElement('url');
//        $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/auto/$mfa_link/");
//        $xmlWriter->writeElement('changefreq', 'weekly');
//        $xmlWriter->writeElement('priority', '0.9');
//        $xmlWriter->endElement();
//        $col++;
//
//        if (($col % $max_tags_count) == 0) {
//            $xmlWriter->endElement();
//            $doc_nom++;
//            file_put_contents(RDD . "/sitemap-groups-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
//            $xmlWriter->startElement('urlset');
//            $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//            $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//            $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//        }
//
//        foreach ($models as $model) {
//            $model_link = $catalog->getModelLink($model);
//            $xmlWriter->setIndent(2);
//            $xmlWriter->startElement('url');
//            $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/auto/$mfa_link/$model_link/");
//            $xmlWriter->writeElement('changefreq', 'weekly');
//            $xmlWriter->writeElement('priority', '0.9');
//            $xmlWriter->endElement();
//            $col++;
//
//            if (($col % $max_tags_count) == 0) {
//                $xmlWriter->endElement();
//                $doc_nom++;
//                file_put_contents(RDD . "/sitemap-groups-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
//                $xmlWriter->startElement('urlset');
//                $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//                $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//            }
//        }
//    }
//}
//
//$xmlWriter->endElement();
//$doc_nom++;
//file_put_contents(RDD . "/sitemap-groups-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups-manufactures-params
// * */
//
//$col = 0;
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups_models_params as $group_id => $params) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//
//    foreach ($params as $param_id => $values) {
//        $param_link = $catalog->getParamLink($param_id);
//
//        foreach ($values as $value_id => $mfas) {
//            $value_link = $catalog->getValueLink($value_id);
//
//            foreach ($mfas as $mfa_id => $models) {
//                $mfa_link = $catalog->getManufactureLink($mfa_id);
//
//                $xmlWriter->setIndent(2);
//                $xmlWriter->startElement('url');
//                $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/$param_link=$value_link/$mfa_link/");
//                $xmlWriter->writeElement('changefreq', 'weekly');
//                $xmlWriter->writeElement('priority', '1');
//                $xmlWriter->endElement();
//                $col++;
//
//                if (($col % $max_tags_count) == 0) {
//                    $xmlWriter->endElement();
//                    $doc_nom_params++;
//                    file_put_contents(RDD . "/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
//                    $xmlWriter->startElement('urlset');
//                    $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                    $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//                    $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                }
//
//                foreach ($models as $model) {
//                    $model_link = $catalog->getModelLink($model);
//
//                    $xmlWriter->setIndent(2);
//                    $xmlWriter->startElement('url');
//                    $xmlWriter->writeElement('loc', $link . "catalog/$tex_link/$param_link=$value_link/$mfa_link/$model_link/");
//                    $xmlWriter->writeElement('changefreq', 'weekly');
//                    $xmlWriter->writeElement('priority', '1');
//                    $xmlWriter->endElement();
//                    $col++;
//
//                    if (($col % $max_tags_count) == 0) {
//                        $xmlWriter->endElement();
//                        $doc_nom_params++;
//                        file_put_contents(RDD . "/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
//                        $xmlWriter->startElement('urlset');
//                        $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                        $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//                        $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                    }
//
//                }
//            }
//        }
//    }
//
//}
//
//$xmlWriter->endElement();
//$doc_nom_params++;
//file_put_contents(RDD . "/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * INIT `sitemap`
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('sitemapindex');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-pages.xml");
//$xmlWriter->endElement();
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-cars.xml");
//$xmlWriter->endElement();
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups.xml");
//$xmlWriter->endElement();
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups-params.xml");
//$xmlWriter->endElement();
//
//for ($i = 1; $i <= $doc_nom; $i++) {
//    $xmlWriter->startElement('sitemap');
//    $xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups-manufactures-$i.xml");
//    $xmlWriter->endElement();
//}
//
//for ($i = 1; $i <= $doc_nom_params; $i++) {
//    $xmlWriter->startElement('sitemap');
//    $xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups-manufactures-params-$i.xml");
//    $xmlWriter->endElement();
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/sitemap.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*UA==================================================================================================================*/
//
///*======================================================================================================================
// * OUTPUT sitemap-pages
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//// ==> MAIN PAGE
//$xmlWriter->startElement('url');
//$xmlWriter->writeElement('loc', $link_ua);
//$xmlWriter->writeElement('changefreq', 'weekly');
//$xmlWriter->writeElement('priority', '1');
//$xmlWriter->endElement();
//
//// == > MODULES
//foreach ($arr_modules as $module) {
//    $xmlWriter->startElement('url');
//    $xmlWriter->writeElement('loc', $link_ua . $module . "/");
//    $xmlWriter->writeElement('changefreq', 'weekly');
//    $xmlWriter->writeElement('priority', '0.9');
//    $xmlWriter->endElement();
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/ua-uk/sitemap-pages.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-cars
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_cars as $mfa_id => $models) {
//    $mfa_link = $catalog->getManufactureLink($mfa_id);
//    $xmlWriter->setIndent(2);
//    $xmlWriter->startElement('url');
//    $xmlWriter->writeElement('loc', "https://toko.ua/cars/$mfa_link/");
//    $xmlWriter->writeElement('changefreq', 'weekly');
//    $xmlWriter->writeElement('priority', '0.9');
//    $xmlWriter->endElement();
//    foreach ($models as $model) {
//        $model_link = $catalog->getModelLink($model);
//        $xmlWriter->setIndent(2);
//        $xmlWriter->startElement('url');
//        $xmlWriter->writeElement('loc', "https://toko.ua/cars/$mfa_link/$model_link/");
//        $xmlWriter->writeElement('changefreq', 'weekly');
//        $xmlWriter->writeElement('priority', '0.9');
//        $xmlWriter->endElement();
//    }
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/ua-uk/sitemap-cars.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups as $group_id) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//    $xmlWriter->setIndent(2);
//    $xmlWriter->startElement('url');
//    $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/");
//    $xmlWriter->writeElement('changefreq', 'weekly');
//    $xmlWriter->writeElement('priority', '1');
//    $xmlWriter->endElement();
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/ua-uk/sitemap-groups.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups-params
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups_params as $group_id => $params) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//    foreach ($params as $param_id => $values) {
//        $param_link = $catalog->getParamLink($param_id);
//        foreach ($values as $value_id) {
//            $value_link = $catalog->getValueLink($value_id);
//            $xmlWriter->setIndent(2);
//            $xmlWriter->startElement('url');
//            $xmlWriter->writeElement('loc', $link_ua . "catalog/$tex_link/$param_link=$value_link/");
//            $xmlWriter->writeElement('changefreq', 'weekly');
//            $xmlWriter->writeElement('priority', '1');
//            $xmlWriter->endElement();
//        }
//    }
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/ua-uk/sitemap-groups-params.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups-manufactures
// * */
//
//$col = 0;
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups_models as $group_id => $mfas) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//    foreach ($mfas as $mfa_id => $models) {
//        $mfa_link = $catalog->getManufactureLink($mfa_id);
//        $xmlWriter->setIndent(2);
//        $xmlWriter->startElement('url');
//        $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/auto/$mfa_link/");
//        $xmlWriter->writeElement('changefreq', 'weekly');
//        $xmlWriter->writeElement('priority', '0.9');
//        $xmlWriter->endElement();
//        $col++;
//
//        if (($col % $max_tags_count) == 0) {
//            $xmlWriter->endElement();
//            $doc_nom++;
//            file_put_contents(RDD . "/ua-uk/sitemap-groups-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
//            $xmlWriter->startElement('urlset');
//            $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//            $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//            $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//        }
//
//        foreach ($models as $model) {
//            $model_link = $catalog->getModelLink($model);
//            $xmlWriter->setIndent(2);
//            $xmlWriter->startElement('url');
//            $xmlWriter->writeElement('loc', "https://toko.ua/catalog/$tex_link/auto/$mfa_link/$model_link/");
//            $xmlWriter->writeElement('changefreq', 'weekly');
//            $xmlWriter->writeElement('priority', '0.9');
//            $xmlWriter->endElement();
//            $col++;
//
//            if (($col % $max_tags_count) == 0) {
//                $xmlWriter->endElement();
//                $doc_nom++;
//                file_put_contents(RDD . "/ua-uk/sitemap-groups-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
//                $xmlWriter->startElement('urlset');
//                $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//                $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//            }
//        }
//    }
//}
//
//$xmlWriter->endElement();
//$doc_nom++;
//file_put_contents(RDD . "/ua-uk/sitemap-groups-manufactures-$doc_nom.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * OUTPUT sitemap-groups-manufactures-params
// * */
//
//$col = 0;
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('urlset');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//$xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//$xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//foreach ($arr_groups_models_params as $group_id => $params) {
//    $tex_link = $catalog->getGroupRowLink($group_id);
//
//    foreach ($params as $param_id => $values) {
//        $param_link = $catalog->getParamLink($param_id);
//
//        foreach ($values as $value_id => $mfas) {
//            $value_link = $catalog->getValueLink($value_id);
//
//            foreach ($mfas as $mfa_id => $models) {
//                $mfa_link = $catalog->getManufactureLink($mfa_id);
//
//                $xmlWriter->setIndent(2);
//                $xmlWriter->startElement('url');
//                $xmlWriter->writeElement('loc', $link_ua . "catalog/$tex_link/$param_link=$value_link/$mfa_link/");
//                $xmlWriter->writeElement('changefreq', 'weekly');
//                $xmlWriter->writeElement('priority', '1');
//                $xmlWriter->endElement();
//                $col++;
//
//                if (($col % $max_tags_count) == 0) {
//                    $xmlWriter->endElement();
//                    $doc_nom_params++;
//                    file_put_contents(RDD . "/ua-uk/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
//                    $xmlWriter->startElement('urlset');
//                    $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                    $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//                    $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                }
//
//                foreach ($models as $model) {
//                    $model_link = $catalog->getModelLink($model);
//
//                    $xmlWriter->setIndent(2);
//                    $xmlWriter->startElement('url');
//                    $xmlWriter->writeElement('loc', $link_ua . "catalog/$tex_link/$param_link=$value_link/$mfa_link/$model_link/");
//                    $xmlWriter->writeElement('changefreq', 'weekly');
//                    $xmlWriter->writeElement('priority', '1');
//                    $xmlWriter->endElement();
//                    $col++;
//
//                    if (($col % $max_tags_count) == 0) {
//                        $xmlWriter->endElement();
//                        $doc_nom_params++;
//                        file_put_contents(RDD . "/ua-uk/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
//                        $xmlWriter->startElement('urlset');
//                        $xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                        $xmlWriter->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
//                        $xmlWriter->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9");
//                    }
//
//                }
//            }
//        }
//    }
//
//}
//
//$xmlWriter->endElement();
//$doc_nom_params++;
//file_put_contents(RDD . "/ua-uk/sitemap-manufactures-params-$doc_nom_params.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();
//
///*======================================================================================================================
// * INIT `sitemap`
// * */
//
//$xmlWriter->startDocument('1.0', 'UTF-8');
//$xmlWriter->startElement('sitemapindex');
//$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-pages.xml");
//$xmlWriter->endElement();
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-cars.xml");
//$xmlWriter->endElement();
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups.xml");
//$xmlWriter->endElement();
//
//$xmlWriter->startElement('sitemap');
//$xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups-params.xml");
//$xmlWriter->endElement();
//
//for ($i = 1; $i <= $doc_nom; $i++) {
//    $xmlWriter->startElement('sitemap');
//    $xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups-manufactures-$i.xml");
//    $xmlWriter->endElement();
//}
//
//for ($i = 1; $i <= $doc_nom_params; $i++) {
//    $xmlWriter->startElement('sitemap');
//    $xmlWriter->writeElement('loc', "https://toko.ua/sitemap-groups-manufactures-params-$i.xml");
//    $xmlWriter->endElement();
//}
//
//$xmlWriter->endElement();
//file_put_contents(RDD . "/ua-uk/sitemap.xml", $xmlWriter->flush(true), FILE_APPEND);
//$xmlWriter->endDocument();

/*RT==================================================================================================================*/

$time = microtime(true) - $start;
print "RUN TIME: " . $time;

