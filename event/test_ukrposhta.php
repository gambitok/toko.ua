<?php

$config = new Ukrposhta\Data\Configuration();
$config->setBearer('7e769f5c-4ac8-32a8-bdf6-0f5bede5a204');
$config->setToken('1743156c-40ab-451d-9fd7-2970b407c583');

$doc = new Ukrposhta\Doc($config);
$doc->save('./');
