<?php

trait Variables
{

    /*
     * get secret article
     * add zero to first digit in string
     * */
    public function getSecretString($str)
    {
        preg_match('/\d+/', $str, $matches);
        $pos = strpos($str, $matches[0]);
        return substr_replace($str, '0', $pos, 0);
    }

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
        $db = DbSingleton::getTokoDb();
        $brand_id = 0;
        if ($art_id != "") {
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ART_ID`='$art_id' LIMIT 1;");
            $brand_id = $db->result($r, 0, "BRAND_ID");
        }
        return $brand_id;
    }

    /*
     * ART_ID => ARTICLE_NAME
     * */
    public function getArticleName($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $name = "";
        $art_id = $this->getUrlNumber($art_id);
        if ($art_id > 0) {
            $r = $db->query("SELECT `NAME` FROM `T2_NAMES` WHERE `ART_ID`=$art_id AND `LANG_ID`=16 LIMIT 1;");
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
        $r = $db->query("SELECT `ARTICLE_NR_SEARCH` FROM `T2_ARTICLES` WHERE `ART_ID`='$art_id' LIMIT 1;");
        return $db->result($r, 0, "ARTICLE_NR_SEARCH");
    }

    /*
     * ART_ID => ARTICLE_NR_DISPL
     * */
    public function getArticleDispl($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ARTICLE_NR_DISPL` FROM `T2_ARTICLES` WHERE `ART_ID`='$art_id' LIMIT 1;");
        return $db->result($r, 0, "ARTICLE_NR_DISPL");
    }

    /*
     * ART_ID => ARTICLE NAME / BRAND NAME / ARTICLE DISPL
     * */
    public function getArticleText($art_id)
    {
        $brand_id = $this->getArticleBrand($art_id);
        $article_nr_displ = $this->getArticleDispl($art_id);
        $brand_name = $this->getBrandName($brand_id);
        $article_name = $this->getArticleName($art_id);
        return "$article_name $brand_name $article_nr_displ";
    }

    /*
     * ART_ID => BARCODE
     * */
    public function getBarcode($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $art_id = $this->getUrlNumber($art_id);
        $r = $db->query("SELECT `BARCODE` FROM `T2_BARCODES` WHERE `ART_ID`='$art_id' LIMIT 1;");
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
        WHERE `ART_ID`='$art_id' AND `STORAGE_ID` IN ($storage_id);");
        $n = $db->num_rows($r);
        $n > 0 ? $stock = intval($db->result($r, 0, "summ_amount")) : $stock = 0;
        return $stock;
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
            $where_brand = "AND `BRAND_ID`='$brand_nr_search'";
        }
        $r = $db->query("SELECT `ARTICLE_NR_DISPL` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' $where_brand LIMIT 1;");
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
        $db = DbSingleton::getTokoDb();
        $art_id = "";
        $brand_id = $this->getUrlNumber($brand_id);
        $article_nr_search = $this->getUrlString($article_nr_search);
        if ($brand_id > 0) {
            $where_brand = " AND `BRAND_ID`=$brand_id";
        } else {
            $where_brand = "";
        }
        if ($article_nr_search != "") {
            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' $where_brand;");
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
        $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $art_id = $db->result($r, 0, "ART_ID");
        }
        return $art_id;
    }

    /*
     * ARTICLE_NR_SEARCH => BRAND_NAME
     * */
