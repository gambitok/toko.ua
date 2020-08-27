<?php
error_reporting( E_ALL ^ E_NOTICE);
@ini_set('display_errors', true);
define('RD', dirname (__FILE__));
require_once (RD."/lib/DbSingleton.php"); $db = DbSingleton::getTokoDb();
require_once (RD."/lib/slave_class.php"); $slave = new slave;

$r=$db->query("SELECT * FROM `cron_api` WHERE `ison`='1' ORDER BY `id` ASC;");$n=$db->num_rows($r);
for ($i=1;$i<=$n;$i++){
	$id=$db->result($r,$i-1,"id");
	$branch_id=$db->result($r,$i-1,"branch");
	$file_name=$db->result($r,$i-1,"file_name");
	$file_path=RD."/uploads/import/manager_update/$file_name";
	if (file_exists($file_path)){
		$handle = @fopen($file_path, "r");$sql_query="";
		if ($handle) { $data=date("Y-m-d H:i:s");
			$db->query("DELETE FROM `T_stock` WHERE `branch`='$branch_id';");
			print "DELETE FROM `T_stock` WHERE `branch`='$branch_id';";
			while (($buffer = fgets($handle, 4096)) !== false) {
				$buffer=str_replace("'","\'",$buffer);$buffer=str_replace("?","",$buffer);
				$buf=explode(";",$buffer);
				$sql_query="INSERT INTO `T_stock` (`branch`,`REF`,`Stock`) VALUES ('$branch_id','$buf[0]','$buf[1]');";
				print "$sql_query<br>";
				$db->query($sql_query);
				
			}
		}
		fclose($handle);
		unlink($file_path);
		$db->query("UPDATE `cron_api` SET `ison`='0' WHERE `id`='$id';");
	}
}
?>