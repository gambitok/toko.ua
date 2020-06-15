<?php

if (empty($_GET["order_id"]))
    $content=str_replace("{main_window}", $shop->showOrderForm(), $content);
else {
    $form=$shop->getHtmlForm("order/order_conversation");
    $form=str_replace("{conversation_summ}", $shop->getOrderSumm($_GET["order_id"]), $form);
    $content=str_replace("{site_google_conversation}", $form, $content);
    $content=str_replace("{main_window}", $shop->showRegistrationSuccessForm($_GET["order_id"], $_GET["client_id"]), $content);
}
