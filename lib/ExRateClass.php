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
    public function getCurrentKours(): int
    {
        session_start();
        $cur = $this->getUrlNumber($_COOKIE["currency"]);

        if (empty($cur)) {
            $cur = $this->getUrlNumber($_SESSION["currency"]);
        }

        if (empty($cur)) {
            $cur = 1;
        }

        return $cur;
    }

    /*
     * get currency_id
     * from currency text value
     * */
    public function getKours($val)
    {
        $db = DbSingleton::getDbm();
        if ($val === "dollar") {
            if ($this->usdRate === null) {
                $r = $db->query("SELECT `kours_value` FROM `J_KOURS` WHERE `cash_id` = 2 AND `in_use` = 1 LIMIT 1;");
                $this->usdRate = number_format($db->result($r, 0, "kours_value"), 2, '.', '');
            }
            return $this->usdRate;
        }

        if ($val === "euro") {
            if ($this->euroRate === null) {
                $r = $db->query("SELECT `kours_value` FROM `J_KOURS` WHERE `cash_id` = 3 AND `in_use` = 1 LIMIT 1;");
                $this->euroRate = number_format($db->result($r, 0, "kours_value"), 2, '.', '');
            }
            return $this->euroRate;
        }

        return 0;
    }

    /*
     * get exchange rate price
     * from price & currency_id
     * */
    public function getKoursPrice($price, $cur): float
    {
        $cur = (int)$cur;
        if ($cur === 2) {
            $price /= $this->getKours("dollar");
            $price = number_format($price, 2, '.', '');
        } elseif ($cur === 3) {
            $price /= $this->getKours("euro");
            $price = number_format($price, 2, '.', '');
        } elseif (is_float($price)) {
            $price = number_format($price, 2, '.', '');
        }

        return (float)$price;
    }

    /*
     * get exchange rate price from usa
     * from price & currency_id
     * */
    public function getKoursFromUSA($price, $cur): string
    {
        $cur = (int)$cur;
        if ($cur === 1) {
            $price *= $this->getKours("dollar");
            $price = number_format($price, 2, '.', '');
        } elseif ($cur === 3) {
            $price = ($price * $this->getKours("dollar")) / $this->getKours("euro");
            $price = number_format($price, 2, '.', '');
        } elseif (is_float($price)) {
            $price = number_format($price, 2, '.', '');
        }

        return $price;
    }

    /*
     * get exchange rate price from usd
     * from price & currency_id
     * */
    public function getKoursFromUAH($price, $cur): string
    {
        $cur = (int)$cur;
        if ($cur === 2) {
            $price /= $this->getKours("dollar");
            $price = number_format($price, 2, '.', '');
        } elseif ($cur === 3) {
            $price /= $this->getKours("euro");
            $price = number_format($price, 2, '.', '');
        } else {
            $price = number_format($price, 2, '.', '');
        }

        return $price;
    }

    /*
     * get exchange rate caption
     * from currency_id
     * */
    public function getKoursCaption($cur)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `abr` FROM `CASH` WHERE `id` = $cur LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0) ? $db->result($r, 0, "abr") : "{uah_cap}";
    }

    /*
     * get exchange rate caption lang
     * from currency_id
     * */
    public function getKoursCaptionLang($cur)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `abr2` FROM `CASH` WHERE `id` = $cur LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0) ? $db->result($r, 0, "abr2") : "{uah_cap}";
    }

    /*
     * get exchange rate symbol
     * from currency_id
     * */
    public function getKoursSymbol($cur): string
    {
        $result = "{uah_cap}";
        if ($cur === 2) {
            $result = "$";
        }
        if ($cur === 3) {
            $result = "€";
        }

        return $result;
    }

}