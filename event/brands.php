<?php

$linka = findLinks();
$brand_link = $catalogue->getUrlString($linka[1]);
$brand_id = $catalogue->getBrandNameLink($brand_link);
$content = str_replace("{main_window}", "<div class='metro'>" . $showform->showBrandForm($brand_id) . "</div>", $content);