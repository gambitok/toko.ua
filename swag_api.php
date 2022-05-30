<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');
header('Content-Type: text/html; charset=utf-8');

require(RDD . "/checkbox-php/vendor/autoload.php");

$config = new \igorbunov\Checkbox\Config([
    \igorbunov\Checkbox\Config::API_URL     => 'https://api.checkbox.in.ua/api/v1',
    \igorbunov\Checkbox\Config::LOGIN       => 'test_2hww3xtdc',
    \igorbunov\Checkbox\Config::PASSWORD    => 'test_2hww3xtdc',
    \igorbunov\Checkbox\Config::LICENSE_KEY => 'testa3e8f4fa24b4a2fbdac576b3'
]);

$api = new \igorbunov\Checkbox\CheckboxJsonApi($config);

$api->signInCashier();

if (!empty($api->getCashierShift())) {
    $api->createShift();
}

//$cashier_name = iconv("windows-1251", "UTF-8", "Касир");
//$department = iconv("windows-1251", "UTF-8", "Отдел");
//$name1 = iconv("windows-1251", "UTF-8", "Биовак");
//$name2 = iconv("windows-1251", "UTF-8", "Биовак 2");

//3
//$receipt = new \igorbunov\Checkbox\Models\Receipts\SellReceipt(
//    $cashier_name, // кассир
//    $department, // отдел
//    new \igorbunov\Checkbox\Models\Receipts\Goods\Goods(
//        [
//            new \igorbunov\Checkbox\Models\Receipts\Goods\GoodItemModel( // товар 1
//                new \igorbunov\Checkbox\Models\Receipts\Goods\GoodModel(
//                    'vm-123', // good_id
//                    50 * 100, // 50 грн
//                    $name1 // название товара
//                ),
//                1 * 1000 // кол-во товара  1 шт
//            ),
//            new \igorbunov\Checkbox\Models\Receipts\Goods\GoodItemModel( // товар 2
//                new \igorbunov\Checkbox\Models\Receipts\Goods\GoodModel(
//                    'vm-124', // good_id
//                    20 * 100, // 20 грн
//                    $name2 // название товара
//                ),
//                2 * 1000 // кол-во товара 2 шт
//            )
//        ]
//    ),
//    'admin@gmail.com', // кому отправлять чек по почте
//    new \igorbunov\Checkbox\Models\Receipts\Payments\Payments([
//        new \igorbunov\Checkbox\Models\Receipts\Payments\CardPaymentPayload( // безналичная оплата
//            40 * 100 // 40 грн
//        ),
//        new \igorbunov\Checkbox\Models\Receipts\Payments\CashPaymentPayload( // наличная оплата
//            50 * 100 // 50 грн
//        )
//    ])
//);

//$api->createSellReceipt($receipt); // выполняем оплату

//$payments = [];
//foreach ($arr["payments"]["results"] as $item) {
//    $payments[] = iconv("UTF-8", "windows-1251", $item["label"]);
//}
//$payments = implode(",", $payments);
//var_dump($payments);

//$arr = json_decode(json_encode($api->getReceipts()), true);
//foreach ($arr["results"] as $key => $val) {
//    var_dump($key . " = " . $val["id"]);
//}
//var_dump($api->getReceiptHtml('93f33f0c-8e13-4072-8b7b-b54a78dbeb9d'));

//$arr = json_decode(json_encode($api->getReceipt('1e7eef25-b5d9-4752-aca2-90bd2d176956')), true);
//var_dump($arr['fiscal_code']);

//var_dump($api->getReceipts());

//$api->closeShift();

//$api->signOutCashier();