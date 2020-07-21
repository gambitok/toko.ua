<?php
//
//if (empty($_GET["order_id"]))
//    $content=str_replace("{main_window}", $shop->showOrderForm(), $content);
//else {
//    $form=$shop->getHtmlForm("order/order_conversation");
//    $form=str_replace("{conversation_summ}", $shop->getOrderSumm($_GET["order_id"]), $form);
//    $content=str_replace("{site_google_conversation}", $form, $content);
//    $content=str_replace("{main_window}", $shop->showRegistrationSuccessForm($_GET["order_id"], $_GET["client_id"]), $content);
//}


$order_id = $_GET["order_id"];
$user_id = $_GET["user_id"];
$user_status = $_GET["user_status"];

if ($order_id=="") {
    $content = $shop->getHtmlForm("orders/template");
    $content = str_replace("{main_window}", $shop->getOrderForm(), $content);
} else {
    $content = str_replace("{main_window}", $shop->getOrderContentForm($order_id, $user_id, $user_status), $content);
}



