<?php

trait Variables {

    function getFormatAticle($name) {
        $format_name = str_replace(str_split('.,+-\/:*?"<>| '), "", $name);
        return $format_name;
    }

    function getFormatBrand($brand) {
        $format_brand = str_replace("/", "-", $brand);
        $format_brand = str_replace(" ", "%20", $format_brand);
        return $format_brand;
    }

    /*==== ARTICLE_ID ================================================================================================*/

    /*
     * ART_ID => BRAND_ID
     * */
    function getArticleBrand($art_id) { $db = DbSingleton::getTokoDb();
        $brand_id = 0;
        if ($art_id!="") {
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ART_ID`='$art_id' LIMIT 1;");
            $brand_id = $db->result($r,0,"BRAND_ID");
        }
        return $brand_id;
    }

    /*
     * ART_ID => ARTICLE_NAME
     * */
    function getArticleName($art_id) { $db = DbSingleton::getTokoDb();
        $name="";
        $art_id=$this->getUrlNumber($art_id);
        if ($art_id>0) {
            $r=$db->query("SELECT `NAME` FROM `T2_NAMES` WHERE `ART_ID`=$art_id AND `LANG_ID`=16 LIMIT 1;"); $n=$db->num_rows($r);
            if ($n>0) $name=$db->result($r, 0, "NAME");
        }
        if ($name=="") $name=$this->replaceLang("{details_name_cap}");
        return $name;
    }

    /*
     * ART_ID => ARTICLE_NR_SEARCH
     * */
    function getArticleSearch($art_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_ARTICLES` WHERE `ART_ID`='$art_id' LIMIT 1;");
        $article_nr_search=$db->result($r,0,"ARTICLE_NR_SEARCH");
        return $article_nr_search;
    }

    /*
     * ART_ID => ARTICLE_NR_DISPL
     * */
    function getArticleDispl($art_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_ARTICLES` WHERE `ART_ID`='$art_id' LIMIT 1;");
        $article_nr_displ=$db->result($r,0,"ARTICLE_NR_DISPL");
        return $article_nr_displ;
    }

    /*
     * ART_ID => ARTICLE NAME / BRAND NAME / ARTICLE DISPL
     * */
    function getArticleText($art_id) {
        $brand_id = $this->getArticleBrand($art_id);
        $article_nr_displ = $this->getArticleDispl($art_id);
        $brand_name = $this->getBrandName($brand_id);
        $article_name = $this->getArticleName($art_id);
        return "$article_name $brand_name $article_nr_displ";
    }

    /*
     * ART_ID => BARCODE
     * */
    function getBarcode($art_id) { $db = DbSingleton::getTokoDb();
        $art_id = $this->getUrlNumber($art_id);
        $r = $db->query("SELECT * FROM `T2_BARCODES` WHERE `ART_ID`='$art_id' LIMIT 1;");
        $barcode = $db->result($r,0,"BARCODE");
        if ($barcode=="") {
            $r = $db->query("SELECT MAX(`BARCODE`) as max_barcode FROM `T2_BARCODES`;");
            $barcode = $db->result($r,0,"max_barcode")+0;
        }
        return $barcode;
    }

    /*
     * ART_ID + STORAGE => STOCK (AMOUNT)
     * */
    function getArticleStock($art_id, $storage_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID`='$art_id' AND `STORAGE_ID`='$storage_id';");
        $stock = $db->result($r,0,"AMOUNT");
        return $stock;
    }

    /*
     * ART_ID + STORAGE => SUPPL STOCK (AMOUNT)
     * */
    function getArticleSupplStock($art_id, $suppl_id, $storage_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT `stock_suppl` FROM `T2_SUPPL_IMPORT` WHERE `art_id`='$art_id' AND `suppl_id`='$suppl_id' AND `client_storage_id`='$storage_id' AND `status`='1';");
        $stock=$db->result($r,0,"stock_suppl");
        return $stock;
    }

    /*
     * ART_ID + STORAGE => STOCK (PRICE LIST)
     * */
    function getStockStorage($art_id, $storage_id) { $db = DbSingleton::getTokoDb();
        if (empty($storage_id)) $storage_id = 0;
        $r = $db->query("SELECT SUM(`AMOUNT`) as summ_amount FROM `T2_ARTICLES_STRORAGE`
        WHERE `ART_ID`='$art_id' AND `STORAGE_ID` IN ($storage_id);"); $n=$db->num_rows($r);
        $n>0 ? $stock=intval($db->result($r,0,"summ_amount")) : $stock=0;
        return $stock;
    }

    /*==== ARTICLE_NR ================================================================================================*/
    /*
     * ARTICLE_NR_SEARCH => ARTICLE_NR_DISPL
     * */
    function getArtDispl($article_nr_search) { $db = DbSingleton::getTokoDb();
        $article_nr_displ = $article_nr_search;
        $r = $db->query("SELECT * FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' LIMIT 1;"); $n=$db->num_rows($r);
        if ($n>0) $article_nr_displ = $db->result($r,0,"ARTICLE_NR_DISPL");
        return $article_nr_displ;
    }

    /*
     * ARTICLE_NR_SEARCH + BRAND_ID => ART_ID
     * */
    function getArticleId($article_nr_search, $brand_id) { $db = DbSingleton::getTokoDb();
        $art_id = "";
        $brand_id = $this->getUrlNumber($brand_id);
        $article_nr_search = $this->getUrlString($article_nr_search);
        if ($brand_id>0) $where_brand = " AND `BRAND_ID`=$brand_id"; else $where_brand = "";
        if ($article_nr_search!="") {
            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' $where_brand;"); $n = $db->num_rows($r);
            if ($n>0) $art_id = $db->result($r, 0, "ART_ID");
        }
        return $art_id;
    }

    /*
     * ARTICLE_NR_SEARCH => ART_ID
     * */
    function getArtID($article_nr_search) { $db = DbSingleton::getTokoDb();
        $art_id = 0;
        $r = $db->query("SELECT * FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' LIMIT 1;"); $n=$db->num_rows($r);
        if ($n>0) $art_id = $db->result($r,0,"ART_ID");
        return $art_id;
    }

    /*
     * ARTICLE_NR_SEARCH => BRAND_NAME
     * */
    function getBrandIdArt($article_nr_search) { $db = DbSingleton::getTokoDb();
        $brand_name="";
        $article_nr_search = $this->getUrlString($article_nr_search);
        if ($article_nr_search!="") {
            $r=$db->query("SELECT `BRAND_ID`, `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' LIMIT 1;"); $n=$db->num_rows($r);
            if ($n>0) {
                $brand_id = $db->result($r, 0, "BRAND_ID");
                $brand_id = $this->getUrlNumber($brand_id);
                $r=$db->query("SELECT `BRAND_NAME` FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;"); $n=$db->num_rows($r);
                $n==1 ? $brand_name=$db->result($r, 0, "BRAND_NAME") : $brand_name="";
            }
        }
        return $brand_name;
    }

    /*==== BRAND =====================================================================================================*/

    function getBrandName($brand_id) { $db = DbSingleton::getTokoDb();
        $brand_id = $this->getUrlNumber($brand_id);
        $r = $db->query("SELECT `BRAND_NAME` FROM `T2_BRANDS` WHERE `BRAND_ID`=$brand_id LIMIT 1;"); $n = $db->num_rows($r);
        $n==1 ?	$brand=$db->result($r, 0, "BRAND_NAME") : $brand=0;
        return $brand;
    }

    function getBrandLink($brand_id) { $db = DbSingleton::getTokoDb();
        $brand_id = $this->getUrlNumber($brand_id);
        $r = $db->query("SELECT `BRAND_LINK` FROM `T2_BRANDS` WHERE `BRAND_ID`=$brand_id LIMIT 1;"); $n = $db->num_rows($r);
        $n==1 ?	$brand=$db->result($r, 0, "BRAND_LINK") : $brand=0;
        return $brand;
    }

    /*==== PHOTO =====================================================================================================*/

    function getArticlePhoto($art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;");
        $photo_name=$db->result($r,0,"PHOTO_NAME");
        $photo_src="https://toko.ua/uploads/images/catalogue/$photo_name";
        return $photo_src;
    }

    function getBasketArticlePhoto($art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;"); $n=$db->num_rows($r);
        $photo_name=$db->result($r,0,"PHOTO_NAME");
        $photo_src="https://toko.ua/uploads/images/catalogue/$photo_name";
        if ($n==0) $photo_src="https://toko.ua/$this->noPhoto";
        return $photo_src;
    }

    /*==== VARIABLES =================================================================================================*/

    function getCityName($city_id) { $db = DbSingleton::getDbm();
        $city_id = $this->getUrlNumber($city_id);
        $r = $db->query("SELECT `CITY_NAME` FROM `T2_CITY` WHERE `CITY_ID`=$city_id LIMIT 1;");
        $name = $db->result($r,0,"CITY_NAME");
        return $name;
    }

    function getCountryName($country_id) { $db = DbSingleton::getDbm();
        $country_id = $this->getUrlNumber($country_id);
        $r = $db->query("SELECT `COUNTRY_NAME` FROM `T2_COUNTRIES` WHERE `COUNTRY_ID`='$country_id' LIMIT 1;");
        $country_name = $db->result($r,0,"COUNTRY_NAME");
        return $country_name;
    }

    function getSaleInvoiceName($invoice_id) { $db = DbSingleton::getDbm();
        $name=""; $invoice_id=$this->getUrlNumber($invoice_id);
        $r=$db->query("SELECT * FROM `J_SALE_INVOICE` WHERE `status`=1 AND `id`='$invoice_id' LIMIT 1;"); $n=$db->num_rows($r);
        if ($n==1) $name=$db->result($r,0,"prefix")."-".$db->result($r,0,"doc_nom");
        return $name;
    }

    function getJPayName($jpay_id) { $db = DbSingleton::getDbm();
        $name=""; $pay_type_id=0; $jpay_id = $this->getUrlNumber($jpay_id);
        $r=$db->query("SELECT p.*, m.mcaption as pay_type_name FROM `J_PAY` p 
            LEFT JOIN `manual` m ON (m.id=p.pay_type_id AND m.`key`='pay_type_id') 
        WHERE p.status=1 AND p.id='$jpay_id' LIMIT 1;"); $n=$db->num_rows($r);
        if ($n==1){
            $pay_type_id=$db->result($r,0,"pay_type_id");
            $name=$db->result($r,0,"pay_type_name")." #".$db->result($r,0,"doc_nom");
        }
        return array($pay_type_id, $name);
    }

    function getFuelName($fuel_id) { $db=DbSingleton::getTokoDb();
        $language=new LangClass;
        $lang_id=$language->getLanguage();
        $fuel_id=$this->getUrlNumber($fuel_id);
        if ($lang_id==1) $lang_id=16;
        if ($lang_id==2) $lang_id=41;
        if ($lang_id==3) $lang_id=4;
        $r=$db->query("SELECT `FUEL` FROM `T_types_fuel` WHERE `FUEL_ID`='$fuel_id' AND `LANG_ID`='$lang_id' LIMIT 1;");
        $fuel=$db->result($r,0,"FUEL");
        return $fuel;
    }

    function getBackClientsName($back_id) { $db = DbSingleton::getDbm();
        $prefix=$doc_nom="";
        $back_id = $this->getUrlNumber($back_id);
        $r=$db->query("SELECT * FROM `J_BACK_CLIENTS` WHERE `id`='$back_id' LIMIT 1;");$n=$db->num_rows($r);
        if ($n==1){
            $prefix=$db->result($r,0,"prefix");
            $doc_nom=$db->result($r,0,"doc_nom");
        }
        return $prefix."-".$doc_nom;
    }

    function getCatalogueLink($article_nr_search) {
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $article_nr_search=$this->getUrlString($article_nr_search);
        $brand_link=$this->getCatalogueBrandLink2($article_nr_search);
        $link="https://toko.ua$prefix/search/$article_nr_search/$brand_link";
        return $link;
    }

    function getCatalogueBrandLink2($article_nr_search) { $db=DbSingleton::getTokoDb();
        $brand_link="";
        $r=$db->query("SELECT * FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search';"); $n=$db->num_rows($r);
        if ($n==1) {
            $brand_id=$db->result($r,0,"BRAND_ID");
            $r=$db->query("SELECT * FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;");
            $brand_link=$db->result($r,0,"BRAND_LINK");
        }
        return $brand_link;
    }

    function getFilters($text_filter, $brand_filter) {
        if ($text_filter!="" && $text_filter!=" ") $where_text=" AND t2a.ARTICLE_NR_DISPL LIKE '%$text_filter%' "; else $where_text="";
        if ($brand_filter!="") $where_brands=" AND t2a.BRAND_ID IN ($brand_filter) "; else $where_brands="";
        return array($where_text, $where_brands);
    }

    function getPagePagination($page, $max_page) {
        $prev=$next="";
        if ($page>0) {
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            if (strpos($actual_link,"?")!==false) $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
            if ($page==1) {
                $next_page=$page+1;
                $next="<link rel=\"next\" href=\"$actual_link?page=$next_page\">";
                $prev="";
            }
            if ($page>1 && $page<$max_page) {
                $next_page=$page+1;
                $prev_page=$page-1;
                $next="<link rel=\"next\" href=\"$actual_link?page=$next_page\">";
                $prev="<link rel=\"prev\" href=\"$actual_link?page=$prev_page\">";
            }
            if ($page==$max_page) {
                $prev_page=$page-1;
                $next="";
                $prev="<link rel=\"prev\" href=\"$actual_link?page=$prev_page\">";
            }
            $page_pagination="        
                $prev
                $next
            ";
        } else {
            $page_pagination="";
        }
        return $page_pagination;
    }

    function getDeliveryName($delivery_id) { $db=DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_DELIVERY` WHERE `ID`='$delivery_id' LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    function getDepartmentExpressName($delivery_id) { $db=DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_DELIVERY_EXPRESS` WHERE `ID`='$delivery_id' LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    function getPaymentName($payment_id) { $db=DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_PAYMENT` WHERE `ID`='$payment_id' LIMIT 1;");
        $name = $db->result($r, 0, "TEXT");
        $name = $this->replaceLang($name);
        return $name;
    }

    function getSearchMessages($type_filter) {
        $form_404=$this->getHtmlForm("error/404_tree");
        $form_404=$this->replaceLang($form_404);
        switch ($type_filter) {
            case 1:  { $error="<h5 class=\"error_message\">$this->err1</h5>"; $list=""; $jsFilterModel="catalogueFilter();"; break; }
            case 2:  { $error="$form_404"; $list=""; $jsFilterModel="tecModelsFilter();"; break; }
            default: { $error="$form_404"; $list=""; $jsFilterModel="catalogueFilter();"; break; }
        }
        return array($error, $jsFilterModel, $list);
    }

    /*==== TEMPLATE VARIABLES ========================================================================================*/

    public function getTemplateID($template_link) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_LINK`='$template_link' LIMIT 1;");
        $template_id=$db->result($r,0,"TEMPLATE_ID");
        return $template_id;
    }
    public function getTemplateName($template_id) { $db=DbSingleton::getTokoDb();
        $template_id = $this->getUrlNumber($template_id);
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_ID`='$template_id' LIMIT 1;");
        $template_name=$db->result($r,0,"TEMPLATE_NAME");
        return $template_name;
    }
    public function getTemplateLink($template_id) { $db=DbSingleton::getTokoDb();
        $template_id = $this->getUrlNumber($template_id);
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_ID`='$template_id' LIMIT 1;");
        $template_link=$db->result($r,0,"TEMPLATE_LINK");
        return $template_link;
    }

    public function getCatalogueParamID($param_link, $template_id=1) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_LINK`='$param_link' LIMIT 1;");
        $param_id=$db->result($r,0,"PARAM_ID");
        return $param_id;
    }
    public function getCatalogueParamName($param_id, $template_id=1) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_ID`='$param_id' LIMIT 1;");
        $param_name=$db->result($r,0,"PARAM_NAME");
        return $param_name;
    }
    public function getCatalogueParamLink($param_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        $param_link=$db->result($r,0,"PARAM_LINK");
        return $param_link;
    }
    public function getParamFromValue($value_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT `PARAM_ID` FROM `T2_CATALOGUES_VALUES` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        $param_id=$db->result($r,0,"PARAM_ID");
        return $param_id;
    }

    public function getCatalogueValueID($value_link, $param_id, $template_id=1) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_ID`='$param_id' AND`VALUE_LINK`='$value_link' LIMIT 1;");
        $value_id=$db->result($r,0,"VALUE_ID");
        return $value_id;
    }
    public function getCatalogueValueName($value_id, $template_id=1) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES` WHERE `TEMPLATE_ID`='$template_id' AND `VALUE_ID`='$value_id' LIMIT 1;");
        $param_value=$db->result($r,0,"PARAM_VALUE");
        return $param_value;
    }
    public function getCatalogueValueLink($value_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        $value_link=$db->result($r,0,"VALUE_LINK");
        return $value_link;
    }

    public function getCatalogueBrandID($brand_link) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_BRANDS` WHERE `BRAND_LINK`='$brand_link' LIMIT 1;");
        $brand_id=$db->result($r,0,"BRAND_ID");
        return $brand_id;
    }
    public function getCatalogueBrandLink($brand_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;");
        $brand_link=$db->result($r,0,"BRAND_LINK");
        return $brand_link;
    }

}