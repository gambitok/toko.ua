<?php

$r = $db->query("SELECT `caption_utf` FROM `new_lang_wdv_utf` WHERE `id` = 1 LIMIT 1;");

print($db->result($r, 0, "caption_utf"));



