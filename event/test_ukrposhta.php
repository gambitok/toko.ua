<?php

require_once RDD . "/lib/UkrPoshtaClass.php";

$up = new UkrPoshtaClass("a979e2d9-d044-3f41-8b8c-099c5879ae32");

var_dump($up->getDistrictsList(10765));