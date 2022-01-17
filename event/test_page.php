<?php

//$list = $catalogue->searchListCatalog2("1127857,1127760,1127727,1127747,1126756,1126663,116095526,116095581,1344821,1344590,1344591,1344651", 1);

//var_dump($catalogue->getCatalogueLink('oc90'));

//$content = str_replace("{main_window}", $shop->getSearchCityForm(), $content);

require(RDD . "/swagger-generator/vendor/autoload.php");
$openapi = \OpenApi\Generator::getVersion();
header('Content-Type: application/x-yaml');
echo $openapi;
