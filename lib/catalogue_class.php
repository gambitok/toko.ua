<?php

class CatalogueClass
{

    use Helper;
    use Variables;

    public $products_link = "products";
    public $catalog_link = "catalog";
    public $search_link = "search";
    public $faq_card_count = 2;

    /*
     * get catalog search form
     * */
    public function getSearchList($article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $article_search = "";
        $brand_id = 0;
        $article_nr_search = $this->getUrlString($article_nr_search);
        $r = $db->query("SELECT `SEARCH_NUMBER`, `BRAND_ID` FROM `T2_CROSS` WHERE `SEARCH_NUMBER`='$article_nr_search' GROUP BY `BRAND_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $article_search = $db->result($r, $i - 1, "SEARCH_NUMBER");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
        }
        if ($n == 1) {
            return $this->getCatalogList($article_search, $brand_id);
        } else {
            return $this->getBrandList($article_search, $article_nr_search);
        }
    }

    /*
     * get catalog search List
     * */
    public function getCatalogList($article_nr_search, $brand_nr_search, $status_brand = 0)
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $client->insertHistory($article_nr_search, $brand_nr_search);
        $client->toggleProductView(0);
        $cur = $this->getCurrentExrate();
        $article_nr_search = $this->getUrlString($article_nr_search);
        $brand_nr_search = $this->getUrlNumber($brand_nr_search);

        $r = $db->query("SELECT t2c.ART_ID
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2c.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2c.ART_ID
        WHERE t2c.SEARCH_NUMBER='$article_nr_search' AND t2c.BRAND_ID=$brand_nr_search AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
        GROUP BY t2c.`ART_ID` ORDER BY t2n.NAME ASC;");
        $n = $db->num_rows($r);

        $art_ids = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $art_id_str = implode(",", $art_ids);

        $form = $this->getHtmlForm("cat_search");
        $search_main = $this->getHtmlForm("cat_search_main");
        $search_filters = $this->getHtmlForm("cat_search_filters");
        $search_brands = $this->getHtmlForm("cat_search_brands");

        list($list, $list_brand, $filters) = $this->searchList($art_id_str, 1, 0, $article_nr_search, $brand_nr_search);

        // if found something
        if (($list_brand) && ($filters)) {
            $colon = "col-lg-10 col-12 cat_result_table";
            $colon_filter = "col-12 col-lg-2";
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
        $search_main = $this->getSearchMain($search_main, $article_nr_search, $this->getBrandName($brand_nr_search), $list, 1, $cur, $status_brand);
        $form = str_replace("{cat_search_main}", $search_main, $form);

        //search filters
        if (!empty($filters)) {
            $search_filters = $this->getSearchFilters($search_filters, $filters, $cur, [], 1, 0);
            $form = str_replace("{cat_search_filters}", $search_filters, $form);
        }

        //search brands
        if (!empty($list_brand)) {
            $search_brands = str_replace("{brands}", $list_brand, $search_brands);
            $search_brands = str_replace("{brands_display}", ($list_brand == "") ? "none" : "", $search_brands);
            $form = str_replace("{cat_search_brands}", $search_brands, $form);
        }

        //search auto & tree
        $form = str_replace("{cat_search_auto}", "", $form);
        $form = str_replace("{cat_search_tree}", "", $form);

        $form = $this->replaceLang($form);

        return $form;
    }

    /*
    * get catalog search List filtred
    * */
    public function getCatalogListFilter($article_nr_search, $brand_nr_search, $brand_filter, $text_filter, $cur, $price_f, $deliv_f, $order_value)
    {
        $article_nr_search = $this->getNameString($article_nr_search);
        $brand_nr_search = $this->getNameString($brand_nr_search);
        $text_filter = $this->getNameString($text_filter);
        $cur = $this->getUrlNumber($cur);
        $order_value = $this->getUrlNumber($order_value);
        $db = DbSingleton::getTokoDb();
        $brand_nr_search = $this->getUrlNumber($brand_nr_search);
        $r = $db->query("SELECT t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2c.BRAND_ID, t2c.DISPLAY_NR, t2c.ART_ID, t2c.KIND, t2c.RELATION 
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2c.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2c.ART_ID
        WHERE t2c.SEARCH_NUMBER='$article_nr_search' AND t2c.BRAND_ID=$brand_nr_search AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
        ORDER BY t2n.NAME ASC;");
        $n = $db->num_rows($r);
        $art_id_str = "";
        $brand_name = "";
        for ($i = 1; $i <= $n; $i++) {
            $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $art_id_str .= "'$art_id'";
            if ($i < $n) {
                $art_id_str .= ",";
            }
        }

        $brand_filter = json_decode($brand_filter);
        if (count($brand_filter) > 0) {
            $brand_filter = implode(",", $brand_filter);
        } else {
            $brand_filter = "";
        }
        $exp_price = explode(",", $price_f);
        $exp_deliv = explode(",", $deliv_f);

        list($list, $filters, $list_brand, $current_value) = $this->searchListFilter($art_id_str, $article_nr_search, $brand_filter, $text_filter, $cur, $exp_price[0], $exp_price[1], $exp_deliv[0], $exp_deliv[1], $brand_nr_search, $order_value, 1);

        $search_main = $this->getHtmlForm("cat_search_main");
        $search_filters = $this->getHtmlForm("cat_search_filters");
        $search_brands = $this->getHtmlForm("cat_search_brands");
        $search_main = $this->getSearchMain($search_main, $article_nr_search, $brand_name, $list, 1, $cur);
        $search_main = $this->replaceLang($search_main);
        $search_filters = $this->getSearchFilters($search_filters, $filters, $cur, $current_value, 1, 0);
        $search_filters = $this->replaceLang($search_filters);
        $search_brands = str_replace("{brands}", $list_brand, $search_brands);
        $search_brands = str_replace("{brands_display}", ($list_brand == "") ? "none" : "", $search_brands);
        $search_brands = $this->replaceLang($search_brands);

        return array($search_main, $search_filters, $search_brands, $filters["max_price"], $text_filter);
    }

    /*
     * Show `CHOOSE BRAND` Form
     * */
    public function getBrandList($article_search, $article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $showform = new FormClass();
        $prefix = $this->getLangPrefix();

        $count_zero = $exist_search_number = 0;
        $exist_brand_link = $result = $list = "";
        $colon = "col-lg-12 col-12";
        $mas = [];

        $form = $this->getHtmlForm("cat_search_list");
        $form_brand = $this->getHtmlForm("cat_brand_list");
        $search_form = $this->getHtmlForm("cat_search_brands_list");

        $r = $db->query("SELECT t2c.ART_ID, t2c.BRAND_ID, t2c.SEARCH_NUMBER, t2c.DISPLAY_NR, t2c.KIND, t2c.RELATION, t2b.BRAND_NAME, t2b.BRAND_LINK, IFNULL(t2n.NAME,'') as NAME 
        FROM `T2_CROSS` t2c 
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID=t2c.BRAND_ID) 	
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID=t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER='$article_search' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) 
        GROUP BY t2c.BRAND_ID;");
        $n = $db->num_rows($r);

        if ($article_search != "") {
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "ART_ID");
                $search_number = $db->result($r, $i - 1, "SEARCH_NUMBER");
                $text = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                $brand_link = $db->result($r, $i - 1, "BRAND_LINK");
                $name = $db->result($r, $i - 1, "NAME");
                $count = $this->countBrandItems($search_number, $brand_id);
                if ($count == 0) {
                    $count_zero++;
                } else {
                    $exist_search_number = strtolower($search_number);
                    $exist_brand_link = $brand_link;
                }
                $mas[$i] = compact("search_number", "text", "brand_id", "brand_name", "brand_link", "count", "name", "art_id");
            }

            usort($mas, "myBrandCmp");
            for ($i = 0; $i < $n; $i++) {
                $search_number = strtolower($mas[$i]["search_number"]);
                $text = $mas[$i]["text"];
                $brand_name = $mas[$i]["brand_name"];
                $brand_link = $mas[$i]["brand_link"];
                $count = $mas[$i]["count"];
                $name = $mas[$i]["name"];
                $photo_name = $showform->getShortArticlePhoto($mas[$i]["art_id"]);
                $link = ($count == 0) ?  "showAlertModal(\"{brand_no_offer} `$text/$brand_name`\",\"{sorry_cap}\");" : "location.href=\"https://toko.ua$prefix/$this->search_link/$search_number/$brand_link/\";";
                $list .= "<tr class=\"pointer table-row\" onclick='$link'>
                    <td class=\"minify\">
                        <img itemprop=\"image\" src=\"$photo_name\" alt=\"Article\">
                    </td>
                    <td>$text</td>
                    <td>$brand_name</td>
                    <td>$name</td>
                    <td>$count</td>
                </tr>";
            }
            $form_brand = str_replace("{brand_list}", $list, $form_brand);
        } else {
            $search_form = str_replace("{search_results}", "{offers_request}", $search_form);
            $search_form = str_replace("{search_result_index}", "<br><span class=\"span-search text-uppercase\">{search_result_for} <b style=\"color:#f44336\">$article_nr_search</b> {nothing_found}</span>
            <br><br><p class=\"span-search\">{check_the_data}</p>", $search_form);
            $search_form = str_replace("{search_result}", "", $search_form);
        }

        $search_form = str_replace("{search_results}", "{choose_brand_manuf}", $search_form);
        $search_form = str_replace("{search_result_index}", "<span class=\"span-brand-search\">{search_request} <b>$article_search</b> {search_result_for_end}</span>", $search_form);
        $search_form = str_replace("{art}", $result, $search_form);
        $search_form = str_replace("{currency}", "", $search_form);
        $search_form = str_replace("{products_view}", "", $search_form);
        $search_form = str_replace("{search_result}", $form_brand, $search_form);
        $search_form = str_replace("{search_form_col}", $colon, $search_form);
        $form = str_replace("{search_filters}", "", $form);
        $form = str_replace("{search_form}", $search_form, $form);

