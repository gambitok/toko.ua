<?php
define('RDD', __DIR__);
error_reporting(0);
@ini_set('display_errors', false);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");

require_once (RDD."/../lib/DbSingleton.php");
require_once (RDD . "/../lib/Traits/Helper.php");
require_once (RDD . "/../lib/Traits/Variables.php");
require_once (RDD."/../lib/CatalogueClass.php");
require_once (RDD."/../lib/ClientClass.php");
require_once (RDD."/../lib/ProfileClass.php");
require_once (RDD."/../lib/LangClass.php");
require_once (RDD."/../lib/ExRateClass.php");
$catalogue = new CatalogueClass();

$dbm = DbSingleton::getDbm();

$r = $dbm->query("SELECT `user_id`, `date`, `filename` FROM `cron_task_prices` WHERE `status` = 1;");
$n = $dbm->num_rows($r);
print "n=$n";

if ($n > 0) {
    for ($i = 1; $i <= $n; $i++) {
        $user       = $dbm->result($r, $i - 1, "user_id");
        $date       = $dbm->result($r, $i - 1, "date");
        $filename   = $user . "/" . $dbm->result($r, $i - 1, "filename");
        $list       = $catalogue->getPriceList($user);

        $csv = "";
        foreach ($list as $record) {
            foreach ($record as $rec) {
                $csv .= $rec . ';';
            }
            $csv .= "\n";
        }

        if (!file_exists(RDD . "/../uploads/$user")) {
            mkdir(RDD . "/../uploads/$user", 0777, true);
        } elseif (file_exists(RDD . "/../uploads/$user/")) {
            foreach (glob(RDD . "/../uploads/$user/*") as $file) {
                unlink($file);
            }
        }

        $csv_handler = fopen(RDD . "/../uploads/$filename", 'w') or die("Can't create file");
        fwrite($csv_handler, $csv);
        fclose($csv_handler);
        $date_end = date("Y-m-d H:i:s");
        $dbm->query("UPDATE `cron_task_prices` SET `status` = 2, `date_end` = '$date_end' WHERE `user_id` = '$user' AND `status` = 1;");
    }
}