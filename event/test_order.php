<?php

//$np = new \LisDev\Delivery\NovaPoshtaApi2(
//    '656d2934ac1411fdb377a1d6de96fd92',
//    'ru', // Язык возвращаемых данных: ru (default) | ua | en
//    FALSE, // При ошибке в запросе выбрасывать Exception: FALSE (default) | TRUE
//    'curl' // Используемый механизм запроса: curl (defalut) | file_get_content
//);
//
//$recipient_city = $np->getCity('Киев', 'Киевская');
//
//print_r($recipient_city);

$content=str_replace("{main_window}", $shop->getOrderForm(), $content);