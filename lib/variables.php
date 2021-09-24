<?php

trait Variables
{

    /*
     * get secret article
     * add zero to first digit in string
     * */
//    public function getSecretString($str)
//    {
//        preg_match('/\d+/', $str, $matches);
//        $pos = strpos($str, $matches[0]);
//        return substr_replace($str, '0', $pos, 0);
//    }

    /*
     * Format Article
     * */
    public function getFormatAticle($name)
    {
        $name = strtolower($name);
        return str_replace(str_split('.,+-\/:*?"<>| '), "", $name);
    }

    /*
     * Format Brand
     * */
    public function getFormatBrand($brand)
    {
        $format_brand = str_replace("/", "-", $brand);
        $format_brand = str_replace(" ", "%20", $format_brand);
        return $format_brand;
    }

    /*==== ARTICLE_ID ================================================================================================*/

    /*
     * ART_ID => BRAND_ID
     * */
    public function getArticleBrand($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $brand_id = 0;
        if ($art_id > 0) {
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
            $brand_id = $db->result($r, 0, "BRAND_ID");
        }
        return $brand_id;
    }

    /*
     * ART_ID => ARTICLE_NAME
     * */
    public function getArticleName($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $name = "";
        if ($art_id > 0) {
            $r = $db->query("SELECT `NAME` FROM `T2_NAMES` WHERE `ART_ID` = $art_id AND `LANG_ID` = 16 LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $name = $db->result($r, 0, "NAME");
            }
        }
        if ($name == "") {
            $name = $this->replaceLang("{details_name_cap}");
        }
        return $name;
    }

    /*
     * ART_ID => ARTICLE_NR_SEARCH
     * */
    public function getArticleSearch($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ARTICLE_NR_SEARCH` FROM `T2_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
        return $db->result($r, 0, "ARTICLE_NR_SEARCH");
    }

    /*
     * ART_ID => ARTICLE_NR_DISPL
     * */
    public function getArticleDispl($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ARTICLE_NR_DISPL` FROM `T2_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
        return $db->result($r, 0, "ARTICLE_NR_DISPL");
    }

    public function getArticleGroupExist($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $group_id = 0;
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_ARTS_EXIST` WHERE `ART_ID` = $art_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $group_id = $db->result($r, 0, "GROUP_ID");
        }
        return $group_id;
    }

    /*
     * ART_ID => BARCODE
     * */
    public function getBarcode($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BARCODE` FROM `T2_BARCODES` WHERE `ART_ID` = $art_id LIMIT 1;");
        $barcode = $db->result($r, 0, "BARCODE");
        if ($barcode == "") {
            $r = $db->query("SELECT MAX(`BARCODE`) as max_barcode FROM `T2_BARCODES`;");
            $barcode = $db->result($r, 0, "max_barcode") + 0;
        }
        return $barcode;
    }

    /*
     * ART_ID + STORAGE => STOCK (PRICE LIST)
     * */
    public function getStockStorage($art_id, $storage_id)
    {
        $db = DbSingleton::getTokoDb();
        if (empty($storage_id)) {
            $storage_id = 0;
        }
        $r = $db->query("SELECT SUM(`AMOUNT`) as summ_amount FROM `T2_ARTICLES_STRORAGE`
        WHERE `ART_ID` = $art_id AND `STORAGE_ID` IN ($storage_id);");
        $n = $db->num_rows($r);
        return ($n > 0) ? intval($db->result($r, 0, "summ_amount")) : 0;
    }

    /*==== ARTICLE_NR ================================================================================================*/

    /*
     * ARTICLE_NR_SEARCH => ARTICLE_NR_DISPL
     * */
    public function getArtDispl($article_nr_search, $brand_nr_search = 0)
    {
        $db = DbSingleton::getTokoDb();
        $article_nr_displ = $article_nr_search;
        $where_brand = "";
        if ($brand_nr_search > 0) {
            $where_brand = "AND `BRAND_ID` = $brand_nr_search";
        }
        $r = $db->query("SELECT `ARTICLE_NR_DISPL` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' $where_brand LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $article_nr_displ = $db->result($r, 0, "ARTICLE_NR_DISPL");
        }
        return $article_nr_displ;
    }

    /*
     * ARTICLE_NR_SEARCH + BRAND_ID => ART_ID
     * */
    public function getArticleId($article_nr_search, $brand_id)
    {
        $brand_id = $this->getUrlNumber($brand_id);
        $article_nr_search = $this->getUrlString($article_nr_search);
        $db = DbSingleton::getTokoDb();
        $art_id = 0;
        $where_brand = ($brand_id > 0) ? " AND `BRAND_ID` = $brand_id" : "";
        if ($article_nr_search != "") {
            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' $where_brand;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $art_id = $db->result($r, 0, "ART_ID");
            }
        }
        return $art_id;
    }

