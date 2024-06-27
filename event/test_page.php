<?php

$content = "";

$start = microtime(true);
$mp = DbSingleton::getMyPartsDb();
$r = $mp->query('SELECT * FROM T2_ARTICLES_PARTITIONS_PERIOD WHERE op_type = ? LIMIT 100', 1)->fetchAll();
$time = microtime(true) - $start;
var_dump($time);

//$start = microtime(true);
//$dm = DbSingleton::getDbm();
//$r = $dm->query('SELECT * FROM T2_ARTICLES_PARTITIONS_PERIOD WHERE op_type = 1 LIMIT 100');
//$time = microtime(true) - $start;
//var_dump($time);

//$server = 'https://api.checkbox.in.ua/api/v1';
//$login  = 'vera777';
//$pass   = 'Vera197022';
//$lkey   = '7b723999628816eb0df24b8e';
//
//$config = new \igorbunov\Checkbox\Config([
//    \igorbunov\Checkbox\Config::API_URL     => $server,
//    \igorbunov\Checkbox\Config::LOGIN       => $login,
//    \igorbunov\Checkbox\Config::PASSWORD    => $pass,
//    \igorbunov\Checkbox\Config::LICENSE_KEY => $lkey
//]);
//
//$api = new \igorbunov\Checkbox\CheckboxJsonApi($config);
//
//try {
//    $api->signInCashier();
//    var_dump("sign in good");
//} catch (\igorbunov\Checkbox\Errors\EmptyResponse $e) {
//    var_dump("sign in error");
//}
//
//var_dump($api->getReceiptHtml('93f33f0c-8e13-4072-8b7b-b54a78dbeb9d'));
//
//$arr = json_decode(json_encode($api->getReceipt('1e7eef25-b5d9-4752-aca2-90bd2d176956')), true);
//var_dump($arr['fiscal_code']);
//
//$api->closeShift();
//
//$api->signOutCashier();
