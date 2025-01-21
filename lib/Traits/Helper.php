<?php

trait Helper
{

    public $images  = "/images";
    public $uploads = "/uploads";
    public $noPhoto = "/images/no_photo.svg";
    protected $err1 = "{nothing_found}";
    protected $err2 = "{not_specified}";

    /*
     * get cookie `session_id`
     * */
    public function getSessionID(): string
    {
        $cookie_id = $this->getUrlString($_COOKIE["session_id"]);

        if (empty($cookie_id)) {
            $cookie_id = 0;
        }

        return $cookie_id;
    }

    /*
     * get HTML forms
     * */
    public function getHtmlForm($name)
    {
        $form = "";
        $form_htm = RDD . "/tpl/$name.htm";

        if (file_exists($form_htm)) {
            $form = file_get_contents($form_htm);
        }

        return $form;
    }

    public function getHtmlTag($name, $text, $arr = []): string
    {
        $tag = "";

        foreach ($arr as $key => $value) {
            $tag .= "$key='$value'";
        }

        $form = "<$name $tag>$text</$name>";

        if ($name === "input") {
            $form = "<input $tag/>";
        }

        return $form;
    }

    /*
     * validate string URL
     * */
    public function getUrlString($str): string
    {
        return str_replace(
            array("'", "`", ",", '"', "%22", "%27", "%60", "&nbsp;", "&rsquo;", "%20", "%5c", '\\'),
            array("", "", "", "", "", "", "", "", "", " ", "", ""),
        $str);
    }

    public function getUrlString2($str): string
    {
        return str_replace(
            array("'", "`", ",", '"', "&nbsp;", "/"),
            array("", "", "", "", "", ""),
        $str);
    }

    /*
     * validate string
     * */
    public function getNameString($str): string
    {
        return str_replace(
            array("'", "`", '"', "%22", "%27", "%60", "&rsquo;", "%20"),
            array("", "", "", "", "", "", "", " "),
        $str);
    }

    /*
     * validate number URL
     * */
    public function getUrlNumber($number): int
    {
        if (!is_numeric($number)) {
            $number = 0;
        }

        return $number;
    }

    public function getCurrentExRate(): int
    {
        return (new ExRateClass())->getCurrentExRate();
    }

    public function getSymbolExRate($cur): string
    {
        return (new ExRateClass())->getExRateSymbol($cur);
    }

    public function getClient(): int
    {
        $client = new ClientClass();
        $clientData = $client->getClientData();

        return (int)$clientData[0];
    }

    public function getUser(): int
    {
        $client = new ClientClass();
        $clientData = $client->getClientData();

        return (int)$clientData[1];
    }

    public function getTpointID(): int
    {
        return (new ClientClass())->getTpoint();
    }

    public function replaceLang($cont)
    {
        return (new LangClass())->replaceLangData($cont);
    }

    public function getSiteLink(): string
    {
        return "https://toko.ua/" . (new LangClass())->getLangIDPrefix($this->getLanguage());
    }

    public function getLanguage(): int
    {
        return (new LangClass())->getLanguageData();
    }

    public function getOldLanguage($lang_id): int
    {
        return (new LangClass())->getOldLanguage($lang_id);
    }

    public function getLangPostfix($lang_id): string
    {
        $postfix = "RU";

        if ($lang_id === 2) {
            $postfix = "UA";
        }

        if ($lang_id === 3) {
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

    public function getManualOptions($key): string
    {
        $db = DbSingleton::getDbm();
        $lang_id = $this->getLanguage();
        $list = "";

        $r = $db->query("SELECT `id` FROM `manual` WHERE `key` = '$key' ORDER BY `mid`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id         = $db->result($r, $i - 1, "id");
            $rs         = $db->query("SELECT `caption` FROM `A_CUSTOMERS_CATEGORIES` WHERE `manual_id` = $id AND `lang_id` = $lang_id LIMIT 1;");
            $caption    = $db->result($rs, 0, "caption");

            if (empty($caption)) {
                $caption = $db->result($r, $i - 1, "mcaption");
            }

            $list .= str_replace(
                array("{value}", "{name}", "{checked}"),
                array($id, $caption, ""),
            $this->getHtmlForm("helper/select_option"));
        }

        return $list;
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
            while ($cmp === 0 && $i < $c) {
                $cmp = strcmp($a[$args[$i]], $b[$args[$i]]);
                $i++;
            }
            return $cmp;
        });

        return $array;
    }

