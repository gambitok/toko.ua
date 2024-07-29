<?php

trait Variables
{
    /*
     * Format Article
     * */
    public function getFormatArticle($name)
    {
        $name = strtolower($name);
        return str_replace(str_split('.,+-\/:*?"<>| '), "", $name);
    }

    /*
     * Format Brand
     * */
    public function getFormatBrand($brand)
    {
        return str_replace(array("/", " "), array("-", "%20"), $brand);
    }

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
        $lang_id = $this->getOldLanguage($this->getLanguage());

        if ($art_id > 0) {
            $r = $db->query("SELECT `NAME` FROM `T2_NAMES` WHERE `ART_ID` = $art_id AND `LANG_ID` = $lang_id LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $name = $db->result($r, 0, "NAME");
            }
        }
        if ($name === "") {
            $name = $this->replaceLang("{details_name_cap}");
        }

        return $name;
    }

    /*
     * ART_ID => ARTICLE_NAME
     * */
    public function getArticleNameLang($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $name = "";
        $language = new LangClass();
        $lang_id = $language->getOldLanguage($this->getLanguage());

        if ($art_id > 0) {
            $r = $db->query("SELECT `NAME` FROM `T2_NAMES` WHERE `ART_ID` = $art_id AND `LANG_ID` = $lang_id LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $name = $db->result($r, 0, "NAME");
            }
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
     * ART_ID => ARTICLE_NR_DISPLAY
     * */
    public function getArticleDisplay($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
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

        if ($barcode === "") {
            $r = $db->query("SELECT MAX(`BARCODE`) as max_barcode FROM `T2_BARCODES`;");
            $barcode = $db->result($r, 0, "max_barcode") + 0;
        }
        
        return $barcode;
    }

    /*
     * ART_ID + STORAGE => STOCK (PRICE LIST)
     * */
    public function getStockStorage($art_id, $storage_id): int
    {
        $db = DbSingleton::getTokoDb();
        if (empty($storage_id)) {
            $storage_id = 0;
        }

        $r = $db->query("SELECT SUM(`AMOUNT`) as summ_amount FROM `T2_ARTICLES_STRORAGE`
        WHERE `ART_ID` = $art_id AND `STORAGE_ID` IN ($storage_id);");
        $n = $db->num_rows($r);
        
        return ($n > 0) ? (int)$db->result($r, 0, "summ_amount") : 0;
    }

    /*
     * ARTICLE_NR_SEARCH => ARTICLE_NR_DISPLAY
     * */
    public function getArtDisplay($article_nr_search, $brand_nr_search = 0): array
    {
        $db = DbSingleton::getTokoDb();
        $article_nr_display = $article_nr_search;
        $brand_id = $brand_nr_search;
        $art_id = 0;

        $where_brand = "";
        if ($brand_nr_search > 0) {
            $where_brand = "AND `BRAND_ID` = $brand_nr_search";
        }

        $r = $db->query("SELECT `ART_ID`, `ARTICLE_NR_DISPL` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' $where_brand LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $art_id = $db->result($r, 0, "ART_ID");
            $article_nr_display = $db->result($r, 0, "ARTICLE_NR_DISPL");
        }

        if ($n === 0) {
            $r2 = $db->query("SELECT  `ART_ID`, `DISPLAY_NR` FROM `T2_CROSS` WHERE `SEARCH_NUMBER` = '$article_nr_search' $where_brand LIMIT 1;");
            $n2 = $db->num_rows($r2);
            if ($n2 > 0) {
                $art_id = $db->result($r2, 0, "ART_ID");
                $article_nr_display = $db->result($r2, 0, "DISPLAY_NR");
            }
        }

        $brand = $this->getBrandName($brand_id);

        return array("art" => $article_nr_display, "brand" => $brand, "brand_id" => $brand_id, "art_id" => $art_id);
    }

    /*
     * ARTICLE_NR_SEARCH + BRAND_ID => ART_ID
     * */
    public function getArticleId($article_nr_search, $brand_id): int
    {
        $brand_id = $this->getUrlNumber($brand_id);
        $article_nr_search = $this->getUrlString($article_nr_search);
        $db = DbSingleton::getTokoDb();
        $art_id = 0;
        $where_brand = ($brand_id > 0) ? " AND `BRAND_ID` = $brand_id" : "";

        if ($article_nr_search !== "") {
            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' $where_brand;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $art_id = (int)$db->result($r, 0, "ART_ID");
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
        return ($n === 1) ? $db->result($r, 0, "BRAND_NAME") : 0;
    }

    public function getBrandNameLink($brand)
    {
        $brand_id = 0;
        $brand = $this->getUrlString($brand);
        if ($brand !== "") {
            $db = DbSingleton::getTokoDb();
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE BINARY `BRAND_LINK` = BINARY '$brand' LIMIT 1;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $brand_id = $db->result($r, 0, "BRAND_ID");
            }
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
        
        return ($n === 1) ? $db->result($r, 0, "BRAND_LINK") : 0;
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
    public function getSaleInvoiceName($invoice_id): string
    {
        $invoice_id = $this->getUrlNumber($invoice_id);
        $db = DbSingleton::getDbm();
        $name = "";
        $r = $db->query("SELECT `prefix`, `doc_nom` FROM `J_SALE_INVOICE` WHERE `status` = 1 AND `id` = $invoice_id LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $name = $db->result($r, 0, "prefix") . "-" . $db->result($r, 0, "doc_nom");
        }
        
        return $name;
    }

    /*
     * get pay name
     * from PAY_ID
     * */
    public function getJPayName($j_pay_id): array
    {
        $j_pay_id = $this->getUrlNumber($j_pay_id);
        $db = DbSingleton::getDbm();
        $name = "";
        $pay_type_id = 0;
        
        $r = $db->query("SELECT p.*, m.mcaption as pay_type_name 
        FROM `J_PAY` p 
            LEFT JOIN `manual` m ON (m.id = p.pay_type_id AND m.`key` = 'pay_type_id') 
        WHERE p.status = 1 AND p.id = $j_pay_id LIMIT 1;");
        $n = (int)$db->num_rows($r);

        if ($n === 1) {
            $pay_type_id    = (int)$db->result($r, 0, "pay_type_id");
            $name           = $db->result($r, 0, "pay_type_name") . " #" . $db->result($r, 0, "doc_nom");
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
        if ($lang_id === 1) {
            $lang_id = 16;
        }
        if ($lang_id === 2) {
            $lang_id = 41;
        }
        if ($lang_id === 3) {
            $lang_id = 4;
        }

        $r = $db->query("SELECT `FUEL` FROM `T_types_fuel` WHERE `FUEL_ID` = $fuel_id AND `LANG_ID` = $lang_id LIMIT 1;");
        
        return $db->result($r, 0, "FUEL");
    }

    /*
     * get back clients name
     * from BACK_ID
     * */
    public function getBackClientsName($back_id): string
    {
        $back_id = $this->getUrlNumber($back_id);
        $db = DbSingleton::getDbm();
        $prefix = $doc_nom = "";
        $r = $db->query("SELECT `prefix`, `doc_nom` FROM `J_BACK_CLIENTS` WHERE `id` = $back_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n === 1) {
            $prefix = $db->result($r, 0, "prefix");
            $doc_nom = $db->result($r, 0, "doc_nom");
        }
        return $prefix . "-" . $doc_nom;
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
        return $this->replaceLang($name);
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
        return $this->replaceLang($name);
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
        return $this->replaceLang($name);
    }

    public function getSearchMessages(): array
    {
        $error          = "<h5 class=\"error_message\">$this->err1</h5>";
        $list           = "";
        return array($error, $list);
    }

    /*
     * get BRAND_ID
     * from BRAND_LINK
     * */
    public function getCatalogueBrandID($brand_link): int
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE `BRAND_LINK` = '$brand_link' LIMIT 1;");
        $brand_id = $db->result($r, 0, "BRAND_ID");
        return $this->getUrlNumber($brand_id);
    }

    /*
     * get Cookie Car
     * */
    public function getCookieAuto()
    {
        $auto_typ_id = $this->getUrlNumber($_COOKIE["auto_typ_id"]);
        return ($auto_typ_id > 0) ? $auto_typ_id : "";
    }

    public function formatArticleName($text, $max_symbols = 146): string
    {
        $dots = '...';
        $text = "$text";
        if (strlen($text) > $max_symbols) {
            $format_text = mb_substr($text, 0, $max_symbols - strlen($dots)) . $dots;
        } else {
            $format_text = $text;
        }
        return $format_text;
    }

    /*
     * Get Kind of brand
     * */
    public function getBrandType($brand_id): bool
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `KIND` FROM `T2_BRANDS` WHERE `BRAND_ID` = $brand_id LIMIT 1;");
        $kind = (int)$db->result($r, 0, "KIND");
        return ($kind === 3);
    }

    /*
     * check if art_id is original
     * */
    public function checkOriginalEquipment($art_id, $search_number): bool
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `SEARCH_NUMBER` FROM `T2_CROSS` WHERE `ART_ID` = $art_id AND `KIND` = 3 AND `RELATION` = 0;");
        $n = $db->num_rows($r);
        $nom = 0;
        for ($i = 1; $i <= $n; $i++) {
            $number = $db->result($r, $i - 1, "SEARCH_NUMBER");
            if ($search_number === $number) {
                $nom++;
            }
        }
        return ($nom > 0);
    }

    /*
     * Check relation from t2_cross
     * */
    public function checkAnalogTypes($art_id, $article_nr_search, $relation_id): bool
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT COUNT(`ART_ID`) as count_arts FROM `T2_CROSS` 
        WHERE `ART_ID` = $art_id AND `SEARCH_NUMBER` LIKE '$article_nr_search' AND `KIND` IN (3,4) AND `RELATION` = $relation_id;");
        $n = (int)$db->result($r, 0, "count_arts");
        return ($n > 0);
    }

    public function getFaqForm()
    {
        $form = "
        <div class=\"col-lg-4 col-12 pad0\"><div class=\"article-card\">" . $this->getHtmlForm("faq/request-card") . "</div></div>";
        return $this->replaceLang($form);
    }

    public function getFaqSocialsForm()
    {
        $form = $this->getHtmlForm("faq/request-socials");
        return $this->replaceLang($form);
    }

    public function setClientRequestDone()
    {
        $form = $this->getHtmlForm("faq/request-done");
        return $this->replaceLang($form);
    }

}