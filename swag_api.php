<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');
header('Content-Type: text/html; charset=utf-8');

require_once (RDD . "/vendor/autoload.php");
require_once (RDD . "/lib/Plugins/checkbox-php/vendor/autoload.php");

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

var_dump($api->getReceipts());

//$db = DbSingleton::getDbm();
//
//$cashier_name = iconv("windows-1251", "UTF-8", "Касир");
//$department = iconv("windows-1251", "UTF-8", "Отдел");
//$email = "gambitokgd@gmail.com";
//
//$r = $db->query("SELECT `summ` FROM `J_SALE_INVOICE` WHERE `id` = 76749 LIMIT 1;");
//$sum = (float)$db->result($r, 0, "summ");
//$sum *= 100;
//
//$r = $db->query("SELECT `price_end`, `amount` FROM `J_SALE_INVOICE_STR` WHERE `invoice_id` = 76749;");
//$n = $db->num_rows($r);
//
//$arr = [];
//for ($i = 1; $i <= $n; $i++) {
//    $amount = (int)$db->result($r, $i - 1, "amount");
//    $price_end = (float)$db->result($r, $i - 1, "price_end");
//
//    $amount *= 1000;
//    $price_end *= 100;
//
//    $arr[] = new \igorbunov\Checkbox\Models\Receipts\Goods\GoodItemModel(
//        new \igorbunov\Checkbox\Models\Receipts\Goods\GoodModel(
//            "test $i",
//            $price_end,
//            "text $i"
//        ),
//        $amount,
//        NULL,
//        NULL,
//        false
//    );
//}
//
//try {
//    $answer = 1; $err = "";
//    $arr_pay = [];
//
//    $arr_pay[] = new \igorbunov\Checkbox\Models\Receipts\Payments\CashPaymentPayload(
//        $sum
//    );
//
//    $receipt = new \igorbunov\Checkbox\Models\Receipts\SellReceipt(
//        $cashier_name,
//        $department,
//        new \igorbunov\Checkbox\Models\Receipts\Goods\Goods(
//            $arr
//        ),
//        $email,
//        new \igorbunov\Checkbox\Models\Receipts\Payments\Payments(
//            $arr_pay
//        )
//    );
//
//    $api->createSellReceipt($receipt);
//
//    var_dump($api->getReceipts());
//
//} catch (\igorbunov\Checkbox\Errors\NoActiveShift $err) {
//    $answer = 0; $err = "Для проведення поточного фіскального чеку на повернення в касі не вистачає коштів. Зробіть службове внесення коштів, або наторгуйте";
//} catch (Exception $e) {
//    $answer = 0; $err = "err";
//}

//======================================================================================================================

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