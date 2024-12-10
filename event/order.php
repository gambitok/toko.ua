<?php

global $content, $catalogue, $shop;

$order_id       = $catalogue->getUrlNumber($_GET['order_id']);
$user_id        = $catalogue->getUrlNumber($_GET['user_id']);
$user_status    = $catalogue->getUrlNumber($_GET['user_status']);

if (empty($order_id)) {
    $content = $shop->getHtmlForm("orders/template");
    $content = str_replace("{main_window}", $shop->getOrderForm(), $content);
} else {
    $form = $shop->getHtmlForm("orders/conversation");
    $form = str_replace("{conversation_sum}", $shop->getOrderSum($order_id), $form);
    $content = str_replace(
        array("{site_google_conversation}", "{main_window}"),
        array($form, $shop->getOrderContentForm($order_id, $user_id, $user_status)),
    $content);
}
