<?php

global $content, $menu;
$content = str_replace("{main_window}", $menu->getTelegramBotForm(), $content);