<?php
$dbe = DbSingleton::getTokoEmojiDb();

$r = $dbe->query("SELECT * FROM `SEO_TEXT_EMOJI` WHERE 1;");
$text = $dbe->result($r, 0, "TEXT");
var_dump($text);