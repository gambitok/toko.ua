<?php
$dbe = DbSingleton::getTokoEmojiDb();

$r = $dbe->query("SELECT * FROM `T2_SEO_TITLE` WHERE `ID` = 66;");
$text = $dbe->result($r, 0, "DESCR_RU");
var_dump($text);