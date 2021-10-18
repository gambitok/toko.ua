<?php

$content = str_replace("{main_window}", $showform->showSitemap(), $content);

$content = str_replace("{site_title}", "{sitemap_toko_cap}", $content);
$content = str_replace("{site_description}", "", $content);