<?php

class LangClass {

    private static $langVariables;
    private static $langNames;

    function getLanguage() {
        if ($_SESSION["lang"] == "" || $_SESSION["lang"] == 0) {
            $_SESSION["lang"] = 1;
        }
        $lang = $_SESSION["lang"];
        return $lang;
    }

    function getOldLanguage($lang_id) {
        if ($lang_id == 1) {
            $lang_id = 16;
        }
        if ($lang_id == 2) {
            $lang_id = 41;
        }
        if ($lang_id == 3) {
            $lang_id = 4;
        }
        return $lang_id;
    }

    function getTexCapLanguage($lang_id) {
        $cap = "RU";
        if ($lang_id == 2) {
            $cap = "UA";
        }
        if ($lang_id == 3) {
            $cap = "EN";
        }
        return $cap;
    }

    function getLangCap($lang_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `new_lang` WHERE `id`='$lang_id' LIMIT 1;");
        $lang_name = $db->result($r,0,"abr");
        return $lang_name;
    }

    function getLanguageSelectList($sel_id) { $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT * FROM `new_lang`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $lang_id = $db->result($r,$i-1,"id");
            $lang_abr = $db->result($r,$i-1,"abr");
            $active = ($lang_id == $sel_id) ? "active" : "";
            $list .= "<a class=\"dropdown-item $active\" onclick=\"setSiteLang($lang_id);\">$lang_abr</a>";
        }
        return $list;
    }

    function getLangPrefix() {
        session_start();
        $lang = $_SESSION["lang"];
        $pre = "";
        if ($lang == 1) {
            $pre = "";
        }
        if ($lang == 2) {
            $pre = "/uk";
        }
        if ($lang == 3) {
            $pre = "/en";
        }
        return $pre;
    }

//    function setLanguage($id) {
//        $_SESSION["lang"] = $id;
//        return $_SESSION["lang"];
//    }

    function setSiteLang($id) {
        $_SESSION["lang"] = $id;
        $prefix = $this->getLangPrefix();
        return $prefix;
    }

    function getLanguageName($code) { $db = DbSingleton::getTokoDb();
        $lang = $this->getLanguage();
        if (self::$langNames === null) {
            $r = $db->query("SELECT l.caption, lw.variable 
            FROM `new_lang_wdv` l
                LEFT OUTER JOIN `new_lang_wd` lw ON lw.id=l.wd
            WHERE l.lang_id='$lang';");
            $result = mysqli_fetch_all($r, MYSQLI_ASSOC);
            self::$langNames = array_column($result, 'caption', 'variable');
        }
        return self::$langNames[$code];
    }

    function replaceLang($cont) { $db = DbSingleton::getTokoDb();
        if (self::$langVariables === null) {
            $r = $db->query("SELECT `variable` FROM `new_lang_wd`;");
            self::$langVariables = array_column(mysqli_fetch_all($r), 0);
        }
        foreach (self::$langVariables as $langVariable) {
            $cont = str_replace("{".$langVariable."}", $this->getLanguageName($langVariable), $cont);
        }
        return $cont;
    }

    function changeLangAlert($message, $title) {
        $message = $this->replaceLang($message);
        $title = $this->replaceLang($title);
        return array($message, $title);
    }

    function changeLangJs($text) {
        $text = $this->replaceLang($text);
        return $text;
    }
	
}
