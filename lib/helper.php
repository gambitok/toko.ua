<?php

trait Helper
{

    public $images = "/images";
    public $uploads = "/uploads";
    public $noPhoto = "/images/no_photo.png";
    protected $err1 = "{nothing_found}";
    protected $err2 = "{not_specified}";
    protected $err3 = "{no_info}";

    /*
     * get cookie `session_id`
     * */
    public function getSessionID() {
        return $this->getUrlString($_COOKIE["session_id"]);
    }

    /*
     * get HTML forms
     * */
    public function getHtmlForm($name)
    {
        $form = "";
        $form_htm = RDD . "/tpl/$name.htm";
        if (file_exists("$form_htm")) {
            $form = file_get_contents($form_htm);
        }
        iconv('Windows-1251', 'UTF-8', $form);
        mb_convert_encoding($form, 'UTF-8', 'Windows-1251');
        return $form;
    }

    /*
     * validate string URL
     * */
    public function getUrlString($str)
    {
        $str = str_replace("'", "", $str);
        $str = str_replace("`", "", $str);
        $str = str_replace(",", "", $str);
        $str = str_replace('"', "", $str);
        $str = str_replace("%20", " ", $str);
        $str = str_replace("%22", "", $str);
        $str = str_replace("%27", "", $str);
        $str = str_replace("%60", "", $str);
        $str = str_replace("&nbsp;", "", $str);
        $str = str_replace("&rsquo;", "", $str);
        return $str;
    }

    /*
     * validate string
     * */
    public function getNameString($str)
    {
        $str = str_replace("'", "", $str);
        $str = str_replace("`", "", $str);
        $str = str_replace('"', "", $str);
        $str = str_replace("%20", " ", $str);
        $str = str_replace("%22", "", $str);
        $str = str_replace("%27", "", $str);
        $str = str_replace("%60", "", $str);
        $str = str_replace("&rsquo;", "", $str);
        return $str;
    }

    /*
     * validate number URL
     * */
    public function getUrlNumber($number)
    {
        if (!is_numeric($number)) {
            $number = 0;
        }
        return $number;
    }

    public function getCurrentExrate()
    {
        $kours = new ExRateClass();
        return $kours->getCurrentKours();
    }

    public function getSymbolExrate($cur)
    {
        $kours = new ExRateClass();
        return $kours->getKoursSymbol($cur);
    }

    public function getClient()
    {
        $client = new ClientClass();
        $clientData = $client->getClientData();
        return $clientData[0];
    }

    public function getUser()
    {
        $client = new ClientClass();
        $clientData = $client->getClientData();
        return $clientData[1];
    }

    public function getTpointID()
    {
        $client = new ClientClass();
        return $client->getTpoint();
    }

    public function replaceLang($cont)
    {
        $language = new LangClass();
        $cont = $language->replaceLangData($cont);
        return $cont;
    }

    public function getSiteLink()
    {
        $link = "https://toko.ua/";
        $language = new LangClass();
        $postfix = $language->getLangIDPrefix($this->getLanguage());
        $link .= $postfix;
        return $link;
    }

    public function getLanguage()
    {
        $language = new LangClass();
        return $language->getLanguageData();
    }

//    public function getLangPrefix()
//    {
//        $lang = $this->getLanguage();
//        $pre = "";
//        if ($lang == 1) {
//            $pre = "";
//        }
//        if ($lang == 2) {
//            $pre = "/uk";
//        }
//        if ($lang == 3) {
//            $pre = "/en";
//        }
//        return $pre;
//    }

    public function getLangPostfix($lang_id) {
        $postfix = "RU";
        if ($lang_id == 2) {
            $postfix = "UA";
        }
        if ($lang_id == 3) {
            $postfix = "EN";
        }
        return $postfix;
    }

