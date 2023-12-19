<?php

use LisDev\Delivery\NovaPoshtaApi2;

function getNovaPoshtaKey()
{
    $db = DbSingleton::getTokoDb();
    $key = "";
    $r = $db->query("SELECT `CODE` FROM `MAIL_SETTINGS` WHERE `ID` = 1 LIMIT 1;");
    $n = $db->num_rows($r);
    if ($n > 0) {
        $key = $db->result($r, 0, "CODE");
    }
    return $key;
}

$np = new NovaPoshtaApi2(getNovaPoshtaKey());

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