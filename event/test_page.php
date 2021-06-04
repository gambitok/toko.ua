<?php

$r = $dbm->query("SELECT `id`, `name` FROM `A_CLIENTS` WHERE 1 LIMIT 1;");
$n = $dbm->num_rows($r);

for ($i = 1; $i <= $n; $i++) {
    $result = mysqli_fetch_assoc($r);
    var_dump($result["name"]);
}

$content = str_replace("{main_window}", "", $content);



