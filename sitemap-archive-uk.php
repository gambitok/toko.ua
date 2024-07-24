<?php

$xmlWriter = new XMLWriter();
$xmlWriter->openMemory();

const RDD = __DIR__;

$lang = "/ua-uk";

$names = [
    RDD . "$lang/sitemap-pages.xml",
    RDD . "$lang/sitemap-cars.xml",
    RDD . "$lang/sitemap-groups.xml"
];
foreach (glob(RDD . "$lang/sitemap-groups-params-*.*") as $file) {
    $names[] = $file;
}
foreach (glob(RDD . "$lang/sitemap-groups-manufactures-*.*") as $file) {
    $names[] = $file;
}
foreach (glob(RDD . "$lang/sitemap-groups-manufactures-params-*.*") as $file) {
    $names[] = $file;
}

$names = array_unique($names);

unlink(RDD . "$lang/sitemap.xml");

$xmlWriter->startDocument('1.0', 'UTF-8');
$xmlWriter->startElement('sitemapindex');
$xmlWriter->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");

foreach ($names as $file) {
    if (file_exists($file)) {
        $data = file_get_contents($file);
        $gz_data = gzencode($data);
        $new_file = str_replace(".xml", ".xml.gz", $file);
        file_put_contents($new_file, $gz_data);
        unlink($file);

        $new_path = str_replace("/var/www/toko.ua$lang/", "https://toko.ua$lang/", $new_file);

        $xmlWriter->startElement('sitemap');
        $xmlWriter->writeElement('loc', $new_path);
        $xmlWriter->endElement();
    }
}

$xmlWriter->endElement();
file_put_contents(RDD . "$lang/sitemap.xml", $xmlWriter->flush(), FILE_APPEND);
$xmlWriter->endDocument();

