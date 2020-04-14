<?php

if (empty($_GET["order_id"]))
    $content=str_replace("{main_window}", $shop->showOrderForm(), $content);
else
    $content=str_replace("{main_window}", $shop->showRegistrationSuccessForm($_GET["order_id"],$_GET["client_id"]), $content);
