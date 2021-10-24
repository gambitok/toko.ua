<?php

$content = str_replace("{main_window}", $menu->showContacts() . $showform->getHistoryArts(), $content);
$content = str_replace("{site_title}", "{site_contacts}", $content);
$content = str_replace("{site_description}", "", $content);
