<?php

//$list = $catalogue->searchListCatalog2("1127857,1127760,1127727,1127747,1126756,1126663,116095526,116095581,1344821,1344590,1344591,1344651", 1);

//var_dump($catalogue->getCatalogueLink('oc90'));

//$content = str_replace("{main_window}", $catalogue->getHtmlForm("test"), $content);

//require(RDD . "/swagger-generator/vendor/autoload.php");
//$openapi = \OpenApi\Generator::getVersion();
//header('Content-Type: application/x-yaml');
//echo $openapi;

//require(RDD . "/checkbox-php/vendor/autoload.php");
//
//$config = new \igorbunov\Checkbox\Config([
//    \igorbunov\Checkbox\Config::API_URL => 'https://api.checkbox.in.ua/api/v1',
//    \igorbunov\Checkbox\Config::LOGIN => 'test_2hww3xtdc',
//    \igorbunov\Checkbox\Config::PASSWORD => 'test_2hww3xtdc', //or
////    \igorbunov\Checkbox\Config::PINCODE => 7219781348,
//    \igorbunov\Checkbox\Config::LICENSE_KEY => 'testa3e8f4fa24b4a2fbdac576b3'
//]);
//
//$api = new \igorbunov\Checkbox\CheckboxJsonApi($config);
//$api->signInCashier();
//
////$api->signOutCashier();
///

//$link   = "https://toko.ua/catalog/diski-tormoznye-schity-tormoznyh-diskov/brandy=abe;vid-detali=disk-tormoznoy/";
//$out    = bin2hex($link);
//$dec    = hexdec($out);
//$in     = hex2bin($out);
//
//$db->query("INSERT INTO `T2_FOOTER_ARCHIVE2` (`LINK`, `HEXDEC`) VALUES ('text', '$dec');");
//
//var_dump($out);
//var_dump($dec);
//var_dump($in);

$content =  $catalogue->getHtmlForm("test");
