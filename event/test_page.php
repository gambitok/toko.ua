<?php
$form = $catalogue->getHtmlForm("test_page");
$form = str_replace("{test_page_content}", $showform->getTestPageContent(), $form);

$content = str_replace("{main_window}", $form, $content);