    /*
     * ARTICLE_NR_SEARCH => ART_ID
     * */
    public function getArtID($article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $art_id = 0;
        $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $art_id = $db->result($r, 0, "ART_ID");
        }
        return $art_id;
    }

    /*==== BRAND =====================================================================================================*/

    /*
     * get brand name
     * from BRAND_ID
     * */
    public function getBrandName($brand_id)
    {
        $brand_id = $this->getUrlNumber($brand_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_NAME` FROM `T2_BRANDS` WHERE `BRAND_ID` = $brand_id LIMIT 1;");
        $n = $db->num_rows($r);
        return ($n == 1) ? $db->result($r, 0, "BRAND_NAME") : 0;
    }

    public function getBrandNameLink($brand)
    {
        $brand_id = 0;

        $brand = $this->getUrlString($brand);
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE BINARY `BRAND_NAME` = BINARY '$brand' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $brand_id = $db->result($r, 0, "BRAND_ID");
        }

        return $brand_id;
    }

    /*
     * get brand link
     * from BRAND_ID
     * */
    public function getBrandLink($brand_id)
    {
        $brand_id = $this->getUrlNumber($brand_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_LINK` FROM `T2_BRANDS` WHERE `BRAND_ID` = $brand_id LIMIT 1;");
        $n = $db->num_rows($r);
        return ($n == 1) ? $db->result($r, 0, "BRAND_LINK") : 0;
    }

    /*==== VARIABLES =================================================================================================*/

    /*
     * get city name
     * from CITY_ID
     * */
    public function getCityName($city_id)
    {
        $city_id = $this->getUrlNumber($city_id);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `CITY_NAME` FROM `T2_CITY` WHERE `CITY_ID` = $city_id LIMIT 1;");
        return $db->result($r, 0, "CITY_NAME");
    }

    /*
     * get sale invoice name
     * from INVOICE_ID
     * */
    public function getSaleInvoiceName($invoice_id)
    {
        $db = DbSingleton::getDbm();
        $invoice_id = $this->getUrlNumber($invoice_id);
        $name = "";
        $r = $db->query("SELECT `prefix`, `doc_nom` FROM `J_SALE_INVOICE` WHERE `status` = 1 AND `id` = $invoice_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $name = $db->result($r, 0, "prefix") . "-" . $db->result($r, 0, "doc_nom");
        }
        return $name;
    }

    /*
     * get jpay name
     * from JPAY_ID
     * */
    public function getJPayName($jpay_id)
    {
        $db = DbSingleton::getDbm();
        $name = "";
        $pay_type_id = 0;
        $jpay_id = $this->getUrlNumber($jpay_id);
        $r = $db->query("SELECT p.*, m.mcaption as pay_type_name 
        FROM `J_PAY` p 
            LEFT JOIN `manual` m ON (m.id = p.pay_type_id AND m.`key` = 'pay_type_id') 
        WHERE p.status = 1 AND p.id = $jpay_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $pay_type_id = $db->result($r, 0, "pay_type_id");
            $name = $db->result($r, 0, "pay_type_name") . " #" . $db->result($r, 0, "doc_nom");
        }
        return array($pay_type_id, $name);
    }

    /*
     * get fuel name
     * from FUEL_ID
     * */
    public function getFuelName($fuel_id)
    {
        $fuel_id = $this->getUrlNumber($fuel_id);
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        if ($lang_id == 1) {
            $lang_id = 16;
        }
        if ($lang_id == 2) {
            $lang_id = 41;
        }
        if ($lang_id == 3) {
            $lang_id = 4;
        }
        $r = $db->query("SELECT `FUEL` FROM `T_types_fuel` WHERE `FUEL_ID` = $fuel_id AND `LANG_ID` = $lang_id LIMIT 1;");
        return $db->result($r, 0, "FUEL");
    }

    /*
     * get back clients name
     * from BACK_ID
     * */
    public function getBackClientsName($back_id)
    {
        $back_id = $this->getUrlNumber($back_id);
        $db = DbSingleton::getDbm();
        $prefix = $doc_nom = "";
        $r = $db->query("SELECT `prefix`, `doc_nom` FROM `J_BACK_CLIENTS` WHERE `id` = $back_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $prefix = $db->result($r, 0, "prefix");
            $doc_nom = $db->result($r, 0, "doc_nom");
        }
        return $prefix . "-" . $doc_nom;
    }

    /*
     * get catalog search link
     * from ARTICLE_NR_SEARCH
     * */
    public function getCatalogueLink($article_nr_search)
    {
        $article_nr_search = $this->getUrlString($article_nr_search);
        $brand_link = $this->getCatalogueBrandLink2($article_nr_search);
        $link = $this->getSiteLink() . "$this->search_link/$article_nr_search/$brand_link";
        if ($brand_link != "") {
            $link .= "/";
        }
        return $link;
    }

    public function getCatalogueBrandLink2($article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $brand_link = "";
        $r = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search';");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $brand_id = $db->result($r, 0, "BRAND_ID");
            $r = $db->query("SELECT `BRAND_LINK` FROM `T2_BRANDS` WHERE `BRAND_ID` = $brand_id LIMIT 1;");
            $brand_link = $db->result($r, 0, "BRAND_LINK");
        }
        return $brand_link;
    }

    public function getFiltersSearch($brand_filter)
    {
        if ($brand_filter != "") {
            $brand_filter = str_replace("'", "", $brand_filter);
            $where_brands = " AND t2a.BRAND_ID IN ($brand_filter) ";
        } else {
            $where_brands = "";
        }
        return $where_brands;
    }

    /*
     * get delivery name
     * from DELIVERY_ID
     * */
    public function getDeliveryName($delivery_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `T2_DELIVERY` WHERE `ID` = $delivery_id LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    /*
     * get department express name
     * from DELIVERY_ID
     * */
    public function getDepartmentExpressName($delivery_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `T2_DELIVERY_EXPRESS` WHERE `ID` = $delivery_id LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    /*
     * get payment name
     * from PAYMENT_ID
     * */
    public function getPaymentName($payment_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `T2_PAYMENT` WHERE `ID` = $payment_id LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    public function getSearchMessages()
    {
        $error = "<h5 class=\"error_message\">$this->err1</h5>";
        $list = "";
        $jsFilterModel = "catalogueFilter();";
        return array($error, $jsFilterModel, $list);
    }

    /*
     * get BRAND_ID
     * from BRAND_LINK
     * */
    public function getCatalogueBrandID($brand_link)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE `BRAND_LINK` = '$brand_link' LIMIT 1;");
        $brand_id = $db->result($r, 0, "BRAND_ID");
        $brand_id = $this->getUrlNumber($brand_id);
        return $brand_id;
    }

    /*
     * get Cookie Car
     * */
    public function getCookieAuto()
    {
        $auto_typ_id = $this->getUrlNumber($_COOKIE["auto_typ_id"]);
        if ($auto_typ_id > 0 && $auto_typ_id != "") {
            $typ_id = $auto_typ_id;
        } else {
            $typ_id = "";
        }
        return $typ_id;
    }

    public function formatArticleName($text, $max_symbols = 36)
    {
        $dots = "...";
        if (strlen($text) > $max_symbols) {
            $format_text = substr($text, 0, $max_symbols - strlen($dots)) . $dots;
        } else {
            $format_text = $text;
        }
        return $format_text;
    }

}