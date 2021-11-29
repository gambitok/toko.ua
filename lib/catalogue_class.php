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

    /*
     * get catalog search form
     * */
    public function getSearchList($article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $article_search = "";
        $brand_id = 0;
        $article_nr_search = $this->getUrlString($article_nr_search);
        $r = $db->query("SELECT `SEARCH_NUMBER`, `BRAND_ID` FROM `T2_CROSS` WHERE `SEARCH_NUMBER` = '$article_nr_search' GROUP BY `BRAND_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $article_search = $db->result($r, $i - 1, "SEARCH_NUMBER");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
        }
        if ($n == 1) {
            return $this->getCatalogList($article_search, $brand_id);
        } else {
            return $this->getSearchResult($article_search, $article_nr_search);
        }
    }

    public function getSearchResult($article_search, $article_nr_search)
    {
        if ($article_search != "") {
            $form = $this->getBrandList($article_search, $article_nr_search);
        } else {
            $list = $this->showSearchDropdown($article_nr_search);
            if ($list == "") {
                $form = $this->getHtmlForm("error/search_unknown");
            } else {
                $form = $this->getHtmlForm("search/search_catalog");
                $form = str_replace("{search_query}", $article_nr_search, $form);
                $form = str_replace("{search_range}", $list, $form);
            }
        }
        return $form;
    }

    /*
     * get catalog search List
     * */
    public function getCatalogList($article_nr_search, $brand_nr_search)
    {
        $article_nr_search = $this->getUrlString($article_nr_search);
        $brand_nr_search = $this->getUrlNumber($brand_nr_search);
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $client->insertHistory($article_nr_search, $brand_nr_search);
        $client->toggleProductView(0);
        $cur = $this->getCurrentExrate();

        $art_ids = [];
        $r = $db->query("SELECT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_nr_search AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END)
        GROUP BY t2c.`ART_ID` 
        ORDER BY t2n.NAME ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $art_id_str = implode(",", $art_ids);

        $form = $this->getHtmlForm("search/form");
        $search_main = $this->getHtmlForm("search/main");
        $search_filters = $this->getHtmlForm("search/filters");
        $search_brands = $this->getHtmlForm("search/brands");

        list($list, $list_brand, $filters) = $this->searchList($art_id_str, 0, $article_nr_search, $brand_nr_search);

        // if found something
        if (($list_brand) && ($filters)) {
            $colon = "col-lg-9 col-12 pad0";
            $colon_filter = "col-lg-3 col-12";
        } else {
            $colon = "col-lg-12 col-12 pad0";
            $colon_filter = "none";
            $search_main = str_replace("{currency}", "", $search_main);
            $search_main = str_replace("{products_view}", "", $search_main);
            $form = str_replace("{cat_search_filters}", "", $form);
            $form = str_replace("{cat_search_brands}", "", $form);
        }

        //colon
        $form = str_replace("{search_col}", $colon, $form);
        $form = str_replace("{filters_col}", $colon_filter, $form);
        $form = str_replace("{type_search}", 1, $form);
        $form = str_replace("{art_value}", $article_nr_search, $form);
        $form = str_replace("{brand_value}", $brand_nr_search, $form);
        $form = str_replace("{cur_value}", $cur, $form);

        //search main
        $search_main = $this->getSearchMain($search_main, $article_nr_search, $brand_nr_search, $list, $cur);
        $form = str_replace("{cat_search_main}", $search_main, $form);

        //search filters
        if (!empty($filters)) {
            $search_filters = $this->getSearchFilters($search_filters, $filters, $cur, []);
            $form = str_replace("{cat_search_filters}", $search_filters, $form);
        }

        //search brands
        if (!empty($list_brand)) {
            $search_brands = str_replace("{brands_list}", $list_brand, $search_brands);
            $search_brands = str_replace("{brands_display}", ($list_brand == "") ? "none" : "", $search_brands);
            $form = str_replace("{cat_search_brands}", $search_brands, $form);
        }

        $form = $this->replaceLang($form);

        return $form;
    }

    /*
    * get catalog search List filtred
    * */
    public function getCatalogListFilter($article_nr_search, $brand_nr_search, $brand_filter, $cur, $price_f, $deliv_f, $order_value)
    {
        $article_nr_search = $this->getNameString($article_nr_search);
        $brand_nr_search = $this->getNameString($brand_nr_search);
        $cur = $this->getUrlNumber($cur);
        $order_value = $this->getUrlNumber($order_value);
        $brand_nr_search = $this->getUrlNumber($brand_nr_search);
        $db = DbSingleton::getTokoDb();
        $art_ids = [];
        $r = $db->query("SELECT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_nr_search AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END)
        ORDER BY t2n.NAME ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $art_id_str = implode(",", $art_ids);

        $brand_filter = json_decode($brand_filter);
        if (count($brand_filter) > 1) {
            $brand_filter = implode(",", $brand_filter);
        } else {
            $brand_filter = "";
        }
        $exp_price = explode(",", $price_f);
        $exp_deliv = explode(",", $deliv_f);

        list($list, $filters, $list_brand, $current_value) = $this->searchListFilter($art_id_str, $article_nr_search, $brand_filter, $cur, $exp_price[0], $exp_price[1], $exp_deliv[0], $exp_deliv[1], $brand_nr_search, $order_value);

        $search_main = $this->getHtmlForm("search/main");
        $search_filters = $this->getHtmlForm("search/filters");
        $search_brands = $this->getHtmlForm("search/brands");
        $search_main = $this->getSearchMain($search_main, $article_nr_search, $brand_nr_search, $list, $cur);
        $search_main = $this->replaceLang($search_main);
        $search_filters = $this->getSearchFilters($search_filters, $filters, $cur, $current_value);
        $search_filters = $this->replaceLang($search_filters);
        $search_brands = str_replace("{brands_list}", $list_brand, $search_brands);
        $search_brands = str_replace("{brands_display}", ($list_brand == "") ? "none" : "", $search_brands);
        $search_brands = $this->replaceLang($search_brands);

        return array($search_main, $search_filters, $search_brands, $filters["max_price"]);
    }

    /*
     * Show `CHOOSE BRAND` Form
     * */
    public function getBrandList($article_search, $article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $showform = new FormClass();

        $count_zero = $exist_search_number = 0;
        $exist_brand_link = $result = $list = "";
        $mas = [];

        $form = $this->getHtmlForm("search/brand_options_form");
        $form_brand = $this->getHtmlForm("search/brand_options_list");
        $search_form = $this->getHtmlForm("search/brand_options");

        $r = $db->query("SELECT t2c.ART_ID, t2c.BRAND_ID, t2c.SEARCH_NUMBER, t2c.DISPLAY_NR, t2c.KIND, t2c.RELATION, t2b.BRAND_NAME, t2b.BRAND_LINK, IFNULL(t2n.NAME,'') as NAME 
        FROM `T2_CROSS` t2c 
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID) 	
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_search' AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END) 
        GROUP BY t2c.BRAND_ID;");
        $n = $db->num_rows($r);
        if ($article_search != "") {
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "ART_ID");
                $search_number = $db->result($r, $i - 1, "SEARCH_NUMBER");
                $article_nr_displ = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                $brand_link = $db->result($r, $i - 1, "BRAND_LINK");
                $article_name = $db->result($r, $i - 1, "NAME");
                $count = $this->countBrandItems($search_number, $brand_id);
                if ($count == 0) {
                    $count_zero++;
                } else {
                    $exist_search_number = strtolower($search_number);
                    $exist_brand_link = $brand_link;
                }
                $mas[$i] = compact("search_number", "article_nr_displ", "brand_id", "brand_name", "brand_link", "count", "article_name", "art_id");
            }
            $nophoto = $this->noPhoto;
            usort($mas, "myBrandCmp");
            for ($i = 0; $i < $n; $i++) {
                $search_number = strtolower($mas[$i]["search_number"]);
                $article_nr_displ = $mas[$i]["article_nr_displ"];
                $brand_name = $mas[$i]["brand_name"];
                $brand_link = $mas[$i]["brand_link"];
                $count = $mas[$i]["count"];
                $article_name = $mas[$i]["article_name"];
                $photo_name = $showform->getArticleActivePhoto($mas[$i]["art_id"]);
                $link = ($count == 0) ? "showAlertModal(\"{brand_no_offer} `$article_nr_displ/$brand_name`\",\"{sorry_cap}\");" : "location.href=\"" . $this->getSiteLink() . "$this->search_link/$search_number/$brand_link/\";";
                $list .= "
                <tr onclick='$link'>
                    <td class=\"minify\">
                        <img itemprop=\"image\" data-src=\"$photo_name\" class=\"lazy\" alt=\"$article_nr_displ\" src=\"$nophoto\">
                    </td>
                    <td>$article_nr_displ</td>
                    <td>$brand_name</td>
                    <td>$article_name</td>
                    <td>$count</td>
                </tr>";
            }
            $form_brand = str_replace("{brand_list}", $list, $form_brand);
        } else {
            $search_form = str_replace("{search_results}", "{offers_request}", $search_form);
            $search_form = str_replace("{search_result_index}", "<br><span class=\"span-search text-uppercase\">{search_result_for} <b class=\"span-dark-red\">$article_nr_search</b> {nothing_found}</span>
            <br><br><p class=\"span-search\">{check_the_data}</p>", $search_form);
            $search_form = str_replace("{search_result}", "", $search_form);
        }

        $search_form = str_replace("{search_results}", "{choose_brand_manuf}", $search_form);
        $search_form = str_replace("{search_result_index}", "<span class=\"span-brand-search\">{search_request} <b>$article_search</b> {search_result_for_end}</span>", $search_form);
        $search_form = str_replace("{art}", $result, $search_form);
        $search_form = str_replace("{currency}", "", $search_form);
        $search_form = str_replace("{products_view}", "", $search_form);
        $search_form = str_replace("{search_result}", $form_brand, $search_form);
        $form = str_replace("{search_filters}", "", $form);
        $form = str_replace("{search_form}", $search_form, $form);

        if ($count_zero == ($n - 1)) {
            header("Location: " . $this->getSiteLink() . "$this->search_link/$exist_search_number/$exist_brand_link/");
        }
        return $form;
    }

    public function getSearchMain($search_main, $article_nr_search, $brand_nr_search, $list, $cur)
    {
        $client = new ClientClass();
        $showform = new FormClass();
        $article_nr_displ = $this->getArtDispl($article_nr_search, $brand_nr_search);
        $title = $this->getHtmlForm("catalog_exist/title");
        $title = str_replace("{article_nr_displ}", $article_nr_displ, $title);
        $title = str_replace("{brand_name}", $this->getBrandName($brand_nr_search), $title);
        $view = $client->getProductView();
        $radio_view = $this->getHtmlForm("search/view_radio");
        $radio_view = str_replace("{checked_table}", ($view == 0) ? "checked" : "", $radio_view);
        $radio_view = str_replace("{checked_cards}", ($view == 1) ? "checked" : "", $radio_view);
        $search_main = str_replace("{art}", $this->replaceLang($title), $search_main);
        $search_main = str_replace("{currency}", $showform->getCurrencyForm($cur), $search_main);
        $search_main = str_replace("{products_view}", $radio_view, $search_main);
        $search_main = str_replace("{search_result}", $list, $search_main);
        return $search_main;
    }

    /*
     * get catalog search filter variables
     * */
    public function getSearchFilters($search_filters, $filters, $cur, $current_value)
    {
        if (!empty($filters)) {
            if (empty($current_value)) {
                $current_value = array();
                $current_value["min_price"] = 0;
                $current_value["max_price"] = $filters["max_price"];
                $current_value["min_dd"] = 0;
                $current_value["max_dd"] = $filters["max_dd"];
            }
        }
        $search_filters = str_replace("{sideblock_max_price}", $filters["max_price"], $search_filters);
        $search_filters = str_replace("{sideblock_max_dd}", $filters["max_dd"], $search_filters);
        $search_filters = str_replace("{sideblock_max_price_val}", $current_value["max_price"], $search_filters);
        $search_filters = str_replace("{sideblock_max_dd_val}", $current_value["max_dd"], $search_filters);
        $search_filters = str_replace("{sideblock_min_price_val}", $current_value["min_price"], $search_filters);
        $search_filters = str_replace("{sideblock_min_dd_val}", $current_value["min_dd"], $search_filters);
        $search_filters = str_replace("{cur_value}", $cur, $search_filters);
        $search_filters = str_replace("{catalogue_js_filter}", "catalogueFilter();", $search_filters);
        $search_filters = str_replace("{filters_col}", "col-lg-2 col-12 pad0", $search_filters);
        return $search_filters;
    }

    /*
     * get Search Articles Count
     * */
    public function countBrandItems($article_nr_search, $brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $art_ids = [];
        $brand_id = $this->getUrlNumber($brand_id);
        $r = $db->query("SELECT t2c.ART_ID 
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID)
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_id AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $art_id_str = implode(",", $art_ids);
        return $this->searchList($art_id_str, 0, $article_nr_search, $brand_id)[3];
    }

    /*
     * show search result header
     * */
    public function drawHeaderSearchList($view = 0, $order = "")
    {
        $sort1 = $sort2 = $sort3 = $sort4 = "fa-sort";
        switch ($order) {
            case "delivery_days":
                $sort2 = "fa-sort-alpha-down";
                break;
            case "stock":
                $sort3 = "fa-sort-alpha-down";
                break;
            case "price":
                $sort4 = "fa-sort-alpha-down";
                break;
            case "article_nr_displ" :
            default:
                $sort1 = "fa-sort-alpha-down";
                break;
        }
        $jsFilter = "catalogueFilter";
        $form = $this->getHtmlForm("search/header");
        $form = str_replace("{cat_js_filter}", $jsFilter, $form);
        $form = str_replace("{cat_sort_1}", $sort1, $form);
        $form = str_replace("{cat_sort_2}", $sort2, $form);
        $form = str_replace("{cat_sort_3}", $sort3, $form);
        $form = str_replace("{cat_sort_4}", $sort4, $form);
        $form = str_replace("{cat_storage_info}", "{storage_full_info}", $form);
        $form = str_replace("{cat_product_view}", (!$view) ? "" : "none", $form);
        return $form;
    }

    /*
     * create temporary table
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
     * get Main Search Order
     * */
    public function getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, $where_brands)
    {
        $db = DbSingleton::getTokoDb();
        if ($article_nr_search != "") {
            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` = '$article_nr_search' AND `BRAND_ID` = $brand_nr_search LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $art_id = $db->result($r, 0, "ART_ID");
                $where_oe_art_id = $this->getOriginalEquipment($art_id);
                $where_art_id_str .= ",$where_oe_art_id";
            }
        }
        if ($where_art_id_str == "") {
            $where_art_id_str = 0;
        }
        $where_art_id_str = rtrim($where_art_id_str, ",");
        $where_art_id_str = str_replace("'", "", $where_art_id_str);
        $r = "";

        if ($where_art_id_str != "") {
            $r = $db->query("
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2asc.AMOUNT as AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID)
                LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID)
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.ART_ID = t2a.ART_ID)
            WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE` = '1' AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END) AND (t2asc.AMOUNT != NULL OR t2asc.AMOUNT != 0) $where_brands 
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
            UNION ALL
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2si.stock_suppl as AMOUNT, t2si.client_storage_id as storage_id, t2si.suppl_id, t2si.return_delay
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID)
                LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID)
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
            WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE` = '1' AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END) AND (t2si.stock_suppl != NULL OR t2si.stock_suppl != 0) $where_brands 
            GROUP BY t2a.ART_ID, t2si.client_storage_id;");
        }

        return $r;
    }

    /*
     * get Original Numbers
     * */
    public function getOriginalEquipment($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $arts = [];
        $art_id_arr = [];
        $r = $db->query("SELECT `SEARCH_NUMBER`, `BRAND_ID` 
        FROM `T2_CROSS` 
        WHERE `ART_ID` = $art_id AND ((`KIND` = 3 AND `RELATION` = 0) OR (`KIND` IN (3, 4) AND `RELATION` = 1) OR (`KIND` IN (3, 4) AND `RELATION` = 2)) 
        GROUP BY `SEARCH_NUMBER` LIMIT 0,10;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $article_search = $db->result($r, $i - 1, "SEARCH_NUMBER");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            $arts[$i] = [
                "search_number" => $article_search,
                "brand_id"      => $brand_id
            ];
        }
        foreach ($arts as $art) {
            $article_search = $art["search_number"];
            $brand_id       = $art["brand_id"];
            $r = $db->query("SELECT `ART_ID` 
            FROM `T2_CROSS` 
            WHERE `SEARCH_NUMBER` = '$article_search' AND `BRAND_ID` = $brand_id AND ((`KIND` = 3 AND `RELATION` = 0) OR (`KIND` = 0 AND `RELATION` = 0));");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $cross_art_id = $db->result($r, $i - 1, "ART_ID");
                array_push($art_id_arr, $cross_art_id);
            }
        }
        return implode(",", $art_id_arr);
    }

    /*
     * get brand list form
     * */
    public function getListBrand($brands, $main_brand, $cur, $jsFilterModel, $brand_filter = [])
    {
        $list_brand = $checked = $main_brand_class = "";
        $unique_brands = $brand_array = array();

        usort($brands, "cmpPrice");
        // main brand first
        $brand_main_key = 0;
        if ($main_brand != 0) {
            foreach ($brands as $key => $value) {
                $brand_id = $value["brand_id"];
                if ($main_brand == $brand_id) {
                    $brand_main_key = $key;
                }
            }
            $brands = array($brand_main_key => $brands[$brand_main_key]) + $brands;
        }

        //get unique brands with min price
        foreach ($brands as $key => $value) {
            //delete 0;
            if (!empty($unique_brands)) {
                if ($unique_brands[$value["brand_id"]]["brand_count"] > 0) {
                    unset($brands[$key]);
                }
            }
            if (in_array($value["brand_id"], $value)) {
                $unique_brands[$value["brand_id"]]["brand_count"] = $unique_brands[$value["brand_id"]]["brand_count"] + 1;
            }
        }

        foreach ($brands as $key => $value) {
            $min_price = $value["price"];
            $brand_id = $value["brand_id"];
            $val_brand = $value["brand_name"];

            if ($brand_filter != "") {
                $brand_array = explode(",", $brand_filter);
                $checked = (in_array($brand_id, $brand_array)) ? "checked=\"checked\"" : "";
            } else {
                $checked = (in_array($main_brand, $brand_array)) ? "checked=\"checked\"" : "";
            }

            if ($brand_id != "") {
                if ($brand_id == $main_brand) {
                    $checked = "checked=\"checked\" disabled=\"true\"";
                    $main_brand_class = "main-brand";
                } else {
                    $main_brand_class = "";
                }
            }

            $list_brand .= $this->getHtmlForm("search/brand_item");
            $list_brand = str_replace("{val_brand}", $val_brand, $list_brand);
            $list_brand = str_replace("{brand_id}", $brand_id, $list_brand);
            $list_brand = str_replace("{main_brand_class}", $main_brand_class, $list_brand);
            $list_brand = str_replace("{checked}", $checked, $list_brand);
            $list_brand = str_replace("{min_price}", $min_price, $list_brand);
            $list_brand = str_replace("{currency_cap}", $this->getSymbolExrate($cur), $list_brand);
            $list_brand = str_replace("{jsFilterModel}", $jsFilterModel, $list_brand);
        }

        return $this->replaceLang($list_brand);
    }

    public function searchListCatalog($where_art_id_str, $view = 0, $mfa_id = 0, $model = "", $status_auto = 0)
    {
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $client_id = $this->getClient();
        $tpoint_id = $this->getTpointID();
        $cur = $this->getCurrentExrate();

        session_start();
        $temp_key = session_id();
        $mas = [];

        list($error, , $list) = $this->getSearchMessages();

        if ($where_art_id_str != "") {
            $this->createTemporarySearchTable($temp_key);
            $r = $this->getTemporarySearchTable($where_art_id_str, "", "", "");
            $n = $db->num_rows($r);
            $list = $this->drawHeaderSearchList($view);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                    $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name = $db->result($r, $i - 1, "NAME");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $stock = intval($db->result($r, $i - 1, "AMOUNT"));
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $return_days = $db->result($r, $i - 1, "return_delay");

                    // price
                    $price = $this->getArticlePrice($art_id);
                    if ($suppl_id != 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }
                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur == 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }

                    // delivery
                    $deliveryData           = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
                    $delivery_info          = $deliveryData["info"];
                    $delivery_days          = $deliveryData["days"];
                    $delivery_short_info    = $deliveryData["short"];
                    if ($suppl_id != 0) {
                        $deliveryData           = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = $deliveryData["days"];
                        $delivery_short_info    = $deliveryData["short"];
                    }

                    $status = ($suppl_id == 0) ? 1 : 0;

                    if ($price != 0) {
                        if ($stock > 0) {
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', '$article_name', '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                            }
                        }
                    }
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `article_nr_displ` ASC;");
                $n = $db->num_rows($r);

                if ($n == 1) {
                    $stock = $db->result($r, 0, "stock");
                    $price = $db->result($r, 0, "price");
                    if ($stock == 0 && $price == 0) {
                        $list = $this->getHtmlForm("error/nothing_found");
                        $list = str_replace("{error_nothing_found}", $this->err1, $list);
                        return array($list, "", "", 0);
                    }
                }

                $temp_arr = [];
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "art_id");
                    $article_nr_displ = $db->result($r, $i - 1, "article_nr_displ");
                    $brand_id = $db->result($r, $i - 1, "brand_id");
                    $brand_name = $db->result($r, $i - 1, "brand_name");
                    $article_name = $db->result($r, $i - 1, "article_name");
                    $delivery_days = $db->result($r, $i - 1, "delivery_days");
                    $delivery_info = $db->result($r, $i - 1, "delivery_info");
                    $delivery_short_info = $db->result($r, $i - 1, "delivery_short_info");
                    $stock = $db->result($r, $i - 1, "stock");
                    $price = $db->result($r, $i - 1, "price");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $return_days = $db->result($r, $i - 1, "return_days");
                    $status = $db->result($r, $i - 1, "status");
                    $temp_arr[] = compact("art_id", "article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                }

                usort($temp_arr, "cmpPrice");
                foreach ($temp_arr as $value) {
                    $art_id                 = $value["art_id"];
                    $article_nr_displ       = $value["article_nr_displ"];
                    $brand_id               = $value["brand_id"];
                    $brand_name             = $value["brand_name"];
                    $article_name           = $value["article_name"];
                    $delivery_days          = $value["delivery_days"];
                    $delivery_info          = $value["delivery_info"];
                    $delivery_short_info    = $value["delivery_short_info"];
                    $stock                  = $value["stock"];
                    $price                  = $value["price"];
                    $suppl_id               = $value["suppl_id"];
                    $storage_id             = $value["storage_id"];
                    $return_days            = $value["return_days"];
                    $status                 = $value["status"];
                    if (!empty($mas[$art_id][0])) {
                        if ($mas[$art_id][0]["price"] > $price) {
                            $mas[$art_id][0] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                        }
                    } else {
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
                $list = $this->outSearchList($list, $error, $mas, "", "", "", $view, 0, $status_auto, $mfa_id, $model);
            }

            $count = count($mas);
            if ($count < 1) {
                $list = $error;
            }

        }
        return $list;
    }

    public function searchList($where_art_id_str, $view = 0, $article_nr_search = "", $brand_nr_search = "", $mfa_id = 0, $model = "", $status_auto = 0)
    {
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $client_id = $this->getClient();
        $tpoint_id = $this->getTpointID();
        $cur = $this->getCurrentExrate();
        if (!$view) {
            $view = $client->getProductView();
        }
        session_start();
        $temp_key = session_id();
        $mas = $filters = $brands = $brand_ids = [];
        $list_brand = "";
        $art_id_search = 0;
        $filters["max_price"] = $filters["max_dd"] = $count = $main_brand = 0;
        $filters["min_price"] = 99999999;

        if ($article_nr_search != "") {
            $art_id_search = $this->getArticleId($article_nr_search, $brand_nr_search);
        }

        list($error, $jsFilterModel, $list) = $this->getSearchMessages();

        if ($where_art_id_str != "") {
            $this->createTemporarySearchTable($temp_key);
            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "");
            $n = $db->num_rows($r);
            $list = $this->drawHeaderSearchList($view);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                    $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name = $db->result($r, $i - 1, "NAME");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $stock = intval($db->result($r, $i - 1, "AMOUNT"));
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $return_days = $db->result($r, $i - 1, "return_delay");
                    $format_name = $this->getFormatAticle($article_nr_displ);

                    // price
                    $price = $this->getArticlePrice($art_id);
                    if ($suppl_id != 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }
                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur == 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }
                    $filter_price = $price;

                    // delivery
                    $deliveryData           = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
                    $delivery_info          = $deliveryData["info"];
                    $delivery_days          = $deliveryData["days"];
                    $delivery_short_info    = $deliveryData["short"];
                    if ($suppl_id != 0) {
                        $deliveryData           = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = $deliveryData["days"];
                        $delivery_short_info    = $deliveryData["short"];
                    }

                    // filters
                    if ($filter_price > $filters["max_price"]) {
                        $filters["max_price"] = ceil($filter_price);
                    }
                    if ($filter_price < $filters["min_price"]) {
                        $filters["min_price"] = ceil($filter_price);
                    }
                    if ($delivery_days > $filters["max_dd"]) {
                        $filters["max_dd"] = $delivery_days;
                    }

                    // ORDER BY search art and suppl_id
                    if (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id == 0) ? 1 : 0;
                    }

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if ($price != 0 || (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                        if ($stock > 0 || (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                            // visible suppl storage
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', '$article_name', '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                                if ($art_id == $art_id_search) {
                                    $main_brand = $brand_id;
                                }
                                if ($brand_name != "") {
                                    if ($stock > 0 && $price > 0) {
                                        array_push($brand_ids, $brand_id);
                                        $brands[$art_id]["brand_name"]  = $brand_name;
                                        $brands[$art_id]["brand_id"]    = $brand_id;
                                        if (!empty($brands[$art_id]["price"])) {
                                            if ($price < $brands[$art_id]["price"]) {
                                                $brands[$art_id]["price"] = $price;
                                            }
                                        } else {
                                            $brands[$art_id]["price"] = $price;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `article_nr_displ` ASC;");
                $n = $db->num_rows($r);

                if ($n == 1) {
                    $stock = $db->result($r, 0, "stock");
                    $price = $db->result($r, 0, "price");
                    if ($stock == 0 && $price == 0) {
                        $list = $this->getHtmlForm("error/nothing_found");
                        $list = str_replace("{error_nothing_found}", $this->err1, $list);
                        return array($list, "", "", 0);
                    }
                }

                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "art_id");
                    $article_nr_displ = $db->result($r, $i - 1, "article_nr_displ");
                    $brand_id = $db->result($r, $i - 1, "brand_id");
                    $brand_name = $db->result($r, $i - 1, "brand_name");
                    $article_name = $db->result($r, $i - 1, "article_name");
                    $delivery_days = $db->result($r, $i - 1, "delivery_days");
                    $delivery_info = $db->result($r, $i - 1, "delivery_info");
                    $delivery_short_info = $db->result($r, $i - 1, "delivery_short_info");
                    $stock = $db->result($r, $i - 1, "stock");
                    $price = $db->result($r, $i - 1, "price");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $return_days = $db->result($r, $i - 1, "return_days");
                    $status = $db->result($r, $i - 1, "status");
                    $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                }

                // delete temp table
                $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

                // get filter brand list
                $list_brand = $this->getListBrand($brands, $main_brand, $cur, $jsFilterModel);

                // delete empty stocks and prices
                $mas = $this->deleteEmptyPosition($mas);
                $mas = $this->deleteSupplPosition($mas);
                $mas = $this->deleteRepeatPosition($mas);

                if (empty($mas)) {
                    // $list = $this->getHtmlForm("error/search_unknown");
                    $list = $this->getHtmlForm("error/nothing_found");
                    $list = str_replace("{error_nothing_found}", $this->err1, $list);
                    return array($list, "", "", 0);
                }

                // sort by delivery and price
                foreach ($mas as $mas_key => $mas_val) {
                    $mas[$mas_key] = $this->multiSort($mas[$mas_key], "delivery_days", "price");
                }

                // sort like: first = min delivery, second = min price, else = default
                $mas = $this->sortByMinStock($mas);

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, $view);

                // show search list
                $list = $this->outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view, 0, $status_auto, $mfa_id, $model);
            }

            $count = count($mas);
            if ($count < 1) {
                $list = $error;
                $list_brand = "";
                $filters = [];
                $filters["max_price"] = 0;
                $filters["max_dd"] = 0;
            }

        }
        return array($list, $list_brand, $filters, $count, $brand_ids);
    }

    public function searchListFilter($where_art_id_str, $article_nr_search, $brand_filter, $cur, $price_min, $price_max, $del_min, $del_max, $brand_nr_search, $order_value)
    {
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();
        session_start();
        setcookie("currency", $cur);
        $_SESSION["currency"] = $cur;
        $view = $client->getProductView();
        $client_id = $this->getClient();
        $tpoint_id = $this->getTpointID();
        $mas = $filters = $brands = $current_value = array();
        $filters["max_price"] = $filters["max_dd"] = $main_brand = $count = 0;
        $list_brand = "";
        $error = $this->replaceLang("<h5 class=\"error_message\">$this->err1</h5>");
        $list = "$error";
        $art_id_search = 0;
        if ($article_nr_search != "") {
            $art_id_search = $this->getArticleId($article_nr_search, $brand_nr_search);
        }
        $where_brands = $this->getFiltersSearch($brand_filter);

        if ($where_art_id_str != "") {
            $articlePrices = $this->getArticlePrices($where_art_id_str);
            $deliverInfo = $this->getTpointDeliveryInfos($tpoint_id, $where_art_id_str);
            $articleSupplPrices = $this->getArticleSupplPrices($where_art_id_str);
            $supplDeliverInfo = $this->getTpointSupplDeliveriesInfo($tpoint_id);
            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, $where_brands);
            $n = $db->num_rows($r);

            $list = $this->drawHeaderSearchList($view, $order_value);

            if ($where_brands == "") {
                $rs = $r;
                $ns = $n;
            } else {
                $rs = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "");
                $ns = $db->num_rows($rs);
            }

            if ($ns > 0) {
                // filters with default search
                for ($i = 1; $i <= $ns; $i++) {
                    $art_id = $db->result($rs, $i - 1, "ART_ID");
                    $brand_id = $db->result($rs, $i - 1, "BRAND_ID");
                    $brand_name = $db->result($rs, $i - 1, "BRAND_NAME");
                    $article_nr_displ = $db->result($rs, $i - 1, "ARTICLE_NR_DISPL");
                    $stock = intval($db->result($rs, $i - 1, "AMOUNT"));
                    $suppl_id = $db->result($rs, $i - 1, "suppl_id");
                    $storage_id = $db->result($rs, $i - 1, "storage_id");
                    $format_name = $this->getFormatAticle($article_nr_displ);

                    // price
                    $price = $articlePrices[$art_id] ?? 0;
                    // delivery
                    $delivery_days = $deliverInfo[$storage_id]["delivery_days"] ?? 0;
                    if ($suppl_id != 0) {
                        $price = $articleSupplPrices[$art_id][$suppl_id][$storage_id];
                        $deliveryData = $supplDeliverInfo[$suppl_id][$storage_id] ?? [
                                "info"                  => $this->err2,
                                "delivery_days"         => 0,
                                "delivery_short_info"   => $this->err2
                            ];
                        $delivery_days = $deliveryData["delivery_days"];
                    }

                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur == 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }
                    $filter_price = $price;

                    // filters
                    if ($filter_price > $filters["max_price"]) {
                        $filters["max_price"] = ceil($filter_price);
                    }
                    if ($delivery_days > $filters["max_dd"]) {
                        $filters["max_dd"] = $delivery_days;
                    }

                    if ($price != 0 || (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                        if ($stock > 0 || (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                            if ($art_id == $art_id_search) {
                                $main_brand = $brand_id;
                            }
                            if ($brand_name != "") {
                                if ($stock > 0 && $price > 0) {
                                    $brands[$art_id]["brand_name"] = $brand_name;
                                    $brands[$art_id]["brand_id"] = $brand_id;
                                    if (!empty($brands[$art_id]["price"])) {
                                        if ($price < $brands[$art_id]["price"]) {
                                            $brands[$art_id]["price"] = $price;
                                        }
                                    } else {
                                        $brands[$art_id]["price"] = $price;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // get filter brand list
            $jsFilterModel = $this->getSearchMessages()[1];
            $list_brand = $this->getListBrand($brands, $main_brand, $cur, $jsFilterModel, $brand_filter);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name = $db->result($r, $i - 1, "NAME");
                    $return_days = $db->result($r, $i - 1, "return_delay");
                    $stock = intval($db->result($r, $i - 1, "AMOUNT"));
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $format_name = $this->getFormatAticle($article_nr_displ);

                    // price
                    $price = $articlePrices[$art_id] ?? 0;
                    if ($suppl_id != 0) {
                        $price = $articleSupplPrices[$art_id][$suppl_id][$storage_id];
                    }
                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur == 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }
                    $filter_price = $price;

                    // delivery
                    $delivery_info          = $deliverInfo[$storage_id]["info"];
                    $delivery_days          = $deliverInfo[$storage_id]["delivery_days"];
                    $delivery_short_info    = $deliverInfo[$storage_id]["delivery_short_info"];
                    if ($suppl_id != 0) {
                        $deliveryData = $supplDeliverInfo[$suppl_id][$storage_id] ?? [
                                "info"                  => $this->err2,
                                "delivery_days"         => 0,
                                "delivery_short_info"   => $this->err2
                            ];
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = $deliveryData["delivery_days"];
                        $delivery_short_info    = $deliveryData["delivery_short_info"];
                    }

                    // filters
                    if ($filter_price > $filters["max_price"]) {
                        $filters["max_price"] = ceil($filter_price);
                    }
                    $current_value["min_price"] = $price_min;
                    $current_value["max_price"] = $price_max;
                    $current_value["min_dd"]    = $del_min;
                    $current_value["max_dd"]    = $del_max;

                    if (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id == 0) ? 1 : 0;
                    }

                    if (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                    }
                    elseif ($stock > 0) {
                        if ($price >= $price_min && $price <= $price_max && $delivery_days >= $del_min && $delivery_days <= $del_max) {
                            $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                        }
                    }
                }

                // delete empty stocks and prices
                $mas = $this->deleteEmptyPosition($mas);
                $mas = $this->deleteSupplPosition($mas);
                $mas = $this->deleteRepeatPosition($mas);

                if (empty($mas)) {
                    $list = $this->getHtmlForm("error/nothing_found");
                    $list = str_replace("{error_nothing_found}", $this->err1, $list);
                    return array($list, "", "", 0);
                }

                // sort by delivery and price
                foreach ($mas as $mas_key => $mas_val) {
                    $mas[$mas_key] = $this->multiSort($mas[$mas_key], "delivery_days", "price");
                }

                // sort like: first = min delivery, second = min price, else = default
                $mas = $this->sortByMinStock($mas);

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, $view);

                // show search list
                FormClass::cacheArticlesPhotos($where_art_id_str);
                FormClass::cacheInfoTemplates($where_art_id_str);
                $list = $this->outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view);
            }
            $count = count($mas);
            if ($count == 0) {
                $list = "$error";
            }
        }

        $art_id_array = [];
        foreach ($mas as $key => $value) {
            array_push($art_id_array, $key);
        }
        $art_id_array = implode(",", $art_id_array);

        return array($list, $filters, $list_brand, $current_value, $art_id_array, $count);
    }

    public function shortSearchList($art_id_search)
    {
        $art_id_search = $this->getUrlNumber($art_id_search);
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $client_id = $this->getClient();
        $tpoint_id = $this->getTpointID();
        $cur = $this->getCurrentExrate();
        $view = 1;
        session_start();
        $temp_key = session_id();
        $mas = [];
        $list = "";

        $article_nr_search = $this->getArticleSearch($art_id_search);
        $brand_nr_search = $this->getArticleBrand($art_id_search);

        $arts = [];
        $r = $db->query("SELECT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID)
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_nr_search AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END)
        GROUP BY t2c.`ART_ID` 
        ORDER BY t2n.NAME ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[] = $art_id;
        }
        $where_art_id_str = implode(",", $arts);

        if ($where_art_id_str != "") {
            $this->createTemporarySearchTable($temp_key);
            list($error) = $this->getSearchMessages();
            $list = $this->drawHeaderSearchList($view);

            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "");
            $n = $db->num_rows($r);
            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                    $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name = $db->result($r, $i - 1, "NAME");
                    $return_days = $db->result($r, $i - 1, "return_delay");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $stock = intval($db->result($r, $i - 1, "AMOUNT"));
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $format_name = $this->getFormatAticle($article_nr_displ);

                    // price
                    $price = $this->getArticlePrice($art_id);
                    if ($suppl_id != 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }
                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur == 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }

                    // delivery
                    $deliveryData = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
                    $delivery_info = $deliveryData["info"];
                    $delivery_days = $deliveryData["days"];
                    $delivery_short_info = $deliveryData["short"];
                    if ($suppl_id != 0) {
                        $deliveryData = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
                        $delivery_info = $deliveryData["info"];
                        $delivery_days = $deliveryData["days"];
                        $delivery_short_info = $deliveryData["short"];
                    }

                    // ORDER BY search art and suppl_id
                    if (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id == 0) ? 1 : 0;
                    }

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if ($price != 0 || (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                        if ($stock > 0 || (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                            // visible suppl storage
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                if ($art_id_search != $art_id) {
                                    $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                    VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', '$article_name', '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                                }
                            }
                        }
                    }
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `article_nr_displ` ASC;");
                $n = $db->num_rows($r);

                if ($n == 1) {
                    $stock = $db->result($r, 0, "stock");
                    $price = $db->result($r, 0, "price");
                    if ($stock == 0 && $price == 0) {
                        $list = $this->getHtmlForm("error/nothing_found");
                        $list = str_replace("{error_nothing_found}", $this->err1, $list);
                        return array($list, "", "", 0);
                    }
                }

                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "art_id");
                    $article_nr_displ = $db->result($r, $i - 1, "article_nr_displ");
                    $brand_id = $db->result($r, $i - 1, "brand_id");
                    $brand_name = $db->result($r, $i - 1, "brand_name");
                    $article_name = $db->result($r, $i - 1, "article_name");
                    $delivery_days = $db->result($r, $i - 1, "delivery_days");
                    $delivery_info = $db->result($r, $i - 1, "delivery_info");
                    $delivery_short_info = $db->result($r, $i - 1, "delivery_short_info");
                    $stock = $db->result($r, $i - 1, "stock");
                    $price = $db->result($r, $i - 1, "price");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $return_days = $db->result($r, $i - 1, "return_days");
                    $status = $db->result($r, $i - 1, "status");
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
                    $mas[$mas_key] = $this->multiSort($mas[$mas_key], "delivery_days", "price");
                }

                // sort like: first = min delivery, second = min price, else = default
                $mas = $this->sortByMinStock($mas);

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, $view);

                // show search list
                $list = $this->outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view);
            }
            if (count($mas) < 1) {
                $list = "";
            }
        }

        return $this->replaceLang($list);
    }

    public function getTpointDeliveryInfos($tpoint_id, $where_art_id_str)
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $r = $db->query("SELECT tpdt.delivery_days, tpdt.week_day, tpdt.time_from_del, tpdt.time_to_del, tpdt.storage_id 
        FROM `T_POINT_DELIVERY_TIME` tpdt
            JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.STORAGE_ID = tpdt.storage_id)
        WHERE t2asc.ART_ID IN ($where_art_id_str) AND tpdt.status = '1' AND tpdt.tpoint_id = '$tpoint_id' AND tpdt.week_day = '$week_day' AND tpdt.time_from <= '$cur_time' AND tpdt.time_to >= '$cur_time' 
        ORDER BY tpdt.delivery_days ASC;");
        $delivers = mysqli_fetch_all($r, MYSQLI_ASSOC);
        $array = [];
        foreach ($delivers as $deliver) {
            $delivery_days = $deliver["delivery_days"];
            $time_from_del = substr($deliver["time_from_del"], 0, -3);
            $time_to_del = substr($deliver["time_to_del"], 0, -3);
            $week = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $delivery_days . " days"));
            $today = (($delivery_days == 0)
                ? "<span class=\"delivery-green\">{today_cap}</span>"
                : (($delivery_days == 1)
                    ? "<span class=\"delivery-blue\">{tomorrow_cap}</span>"
                    : "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>"));
            $info = "$today<br>$time_from_del - $time_to_del";
            $delivery_short_info = "$today<br>{with_cap} $time_from_del";
            $array[$deliver["storage_id"]] = compact("info", "delivery_days", "delivery_short_info");
        }
        return $array;
    }

    public function getArticlePrices($where_art_id_str)
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
            $price = $row["price"];
            $cash_id = $row["cash_id"];
            $price = $this->getPriceRatingKours($price, $cash_id, 1);
            if ($margin_price_lvl > 0) {
                $price = floatval($price) + round($price * $margin_price_lvl / 100, 2);
            }
            if ($cash_id == 1) {
                $price = $client->getClientPriceRounding($client_id, $price);
            }
            $prices[$row["ART_ID"]] = $price;
        }
        return $prices;
    }

    public function getTpointSupplDeliveriesInfo($tpoint_id)
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $result = [];
        $r = $db->query("SELECT `delivery_days`, `week_day`, `time_from_del`, `time_to_del`, `suppl_storage_id`, `suppl_id` 
        FROM `T_POINT_SUPPL_DELIVERY_TIME` 
        WHERE `status` = '1' AND `tpoint_id` = '$tpoint_id' AND `week_day` = '$week_day' AND `time_from` <= '$cur_time' AND `time_to` >= '$cur_time';");
        $deliveryTimes = mysqli_fetch_all($r, MYSQLI_ASSOC);
        foreach ($deliveryTimes as $deliveryTime) {
            $time_from_del = substr($deliveryTime["time_from_del"], 0, -3);
            $time_to_del = substr($deliveryTime["time_to_del"], 0, -3);
            $week = date("N", strtotime(" + " . $deliveryTime["delivery_days"] . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $deliveryTime["delivery_days"] . " days"));
            $delivery_days = $deliveryTime["delivery_days"];
            $today = (($delivery_days == 0)
                ? "<span class=\"delivery-green\">{today_cap}</span>"
                : (($delivery_days == 1)
                    ? "<span class=\"delivery-blue\">{tomorrow_cap}</span>"
                    : "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>"));
            $info = "$today<br>{$time_from_del} - {$time_to_del}";
            $delivery_short_info = "$today<br>{with_cap} $time_from_del";
            $result[$deliveryTime["suppl_id"]][$deliveryTime["suppl_storage_id"]] = [
                "info"                  => $info,
                "delivery_days"         => $deliveryTime["delivery_days"],
                "delivery_short_info"   => $delivery_short_info
            ];
        }
        return $result;
    }

    public function getArticleSupplPrices($where_art_id_str)
    {
        $dbt = DbSingleton::getTokoDb();
        $db = DbSingleton::getDbm();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $tpoint = $this->getTpointID();
        $client_id = $this->getClient();
        $price = 0;
        list(, , $price_suppl_lvl, $margin_price_suppl_lvl, $client_vat) = $this->getDpClientPriceLevels($client_id);
        $r = $dbt->query("SELECT t2a.ART_ID, t2si.client_storage_id, t2si.price_usd, t2si.suppl_id, acvc.*, t2si.suppl_id, tpsf.margin, tpsf.delivery, tpsf.margin2 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
            LEFT OUTER JOIN {$db->getDbName()}.A_CLIENTS_VAT_CONDITIONS acvc ON (acvc.client_id = t2si.suppl_id)
            LEFT OUTER JOIN `T_POINT_SUPPL_FM` tpsf ON (tpsf.suppl_id = t2si.suppl_id AND tpsf.suppl_storage_id = t2si.client_storage_id)
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2si.status = 1 AND tpsf.tpoint_id = $tpoint AND tpsf.price_rating_id = '$price_suppl_lvl' AND tpsf.price_from <= t2si.price_usd AND tpsf.price_to >= t2si.price_usd;");
        $supplPrices = mysqli_fetch_all($r, MYSQLI_ASSOC);
        $prices = [];
        foreach ($supplPrices as $supplPrice) {
            $suppl_price_usd = floatval($supplPrice["price_usd"]);
            $price_suppl = $suppl_price_usd;
            if ($supplPrice["margin"] > 0) {
                $price = ($price_suppl + $price_suppl * $supplPrice["margin"] / 100) - $price_suppl;
                if ($price > $supplPrice["delivery"]) {
                    $price = ($price_suppl + $price_suppl * $supplPrice["margin"] / 100);
                }
                if ($price <= $supplPrice["delivery"]) {
                    $price = $price_suppl + $price_suppl * $supplPrice["margin2"] / 100 + $supplPrice["delivery"];
                }
                if ($margin_price_suppl_lvl > 0 && $margin_price_suppl_lvl != "") {
                    $price = $price + $price * $margin_price_suppl_lvl / 100;
                }
                if ($client_vat == 1) {
                    if ($supplPrice["price_in_vat"] == 0 && $supplPrice["show_in_vat"] == 1 && $supplPrice["price_add_vat"] == 1) {
                        $price = $price + $price * 20 / 100;
                    }
                    if ($supplPrice["price_in_vat"] == 0 && $supplPrice["show_in_vat"] == 0) {
                        $price = 0;
                    }
                }
            }
            $price = round($price, 2);
            $cur_usd = $kours->getKours("dollar");
            $price = $price * $cur_usd;
            $price = $client->getClientPriceRounding($client_id, $price);
            $prices[$supplPrice["ART_ID"]][$supplPrice["suppl_id"]][$supplPrice["client_storage_id"]] = $price;
        }
        return $prices;
    }

    /*
     * check if art_id is original
     * */
    public function checkOriginalEquipment($art_id, $search_number)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `SEARCH_NUMBER` FROM `T2_CROSS` WHERE `ART_ID` = $art_id AND `KIND` = 3 AND `RELATION` = 0;");
        $n = $db->num_rows($r);
        $nom = 0;
        for ($i = 1; $i <= $n; $i++) {
            $number = $db->result($r, $i - 1, "SEARCH_NUMBER");
            if ($search_number == $number) {
                $nom++;
            }
        }
        return ($nom > 0);
    }

    /*
     * Check relation from t2_cross
     * */
    public function checkAnalogTypes($art_id, $article_nr_search, $relation_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT COUNT(`ART_ID`) as count_arts FROM `T2_CROSS` 
        WHERE `ART_ID` = $art_id AND `SEARCH_NUMBER` LIKE '$article_nr_search' AND `KIND` IN (3,4) AND `RELATION` = $relation_id;");
        $n = $db->result($r, 0, `count_arts`);
        return ($n > 0);
    }

    /*
     * Get Kind of brand
     * */
    public function getBrandType($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `KIND` FROM `T2_BRANDS` WHERE `BRAND_ID` = $brand_id LIMIT 1;");
        $kind = $db->result($r, 0, "KIND");
        return ($kind == 3);
    }

    /*
     * Get Article image Type
     * */
    public function getIndexTypeImage($art_id, $article_nr_search, $article_nr_displ, $format_name, $brand_id, $brand_nr_search)
    {
        $true_art_id = $this->getArtID($article_nr_search);
        $brand_name = $this->getBrandName($brand_nr_search);
        // ANALOGS
        $image_analog = $this->images . "/tcdanalogs/clone.svg";
        $index_type = "<img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_analog}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_analog} $article_nr_search $brand_name\">";
        // OE
        if ($this->checkOriginalEquipment($true_art_id, $format_name)) {
            $image_analog = $this->images . "/tcdanalogs/OE.svg";
            $index_type = "<img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_original}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_original} $article_nr_search $brand_name\">";
        }
        // INCLUDED
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 1)) {
            $image_analog = $this->images . "/tcdanalogs/chevron-square-down.svg";
            $index_type = "<img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_included}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_included} $article_nr_search $brand_name\">";
        }
        // PRESENTED
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 2)) {
            $image_analog = $this->images . "/tcdanalogs/chevron-square-up.svg";
            $index_type = "<img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_presented}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_presented} $article_nr_search $brand_name\">";
        }
        // COMPANION
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 3)) {
            $image_analog = $this->images . "/tcdanalogs/plus-square.svg";
            $index_type = "<img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_companion}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_companion} $article_nr_search $brand_name\">";
        }
        // REQUESTED
        if ($article_nr_search != "") if (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && ($brand_id == $brand_nr_search)) {
            $image_analog = $this->images . "/tcdanalogs/square.svg";
            $index_type = "<img data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_requested}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_requested} $article_nr_search $brand_name\">";
            if ($this->getBrandType($brand_id)) {
                $image_analog = $this->images . "/tcdanalogs/OE.svg";
                $index_type .= "<img style=\"margin-left: 5px;\" data-src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_original}\" class=\"tooltips lazy\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_original} $article_nr_search $brand_name\">";
            }
        }
        return $index_type;
    }

    /*
     * Check visibility of SUPPL storage
     * */
    public function getSuppLStorageVisible($suppl_id, $storage_id)
    {
        $db = DbSingleton::getDbm();
        if ($suppl_id > 0) {
            $r = $db->query("SELECT `visible` FROM `A_CLIENTS_STORAGE` WHERE `client_id` = $suppl_id AND `id` = $storage_id LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $visible = $db->result($r, 0, "visible");
                if ($visible == 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /*
     * Show SEARCH Line OR Card
     * */
    public function printSearchList($id, $art_id, $article_nr_displ, $brand_id, $brand_name, $article_name, $delivery_info, $stock, $price, $article_nr_search, $ll, $class, $hide, $border, $none, $brand_nr_search, $suppl_id, $return_days, $delivery_days, $delivery_short_info, $storage_id, $status, $view, $status_auto = 0, $mfa_id = 0, $model = "")
    {
        $showform = new FormClass();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $shop = new ShopClass();
        $automan = new AutoClass();
        $cur = $this->getCurrentExrate();
        $kours_cap = $this->getSymbolExrate($cur);
        $format_name = $this->getFormatAticle($article_nr_displ);
        $format_brand_name = $this->getFormatBrand($brand_name);
        $return_days_alt = $return_days_img = "";
        if ($suppl_id > 0) {
            if ($stock != 0) {
                if ($return_days == 0) {
                    $return_days_alt = "{no_exchange}";
                    $return_days_img = $this->images . "/exchange/exchange2.png";
                }
                elseif ($return_days == 14) {
                    $return_days_alt = "";
                    $return_days_img = "";
                }
                elseif ($return_days >= 15) {
                    $return_days_alt = "{return_within} $return_days {days_cap}";
                    $return_days_img = $this->images . "/exchange/exchange1.png";
                } else {
                    $return_days_alt = "{return_within} $return_days {days_cap}";
                    $return_days_img = $this->images . "/exchange/exchange3.png";
                }
            }
        }
        if (!($this->checkActionPrice($art_id))) {
            $action_form = "";
            $action_count = "";
        } else {
            list(, $action_amount, $action_price, $action_max_amount, $action_data) = $this->checkActionPrice($art_id);
            $action_price = $kours->getKoursFromUSA($action_price, $cur);
            if ($cur == 1) {
                $action_price = $client->getClientPriceRounding($this->getClient(), $action_price);
            }
            $action_form = $this->getHtmlForm("search/action_box");
            $action_form = str_replace("{action_price}", $action_price, $action_form);
            $action_form = str_replace("{action_amount}", $action_amount, $action_form);
            $action_form = str_replace("{action_data}", ($action_data > 0) ? date("d.m.Y", strtotime($action_data)) : "{indefinitely_cap}", $action_form);
            $action_form = str_replace("{action_max_amount}", ($action_max_amount > 0) ? "{yes_cap}" : "{no_cap}", $action_form);
            $action_form = str_replace("{cur_cap}", $kours_cap, $action_form);
            $action_count = "oninput=\"changeActionCount('$id','$action_price','$action_amount');\"";
        }

        $form = ($view) ? $this->getHtmlForm("article_card") : $this->getHtmlForm("product_card");
        $form = str_replace("{product_i}", $id, $form);
        $form = str_replace("{art_id}", $art_id, $form);
        $form = str_replace("{brand_id}", $brand_id, $form);
        $form = str_replace("{product_name}", $article_nr_displ, $form);
        $form = str_replace("{product_brand}", $brand_name, $form);
        $form = str_replace("{product_format_name}", $format_name, $form);
        $form = str_replace("{product_format_brand}", $format_brand_name, $form);
        $format_brand_link = $this->getBrandLink($brand_id);
        $form = str_replace("{page_product_link}", $this->getSiteLink() . "$this->products_link/$format_name-$format_brand_link-$art_id/", $form);
        $form = str_replace("{product_brand_link}", $this->getBrandLink($brand_id), $form);
        $product_text = ($article_name == "") ? "{details_name_cap}" : $article_name;
        $format_product_text = ($article_name == "") ? "{details_name_cap}" : $this->formatArticleName($article_name);
        $mfa_text = "";
        if ($status_auto == 0) {
            if ($mfa_id > 0) {
                $mfa_name = $automan->getMfaBrand($mfa_id);
                $mfa_text .= " {on_cap} $mfa_name";
                if ($model != "") {
                    $mfa_text .= " $model";
                }
            }
            $product_text .= $mfa_text;
            $format_product_text .= $mfa_text;
        }
        $form = str_replace("{product_text}", $product_text, $form);
        $form = str_replace("{format_product_text}", $format_product_text, $form);
        $form = str_replace("{product_stock}", ($suppl_id == 0) ? ($stock > 10 ? ">10" : $stock) : $stock, $form);
        $form = str_replace("{product_real_stock}", $stock, $form);
        $form = str_replace("{product_storage_id}", $storage_id, $form);
        $form = str_replace("{product_suppl_id}", $suppl_id, $form);

        $form = str_replace("{return_days_img}", $return_days_img, $form);
        $form = str_replace("{return_days_alt}", $return_days_alt, $form);
        $form = str_replace("{return_display}", ($return_days == 14 || $return_days_img == "") ? "none" : "", $form);

        $form = str_replace("{photo_src}", $showform->getArticleActivePhoto($art_id), $form);
        $form = str_replace("{photo_display}", $this->checkPhoto($art_id) ? "" : "none", $form);
        $form = str_replace("{product_main_photo}", ($showform->getArticlePhoto($art_id) == "") ? $this->noPhoto : $showform->getArticlePhoto($art_id), $form);
        $delivery_info = str_replace('"', "", $delivery_info);
        $form = str_replace("{product_del}", $delivery_info, $form);
        $form = str_replace("{product_dd}", $delivery_days, $form);

        $form = str_replace("{product_delivery_class}", "", $form);
        $delivery_short_info = str_replace("<br>", " ", $delivery_short_info);
        if ($delivery_days == 0 && $suppl_id == 0) {
            $delivery_short_info = "<span class='delivery-green'>{send_done}</span>";
        }
        $form = str_replace("{product_delivery_short_info}", $delivery_short_info, $form);

        $form = str_replace("{product_price}", $price . " $kours_cap", $form);
        $form = str_replace("{product_true_price}", $price, $form);
        $form = str_replace("{product_kours_cap}", $kours_cap, $form);

        $form = str_replace("{product_action}", $action_form, $form);
        $form = str_replace("{product_action_count}", $action_count, $form);
        $form = str_replace("{product_title_del}", str_replace("<br>", " ", $delivery_info), $form);
        $form = str_replace("{analog_display}", (($article_nr_displ == $article_nr_search || $format_name == $article_nr_search) && ($brand_id == $brand_nr_search)) ? "none" : "", $form);
        $form = str_replace("{product_barcode}", $this->getBarcode($art_id), $form);

        $form = str_replace("{style_border}", $border, $form);
        $form = str_replace("{style_class}", $class, $form);
        $form = str_replace("{style_none}", $none, $form);
        $form = str_replace("{style_hide}", $hide, $form);

        $flagData = $showform->getCountryFlag($brand_id);
        $form = str_replace("{country_display}", (!$flagData) ? "none" : "", $form);
        $form = str_replace("{flag_image}", $flagData["flag"], $form);
        $form = str_replace("{country_name}", $flagData["country"], $form);
        $form = str_replace("{instock}", ($suppl_id == 0) ? "<b class=\"tables__instock\"> {in_stock}</b>" : "", $form);
        $form = str_replace("{index_type}", $this->getIndexTypeImage($art_id, $article_nr_search, $article_nr_displ, $format_name, $brand_id, $brand_nr_search), $form);
        $form = str_replace("{count_users}", $client->getUsersCount(), $form);
        $form = str_replace("{data_today}", date("Y-m-d"), $form);
        $form = str_replace("{tpoint_full_name}", ($suppl_id == 0) ? $client->getArticleStorageTPoint($storage_id) : "", $form);

        $form = str_replace("{product_info}", $showform->getArticleInfoForm($art_id, 1), $form);
        $form = str_replace("{product_button}", ($price == 0) ? "none" : "", $form);

        $photoData = $showform->getArticleCatalogPhoto($art_id, $brand_id);
        $form = str_replace("{product_image}", $photoData["photo_name"], $form);
        $form = str_replace("{product_image_class}", ($photoData["status"] == 0) ? "" : "filter-bw", $form);

        $form = str_replace("{product_title}", "$article_name $brand_name $article_nr_displ", $form);

        $basket_amount = $shop->getBasketArticleAmount($art_id, $storage_id);
        $form = str_replace("{basket_amount}", ($basket_amount > 0) ? "{site_basket}: $basket_amount {amount_abbr}." : "", $form);

        if ($this->checkT2Link($this->getCookieAuto(), $art_id)) {
            $form = str_replace("{product_auto_appl}", "{is_applicable}", $form);
        } else {
            $form = str_replace("{product_auto_appl}", "", $form);
        }

        if ($status) {
            // PRICE & STOCK NICE
            $form = str_replace("{price_row_status}", "", $form);
            $form = str_replace("{soldout_row_status}", "none", $form);
        } else {
            // PRICE & STOCK = 0
            $form = str_replace("{price_row_status}", "none", $form);
            $form = str_replace("{soldout_row_status}", "", $form);
        }

        // status_auto
        if ($status_auto == 2) {
            $form = str_replace("{applicable_display}", "dnone", $form);
        }

        $auto_typ_id = $this->getCookieAuto();
        if ($auto_typ_id != "") {
            if ($this->checkT2Link($auto_typ_id, $art_id)) {
                $form = str_replace("{applicable_display}", "applicable-active", $form);
                $form = str_replace("{applicable_display_text}", "{is_applicable}", $form);
                $form = str_replace("{applicable_onclick}", "", $form);
            } else {
                if ($status_auto == 1) {
                    $form = str_replace("{applicable_display}", "dnone", $form);
                }
                $form = str_replace("{applicable_display}", "", $form);
                $form = str_replace("{applicable_display_text}", "{is_didnt_applicable}", $form);
                $form = str_replace("{applicable_onclick}", "", $form);
            }
        }
        if ($status_auto == 1) {
            $form = str_replace("{applicable_display}", "dnone", $form);
        }
        $form = str_replace("{applicable_display}", "", $form);
        $form = str_replace("{applicable_display_text}", "{is_not_applicable}", $form);
        $form = str_replace("{applicable_onclick}", "toggleNavMob();", $form);

        $list = "$form";
        if (!$view) {
            $list .= "$ll";
        }

        $list = $this->replaceLang($list);

        return $list;
    }

    public function getFaqForm()
    {
        $form = $this->getHtmlForm("faq/request-card");
        $form = "<div class=\"col-lg-4 col-12 pad0\"><div class=\"article-card\">$form</div></div>";
        $form = $this->replaceLang($form);
        return $form;
    }

    public function getFaqSocialsForm()
    {
        $form = $this->getHtmlForm("faq/request-socials");
        $form = $this->replaceLang($form);
        return $form;
    }

    public function setClientRequestDone()
    {
        $form = $this->getHtmlForm("faq/request-done");
        $form = $this->replaceLang($form);
        return $form;
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
            $category_id = $db->result($r, $i - 1, "client_category");
            array_push($categories, $category_id);
        }
        $categories = implode(",", $categories);

        $r = $db->query("SELECT `id` FROM `ACTION_CLIENTS` WHERE `art_id` = $art_id AND `status` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $action_id = $db->result($r, $i - 1, "id");
            $r2 = $db->query("SELECT * FROM `ACTION_CLIENTS_LIST` WHERE `action_id` = $action_id AND `client_id` = $client_id;");
            $n2 = $db->num_rows($r2);
            if ($n2 > 0) {
                array_push($actions, $action_id);
            }
            if ($categories != "") {
                $r3 = $db->query("SELECT * FROM `ACTION_CLIENTS_CATEGORY` WHERE `action_id` = $action_id AND `category_id` IN ($categories);");
                $n3 = $db->num_rows($r3);
                if ($n3 > 0) {
                    array_push($actions, $action_id);
                }
            }
        }

        $actions = implode(",", $actions);
        if ($actions == "") {
            return false;
        } else {
            $r = $db->query("SELECT `id`, `amount`, `max_amount`, `price`, `data` FROM `ACTION_CLIENTS` WHERE `id` IN ($actions) AND `status` = 1 LIMIT 1;");
            $action_id = $db->result($r, 0, "id");
            $amount = $db->result($r, 0, "amount");
            $max_amount = $db->result($r, 0, "max_amount");
            $price = $db->result($r, 0, "price");
            $data = $db->result($r, 0, "data");
            if ($this->checkActionAmount($art_id, $max_amount, $data)) {
                return array($action_id, $amount, $price, $max_amount, $data);
            } else {
                return false;
            }
        }
    }

    /*
     * check action amount
     * */
    public function checkActionAmount($art_id, $max_amount, $data)
    {
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();
        $data_today = date("Y-m-d");
        $all_amount = 0;
        $r = $dbt->query("SELECT `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` = $art_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $amount = $db->result($r, $i - 1, "AMOUNT");
            $all_amount += $amount;
        }
        $r = $db->query("SELECT `amount` FROM `J_DP_STR` WHERE `art_id` = $art_id AND `status_dps` = '93';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $amount = $db->result($r, $i - 1, "amount");
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
            $cash_id = $dbt->result($r, 0, "cash_id");
        }

        $price = 0;
        list($price_lvl, $margin_price_lvl) = $this->getDpClientPriceLevels($client_id);
        $markup_min = $client->getClientMarkupMin($client_id);
        $r = $dbt->query("SELECT t2apr.price_$price_lvl, t2apr.minMarkup, t2aps.OPER_PRICE
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id = t2a.ART_ID)
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_STOCK` t2aps ON (t2aps.ART_ID = t2a.ART_ID)
        WHERE t2a.ART_ID = $art_id AND t2apr.in_use = '1' LIMIT 1;");
        $n = $dbt->num_rows($r);
        if ($n == 1) {
            $price = $dbt->result($r, 0, "price_" . $price_lvl);
            $minMarkup = $dbt->result($r, 0, "minMarkup");
            $oper_price = $dbt->result($r, 0, "OPER_PRICE");
            $float_price = floatval($price);
            // 1
            if ($margin_price_lvl > 0) {
                $price = $float_price + round($price * $margin_price_lvl / 100, 2);
            }
            // 2
            if ($margin_price_lvl < 0 && $markup_min == 0) {
                $price_minus = $price + ($price * $margin_price_lvl / 100);
                $oper_limit = $oper_price + ($oper_price * $minMarkup / 100);
                if ($price_minus >= $oper_limit) {
                    $price = $price_minus;
                }
                elseif ($oper_limit >= $price) {
                    true;
                } else {
                    $price = $oper_limit;
                }
            }
            // 3
            $art_cash_id = $this->getArticlePriceRatingCash($art_id);
            if ($margin_price_lvl < 0 && $markup_min > 0) {
                $price = $this->getPriceRatingKours($price, $art_cash_id, $cash_id);
                $proc_price_margin = $price - ($price * abs($margin_price_lvl) / 100);
                $proc_oper_price_min = $oper_price + ($oper_price * $markup_min / 100);
                if ($proc_price_margin >= $proc_oper_price_min) {
                    $price = $proc_price_margin;
                }
                elseif (($proc_price_margin < $proc_oper_price_min) && ($proc_oper_price_min > $price)) {
                    true;
                } else {
                    $price = $proc_oper_price_min;
                }
                $price = $this->getPriceRatingKours($price, $cash_id, $art_cash_id);
            }
            $price = $this->getPriceRatingKours($price, $cash_id, $cur);
            if ($cur == 1) {
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
        $n = $dbt->num_rows($r);
        if ($n == 1) {
            $price = $dbt->result($r, 0, "price_" . $price_lvl);
            $minMarkup = $dbt->result($r, 0, "minMarkup");
            $oper_price = $dbt->result($r, 0, "OPER_PRICE");
            $cash_id = $dbt->result($r, 0, "cash_id");
            if ($margin_price_lvl > 0) {
                $price = floatval($price) + round($price * $margin_price_lvl / 100, 2);
            }
            if ($margin_price_lvl < 0 && $markup_min == 0) {
                $price_minus = $price + ($price * $margin_price_lvl / 100);
                $oper_limit = $oper_price + ($oper_price * $minMarkup / 100);
                if ($price_minus >= $oper_limit) {
                    $price = $price_minus;
                }
                elseif ($oper_limit >= $price) {
                    true;
                } else {
                    $price = $oper_limit;
                }
            }
            if ($margin_price_lvl < 0 && $markup_min > 0) {
                $price = $this->getPriceRatingKours($price, $cash_id, 2);
                $proc_price_margin = $price - ($price * abs($margin_price_lvl) / 100);
                $proc_oper_price_min = $oper_price + ($oper_price * $markup_min / 100);
                if ($proc_price_margin >= $proc_oper_price_min) {
                    $price = $proc_price_margin;
                }
                elseif (($proc_price_margin < $proc_oper_price_min) && ($proc_oper_price_min > $price)) {
                    true;
                } else {
                    $price = $proc_oper_price_min;
                }
                $price = $this->getPriceRatingKours($price, 2, $cash_id);
            }
            $price = $this->getPriceRatingKours($price, $cash_id, 1);
            if ($cash_id == 1) {
                $price = $client->getClientPriceRounding($client_id, $price);
            }
        }
        return $price;
    }

    public function getArticleSupplPrice($art_id, $suppl_id, $suppl_storage_id)
    {
        $dbt = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $tpoint = $this->getTpointID();
        $client_id = $this->getClient();
        $price = 0;
        list(, , $price_suppl_lvl, $margin_price_suppl_lvl, $client_vat) = $this->getDpClientPriceLevels($client_id);
        $r = $dbt->query("SELECT t2si.price_usd 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
        WHERE t2a.ART_ID = $art_id AND t2si.suppl_id = $suppl_id LIMIT 1;");
        $n = $dbt->num_rows($r);
        if ($n == 1) {
            $suppl_price_usd = floatval($dbt->result($r, 0, "price_usd"));
            list($price_in_vat, $show_in_vat, $price_add_vat) = $this->getSupplVatConditions($suppl_id);
            $price_suppl = $suppl_price_usd;
            list($suppl_margin_fm, $suppl_delivery_fm, $suppl_margin2_fm) = $this->getTpointSupplFm($tpoint, $suppl_id, $suppl_storage_id, $price_suppl, $price_suppl_lvl);
            if ($suppl_margin_fm > 0) {
                $price = ($price_suppl + $price_suppl * $suppl_margin_fm / 100) - $price_suppl;
                if ($price > $suppl_delivery_fm) {
                    $price = ($price_suppl + $price_suppl * $suppl_margin_fm / 100);
                }
                if ($price <= $suppl_delivery_fm) {
                    $price = $price_suppl + $price_suppl * $suppl_margin2_fm / 100 + $suppl_delivery_fm;
                }
                if ($margin_price_suppl_lvl > 0 && $margin_price_suppl_lvl != "") {
                    $price = $price + $price * $margin_price_suppl_lvl / 100;
                }
                if ($client_vat == 1) {
                    if ($price_in_vat == 0 && $show_in_vat == 1 && $price_add_vat == 1) {
                        $price = $price + $price * 20 / 100;
                    }
                    if ($price_in_vat == 0 && $show_in_vat == 0) {
                        $price = 0;
                    }
                }
            }
            $price = round($price, 2);
        }
        $cur_usd = $kours->getKours("dollar");
        $price = $price * $cur_usd;
        $price = $client->getClientPriceRounding($client_id, $price);
        return $price;
    }

    public function getDpClientPriceLevels($client_id)
    {
        $db = DbSingleton::getDbm();
        $price_lvl = $margin_price_lvl = $price_suppl_lvl = $margin_price_suppl_lvl = $client_vat = 0;
        $r = $db->query("SELECT * FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $price_lvl = $db->result($r, 0, "price_lvl");
            $price_lvl++;
            $margin_price_lvl = $db->result($r, 0, "margin_price_lvl");
            $price_suppl_lvl = $db->result($r, 0, "price_suppl_lvl");
            $price_suppl_lvl++;
            $margin_price_suppl_lvl = $db->result($r, 0, "margin_price_suppl_lvl");
            $client_vat = $db->result($r, 0, "client_vat");
        }
        return array($price_lvl, $margin_price_lvl, $price_suppl_lvl, $margin_price_suppl_lvl, $client_vat);
    }

    public function getSupplVatConditions($suppl_id)
    {
        $db = DbSingleton::getDbm();
        $price_in_vat = $show_in_vat = $price_add_vat = 0;
        $r = $db->query("SELECT `price_in_vat`, `show_in_vat`, `price_add_vat` FROM `A_CLIENTS_VAT_CONDITIONS` WHERE `client_id` = $suppl_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $price_in_vat = $db->result($r, 0, "price_in_vat");
            $show_in_vat = $db->result($r, 0, "show_in_vat");
            $price_add_vat = $db->result($r, 0, "price_add_vat");
        }
        return array($price_in_vat, $show_in_vat, $price_add_vat);
    }

    public function getTpointSupplFm($tpoint_id, $suppl_id, $suppl_storage_id, $price_suppl, $price_suppl_lvl)
    {
        $db = DbSingleton::getTokoDb();
        $margin = $delivery = $margin2 = 0;
        $r = $db->query("SELECT `margin`, `delivery`, `margin2` 
        FROM `T_POINT_SUPPL_FM` 
        WHERE `tpoint_id` = '$tpoint_id' AND `suppl_id` = '$suppl_id' AND `suppl_storage_id` = '$suppl_storage_id' AND `price_from` <= '$price_suppl' 
        AND `price_to` >= '$price_suppl' AND `price_rating_id` = '$price_suppl_lvl' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $margin = $db->result($r, 0, "margin");
            $delivery = $db->result($r, 0, "delivery");
            $margin2 = $db->result($r, 0, "margin2");
        }
        return array($margin, $delivery, $margin2);
    }

    public function getTpointDeliveryInfo($tpoint_id, $storage_id)
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $delivery_days = 0;
        $info = $short_info = "";
        $r = $db->query("SELECT `delivery_days`, `time_from_del`, `time_to_del` 
        FROM `T_POINT_DELIVERY_TIME`
        WHERE `status` = '1' AND `tpoint_id` = '$tpoint_id' AND `storage_id` = '$storage_id' AND `week_day` = '$week_day' AND `time_from` <= '$cur_time' AND `time_to` >= '$cur_time' 
        ORDER BY `delivery_days` ASC LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $delivery_days = $db->result($r, 0, "delivery_days");
            $time_from_del = substr($db->result($r, 0, "time_from_del"), 0, -3);
            $time_to_del = substr($db->result($r, 0, "time_to_del"), 0, -3);
            $week = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $delivery_days . " days"));
            $today = (($delivery_days == 0)
                ? "<span class=\"delivery-green\">{today_cap}</span>"
                : (($delivery_days == 1)
                    ? "<span class=\"delivery-blue\">{tomorrow_cap}</span>"
                    : "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>"));

            $info = "$today<br>$time_from_del - $time_to_del";
            $short_info = "$today<br>{with_cap} $time_from_del";
        }
        return array(
            "info"  => $info,
            "days"  => $delivery_days,
            "short" => $short_info
        );
    }

    public function getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $suppl_storage_id)
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $delivery_days = 0;
        $info = $short_info = "";
        $r = $db->query("SELECT `delivery_days`, `time_from_del`, `time_to_del` 
        FROM `T_POINT_SUPPL_DELIVERY_TIME` 
        WHERE `status` = '1' AND `tpoint_id` = '$tpoint_id' AND `suppl_storage_id` = '$suppl_storage_id' AND `suppl_id` = '$suppl_id' AND `week_day` = '$week_day' 
        AND `time_from`<='$cur_time' AND `time_to`>='$cur_time' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $delivery_days = $db->result($r, 0, "delivery_days");
            $time_from_del = substr($db->result($r, 0, "time_from_del"), 0, -3);
            $time_to_del = substr($db->result($r, 0, "time_to_del"), 0, -3);
            $week = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $delivery_days . " days"));
            $today = (($delivery_days == 0)
                ? "<span class=\"delivery-green\">{today_cap}</span>"
                : (($delivery_days == 1)
                    ? "<span class=\"delivery-blue\">{tomorrow_cap}</span>"
                    : "<span class=\"delivery-dark\">$date_del ($week_day_short)</span>"));

            $info = "$today<br>$time_from_del - $time_to_del";
            $short_info = "$today<br>{with_cap} $time_from_del";
        }
        return array(
            "info"  => $info,
            "days"  => $delivery_days,
            "short" => $short_info
        );
    }

    /*
     * get original numbers form
     * */
    public function getOriginalNumbers($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT t2c.DISPLAY_NR, t2b.BRAND_NAME 
        FROM `T2_CROSS` t2c
            LEFT JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID)
        WHERE t2c.KIND = 3 AND t2c.RELATION = 0 AND t2c.ART_ID = $art_id;");
        $n = $db->num_rows($r);
        $arr = [];
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $art_name = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                $arr[$brand_name][$i] = $art_name;
            }
            $list = "
            <div class=\"info__numbers\">
                <div class=\"row info__numbers-title\">
                    <div class=\"col-3\">{brand_cap}</div>
                    <div class=\"col-9\">{art_cap}</div>
                </div>";
            $i = 1;
            foreach ($arr as $key => $values) {
                $list .= "<div class=\"row info__numbers-row\">
                    <div class=\"col-3 info__numbers-row-auto\">" . $key . "</div>
                    <div class=\"col-9 info__numbers-row-article\">";
                foreach ($values as $value) {
                    $format_value = str_replace(str_split('.,+-\/:*?"<>| '), "", $value);
                    $list .= "<a target=\"_blank\" href=\"" . $this->getSiteLink() . "$this->search_link/$format_value/\">$value</a>";
                    $i++;
                    if ($i <= count($values)) {
                        $list .= ", ";
                    }
                }
                $list .= "</div></div>";
                $i = 1;
            }
            $list .= "</div>";
        } else {
            $list = $this->err1;
        }
        return $this->replaceLang($list);
    }

    /*
     * format text for URL
     * */
    public function formatUrlText($str)
    {
        $format_text = mb_convert_encoding($str, "UTF-8", "Windows-1251");
        $format_text = $this->translit($format_text);
        $format_text = str_replace(str_split('.,+-\/:*?"<>|_'), "", $format_text);
        $format_text = str_replace(" ", "-", $format_text);
        $format_text = str_replace("'", "", $format_text);
        $format_text = mb_strtolower($format_text);
        return $format_text;
    }

    /*
     * Get article default cash
     * table toko_dba.T2_ARTICLES_PRICE_RATING
     * */
    public function getArticlePriceRatingCash($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $cash_id = 2;
        $r = $db->query("SELECT `cash_id` FROM `T2_ARTICLES_PRICE_RATING` WHERE `art_id` = $art_id AND `in_use` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $cash_id = $db->result($r, 0, "cash_id");
        }
        if ($cash_id == 0 || $cash_id == "0") {
            $db->query("UPDATE `T2_ARTICLES_PRICE_RATING` SET `cash_id` = 2 WHERE `art_id` = $art_id AND `in_use` = 1 LIMIT 1;");
            $cash_id = 2;
        }
        return $cash_id;
    }

    /*
     * getPriceRatingKours
     * */
    public function getPriceRatingKours($price, $cash_id_from, $cash_id_to)
    {
        $kours = new ExRateClass();
        $usd_to_uah = $kours->getKours("dollar");
        $eur_to_uah = $kours->getKours("euro");
        if ($cash_id_from == $cash_id_to) {
            $price = $price * 1;
        }
        if ($cash_id_from == 1 && $cash_id_to == 2) {
            $price = $price / $usd_to_uah;
        }
        if ($cash_id_from == 1 && $cash_id_to == 3) {
            $price = $price / $eur_to_uah;
        }
        if ($cash_id_from == 2 && $cash_id_to == 1) {
            $price = $price * $usd_to_uah;
        }
        if ($cash_id_from == 3 && $cash_id_to == 1) {
            $price = $price * $eur_to_uah;
        }
        if ($cash_id_from == 2 && $cash_id_to == 3) {
            $price = $price * $usd_to_uah / $eur_to_uah;
        }
        if ($cash_id_from == 3 && $cash_id_to == 2) {
            $price = $price * $eur_to_uah / $usd_to_uah;
        }
        $price = round($price, 2);
        return $price;
    }

    /*
     * delete article from list with price = 0 OR stock = 0
     * */
    public function deleteEmptyPosition($mas)
    {
        $i = $count_suggest = 0;
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {
                if ($i == 0) {
                    $count_suggest++;
                }
            }
            $i++;
        }
        $i = 0;
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {
                if ($i == 0) {
                    if ($count_suggest > 1) {
                        if ($val["stock"] == 0) {
                            unset($mas[$mas_key][$key]);
                        }
                        elseif ($val["price"] == 0) {
                            unset($mas[$mas_key][$key]);
                        }
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
     * delete position, where suppl_id = 0 AND suppl_id > 0 (same ART_ID)
     * */
    public function deleteSupplPosition($mas)
    {
        $array_toko = [];
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {
                if ($val["suppl_id"] == 0) {
                    array_push($array_toko, $mas_key);
                }
            }
        }
        $array_toko = array_unique($array_toko);
        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {
                if (in_array($mas_key, $array_toko)) {
                    if ($val["suppl_id"] != 0) {
                        unset($mas[$mas_key][$key]);
                    }
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
                $delivery_days  = $val["delivery_days"];
                $delivery_info  = $val["delivery_info"];
                $price          = $val["price"];
                $stock          = $val["stock"];
                if (!empty($uniq)) {
                    foreach ($uniq as $uval) {
                        if ($delivery_days == $uval["delivery_days"] && $delivery_info == $uval["delivery_info"] && $price == $uval["price"]) {
                            if ($stock > $uval["stock"]) {
                                $ukey = intval($uval["key"]);
                            } else {
                                $ukey = $key;
                            }
                            unset($mas[$mas_key][$ukey]);
                            unset($uniq[$key]);
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
                if ($min_key != 0) {
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
                if ($val["price"] != 0) {
                    if ($val["price"] < $min_pr) {
                        $min_pr = $val["price"];
                        $min_key = $key;
                    }
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
    public function showOtherStorages($mas, $cur, $view)
    {
        $currency_cap = $this->getSymbolExrate($cur);
        $ll = $class = $hide = $border = $none = $checkarray = [];
        $i = $j = $double = $preprice = 0;
        $min_price = 9999999;

        foreach ($mas as $mas_key => $mas_val) {
            foreach ($mas_val as $key => $val) {
                $art_id = $mas_key;
                if (in_array($art_id, $checkarray)) {
                    if ($val["price"] < $preprice) {
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
                                    <a id=\"fa-$art_id\" class=\"show_more\" onClick=\"showStorage('$art_id');\">{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> <i class=\"rotate_anime fas fa-chevron-down\"></i></a>
                                    <a id=\"fas-$art_id\" class=\"show_more none\" onClick=\"showStorage('$art_id');\"><span class=\"span-grey\">{collapse_cap}</span> <i class=\"rotate_anime fas fa-chevron-up\"></i></a>
                                </div>";
                            } else {
                                $ll[$i] = "<a href=\"" . $this->getSiteLink() . "$this->search_link/{content_search_number}/{content_brand_link}/\">{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> ></a>";
                            }
                            $hide[$i] = "none";
                            $class[$i] = "$art_id-hide";
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
                                <a id=\"fa-$art_id\" class=\"show_more\" onClick=\"showStorage('$art_id');\">{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> <i class=\"rotate_anime fas fa-chevron-down\"></i></a>
                                <a id=\"fas-$art_id\" class=\"show_more none\" onClick=\"showStorage('$art_id');\"><span class=\"span-grey\">{collapse_cap}</span> <i class=\"rotate_anime fas fa-chevron-up\"></i></a>
                            </div>";
                        } else {
                            $ll[$i] = "<a href=\"" . $this->getSiteLink() . "$this->search_link/{content_search_number}/{content_brand_link}/\">{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> ></i></a>";
                        }
                        $hide[$i] = "none";
                        $class[$i] = "$art_id-hide";
                    }
                    $none[$i] = "dvisibility0";
                    $border[$i] = "border-dashed";
                    $double++;
                } else {
                    $hide[$i]   = "";
                    $none[$i]   = "dvisibility";
                    $border[$i] = "border-line";
                    $checkarray = array();
                    $double     = 0;
                    $preprice   = $val["price"];
                }
                array_push($checkarray, $art_id);
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
     * $status_auto - 0,1,2
     * */
    public function outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view, $saleout = 0, $status_auto = 0, $mfa_id = 0, $model = "")
    {
        $ll     = $other_storages["content"];
        $class  = $other_storages["class"];
        $hide   = $other_storages["hide"];
        $border = $other_storages["border"];
        $none   = $other_storages["none"];

        (!$view) ?: $list .= "<div class=\"row\">";

        $cc = 0;
        if ($view) {
            foreach ($mas as $mas_key => $mas_val) {
                foreach ($mas_val as $key => $val) {
                    if ($cc > 0) {
                        unset($mas[$mas_key][$key]);
                    }
                    $cc++;
                }
                $cc = 0;
            }
        }

        $i = 0;
        $faq_pos = (count($mas) >= $this->faq_card_count) ? $this->faq_card_count : count($mas);
        $faq_socials_pos = (count($mas) >= $this->faq_socials_card_count) ? $this->faq_socials_card_count : count($mas);

        if (!empty($mas)) {
            foreach ($mas as $mas_key => $mas_val) {
                foreach ($mas_val as $key => $val) {
                    $art_id                 = $mas_key;
                    $article_nr_displ       = $val["article_nr_displ"];
                    $brand_id               = $val["brand_id"];
                    $brand_name             = $val["brand_name"];
                    $article_name           = $val["article_name"];
                    $stock                  = $val["stock"];
                    $delivery_info          = $val["delivery_info"];
                    $price                  = $val["price"];
                    $delivery_days          = $val["delivery_days"];
                    $delivery_short_info    = $val["delivery_short_info"];
                    $suppl_id               = $val["suppl_id"];
                    $return_days            = $val["return_days"];
                    $storage_id             = $val["storage_id"];
                    $status                 = ($saleout > 0) ? $val["status"] : 1;
                    if ($status_auto == 0) {
                        if ($view && ($i == $faq_pos)) {
                            $faq_form = $this->getFaqForm();
                            $list .= $faq_form;
                        }
                    }
                    if ($status_auto == 0) {
                        if ($view && ($i == $faq_socials_pos)) {
                            $faq_socials_form = $this->getFaqSocialsForm();
                            $list .= $faq_socials_form;
                        }
                    }
                    $list .= $this->printSearchList($i, $art_id, $article_nr_displ, $brand_id, $brand_name, $article_name, $delivery_info, $stock, $price, $article_nr_search, $ll[$i], $class[$i], $hide[$i], $border[$i], $none[$i], $brand_nr_search, $suppl_id, $return_days, $delivery_days, $delivery_short_info, $storage_id, $status, $view, $status_auto, $mfa_id, $model);
                    $i++;
                }
            }
            $list .= "</div>";
        } else {
            $list = "$error";
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
        $r = $db->query("SELECT `TEX_$postfix` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");
        return $db->result($r, 0, "TEX_$postfix");
    }

    public function getHeadRowLink($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");
        return $db->result($r, 0, "TEX_LINK");
    }

    public function getHeadRowImage($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `IMAGES` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");
        return $db->result($r, 0, "IMAGES");
    }

    public function getCatRowName($cat_id)
    {
        $db = DbSingleton::getTokoDb();
        if ($cat_id == 0) {
            $text_txt = $this->replaceLang("{popular_goods_cap}");
        } else {
            $postfix = $this->getLangPostfix($this->getLanguage());
            $r = $db->query("SELECT `TEX_$postfix` FROM `T2_TREE_CAT_EXIST` WHERE `CAT_ID` = $cat_id LIMIT 1;");
            $text_txt = $db->result($r, 0, "TEX_$postfix");
        }
        return $text_txt;
    }

    public function getCatRowLink($cat_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_CAT_EXIST` WHERE `CAT_ID` = $cat_id LIMIT 1;");
        return $db->result($r, 0, "TEX_LINK");
    }

    public function getGroupRowName($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `TEX_$postfix`, `H1_$postfix` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        return ($db->result($r, 0, "H1_$postfix") == "")
            ? $db->result($r, 0, "TEX_$postfix")
            : $db->result($r, 0, "H1_$postfix");
    }

    public function getGroupRowText($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `TEX_$postfix` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        return $db->result($r, 0, "TEX_$postfix");
    }

    public function getGroupRowStatusAuto($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `STATUS_AUTO` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        return $db->result($r, 0, "STATUS_AUTO");
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

    public function getHeadRowText($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $cats = [];
        $r = $db->query("SELECT `CAT_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id GROUP BY `CAT_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id = $db->result($r, $i - 1, "CAT_ID");
            $cat_name = $this->getCatRowName($cat_id);
            if ($cat_id > 0) {
                array_push($cats, $cat_name);
            }
        }
        $cats = implode(", ", $cats);
        return $cats;
    }

    public function getMaxPosition($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT MAX(`COL`) as max_col FROM `T2_TREE_CONSTRUCTOR_STR` WHERE `HEAD_ID` = $head_id;");
        return $db->result($r, 0, "max_col") + 1;
    }

    /*
     * Tree GRID Headers
     * */
    public function getCatalogColList($mfa_link = "", $model_link = "", $heads = [], $cats = [], $groups = [], $brand_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $nophoto = $this->noPhoto;
        $list = "";
        $where = "1";
        if (!empty($heads)) {
            $heads_str = implode(",", $heads);
            $where = "`HEAD_ID` IN ($heads_str)";
        }
        $brand_name = "";
        if ($brand_id > 0) {
            $brand_name = $this->getBrandName($brand_id);
        }
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_CONSTRUCTOR` WHERE $where ORDER BY `POSITION` ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $head_id = $db->result($r, $i - 1, "HEAD_ID");
                $head_name = $this->getHeadRowName($head_id);
                $head_img = $this->getHeadRowImage($head_id);
                $head_text = $this->getHeadRowText($head_id);
                $head_content = $this->getCatalogColListCat($head_id, $mfa_link, $model_link, $cats, $groups, $brand_id);

                $list .= "
                <div class=\"tree-heads__item\">
                    <input type=\"checkbox\" id=\"toggle-head-$head_id\">
                    <label for=\"toggle-head-$head_id\">
                        <div id=\"tree_head-$head_id\" class=\"tree-heads__item-header\">
                            <div class=\"tree-heads__item-text\">
                                <div class=\"tree-heads__item-title\">
                                    $head_name $brand_name
                                </div>
                                <div class=\"tree-heads__item-descr\">
                                    $head_text
                                </div>
                            </div>
                            <div class=\"tree-heads__item-image\">
                                <img data-src=\"/uploads/images/group_tree_head/$head_img\" class=\"lazy\" alt=\"$head_name\" src=\"$nophoto\">
                            </div>
                        </div>
                    </label>
                    <div class=\"tree-cat\" style=\"display: none;\">
                        $head_content
                    </div>
                </div>";
            }
        }
        return $list;
    }

    /*
     * Tree GRID Categories
     * */
    public function getCatalogColListCat($head_id, $mfa_link = "", $model_link = "", $cats = [], $groups = [], $brand_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $head_link = $this->getHeadRowLink($head_id);
        $where = "1";
        if (!empty($cats)) {
            $cats_str = implode(",", $cats);
            $where = "`CAT_ID` IN ($cats_str)";
        }
        $brand_name = "";
        if ($brand_id > 0) {
            $brand_name = $this->getBrandName($brand_id);
        }
        $r = $db->query("SELECT `CAT_ID` FROM `T2_TREE_CONSTRUCTOR_STR` WHERE `HEAD_ID` = $head_id AND $where ORDER BY `COL` ASC, `ROW` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id = $db->result($r, $i - 1, "CAT_ID");
            $cat_name = $this->getCatRowName($cat_id);
            $cat_link = $this->getCatRowLink($cat_id);
            $group_list = $this->getCatalogColListGroup($head_id, $cat_id, $mfa_link, $model_link, $groups, $brand_id);
            $icon = "";
            $link = "<a href=\"" . $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/\">$icon $cat_name $brand_name</a>";
            if ($cat_id == 0) {
                $icon = "<span style=\"color: #f44438; margin-right: 5px;\">&bull;</span>";
                $link = "<span>$icon $cat_name $brand_name</span>";
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

    /*
  * check exist of group params table
  * */
    public function checkTable($group_id)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    /*
     * Tree GRID Groups
     * */
    public function getCatalogColListGroup($head_id, $cat_id, $mfa_link = "", $model_link = "", $groups_sel = [], $brand_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $groups = [];
        $where_1 = "1";
        $where_2 = "1";
        if (!empty($groups_sel)) {
            $groups_sel_str = implode(",", $groups_sel);
            $where_1 = "t2hcg.`GROUP_ID` IN ($groups_sel_str)";
            $where_2 = "`GROUP_ID` IN ($groups_sel_str)";
        }
        $r = $db->query("SELECT t2hcg.`GROUP_ID`
        FROM `T2_TREE_HCG_EXIST` t2hcg
            LEFT JOIN `T2_TREE_GROUP_EXIST` t2g ON (t2g.GROUP_ID = t2hcg.GROUP_ID)
        WHERE t2hcg.`HEAD_ID` = $head_id AND t2hcg.`CAT_ID` = $cat_id AND t2g.`STATUS` = 1 AND $where_1;");
        $n = $db->num_rows($r);
        $gg = [];
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $dbc->result($r, $i - 1, "GROUP_ID");
            $gg[] = $group_id;
        }

        if ($cat_id == 0) {
            $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id AND `POPULAR` = 1 AND $where_2;");
            $n = $db->num_rows($r);
            $gg = [];
            for ($i = 1; $i <= $n; $i++) {
                $group_id = $dbc->result($r, $i - 1, "GROUP_ID");
                $gg[] = $group_id;
            }
        }

        $where_gg = "1";
        if (!empty($gg)) {
            $where_gg = "`group_id` IN (" . implode(",", $gg) . ")";
        }

        $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE $where_gg GROUP BY `group_id`;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $dbc->result($r, $i - 1, "group_id");
            $group_name = $this->getGroupRowText($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $group_image = $this->getGroupRowImage($group_id);
            $status_auto = $this->getGroupRowStatusAuto($group_id);
            $groups[] = compact("group_name", "group_link", "group_image", "status_auto");
        }

        return $this->getCatalogColListGroupList($groups, $mfa_link, $model_link, $brand_id);
    }

    public function getCatalogColListGroupList($groups, $mfa_link = "", $model_link = "", $brand_id = 0)
    {
        $brand_name = "";
        if ($brand_id > 0) {
            $brand_name = $this->getBrandName($brand_id);
        }
        $list = "";
        foreach ($groups as $value) {
            $group_name = $value["group_name"];
            $group_link = $value["group_link"];
            $group_image = $value["group_image"];
            $status_auto = $value["status_auto"];
            $link = "";

            if ($status_auto != 2) {
                if ($mfa_link != "") {
                    $link .= "auto/$mfa_link/";
                    if ($model_link != "") {
                        $link .= "$model_link/";
                    }
                }
            }

            if ($brand_id > 0) {
                $brand_link = $this->getBrandLink($brand_id);
                $link .= "brandy=$brand_link/";
            }
            $list .= "
            <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/$link\" class=\"tree-group__item\">
                <div class=\"tree-group__item-image\">
                    <img data-src=\"/images/tree-group/$group_image\" class=\"lazy\" alt=\"$group_name\">
                </div>
                <div class=\"tree-group__item-text\">
                    <span>$group_name $brand_name</span>
                </div>
            </a>";
        }
        return $list;
    }

    /*
     * Tree List Headers
     * */
    public function getSiteNavigation()
    {
        $db = DbSingleton::getTokoDb();
        $form = $this->getHtmlForm("main/navigation");
        $list = "";
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_CONSTRUCTOR` WHERE 1 ORDER BY `POSITION` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $head_name = $this->getHeadRowName($head_id);
            $head_link = $this->getHeadRowLink($head_id);
            $list .= "
            <li class=\"header-nav__li\" data-nav-id=\"$head_id\">
                <a rel=\"noopener\" href=\"" . $this->getSiteLink() . "$this->catalog_link/$head_link/\">$head_name</a>
            </li>";
        }
        $form = str_replace("{catalog_range}", $list, $form);
        return $form;
    }

    /*
     * Show Tree List Categories
     * */
    public function getHeaderContent($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $form = $this->getHtmlForm("catalog_menu/list");
        $list = "";
        $arr = [];
        $r = $db->query("SELECT `CAT_ID`, `COL`, `ROW` FROM `T2_TREE_CONSTRUCTOR_STR` WHERE `HEAD_ID` = $head_id ORDER BY `COL` ASC, `ROW` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id = $db->result($r, $i - 1, "CAT_ID");
            $col = $db->result($r, $i - 1, "COL");
            $row = $db->result($r, $i - 1, "ROW");
            $arr[$col][$row] = $cat_id;
        }
        $max_col = $this->getMaxPosition($head_id);
        if ($n > 0) {
            $list = "
            <div class=\"tree-block\">";
            foreach ($arr as $col_id => $rows) {
                $list .= "
                <div class=\"tree-block__col\" style=\"width: calc(100% / $max_col);\">";
                foreach ($rows as $row_id => $cat_id) {
                    $cat_name = $this->getCatRowName($cat_id);
                    $group_list = $this->getTreeConsGroupList($head_id, $cat_id);
                    $head_link = $this->getHeadRowLink($head_id);
                    $cat_link = $this->getCatRowLink($cat_id);
                    $href = $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/";
                    $icon = "";
                    if ($cat_id == 0) {
                        $href = $this->getSiteLink();
                        $icon = "<span style=\"margin-right: 5px; color: #f44438;\">&bull;</span>";
                    }
                    $list .= "
                    <div>
                        <div class=\"tree-item\">
                            <div class=\"tree-item-title\">
                                <a href=\"$href\">$icon$cat_name</a>
                            </div>
                            <div class=\"tree-item-list\">$group_list</div>
                        </div>
                    </div>";
                }
                $list .= "</div>";
            }
            $list .= "</div>";
        }
        $form = str_replace("{content_range}", $list, $form);
        return $form;
    }

    /*
     * Tree List Groups
     * */
    public function getTreeConsGroupList($head_id, $cat_id, $mfa_link = "", $model_link = "")
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id AND `CAT_ID` = $cat_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $db->result($r, $i - 1, "GROUP_ID");
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $link = "";
            if ($mfa_link != "") {
                $link .= "auto/$mfa_link/";
                if ($model_link != "") {
                    $link .= "$model_link/";
                }
            }
            $list .= "
            <div class=\"tree-item-list__element\">
                <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/$link\">$group_name</a>
            </div>";
        }
        if ($cat_id == 0) {
            $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID` = $head_id AND `POPULAR` = 1;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $group_id = $db->result($r, $i - 1, "GROUP_ID");
                $group_name = $this->getGroupRowName($group_id);
                $group_link = $this->getGroupRowLink($group_id);
                $link = "";
                if ($mfa_link != "") {
                    $link .= "auto/$mfa_link/";
                    if ($model_link != "") {
                        $link .= "$model_link/";
                    }
                }
                $list .= "
                <div class=\"tree-item-list__element\">
                    <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/$link\">$group_name</a>
                </div>";
            }
        }
        return $list;
    }

    // get catalog links
    public function getGroupsList()
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT `GROUP_ID`, `TEX_RU` FROM `T2_TREE_GROUP_EXIST` WHERE `STATUS` = 1 ORDER BY `TEX_RU` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $db->result($r, $i - 1, "GROUP_ID");
            $group_name = $db->result($r, $i - 1, "TEX_RU");
            $list .= "<option value=\"$group_id\">$group_name</option>";
        }
        return $list;
    }

    // get catalog links VALUES
    public function getGroupsListValues($group_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $list = "<option value='0'>-не вибрано-</option>";
        $r = $db->query("SELECT `VALUE_ID`, `VALUE_NAME`, `PARAM_ID` FROM `T2_TREE_VALUE_EXIST` WHERE `GROUP_ID` = $group_id ORDER BY `VALUE_NAME` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $value_id = $db->result($r, $i - 1, "VALUE_ID");
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $value_name = $db->result($r, $i - 1, "VALUE_NAME");
            $list .= "<option value=\"$value_id\" data-value-param=\"$param_id\">$value_name</option>";
        }
        return $list;
    }

    public function checkSeoText($router, $link)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `CONTENT_RU` FROM `T2_SEO_TEXT` WHERE `ROUTER` = '$router' AND `LINK` = '$link' LIMIT 1;");
        $content_ru = $db->result($r, 0, "CONTENT_RU");
        return ($content_ru != "");
    }

    public function getGroupsLinks($group_id = 0, $param_id = 0, $value_id = 0)
    {
        $group_link = $this->getGroupRowLink($group_id);
        $list = "";
        $count = 0;
        $dbc = DbSingleton::getTokoCacheDb();
        if ($group_id > 0) {
            $list = "
            <table class='table'>";
            $list .= "
            <thead><tr>
                <th>#</th>
                <th>link</th>
                <th>count</th>
                <th>seo</th>
            </tr></thead><tbody>";
            if ($value_id == 0) {
                $r = $dbc->query("SELECT `mfa_id`, `model`, COUNT(`art_id`) as count_arts  FROM `EX_TABLE_TREE_MFA_$group_id` WHERE 1 GROUP BY `mfa_id`, `model`;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $mfa_id = $dbc->result($r, $i - 1, "mfa_id");
                    $model = $dbc->result($r, $i - 1, "model");
                    $count_arts = $dbc->result($r, $i - 1, "count_arts");
                    if ($mfa_id > 0) {
                        $mfa_link = $this->getManufactureLink($mfa_id);
                        $model_link = $this->getModelLink($model);
                        $link_catalog = "$group_link/auto/$mfa_link/$model_link";
                        $link = "https://toko.ua/catalog/$link_catalog/";
                        $seo_status = intval($this->checkSeoText("catalog", $link_catalog));
                        $count++;
                        $list .= "
                        <tr>
                            <td>$count</td>
                            <td>$link</td>
                            <td>$count_arts</td>
                            <td>$seo_status</td>
                        </tr>";
                    }
                }
            } else {
                $param_link = $this->getParamLink($param_id);
                $value_link = $this->getValueLink($value_id);

                $r = $dbc->query("SELECT COUNT(tm.`art_id`) as count_arts 
                FROM `EX_TABLE_TREE_PARAMS_$group_id` tp
                    LEFT JOIN `EX_TABLE_TREE_$group_id` tm ON (tm.art_id = tp.art_id)
                WHERE (tp.`param_$param_id` = '$value_id' OR tp.`param_$param_id` LIKE '%,$value_id%' OR tp.`param_$param_id` LIKE '%$value_id,%');");
                $count_arts = $dbc->result($r, 0, "count_arts");
                $link_catalog = "$group_link/$param_link=$value_link";
                $link = "https://toko.ua/catalog/$link_catalog/";
                $seo_status = intval($this->checkSeoText("catalog", $link));
                $count++;
                $list .= "    
                <tr>
                    <td>$count</td>
                    <td>$link</td>
                    <td>$count_arts</td>
                    <td>$seo_status</td>
                </tr>";

                $r = $dbc->query("SELECT tm.`mfa_id`, COUNT(tm.`art_id`) as count_arts 
                FROM `EX_TABLE_TREE_PARAMS_$group_id` tp
                    LEFT JOIN `EX_TABLE_TREE_MFA_$group_id` tm ON (tm.art_id = tp.art_id)
                WHERE (tp.`param_$param_id` = '$value_id' OR tp.`param_$param_id` LIKE '%,$value_id%' OR tp.`param_$param_id` LIKE '%$value_id,%')
                GROUP BY tm.`mfa_id`;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $mfa_id = $dbc->result($r, $i - 1, "mfa_id");
                    $count_arts = $dbc->result($r, $i - 1, "count_arts");
                    if ($mfa_id > 0) {
                        $mfa_link = $this->getManufactureLink($mfa_id);
                        $link_catalog = "$group_link/$param_link=$value_link/$mfa_link";
                        $link = "https://toko.ua/catalog/$link_catalog/";
                        $seo_status = intval($this->checkSeoText("catalog", $link));
                        $count++;
                        $list .= "
                        <tr>
                            <td>$count</td>
                            <td>$link</td>
                            <td>$count_arts</td>
                            <td>$seo_status</td>
                        </tr>";
                    }
                }

                $r = $dbc->query("SELECT tm.`mfa_id`, tm.`model`, COUNT(tm.`art_id`) as count_arts 
                FROM `EX_TABLE_TREE_PARAMS_$group_id` tp
                    LEFT JOIN `EX_TABLE_TREE_MFA_$group_id` tm ON (tm.art_id = tp.art_id)
                WHERE (tp.`param_$param_id` = '$value_id' OR tp.`param_$param_id` LIKE '%,$value_id%' OR tp.`param_$param_id` LIKE '%$value_id,%')
                GROUP BY tm.`mfa_id`, tm.`model`;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $mfa_id = $dbc->result($r, $i - 1, "mfa_id");
                    $model = $dbc->result($r, $i - 1, "model");
                    $count_arts = $dbc->result($r, $i - 1, "count_arts");
                    if ($mfa_id > 0) {
                        $mfa_link = $this->getManufactureLink($mfa_id);
                        $model_link = $this->getModelLink($model);
                        $link_catalog = "$group_link/$param_link=$value_link/$mfa_link/$model_link";
                        $link = "https://toko.ua/catalog/$link_catalog/";
                        $seo_status = intval($this->checkSeoText("catalog", $link));
                        $count++;
                        $list .= "
                        <tr>
                            <td>$count</td>
                            <td>$link</td>
                            <td>$count_arts</td>
                            <td>$seo_status</td>
                        </tr>";
                    }
                }
            }

            if ($count == 0) {
                $list .= "
                <tr><td colspan='4'>" . $this->replaceLang('{nothing_found}') . "</td></tr>";
            }

            $list .= "
            </tbody></table>";
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
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_name = $db->result($r, 0, "VALUE_LINK");
        }
        return $value_name;
    }

    /*
     * export price list for client
     * */
    public function getPriceList($user_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $kours = new ExRateClass();
        $client_id = $client->getClientByUser($user_id);
        $tpoint_user_id = $client->getTpointUser($client_id);
        $cur = $client->getClientCurrency($client_id);
        $cur_cap = $kours->getKoursCaption($cur);
        $list = $storages = [];
        $filials_list = ["#", "{art_cap}", "{brand_cap}", "{caption_cap}", "{price_cap}", "{currency}", "{descrip_cap}", "{barcode_cap}"];

        $tpoints = $client->getOtherTpoints($tpoint_user_id);
        foreach ($tpoints as $tpoint) {
            list($storage_local_alien, $storage_remote_alien) = $client->getStorageByTpoint($tpoint);
            $storage_cap = ($tpoint == $tpoint_user_id) ? "{your_affiliate} -" : "";

            $city_local = $client->getTPointCity($tpoint);
            $city_remote = $client->getStorageCity($storage_remote_alien);

            $address_local = $client->getTPointAddress($tpoint);
            $address_remote = $client->getStorageAddress($storage_remote_alien);

            if ($storage_local_alien != "") {
                array_push($filials_list, "$storage_cap $city_local ($address_local) ({local_storage})");
                array_push($storages, $storage_local_alien);
            }
            if ($storage_remote_alien != "") {
                array_push($filials_list, "$storage_cap $city_remote ($address_remote) ({remote_storage})");
                array_push($storages, $storage_remote_alien);
            }
        }

        $list[0] = $filials_list;
        $list[0] = $this->replaceLang($list[0]);

        $r = $db->query("SELECT t2as.ART_ID, t2as.STORAGE_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2br.BARCODE 
        FROM `T2_ARTICLES_STRORAGE` t2as
            LEFT OUTER JOIN `T2_ARTICLES` t2a ON (t2a.ART_ID = t2as.ART_ID)
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2a.BRAND_ID)
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2a.ART_ID)
            LEFT OUTER JOIN `T2_BARCODES` t2br ON (t2br.ART_ID = t2a.ART_ID)
        WHERE t2as.AMOUNT != 0 AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END)
        GROUP BY t2a.ARTICLE_NR_DISPL;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
            $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
            $article_name = $db->result($r, $i - 1, "NAME");
            $info = $db->result($r, $i - 1, "INFO");
            $barcode = $db->result($r, $i - 1, "BARCODE");
            $info = trim($info, " ");
            $info = trim($info, "\n");
            $info = trim($info, "\r");
            $info = str_replace("\n", "", $info);
            $info = str_replace("\r", "", $info);

            $price = $this->getArticlePriceClient($art_id, $client_id, $cur);
            $price = str_replace(".", ",", "$price");

            $rs = $db->query("SELECT COUNT(`ART_ID`) as count_arts FROM `T2_ARTICLES_NOT_EXPORT` WHERE `ART_ID` = $art_id LIMIT 1;");
            $ns = $db->result($rs, 0, "count_arts");
            if ($ns == 0) {
                $list[$i] = [$i, "$article_nr_displ", "$brand_name", "$article_name", "$price", "$cur_cap", "$info", "$barcode"];
                foreach ($storages as $storage) {
                    $stock = $this->getStockStorage($art_id, $storage);
                    if ($stock > 10) {
                        $stock = ">10";
                    }
                    array_push($list[$i], $stock);
                }
            }
        }
        return $list;
    }

    /*
     * download prices
     * */
    public function downloadPrices()
    {
        $db = DbSingleton::getTokoDb();
        $dbm = DbSingleton::getDbm();

        $r = $dbm->query("SELECT `user_id`, `date`, `filename` FROM `cron_task_prices` WHERE `status` = 1;");
        $n = $dbm->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $user = $db->result($r, $i - 1, "user_id");
                $filename = $user . "/" . $dbm->result($r, $i - 1, "filename");

                $csv = "";
                $list = $this->getPriceList();
                foreach ($list as $record) {
                    foreach ($record as $rec) {
                        $csv .= $rec . ';';
                    }
                    $csv .= "\n";
                }

                if (!file_exists(RDD . "/uploads/$user")) {
                    mkdir(RDD . "/uploads/$user", 0777, true);
                }
                elseif (file_exists(RDD . "/uploads/$user/")) {
                    foreach (glob(RDD . "/uploads/$user/*") as $file) {
                        unlink($file);
                    }
                }

                $csv_handler = fopen(RDD . "/uploads/$filename", 'w') or die("Can't create file");
                fwrite($csv_handler, $csv);
                fclose($csv_handler);
                $date_end = date("Y-m-d H:i:s");
                $dbm->query("UPDATE `cron_task_prices` SET `status` = 2, `date_end` = '$date_end' WHERE `user_id` = '$user' AND `status` = 1;");
            }
        }
        return true;
    }

    public function getSlideProPhoto($art_id, $brand_id, $h1)
    {
        $db = DbSingleton::getTokoDb();
        $status = 0;
        $slide = "";
        $thumbnail = "";
        $arr = [];
        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` 
        WHERE `ART_ID` = $art_id AND `ACTIVE` = 1 ORDER BY `MAIN` DESC, `ID` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $photo = $db->result($r, $i - 1, "PHOTO_NAME");
            if ($photo != "") {
                $arr[] = ["type" => "catalogue", "photo" => $photo];
            }
        }

        $client = new ClientClass();
        $nn = 0;
        if ($client->checkRetailClientCategory($this->getClient()) && $brand_id > 0) {
            $date_cur = date("Y-m-d");
            $r = $db->query("SELECT `photo_link` FROM `T2_CERTIFICATES` 
            WHERE `brand_id` = $brand_id AND `date_from` <= '$date_cur' AND `date_to` >= '$date_cur' AND `status` = 1;");
            $nn = $db->num_rows($r);
            for ($i = 1; $i <= $nn; $i++) {
                $photo = $db->result($r, $i - 1, "photo_link");
                $arr[] = ["type" => "certificates", "photo" => $photo];
            }
        }

        if ($n > 0 || $nn > 0) {
            $i = 0;
            foreach ($arr as $value) {
                $i++;
                $photo = $value["photo"];
                $type = $value["type"];
                $slide .= "
                <div class=\"sp-slide\">
                    <img class=\"sp-image\" 
                        src=\"https://toko.ua/resize_image.php?image=$photo&w=633&h=0&type=$type\"
                        alt=\"$h1 - {photo_card_cap} #$i\"
                        title=\"$h1 - {photo_card_cap} #$i\"/>
                </div>";
                $thumbnail .= "
                <div class=\"sp-thumbnail\">
                    <div class=\"sp-thumbnail-image-container\">
                        <img class=\"sp-image sp-thumbnail-image\" 
                            src=\"https://toko.ua/resize_image.php?image=$photo&w=100&h=80&type=$type\"
                            alt=\"$h1 - {photo_card_cap} #$i\"/>
                    </div>
                </div>";
                $status = 1;
            }
        } else {
            $slide = "";
            $thumbnail = "";
            $status = 0;
        }
        return array(
            "slide"     => $slide,
            "thumbnail" => $thumbnail,
            "status"    => $status
        );
    }

    public function checkMfa($mfa_link)
    {
        $mfa_id = 0;
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' AND `ACTIVE` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $mfa_id = $db->result($r, 0, "MFA_ID");
        }
        return $mfa_id;
    }

    public function checkModel($mfa_id, $model_link)
    {
        $model = "";
        if ($mfa_id > 0) {
            $db = DbSingleton::getTokoDb();
            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link` = '$model_link' AND `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $model = $db->result($r, 0, "Model");
            }
        }
        return $model;
    }

//    public function testLinks()
//    {
//        $db = DbSingleton::getTokoDb();
//        $r = $db->query("SELECT `LINK` FROM `T_TEST_LINKS`;");
//        $n = $db->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $link = $db->result($r, $i - 1, "LINK");
//            $arr = explode("/", $link);
//
//            $status = 0;
//            $router = $arr[1];
//
//            if ($router == "") {
//                $status = 1;
//            }
//
//            if ($router == "cars") {
//                $mfa_link = $arr[2]; $mfa_id = 0;
//                $model_link = $arr[3]; $model = "";
//                if ($mfa_link != "") {
//                    $mfa_id = $this->checkMfa($mfa_link);
//                    if ($model_link != "") {
//                        $model = $this->checkModel($mfa_id, $model_link);
//                    }
//                }
//                if ($mfa_link != "" && $model_link == "") {
//                    if ($mfa_id > 0) {
//                        $status = 1;
//                    } else {
//                        $status = 0;
//                    }
//                }
//                elseif ($mfa_link != "" && $model_link != "") {
//                    if ($model != "") {
//                        $status = 1;
//                    } else {
//                        $status = 0;
//                    }
//                }
//
//            }
//
//            $db->query("UPDATE `T_TEST_LINKS` SET `STATUS` = $status WHERE `LINK` = '$link' LIMIT 1;");
//        }
//        return 0;
//    }

    /*
     * type_id = 1 : group_id
     * type_id = 2 : cat_id
     * type_id = 3 : head_id
     * */
//    public function initKeywords()
//    {
//        $db = DbSingleton::getTokoDb();
//        $r = $db->query("SELECT `HEAD_ID`, `TEX_RU`, `TEX_UA` FROM `T2_TREE_HEAD_EXIST` WHERE `STATUS` = 1;");
//        $n = $db->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $group_id = $db->result($r, $i - 1, "HEAD_ID");
//            $text_ru = $db->result($r, $i - 1, "TEX_RU");
//            $text_ua = $db->result($r, $i - 1, "TEX_UA");
//            $db->query("INSERT INTO `T2_TREE_KEYWORDS` (`KEY_ID`, `TYPE_ID`, `KEYWORD`) VALUES ($group_id, 3, \"$text_ru\");");
//            $db->query("INSERT INTO `T2_TREE_KEYWORDS` (`KEY_ID`, `TYPE_ID`, `KEYWORD`) VALUES ($group_id, 3, \"$text_ua\");");
//        }
//        return true;
//    }

    public function getHeadCatRow($cat_id)
    {
        $head_id = 0;
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HCG_EXIST` WHERE `CAT_ID` = $cat_id AND `HEAD_ID` != 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_id = $db->result($r, 0, "HEAD_ID");
        }
        return $head_id;
    }

    public function getSearchMatches($text)
    {
        $text = mb_strtolower($text, 'windows-1251');
        $max_word = 4;
        $arr = [];

        if ($text != "") {
            $text_arr = explode(" ", $text);
            $i = 0;
            foreach ($text_arr as $value) {
                $i++;
                if (mb_strlen($value) > 1) {
                    $arr[$i][] = $value;
                    if (mb_strlen($value) > $max_word) {
                        $format_value = substr($value, 0, mb_strlen($value) - 2);
                        $arr[$i][] = $format_value;
                    }
                }
            }
        }

        $db = DbSingleton::getTokoDb();
        $new = $m = [];

        foreach ($arr as $key => $values) {
            foreach ($values as $value) {
                $r = $db->query("
                SELECT `ID`, `KEY_ID`, `TYPE_ID`, `KEYWORD`, substrCount(LOWER(`KEYWORD`), '$value') as str_count 
                FROM `T2_TREE_KEYWORDS` 
                WHERE `ID` IN (
                    SELECT `ID` FROM `T2_TREE_KEYWORDS` GROUP BY `KEY_ID`, `TYPE_ID`
                ) GROUP BY `ID` HAVING str_count > 0;");
                $n = $db->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $key_id = $db->result($r, $i - 1, "KEY_ID");
                    $type_id = $db->result($r, $i - 1, "TYPE_ID");
                    $str_count = $db->result($r, $i - 1, "str_count");
                    $k = $key_id . "_" . $type_id;
                    if (!array_key_exists($k, $new)) {
                        $new[$k] = ["key_id" => $key_id, "type_id" => $type_id, "str_count" => $str_count];
                    } else {
                        $new[$k]["str_count"] += $str_count;
                    }
                    if (!in_array($key, $new[$k]["key"])) {
                        $new[$k]["key"][] = $key;
                        $m[] = $key;
                    }
                }
            }
        }

        $max_matches = count(array_unique($m));
        uasort($new, "keywordCmp");

        return array($new, $max_matches);
    }

    public function showSearchDropdown($text)
    {
        $db = DbSingleton::getTokoDb();
        $showform = new FormClass();
        $list = $list1 = $list2 = $list3 = "";
        if ($text == "") {
            $list = $showform->showHistoryList();
        }

        if ($text != "" && mb_strlen($text) > 1) {
            $text = $this->getUrlString($text);
            $format_text = $text;
            $format_text = str_replace(str_split(' -,+\/:*?"<>|_'), "", $format_text);

            $r = $db->query("SELECT `ART_ID`, `BRAND_ID`, `DISPLAY_NR`, MIN(`KIND`) as min_kind 
            FROM `T2_CROSS` 
            WHERE `SEARCH_NUMBER` = '$format_text' 
            GROUP BY `BRAND_ID` 
            ORDER BY `min_kind`;");
            $n1 = $db->num_rows($r);
            for ($i = 1; $i <= $n1; $i++) {
                $art_id = $db->result($r, $i - 1, "ART_ID");
                $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                $min_kind = $db->result($r, $i - 1, "min_kind");
                $display_nr = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_name = $this->getBrandName($brand_id);
                $brand_link = $this->getBrandLink($brand_id);
                if ($min_kind == "0") {
                    $format_name = $this->getFormatAticle($display_nr);
                    $article_name = $this->getArticleName($art_id);
                    $link = $this->getSiteLink() . $this->search_link . "/" . $format_name . "/" . $brand_link . "/";
                    $str = "$brand_name $display_nr $article_name";
                } else {
                    $format_name = $this->getFormatAticle($display_nr);
                    $link = $this->getSiteLink() . $this->search_link . "/" . $format_name . "/" . $brand_link . "/";
                    $str = "$brand_name $display_nr";
                }
                $list1 .= "<li>
                    <a href='$link'>$str </a>
                </li>";
            }

            $text = str_replace(str_split('+\/:*?"<>|'), "", $text);
            list($arr, $max_matches) = $this->getSearchMatches($text);

            $n = count($arr);
            foreach ($arr as $value) {
                $key_id = $value["key_id"];
                $type_id = $value["type_id"];
                $key = $value["key"];

                if (count($key) >= $max_matches) {
                    if ($type_id == 1) {
                        $key_name = $this->getGroupRowText($key_id);
                        $key_link = $this->getGroupRowLink($key_id);
                        $link = $this->getSiteLink() . $this->catalog_link . "/" . $key_link . "/";
                        $list2 .= "<li>
                            <a href='$link'>$key_name</a>
                        <li>";
                    }
                    elseif ($type_id == 2) {
                        $key_name = $this->getCatRowName($key_id);
                        $key_link = $this->getCatRowLink($key_id);
                        $head_id = $this->getHeadCatRow($key_id);
                        $head_link = $this->getHeadRowLink($head_id);
                        $link = $this->getSiteLink() . $this->catalog_link . "/" . $head_link . "/" . $key_link . "/";
                        $list3 .= "<li>
                            <a href='$link'>$key_name</a>
                        <li>";
                    }
                    elseif ($type_id == 3) {
                        $key_name = $this->getHeadRowName($key_id);
                        $key_link = $this->getHeadRowLink($key_id);
                        $link = $this->getSiteLink() . $this->catalog_link . "/" . $key_link . "/";
                        $list3 .= "<li>
                            <a href='$link'>$key_name</a>
                        <li>";
                    }
                }
            }
            if ($n > 0 || $n1 > 0) {
                if ($n1 > 0) {
                    $list .= "
                     <div class='search-block'>
                        <div class='search-block-header'>
                            <img src='/images/icons/search/number_result.svg' alt='number'>
                            <span class='search-block-header__item'>{search_accurate}</span>
                        </div>
                        <ul class='search-block-content'>
                            $list1
                        </ul>
                    </div>";
                }
                if ($n > 0) {
                    if ($list2 != "") {
                        $list .= "
                        <div class='search-block'>
                            <div class='search-block-header'>
                                <img src='/images/icons/search/categories_result.svg' alt='groups'>   
                                <span class='search-block-header__item'>{category_cap}</span>
                            </div>
                            <ul class='search-block-content'>
                                $list2
                            </ul>
                        </div>";
                    }
                    if ($list3 != "") {
                        $list .= "
                        <div class='search-block'>
                            <div class='search-block-header'>
                                <img src='/images/icons/search/groups_result.svg' alt='categories'>   
                                <span class='search-block-header__item'>{sections_groups}</span>
                            </div>
                            <ul class='search-block-content'>
                                $list3
                            </ul>
                        </div>";
                    }
                }
            } else {
                $list = "";
            }
        }

        return $this->replaceLang($list);
    }

}

function keywordCmp($a, $b) {
    if ($a["str_count"] == $b["str_count"]) return 0;
    return $a["str_count"] < $b["str_count"] ? 1 : -1;
}

function myBrandCmp($a, $b) {
   if ($a["count"] == $b["count"]) return 0;
   return $a["count"] < $b["count"] ? 1 : -1;
}

function cmpPrice($a, $b) {
    if (floatval($a["price"]) == floatval($b["price"])) return 0;
    return floatval($a["price"]) > floatval($b["price"]) ? 1 : -1;
}