    public function getWeekdayAbr($week_day): string
    {
        $wks = [
            '1' => "Пн", '2' => "Вт", '3' => "Ср", '4' => "Чт", '5' => "Пт", '6' => "Сб", '7' => "Нд"
        ];
        return $wks[$week_day];
    }

    public function getFormattedTranslatedText($st)
    {
        $cyr = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', 'і', 'ї', 'є',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'І', 'Ї', 'Є'
        ];

        $lat = [
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya', 'i', 'yi', 'ye',
            'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya', 'I', 'Yi', 'Ye'
        ];

        return str_replace($cyr, $lat, $st);
    }

    /*
     * set random password
     * set 4 numbers
     * */
    public function randomPassword(): string
    {
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

    public function getOfferCap($i)
    {
        return $this->extracted($i, "{offer_cap}", "{offer_pair_cap}", "{offer_tenths_cap}");
    }
    public function getGoodsCap($i)
    {
        return $this->extracted($i, "{goods_cap}", "{goods_pair_cap}", "{goods_tenths_cap}");
    }
    
    public function checkPhoto($ref): bool
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT COUNT(`ART_ID`) as col FROM `T2_PHOTOS` WHERE `ART_ID` = $ref AND `ACTIVE` = 1;");
        $n = (int)$db->result($r, 0, "col");

        return ($n > 0);
    }

    /*
     * Get `Seo shield` H1
     * */
    public function getStaticH1($uri)
    {
        $static_h1      = "";
        $static_data    = include $_SERVER["DOCUMENT_ROOT"] . '/seoshield-client/data/static_meta.cache.php';
        $static         = isset($static_data['//' . $_SERVER["HTTP_HOST"] . $uri]);

        if ($static) {
            $static_h1 = $static_data['//' . $_SERVER["HTTP_HOST"] . $uri][2];
        }

        return $static_h1;
    }

    /*
     * check art_id in typ_id
     * */
    public function checkT2Link($typ_id, $art_id): bool
    {
        $art_id = $this->getUrlNumber($art_id);
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `ART_ID` = $art_id AND `TYP_ID` = $typ_id LIMIT 1;");
        $n = $db->num_rows($r);

        return $n !== 0;
    }

    public function getCatalogOldRedirectLink($httpHost): array
    {
        $autoObj = new AutoClass();
        $catalog_exist = new CatalogExistClass();

        $status = 0;

        $redirect_link = $this->getSiteLink() . $httpHost[0] . "/" . $httpHost[1] . "/";

        $group_id = $catalog_exist->getGroupExistId($httpHost[1]);

        if (!empty($httpHost[2]) && $group_id > 0 && $httpHost[2] !== "auto" && strpos($httpHost[2], "brandy=") === false) {

            // 1 - mfa link
            if ($autoObj->getMfaLink($httpHost[2]) > 0) {
                $status = 1;
                $redirect_link .= "auto/" . $httpHost[2] . "/";
                if (!empty($httpHost[3])) {
                    $redirect_link .= $httpHost[3] . "/";
                }
            }

            // 2 - filter link (brands)
            if (strpos($httpHost[2], "brandy=") !== false) {
                $status = 2;
                $redirect_link .= $httpHost[2] . "/";

                if ($autoObj->getMfaLink($httpHost[3]) > 0) {
                    $status = 3;
                    $redirect_link .= $httpHost[3] . "/";

                    if (!empty($httpHost[4])) {
                        $redirect_link .= $httpHost[4] . "/";
                    }
                }
            }
        }

        $redirect_link = rtrim($redirect_link, '/') . '/';

        return array(
            "status"        => $status,
            "redirect_link" => $redirect_link
        );
    }

    public function getCatalogRedirectLink($link, $mfa_link = "", $model_link = ""): array
    {
        $autoObj = new AutoClass();
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `LINK_TO` FROM `T2_CATALOG_REDIRECT` WHERE `LINK_FROM` LIKE '%$link%' LIMIT 1;");
        $n = $db->num_rows($r);
        $status = 0;
        $redirect_link = "";

        if ($n > 0) {
            $status = 1;
            $redirect_link = $db->result($r, 0, "LINK_TO");
            $redirect_link = str_replace("https://toko.ua/", $this->getSiteLink(), $redirect_link);
            if (substr($redirect_link, -1) !== "/") {
                $redirect_link .= "/";
            }
        }

        $mfa_id = $autoObj->getMfaLink($mfa_link);

        if (($mfa_id > 0) && !empty($mfa_link)) {
            if (!$this->checkCatalogRedirectFilters($redirect_link)) {
                $redirect_link .= "auto/";
            }
            $redirect_link .= "$mfa_link/";
            if (!empty($model_link)) {
                $redirect_link .= $model_link;
            }
        }

        $redirect_link = rtrim($redirect_link, '/') . '/';

        return array(
            "status"        => $status,
            "redirect_link" => $redirect_link
        );
    }

    /*
     * if status = 1 - link have filters
     * */
    public function checkCatalogRedirectFilters($link, $path = "/catalog/"): int
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

    public function checkArticleExist($art_id): bool
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT COUNT(`ART_ID`) as count_arts FROM `T2_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
        $n = (int)$db->result($r, 0, "count_arts");

        return ($n > 0);
    }

    public function getBreadCrumbForm($breads): array
    {
        $form = "";
        $script = "";

        if (!empty($breads)) {
            $key            = 0;
            $list           = "";
            $script_list    = "";
            $icon           = "<span> > </span>";

            foreach ($breads as $bread) {
                $key++;
                $name = $bread["name"];
                $link = $bread["link"];

                if ($key !== count($breads)) {
                    $list .= "
                    <li class=\"cat-products-bread__item\" typeof=\"v:Breadcrumb\">
                        <a href=\"$link\" rel=\"v:url\" property=\"v:title\">$name</a>$icon
                    </li>";
                } else {
                    $list .= $name;
                }

                $script_list .= "
                {
                    \"@type\": \"ListItem\",
                    \"position\": $key,
                    \"name\": \"$name\",
                    \"item\": \"$link\"
                },";
            }
            $script_list = rtrim($script_list, ",");

            $form = getHtmlForm("menu/breadcrumbs");
            $form = str_replace("{bread_text}", $list, $form);

            $script = getHtmlForm("menu/breadcrumbs_script");
            $script = str_replace("{bread_text}", $script_list, $script);
        }

        return compact("form", "script");
    }

    public function checkCityLink($city_link): bool
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ID` FROM `SEO_LISTING_CITY` WHERE `LINK_NAME` = '$city_link' LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    public function getCityNameIn($city_link, $prefix = "CITY_NAME_")
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `$prefix$postfix` FROM `SEO_LISTING_CITY` WHERE `LINK_NAME` = '$city_link' LIMIT 1;");

        return $db->result($r, 0, "$prefix$postfix");
    }

    /**
     * @param $i
     * @param string $caption_1
     * @param string $caption_2
     * @param string $caption_3
     * @return array|mixed|string|string[]
     */
    public function extracted($i, string $caption_1, string $caption_2, string $caption_3)
    {
        $caption = "";
        $mas1 = [1];
        $mas2 = [2, 3, 4];
        $mas3 = [0, 5, 6, 7, 8, 9];
        $mas4 = [11, 12, 13, 14, 15, 16, 17, 18, 19];
        $mod = $i % 10;

        if (in_array($mod, $mas1)) {
            $caption = $caption_1;
        }
        if (in_array($mod, $mas2)) {
            $caption = $caption_2;
        }
        if (in_array($mod, $mas3) || in_array($i, $mas4, true)) {
            $caption = $caption_3;
        }
        return $this->replaceLang($caption);
    }

}