//    public function getBrandIdArt($article_nr_search)
//    {
//        $db = DbSingleton::getTokoDb();
//        $brand_name = "";
//        $article_nr_search = $this->getUrlString($article_nr_search);
//        if ($article_nr_search != "") {
//            $r = $db->query("SELECT `BRAND_ID`, `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' LIMIT 1;");
//            $n = $db->num_rows($r);
//            if ($n > 0) {
//                $brand_id = $db->result($r, 0, "BRAND_ID");
//                $brand_id = $this->getUrlNumber($brand_id);
//                $r = $db->query("SELECT `BRAND_NAME` FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;");
//                $n = $db->num_rows($r);
//                $brand_name = ($n == 1) ? $db->result($r, 0, "BRAND_NAME") : "";
//            }
//        }
//        return $brand_name;
//    }

    /*==== BRAND =====================================================================================================*/

    /*
     * get brand name
     * from BRAND_ID
     * */
    public function getBrandName($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $brand_id = $this->getUrlNumber($brand_id);
        $r = $db->query("SELECT `BRAND_NAME` FROM `T2_BRANDS` WHERE `BRAND_ID`=$brand_id LIMIT 1;");
        $n = $db->num_rows($r);
        $n == 1 ? $brand = $db->result($r, 0, "BRAND_NAME") : $brand = 0;
        return $brand;
    }

    /*
     * get brand link
     * from BRAND_ID
     * */
    public function getBrandLink($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $brand_id = $this->getUrlNumber($brand_id);
        $r = $db->query("SELECT `BRAND_LINK` FROM `T2_BRANDS` WHERE `BRAND_ID`=$brand_id LIMIT 1;");
        $n = $db->num_rows($r);
        $n == 1 ? $brand = $db->result($r, 0, "BRAND_LINK") : $brand = 0;
        return $brand;
    }

    /*==== PHOTO =====================================================================================================*/

    public function getArticlePhoto($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;");
        $photo_name = $db->result($r, 0, "PHOTO_NAME");
        return "https://toko.ua/uploads/images/catalogue/$photo_name";
    }

    public function getBasketArticlePhoto($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;");
        $n = $db->num_rows($r);
        $photo_name = $db->result($r, 0, "PHOTO_NAME");
        $photo_src = "https://toko.ua/uploads/images/catalogue/$photo_name";
        if ($n == 0) {
            $photo_src = "https://toko.ua/$this->noPhoto";
        }
        return $photo_src;
    }

    /*==== VARIABLES =================================================================================================*/

    /*
     * get city name
     * from CITY_ID
     * */
    public function getCityName($city_id)
    {
        $db = DbSingleton::getDbm();
        $city_id = $this->getUrlNumber($city_id);
        $r = $db->query("SELECT `CITY_NAME` FROM `T2_CITY` WHERE `CITY_ID`=$city_id LIMIT 1;");
        return $db->result($r, 0, "CITY_NAME");
    }

    /*
     * get country name
     * from COUNTRY_ID
     * */
    public function getCountryName($country_id)
    {
        $db = DbSingleton::getDbm();
        $country_id = $this->getUrlNumber($country_id);
        $r = $db->query("SELECT `COUNTRY_NAME` FROM `T2_COUNTRIES` WHERE `COUNTRY_ID`='$country_id' LIMIT 1;");
        return $db->result($r, 0, "COUNTRY_NAME");
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
        $r = $db->query("SELECT `prefix`, `doc_nom` FROM `J_SALE_INVOICE` WHERE `status`=1 AND `id`='$invoice_id' LIMIT 1;");
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
        $r = $db->query("SELECT p.*, m.mcaption as pay_type_name FROM `J_PAY` p 
            LEFT JOIN `manual` m ON (m.id=p.pay_type_id AND m.`key`='pay_type_id') 
        WHERE p.status=1 AND p.id='$jpay_id' LIMIT 1;");
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
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $fuel_id = $this->getUrlNumber($fuel_id);
        if ($lang_id == 1) $lang_id = 16;
        if ($lang_id == 2) $lang_id = 41;
        if ($lang_id == 3) $lang_id = 4;
        $r = $db->query("SELECT `FUEL` FROM `T_types_fuel` WHERE `FUEL_ID`='$fuel_id' AND `LANG_ID`='$lang_id' LIMIT 1;");
        return $db->result($r, 0, "FUEL");
    }

    /*
     * get back clients name
     * from BACK_ID
     * */
    public function getBackClientsName($back_id)
    {
        $db = DbSingleton::getDbm();
        $prefix = $doc_nom = "";
        $back_id = $this->getUrlNumber($back_id);
        $r = $db->query("SELECT `prefix`, `doc_nom` FROM `J_BACK_CLIENTS` WHERE `id`='$back_id' LIMIT 1;");
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
        $prefix = $this->getLangPrefix();
        return "https://toko.ua$prefix/search/$article_nr_search/$brand_link";
    }

    public function getCatalogueBrandLink2($article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $brand_link = "";
        $r = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search';");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $brand_id = $db->result($r, 0, "BRAND_ID");
            $r = $db->query("SELECT `BRAND_LINK` FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;");
            $brand_link = $db->result($r, 0, "BRAND_LINK");
        }
        return $brand_link;
    }

    public function getFiltersSearch($brand_filter)
    {
        if ($brand_filter != "") {
            $where_brands = " AND t2a.BRAND_ID IN ($brand_filter) ";
        } else {
            $where_brands = "";
        }
        return $where_brands;
    }

    /*
     * get catalog pagination
     * */
    public function getPagePagination($page, $max_page)
    {
        $prev = $next = "";
        if ($page > 0) {
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            if (strpos($actual_link, "?") !== false) {
                $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
            }
            if ($page == 1) {
                $next_page = $page + 1;
                $next = "<link rel=\"next\" href=\"$actual_link?page=$next_page\">";
                $prev = "";
            }
            if ($page > 1 && $page < $max_page) {
                $next_page = $page + 1;
                $prev_page = $page - 1;
                $next = "<link rel=\"next\" href=\"$actual_link?page=$next_page\">";
                $prev = "<link rel=\"prev\" href=\"$actual_link?page=$prev_page\">";
            }
            if ($page == $max_page) {
                $prev_page = $page - 1;
                $next = "";
                $prev = "<link rel=\"prev\" href=\"$actual_link?page=$prev_page\">";
            }
            $page_pagination = "        
                $prev
                $next
            ";
        } else {
            $page_pagination = "";
        }
        return $page_pagination;
    }

    /*
     * get delivery name
     * from DELIVERY_ID
     * */
    public function getDeliveryName($delivery_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `T2_DELIVERY` WHERE `ID`='$delivery_id' LIMIT 1;");
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
        $r = $db->query("SELECT `TEXT` FROM `T2_DELIVERY_EXPRESS` WHERE `ID`='$delivery_id' LIMIT 1;");
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
        $r = $db->query("SELECT `TEXT` FROM `T2_PAYMENT` WHERE `ID`='$payment_id' LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    public function getSearchMessages($type_filter)
    {
        $form_404 = $this->replaceLang($this->getHtmlForm("error/404_tree"));
        switch ($type_filter) {
            case 1:
            {
                $error = "<h5 class=\"error_message\">$this->err1</h5>";
                $list = "";
                $jsFilterModel = "catalogueFilter();";
                break;
            }
            case 2:
            {
                $error = "$form_404";
                $list = "";
                $jsFilterModel = "tecModelsFilter();";
                break;
            }
            default:
            {
                $error = "$form_404";
                $list = "";
                $jsFilterModel = "catalogueFilter();";
                break;
            }
        }
        return array($error, $jsFilterModel, $list);
    }

    /*==== TEMPLATE VARIABLES ========================================================================================*/

    public function getTemplateID($template_link)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEMPLATE_ID` FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_LINK`='$template_link' LIMIT 1;");
        return $db->result($r, 0, "TEMPLATE_ID");
    }

    public function getTemplateName($template_id)
    {
        $db = DbSingleton::getTokoDb();
        $template_id = $this->getUrlNumber($template_id);
        $r = $db->query("SELECT `TEMPLATE_NAME` FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_ID`='$template_id' LIMIT 1;");
        return $db->result($r, 0, "TEMPLATE_NAME");
    }

    public function getTemplateLink($template_id)
    {
        $db = DbSingleton::getTokoDb();
        $template_id = $this->getUrlNumber($template_id);
        $r = $db->query("SELECT `TEMPLATE_LINK` FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_ID`='$template_id' LIMIT 1;");
        return $db->result($r, 0, "TEMPLATE_LINK");
    }

    public function getCatalogueParamID($param_link, $template_id = 1)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_LINK`='$param_link' LIMIT 1;");
        return $db->result($r, 0, "PARAM_ID");
    }

    public function getCatalogueParamName($param_id, $template_id = 1)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_NAME` FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_ID`='$param_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_NAME");
    }

    public function getCatalogueParamLink($param_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_CATALOGUES_PARAMS` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_LINK");
    }

    public function getParamFromValue($value_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_CATALOGUES_VALUES` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_ID");
    }

    public function getCatalogueValueID($value_link, $param_id, $template_id = 1)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `VALUE_ID` FROM `T2_CATALOGUES_VALUES` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_ID`='$param_id' AND `VALUE_LINK`='$value_link' LIMIT 1;");
        return $db->result($r, 0, "VALUE_ID");
    }

    public function getCatalogueValueName($value_id, $template_id = 1)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_VALUE` FROM `T2_CATALOGUES_VALUES` WHERE `TEMPLATE_ID`='$template_id' AND `VALUE_ID`='$value_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_VALUE");
    }

    public function getCatalogueValueLink($value_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_CATALOGUES_VALUES` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        return $db->result($r, 0, "VALUE_LINK");
    }

    public function getCatalogueBrandID($brand_link)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE `BRAND_LINK`='$brand_link' LIMIT 1;");
        $brand_id = $db->result($r, 0, "BRAND_ID");
        $brand_id = $this->getUrlNumber($brand_id);
        return $brand_id;
    }

    public function getCatalogueBrandLink($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_LINK` FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;");
        return $db->result($r, 0, "BRAND_LINK");
    }

    public function getHeadName($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $lang_id = $this->getLanguage();
        $prefix = $language->getTexCapLanguage($lang_id);
        $r = $db->query("SELECT `TEX_$prefix` FROM `T2_TREE_HEAD` WHERE `HEAD_ID`='$head_id' LIMIT 1;");
        return $db->result($r, 0, "TEX_$prefix");
    }

    public function getCatName($cat_id)
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $lang_id = $this->getLanguage();
        $prefix = $language->getTexCapLanguage($lang_id);
        $r = $db->query("SELECT `TEX_$prefix` FROM `T2_TREE_CAT` WHERE `CAT_ID`='$cat_id' LIMIT 1;");
        return $db->result($r, 0, "TEX_$prefix");
    }

    public function getGroupName($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $lang_id = $this->getLanguage();
        $prefix = $language->getTexCapLanguage($lang_id);
        $r = $db->query("SELECT `TEX_$prefix` FROM `T2_TREE_GROUP` WHERE `GROUP_ID`='$group_id' LIMIT 1;");
        return $db->result($r, 0, "TEX_$prefix");
    }

    public function getGroupLink($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_GROUP` WHERE `GROUP_ID`='$group_id' LIMIT 1;");
        return $db->result($r, 0, "TEX_LINK");
    }

    public function getGroupLinkID($group_link)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP` WHERE `TEX_LINK`='$group_link' LIMIT 1;");
        return $db->result($r, 0, "GROUP_ID");
    }

    /* GROUP VALUES */
    public function getValueID($value_link, $param_id, $group_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `VALUE_ID` FROM `T2_TREE_VALUE` WHERE `VALUE_LINK`='$value_link' AND `PARAM_ID`='$param_id' AND `GROUP_ID`='$group_id' LIMIT 1;");
        return $db->result($r, 0, "VALUE_ID");
    }

    public function getValueName($value_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_VALUE` FROM `T2_TREE_VALUE` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_VALUE");
    }

    public function getValueLink($value_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        return $db->result($r, 0, "VALUE_LINK");
    }

    /* GROUP PARAMS */
    public function getParamID($param_link, $group_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS` WHERE `PARAM_LINK`='$param_link' AND `GROUP_ID`='$group_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_ID");
    }

    public function getParamName($param_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_NAME` FROM `T2_TREE_PARAMS` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_NAME");
    }

    public function getParamLink($param_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_TREE_PARAMS` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        return $db->result($r, 0, "PARAM_LINK");
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