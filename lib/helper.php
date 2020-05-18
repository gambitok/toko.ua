<?php

trait Helper {

    var $images="/images";
    var $uplImages="/uploads/images";
    var $uploads="/uploads";
    var $noPhoto="/images/no_photo.png";
    protected $err1="{nothing_found}";
    protected $err2="{not_specified}";
    protected $err3="{no_info}";
    protected $mess1="-{not_chosen}-";

    function getHtmlForm($name) {
        $form=""; $form_htm=RDD."/tpl/$name.htm"; if (file_exists("$form_htm")){ $form = file_get_contents($form_htm);}
        iconv('Windows-1251', 'UTF-8', $form);
        mb_convert_encoding($form, 'UTF-8', 'Windows-1251');
        return $form;
    }

    function checkRedirectLink($str) {
        $pos = strpos($str, ".htm"); if ($pos !== false) return 0;
        $pos = strpos($str, ".html"); if ($pos !== false) return 0;
        $pos = strpos($str, ".php"); if ($pos !== false) return 0;
        return 1;//
    }

    function getRedirectLink($str) {
        $str = str_replace(".htm", "", $str);
        $str = str_replace(".html", "", $str);
        $str = str_replace(".php", "", $str);
        return $str;
    }

    function getUrlString($str) {
        $str = str_replace("'","",$str);
        $str = str_replace("`","",$str);
        $str = str_replace(",","",$str);
        $str = str_replace('"',"",$str);
        $str = str_replace("%20"," ",$str);
        $str = str_replace("%60","",$str);
        $str = str_replace("&nbsp;","",$str);
        $str = str_replace("&rsquo;","",$str);
        return $str;
    }

    function getUrlNumber($number) {
        if (!is_numeric($number)) $number=0;
        return $number;
    }

    function getClient() {
        $client=new ClientClass;
        $clientData=$client->getClient(); $client_id=$clientData[0];
        return $client_id;
    }

    function getUser() {
        $client=new ClientClass;
        $clientData=$client->getClient(); $user=$clientData[1];
        return $user;
    }

    function replaceLang($cont) {
        $language=new LangClass;
        $cont=$language->replaceLang($cont);
        return $cont;
    }

    function getManualName($key) { $db=DbSingleton::getDbm();
        $r=$db->query("SELECT `mcaption` FROM `manual` WHERE `id`='$key';");
        $caption=$db->result($r,0,"mcaption");
        return $caption;
    }

    function getManualNameCaption($key,$mid) { $db=DbSingleton::getDbm();
        $r=$db->query("SELECT `mcaption` FROM `manual` WHERE `key`='$key' AND `mid`='$mid' LIMIT 1;");
        $caption=$db->result($r,0,"mcaption");
        return $caption;
    }

    function getManualOptions($key) { $db=DbSingleton::getDbm();
        $language=new LangClass; $lang_id=$language->getLanguage();
        $r=$db->query("SELECT * FROM `manual` WHERE `key`='$key' ORDER BY mid ASC;"); $n=$db->num_rows($r); $options="";
        for ($i=1;$i<=$n;$i++) {
            $id = $db->result($r, $i - 1, "id");
            $rs=$db->query("SELECT * FROM `A_CUSTOMERS_CATEGORIES` WHERE `manual_id`='$id' AND `lang_id`='$lang_id' LIMIT 1;");
            $caption = $db->result($rs, 0, "caption");
            if ($caption=="") $caption = $db->result($r, $i-1, "mcaption");
            $options.="<option value=\"$id\">$caption</option>";
        }
        return $options;
    }

    function multiSort() {
        $args = func_get_args();
        $c = count($args);
        if ($c < 2) { return false; }
        $array = array_splice($args, 0, 1);
        $array = $array[0];
        usort($array, function($a, $b) use($args) {
            $i = 0; $c = count($args); $cmp = 0;
            while($cmp == 0 && $i < $c)
            {
                $cmp = strcmp($a[ $args[ $i ] ], $b[ $args[ $i ] ]);
                $i++;
            }
            return $cmp;
        });
        return $array;
    }

    function getWeekdayAbr($week_day){
        $wks = array ( '1' => "Пн", '2' => "Вт", '3' => "Ср", '4' => "Чт", '5' => "Пт", '6' => "Сб", '7' => "Нд");
        $wks["$week_day"]=iconv("UTF-8", "Windows-1251", $wks["$week_day"]);
        return $wks["$week_day"];
    }

    function translit($st) {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];
        $lat = [
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya'
        ];
        $st = str_replace($cyr, $lat, $st);
        return $st;
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    function getOfferCap($i) {
        $cap1="{offer_cap}";
        $cap2="{offer_pair_cap}";
        $cap3="{offer_tenths_cap}";
        $cap="";
        $mas1 = [1];
        $mas2 = [2,3,4];
        $mas3 = [0,5,6,7,8,9];
        $mas4 = [11,12,13,14,15,16,17,18,19];
        $mod = $i%10;
        if (in_array($mod,$mas1)) $cap=$cap1;
        if (in_array($mod,$mas2)) $cap=$cap2;
        if (in_array($mod,$mas3) || in_array($i,$mas4)) $cap=$cap3;
        $cap=$this->replaceLang($cap);
        return $cap;
    }

    function checkPhoto($ref) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT COUNT(*) as col FROM `T2_PHOTOS` WHERE `ART_ID`='$ref' AND `ACTIVE`=1;");
        $n=intval($db->result($r,0,"col"));
        $n > 0 ? $res = true : $res = false;
        return $res;
    }

    function getStaticH1($uri) {
        $static_data = include $_SERVER["DOCUMENT_ROOT"].'/seoshield-client/data/static_meta.cache.php';
        $static_h1 = "";
        if (isset($static_data['//'.$_SERVER["HTTP_HOST"].$uri])){
            $static_h1 = $static_data['//'.$_SERVER["HTTP_HOST"].$uri][2];
        }
        $static_h1 = iconv("UTF-8", "windows-1251", $static_h1);
        return $static_h1;
    }

}