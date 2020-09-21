<?php

class ExRateClass {

    private $usdRate;
    private $euroRate;

    function getCurrentKours() {
        session_start();
        $currency = $_COOKIE["currency"];
        if ($currency=="" || $currency==0) $currency = $_SESSION["currency"];
        if ($currency=="" || $currency==0) $currency = 1;
        return $currency;
    }

    function getKours($val) { $db = DbSingleton::getDbm();
        if ($val=="dollar") {
            if ($this->usdRate === null) {
                $r = $db->query("SELECT `kours_value` FROM `J_KOURS` WHERE `cash_id`=2 AND `in_use`=1 LIMIT 1;");
                $this->usdRate = number_format($db->result($r, 0, "kours_value"), 2, '.', '');
            }
            return $this->usdRate;
        } elseif ($val=="euro") {
            if ($this->euroRate === null) {
                $r = $db->query("SELECT `kours_value` FROM `J_KOURS` WHERE `cash_id`=3 AND `in_use`=1 LIMIT 1;");
                $this->euroRate = number_format($db->result($r, 0, "kours_value"), 2, '.', '');
            }
            return $this->euroRate;
        } else {
            return 0;
        }
    }

    function getKoursPrice($price, $cur) {
        if ($cur==2) {$price = $price / $this->getKours("dollar"); $price = number_format($price, 2, '.', ''); } else
        if ($cur==3) {$price = $price / $this->getKours("euro"); $price = number_format($price, 2, '.', ''); } else
        if (is_float($price)) $price = number_format($price, 2, '.', '');
        return $price;
    }

    function getKoursFromUSA($price, $cur) {
        if ($cur==1) {$price = $price * $this->getKours("dollar"); $price = number_format($price, 2, '.', ''); } else
        if ($cur==3) {$price = ($price * $this->getKours("dollar")) / $this->getKours("euro"); $price = number_format($price, 2, '.', ''); } else
        if (is_float($price)) $price = number_format($price, 2, '.', '');
        return $price;
    }

    function getKoursFromUAH($price, $cur) {
        if ($cur==2) {$price = $price / $this->getKours("dollar"); $price = number_format($price, 2, '.', ''); } else
        if ($cur==3) {$price = $price / $this->getKours("euro"); $price = number_format($price, 2, '.', ''); } else
        $price = number_format($price, 2, '.', '');
        return $price;
    }

    function getKoursCaption($cur) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `abr` FROM `CASH` WHERE `id`='$cur' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $result = $db->result($r, 0, "abr"); else $result = "{uah_cap}";
        return $result;
    }

    function getKoursCaptionLang($cur) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `abr2` FROM `CASH` WHERE `id`='$cur' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $result = $db->result($r, 0, "abr2"); else $result = "{uah_cap}";
        return $result;
    }

    function getKoursSymbol($cur) {
        switch ($cur) {
            case 1:  { $result = "{uah_cap}"; break; }
            case 2:  { $result = "$"; break; }
            case 3:  { $result = "€"; break; }
            default: { $result = "{uah_cap}"; break; }
        }
        return $result;
    }
	
}