        if ($count_zero == ($n - 1)) {
            header("Location: https://toko.ua$prefix/$this->search_link/$exist_search_number/$exist_brand_link/");
        }
        return $form;
    }

    /*
     * show navigation row (with Details headers)
     * */
    public function getDetailsListing()
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $automan = new AutoClass();
        $prefix = $language->getLangPrefix();
        $lang_id = $this->getLanguage();
        $lang_cap = $language->getTexCapLanguage($lang_id);
        $r = $db->query("SELECT * FROM `T2_GROUP_TREE_HEAD` WHERE `STATUS`=1;");
        $n = $db->num_rows($r);
        $list = "";
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $tex_text = $db->result($r, $i - 1, "TEX_$lang_cap");
            $head_link = $automan->getHeadNewDescr($head_id)["link"];
            $header = "<a href=\"https://toko.ua$prefix/catalog/$head_link/\">$tex_text</a>";
            $list .= "<li class=\"header-nav__li\" data-nav-id=\"$head_id\">$header</li>";
        }
        return $list;
    }

    /*
     * Show Header Navigation
     * */
    public function showHeadTemplate($head_id)
    {
        $head_id = $this->getUrlNumber($head_id);
        $automan = new AutoClass();
        $head_link = $automan->getHeadNewDescr($head_id)["link"];
        $list = $this->getGroupTreeStr($head_id);
        $footer = "<a href=\"https://toko.ua/catalog/$head_link\">{show_all_cap} <i class=\"fa fa-chevron-right\"></i></a>";
        $footer = $this->replaceLang($footer);
        return array($list, $footer);
    }

    /*
     * Show Header Navigation Item
     * */
    public function getGroupTreeStr($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        $language = new LangClass();
        $lang_id = $this->getLanguage();
        $prefix = $this->getLangPrefix();
        $lang_cap = $language->getTexCapLanguage($lang_id);
        $arr = [];
        $list = "";
        $head_link = $automan->getHeadNewDescr($head_id)["link"];
        $r = $db->query("SELECT cs.*, cat.CAT_ID
        FROM `T2_GROUP_TREE_STR` cs 
            LEFT OUTER JOIN `T2_GROUP_TREE_CATEGORY` cat ON cat.CAT_ID=cs.CAT_ID
		WHERE cs.HEAD_ID='$head_id' ORDER BY cat.POSITION ASC, cs.POSITION ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $cat_id = $db->result($r, $i - 1, "CAT_ID");
                $text = $db->result($r, $i - 1, "TEX_$lang_cap");
                $image = $db->result($r, $i - 1, "IMAGES");
                $str_id = $db->result($r, $i - 1, "STR_ID");
                $link = $db->result($r, $i - 1, "TEX_LINK");
                $arr[$cat_id][$i] = ["text" => $text, "image" => $image, "str_id" => $str_id, "str_link" => $link];
            }
            foreach ($arr as $key => $value) {
                list($cat_name, $cat_link) = $automan->getCatNewDescr($key);
                $list .= "<div class=\"tree-item\">";
                $list .= "<div class=\"tree-item-title\">
                    <a href=\"https://toko.ua$prefix/$this->catalog_link/$head_link/$cat_link/\">$cat_name</a>
                </div>";
                $list .= "<div class=\"tree-item-list\">";
                foreach ($value as $v) {
                    $tex = $v["text"];
                    $str_link = $v["str_link"];
                    $link = "https://toko.ua$prefix/$this->catalog_link/$str_link/";
                    $list .= "<div class=\"tree-item-list__element\">
                        <a href=\"$link\">$tex</a>
                    </div>";
                }
                $list .= "</div>";
                $list .= "</div>";
            }
        }
        if ($n == 0) {
            $list = "";
        }
        $list = $this->replaceLang($list);
        return $list;
    }

    /*
     * show tec models list
     * catalog/maslyanyj-filtr/ + TYP_ID
     * */
    public function techModelsList($typ_id, $str_id)
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $automan = new AutoClass();
        $cur = $this->getCurrentExrate();
        $str_id = $this->getUrlNumber($str_id);
        list($manufacture, $model, $model_id) = $automan->getCarInfo($typ_id);
        $automan->setAutoData($manufacture, $model, $model_id, $typ_id, $str_id);
        $client->toggleProductView(1);

        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$typ_id' GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        $t2_link_arts = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $t2_link_arts[] = $art_id;
        }
        $t2_link_arts = implode(",", $t2_link_arts);

        $r = $db->query("SELECT `ART_ID` FROM `T2_TREE` WHERE `ART_ID` IN ($t2_link_arts) AND `STR_ID`=$str_id;");
        $n = $db->num_rows($r);
        $t2_tree_arts = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $t2_tree_arts[] = $art_id;
        }
        $art_id_str = implode(",", $t2_tree_arts);

        list($list, $list_brand, $filters) = $this->searchList($art_id_str, 2, 1);

        $search_filters = $this->getHtmlForm("cat_search_filters");
        $search_brands = $this->getHtmlForm("cat_search_brands");
        $search_filters = $this->getSearchFilters($search_filters, $filters, $cur, [], 2, 0);
        $search_filters = $this->replaceLang($search_filters);
        $search_brands = str_replace("{brands}", $list_brand, $search_brands);
        $search_brands = str_replace("{brands_display}", ($list_brand == "") ? "none" : "", $search_brands);
        $search_brands = $this->replaceLang($search_brands);

        return array($list, $search_brands, $search_filters);
    }

    /*
     * show tec models list filtred
     * catalog/maslyanyj-filtr/ + TYP_ID
     * */
    public function techModelsFilters($art, $brand, $brand_filter, $text_filter, $cur, $price_f, $deliv_f, $order_value)
    {
        $art = $this->getNameString($art);
        $brand = $this->getNameString($brand);
        $text_filter = $this->getNameString($text_filter);
        $cur = $this->getUrlNumber($cur);
        $order_value = $this->getUrlNumber($order_value);
        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        setcookie("currency", $cur);
        session_start();
        $_SESSION["currency"] = $cur;
        $typ_id = $_SESSION["group"];
        $str_id = $_SESSION["str_id"];
        $text_filter = $this->getNameString($text_filter);

        $str_text = $automan->getStrNewDescr($str_id);
        if ($str_text == "") {
            $str_text = $automan->getStrDescr($str_id);
        }

        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$typ_id' GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        $t2_link_arts = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $t2_link_arts[] = $art_id;
        }
        $t2_link_arts = implode(",", $t2_link_arts);

        $r = $db->query("SELECT `ART_ID` FROM `T2_TREE` WHERE `ART_ID` IN ($t2_link_arts) AND `STR_ID`=$str_id;");
        $n = $db->num_rows($r);
        $t2_tree_arts = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $t2_tree_arts[] = $art_id;
        }
        $t2_tree_arts = implode(",", $t2_tree_arts);

        $brand_filter = json_decode($brand_filter);
        $brand_filter = implode(",", $brand_filter);
        $exp_price = explode(",", $price_f);
        $exp_deliv = explode(",", $deliv_f);

        list($list, $filters, $list_brand, $current_value) = $this->searchListFilter($t2_tree_arts, $art, $brand_filter, $text_filter, $cur, $exp_price[0], $exp_price[1], $exp_deliv[0], $exp_deliv[1], $brand, $order_value, 2);

        $search_main = $this->getHtmlForm("cat_search_main");
        $search_main = $this->getSearchMainTree($search_main, $list, $str_text, $typ_id, $str_id);
        $search_filters = $this->getHtmlForm("cat_search_filters");
        $search_filters = $this->getSearchFilters($search_filters, $filters, $cur, $current_value, 2, 0);
        $search_brands = $this->getHtmlForm("cat_search_brands");
        $search_brands = str_replace("{brands}", $list_brand, $search_brands);
        $search_brands = str_replace("{brands_display}", ($list_brand == "") ? "none" : "", $search_brands);

        return array($this->replaceLang($search_main), $this->replaceLang($search_filters), $this->replaceLang($search_brands), $filters["max_price"], $text_filter);
    }

    public function getSearchMain($search_main, $article_nr_search, $brand_name, $list, $type_filter, $cur, $status_brand = 0)
    {
        $client = new ClientClass();
        $showform = new FormClass();
        if ($status_brand == 1) {
            $brand_name = $this->getBrandIdArt($article_nr_search);
        }
        $article_nr_displ = $this->getArtDispl($article_nr_search);
        $title = $this->getHtmlForm("catalog/title");
        $title = str_replace("{article_nr_displ}", $article_nr_displ, $title);
        $title = str_replace("{brand_name}", $brand_name, $title);
        $radio_view = $this->getHtmlForm("products_view_radio");
        $radio_view = str_replace("{checked_table}", ($client->getProductView() == 0) ? "checked" : "", $radio_view);
        $radio_view = str_replace("{checked_cards}", ($client->getProductView() == 1) ? "checked" : "", $radio_view);
        $search_main = str_replace("{art}", $this->replaceLang($title), $search_main);
        $search_main = str_replace("{currency}", $showform->getCurrencyForm($type_filter, 0, $cur), $search_main);
        $search_main = str_replace("{products_view}", $radio_view, $search_main);
        $search_main = str_replace("{search_result}", $list, $search_main);
        return $search_main;
    }

    /*
     * get catalog search filter variables
     * */
    public function getSearchFilters($search_filters, $filters, $cur, $current_value, $type_filter, $template_id)
    {
        $jsFilterNull = $jsFilterClear = $jsTextFilter = "";
        if (!empty($filters))
            if (empty($current_value)) {
                $current_value = array();
                $current_value["min_price"] = 0;
                $current_value["max_price"] = $filters["max_price"];
                $current_value["min_dd"] = 0;
                $current_value["max_dd"] = $filters["max_dd"];
            }
        switch ($type_filter) {
            case 1:
            {
                $jsFilter = "catalogueFilter();";
                $jsFilterNull = "catalogueFilterNull();";
                $jsTextFilter = "search";
                $jsFilterClear = "location.reload(true);";
                break;
            }
            default:
            {
                $jsFilter = "catalogueFilter();";
                break;
            }
        }
        $search_filters = str_replace("{sideblock_title}", "<i class=\"fas fa-filter\"></i> {filters_cap}", $search_filters);
        $search_filters = str_replace("{sideblock_max_price}", $filters["max_price"], $search_filters);
        $search_filters = str_replace("{sideblock_max_dd}", $filters["max_dd"], $search_filters);
        $search_filters = str_replace("{sideblock_max_price_val}", $current_value["max_price"], $search_filters);
        $search_filters = str_replace("{sideblock_max_dd_val}", $current_value["max_dd"], $search_filters);
        $search_filters = str_replace("{sideblock_min_price_val}", $current_value["min_price"], $search_filters);
        $search_filters = str_replace("{sideblock_min_dd_val}", $current_value["min_dd"], $search_filters);
        $search_filters = str_replace("{cur_value}", $cur, $search_filters);
        $search_filters = str_replace("{text_filter_js}", $jsTextFilter, $search_filters);
        $search_filters = str_replace("{template_id}", $template_id, $search_filters);
        $search_filters = str_replace("{catalogue_js_filter}", $jsFilter, $search_filters);
        $search_filters = str_replace("{catalogue_js_filter_null}", $jsFilterNull, $search_filters);
        $search_filters = str_replace("{catalogue_js_filter_clear}", $jsFilterClear, $search_filters);
        $search_filters = str_replace("{filters_col}", "col-lg-2 col-12 pad0", $search_filters);
        return $search_filters;
    }

    public function getSearchMainTree($search_main, $list, $str_text, $typ_id, $str_id)
    {
        $client = new ClientClass();
        $automan = new AutoClass();
        $cash_id = $client->getClientCurrency($this->getClient());
        $cur = $this->getCurrentExrate();
        $mfa_mod_typ_text = $automan->getCarDescription($typ_id);
        $ch1 = $ch2 = $ch3 = $cash_add = "";

        $str_link = $automan->getStrNewLink($str_id);
        $h1_text = $this->getStaticH1("/catalog/$str_link/");
        if ($h1_text != "") {
            $str_text = $h1_text;
        }

        if ($str_text == "") {
            $result = "<h1>{details_for} $mfa_mod_typ_text</h1>";
        } else {
            $result = "<h1>$str_text</h1>";
        }

        if ($cur == 2) {
            $ch2 = "checked=\"checked\"";
        } elseif ($cur == 3) {
            $ch3 = "checked=\"checked\"";
        } else {
            $ch1 = "checked=\"checked\"";
        }

        if ($cash_id == 2) {
            $cash_add = "<input id=\"radio_usd\" type=\"radio\" name=\"cur\" value=\"$cash_id\" $ch2 onclick=\"tecModelsFilter();\"><label for=\"radio_usd\">$</label>";
        }
        if ($cash_id == 3) {
            $cash_add = "<input id=\"radio_eur\" type=\"radio\" name=\"cur\" value=\"$cash_id\" $ch3 onclick=\"tecModelsFilter();\"><label for=\"radio_eur\">€</label>";
        }

        if ($this->getUser() != 0) {
            $currency = "<input id=\"radio_uah\" type=\"radio\" name=\"cur\" value=\"1\" $ch1 onclick=\"tecModelsFilter();\"><label for=\"radio_uah\">{uah_cap}</label>$cash_add";
        } else {
            $currency = "<input id=\"radio_uah\" type=\"radio\" name=\"cur\" value=\"1\" $ch1 onclick=\"tecModelsFilter();\"><label for=\"radio_uah\">{uah_cap}</label>";
        }

        $search_main = str_replace("{art}", $result, $search_main);
        $search_main = str_replace("{currency}", ($cash_id == 1 || $str_text == "") ? "" : $currency, $search_main);
        $radio_view = $this->getHtmlForm("products_view_radio");
        $radio_view = str_replace("{checked_table}", ($client->getProductView() == 0) ? "checked" : "", $radio_view);
        $radio_view = str_replace("{checked_cards}", ($client->getProductView() == 1) ? "checked" : "", $radio_view);
        $search_main = str_replace("{products_view}", $radio_view, $search_main);
        $search_main = str_replace("{search_result}", $list, $search_main);
        return $search_main;
    }

    public function getStrParrents($str_id, $str_level)
    {
        //DELETE?
        $db = DbSingleton::getTokoDb();
        $str_id = $this->getUrlNumber($str_id);
        $str_level = $this->getUrlNumber($str_level);
        $str_array = [];
        $n = $str_level - 2;
        for ($i = 1; $i <= $n; $i++) {
            $r = $db->query("SELECT `STR_ID_PARENT` FROM `T2_GROUP_TREE` WHERE `STR_ID`=$str_id LIMIT 1;");
            $str_id_parrent = $db->result($r, 0, "STR_ID_PARENT");
            array_push($str_array, $str_id_parrent);
            $str_id = $str_id_parrent;
        }
        return $str_array;
    }

    public function getSearchTree($search_tree, $td_array, $typ_id, $status_str, $str_id)
    {
        //DELETE?
        $automan = new AutoClass();
        $prefix = $this->getLangPrefix();
        if ($str_id == 0) {
            list($str_id, $slvl,) = $automan->getAutoStrData();
        } else {
            list($slvl,) = $automan->getStrParams($str_id);
        }
        $tree = "";
        $lvl = 1;
        $parrents = $this->getStrParrents($str_id, $slvl);

        for ($i = 1; $i <= 10; $i++) {
            $lvl += 1;
            foreach ($td_array as $elm) {
                if ($elm["level"] == $lvl) {
                    $str_id2 = $elm["id_tree"];
                    $str_id_parrent2 = $elm["id_parent"];
                    $class_parrent = (in_array($str_id2, $parrents)) ? "tf-child-true tf-open" : "";
                    $class_str = ($str_id_parrent2 == $str_id) ? "tf-child-false tf-open" : "";
                    $class_check = ($str_id2 == $str_id) ? "detail-red" : "";

                    $str = "<li class=\"$str_id2 $class_parrent $class_str\"><div>";
                    if ($elm["child"] > 0) {
                        $str .= $elm["name"];
                    }
                    if ($elm["child"] == 0) {
                        $newLink = $automan->getCarLink($typ_id, $str_id2);
                        $str .= "<a class=\"details_class $class_check\" href=\"$newLink\">" . $elm["name"] . "</a>";
                    }
                    $str .= "</div>";
                    if ($elm["child"] > 0) {
                        $str .= "\n<ul>\n{p" . $elm["id_tree"] . "}</ul>\n";
                    }
                    $str .= "</li>\n";
                    if ($lvl == 2) {
                        $tree .= $str;
                    }
                    if ($lvl > 2) {
                        $tree = str_replace("{p" . $elm["id_parent"] . "}", $str . "{p" . $elm["id_parent"] . "}", $tree);
                    }
                }
            }
        }
        foreach ($td_array as $elm) {
            $tree = str_replace("{p" . $elm["id_parent"] . "}", "", $tree);
            $tree = str_replace("{p" . $elm["id_tree"] . "}", "", $tree);
        }

        $treeFilter = $this->getHtmlForm("cat_tree_filter");
        $treeFilter = str_replace("{tree_filter}", $tree, $treeFilter);
        $treeFilter = str_replace("{tree_catalogue}", "https://toko.ua$prefix/$this->catalog_link/", $treeFilter);
        $treeFilter = str_replace("{tree_catalogue_class}", $status_str, $treeFilter);
        $search_tree = str_replace("{tree}", $treeFilter, $search_tree);
        $search_tree = $this->replaceLang($search_tree);
        return $search_tree;
    }

    /*
     * get Search Articles Count
     * */
    public function countBrandItems($article_nr_search, $brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $art_ids = [];
        $brand_id = $this->getUrlNumber($brand_id);
        $r = $db->query("SELECT t2c.ART_ID FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2c.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2c.ART_ID
        WHERE t2c.SEARCH_NUMBER='$article_nr_search' AND t2c.BRAND_ID=$brand_id AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $art_id_str = implode(",", $art_ids);
        return $this->searchList($art_id_str, 1, 0, $article_nr_search, $brand_id)[3];
    }

    /*
     * show search result header
     * */
    public function drawHeaderSearchList($type_filter, $view = 0, $order = "")
    {
        $sort1 = $sort2 = $sort3 = $sort4 = "fa-sort";
        $storage_info = "{storage_full_info}";
        switch ($order) {
            case "dd":
                $sort2 = "fa-sort-alpha-down";
                break;
            case "stock":
                $sort3 = "fa-sort-alpha-down";
                break;
            case "price":
                $sort4 = "fa-sort-alpha-down";
                break;
            case "name" :
            default:
                $sort1 = "fa-sort-alpha-down";
                break;
        }
        switch ($type_filter) {
            case 2:
                $jsFilter = "tecModelsFilter";
                break;
            case 3:
                $jsFilter = "catGroupFilter";
                break;
            case 1:
            default:
                $jsFilter = "catalogueFilter";
                break;
        }
        $form = $this->getHtmlForm("cat_search_header");
        $form = str_replace("{cat_js_filter}", $jsFilter, $form);
        $form = str_replace("{cat_sort_1}", $sort1, $form);
        $form = str_replace("{cat_sort_2}", $sort2, $form);
        $form = str_replace("{cat_sort_3}", $sort3, $form);
        $form = str_replace("{cat_sort_4}", $sort4, $form);
        $form = str_replace("{cat_storage_info}", $storage_info, $form);
        $form = str_replace("{cat_product_view}", (!$view) ? "" : "none", $form);
        if ($type_filter == 3) {
            $form = "";
        }
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
            `name` VARCHAR(100),
            `brand_id` INT(100), 
            `brand` VARCHAR(100),
            `text` VARCHAR(100),
            `del` VARCHAR(100),
            `stock` INT(100),
            `price` FLOAT,
            `dd` INT(100),
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
    public function getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, $where_brands, $where_text)
    {
        $db = DbSingleton::getTokoDb();
//        if ($article_nr_search != "") {
//            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH`='$article_nr_search' AND `BRAND_ID`='$brand_nr_search' LIMIT 1;");
//            $n = $db->num_rows($r);
//            if ($n > 0) {
//                $art_id = $db->result($r, 0, "ART_ID");
//                $where_oe_art_id = $this->getOriginalEquipment($art_id);
//                $where_art_id_str .= ",$where_oe_art_id";
//            }
//        }
//        if ($where_art_id_str == "") {
//            $where_art_id_str = 0;
//        }
        $where_art_id_str = rtrim($where_art_id_str, ",");
        $r = $db->query("
        SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2asc.AMOUNT as AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) AND (t2asc.AMOUNT!=NULL OR t2asc.AMOUNT!=0) $where_brands $where_text
        GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
        UNION ALL
        SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2si.stock_suppl as AMOUNT, t2si.client_storage_id as storage_id, t2si.suppl_id, t2si.return_delay
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) AND (t2si.stock_suppl!=NULL OR t2si.stock_suppl!=0) $where_brands $where_text
        GROUP BY t2a.ART_ID, t2si.client_storage_id;");
        return $r;
    }

    /*
     * get Original Numbers
     * */
    public function getOriginalEquipment($art_id)
    {
        $db = DbSingleton::getTokoDb();
//        $search_arr = [];
//        $brand_arr = [];
        $arts = [];
        $art_id_arr = [];

        $r = $db->query("SELECT `SEARCH_NUMBER`, `BRAND_ID` FROM `T2_CROSS` 
        WHERE `ART_ID`='$art_id' AND ((`KIND`=3 AND `RELATION`=0) OR (`KIND` IN (3,4) AND `RELATION`=1) OR (`KIND` IN (3,4) AND `RELATION`=2)) 
        GROUP BY `SEARCH_NUMBER`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $article_search = $db->result($r, $i - 1, "SEARCH_NUMBER");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
//            array_push($search_arr, "'$article_search'");
//            array_push($brand_arr, $brand_id);
            $arts[$i] = ["search_number" => $article_search, "brand_id" => $brand_id];
        }
//        $search_str = implode(",", $search_arr);
//        $brand_str = implode(",", $brand_arr);

//        if ($search_str != "") {
//            $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES` WHERE `ARTICLE_NR_SEARCH` IN ($search_str) AND `BRAND_ID` IN ($brand_str);");
//            $n = $db->num_rows($r);
//            for ($i = 1; $i <= $n; $i++) {
//                $cross_art_id = $db->result($r, $i - 1, "ART_ID");
//                array_push($art_id_arr, $cross_art_id);
//            }
//        }

        foreach ($arts as $art) {
            $article_search = $art["search_number"];
            $brand_id = $art["brand_id"];
            $r = $db->query("SELECT `ART_ID` FROM `T2_CROSS` WHERE `SEARCH_NUMBER`='$article_search' AND `BRAND_ID`='$brand_id' AND `KIND`=3 AND `RELATION`=0;");
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
    public function getListBrand($brands, $main_brand, $cur, $jsFilterModel, $brand_filter)
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
            $val_brand = $value["brand"];

            if ($brand_filter != "") {
                $brand_array = explode(",", $brand_filter);
                $checked = (in_array($brand_id, $brand_array)) ? "checked=\"checked\"" : "";
            }

            if ($brand_filter == "") {
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

            $list_brand .= $this->getHtmlForm("cat_brand_range");
            $list_brand = str_replace("{val_brand}", $val_brand, $list_brand);
            $list_brand = str_replace("{brand_id}", $brand_id, $list_brand);
            $list_brand = str_replace("{main_brand_class}", $main_brand_class, $list_brand);
            $list_brand = str_replace("{checked}", $checked, $list_brand);
            $list_brand = str_replace("{min_price}", $min_price, $list_brand);
            $list_brand = str_replace("{currency_cap}", $this->getSymbolExrate($cur), $list_brand);
            $list_brand = str_replace("{jsFilterModel}", $jsFilterModel, $list_brand);
        }
        $list_brand = $this->replaceLang($list_brand);
        return $list_brand;
    }

    public function searchList($where_art_id_str, $type_filter = 1, $view = 0, $article_nr_search = "", $brand_nr_search = "")
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
        $filters = $mas = $brands = $brand_ids = [];
        $list_brand = "";
        $filters["max_price"] = $filters["max_dd"] = $count = $main_brand = 0;
        $filters["min_price"] = 99999999;

        $art_id_search = $this->getArticleId($article_nr_search, $brand_nr_search);

        list($error, $jsFilterModel, $list) = $this->getSearchMessages($type_filter);

        if ($where_art_id_str != "") {
            $this->createTemporarySearchTable($temp_key);
            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "", "");
            $n = $db->num_rows($r);
            $list = $this->drawHeaderSearchList($type_filter, $view);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand = $db->result($r, $i - 1, "BRAND_NAME");
                    $name = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $format_name = $this->getFormatAticle($name);
                    $text = $db->result($r, $i - 1, "NAME");
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
                    $filter_price = $price;

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
                    if (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id == 0) ? 1 : 0;
                    }

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if ($price != 0 || (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                        if ($stock > 0 || (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                            // visible suppl storage
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `name`, `brand_id`, `brand`, `text`, `del`, `stock`, `price`, `dd`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$name', '$brand_id', '$brand', '$text', '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                                if ($type_filter == 1) {
                                    if ($art_id == $art_id_search) {
                                        $main_brand = $brand_id;
                                    }
                                }
                                if ($brand != "") {
                                    if ($stock > 0 && $price > 0) {
                                        array_push($brand_ids, $brand_id);
                                        $brands[$art_id]["brand"] = $brand;
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

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `name` ASC;");
                $n = $db->num_rows($r);

                if ($n == 1) {
                    $stock = $db->result($r, 0, "stock");
                    $price = $db->result($r, 0, "price");
                    if ($stock == 0 && $price == 0) {
                        $list = $this->getHtmlForm("enothing_found");
                        $list = str_replace("{error_nothing_found}", $this->err1, $list);
                        return array($list, "", "", 0);
                    }
                }

                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "art_id");
                    $name = $db->result($r, $i - 1, "name");
                    $brand_id = $db->result($r, $i - 1, "brand_id");
                    $brand = $db->result($r, $i - 1, "brand");
                    $text = $db->result($r, $i - 1, "text");
                    $delivery_info = $db->result($r, $i - 1, "del");
                    $stock = $db->result($r, $i - 1, "stock");
                    $price = $db->result($r, $i - 1, "price");
                    $delivery_days = $db->result($r, $i - 1, "dd");
                    $delivery_short_info = $db->result($r, $i - 1, "delivery_short_info");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $return_days = $db->result($r, $i - 1, "return_days");
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $status = $db->result($r, $i - 1, "status");
                    $mas[$art_id][$i] = [
                        "name" => $name,
                        "brand_id" => $brand_id,
                        "brand" => $brand,
                        "text" => $text,
                        "delivery_info" => $delivery_info,
                        "stock" => $stock,
                        "price" => $price,
                        "delivery_days" => $delivery_days,
                        "delivery_short_info" => $delivery_short_info,
                        "suppl_id" => $suppl_id,
                        "return_days" => $return_days,
                        "storage_id" => $storage_id,
                        "status" => $status
                    ];
                }

                // delete temp table
                $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

                // get filter brand list
                $list_brand = $this->getListBrand($brands, $main_brand, $cur, $jsFilterModel, []);

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
                $list = $this->outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view);
            }

            $count = count($mas);
            if ($count < 1) {
                $list = "$error";
                unset($list_brand);
                $list_brand = "";
                unset($filters);
                $filters = array();
                $filters["max_price"] = 0;
                $filters["max_dd"] = 0;
            }

        }
        return array($list, $list_brand, $filters, $count, $brand_ids);
    }

