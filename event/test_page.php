<?php

$date = date("Y-m-d H:i:s");
$date_sel = date("Y-m-d H:i:s", (strtotime("-15 day" , strtotime($date))));

print($date_sel);



