<?php

global $content, $formObj;
$content = str_replace("{main_window}", $formObj->showHistoryForm(), $content);