//    public function searchList($where_art_id_str, $type_filter = 1, $view = 0, $article_nr_search = "", $brand_nr_search = "")
    public function searchListFilter($where_art_id_str, $article_nr_search, $brand_filter, $text_filter, $cur, $price_min, $price_max, $del_min, $del_max, $brand_nr_search, $order_value, $type_filter = 1)
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
        $error = $this->replaceLang($this->getHtmlForm("error/404_tree"));
        $list = "$error";
        $art_id_search = $this->getArticleId($article_nr_search, $brand_nr_search);

        $text_filter = $this->getNameString($text_filter);
        list($where_text, $where_brands) = $this->getFilters($text_filter, $brand_filter);

        if ($where_art_id_str != "") {
            $articlePrices = $this->getArticlePrices($where_art_id_str);
            $deliverInfo = $this->getTpointDeliveryInfos($tpoint_id, $where_art_id_str);
            $articleSupplPrices = $this->getArticleSupplPrices($where_art_id_str);
            $supplDeliverInfo = $this->getTpointSupplDeliveriesInfo($tpoint_id);
            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, $where_brands, $where_text);
            $n = $db->num_rows($r);

            $list = $this->drawHeaderSearchList($type_filter, $view, $order_value);

            if ($where_brands == "" && $where_text == "") {
                $rs = $r;
                $ns = $n;
            } else {
                $rs = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "", "");
                $ns = $db->num_rows($rs);
            }

            if ($ns > 0) {
                // filters with default search
                for ($i = 1; $i <= $ns; $i++) {
                    $art_id = $db->result($rs, $i - 1, "ART_ID");
                    $brand_id = $db->result($rs, $i - 1, "BRAND_ID");
                    $brand = $db->result($rs, $i - 1, "BRAND_NAME");
                    $name = $db->result($rs, $i - 1, "ARTICLE_NR_DISPL");
                    $format_name = $this->getFormatAticle($name);
                    $stock = intval($db->result($rs, $i - 1, "AMOUNT"));
                    $suppl_id = $db->result($rs, $i - 1, "suppl_id");
                    $storage_id = $db->result($rs, $i - 1, "storage_id");
                    // price
                    $price = $articlePrices[$art_id] ?? 0;
                    // delivery
                    $delivery_days = $deliverInfo[$storage_id]["delivery_days"] ?? 0;
                    if ($suppl_id != 0) {
                        $price = $articleSupplPrices[$art_id][$suppl_id][$storage_id];
                        $deliveryData = $supplDeliverInfo[$suppl_id][$storage_id] ?? [
                                "info" => $this->err2,
                                "delivery_days" => 0,
                                "delivery_short_info" => $this->err2
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

                    if ($price != 0 || (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                        if ($stock > 0 || (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search)) {
                            if ($type_filter == 1) {
                                if ($art_id == $art_id_search) {
                                    $main_brand = $brand_id;
                                }
                            }
                            if ($stock > 0 && $price > 0) {
                                if ($brand != "") {
                                    $brands[$art_id]["brand"] = $brand;
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
            $jsFilterModel = $this->getSearchMessages($type_filter)[1];
            $list_brand = $this->getListBrand($brands, $main_brand, $cur, $jsFilterModel, $brand_filter);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand = $db->result($r, $i - 1, "BRAND_NAME");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $name = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $format_name = $this->getFormatAticle($name);
                    $text = $db->result($r, $i - 1, "NAME");
                    $return_days = $db->result($r, $i - 1, "return_delay");
                    $stock = intval($db->result($r, $i - 1, "AMOUNT"));
                    $storage_id = $db->result($r, $i - 1, "storage_id");

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
                    $delivery_info = $deliverInfo[$storage_id]["info"];
                    $delivery_days = $deliverInfo[$storage_id]["delivery_days"];
                    $delivery_short_info = $deliverInfo[$storage_id]["delivery_short_info"];
                    if ($suppl_id != 0) {
                        $deliveryData = $supplDeliverInfo[$suppl_id][$storage_id] ?? [
                                "info" => $this->err2,
                                "delivery_days" => 0,
                                "delivery_short_info" => $this->err2
                            ];
                        $delivery_info = $deliveryData["info"];
                        $delivery_days = $deliveryData["delivery_days"];
                        $delivery_short_info = $deliveryData["delivery_short_info"];
                    }

                    // filters
                    if ($filter_price > $filters["max_price"]) {
                        $filters["max_price"] = ceil($filter_price);
                    }
                    $current_value["min_price"] = $price_min;
                    $current_value["max_price"] = $price_max;
                    $current_value["min_dd"] = $del_min;
                    $current_value["max_dd"] = $del_max;

                    if (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id == 0) ? 1 : 0;
                    }

                    if (($name == $article_nr_search || $format_name == $article_nr_search) && $brand_id == $brand_nr_search) {
                        $mas[$art_id][$i] = [
                            "name" => $name,
                            "brand_id" => $brand_id,
                            "brand" => $brand,
                            "text" => $text,
                            "delivery_info" => $delivery_info,
                            "stock" => $stock,
                            "price" => $price,
                            "delivery_days" => $delivery_days,
                            "delivery_short_info" => $delivery_short_info,
                            "suppl_id" => $suppl_id,
                            "return_days" => $return_days,
                            "storage_id" => $storage_id,
                            "status" => $status
                        ];
                    } elseif ($stock > 0) {
                        if ($price >= $price_min && $price <= $price_max && $delivery_days >= $del_min && $delivery_days <= $del_max) {
                            $mas[$art_id][$i] = [
                                "name" => $name,
                                "brand_id" => $brand_id,
                                "brand" => $brand,
                                "text" => $text,
                                "delivery_info" => $delivery_info,
                                "stock" => $stock,
                                "price" => $price,
                                "delivery_days" => $delivery_days,
                                "delivery_short_info" => $delivery_short_info,
                                "suppl_id" => $suppl_id,
                                "return_days" => $return_days,
                                "storage_id" => $storage_id,
                                "status" => $status
                            ];
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
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $client_id = $this->getClient();
        $tpoint_id = $this->getTpointID();
        $cur = $this->getCurrentExrate();
        $view = $client->getProductView();
        session_start();
        $temp_key = session_id();
        $mas = [];
        $list = $where_art_id_str = "";

        $article_nr_search = $this->getArticleDispl($art_id_search);
        $brand_nr_search = $this->getArticleBrand($art_id_search);

        $r = $db->query("SELECT t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2c.BRAND_ID, t2c.DISPLAY_NR, t2c.ART_ID, t2c.KIND, t2c.RELATION 
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2c.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2c.ART_ID
        WHERE t2c.SEARCH_NUMBER='$article_nr_search' AND t2c.BRAND_ID=$brand_nr_search AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
        ORDER BY t2n.NAME ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            if ($art_id_search != $art_id) {
                $where_art_id_str .= "'$art_id',";
            }
        }
        $where_art_id_str = rtrim($where_art_id_str, ",");

        if ($where_art_id_str != "") {
            $this->createTemporarySearchTable($temp_key);
            list($error, ,) = $this->getSearchMessages(1);

            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "", "");
            $n = $db->num_rows($r);

            $list = $this->drawHeaderSearchList(1, $view);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand = $db->result($r, $i - 1, "BRAND_NAME");
                    $name = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $text = $db->result($r, $i - 1, "NAME");
                    $return_days = $db->result($r, $i - 1, "return_delay");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $stock = intval($db->result($r, $i - 1, "AMOUNT"));
                    $storage_id = $db->result($r, $i - 1, "storage_id");

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
                    if ($art_id == $art_id_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id == 0) ? 1 : 0;
                    }

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if ($price != 0 || ($art_id == $art_id_search)) {
                        if ($stock > 0 || ($art_id == $art_id_search)) {
                            // visible suppl storage
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `name`, `brand_id`, `brand`, `text`, `del`, `stock`, `price`, `dd`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$name', '$brand_id', '$brand', '$text', '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                            }
                        }
                    }
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `name` ASC;");
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
                    $name = $db->result($r, $i - 1, "name");
                    $brand_id = $db->result($r, $i - 1, "brand_id");
                    $brand = $db->result($r, $i - 1, "brand");
                    $text = $db->result($r, $i - 1, "text");
                    $delivery_info = $db->result($r, $i - 1, "del");
                    $stock = $db->result($r, $i - 1, "stock");
                    $price = $db->result($r, $i - 1, "price");
                    $delivery_days = $db->result($r, $i - 1, "dd");
                    $delivery_short_info = $db->result($r, $i - 1, "delivery_short_info");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $return_days = $db->result($r, $i - 1, "return_days");
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $status = $db->result($r, $i - 1, "status");
                    $mas[$art_id][$i] = [
                        "name" => $name,
                        "brand_id" => $brand_id,
                        "brand" => $brand,
                        "text" => $text,
                        "delivery_info" => $delivery_info,
                        "stock" => $stock,
                        "price" => $price,
                        "delivery_days" => $delivery_days,
                        "delivery_short_info" => $delivery_short_info,
                        "suppl_id" => $suppl_id,
                        "return_days" => $return_days,
                        "storage_id" => $storage_id,
                        "status" => $status
                    ];
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

                $cc = 0;
                if (!empty($mas)) {
                    foreach ($mas as $mas_key => $mas_val) {
                        $cc++;
                        if ($cc > 3) {
                            unset($mas[$mas_key]);
                        }
                    }
                }

                // show search list
                $list = $this->outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view);
            }

            if (count($mas) < 1) {
                $list = "";
            }
        }

        return $list;
    }

    public function getTpointDeliveryInfos($tpoint_id, $where_art_id_str)
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $r = $db->query("SELECT tpdt.delivery_days, tpdt.week_day, tpdt.time_from_del, tpdt.time_to_del, tpdt.storage_id 
        FROM `T_POINT_DELIVERY_TIME` tpdt
            JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.STORAGE_ID=tpdt.storage_id
        WHERE t2asc.ART_ID IN ($where_art_id_str) AND tpdt.status='1' AND tpdt.tpoint_id='$tpoint_id' AND tpdt.week_day='$week_day' AND tpdt.time_from<='$cur_time' AND tpdt.time_to>='$cur_time' 
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
            $today = (($delivery_days == 0) ? "<i>{today_cap}</i>" : (($delivery_days == 1) ? "<i>{tomorrow_cap}</i>" : "<i>$date_del ($week_day_short)</i>"));
//            if ($delivery_days == 0) {
//                $today = "<i>{today_cap}</i>";
//            } elseif ($delivery_days == 1) {
//                $today = "<i>{tomorrow_cap}</i>";
//            } else {
//                $today = "<i>$date_del ($week_day_short)</i>";
//            }
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
        $query = "SELECT t2apr.price_$price_lvl price, t2apr.cash_id, t2a.ART_ID 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id=t2a.ART_ID)
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID)
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2apr.in_use='1';";
        $r = mysqli_fetch_all($dbt->query($query), MYSQLI_ASSOC);
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
        WHERE `status`='1' AND `tpoint_id`='$tpoint_id' AND `week_day`='$week_day' AND `time_from`<='$cur_time' AND `time_to`>='$cur_time';");
        $deliveryTimes = mysqli_fetch_all($r, MYSQLI_ASSOC);
        foreach ($deliveryTimes as $deliveryTime) {
            $time_from_del = substr($deliveryTime["time_from_del"], 0, -3);
            $time_to_del = substr($deliveryTime["time_to_del"], 0, -3);
            $week = date("N", strtotime(" + " . $deliveryTime["delivery_days"] . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $deliveryTime["delivery_days"] . " days"));
            $today = (($deliveryTime["delivery_days"] == 0) ? "<i>{today_cap}</i>" : (($deliveryTime["delivery_days"] == 1) ? "<i>{tomorrow_cap}</i>" : "<i>$date_del ($week_day_short)</i>"));
//            if ($deliveryTime["delivery_days"] == 0) {
//                $today = "<i>{today_cap}</i>";
//            } elseif ($deliveryTime["delivery_days"] == 1) {
//                $today = "<i>{tomorrow_cap}</i>";
//            } else {
//                $today = "<i>$date_del ($week_day_short)</i>";
//            }
            $info = "$today<br>{$time_from_del} - {$time_to_del}";
            $delivery_short_info = "$today<br>{with_cap} $time_from_del";
            $result[$deliveryTime["suppl_id"]][$deliveryTime["suppl_storage_id"]] = [
                "info" => $info,
                "delivery_days" => $deliveryTime["delivery_days"],
                "delivery_short_info" => $delivery_short_info
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
        $query = "SELECT t2a.ART_ID, t2si.client_storage_id, t2si.price_usd, t2si.suppl_id, acvc.*, t2si.suppl_id, tpsf.margin, tpsf.delivery, tpsf.margin2 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
            LEFT OUTER JOIN {$db->getDbName()}.A_CLIENTS_VAT_CONDITIONS acvc ON acvc.client_id = t2si.suppl_id
            LEFT OUTER JOIN `T_POINT_SUPPL_FM` tpsf ON (tpsf.suppl_id = t2si.suppl_id AND tpsf.suppl_storage_id = t2si.client_storage_id)
        WHERE t2a.ART_ID IN ($where_art_id_str) AND t2si.status=1 AND tpsf.tpoint_id=$tpoint AND tpsf.price_rating_id='$price_suppl_lvl' AND tpsf.price_from<=t2si.price_usd AND tpsf.price_to>=t2si.price_usd;";
        $r = $dbt->query($query);
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
        $r = $db->query("SELECT `SEARCH_NUMBER` FROM `T2_CROSS` WHERE `ART_ID`='$art_id' AND `KIND` LIKE '3' AND `RELATION`=0;");
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
        WHERE `ART_ID`='$art_id' AND `SEARCH_NUMBER` LIKE '$article_nr_search' AND `KIND` IN (3,4) AND `RELATION`=$relation_id;");
        $n = $db->result($r, 0, `count_arts`);
        return ($n > 0);
    }

    /*
     * Get Kind of brand
     * */
    public function getBrandType($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `KIND` FROM `T2_BRANDS` WHERE `BRAND_ID`='$brand_id' LIMIT 1;");
        $kind = $db->result($r, 0, "KIND");
        return ($kind == 3);
    }

    /*
     * Get Article image Type
     * */
    public function getIndexTypeImage($art_id, $article_nr_search, $name, $format_name, $brand_id, $brand_nr_search)
    {
        $true_art_id = $this->getArtID($article_nr_search);
        $brand = $this->getBrandName($brand_nr_search);
        // ANALOGS
        $image_analog = $this->images . "/tcdanalogs/clone.svg";
        $index_type = "<img src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_analog}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_analog} $article_nr_search $brand\">";
        // OE
        if ($this->checkOriginalEquipment($true_art_id, $format_name)) {
            $image_analog = $this->images . "/tcdanalogs/OE.svg";
            $index_type = "<img src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_original}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_original} $article_nr_search $brand\">";
        }
        // INCLUDED
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 1)) {
            $image_analog = $this->images . "/tcdanalogs/chevron-square-down.svg";
            $index_type = "<img src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_included}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_included} $article_nr_search $brand\">";
        }
        // PRESENTED
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 2)) {
            $image_analog = $this->images . "/tcdanalogs/chevron-square-up.svg";
            $index_type = "<img src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_presented}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_presented} $article_nr_search $brand\">";
        }
        // COMPANION
        if ($this->checkAnalogTypes($art_id, $article_nr_search, 3)) {
            $image_analog = $this->images . "/tcdanalogs/plus-square.svg";
            $index_type = "<img src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_companion}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_companion} $article_nr_search $brand\">";
        }
        // REQUESTED
        if ($article_nr_search != "") if (($name == $article_nr_search || $format_name == $article_nr_search) && ($brand_id == $brand_nr_search)) {
            $image_analog = $this->images . "/tcdanalogs/square.svg";
            $index_type = "<img src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_requested}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_requested} $article_nr_search $brand\">";
            if ($this->getBrandType($brand_id)) {
                $image_analog = $this->images . "/tcdanalogs/OE.svg";
                $index_type .= "<img style=\"margin-left: 5px;\" src=\"$image_analog\" width=\"15\" height=\"15\" alt=\"{index_type_original}\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{index_type_original} $article_nr_search $brand\">";
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
            $r = $db->query("SELECT `visible` FROM `A_CLIENTS_STORAGE` WHERE `client_id`='$suppl_id' AND `id`='$storage_id' LIMIT 1;");
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
    public function printSearchList($id, $art_id, $article_name, $brand_id, $brand_name, $text, $delivery_info, $stock, $price, $article_nr_search, $ll, $class, $hide, $border, $none, $brand_nr_search, $suppl_id, $return_days, $delivery_days, $delivery_short_info, $storage_id, $status, $view)
    {
        $showform = new FormClass();
        $kours = new ExRateClass();
        $client = new ClientClass();
        $shop = new ShopClass();
        $prefix = $this->getLangPrefix();
        $cur = $this->getCurrentExrate();
        $kours_cap = $this->getSymbolExrate($cur);
        $format_name = $this->getFormatAticle($article_name);
        $return_days_alt = $return_days_img = "";
        if ($suppl_id > 0) {
            if ($stock != 0) {
                if ($return_days == 0) {
                    $return_days_alt = "{no_exchange}";
                    $return_days_img = $this->images . "/exchange/exchange2.png";
                } elseif ($return_days == 14) {
                    $return_days_alt = "";
                    $return_days_img = "";
                } elseif ($return_days >= 15) {
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
            $action_form = $this->getHtmlForm("action_box");
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
        $form = str_replace("{product_name}", $article_name, $form);
        $form = str_replace("{product_brand}", $brand_name, $form);
        $form = str_replace("{product_format_name}", $format_name, $form);
        $form = str_replace("{product_lang_prefix}", $prefix, $form);
        $form = str_replace("{product_brand_link}", $this->getBrandLink($brand_id), $form);
        $form = str_replace("{product_format_brand}", $this->getFormatBrand($brand_name), $form);
        $form = str_replace("{product_text}", ($text == "") ? "{details_name_cap}" : $text, $form);
        $form = str_replace("{format_product_text}", ($text == "") ? "{details_name_cap}" : $this->formatArticleName($text), $form);
        $form = str_replace("{product_stock}", ($suppl_id == 0) ? ($stock > 10 ? ">10" : $stock) : $stock, $form);
        $form = str_replace("{product_real_stock}", $stock, $form);
        $form = str_replace("{product_storage_id}", $storage_id, $form);
        $form = str_replace("{product_suppl_id}", $suppl_id, $form);

        $form = str_replace("{return_days_img}", $return_days_img, $form);
        $form = str_replace("{return_days_alt}", $return_days_alt, $form);
        $form = str_replace("{return_display}", ($return_days == 14 || $return_days_img == "") ? "none" : "", $form);

        $form = str_replace("{photo_src}", $this->getArticlePhoto($art_id), $form);
        $form = str_replace("{photo_display}", $this->checkPhoto($art_id) ? "" : "none", $form);
        $form = str_replace("{product_main_photo}", $showform->getArticleMainPhoto($art_id), $form);

        $form = str_replace("{product_del}", $delivery_info, $form);
        $form = str_replace("{product_dd}", $delivery_days, $form);
        $form = str_replace("{product_delivery_class}", ($delivery_days == 0) ? "delivery-green" : ($delivery_days == 1 ? "delivery-blue" : ($delivery_days > 1 ? "delivery-dark" : "")), $form);
        $form = str_replace("{product_delivery_short_info}", str_replace("<br>", " ", $delivery_short_info), $form);

        $form = str_replace("{product_price}", $price . " $kours_cap", $form);
        $form = str_replace("{product_true_price}", $price, $form);
        $form = str_replace("{product_kours_cap}", $kours_cap, $form);

        $form = str_replace("{product_action}", $action_form, $form);
        $form = str_replace("{product_action_count}", $action_count, $form);
        $form = str_replace("{product_title_del}", str_replace("<br>", " ", $delivery_info), $form);
        $form = str_replace("{analog_display}", (($article_name == $article_nr_search || $format_name == $article_nr_search) && ($brand_id == $brand_nr_search)) ? "none" : "", $form);
        $form = str_replace("{product_barcode}", $this->getBarcode($art_id), $form);

        $form = str_replace("{style_border}", $border, $form);
        $form = str_replace("{style_class}", $class, $form);
        $form = str_replace("{style_none}", $none, $form);
        $form = str_replace("{style_hide}", $hide, $form);

        if ($view) {
            $link = $ll;
            $search_number = $this->getArticleSearch($art_id);
            $brand_link = $this->getBrandLink($brand_id);
            $link = str_replace("{content_prefix}", $prefix, $link);
            $link = str_replace("{content_search_number}", $search_number, $link);
            $link = str_replace("{content_brand_link}", $brand_link, $link);
            $form = str_replace("{product_test_offers}", $link, $form);
        } else {
            $form = str_replace("{product_test_offers}", "", $form);
        }

        $flagData = $showform->getCountryFlag($brand_id);
        $form = str_replace("{country_display}", (!$flagData) ? "none" : "", $form);
        $form = str_replace("{flag_image}", $flagData["flag"], $form);
        $form = str_replace("{country_name}", $flagData["country"], $form);
        $form = str_replace("{instock}", ($suppl_id == 0) ? "<b class=\"tables__instock\"> {in_stock}</b>" : "", $form);
        $form = str_replace("{index_type}", $this->getIndexTypeImage($art_id, $article_nr_search, $article_name, $format_name, $brand_id, $brand_nr_search), $form);
        $form = str_replace("{count_users}", $client->getUsersCount(), $form);
        $form = str_replace("{data_today}", date("Y-m-d"), $form);
        $form = str_replace("{prefix_url}", $prefix, $form);
        $form = str_replace("{tpoint_full_name}", ($suppl_id == 0) ? $client->getArticleStorageTPoint($storage_id) : "", $form);

        $form = str_replace("{product_info}", $showform->getArticleInfoForm($art_id, 1), $form);
        $form = str_replace("{product_button}", ($price == 0) ? "none" : "", $form);
        $form = str_replace("{product_image}", $showform->getArticleActivePhoto($art_id), $form);
        $form = str_replace("{product_title}", "$text $brand_name $article_name", $form);

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

        $auto_typ_id = $this->getCookieAuto();
        if ($auto_typ_id != "") {
            if ($this->checkT2Link($auto_typ_id, $art_id)) {
                $form = str_replace("{applicable_display}", "applicable-active", $form);
                $form = str_replace("{applicable_display_text}", "{is_applicable}", $form);
                $form = str_replace("{applicable_onclick}", "", $form);
            } else {
                $form = str_replace("{applicable_display}", "", $form);
                $form = str_replace("{applicable_display_text}", "{is_didnt_applicable}", $form);
                $form = str_replace("{applicable_onclick}", "", $form);
            }
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

        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id`='$client_id';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $category_id = $db->result($r, $i - 1, "client_category");
            array_push($categories, $category_id);
        }
        $categories = implode(",", $categories);

        $r = $db->query("SELECT `id` FROM `ACTION_CLIENTS` WHERE `art_id`='$art_id' AND `status`=1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $action_id = $db->result($r, $i - 1, "id");
            $r2 = $db->query("SELECT * FROM `ACTION_CLIENTS_LIST` WHERE `action_id`='$action_id' AND `client_id`='$client_id';");
            $n2 = $db->num_rows($r2);
            if ($n2 > 0) {
                array_push($actions, $action_id);
            }
            if ($categories != "") {
                $r3 = $db->query("SELECT * FROM `ACTION_CLIENTS_CATEGORY` WHERE `action_id`='$action_id' AND `category_id` IN ($categories);");
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
            $r = $db->query("SELECT * FROM `ACTION_CLIENTS` WHERE `id` IN ($actions) AND `status`=1 LIMIT 1;");
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
        $r = $dbt->query("SELECT `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID`='$art_id';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $amount = $db->result($r, $i - 1, "AMOUNT");
            $all_amount += $amount;
        }
        $r = $db->query("SELECT `amount` FROM `J_DP_STR` WHERE `art_id`='$art_id' AND `status_dps`='93';");
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
        list($price_lvl, $margin_price_lvl) = $this->getDpClientPriceLevels($client_id);
        $markup_min = $client->getClientMarkupMin($client_id);
        $query = "SELECT t2apr.price_$price_lvl, t2apr.minMarkup, t2aps.OPER_PRICE
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id=t2a.ART_ID)
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_STOCK` t2aps ON (t2aps.ART_ID=t2a.ART_ID)
        WHERE t2a.ART_ID='$art_id' AND t2apr.in_use='1' LIMIT 1;";
        $r = $dbt->query($query);
        $n = $dbt->num_rows($r);
        $price = 0;
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
                } elseif ($oper_limit >= $price) {
                    true;
                } else {
                    $price = $oper_limit;
                }
            }
            // 3
            $art_cash_id = $this->getArticlePriceRatingCash($art_id);
            if ($margin_price_lvl < 0 && $markup_min > 0) {
                $price = $this->getPriceRatingKours($price, $art_cash_id, 2);
                $proc_price_margin = $price - ($price * abs($margin_price_lvl) / 100);
                $proc_oper_price_min = $oper_price + ($oper_price * $markup_min / 100);
                if ($proc_price_margin >= $proc_oper_price_min) {
                    $price = $proc_price_margin;
                } elseif (($proc_price_margin < $proc_oper_price_min) && ($proc_oper_price_min > $price)) {
                    true;
                } else {
                    $price = $proc_oper_price_min;
                }
                $price = $this->getPriceRatingKours($price, 2, $art_cash_id);
            }
            $price = $this->getPriceRatingKours($price, 2, $cur);
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
        $query = "SELECT t2apr.price_$price_lvl, t2apr.cash_id, t2apr.minMarkup, t2aps.OPER_PRICE
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_RATING` t2apr ON (t2apr.art_id=t2a.ART_ID)
            LEFT OUTER JOIN `T2_ARTICLES_PRICE_STOCK` t2aps ON (t2aps.ART_ID=t2a.ART_ID)
        WHERE t2a.ART_ID='$art_id' AND t2apr.in_use='1' LIMIT 1;";
        $r = $dbt->query($query);
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
                } elseif ($oper_limit >= $price) {
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
                } elseif (($proc_price_margin < $proc_oper_price_min) && ($proc_oper_price_min > $price)) {
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
        $query = "SELECT t2si.price_usd 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_SUPPL_ARTICLES_IMPORT` t2sai ON (t2sai.art_id=t2a.ART_ID)
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2sai.art_id AND t2si.suppl_id=t2sai.suppl_id AND t2si.status=1)
        WHERE t2a.ART_ID='$art_id' AND t2sai.suppl_id='$suppl_id' LIMIT 1;";
        $r = $dbt->query($query);
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
        $r = $db->query("SELECT * FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$client_id' LIMIT 1;");
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
        $query = "SELECT * FROM `A_CLIENTS_VAT_CONDITIONS` WHERE `client_id`='$suppl_id' LIMIT 1;";
        $r = $db->query($query);
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
        $query = "SELECT `margin`, `delivery`, `margin2` FROM `T_POINT_SUPPL_FM` 
        WHERE `tpoint_id`='$tpoint_id' AND `suppl_id`='$suppl_id' AND `suppl_storage_id`='$suppl_storage_id' AND `price_from`<='$price_suppl' 
        AND `price_to`>='$price_suppl' AND `price_rating_id`='$price_suppl_lvl' LIMIT 1;";
        $r = $db->query($query);
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
        $r = $db->query("SELECT `delivery_days`, `time_from_del`, `time_to_del` FROM `T_POINT_DELIVERY_TIME`
        WHERE `status`='1' AND `tpoint_id`='$tpoint_id' AND `storage_id`='$storage_id' AND `week_day`='$week_day' AND `time_from`<='$cur_time' 
        AND `time_to`>='$cur_time' ORDER BY `delivery_days` ASC LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $delivery_days = $db->result($r, 0, "delivery_days");
            $time_from_del = substr($db->result($r, 0, "time_from_del"), 0, -3);
            $time_to_del = substr($db->result($r, 0, "time_to_del"), 0, -3);
            $week = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $delivery_days . " days"));

            $today = (($delivery_days == 0) ? "<i>{today_cap}</i>" : (($delivery_days == 1) ? "<i>{tomorrow_cap}</i>" : "<i>$date_del ($week_day_short)</i>"));
//            if ($delivery_days == 0) {
//                $today = "<i>{today_cap}</i>";
//            } elseif ($delivery_days == 1) {
//                $today = "<i>{tomorrow_cap}</i>";
//            } else {
//                $today = "<i>$date_del ($week_day_short)</i>";
//            }
            $info = "$today<br>$time_from_del - $time_to_del";
            $short_info = "$today<br>{with_cap} $time_from_del";
        }
        return array("info" => $info, "days" => $delivery_days, "short" => $short_info);
    }

    public function getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $suppl_storage_id)
    {
        $db = DbSingleton::getTokoDb();
        $week_day = date("N");
        $cur_time = date("H:i:s");
        $delivery_days = 0;
        $info = $short_info = "";
        $r = $db->query("SELECT `delivery_days`, `time_from_del`, `time_to_del` FROM `T_POINT_SUPPL_DELIVERY_TIME` 
        WHERE `status`='1' AND `tpoint_id`='$tpoint_id' AND `suppl_storage_id`='$suppl_storage_id' AND `suppl_id`='$suppl_id' AND `week_day`='$week_day' 
        AND `time_from`<='$cur_time' AND `time_to`>='$cur_time' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $delivery_days = $db->result($r, 0, "delivery_days");
            $time_from_del = substr($db->result($r, 0, "time_from_del"), 0, -3);
            $time_to_del = substr($db->result($r, 0, "time_to_del"), 0, -3);
            $week = date("N", strtotime(" + " . $delivery_days . " days"));
            $week_day_short = $this->getWeekdayAbr($week);
            $date_del = date("d.m", strtotime(" + " . $delivery_days . " days"));
            $today = (($delivery_days == 0) ? "<i>{today_cap}</i>" : (($delivery_days == 1) ? "<i>{tomorrow_cap}</i>" : "<i>$date_del ($week_day_short)</i>"));
//            if ($delivery_days == 0) {
//                $today = "<i>{today_cap}</i>";
//            } elseif ($delivery_days == 1) {
//                $today = "<i>{tomorrow_cap}</i>";
//            } else {
//                $today = "<i>$date_del ($week_day_short)</i>";
//            }
            $info = "$today<br>$time_from_del - $time_to_del";
            $short_info = "$today<br>{with_cap} $time_from_del";
        }
        return array("info" => $info, "days" => $delivery_days, "short" => $short_info);
    }

    /*
     * get original numbers form
     * */
    public function getOriginalNumbers($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $r = $db->query("SELECT t2c.DISPLAY_NR, t2b.BRAND_NAME 
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2c.BRAND_ID
        WHERE t2c.KIND='3' AND t2c.RELATION='0' AND t2c.ART_ID='$art_id';");
        $n = $db->num_rows($r);
        $arr = [];
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $art_name = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                $arr[$brand_name][$i] = $art_name;
            }
            $list = "<div class=\"info__numbers\">
            <div class=\"row info__numbers-title\">
                <div class=\"col-3\">{brand_cap}</div>
                <div class=\"col-9\">{art_cap}</div>
            </div>";
            $i = 1;
            foreach ($arr as $arr_key => $arr_val) {
                $list .= "<div class=\"row info__numbers-row\">
                <div class=\"col-3 info__numbers-row-auto\">" . $arr_key . "</div>
                <div class=\"col-9 info__numbers-row-article\">";
                foreach ($arr_val as $key => $val) {
                    $format_val = str_replace(str_split('.,+-\/:*?"<>| '), "", $val);
                    $list .= "<a target=\"_blank\" href=\"https://toko.ua$prefix/$this->search_link/$format_val/\">$val</a>";
                    $i++;
                    if ($i <= count($arr_val)) {
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
        return $list;
    }

    public function showCatalogueTemplates()
    {
        $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $r = $db->query("SELECT * FROM `T2_CATALOGUES_TEMPLATES` WHERE `STATUS`=1 AND `PARENT_ID`=0;");
        $n = $db->num_rows($r);
        $list = "<ul class=\"goods\">";
        for ($i = 1; $i <= $n; $i++) {
            $template_id = $db->result($r, $i - 1, "TEMPLATE_ID");
            $template_link = $db->result($r, $i - 1, "TEMPLATE_LINK");
            $text = $db->result($r, $i - 1, "TEMPLATE_NAME");
            $descr = $db->result($r, $i - 1, "TEMPLATE_DESCR");
            $link = $this->images . "/templates/$template_id.png";
            $url = "https://toko.ua$prefix/$this->products_link/$template_link/";
            $list .= "<li class=\"goods__item\">
                <a href=\"$url\">
                    <img class=\"lazy\" data-src=\"$link\" alt=\"$text\" title=\"$text\">
                    <span>$text</span>
                    <input type=\"hidden\" value=\"$descr\" title=\"$text\">
                </a>
            </li>";
        }
        $list .= "</ul>";
        if ($n == 0) {
            $list = $this->err1;
        }
        $list = $this->replaceLang($list);
        return $list;
    }

    /*
     * format text for URL
     * */
    public function formatUrlText($text)
    {
        $format_text = mb_convert_encoding($text, "UTF-8", "Windows-1251");
        $format_text = $this->translit($format_text);
        $format_text = str_replace(str_split('.,+-\/:*?"<>|_'), "", $format_text);
        $format_text = str_replace(" ", "-", $format_text);
        $format_text = str_replace("'", "", $format_text);
        $format_text = mb_strtolower($format_text);
        return $format_text;
    }

    /*
     * export price list for client
     * */
    public function getPriceList($user_id = null)
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
            LEFT OUTER JOIN `T2_ARTICLES` t2a ON t2a.ART_ID=t2as.ART_ID
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_BARCODES` t2br ON t2br.ART_ID=t2a.ART_ID
        WHERE t2as.AMOUNT!=0 AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
        GROUP BY t2a.ARTICLE_NR_DISPL;");
        $n = $db->num_rows($r);

        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
            $brand = $db->result($r, $i - 1, "BRAND_NAME");
            $name = $db->result($r, $i - 1, "NAME");
            $info = $db->result($r, $i - 1, "INFO");
            $barcode = $db->result($r, $i - 1, "BARCODE");
            $info = trim($info, " ");
            $info = trim($info, "\n");
            $info = trim($info, "\r");
            $info = str_replace("\n", "", $info);
            $info = str_replace("\r", "", $info);

            $price = $this->getArticlePriceClient($art_id, $client_id, $cur);
            $price = str_replace(".", ",", "$price");

            $rs = $db->query("SELECT COUNT(`ART_ID`) as count_arts FROM `T2_ARTICLES_NOT_EXPORT` WHERE `ART_ID`='$art_id' LIMIT 1;");
            $ns = $db->result($rs, 0, "count_arts");
            if ($ns == 0) {
                $list[$i] = [$i, "$article_nr_displ", "$brand", "$name", "$price", "$cur_cap", "$info", "$barcode"];
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
     * Get article default cash
     * table toko_dba.T2_ARTICLES_PRICE_RATING
     * */
    public function getArticlePriceRatingCash($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $cash_id = 2;
        $r = $db->query("SELECT * FROM `T2_ARTICLES_PRICE_RATING` WHERE `art_id`='$art_id' AND `in_use`=1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $cash_id = $db->result($r, 0, "cash_id");
        }
        if ($cash_id == 0 || $cash_id == "0") {
            $db->query("UPDATE `T2_ARTICLES_PRICE_RATING` SET `cash_id`=2 WHERE `art_id`='$art_id' AND `in_use`=1 LIMIT 1;");
            $cash_id = 2;
        }
        return $cash_id;
    }

    /**
     * getPriceRatingKours
     * @param $price
     * @param $cash_id_from
     * @param $cash_id_to
     * @return float
     */
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
                        } elseif ($val["price"] == 0) {
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
                $dd = $val["delivery_days"];
                $del = $val["delivery_info"];
                $price = $val["price"];
                $stock = $val["stock"];
                if (!empty($uniq)) {
                    foreach ($uniq as $uval) {
                        if ($dd == $uval["delivery_days"] && $del == $uval["delivery_info"] && $price == $uval["price"]) {
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
                $uniq[$key] = ["key" => $key, "delivery_days" => $dd, "delivery_info" => $del, "price" => $price, "stock" => $stock];
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
                                $ll[$i] = "<div class=\"row tables__row show_hidden\">
                                    <a id=\"fa-$art_id\" class=\"show_more\" onClick=\"showStorage('$art_id');\">{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> <i class=\"rotate_anime fas fa-chevron-down\"></i></a>
                                    <a id=\"fas-$art_id\" class=\"show_more none\" onClick=\"showStorage('$art_id');\"><span class=\"span-grey\">{collapse_cap}</span> <i class=\"rotate_anime fas fa-chevron-up\"></i></a>
                                </div>";
                            } else {
                                $ll[$i] = "<a href='https://toko.ua{content_prefix}/$this->search_link/{content_search_number}/{content_brand_link}/'>{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> <i class=\"fa fa-chevron-right\"></i></a>";
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
                            $ll[$i] = "<div class=\"row tables__row show_hidden\">
                                <a id=\"fa-$art_id\" class=\"show_more\" onClick=\"showStorage('$art_id');\">{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> <i class=\"rotate_anime fas fa-chevron-down\"></i></a>
                                <a id=\"fas-$art_id\" class=\"show_more none\" onClick=\"showStorage('$art_id');\"><span class=\"span-grey\">{collapse_cap}</span> <i class=\"rotate_anime fas fa-chevron-up\"></i></a>
                            </div>";
                        } else {
                            $ll[$i] = "<a href='https://toko.ua{content_prefix}/$this->search_link/{content_search_number}/{content_brand_link}/'>{more_cap} <span class=\"span-grey\">$j " . $this->getOfferCap($j) . "</span> {from_cap} <span class=\"span-dark-red\">$min_price $currency_cap</span> <i class=\"fa fa-chevron-right\"></i></a>";
                        }
                        $hide[$i] = "none";
                        $class[$i] = "$art_id-hide";
                    }
                    $none[$i] = "dvisibility0";
                    $border[$i] = "border-dashed";
                    $double++;
                } else {
                    $hide[$i] = "";
                    $none[$i] = "dvisibility";
                    $border[$i] = "border-line";
                    $checkarray = array();
                    $double = 0;
                    $preprice = $val["price"];
                }
                array_push($checkarray, $art_id);
                $i++;
                $j++;
            }
            $j = 0;
            $min_price = 9999999;
        }

        return [
            "content" => $ll,
            "class" => $class,
            "hide" => $hide,
            "border" => $border,
            "none" => $none
        ];
    }

    /*
     * show search list
     * */
    public function outSearchList($list, $error, $mas, $article_nr_search, $brand_nr_search, $other_storages, $view, $saleout = 0)
    {
        $ll = $other_storages["content"];
        $class = $other_storages["class"];
        $hide = $other_storages["hide"];
        $border = $other_storages["border"];
        $none = $other_storages["none"];

        (!$view) ?: $list .= "<div class='row'>";

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

        if (!empty($mas)) {
            foreach ($mas as $mas_key => $mas_val) {
                foreach ($mas_val as $key => $val) {
                    $art_id = $mas_key;
                    $name = $val["name"];
                    $brand_id = $val["brand_id"];
                    $brand = $val["brand"];
                    $text = $val["text"];
                    $stock = $val["stock"];
                    $delivery_info = $val["delivery_info"];
                    $price = $val["price"];
                    $delivery_days = $val["delivery_days"];
                    $delivery_short_info = $val["delivery_short_info"];
                    $suppl_id = $val["suppl_id"];
                    $return_days = $val["return_days"];
                    $storage_id = $val["storage_id"];
                    $status = ($saleout > 0) ? $val["status"] : 1;
                    if ($view && ($i == $faq_pos)) {
                        $faq_form = $this->getFaqForm();
                        $list .= $faq_form;
                    }
                    $list .= $this->printSearchList($i, $art_id, $name, $brand_id, $brand, $text, $delivery_info, $stock, $price, $article_nr_search, $ll[$i], $class[$i], $hide[$i], $border[$i], $none[$i], $brand_nr_search, $suppl_id, $return_days, $delivery_days, $delivery_short_info, $storage_id, $status, $view);
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

//    function triggerDetailCar($type_id, $year, $manufacture, $model, $model_id, $typ_id, $str_id) {
//        $automan = new AutoClass;
//        $skip_id = 0;
//        switch ($type_id) {
//            case 0: { $form = $automan->showTabCatalogueYear(1, $manufacture, $model); break; }
//            case 1: { $form = $automan->showTabCatalogueManufacture($year, 1); break; }
//            case 2: { $form = $automan->showTabCatalogueModel($manufacture, $year, 1); break; }
//            case 3: {
//                $model_id = $automan->skipShowTabCatalogueModelId($model, $manufacture, $year);
//                if (!$model_id) {
//                    $form = $automan->showTabCatalogueModelId($model, $manufacture, $year, 1);
//                } else {
//                    $str_id!=="" ? $onclick=1 : $onclick="";
//                    $form = $automan->showTabCatalogueGroup($model_id, $model, $manufacture, $year);
//                    $skip_id = $model_id;
//                }
//                break;
//            }
//            case 4: {
//                $str_id!=="" ? $onclick=1 : $onclick="";
//                $form = $automan->showTabCatalogueGroup($model_id, $model, $manufacture, $year); break;
//            }
//            default: { $form = "Something wrong!"; break; }
//        }
//        if ($year=="all") $form = $automan->showTabCatalogueYear(1);
//        list($manufacture_text,, $model_id_cap, $typ_text) = $automan->getAutoDescr($manufacture, $model, $model_id, $typ_id);
//        list($t_mf, $t_md, $t_mi,) = $automan->getAutoDescr($manufacture, $model, $model_id, $typ_id);
//        $cat_text = "";
//        if ($t_mf!="") $cat_text = " $t_mf";
//        if ($t_md!="") $cat_text = " $t_mf $t_md";
//        if ($t_mi!="") $cat_text = " $t_mf $t_mi";
//        $str_text = $automan->getStrDescr($str_id);
//        $title = "$str_text {for_cap} $manufacture_text $model_id_cap $typ_text | {site_title_short}";
//        if ($str_id=="")  {
//            $title = "$manufacture_text $model_id_cap $typ_text | {site_title_short}";
//            $str_text = "$manufacture_text $model_id_cap $typ_text";
//        }
//        $str_text = $this->formatUrlText($str_text);
//
//        $title = $this->replaceLang($title);
//        return array($form, $str_text, $cat_text, $skip_id, $title);
//    }

    public function searchListTest($where_art_id_str, $type_filter = 1, $view = 0)
    {
        // DELETE ????
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
        $mas = [];
        list($error, , $list) = $this->getSearchMessages($type_filter);

        if ($where_art_id_str != "") {
            $this->createTemporarySearchTable($temp_key);
            $r = $db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2asc.AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
                LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
            WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
            UNION ALL
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2si.stock_suppl as AMOUNT, t2si.client_storage_id as storage_id, t2si.suppl_id, t2si.return_delay
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
                LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
            WHERE t2a.ART_ID IN ($where_art_id_str) AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
            GROUP BY t2a.ART_ID, t2si.client_storage_id");
            $n = $db->num_rows($r);
            $list = $this->drawHeaderSearchList($type_filter, $view);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "ART_ID");
                    $brand_id = $db->result($r, $i - 1, "BRAND_ID");
                    $brand = $db->result($r, $i - 1, "BRAND_NAME");
                    $name = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $text = $db->result($r, $i - 1, "NAME");
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

                    $status = 0;
                    if ($price > 0) {
                        if ($stock > 0) {
                            if ($suppl_id == 0) {
                                $status = 1;
                            } elseif ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $status = 1;
                            }
                        }
                    }

                    $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `name`, `brand_id`, `brand`, `text`, `del`, `stock`, `price`, `dd`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                    VALUES ('$art_id', '$name', '$brand_id', '$brand', '$text', '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `name` ASC;");
                $n = $db->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "art_id");
                    $name = $db->result($r, $i - 1, "name");
                    $brand_id = $db->result($r, $i - 1, "brand_id");
                    $brand = $db->result($r, $i - 1, "brand");
                    $text = $db->result($r, $i - 1, "text");
                    $delivery_info = $db->result($r, $i - 1, "del");
                    $stock = $db->result($r, $i - 1, "stock");
                    $price = $db->result($r, $i - 1, "price");
                    $delivery_days = $db->result($r, $i - 1, "delivery_days");
                    $delivery_short_info = $db->result($r, $i - 1, "delivery_short_info");
                    $suppl_id = $db->result($r, $i - 1, "suppl_id");
                    $return_days = $db->result($r, $i - 1, "return_days");
                    $storage_id = $db->result($r, $i - 1, "storage_id");
                    $status = $db->result($r, $i - 1, "status");
                    $mas[$art_id][$i] = [
                        "name" => $name,
                        "brand_id" => $brand_id,
                        "brand" => $brand,
                        "text" => $text,
                        "delivery_info" => $delivery_info,
                        "stock" => $stock,
                        "price" => $price,
                        "delivery_days" => $delivery_days,
                        "delivery_short_info" => $delivery_short_info,
                        "suppl_id" => $suppl_id,
                        "return_days" => $return_days,
                        "storage_id" => $storage_id,
                        "status" => $status
                    ];
                }

                // delete temp table
                $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, $view);

                // show search list
                $list = $this->outSearchList($list, $error, $mas, "", "", $other_storages, $view, 1);
            }

            if (count($mas) < 1) {
                $list = "$error";
            }
        }

        return array($list);
    }

}

function myBrandCmp($a, $b) {
   if ($a["count"] == $b["count"]) return 0;
   return $a["count"] < $b["count"] ? 1 : -1;
}

function cmpPrice($a, $b) {
    if (floatval($a["price"]) == floatval($b["price"])) return 0;
    return floatval($a["price"]) > floatval($b["price"]) ? 1 : -1;
}

function cmpChecked($a, $b) {
    if ($a["checked"] == $b["checked"]) return 0;
    return $a["checked"] > $b["checked"] ? 1 : -1;
}
