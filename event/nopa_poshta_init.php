<?php

use LisDev\Delivery\NovaPoshtaApi2;

$np = new NovaPoshtaApi2('c11f032abf542a39f2324d58004e12c1');

$list = "";
$arr = $np->getCities();

foreach ($arr as $val) {
    $name       = iconv("UTF-8", "windows-1251", $val["Description"]);
    $ref    = $val["Ref"];

    $list .= "$name ($ref) \n<br>";
}

$content = "";
echo $list;

// CITY REF

// CITY NAME

// AREA REF

// AREA NAME