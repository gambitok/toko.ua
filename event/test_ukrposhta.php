<?php

require_once RDD . "/lib/UkrPoshtaClass.php";
$up = new UkrPoshtaClass("a979e2d9-d044-3f41-8b8c-099c5879ae32");

$form = $catalogue->getHtmlForm("test_up");
$regions_list = $up->printList($up->getRegionsList());

$form = str_replace(array("{regions_list}", "{cities_list}", "{districts_list}"), array($regions_list, "", ""), $form);

$content = str_replace("{main_window}", $form, $content);

