<?php

class CatalogueClass
{

    use Helper;
    use Variables;

    public $catalog_link            = "catalog";
    public $search_link             = "search";
    public $products_link           = "products";
    public $order_link              = "order";
    public $news_link               = "news";
    public $reviews_link            = "reviews";
    public $cars_link               = "cars";
    public $faq_card_count          = 2;
    public $faq_socials_card_count  = 4;
    public $charset                 = "windows-1251";

    public function getIconv($str)
    {
        //$str  = iconv("UTF-8", "windows-1251", $str);
        return $str;
    }
    public function getIconConvert($str)
    {
        //$str  = mb_convert_encoding($str, "UTF-8", "Windows-1251");
        return $str;
    }
    public function getIconvWindows($str)
    {
        //$str  = iconv("windows-1251", "UTF-8", $str);
        return $str;
    }

    /*
     * SEARCH LIST
     * */
    public function getCatalogList($article_nr_search, $brand_nr_search)
    {
        $article_nr_search  = $this->getUrlString($article_nr_search);
        $brand_nr_search    = $this->getUrlNumber($brand_nr_search);

        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $search = new SearchClass();

        $client->insertHistory($article_nr_search, $brand_nr_search);
        $cur = $this->getCurrentExRate();

        $art_ids = [];
        $r = $db->query("SELECT DISTINCT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_nr_search AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE));");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $art_ids[] = $art_id;
        }
        $art_id_str = implode(",", $art_ids);

        $form           = $this->getHtmlForm("search/form");
        $search_main    = $this->getHtmlForm("search/main");
        $search_filters = $this->getHtmlForm("search/filters");
        $search_brands  = $this->getHtmlForm("search/brands");

        list($list, $list_brand, $filters) = $search->searchList($art_id_str, $article_nr_search, $brand_nr_search);

        // if found something
        if (($list_brand) && ($filters)) {
            $colon          = "col-lg-9 col-12 pad0";
            $colon_filter   = "col-lg-3 col-12";
        } else {
            $colon          = "col-lg-12 col-12 pad0";
            $colon_filter   = "none";
            $search_main    = str_replace(array("{currency}", "{products_view}"), "", $search_main);
            $form           = str_replace(array("{cat_search_filters}", "{cat_search_brands}"), "", $form);
        }

        //colon
        $form = str_replace(array("{search_col}", "{filters_col}", "{type_search}", "{art_value}", "{brand_value}", "{cur_value}", "{cat_search_main}"), array($colon, $colon_filter, 1, $article_nr_search, $brand_nr_search, $cur, $this->getSearchMain($search_main, $list)), $form);

        //search filters
        if (!empty($filters)) {
            $form = str_replace("{cat_search_filters}", $this->getSearchFilters($search_filters, $filters, $cur, []), $form);
        }

        //search brands
        if (!empty($list_brand)) {
            $search_brands = str_replace(array("{brands_list}", "{brands_display}"), array($list_brand, ""), $search_brands);
            $form = str_replace("{cat_search_brands}", $search_brands, $form);
        }
        
        $form = str_replace("{cat_search_telegram}", $this->getTelegramForm(), $form);

