<?php

global $content, $menu;

header("HTTP/1.0 404 Not Found");

$content = str_replace("{main_window}", $menu->getHtmlForm("error/404_catalog"), $content);
