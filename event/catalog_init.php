<?php

$form = $catalogue->getHtmlForm("catalog_init");

$form = str_replace("{select_groups}", $catalogue->getGroupsList(), $form);

$content = str_replace("{main_window}", $form, $content);