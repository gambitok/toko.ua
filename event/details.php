<?php

$result=explode(findLinks()[0]."/", $_SERVER["REQUEST_URI"], 2); $link=ltrim($result[1]);
header("Location: /catalog/$link", TRUE, 301);
