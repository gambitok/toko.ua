<?php

class ExRateClass
{

    use Helper;
    use Variables;

    private $usdRate;
    private $euroRate;

    /*
     * get current currency_id
     * */
    public function getCurrentKours()
    {
        session_start();
        $currency = $this->getUrlNumber($_COOKIE["currency"]);
        if ($currency == "" || $currency == 0) {
            $currency = $this->getUrlNumber($_SESSION["currency"]);
        }
        if ($currency == "" || $currency == 0) {
            $currency = 1;
        }
        return $currency;
    }

    /*
     * get currency_id
     * from currency text value
     * */
    public function getKours($val)
    {
        $db = DbSingleton::getDbm();
        if ($val == "dollar") {
            if ($this->usdRate === null) {
                $r = $db->query("SELECT `kours_value` FROM `J_KOURS` WHERE `cash_id`=2 AND `in_use`=1 LIMIT 1;");
                $this->usdRate = number_format($db->result($r, 0, "kours_value"), 2, '.', '');
            }
            return $this->usdRate;
        } elseif ($val == "euro") {
            if ($this->euroRate === null) {
                $r = $db->query("SELECT `kours_value` FROM `J_KOURS` WHERE `cash_id`=3 AND `in_use`=1 LIMIT 1;");
                $this->euroRate = number_format($db->result($r, 0, "kours_value"), 2, '.', '');
            }
            return $this->euroRate;
        } else {
            return 0;
        }
    }

    /*
     * get exrate price
     * from price & currency_id
     * */
    public function getKoursPrice($price, $cur)
    {
        if ($cur == 2) {
            $price = $price / $this->getKours("dollar");
            $price = number_format($price, 2, '.', '');
        } elseif ($cur == 3) {
            $price = $price / $this->getKours("euro");
            $price = number_format($price, 2, '.', '');
        } elseif (is_float($price)) {
            $price = number_format($price, 2, '.', '');
        }
        return $price;
    }

    /*
     * get exrate price from usa
     * from price & currency_id
     * */
    public function getKoursFromUSA($price, $cur)
    {
        if ($cur == 1) {
            $price = $price * $this->getKours("dollar");
            $price = number_format($price, 2, '.', '');
        } elseif ($cur == 3) {
            $price = ($price * $this->getKours("dollar")) / $this->getKours("euro");
            $price = number_format($price, 2, '.', '');
        } elseif (is_float($price)) {
            $price = number_format($price, 2, '.', '');
        }
        return $price;
    }

    /*
     * get exrate price from usd
     * from price & currency_id
     * */
    public function getKoursFromUAH($price, $cur)
    {
        if ($cur == 2) {
            $price = $price / $this->getKours("dollar");
            $price = number_format($price, 2, '.', '');
        } elseif ($cur == 3) {
            $price = $price / $this->getKours("euro");
            $price = number_format($price, 2, '.', '');
        } else {
            $price = number_format($price, 2, '.', '');
        }
        return $price;
    }

    /*
     * get exrate caption
     * from currency_id
     * */
    public function getKoursCaption($cur)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `abr` FROM `CASH` WHERE `id`='$cur' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $result = $db->result($r, 0, "abr");
        } else {
            $result = "{uah_cap}";
        }
        return $result;
    }

    /*
     * get exrate caption lang
     * from currency_id
     * */
    public function getKoursCaptionLang($cur)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `abr2` FROM `CASH` WHERE `id`='$cur' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $result = $db->result($r, 0, "abr2");
        } else {
            $result = "{uah_cap}";
        }
        return $result;
    }

    /*
     * get exrate symbol
     * from currency_id
     * */
    public function getKoursSymbol($cur)
    {
        switch ($cur) {
            case 2:
            {
                $result = "$";
                break;
            }
            case 3:
            {
                $result = "€";
                break;
            }
            case 1:
            default:
            {
                $result = "{uah_cap}";
                break;
            }
        }
        return $result;
    }

}