        return $this->replaceLang($form);
    }

    public function getTelegramForm()
    {
        return $this->getHtmlForm("catalog_exist/telegram");
    }

    /*
    * SEARCH LIST FILTER
    * */
    public function getCatalogListFilter($article_nr_search, $brand_nr_search, $brand_filter, $price_f, $delivery_f): array
    {
        $article_nr_search  = $this->getNameString($article_nr_search);
        $brand_nr_search    = $this->getUrlNumber($brand_nr_search);
        $cur                = $this->getCurrentExRate();

        $db = DbSingleton::getTokoDb();
        $search = new SearchClass();

        $art_ids = [];
        $r = $db->query("SELECT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_nr_search AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE))
        ORDER BY t2n.`NAME`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_ids[] = (int)$db->result($r, $i - 1, "ART_ID");
        }

        $art_id_str = implode(",", $art_ids);

        $brand_filter = json_decode($brand_filter, false);
        $brand_filter = (count($brand_filter) > 1) ? implode(",", $brand_filter) : "";

        $exp_price = explode(",", $price_f);
        $exp_delivery = explode(",", $delivery_f);

        list($list, $filters, $list_brand, $current_value) = $search->searchListFilter($art_id_str, $article_nr_search, $brand_filter, $cur, $exp_price[0], $exp_price[1], $exp_delivery[0], $exp_delivery[1], $brand_nr_search);

        $search_main    = $this->replaceLang($this->getSearchMain($this->getHtmlForm("search/main"), $list));
        $search_filters = $this->replaceLang($this->getSearchFilters($this->getHtmlForm("search/filters"), $filters, $cur, $current_value));
        $search_brands  = $this->getHtmlForm("search/brands");
        $search_brands  = str_replace(array("{brands_list}", "{brands_display}"), array($list_brand, ($list_brand === "") ? "none" : ""), $search_brands);
        $search_brands  = $this->replaceLang($search_brands);

        return array($search_main, $search_filters, $search_brands, $filters["max_price"]);
    }

    /*
     * SEARCH LIST PRINT
     * */
    public function getSearchMain($search_main, $list)
    {
        return str_replace("{search_result}", $list, $search_main);
    }

    /*
     * SEARCH LIST FILTERS FORM
     * */
    public function getSearchFilters($search_filters, $filters, $cur, $current_value)
    {
        if (!empty($filters) && empty($current_value)) {
            $current_value              = array();
            $current_value["min_price"] = 0;
            $current_value["max_price"] = $filters["max_price"];
            $current_value["min_dd"]    = 0;
            $current_value["max_dd"]    = $filters["max_dd"];
        }
        
        return str_replace(array("{sideblock_max_price}", "{sideblock_max_dd}", "{sideblock_max_price_val}", "{sideblock_max_dd_val}", "{sideblock_min_price_val}", "{sideblock_min_dd_val}", "{cur_value}", "{catalogue_js_filter}", "{filters_col}"), array($filters["max_price"], $filters["max_dd"], $current_value["max_price"], $current_value["max_dd"], $current_value["min_price"], $current_value["min_dd"], $cur, "catalogueFilter();", "col-lg-2 col-12 pad0"), $search_filters);
    }

    /*
     * CATALOG TEMPORARY TABLE
     * */
    public function createTemporarySearchTable($temp_key)
    {
        $db = DbSingleton::getTokoDb();
        $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `TEMP_ARTICLES_$temp_key` (
            `art_id` INT(100) NOT NULL,
            `article_nr_displ` VARCHAR(100),
            `brand_id` INT(100),
            `brand_name` VARCHAR(100),
            `article_name` VARCHAR(100),
            `delivery_info` VARCHAR(100),
            `stock` INT(100),
            `price` FLOAT,
            `delivery_days` INT(100),
            `delivery_short_info` VARCHAR(100),
            `suppl_id` VARCHAR(100),
            `return_days` VARCHAR(100),
            `status` INT(10),
            `storage_id` INT(100)
        ) ENGINE = MYISAM;");
    }

    /*
     * CATALOG ARTS LIST
     * */
    public function getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, $where_brands = "", $nulls = 0)
    {
        $db = DbSingleton::getTokoDb();

        if ($article_nr_search !== "") {
            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' AND `BRAND_ID` = $brand_nr_search LIMIT 1;");
            $n = $db->num_rows($r);
            
            if ($n > 0) {
                $art_id             = $db->result($r, 0, "ART_ID");
                $where_oe_art_id    = $this->getOriginalEquipment($art_id);
                $where_art_id_str   .= ",$where_oe_art_id";
            }
        }

        if (empty($where_art_id_str)) {
            $where_art_id_str = "0";
        }
        
        $where_art_id_str = rtrim($where_art_id_str, ",");
        $where_art_id_str = str_replace("'", "", $where_art_id_str);

        $where_storage1 = "";
        $where_storage2 = "";
        
        if ($nulls === 0) {
            $where_storage1 = "AND ((t2asc.AMOUNT != NULL OR t2asc.AMOUNT != 0) OR (t2a.`ARTICLE_NR_SEARCH` = '$article_nr_search' AND t2a.`BRAND_ID` = $brand_nr_search))";
            $where_storage2 = "AND ((t2si.stock_suppl != NULL OR t2si.stock_suppl != 0) OR (t2a.`ARTICLE_NR_SEARCH` = '$article_nr_search' AND t2a.`BRAND_ID` = $brand_nr_search))";
        }

        $r = "";
        if (!empty($where_art_id_str)) {
            $r = $db->query("
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2asc.AMOUNT as AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID)
                LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID)
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.ART_ID = t2a.ART_ID)
            WHERE t2a.ART_ID IN ($where_art_id_str) 
                AND t2b.`VISIBLE` = '1' 
                AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE))
                $where_storage1
                $where_brands 
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
            UNION ALL
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2si.stock_suppl as AMOUNT, t2si.client_storage_id as storage_id, t2si.suppl_id, t2si.return_delay
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID)
                LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID)
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
            WHERE t2a.ART_ID IN ($where_art_id_str) 
                AND t2b.`VISIBLE` = '1' 
                AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE))
                $where_storage2
                $where_brands 
            GROUP BY t2a.ART_ID, t2si.client_storage_id;");
        }

        return $r;
    }

    public function getTemporarySearchTable2($where_art_id_str)
    {
        $db = DbSingleton::getTokoDb();
        $r = "";

        if ($where_art_id_str !== "") {
            $r = $db->query("
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, IFNULL(t2asc.AMOUNT,0) as AMOUNT, IFNULL(t2asc.STORAGE_ID,0) as storage_id, 0 as suppl_id, 0 as return_delay 
            FROM `T2_ARTICLES` t2a 
                LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID) 
                LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID) 
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.ART_ID = t2a.ART_ID) 
            WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE` = '1' AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE))
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID 
            UNION ALL 
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, IFNULL(t2si.stock_suppl,0) as AMOUNT, IFNULL(t2si.client_storage_id,0) as storage_id, IFNULL(t2si.suppl_id,0), IFNULL(t2si.return_delay,0) 
            FROM `T2_ARTICLES` t2a 
                LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID) 
                LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID) 
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1) 
            WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE` = '1' AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE))
            GROUP BY t2a.ART_ID, t2si.client_storage_id
            ");
        }

        return $r;
    }

    /*
     * CATALOG ORIGINAL NUMBERS
     * */
    public function getOriginalEquipment($art_id): string
    {
        $db = DbSingleton::getTokoDb();

        $arts = $art_id_arr = [];

        $r = $db->query("SELECT DISTINCT `SEARCH_NUMBER`, `BRAND_ID` FROM `T2_CROSS` 
        WHERE `ART_ID` = $art_id AND ((`KIND` = 3 AND `RELATION` = 0) OR (`KIND` IN (3, 4) AND `RELATION` = 1) OR (`KIND` IN (3, 4) AND `RELATION` = 2)) LIMIT 0,10;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_search = $db->result($r, $i - 1, "SEARCH_NUMBER");
            $brand_id   = $db->result($r, $i - 1, "BRAND_ID");
            $arts[$i]   = [
                "search_number" => $art_search,
                "brand_id"      => $brand_id
            ];
        }

        foreach ($arts as $art) {
            $art_search = $art["search_number"];
            $brand_id   = $art["brand_id"];

            $r = $db->query("SELECT `ART_ID` FROM `T2_CROSS` 
            WHERE `SEARCH_NUMBER` = '$art_search' AND `BRAND_ID` = $brand_id AND ((`KIND` = 3 AND `RELATION` = 0) OR (`KIND` = 0 AND `RELATION` = 0));");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $cross_art_id = $db->result($r, $i - 1, "ART_ID");
                $art_id_arr[] = $cross_art_id;
            }
        }

        return implode(",", $art_id_arr);
    }

    /*
     * CATALOG BRAND FORM
     * */
    public function getListBrand($brands, $main_brand, $cur, $brand_filter = [])
    {
        $list_brand = $main_brand_class = "";
        $unique_brands = array();

        usort($brands, "cmpPrice");

        // main brand first
        $brand_main_key = 0;

        if ($main_brand > 0) {
            foreach ($brands as $key => $value) {
                $brand_id = $value["brand_id"];

                if ($main_brand === $brand_id) {
                    $brand_main_key = $key;
                }
            }

            if (!empty($brands)) {
                $brands = array($brand_main_key => $brands[$brand_main_key]) + $brands;
            }
        }

        //get unique brands with min price
        foreach ($brands as $key => $value) {
            //delete 0;
            
            if (!empty($unique_brands) && $unique_brands[$value["brand_id"]]["brand_count"] > 0) {
                unset($brands[$key]);
            }
            
            if (in_array($value["brand_id"], $value, true)) {
                $unique_brands[$value["brand_id"]]["brand_count"] += 1;
            }
        }

        if (!empty($brands)) {
            foreach ($brands as $value) {
                $min_price  = $value["price"];
                $brand_id   = $value["brand_id"];
                $val_brand  = $value["brand_name"];

                $result_brand_array = array();
                $brand_array = explode(",", $brand_filter);
                foreach ($brand_array as $each_number) {
                    $result_brand_array[] = (int) $each_number;
                }

                if (!empty($brand_filter)) {
                    $checked = (in_array($brand_id, $result_brand_array, true)) ? "checked=\"checked\"" : "";
                } else {
                    $checked = (in_array($main_brand, $result_brand_array, true)) ? "checked=\"checked\"" : "";
                }

                if (!empty($brand_id)) {
                    
                    if ($brand_id === $main_brand) {
                        $checked = "checked=\"checked\" disabled=\"true\"";
                        $main_brand_class = "main-brand";
                    } else {
                        $main_brand_class = "";
                    }
                }

                $list_brand .= $this->getHtmlForm("search/brand_item");
                $list_brand = str_replace(
                    array("{val_brand}", "{brand_id}", "{main_brand_class}", "{checked}", "{min_price}", "{cur_cap}"),
                    array($val_brand, $brand_id, $main_brand_class, $checked, $min_price, $this->getSymbolExRate($cur)),
                    $list_brand
                );
            }
        }

        return $this->replaceLang($list_brand);
    }

    /*
     * CATALOG EXIST
     * */
    public function searchListCatalog($where_art_id_str, $mfa_id = 0, $model = "", $status_auto = 0, $order_status = 0)
    {
        $db = DbSingleton::getTokoDb();
        $exRate = new ExRateClass();
        $client = new ClientClass();

        $client_id = $this->getClient();
        $t_point_id = $this->getTpointID();
        $cur = $this->getCurrentExRate();

        session_start();
        $temp_key = session_id();
        $mas = [];

        list($error, $list) = $this->getSearchMessages();

        if ($where_art_id_str !== "") {

            $this->createTemporarySearchTable($temp_key);

            $r = $this->getTemporarySearchTable2($where_art_id_str);
            $n = $db->num_rows($r);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id             = (int)$db->result($r, $i - 1, "ART_ID");
                    $brand_id           = (int)$db->result($r, $i - 1, "BRAND_ID");
                    $brand_name         = $db->result($r, $i - 1, "BRAND_NAME");
                    $article_nr_displ   = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name       = $db->result($r, $i - 1, "NAME");
                    $suppl_id           = (int)$db->result($r, $i - 1, "suppl_id");
                    $stock              = (int)$db->result($r, $i - 1, "AMOUNT");
                    $storage_id         = (int)$db->result($r, $i - 1, "storage_id");
                    $return_days        = (int)$db->result($r, $i - 1, "return_delay");

                    $price = $this->getArticlePrice($art_id);
                    
                    if ($suppl_id !== 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }
                    
                    $price = $exRate->getExRatePrice($price, $cur);
                    
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }

                    $deliveryData           = $this->getTpointDeliveryInfo($t_point_id, $storage_id);
                    $delivery_info          = $deliveryData["info"];
                    $delivery_days          = (int)$deliveryData["days"];
                    $delivery_short_info    = $deliveryData["short"];
                    
                    if ($suppl_id !== 0) {
                        $deliveryData           = $this->getTpointSupplDeliveryInfo($t_point_id, $suppl_id, $storage_id);
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = (int)$deliveryData["days"];
                        $delivery_short_info    = $deliveryData["short"];
                    }

                    $status = ((int)$suppl_id === 0) ? 1 : 0;
                    $article_name = str_replace('"', "''" , $article_name);

                    if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                        $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                        VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', \"$article_name\", '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                    }
                }

                // from cheap to rich
                if ($order_status === 1) {
                    $oderBy = "`status`, `price`";
                }
                // from rich to cheap
                elseif ($order_status === 2) {
                    $oderBy = "`status`, `price` DESC";
                }
                // default status + price order
                else {
                    $oderBy = "`status`, `price`, `article_nr_displ`";
                }

                $temp_arr = [];
                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_" . $temp_key . "` ORDER BY $oderBy");
                $n = $db->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $art_id                 = (int)$db->result($r, $i - 1, "art_id");
                    $article_nr_displ       = $db->result($r, $i - 1, "article_nr_displ");
                    $brand_id               = (int)$db->result($r, $i - 1, "brand_id");
                    $brand_name             = $db->result($r, $i - 1, "brand_name");
                    $article_name = $this->getArticleName($art_id);
                    $delivery_days          = (int)$db->result($r, $i - 1, "delivery_days");
                    $delivery_info          = $db->result($r, $i - 1, "delivery_info");
                    $delivery_short_info    = $db->result($r, $i - 1, "delivery_short_info");
                    $stock                  = (int)$db->result($r, $i - 1, "stock");
                    $price                  = (float)$db->result($r, $i - 1, "price");
                    $suppl_id               = (int)$db->result($r, $i - 1, "suppl_id");
                    $storage_id             = (int)$db->result($r, $i - 1, "storage_id");
                    $return_days            = (int)$db->result($r, $i - 1, "return_days");
                    $status                 = (int)$db->result($r, $i - 1, "status");

                    $temp_arr[] = compact("art_id", "article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                }

                foreach ($temp_arr as $value) {
                    $art_id                 = (int)$value["art_id"];
                    $article_nr_displ       = $value["article_nr_displ"];
                    $brand_id               = (int)$value["brand_id"];
                    $brand_name             = $value["brand_name"];
                    $article_name           = $value["article_name"];
                    $delivery_days          = (int)$value["delivery_days"];
                    $delivery_info          = $value["delivery_info"];
                    $delivery_short_info    = $value["delivery_short_info"];
                    $stock                  = (int)$value["stock"];
                    $price                  = (float)$value["price"];
                    $suppl_id               = (int)$value["suppl_id"];
                    $storage_id             = (int)$value["storage_id"];
                    $return_days            = (int)$value["return_days"];
                    $status                 = (int)$value["status"];

                    if (!isset($mas[$art_id])) {
                        $mas[$art_id][0] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                    }

                    elseif (
                        (
                            $price > 0
                            && $stock > 0
                            && (
                            (($price <= $mas[$art_id][0]["price"] && $delivery_days === (int)$mas[$art_id][0]["delivery_days"]) || ($delivery_days <= (int)$mas[$art_id][0]["delivery_days"] && $price === $mas[$art_id][0]["price"]))
                            )
                        ) || (
                            $price > 0
                            && $stock > 0
                            && (
                                empty((float)$mas[$art_id][0]["price"]) || (int)$mas[$art_id][0]["stock"] === 0
                            )
                        )
                    ) {
                        // fixed price order
                        unset($mas[$art_id]);
                        $mas[$art_id][0] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                    }
                }

                // delete temp table
                $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

                if (empty($mas)) {
                    $list = $this->getHtmlForm("error/nothing_found");
                    $list = str_replace("{error_nothing_found}", $this->err1, $list);
                    return array($list, "", "", 0);
                }

                // show search list
                $list = $this->outSearchList($list, $error, $mas, "", 0, $status_auto, $mfa_id, $model);
            }

            if (count($mas) < 1) {
                $list = $error;
            }

        }

        return $list;
    }

    /*
     * CATALOG ANALOGS LIST
     * */
    public function shortSearchList($art_id_search)
    {
        $art_id_search = $this->getUrlNumber($art_id_search);
        $db = DbSingleton::getTokoDb();
        $exRate = new ExRateClass();
        $client = new ClientClass();

        $client_id  = $this->getClient();
        $t_point_id  = $this->getTpointID();
        $cur        = $this->getCurrentExRate();

        session_start();
        $temp_key   = session_id();
        $mas        = [];
        $list       = "";

        $article_nr_search  = $this->getArticleSearch($art_id_search);
        $brand_nr_search    = $this->getArticleBrand($art_id_search);

        $arts = [];
        $r = $db->query("SELECT DISTINCT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID)
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_nr_search AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE));");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[] = $art_id;
        }
        
        $where_art_id_str = implode(",", $arts);

        if ($where_art_id_str !== "") {
            $this->createTemporarySearchTable($temp_key);
            $error = $this->getSearchMessages()[0];

            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search);
            $n = $db->num_rows($r);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id             = (int)$db->result($r, $i - 1, "ART_ID");
                    $brand_id           = (int)$db->result($r, $i - 1, "BRAND_ID");
                    $brand_name         = $db->result($r, $i - 1, "BRAND_NAME");
                    $article_nr_displ   = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name       = $db->result($r, $i - 1, "NAME");
                    $return_days        = (int)$db->result($r, $i - 1, "return_delay");
                    $suppl_id           = (int)$db->result($r, $i - 1, "suppl_id");
                    $stock              = (int)$db->result($r, $i - 1, "AMOUNT");
                    $storage_id         = (int)$db->result($r, $i - 1, "storage_id");
                    $format_name        = $this->getFormatArticle($article_nr_displ);

                    $price = $this->getArticlePrice($art_id);
                    
                    if ($suppl_id !== 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }
                    
                    $price = $exRate->getExRatePrice($price, $cur);
                    
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }

                    $deliveryData           = $this->getTpointDeliveryInfo($t_point_id, $storage_id);
                    $delivery_info          = $deliveryData["info"];
                    $delivery_days          = (int)$deliveryData["days"];
                    $delivery_short_info    = $deliveryData["short"];

                    if ($suppl_id !== 0) {
                        $deliveryData           = $this->getTpointSupplDeliveryInfo($t_point_id, $suppl_id, $storage_id);
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = (int)$deliveryData["days"];
                        $delivery_short_info    = $deliveryData["short"];
                    }

                    // ORDER BY search art and suppl_id
                    if (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id === 0) ? 1 : 0;
                    }

                    $article_name = str_replace('"', "''" , $article_name);

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if ($price > 0 || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                        
                        if ($stock > 0 || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                            // visible suppl storage
                            $article_name = str_replace('"', "''" , $article_name);
                            
                            if ($art_id_search !== $art_id && $this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_" . $temp_key . "` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', \"$article_name\", '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                            }
                        }
                    }
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_" . $temp_key . "` ORDER BY `status` DESC, `article_nr_displ`;");
                $n = $db->num_rows($r);

                if ($n === 1) {
                    $stock = (int)$db->result($r, 0, "stock");
                    $price = (float)$db->result($r, 0, "price");

                    if (empty($stock) && empty($price)) {
                        $list = $this->getHtmlForm("error/nothing_found");
                        $list = str_replace("{error_nothing_found}", $this->err1, $list);
                        return array($list, "", "", 0);
                    }
                }

                for ($i = 1; $i <= $n; $i++) {
                    $art_id                 = (int)$db->result($r, $i - 1, "art_id");
                    $article_nr_displ       = $db->result($r, $i - 1, "article_nr_displ");
                    $brand_id               = (int)$db->result($r, $i - 1, "brand_id");
                    $brand_name             = $db->result($r, $i - 1, "brand_name");
                    $article_name           = $db->result($r, $i - 1, "article_name");
                    $delivery_days          = (int)$db->result($r, $i - 1, "delivery_days");
                    $delivery_info          = $db->result($r, $i - 1, "delivery_info");
                    $delivery_short_info    = $db->result($r, $i - 1, "delivery_short_info");
                    $stock                  = (int)$db->result($r, $i - 1, "stock");
                    $price                  = (float)$db->result($r, $i - 1, "price");
                    $suppl_id               = (int)$db->result($r, $i - 1, "suppl_id");
                    $storage_id             = (int)$db->result($r, $i - 1, "storage_id");
                    $return_days            = (int)$db->result($r, $i - 1, "return_days");
                    $status                 = (int)$db->result($r, $i - 1, "status");

                    $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                }

                // delete temp table
                $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

                // delete empty stocks and prices
                $mas = $this->deleteEmptyPosition($mas);
                $mas = $this->deleteSupplPosition($mas);
                $mas = $this->deleteRepeatPosition($mas);

                if (empty($mas)) {
                    $list = $this->getHtmlForm("error/nothing_found");
                    $list = str_replace("{error_nothing_found}", $this->err1, $list);
                    return $this->replaceLang($list);
                }

                // sort by delivery and price
                foreach ($mas as $mas_key => $mas_val) {
                    $mas[$mas_key] = $this->multiSort($mas_val, "delivery_days", "price");
                }

                // sort like: first = min delivery, second = min price, else = default
                $mas = $this->sortByMinStock($mas);

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, 1);

                // show search list
                $list = $this->outSearchList($list, $error, $mas, $other_storages);
            }

            if (count($mas) < 1) {
                $list = "";
            }
        }

        return $this->replaceLang($list);
    }

    /*
     * SEARCH CACHE
     * */
    public function getArticlePrices($where_art_id_str): array
    {
        $dbt = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $client_id = $this->getClient();
        list($price_lvl, $margin_price_lvl) = $this->getDpClientPriceLevels($client_id);

        $r = mysqli_fetch_all($dbt->query("SELECT t2apr.price_$price_lvl price, t2apr.cash_id, t2a.ART_ID 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id = t2a.ART_ID)
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID)
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2apr.in_use = '1';"), MYSQLI_ASSOC);
        $prices = [];
        foreach ($r as $row) {
            $price      = (float)$row["price"];
            $cash_id    = (int)$row["cash_id"];
            $price      = $this->getPriceRatingExRate($price, $cash_id, 1);

            if ($margin_price_lvl > 0) {
                $price += round($price * $margin_price_lvl / 100, 2);
            }

            if ($cash_id === 1) {
                $price = $client->getClientPriceRounding($client_id, $price);
            }

            $prices[$row["ART_ID"]] = $price;
        }

        return $prices;
    }

    public function getArticleSupplPrices($where_art_id_str): array
    {
        $dbt = DbSingleton::getTokoDb();
        $db = DbSingleton::getDbm();

        $exRate = new ExRateClass();
        $client = new ClientClass();

        $t_point = $this->getTpointID();
        $client_id = $this->getClient();
        $price = 0;
        list(, , $price_suppl_lvl, $margin_price_suppl_lvl, $client_vat) = $this->getDpClientPriceLevels($client_id);

        $r = $dbt->query("SELECT t2a.ART_ID, t2si.client_storage_id, t2si.price_usd, t2si.suppl_id, acvc.*, t2si.suppl_id, tpsf.margin, tpsf.delivery, tpsf.margin2 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
            LEFT OUTER JOIN {$db->getDbName()}.A_CLIENTS_VAT_CONDITIONS acvc ON ({$db->getDbName()}.acvc.client_id = t2si.suppl_id)
            LEFT OUTER JOIN `T_POINT_SUPPL_FM` tpsf ON (tpsf.suppl_id = t2si.suppl_id AND tpsf.suppl_storage_id = t2si.client_storage_id)
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2si.status = 1 AND tpsf.tpoint_id = $t_point AND tpsf.price_rating_id = '$price_suppl_lvl' AND tpsf.price_from <= t2si.price_usd AND tpsf.price_to >= t2si.price_usd;");
        $supplPrices = mysqli_fetch_all($r, MYSQLI_ASSOC);

        $prices = [];
        foreach ($supplPrices as $supplPrice) {
            $suppl_price_usd = (float)$supplPrice["price_usd"];
            $price_suppl = $suppl_price_usd;

            if ($supplPrice["margin"] > 0) {
                $price = ($price_suppl + $price_suppl * $supplPrice["margin"] / 100) - $price_suppl;

                if ($price > $supplPrice["delivery"]) {
                    $price = ($price_suppl + $price_suppl * $supplPrice["margin"] / 100);
                }

                if ($price <= $supplPrice["delivery"]) {
                    $price = $price_suppl + $price_suppl * $supplPrice["margin2"] / 100 + $supplPrice["delivery"];
                }

                if ($margin_price_suppl_lvl > 0 && $margin_price_suppl_lvl !== "") {
                    $price = $price + $price * $margin_price_suppl_lvl / 100;
                }

                if ($client_vat === 1) {
                    
                    if ((int)$supplPrice["price_in_vat"] === 0 && (int)$supplPrice["show_in_vat"] === 1 && (int)$supplPrice["price_add_vat"] === 1) {
                        $price = $price + $price * 20 / 100;
                    }
                    
                    if ((int)$supplPrice["price_in_vat"] === 0 && (int)$supplPrice["show_in_vat"] === 0) {
                        $price = 0;
                    }
                }
            }

            $price = round($price, 2);
            $cur_usd = $exRate->getExRate("dollar");
            $price *= $cur_usd;
            $price = $client->getClientPriceRounding($client_id, $price);
            $prices[$supplPrice["ART_ID"]][$supplPrice["suppl_id"]][$supplPrice["client_storage_id"]] = $price;
        }

        return $prices;
    }

    public function getSalePointDeliveryInfo($t_point_id, $where_art_id_str): array
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");

        $r = $db->query("SELECT tpdt.delivery_days, tpdt.week_day, tpdt.time_from_del, tpdt.time_to_del, tpdt.storage_id 
        FROM `T_POINT_DELIVERY_TIME` tpdt
            JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.STORAGE_ID = tpdt.storage_id)
        WHERE t2asc.ART_ID IN ($where_art_id_str) AND tpdt.status = '1' AND tpdt.tpoint_id = '$t_point_id' AND tpdt.week_day = '$week_day' AND tpdt.time_from <= '$cur_time' AND tpdt.time_to >= '$cur_time' 
        ORDER BY tpdt.delivery_days;");
        $delivers = mysqli_fetch_all($r, MYSQLI_ASSOC);
        $array = [];

        foreach ($delivers as $deliver) {
            $delivery_days  = (int)$deliver["delivery_days"];
            $time_from_del  = substr($deliver["time_from_del"], 0, -3);
            $time_to_del    = substr($deliver["time_to_del"], 0, -3);
            $week           = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del       = date("d.m", strtotime(" + " . $delivery_days . " days"));

            if ($delivery_days === 0) {
                $today = "<span class=\"delivery-green\">{today_cap}</span>";
            } elseif ($delivery_days === 1) {
                $today = "<span class=\"delivery-blue\">{tomorrow_cap}</span>";
            } else {
                $today = "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>";
            }

            $info = "$today<br>$time_from_del - $time_to_del";
            $delivery_short_info = "$today<br>{with_cap} $time_from_del";
            $array[$deliver["storage_id"]] = compact("info", "delivery_days", "delivery_short_info");
        }

        return $array;
    }

    public function getSalePointSupplDeliveryInfo($t_point_id): array
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $result = [];

        $r = $db->query("SELECT `delivery_days`, `week_day`, `time_from_del`, `time_to_del`, `suppl_storage_id`, `suppl_id` 
        FROM `T_POINT_SUPPL_DELIVERY_TIME` 
        WHERE `status` = '1' AND `tpoint_id` = '$t_point_id' AND `week_day` = '$week_day' AND `time_from` <= '$cur_time' AND `time_to` >= '$cur_time';");
        $deliveryTimes = mysqli_fetch_all($r, MYSQLI_ASSOC);

        foreach ($deliveryTimes as $deliveryTime) {
            $delivery_days  = (int)$deliveryTime["delivery_days"];
            $time_from_del  = substr($deliveryTime["time_from_del"], 0, -3);
            $time_to_del    = substr($deliveryTime["time_to_del"], 0, -3);
            $week           = date("N", strtotime(" + " . $deliveryTime["delivery_days"] . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del       = date("d.m", strtotime(" + " . $deliveryTime["delivery_days"] . " days"));

            if ($delivery_days === 0) {
                $today = "<span class=\"delivery-green\">{today_cap}</span>";
            } elseif ($delivery_days === 1) {
                $today = "<span class=\"delivery-blue\">{tomorrow_cap}</span>";
            } else {
                $today = "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>";
            }

            $info = "$today<br>$time_from_del - $time_to_del";
            $delivery_short_info = "$today<br>{with_cap} $time_from_del";

            $result[$deliveryTime["suppl_id"]][$deliveryTime["suppl_storage_id"]] = [
                "info"                  => $info,
                "delivery_days"         => $deliveryTime["delivery_days"],
                "delivery_short_info"   => $delivery_short_info
            ];
        }

        return $result;
    }

    /*
     * Get Article image Type
     * */
    public function getIndexTypeImage($art_id, $article_nr_search, $article_nr_displ, $format_name, $brand_id, $brand_nr_search): string
    {
        $true_art_id    = $this->getArtID($article_nr_search);
        $brand_name     = $this->getBrandName($brand_nr_search);

        // ANALOGS
        $image_analog = $this->images . "/tcdanalogs/clone.svg";
        $index_type = "
        <img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_analog}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_analog} $article_nr_search $brand_name\">";

        // OE
        if ($this->checkOriginalEquipment($true_art_id, $format_name)) {
            $image_analog = $this->images . "/tcdanalogs/oe.svg";
            $index_type = "
            <img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_original}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_original} $article_nr_search $brand_name\">";
        }

        // INCLUDED
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 1)) {
            $image_analog = $this->images . "/tcdanalogs/chevron-square-down.svg";
            $index_type = "
            <img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_included}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_included} $article_nr_search $brand_name\">";
        }

        // PRESENTED
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 2)) {
            $image_analog = $this->images . "/tcdanalogs/chevron-square-up.svg";
            $index_type = "
            <img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_presented}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_presented} $article_nr_search $brand_name\">";
        }

        // COMPANION
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 3)) {
            $image_analog = $this->images . "/tcdanalogs/plus-square.svg";
            $index_type = "
            <img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_companion}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_companion} $article_nr_search $brand_name\">";
        }

        // REQUESTED
        if (($article_nr_search !== "") && ($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && ($brand_id === $brand_nr_search)) {
            $image_analog = $this->images . "/tcdanalogs/square.svg";
            $index_type = "
            <img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_requested}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_requested} $article_nr_search $brand_name\">";
            
            if ($this->getBrandType($brand_id)) {
                $image_analog = $this->images . "/tcdanalogs/oe.svg";
                $index_type .= "
                <img style=\"margin-left: 5px;\" data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_original}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_original} $article_nr_search $brand_name\">";
            }
        }

        return $index_type;
    }

    /*
     * Check visibility of SUPPL storage
     * */
    public function getSuppLStorageVisible($suppl_id, $storage_id): bool
    {
        $db = DbSingleton::getDbm();

        if ($suppl_id > 0) {
            $r = $db->query("SELECT `visible` FROM `A_CLIENTS_STORAGE` WHERE `client_id` = $suppl_id AND `id` = $storage_id LIMIT 1;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $visible = (int)$db->result($r, 0, "visible");

                if (empty($visible)) {
                    return false;
                }
            }
        }

        return true;
    }

    /*
     * Show SEARCH Line OR Card
     * */
    public function printCatalogList($id, $art_id, $article_nr_displ, $brand_id, $brand_name, $article_name, $stock, $price, $ll, $suppl_id, $delivery_days, $delivery_short_info, $storage_id, $status, $view = 0, $status_auto = 0, $mfa_id = 0, $model = "")
    {
        $formObj = new FormClass();
        $autoObj = new AutoClass();

        $cur                = $this->getCurrentExRate();
        $exRate_cap         = $this->getSymbolExRate($cur);
        $format_name        = $this->getFormatArticle($article_nr_displ);
        $format_brand_link  = $this->getBrandLink($brand_id);

        $form = ($view) ? $this->getHtmlForm("article_card") : $this->getHtmlForm("product_card");
        $product_link = $this->getSiteLink() . "$this->products_link/$format_name-$format_brand_link-$art_id/";

        $product_text = ($article_name === "") ? "{details_name_cap}" : $article_name;
        $format_product_text = ($article_name === "") ? "{details_name_cap}" : $this->formatArticleName($article_name);

        if ((int)$status_auto === 0) {
            $mfa_text = "";
            
            if ($mfa_id > 0) {
                $mfa_name = $autoObj->getMfaBrand($mfa_id);
                $mfa_text .= " {on_cap} $mfa_name";
                
                if ($model !== "") {
                    $mfa_text .= " $model";
                }
            }
            
            $product_text .= $mfa_text;
            $format_product_text .= $mfa_text;
        }

        $product_stock = $stock;
        
        if (((int)$suppl_id === 0) && (int)$stock > 10) {
            $product_stock = ">10";
        }

        $delivery_short_info = str_replace("<br>", " ", $delivery_short_info);
        
        if ((int)$delivery_days === 0 && (int)$suppl_id === 0) {
            $delivery_short_info = "<span class='delivery-green'>{send_done}</span>";
        }

        $flagData = $formObj->getCountryFlag($brand_id);
        $flagDisplay = (!$flagData) ? "none" : "";

        $productInfo = $formObj->getArticleInfoForm($art_id, 1);
        $productBtn = ((float)$price === 0.0) ? "none" : "";

        $photoData = $formObj->getArticleCatalogPhoto($art_id, $brand_id);
        $photoImgClass = ((int)$photoData["status"] === 0) ? "" : "filter-bw";

        $productTitle = "$article_name $brand_name $article_nr_displ";

        $form = str_replace(
            array("{product_i}", "{art_id}", "{brand_id}", "{product_name}", "{product_brand}", "{page_product_link}", "{product_text}", "{format_product_text}", "{product_stock}", "{product_real_stock}", "{product_storage_id}", "{product_suppl_id}", "{product_delivery_class}", "{product_delivery_short_info}", "{product_true_price}", "{product_kours_cap}", "{country_display}", "{flag_image}", "{country_name}", "{product_info}", "{product_button}", "{product_image}", "{product_image_class}", "{product_title}"),
            array($id, $art_id, $brand_id, $article_nr_displ, $brand_name, $product_link, $product_text, $format_product_text, $product_stock, $stock, $storage_id, $suppl_id, "", $delivery_short_info, $price, $exRate_cap, $flagDisplay, $flagData["flag"], $flagData["country"], $productInfo, $productBtn, $photoData["photo_name"], $photoImgClass, $productTitle),
        $form);

        if ((int)$stock === 0) {
            $form = str_replace(array("{price_row_status}", "{soldout_row_status}"), array("none", ""), $form);
        }

        if ($status) {
            // PRICE & STOCK NICE
            $form = str_replace(array("{price_row_status}", "{soldout_row_status}"), array("", "none"), $form);
        } else {
            // PRICE & STOCK = 0
            $form = str_replace(array("{price_row_status}", "{soldout_row_status}"), array("none", ""), $form);
        }

        // status_auto
        if ((int)$status_auto === 2) {
            $form = str_replace("{applicable_display}", "dnone", $form);
        }

        $auto_typ_id = $this->getCookieAuto();
        
        if ($auto_typ_id !== "") {

            if ($this->checkT2Link($auto_typ_id, $art_id)) {
                $form = str_replace(array("{applicable_display}", "{applicable_display_text}", "{applicable_onclick}"), array("applicable-active", "{is_applicable}", ""), $form);
            } else {

                if ((int)$status_auto === 1) {
                    $form = str_replace("{applicable_display}", "dnone", $form);
                }
                
                $form = str_replace(array("{applicable_display}", "{applicable_display_text}", "{applicable_onclick}"), array("", "{is_didnt_applicable}", ""), $form);
            }
        }

        if ((int)$status_auto === 1) {
            $form = str_replace("{applicable_display}", "dnone", $form);
        }
        
        $form = str_replace(array("{applicable_display}", "{applicable_display_text}", "{applicable_onclick}"), array("", "{is_not_applicable}", "toggleNavMob();"), $form);

        $list = $form;
        
        if (!$view) {
            $list .= $ll;
        }

        return $this->replaceLang($list);
    }

    /*
     * check action price
     * */
    public function checkActionPrice($art_id)
    {
        $db = DbSingleton::getDbm();
        $client_id = $this->getClient();
        $categories = $actions = [];

        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id` = $client_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $categories[] = $db->result($r, $i - 1, "client_category");
        }
        $categories = implode(",", $categories);

        $r = $db->query("SELECT `id` FROM `ACTION_CLIENTS` WHERE `art_id` = $art_id AND `status` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $action_id = $db->result($r, $i - 1, "id");

            $r2 = $db->query("SELECT 1 FROM `ACTION_CLIENTS_LIST` WHERE `action_id` = $action_id AND `client_id` = $client_id;");
            $n2 = $db->num_rows($r2);

            if ($n2 > 0) {
                $actions[] = $action_id;
            }

            if ($categories !== "") {
                $r3 = $db->query("SELECT 1 FROM `ACTION_CLIENTS_CATEGORY` WHERE `action_id` = $action_id AND `category_id` IN ($categories);");
                $n3 = $db->num_rows($r3);

                if ($n3 > 0) {
                    $actions[] = $action_id;
                }
            }
        }

        $actions = implode(",", $actions);
        
        if (empty($actions)) {
            return false;
        }

        $r = $db->query("SELECT `id`, `amount`, `max_amount`, `price`, `data` FROM `ACTION_CLIENTS` WHERE `id` IN ($actions) AND `status` = 1 LIMIT 1;");
        $action_id  = $db->result($r, 0, "id");
        $amount     = $db->result($r, 0, "amount");
        $max_amount = $db->result($r, 0, "max_amount");
        $price      = $db->result($r, 0, "price");
        $data       = $db->result($r, 0, "data");

        if ($this->checkActionAmount($art_id, $max_amount, $data)) {
            return array($action_id, $amount, $price, $max_amount, $data);
        }

        return false;
    }

    /*
     * check action amount
     * */
    public function checkActionAmount($art_id, $max_amount, $data): bool
    {
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();
        $data_today = date("Y-m-d");
        $all_amount = 0;

        $r = $dbt->query("SELECT `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` = $art_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $amount     = $db->result($r, $i - 1, "AMOUNT");
            $all_amount += $amount;
        }

        $r = $db->query("SELECT `amount` FROM `J_DP_STR` WHERE `art_id` = $art_id AND `status_dps` = 93;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $amount     = $db->result($r, $i - 1, "amount");
            $all_amount += $amount;
        }

        return ($data >= $data_today && $all_amount > $max_amount);
    }

    /*
     * Export Price Rating (CRON)
     * */
    public function getArticlePriceClient($art_id, $client_id, $cur)
    {
        $dbt = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $cash_id = 2;

        $r = $dbt->query("SELECT `cash_id` FROM `T2_ARTICLES_PRICE_RATING` WHERE `art_id` = $art_id AND `in_use` = 1 ORDER BY `data_update` DESC LIMIT 1;");
        $n = $dbt->num_rows($r);

        if ($n > 0) {
            $cash_id = (int)$dbt->result($r, 0, "cash_id");
        }

        $price = 0;
        list($price_lvl, $margin_price_lvl) = $this->getDpClientPriceLevels($client_id);
        $markup_min = $client->getClientMarkupMin($client_id);

        $r = $dbt->query("SELECT t2apr.price_$price_lvl, t2apr.minMarkup, t2aps.OPER_PRICE
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id = t2a.ART_ID)
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_STOCK` t2aps ON (t2aps.ART_ID = t2a.ART_ID)
        WHERE t2a.ART_ID = $art_id AND t2apr.in_use = '1' LIMIT 1;");
        $n = (int)$dbt->num_rows($r);

        if ($n === 1) {
            $price      = (float)$dbt->result($r, 0, "price_" . $price_lvl);
            $minMarkup  = (float)$dbt->result($r, 0, "minMarkup");
            $operativePrice = (float)$dbt->result($r, 0, "OPER_PRICE");

            $float_price = $price;

            // 1
            if ($margin_price_lvl > 0) {
                $price = $float_price + round($price * $margin_price_lvl / 100, 2);
            }

            // 2
            if ($margin_price_lvl < 0 && $markup_min === 0) {
                $price_minus = $price + ($price * $margin_price_lvl / 100);
                $operativeLimit = $operativePrice + ($operativePrice * $minMarkup / 100);

                if ($price_minus >= $operativeLimit) {
                    $price = $price_minus;
                } elseif ($operativeLimit < $price) {
                    $price = $operativeLimit;
                }
            }

            // 3
            $art_cash_id = $this->getArticlePriceRatingCash($art_id);

            if ($margin_price_lvl < 0 && $markup_min > 0) {
                $price                  = $this->getPriceRatingExRate($price, $art_cash_id, $cash_id);
                $procPriceMargin        = $price - ($price * abs($margin_price_lvl) / 100);
                $procOperativePriceMin  = $operativePrice + ($operativePrice * $markup_min / 100);

                if ($procPriceMargin >= $procOperativePriceMin) {
                    $price = $procPriceMargin;
                } elseif ($procOperativePriceMin <= $price) {
                    $price = $procOperativePriceMin;
                }

                $price = $this->getPriceRatingExRate($price, $cash_id, $art_cash_id);
            }

            $price = $this->getPriceRatingExRate($price, $cash_id, $cur);
            
            if ($cur === 1) {
                $price = $client->getClientPriceRounding($client_id, $price);
            }
        }

        return $price;
    }

    public function getArticlePrice($art_id)
    {
        $dbt = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $client_id = $this->getClient();
        $markup_min = $client->getClientMarkupMin($client_id);
        $price = 0;
        list($price_lvl, $margin_price_lvl) = $this->getDpClientPriceLevels($client_id);

        $r = $dbt->query("SELECT t2apr.price_$price_lvl, t2apr.cash_id, t2apr.minMarkup, t2aps.OPER_PRICE
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id = t2a.ART_ID)
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_STOCK` t2aps ON (t2aps.ART_ID = t2a.ART_ID)
        WHERE t2a.ART_ID = $art_id AND t2apr.in_use = '1' LIMIT 1;");
        $n = (int)$dbt->num_rows($r);

        if ($n === 1) {
            $price      = (float)$dbt->result($r, 0, "price_" . $price_lvl);
            $minMarkup  = (float)$dbt->result($r, 0, "minMarkup");
            $operativePrice = (float)$dbt->result($r, 0, "OPER_PRICE");
            $cash_id    = (int)$dbt->result($r, 0, "cash_id");

            if ($margin_price_lvl > 0) {
                $price += round($price * $margin_price_lvl / 100, 2);
            }

            if ($margin_price_lvl < 0 && $markup_min === 0) {
                $price_minus = $price + ($price * $margin_price_lvl / 100);
                $operativeLimit = $operativePrice + ($operativePrice * $minMarkup / 100);

                if ($price_minus >= $operativeLimit) {
                    $price = $price_minus;
                } elseif ($operativeLimit < $price) {
                    $price = $operativeLimit;
                }
            }

            if ($margin_price_lvl < 0 && $markup_min > 0) {
                $price = $this->getPriceRatingExRate($price, $cash_id, 2);
                $procPriceMargin = $price - ($price * abs($margin_price_lvl) / 100);
                $procOperativePriceMin = $operativePrice + ($operativePrice * $markup_min / 100);

                if ($procPriceMargin >= $procOperativePriceMin) {
                    $price = $procPriceMargin;
                } elseif ($procOperativePriceMin <= $price) {
                    $price = $procOperativePriceMin;
                }

                $price = $this->getPriceRatingExRate($price, 2, $cash_id);
            }

            $price = $this->getPriceRatingExRate($price, $cash_id, 1);
            $price = $client->getClientPriceRounding($client_id, $price);
        }

        return $price;
    }

    public function getArticleSupplPrice($art_id, $suppl_id, $suppl_storage_id): float
    {
        $dbt = DbSingleton::getTokoDb();
        $exRate = new ExRateClass();
        $client = new ClientClass();
        $t_point = $this->getTpointID();
        $client_id = $this->getClient();
        $price = 0;

        list(, , $price_suppl_lvl, $margin_price_suppl_lvl, $client_vat) = $this->getDpClientPriceLevels($client_id);

        $r = $dbt->query("SELECT t2si.price_usd 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
        WHERE t2a.ART_ID = $art_id AND t2si.suppl_id = $suppl_id LIMIT 1;");
        $n = $dbt->num_rows($r);

        if ($n === 1) {
            $suppl_price_usd = (float)($dbt->result($r, 0, "price_usd"));
            list($price_in_vat, $show_in_vat, $price_add_vat) = $this->getSupplVatConditions($suppl_id);
            $price_suppl = $suppl_price_usd;
            //?
            $price = $suppl_price_usd;

            list($suppl_margin_fm, $suppl_delivery_fm, $suppl_margin2_fm) = $this->getSalePointSupplFm($t_point, $suppl_id, $suppl_storage_id, $price_suppl, $price_suppl_lvl);

            if ($suppl_margin_fm > 0) {
                $price = ($price_suppl + $price_suppl * $suppl_margin_fm / 100) - $price_suppl;

                if ($price > $suppl_delivery_fm) {
                    $price = ($price_suppl + $price_suppl * $suppl_margin_fm / 100);
                }
                
                if ($price <= $suppl_delivery_fm) {
                    $price = $price_suppl + $price_suppl * $suppl_margin2_fm / 100 + $suppl_delivery_fm;
                }

                if ($margin_price_suppl_lvl > 0 && $margin_price_suppl_lvl !== "") {
                    $price = $price + $price * $margin_price_suppl_lvl / 100;
                }

                if ($client_vat === 1) {
                    
                    if ($price_in_vat === 0 && $show_in_vat === 1 && $price_add_vat === 1) {
                        $price = $price + $price * 20 / 100;
                    }
                    
                    if ($price_in_vat === 0 && $show_in_vat === 0) {
                        $price = 0;
                    }
                }
            }

            $price = round($price, 2);
        }

        $cur_usd = $exRate->getExRate("dollar");
        $price *= $cur_usd;
        
        return $client->getClientPriceRounding($client_id, $price);
    }

    public function getDpClientPriceLevels($client_id): array
    {
        $db = DbSingleton::getDbm();
        $price_lvl = $margin_price_lvl = $price_suppl_lvl = $margin_price_suppl_lvl = $client_vat = 0;

        $r = $db->query("SELECT `price_lvl`, `margin_price_lvl`, `price_suppl_lvl`, `margin_price_suppl_lvl`, `client_vat` 
        FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $price_lvl                  = (int)$db->result($r, 0, "price_lvl");
            $price_lvl++;
            $margin_price_lvl           = (float)$db->result($r, 0, "margin_price_lvl");
            $price_suppl_lvl            = (int)$db->result($r, 0, "price_suppl_lvl");
            $price_suppl_lvl++;
            $margin_price_suppl_lvl     = (float)$db->result($r, 0, "margin_price_suppl_lvl");
            $client_vat                 = (int)$db->result($r, 0, "client_vat");
        }

        return array($price_lvl, $margin_price_lvl, $price_suppl_lvl, $margin_price_suppl_lvl, $client_vat);
    }

    public function getSupplVatConditions($suppl_id): array
    {
        $db = DbSingleton::getDbm();
        $price_in_vat = $show_in_vat = $price_add_vat = 0;

        $r = $db->query("SELECT `price_in_vat`, `show_in_vat`, `price_add_vat` FROM `A_CLIENTS_VAT_CONDITIONS` WHERE `client_id` = $suppl_id LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $price_in_vat   = (int)$db->result($r, 0, "price_in_vat");
            $show_in_vat    = (int)$db->result($r, 0, "show_in_vat");
            $price_add_vat  = (int)$db->result($r, 0, "price_add_vat");
        }

        return array($price_in_vat, $show_in_vat, $price_add_vat);
    }

    public function getSalePointSupplFm($t_point_id, $suppl_id, $suppl_storage_id, $price_suppl, $price_suppl_lvl): array
    {
        $db = DbSingleton::getTokoDb();
        $margin = $delivery = $margin2 = 0;

        $r = $db->query("SELECT `margin`, `delivery`, `margin2` FROM `T_POINT_SUPPL_FM` 
        WHERE `tpoint_id` = '$t_point_id' AND `suppl_id` = '$suppl_id' AND `suppl_storage_id` = '$suppl_storage_id' AND `price_from` <= '$price_suppl' 
        AND `price_to` >= '$price_suppl' AND `price_rating_id` = '$price_suppl_lvl' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $margin     = (int)$db->result($r, 0, "margin");
            $delivery   = (float)$db->result($r, 0, "delivery");
            $margin2    = (int)$db->result($r, 0, "margin2");
        }

        return array($margin, $delivery, $margin2);
    }

    public function getSalePointDeliveryTime($delivery_days): string
    {
        $current_week_day = (int)date("N");

        if (($delivery_days + $current_week_day) === 7) {
            $delivery_days++;
        }

        if ($delivery_days === 0) {
            $delivery_time = "<span class=\"delivery-green\">{today_cap}</span>";
        } elseif ($delivery_days === 1) {
            $delivery_time = "<span class=\"delivery-blue\">{tomorrow_cap}</span>";
        } else {
            $date_del       = date("d.m", strtotime(" + " . $delivery_days . " days"));
            $week           = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $delivery_time = "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>";
        }

        return $delivery_time;
    }

    public function getTpointDeliveryInfo($t_point_id, $storage_id): array
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $delivery_days = 0;
        $info = $short_info = $time_from_del = "";

        $r = $db->query("SELECT `delivery_days`, `time_from_del`, `time_to_del` 
        FROM `T_POINT_DELIVERY_TIME`
        WHERE `status` = '1' AND `tpoint_id` = '$t_point_id' AND `storage_id` = '$storage_id' AND `week_day` = '$week_day' AND `time_from` <= '$cur_time' AND `time_to` >= '$cur_time' 
        ORDER BY `delivery_days` LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $delivery_days  = (int)$db->result($r, 0, "delivery_days");
            $time_from_del  = substr($db->result($r, 0, "time_from_del"), 0, -3);
            $time_to_del    = substr($db->result($r, 0, "time_to_del"), 0, -3);
            $week           = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del       = date("d.m", strtotime(" + " . $delivery_days . " days"));

            if ($delivery_days === 0) {
                $today = "<span class=\"delivery-green\">{today_cap}</span>";
            } elseif ($delivery_days === 1) {
                $today = "<span class=\"delivery-blue\">{tomorrow_cap}</span>";
            } else {
                $today = "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>";
            }

            $info       = "$today<br>$time_from_del - $time_to_del";
            $short_info = "$today<br>{with_cap} $time_from_del";
        }

        return array(
            "info"      => $info,
            "days"      => $delivery_days,
            "short"     => $short_info,
            "time_from" => $time_from_del
        );
    }

    public function getTpointSupplDeliveryInfo($t_point_id, $suppl_id, $suppl_storage_id): array
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $delivery_days = 0;
        $info = $short_info = $time_from_del = "";

        $r = $db->query("SELECT `delivery_days`, `time_from_del`, `time_to_del` 
        FROM `T_POINT_SUPPL_DELIVERY_TIME` 
        WHERE `status` = '1' AND `tpoint_id` = '$t_point_id' AND `suppl_storage_id` = '$suppl_storage_id' AND `suppl_id` = '$suppl_id' AND `week_day` = '$week_day' 
        AND `time_from`<='$cur_time' AND `time_to`>='$cur_time' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $delivery_days  = (int)$db->result($r, 0, "delivery_days");
            $time_from_del  = substr($db->result($r, 0, "time_from_del"), 0, -3);
            $time_to_del    = substr($db->result($r, 0, "time_to_del"), 0, -3);
            $week           = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del       = date("d.m", strtotime(" + " . $delivery_days . " days"));

            if ($delivery_days === 0) {
                $today = "<span class=\"delivery-green\">{today_cap}</span>";
            } elseif ($delivery_days === 1) {
                $today = "<span class=\"delivery-blue\">{tomorrow_cap}</span>";
            } else {
                $today = "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>";
            }

            $info       = "$today<br>$time_from_del - $time_to_del";
            $short_info = "$today<br>{with_cap} $time_from_del";
        }

        return array(
            "info"  => $info,
            "days"  => $delivery_days,
            "short" => $short_info,
            "time_from" => $time_from_del
        );
    }

    /*
     * Get article default cash
     * table toko_dba.T2_ARTICLES_PRICE_RATING
     * */
    public function getArticlePriceRatingCash($art_id): int
    {
        $db = DbSingleton::getTokoDb();
        $cash_id = 2;

        $r = $db->query("SELECT `cash_id` FROM `T2_ARTICLES_PRICE_RATING` WHERE `art_id` = $art_id AND `in_use` = 1 LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $cash_id = (int)$db->result($r, 0, "cash_id");
        }

        if ($cash_id === 0) {
            $db->query("UPDATE `T2_ARTICLES_PRICE_RATING` SET `cash_id` = 2 WHERE `art_id` = $art_id AND `in_use` = 1 LIMIT 1;");
            $cash_id = 2;
        }

        return $cash_id;
    }

    public function getPriceRatingExRate($price, $cash_id_from, $cash_id_to): float
    {
        $exRate = new ExRateClass();
        $usd_to_uah = $exRate->getExRate("dollar");
        $eur_to_uah = $exRate->getExRate("euro");

        if ($cash_id_from === $cash_id_to) {
            $price *= 1;
        }

        if ($cash_id_from === 1 && $cash_id_to === 2) {
            $price /= $usd_to_uah;
        }

        if ($cash_id_from === 1 && $cash_id_to === 3) {
            $price /= $eur_to_uah;
        }

        if ($cash_id_from === 2 && $cash_id_to === 1) {
            $price *= $usd_to_uah;
        }

        if ($cash_id_from === 3 && $cash_id_to === 1) {
            $price *= $eur_to_uah;
        }

        if ($cash_id_from === 2 && $cash_id_to === 3) {
            $price *= ($usd_to_uah / $eur_to_uah);
        }

        if ($cash_id_from === 3 && $cash_id_to === 2) {
            $price *= ($eur_to_uah / $usd_to_uah);
        }

        return round($price, 2);
    }

    public function deleteEmptyNulls($mas): array
    {
        $arr = [];

        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {

                if ((int)$val["stock"] === 0) {
                    $arr[$mas_key][0] = $mas[$mas_key][$key];
                    unset($mas[$mas_key][$key]);
                }
                elseif (empty((float)$val["price"])) {
                    $arr[$mas_key][0] = $mas[$mas_key][$key];
                    unset($mas[$mas_key][$key]);
                }
            }
        }

        foreach ($mas as $mas_key => $mas_val) {

            if (empty($mas_val)) {
                unset($mas[$mas_key]);
            }
        }

        return array($mas, $arr);
    }

    /*
     * delete article from list with price = 0 OR stock = 0
     * */
    public function deleteEmptyPosition($mas)
    {
        $i = $count_suggest = 0;

        foreach ($mas as $mas_val) {
            foreach ($mas_val as $val) {

                if ($i === 0) {
                    $count_suggest++;
                }
            }
            $i++;
        }

        $i = 0;
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {

                if (($i === 0) && $count_suggest > 1) {

                    if ((int)$val["stock"] === 0) {
                        unset($mas[$mas_key][$key]);
                    }
                    elseif (empty((float)$val["price"])) {
                        unset($mas[$mas_key][$key]);
                    }
                }
            }
            $i++;
        }

        foreach ($mas as $mas_key => $mas_val) {

            if (empty($mas_val)) {
                unset($mas[$mas_key]);
            }
        }

        return $mas;
    }

    /*
     * delete article from list with price = 0 OR stock = 0
     * */
    public function deleteEmptyPositionMain($mas)
    {
        $count_success = 0;

        foreach ($mas as $value) {

            if ($value["stock"] > 0 && $value["price"] > 0) {
                $count_success++;
            }
        }

        if ($count_success > 0) {
            foreach ($mas as $key => $value) {

                if ((int)$value["stock"] === 0 ||  empty((float)$value["price"])) {
                    unset($mas[$key]);
                }
            }
        }

        return $mas;
    }

    /*
     * delete position, where suppl_id = 0 AND suppl_id > 0 (same ART_ID)
     * */
    public function deleteSupplPosition($mas)
    {
        $array_toko = [];
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $val) {

                if ((int)$val["suppl_id"] === 0) {
                    $array_toko[] = $mas_key;
                }
            }
        }

        $array_toko = array_unique($array_toko);
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {

                if ((int)$val["suppl_id"] !== 0 && in_array($mas_key, $array_toko, true)) {
                    unset($mas[$mas_key][$key]);
                }
            }
        }

        foreach ($mas as $mas_key => $mas_val) {

            if (empty($mas_val)) {
                unset($mas[$mas_key]);
            }
        }

        return $mas;
    }

    /*
     * delete position with repeat price AND delivery
     * */
    public function deleteRepeatPosition($mas)
    {
        $uniq = [];
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {
                $delivery_days  = (int)$val["delivery_days"];
                $delivery_info  = $val["delivery_info"];
                $price          = (float)$val["price"];
                $stock          = (int)$val["stock"];

                if (!empty($uniq)) {
                    foreach ($uniq as $uniqValue) {

                        if ($delivery_days === $uniqValue["delivery_days"] && $delivery_info === $uniqValue["delivery_info"] && $price === $uniqValue["price"]) {

                            if ($stock > $uniqValue["stock"]) {
                                $uniqKey = (int)$uniqValue["key"];
                            } else {
                                $uniqKey = $key;
                            }

                            unset($mas[$mas_key][$uniqKey], $uniq[$key]);
                        }
                    }
                }
                $uniq[$key] = compact("key", "delivery_days", "delivery_info", "price", "stock");
            }
            $uniq = [];
        }

        return $mas;
    }

    /*
     * sorted by min stock AND min price
     * */
    public function sortByMinStock($mas)
    {
        $min_key = $pred_key = 0;
        $min_pr = 99999999;

        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {

                if ($min_key !== 0) {

                    if ($mas[$pred_key][0]["price"] > $mas[$pred_key][$min_key]["price"] && $mas[$pred_key][0]["delivery_days"] > $mas[$pred_key][$min_key]["delivery_days"]) {
                        $null_key = 0;
                    } else {
                        $null_key = 1;
                    }

                    if (isset($mas[$pred_key][$min_key])) {
                        $temp = $mas[$pred_key][$min_key];
                        $mas[$pred_key][$min_key] = $mas[$pred_key][$null_key];
                        $mas[$pred_key][$null_key] = $temp;
                    }

                    $min_key = 0;
                }

                if (($val["price"] !== 0) && $val["price"] < $min_pr) {
                    $min_pr = $val["price"];
                    $min_key = $key;
                }
            }

            $pred_key = $mas_key;
            $min_pr = 99999999;
        }

        return $mas;
    }

    /*
     * show same articles with other storages
     * */
    public function showOtherStorages($mas, $cur, $view): array
    {
        $cur_cap = $this->getSymbolExRate($cur);
        $ll = $class = $hide = $border = $none = $arts = [];
        $i = $j = $double = $price_pred = 0;
        $min_price = 9999999;

        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $val) {

                $art_id = $mas_key;

                if (in_array($art_id, $arts, true)) {

                    if ($val["price"] < $price_pred) {

                        if ($double > 0) {

                            if (isset($ll[$i - 1])) {
                                $ll[$i - 1] = "";
                            }

                            if ($min_price > $val["price"]) {
                                $min_price = $val["price"];
                            }

                            if (!$view) {
                                $ll[$i] = "
                                <div class=\"row tables__row show_hidden\">
                                    <a id=\"fa-$art_id\" class=\"show_more\" onClick=\"showStorage('$art_id');\">
                                        {more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $cur_cap</span> <i class=\"rotate_anime fas fa-chevron-down\"></i>
                                    </a>
                                    <a id=\"fas-$art_id\" class=\"show_more none\" onClick=\"showStorage('$art_id');\">
                                        <span class=\"span-grey\">{collapse_cap}</span> <i class=\"rotate_anime fas fa-chevron-up\"></i>
                                    </a>
                                </div>";
                            } else {
                                $ll[$i] = "
                                <a href=\"" . $this->getSiteLink() . "$this->search_link/{content_search_number}/{content_brand_link}/\">
                                    {more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $cur_cap</span> >
                                </a>";
                            }

                            $hide[$i]   = "none";
                            $class[$i]  = "$art_id-hide";
                        }

                    } else {

                        if (isset($ll[$i - 1])) {
                            $ll[$i - 1] = "";
                        }

                        if ($min_price > $val["price"]) {
                            $min_price = $val["price"];
                        }

                        if (!$view) {
                            $ll[$i] = "
                            <div class=\"row tables__row show_hidden\">
                                <a id=\"fa-$art_id\" class=\"show_more\" onClick=\"showStorage('$art_id');\">
                                    {more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $cur_cap</span> <i class=\"rotate_anime fas fa-chevron-down\"></i>
                                </a>
                                <a id=\"fas-$art_id\" class=\"show_more none\" onClick=\"showStorage('$art_id');\">
                                    <span class=\"span-grey\">{collapse_cap}</span> <i class=\"rotate_anime fas fa-chevron-up\"></i>
                                </a>
                            </div>";
                        } else {
                            $ll[$i] = "
                            <a href=\"" . $this->getSiteLink() . "$this->search_link/{content_search_number}/{content_brand_link}/\">
                                {more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $cur_cap</span> ></i>
                            </a>";
                        }

                        $hide[$i]   = "none";
                        $class[$i]  = "$art_id-hide";
                    }

                    $none[$i]   = "dvisibility0";
                    $border[$i] = "border-dashed";
                    $double++;

                } else {
                    $hide[$i]   = "";
                    $none[$i]   = "dvisibility";
                    $border[$i] = "border-line";
                    $arts       = array();
                    $double     = 0;
                    $price_pred = $val["price"];
                }

                $arts[] = $art_id;
                $i++;
                $j++;
            }

            $j = 0;
            $min_price = 9999999;
        }

        return [
            "content"   => $ll,
            "class"     => $class,
            "hide"      => $hide,
            "border"    => $border,
            "none"      => $none
        ];
    }

    /*
     * show search list
     * */
    public function outSearchList($list, $error, $mas, $other_storages, $saleOut = 0, $status_auto = 0, $mfa_id = 0, $model = ""): string
    {
        $ll = $other_storages["content"];
        $view = 1;

        $list .= "<div class=\"row\">";

        $cc = 0;
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {

                if ($cc > 0) {
                    unset($mas[$mas_key][$key]);
                }

                $cc++;
            }

            $cc = 0;
        }

        $i = 0;
        $faq_pos = (count($mas) >= $this->faq_card_count) ? $this->faq_card_count : count($mas);
        $faq_socials_pos = (count($mas) >= $this->faq_socials_card_count) ? $this->faq_socials_card_count : count($mas);

        $list = "<div>$list";

        if (!empty($mas)) {
            foreach ($mas as $mas_key => $mas_val) {
                foreach ($mas_val as $val) {
                    $art_id     = $mas_key;
                    $art_nr_ds  = $val["article_nr_displ"];
                    $brand_id   = $val["brand_id"];
                    $brand_name = $val["brand_name"];
                    $art_name   = $val["article_name"];
                    $stock      = $val["stock"];
                    $price      = $val["price"];
                    $del_days   = $val["delivery_days"];
                    $del_short  = $val["delivery_short_info"];
                    $suppl_id   = $val["suppl_id"];
                    $storage_id = $val["storage_id"];
                    $status     = ($saleOut > 0) ? $val["status"] : 1;

                    if ((int)$status_auto === 0) {

                        if ($view && ($i === $faq_pos)) {
                            $faq_form = $this->getFaqForm();
                            $list .= $faq_form;
                        }

                        if ($view && ($i === $faq_socials_pos)) {
                            $faq_socials_form = $this->getFaqSocialsForm();
                            $list .= $faq_socials_form;
                        }
                    }

                    $list .= $this->printCatalogList($i, $art_id, $art_nr_ds, $brand_id, $brand_name, $art_name, $stock, $price, $ll[$i], $suppl_id, $del_days, $del_short, $storage_id, $status, $view, $status_auto, $mfa_id, $model);
                    $i++;
                }
            }

            $list .= "</div>";
        } else {

            $list = $error;
        }

        (!$view) ?: $list .= "</div>";

        return $list;
    }

    /*
     * CATALOG ROW
     * */
    public function getHeadRowName($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `TEX_" . $postfix . "` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");

        return $db->result($r, 0, "TEX_$postfix");
    }

    public function getHeadRowLink($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");

        return $db->result($r, 0, "TEX_LINK");
    }

    public function getHeadRowStatus($head_id): int
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `STATUS` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");

        return (int)$db->result($r, 0, "STATUS");
    }

    public function getHeadRowImage($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `IMAGES` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");

        return $db->result($r, 0, "IMAGES");
    }

    public function getHeadRowText($head_id): string
    {
        $db = DbSingleton::getTokoDb();
        $cats = [];
        $r = $db->query("SELECT DISTINCT `CAT_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id     = (int)$db->result($r, $i - 1, "CAT_ID");
            $cat_name   = $this->getCatRowData($cat_id)["cat_name"];

            if ($cat_id > 0) {
                $cats[] = $cat_name;
            }
        }

        return implode(", ", $cats);
    }

    public function getCatRowData($cat_id): array
    {
        $db = DbSingleton::getTokoDb();

        if ((int)$cat_id === 0) {
            $cat_name = $this->replaceLang("{popular_goods_cap}");
            $cat_link = "";
        } else {
            $postfix = $this->getLangPostfix($this->getLanguage());
            $r = $db->query("SELECT `TEX_" . $postfix . "`, `TEX_LINK` FROM `T2_TREE_CAT_EXIST` WHERE `CAT_ID` = $cat_id LIMIT 1;");
            $cat_link = $db->result($r, 0, "TEX_LINK");
            $cat_name = $db->result($r, 0, "TEX_$postfix");
        }

        return compact("cat_name", "cat_link");
    }

    public function getHeadCatRow($cat_id): int
    {
        $head_id = 0;
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HCG_EXIST` WHERE `CAT_ID` = $cat_id AND `HEAD_ID` != 1 LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $head_id = (int)$db->result($r, 0, "HEAD_ID");
        }

        return $head_id;
    }

    public function getGroupRowData($group_id): array
    {
        $group_id = $this->getUrlNumber($group_id);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());

        $r = $db->query("SELECT `TEX_" . $postfix . "`, `H1_" . $postfix . "`, `TEX_LINK` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        $name = ($db->result($r, 0, "H1_$postfix") === "")
            ? $db->result($r, 0, "TEX_$postfix")
            : $db->result($r, 0, "H1_$postfix");
        $link = $db->result($r, 0, "TEX_LINK");

        return compact("name", "link");
    }

    public function getGroupRowName($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());

        $r = $db->query("SELECT `TEX_" . $postfix . "`, `H1_" . $postfix . "` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");

        return ($db->result($r, 0, "H1_$postfix") === "")
            ? $db->result($r, 0, "TEX_$postfix")
            : $db->result($r, 0, "H1_$postfix");
    }

    public function getGroupRowText($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());

        $r = $db->query("SELECT `TEX_" . $postfix . "` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");

        return $db->result($r, 0, "TEX_$postfix");
    }

    public function getGroupRowLink($group_id)
    {
        $group_id = $this->getUrlNumber($group_id);
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");

        return $db->result($r, 0, "TEX_LINK");
    }

    public function getGroupRowImage($group_id)
    {
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `IMAGES` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");

        return $db->result($r, 0, "IMAGES");
    }

    public function getGroupRowStatusAuto($group_id): int
    {
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `STATUS_AUTO` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");

        return (int)$db->result($r, 0, "STATUS_AUTO");
    }

    /*
     * Tree GRID Headers
     * */
    public function getCatalogColList(): string
    {
        $db = DbSingleton::getTokoDb();
        $no_photo = $this->noPhoto;

        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_CONSTRUCTOR` WHERE `STATUS` = 1 ORDER BY `POSITION`;");
        $n = $db->num_rows($r);

        $list = "";

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $head_id    = (int)$db->result($r, $i - 1, "HEAD_ID");
                $head_name  = $this->getHeadRowName($head_id);
                $head_img   = $this->getHeadRowImage($head_id);
                $head_text  = $this->getHeadRowText($head_id);
                $head_list  = $this->getCatalogColListCat($head_id);

                $list .= "
                <div class=\"tree-heads__item\">
                    <input type=\"checkbox\" id=\"toggle-head-$head_id\">
                    <label for=\"toggle-head-$head_id\">
                        <div id=\"tree_head-$head_id\" class=\"tree-heads__item-header\">
                            <div class=\"tree-heads__item-text\">
                                <div class=\"tree-heads__item-title\">
                                    $head_name 
                                </div>
                                <div class=\"tree-heads__item-descr\">
                                    $head_text
                                </div>
                            </div>
                            <div class=\"tree-heads__item-image\">
                                <img data-src=\"/uploads/images/group_tree_head/$head_img\" class=\"lazy\" alt=\"$head_name\" src=\"$no_photo\">
                            </div>
                        </div>
                    </label>
                    <div class=\"tree-cat\" style=\"display: none;\">
                        $head_list
                    </div>
                </div>";
            }
        }

        return $list;
    }

    public function getCatalogColListCat($head_id): string
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $head_link = $this->getHeadRowLink($head_id);

        $r = $db->query("SELECT `CAT_ID` FROM `T2_TREE_CONSTRUCTOR_STR` WHERE `HEAD_ID` = $head_id AND `STATUS` = 1 ORDER BY `COL`, `ROW`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id     = (int)$db->result($r, $i - 1, "CAT_ID");
            $catData    = $this->getCatRowData($cat_id);
            $cat_name   = $catData["cat_name"];
            $cat_link   = $catData["cat_link"];
            $group_list = $this->getCatalogColListGroup($head_id, $cat_id);

            $link = "
            <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/\">$cat_name </a>";

            if ($cat_id === 0) {
                $link = "
                <span>
                    <span style=\"color: #f44438; margin-right: 5px;\">&bull;</span>
                    $cat_name 
                </span>";
            }

            $list .= "
            <div class=\"tree-cat__item\">
                <div class=\"tree-cat__item-title\">
                    $link
                </div>
                <div class=\"tree-group\">
                    $group_list    
                </div>
            </div>";
        }

        return $list;
    }

    public function getCatalogColListGroup($head_id, $cat_id): string
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $groups = [];

        $gg = [];
        $r = $db->query("SELECT t2hcg.`GROUP_ID`
        FROM `T2_TREE_HCG_EXIST` t2hcg
            LEFT JOIN `T2_TREE_GROUP_EXIST` t2g ON (t2g.GROUP_ID = t2hcg.GROUP_ID)
        WHERE t2hcg.`HEAD_ID` = $head_id AND t2hcg.`CAT_ID` = $cat_id AND t2g.`STATUS` = 1 AND 1;");

        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $gg[] = $dbc->result($r, $i - 1, "GROUP_ID");
        }

        if ((int)$cat_id === 0) {
            $gg = [];
            $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id AND `POPULAR` = 1 AND 1;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $gg[] = $dbc->result($r, $i - 1, "GROUP_ID");
            }
        }

        $where_gg = "1";
        if (!empty($gg)) {
            $where_gg = "`group_id` IN (" . implode(",", $gg) . ")";
        }

        $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE_GROUP` WHERE $where_gg;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id   = (int)$dbc->result($r, $i - 1, "group_id");
            $group_name = $this->getGroupRowText($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $group_img  = $this->getGroupRowImage($group_id);
            $status_typ = $this->getGroupRowStatusAuto($group_id);
            $groups[]   = compact("group_name", "group_link", "group_img", "status_typ");
        }

        return $this->getCatalogColListGroupList($groups);
    }
    
    public function getCatalogColListGroupList($groups): string
    {
        $list = "";
        foreach ($groups as $value) {
            $group_name = $value["group_name"];
            $group_link = $value["group_link"];
            $group_img  = $value["group_img"];

            $list .= "
            <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/\" class=\"tree-group__item\">
                <div class=\"tree-group__item-image\">
                    <img data-src=\"/images/tree-group/$group_img\" class=\"lazy\" alt=\"$group_name\">
                </div>
                <div class=\"tree-group__item-text\">
                    <span>$group_name</span>
                </div>
            </a>";
        }

        return $list;
    }

    /*
     * HEADER ROW `HEADERS-CATS-GROUPS` LIST
     * */
    public function getHeaderContent($head_id, $cat_id_selected, $group_id_selected)
    {
        $db = DbSingleton::getTokoDb();
        $form = $this->getHtmlForm("catalog_menu/list");
        $list = "";
        $arr = [];
        $r = $db->query("SELECT `CAT_ID`, `COL`, `ROW` FROM `T2_TREE_CONSTRUCTOR_STR` WHERE `HEAD_ID` = $head_id AND `STATUS` = 1 ORDER BY `COL`, `ROW`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id = (int)$db->result($r, $i - 1, "CAT_ID");
            $col    = (int)$db->result($r, $i - 1, "COL");
            $row    = (int)$db->result($r, $i - 1, "ROW");

            $arr[$col][$row] = $cat_id;
        }

        if ($n > 0) {
            $list = "
            <div class=\"tree-block\">";

            $r2 = $db->query("SELECT MAX(`COL`) as max_col FROM `T2_TREE_CONSTRUCTOR_STR` WHERE `HEAD_ID` = $head_id AND `STATUS` = 1;");
            $max_col = $db->result($r2, 0, "max_col") + 1;

            foreach ($arr as $rows) {
                $list .= "
                <div class=\"tree-block__col\" style=\"width: calc(100% / $max_col);\">";

                foreach ($rows as $cat_id) {
                    $group_list = $this->getTreeConsGroupList($head_id, $cat_id, $group_id_selected);
                    $head_link  = $this->getHeadRowLink($head_id);
                    $catData    = $this->getCatRowData($cat_id);
                    $cat_name   = $catData["cat_name"];
                    $cat_link   = $catData["cat_link"];
                    $href       = $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/";
                    $link       = "<a href=\"$href\">$cat_name</a>";

                    if (empty($cat_id)) {
                        $link = "<span style=\"color: #228b94; display: block; font-size: 16px; font-weight: 700; padding-bottom: 15px;\"><span style=\"margin-right: 5px; color: #f44438;\">&bull;</span>$cat_name</span>";
                    }
                    
                    if ((int)$cat_id === (int)$cat_id_selected) {
                        $link = "<a>$cat_name</a>";
                    }

                    $list .= "
                    <div>
                        <div class=\"tree-item\">
                            <div class=\"tree-item-title\">
                                $link
                            </div>
                            <div class=\"tree-item-list\">$group_list</div>
                        </div>
                    </div>";
                }

                $list .= "</div>";
            }

            $list .= "</div>";
        }

        return str_replace("{content_range}", $list, $form);
    }
    
    public function getTreeConsGroupList($head_id, $cat_id, $group_id_selected): string
    {
        $cat_id = $this->getUrlNumber($cat_id);
        $db = DbSingleton::getTokoDb();
        $list = "";

        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id AND `CAT_ID` = $cat_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $db->result($r, $i - 1, "GROUP_ID");
            $groupData = $this->getGroupRowData($group_id);
            $group_name = $groupData["name"];
            $group_link = $groupData["link"];

            $link = "<a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/\">$group_name</a>";
            
            if ((int)$group_id === (int)$group_id_selected) {
                $link = "<a>$group_name</a>";
            }

            $list .= "
            <div class=\"tree-item-list__element\">
                $link
            </div>";
        }

        if ((int)$cat_id === 0) {
            $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id AND `POPULAR` = 1;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $group_id   = $db->result($r, $i - 1, "GROUP_ID");
                $groupData = $this->getGroupRowData($group_id);
                $group_name = $groupData["name"];
                $group_link = $groupData["link"];
                $link = "<a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/\">$group_name</a>";

                if ((int)$group_id === (int)$group_id_selected) {
                    $link = "<a>$group_name</a>";
                }

                $list .= "
                <div class=\"tree-item-list__element\">
                   $link
                </div>";
            }
        }

        return $list;
    }

    public function getManufactureLink($mfa_id)
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $db = DbSingleton::getTokoDb();
        $mfa_link = "";
        
        $r = $db->query("SELECT `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $mfa_link = $db->result($r, 0, "MFA_BRAND_LINK");
        }

        return $mfa_link;
    }
    
    public function getMfaData($mfa_id): array
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $db = DbSingleton::getTokoDb();
        $mfa_brand = $mfa_link = $mfa_ru = $mfa_ua = "";

        $r = $db->query("SELECT `MFA_BRAND`, `MFA_BRAND_TRANSLIT_RU`, `MFA_BRAND_TRANSLIT_UA`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $mfa_brand  = $db->result($r, 0, "MFA_BRAND");
            $mfa_ru     = $db->result($r, 0, "MFA_BRAND_TRANSLIT_RU");
            $mfa_ua     = $db->result($r, 0, "MFA_BRAND_TRANSLIT_UA");
            $mfa_link   = $db->result($r, 0, "MFA_BRAND_LINK");
        }

        return compact("mfa_brand", "mfa_ru", "mfa_ua", "mfa_link");
    }
    
    public function getModelLink($model)
    {
        $model = $this->getUrlString($model);
        $db = DbSingleton::getTokoDb();
        $model_link = "";
        $r = $db->query("SELECT `Model_Link` FROM `T_models` WHERE `Model` = '$model' LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $model_link = $db->result($r, 0, "Model_Link");
        }

        return $model_link;
    }
    
    public function getModelTransl($model): array
    {
        $model = $this->getUrlString($model);
        $db = DbSingleton::getTokoDb();
        $model_link_ru = $model_link_ua = "";

        $r = $db->query("SELECT `Model_TRANSLIT_RU`, `Model_TRANSLIT_UA` FROM `T_models` WHERE `Model` = '$model' LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $model_link_ru = $db->result($r, 0, "Model_TRANSLIT_RU");
            $model_link_ua = $db->result($r, 0, "Model_TRANSLIT_UA");
        }

        return compact('model_link_ru', 'model_link_ua');
    }
    
    public function getModelIDLink($model_id)
    {
        $model_id = $this->getUrlNumber($model_id);
        $db = DbSingleton::getTokoDb();
        $model_id_link = "";

        $r = $db->query("SELECT `TEX_TEXT_link` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $model_id_link = $db->result($r, 0, "TEX_TEXT_link");
        }

        return $model_id_link;
    }

    public function getParamLink($param_id)
    {
        $param_id = $this->getUrlNumber($param_id);
        $db = DbSingleton::getTokoDb();
        $param_name = "";

        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` = $param_id LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $param_name = $db->result($r, 0, "PARAM_LINK");
        }
        
        return $param_name;
    }
    
    public function getValueLink($value_id)
    {
        $value_id = $this->getUrlNumber($value_id);
        $db = DbSingleton::getTokoDb();
        $value_name = "";

        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id AND `LANG_ID` = 16 LIMIT 1;");
        $n = $db->num_rows($r);
        
        if ($n > 0) {
            $value_name = $db->result($r, 0, "VALUE_LINK");
        }
        
        return $value_name;
    }

    /*
     * export price list for client
     * */
    public function getPriceList($user_id = 0): array
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exRate = new ExRateClass();

        $client_id      = $client->getClientByUser($user_id);
        $t_point_user_id = $client->getSalePointUser($client_id);
        $cur            = $client->getClientCurrency($client_id);
        $cur_cap        = $exRate->getExRateCaption($cur);
        $list           = $storages = [];

        $cap1 = $this->replaceLang("{art_cap}");
        $cap2 = $this->replaceLang("{brand_cap}");
        $cap3 = $this->replaceLang("{caption_cap}");
        $cap4 = $this->replaceLang("{price_cap}");
        $cap5 = $this->replaceLang("{currency}");
        $cap6 = $this->replaceLang("{description_cap}");
        $cap7 = $this->replaceLang("{barcode_cap}");
        $cap1 = iconv("UTF-8", "windows-1251", $cap1);
        $cap2 = iconv("UTF-8", "windows-1251", $cap2);
        $cap3 = iconv("UTF-8", "windows-1251", $cap3);
        $cap4 = iconv("UTF-8", "windows-1251", $cap4);
        $cap5 = iconv("UTF-8", "windows-1251", $cap5);
        $cap6 = iconv("UTF-8", "windows-1251", $cap6);
        $cap7 = iconv("UTF-8", "windows-1251", $cap7);

        $filialList = ["#", "$cap1", "$cap2", "$cap3", "$cap4", "$cap5", "$cap6", "$cap7"];
        $t_pointOtherList = $client->getSalePointOtherList($t_point_user_id);

        foreach ($t_pointOtherList as $t_point) {
            list($storage_local_alien, $storage_remote_alien) = $client->getStorageBySalePoint($t_point);

            $storage_cap    = ($t_point === $t_point_user_id) ? "{your_affiliate} -" : "";
            $city_local     = $client->getSalePointCity($t_point);
            $city_remote    = $client->getStorageCity($storage_remote_alien);
            $address_local  = $client->getSalePointAddress($t_point);
            $address_remote = $client->getStorageAddress($storage_remote_alien);

            if (!empty($storage_local_alien)) {
                $storage_text = $this->replaceLang("$storage_cap $city_local ($address_local) ({local_storage})");
                $filialList[] = iconv("UTF-8", "windows-1251", $storage_text);
                $storages[]     = $storage_local_alien;
            }
            
            if (!empty($storage_remote_alien)) {
                $storage_text = $this->replaceLang("$storage_cap $city_remote ($address_remote) ({remote_storage})");
                $filialList[] = iconv("UTF-8", "windows-1251", $storage_text);
                $storages[]     = $storage_remote_alien;
            }
        }

        $list[0] = $filialList;

        $r = $db->query("SELECT t2as.ART_ID, t2as.STORAGE_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2br.BARCODE 
        FROM `T2_ARTICLES_STRORAGE` t2as
            LEFT OUTER JOIN `T2_ARTICLES` t2a ON (t2a.ART_ID = t2as.ART_ID)
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID)
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID)
            LEFT OUTER JOIN `T2_BARCODES` t2br ON (t2br.ART_ID = t2a.ART_ID)
        WHERE t2as.AMOUNT != 0 AND (IF (t2n.LANG_ID != NULL, t2n.LANG_ID = 16, TRUE))
        GROUP BY t2a.ARTICLE_NR_DISPL;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id     = $db->result($r, $i - 1, "ART_ID");
            $art_nr_ds  = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
            $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
            $art_name   = $db->result($r, $i - 1, "NAME");
            $info       = $db->result($r, $i - 1, "INFO");
            $barcode    = $db->result($r, $i - 1, "BARCODE");

            $info = trim($info, " ");
            $info = trim($info, "\n");
            $info = trim($info, "\r");
            $info = str_replace(array("\n", "\r"), "", $info);

            $art_nr_ds = iconv("UTF-8", "windows-1251", $art_nr_ds);
            $brand_name = iconv("UTF-8", "windows-1251", $brand_name);
            $art_name = iconv("UTF-8", "windows-1251", $art_name);
            $info = iconv("UTF-8", "windows-1251", $info);
            $barcode = iconv("UTF-8", "windows-1251", $barcode);

            $price = $this->getArticlePriceClient($art_id, $client_id, $cur);
            $price = str_replace(".", ",", $price);

            $rs = $db->query("SELECT COUNT(`ART_ID`) as count_arts FROM `T2_ARTICLES_NOT_EXPORT` WHERE `ART_ID` = $art_id LIMIT 1;");
            $ns = (int)$db->result($rs, 0, "count_arts");
            
            if ($ns === 0) {
                $list[$i] = [$i, $art_nr_ds, $brand_name, $art_name, $price, $cur_cap, $info, $barcode];
                foreach ($storages as $storage) {
                    $stock = $this->getStockStorage($art_id, $storage);

                    if ($stock > 10) {
                        $stock = ">10";
                    }

                    $list[$i][] = $stock;
                }
            }
        }

        return $list;
    }

    /*
     * get catalog search link
     * from ARTICLE_NR_SEARCH
     * */
    public function getCatalogueLink($article_nr_search, $article_nr_search2): string
    {
        $search = new SearchClass();
        $article_nr_search2 = $this->getUrlString2($article_nr_search2);
        $article_nr_search = $this->getUrlString($article_nr_search);
        $article_nr_search = mb_strtolower($article_nr_search);

        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT DISTINCT `SEARCH_NUMBER`, `BRAND_ID` FROM `T2_CROSS` WHERE `SEARCH_NUMBER` = '$article_nr_search';");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $brand_id   = $db->result($r, 0, "BRAND_ID");
            $brand_link = $this->getBrandLink($brand_id);
            $link       = $this->getSiteLink() . "$this->search_link/$article_nr_search/$brand_link";
        } else {
            $count_zero = 0;
            $exist_search_number = $exist_brand_link = "";

            for ($i = 1; $i <= $n; $i++) {
                $article_search = $db->result($r, $i - 1, "SEARCH_NUMBER");
                $brand_id       = $db->result($r, $i - 1, "BRAND_ID");
                $count          = $search->countBrandItems($article_search, $brand_id);

                if ($count === 0) {
                    $count_zero++;
                } else {
                    $exist_search_number    = strtolower($article_search);
                    $exist_brand_link       = $this->getBrandLink($brand_id);
                }
            }

            if ($count_zero === ($n - 1)) {
                $link = $this->getSiteLink() . "$this->search_link/$exist_search_number/$exist_brand_link/";
            } else {
                $link = $this->getSiteLink() . "$this->search_link/?text=$article_nr_search2/";
            }

        }

        return $link;
    }

}

function cmpPrice($a, $b): int
{
    if ((float)$a["price"] === (float)$b["price"]) {
        return 0;
    }

    return (float)$a["price"] > (float)$b["price"] ? 1 : -1;
}