<?php

class dbmy{
    var $query='';
    function load_auth_param(){
        $this->host = '172.17.0.1';
        $this->dbname = 'myparts_dba';
        $this->username = 'myparts_usr';
        $this->password = 'sdF98723KJef82';
    }

    function connect(){
        $this->load_auth_param();
        $this->db_id = @mysql_connect($this->host, $this->username, $this->password);
        @mysql_select_db($this->dbname, $this->db_id);
        mysql_query ("set character_set_client='cp1251'");
        mysql_query ("set character_set_results='cp1251'");
        mysql_query ("set collation_connection='cp1251_general_ci'");
    }

    function close(){
        @mysql_close($this->db_id);
    }
    function num_rows($result){
        $this->n=mysql_numrows($result);
        return $this->n;
    }
    function query($query){
        if(!$this->db_id){ $this->connect();}
        $this->r = mysql_query($query, $this->db_id);
        if (mysql_error()!=""){ print mysql_error()."<br>query=".$query;}
        return $this->r;
    }
    function result($result,$number,$field_name) {
        return mysql_result($result,$number,"$field_name");
    }
}

$dbm= new dbmy;

$r=$dbm->query("SELECT * FROM `cron_task_prices` WHERE `status`=1;"); $n=$dbm->num_rows($r);
if ($n>0) {
    for ($i=1;$i<=$n;$i++) {
        $user = $db->result($r, $i-1, "user_id");
        $date = $db->result($r, $i-1, "date");
        $filename = $user . "/" . $dbm->result($r, 0, "filename");

        $catalogue = new CatalogueClass;
        $csv = "";

        $list = $catalogue->getPriceList();

        foreach ($list as $record) {
            foreach ($record as $rec) {
                $csv .= $rec . ';';
            }
            $csv .= "\n";
        }

        if (!file_exists(RDD . "/uploads/$user")) {
            mkdir(RDD . "/uploads/$user", 0777, true);
        } else {
            if (file_exists(RDD . "/uploads/$user/")) {
                foreach (glob(RDD . "/uploads/$user/*") as $file) {
                    unlink($file);
                }
            }
        }

        $csv_handler = fopen(RDD . "/uploads/$filename", 'w') or die("Can't create file");
        fwrite($csv_handler, $csv);
        fclose($csv_handler);
        $date_end=date("Y-m-d H:i:s");
        $dbm->query("UPDATE `cron_task_prices` SET `status`=2, `date_end`='$date_end' WHERE `user_id`='$user' AND `status`=1;");
    }
}