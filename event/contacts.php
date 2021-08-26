<?php

$content = str_replace("{main_window}", $menu->showContacts(), $content);
$content = str_replace("{site_title}", "{site_contacts}", $content);
$content = str_replace("{site_description}", "", $content);
