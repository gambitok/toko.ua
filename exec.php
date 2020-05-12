<?php
error_reporting( E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
define('RD', dirname (__FILE__));
function fork($clear,$vtype,$tbl){ 
    global $config;
    $cmd = "/usr/bin/php -d memory_limit=256M /var/www/tokoparts.pl/public_html/cron_1c.php $clear $vtype $tbl";
	print "cmd=$cmd<br>";
    echo shell_exec($cmd);
}
fork(1,1,25);
?>