    public function getManualName($key)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `mcaption` FROM `manual` WHERE `id` = $key;");
        return $db->result($r, 0, "mcaption");
    }

    public function getManualNameCaption($key, $mid)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `mcaption` FROM `manual` WHERE `key` = '$key' AND `mid` = $mid LIMIT 1;");
        return $db->result($r, 0, "mcaption");
    }

    public function getManualOptions($key)
    {
        $db = DbSingleton::getDbm();
        $lang_id = $this->getLanguage();
        $options = "";
        $r = $db->query("SELECT `id` FROM `manual` WHERE `key` = '$key' ORDER BY `mid` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "id");
            $rs = $db->query("SELECT `caption` FROM `A_CUSTOMERS_CATEGORIES` WHERE `manual_id` = $id AND `lang_id` = $lang_id LIMIT 1;");
            $caption = $db->result($rs, 0, "caption");
            if ($caption == "") {
                $caption = $db->result($r, $i - 1, "mcaption");
            }
            $options .= "<option value=\"$id\">$caption</option>";
        }
        return $options;
    }

    public function multiSort()
    {
        $args = func_get_args();
        $c = count($args);
        if ($c < 2) {
            return false;
        }
        $array = array_splice($args, 0, 1);
        $array = $array[0];
        usort($array, function ($a, $b) use ($args) {
            $i = 0;
            $c = count($args);
            $cmp = 0;
            while ($cmp == 0 && $i < $c) {
                $cmp = strcmp($a[$args[$i]], $b[$args[$i]]);
                $i++;
            }
            return $cmp;
        });
        return $array;
    }

    public function getWeekdayAbr($week_day)
    {
        $wks = array('1' => "Пн", '2' => "Вт", '3' => "Ср", '4' => "Чт", '5' => "Пт", '6' => "Сб", '7' => "Нд");
        $wks["$week_day"] = iconv("UTF-8", "Windows-1251", $wks["$week_day"]);
        return $wks["$week_day"];
    }

    public function translit($st)
    {
        $cyr = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
        ];
        $lat = [
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya'
        ];
        $st = str_replace($cyr, $lat, $st);
        return $st;
    }

    /*
     * set random password
     * set 4 numbers
     * */
    public function randomPassword()
    {
        // $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $kol = 4;
        $alphabet = "1234567890";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $kol; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    /*
     * get text offer variables
     * */
    public function getOfferCap($i)
    {
        $cap1 = "{offer_cap}";
        $cap2 = "{offer_pair_cap}";
        $cap3 = "{offer_tenths_cap}";
        $cap = "";
        $mas1 = [1];
        $mas2 = [2, 3, 4];
        $mas3 = [0, 5, 6, 7, 8, 9];
        $mas4 = [11, 12, 13, 14, 15, 16, 17, 18, 19];
        $mod = $i % 10;
        if (in_array($mod, $mas1)) {
            $cap = $cap1;
        }
        if (in_array($mod, $mas2)) {
            $cap = $cap2;
        }
        if (in_array($mod, $mas3) || in_array($i, $mas4)) {
            $cap = $cap3;
        }
        $cap = $this->replaceLang($cap);
        return $cap;
    }
    /*
     * get text offer variables
     * */
    public function getGoodsCap($i)
    {
        $cap1 = "{goods_cap}";
        $cap2 = "{goods_pair_cap}";
        $cap3 = "{goods_tenths_cap}";
        $cap = "";
        $mas1 = [1];
        $mas2 = [2, 3, 4];
        $mas3 = [0, 5, 6, 7, 8, 9];
        $mas4 = [11, 12, 13, 14, 15, 16, 17, 18, 19];
        $mod = $i % 10;
        if (in_array($mod, $mas1)) {
            $cap = $cap1;
        }
        if (in_array($mod, $mas2)) {
            $cap = $cap2;
        }
        if (in_array($mod, $mas3) || in_array($i, $mas4)) {
            $cap = $cap3;
        }
        $cap = $this->replaceLang($cap);
        return $cap;
    }

    /*
     * Check ART_ID in PHOTO
     * */
    public function checkPhoto($ref)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT COUNT(`ART_ID`) as col FROM `T2_PHOTOS` WHERE `ART_ID` = $ref AND `ACTIVE` = 1;");
        $n = intval($db->result($r, 0, "col"));
        return ($n > 0);
    }

    /*
     * Get `Seoshield` H1
     * */
    public function getStaticH1($uri)
    {
        $static_data = include $_SERVER["DOCUMENT_ROOT"] . '/seoshield-client/data/static_meta.cache.php';
        $static_h1 = "";
        if (isset($static_data['//' . $_SERVER["HTTP_HOST"] . $uri])) {
            $static_h1 = $static_data['//' . $_SERVER["HTTP_HOST"] . $uri][2];
        }
        $static_h1 = iconv("UTF-8", "windows-1251", $static_h1);
        return $static_h1;
    }

    /*
     * Associate arrays
     * array_1 + array2
     * */
    public function mergeArray($arr1, $arr2)
    {
        $data = [];
        foreach ($arr1 as $key => $value) {
            if (empty($data[$key])) {
                $data[$key] = [];
            }
            $data[$key] = $value;
        }
        foreach ($arr2 as $key => $value) {
            if (empty($data[$key])) {
                $data[$key] = [];
            }
            $data[$key] = array_unique(array_merge($data[$key], $value));
        }
        return $data;
    }

    /*
     * check art_id in typ_id
     * */
    public function checkT2Link($typ_id, $art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `ART_ID` = $art_id AND `TYP_ID` = $typ_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getCatalogRedirectLink($link, $mfa_link = "", $model_link = "")
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `LINK_TO` FROM `T2_CATALOG_REDIRECT` WHERE `LINK_FROM` LIKE '%$link%' LIMIT 1;");
        $n = $db->num_rows($r);
        $status = 0;
        $redirect_link = "";
        if ($n > 0) {
            $status = 1;
            $redirect_link = $db->result($r, 0, "LINK_TO");
            if (substr($redirect_link, -1) != "/") {
                $redirect_link .= "/";
            }
        }

        if ($mfa_link != "") {
            if (!$this->checkCatalogRedirectFilters($redirect_link)) {
                $redirect_link .= "auto/";
            }
            $redirect_link .= "$mfa_link/";
            if ($model_link != "") {
                $redirect_link .= "$model_link";
            }
        }

        return array("status" => $status, "redirect_link" => $redirect_link);
    }

    /*
     * if status = 1 - link have filters
     * */
    public function checkCatalogRedirectFilters($link, $path = "/catalog/")
    {
        $str_len = strlen($path);
        $str_pos = strpos($link, $path);
        $sub_str = substr($link, $str_pos + $str_len);

        $sub_str_arr = explode("/", $sub_str);
        $sub_str_arr = array_filter($sub_str_arr);

        $status = 0;
        if (count($sub_str_arr) > 1) {
            $status = 1;
        }
        return $status;
    }

}
