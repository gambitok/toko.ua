<?php

class LangClass
{

    use Helper;

    public $default_lang_id = 1;

    private static $langVariables;
    private static $langNames;

    /*
     * get language ID
     * */
    public function getLanguageData(): int
    {
        $lang_id = $this->getUrlNumber($_SESSION["lang_id"]);

        if (empty($lang_id)) {
            $lang_id = $this->getUrlNumber($_COOKIE["lang_id"]);

            if (empty($lang_id)) {
                $lang_id = $this->default_lang_id;
                $_SESSION["lang_id"]    = $lang_id;
                $_COOKIE["lang_id"]     = $lang_id;
            } else {
                $_SESSION["lang_id"] = $lang_id;
            }
        } else {
            $_COOKIE["lang_id"] = $lang_id;
        }

        return $lang_id;
    }

    /*
     * get language old ids
     * */
    public function getOldLanguage($lang_id): int
    {
        $lang_id = (int)$lang_id;

        if ($lang_id === 1) {
            $lang_id = 16;
        }
        if ($lang_id === 2) {
            $lang_id = 41;
        }
        if ($lang_id === 3) {
            $lang_id = 4;
        }

        return $lang_id;
    }

    /*
     * get language caption
     * */
    public function getLangCap($lang_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `abr` FROM `new_lang` WHERE `id` = $lang_id LIMIT 1;");

        return $db->result($r, 0, "abr");
    }

    /*
     * get language select list
     * */
    public function getLanguageMenuList($sel_id): string
    {
        $db = DbSingleton::getTokoDb();

        $list = "";
        $link = ltrim($_SERVER["REQUEST_URI"], "/");
        $link = "/" . $link;
        $link = str_replace(array("/uk/", "/en/"), "", $link);

        $r = $db->query("SELECT `id`, `abr` FROM `new_lang` WHERE 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $lang_id    = (int)$db->result($r, $i - 1, "id");
            $lang_abr   = $db->result($r, $i - 1, "abr");
            $active     = ($lang_id === $sel_id) ? "menu-language__item-active" : "";
            $url        = "toko.ua/" . $this->getLangIDPrefix($lang_id) . $link;
            $url        = str_replace("//", "/", $url);
            $url        = "https://" . $url;

            $list .= "
            <div class=\"menu-language__item $active\">
                <a href=\"$url\">$lang_abr</a>
            </div>";
        }

        return $list;
    }

    public function getLangIDPrefix($lang_id): string
    {
        $pre = "";
        $lang_id = (int)$lang_id;

        if ($lang_id === 2) {
            $pre = "uk/";
        }
        if ($lang_id === 3) {
            $pre = "en/";
        }

        return $pre;
    }

    /*
     * set site language
     * by LANG_ID
     * */
    public function setLangID($lang_id): bool
    {
        $lang_id = $this->getUrlNumber($lang_id);
        $_SESSION["lang_id"] = $lang_id;
        setcookie("lang_id", $lang_id, time() + (86400 * 30), "/");

        return true;
    }

    /*
     * set site language
     * return prefix
     * */
    public function setSiteLang($lang_id): string
    {
        $this->setLangID($lang_id);

        return "https://toko.ua/" . $this->getLangIDPrefix($lang_id);
    }

    /*
     * get language name
     * from code value
     * */
    public function getLanguageName($code)
    {
        $db = DbSingleton::getTokoDb();
        $lang = $this->getLanguageData();
        if (self::$langNames === null) {
            $r = $db->query("SELECT l.caption, lw.variable 
            FROM `new_lang_wdv` l
                LEFT OUTER JOIN `new_lang_wd` lw ON (lw.id = l.wd)
            WHERE l.lang_id = $lang;");
            $result = mysqli_fetch_all($r, MYSQLI_ASSOC);
            self::$langNames = array_column($result, 'caption', 'variable');
        }

        return self::$langNames[$code];
    }

    /*
     * replace language text
     * */
    public function replaceLangData($cont)
    {
        $db = DbSingleton::getTokoDb();
        if (self::$langVariables === null) {
            $r = $db->query("SELECT `variable` FROM `new_lang_wd`;");
            self::$langVariables = array_column(mysqli_fetch_all($r), 0);
        }
        foreach (self::$langVariables as $langVariable) {
            $cont = str_replace("{" . $langVariable . "}", $this->getLanguageName($langVariable), $cont);
        }

        return $cont;
    }

    /*
     * replace language text message
     * */
    public function changeLangAlert($message, $title): array
    {
        $message    = $this->getNameString($message);
        $title      = $this->getNameString($title);
        $message    = $this->replaceLangData($message);
        $title      = $this->replaceLangData($title);

        return array($message, $title);
    }

    /*
     * replace language in js
     * */
    public function changeLangJs($text)
    {
        $text = $this->getNameString($text);
        $text = $this->replaceLangData($text);

        return $text;
    }

}
