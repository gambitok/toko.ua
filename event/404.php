<?php

header("HTTP/1.0 404 Not Found");

$content = str_replace("{main_window}", $menu->getHtmlForm("error/404"), $content);
