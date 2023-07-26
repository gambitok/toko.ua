<?php

class SearchClass extends CatalogueClass
{

    /*
     * SEARCH FORM
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
            $brand_id       = (int)$db->result($r, $i - 1, "BRAND_ID");
        }

        if ($n === 1) {
            return $this->getCatalogList($article_search, $brand_id);
        }

        return $this->getSearchResult($article_search, $article_nr_search);
    }

    /*
     * SEARCH ERROR FORM
     * */
    public function getSearchResult($article_search, $article_nr_search)
    {
        if ($article_search !== "") {
            $form = $this->getBrandList($article_search, $article_nr_search);
        } else {
            $list = $this->showSearchDropdown($article_nr_search);

            if ($list === "") {
                $form = $this->getHtmlForm("error/search_unknown");
            } else {
                $form = $this->getHtmlForm("search/search_catalog");
                $form = str_replace(array("{search_query}", "{search_range}"), array($article_nr_search, $list), $form);
            }
        }

        return $form;
    }

    /*
     * SEARCH BRAND FORM
     * */
    public function getBrandList($article_search, $article_nr_search)
    {
        $db = DbSingleton::getTokoDb();
        $showform = new FormClass();

        $count_zero = $exist_search_number = 0;
        $exist_brand_link = $result = $list = "";
        $mas = [];

        $form           = $this->getHtmlForm("search/brand_options_form");
        $form_brand     = $this->getHtmlForm("search/brand_options_list");
        $search_form    = $this->getHtmlForm("search/brand_options");

        $r = $db->query("SELECT t2c.ART_ID, t2c.BRAND_ID, t2c.SEARCH_NUMBER, t2c.DISPLAY_NR, t2c.KIND, t2c.RELATION, t2b.BRAND_NAME, t2b.BRAND_LINK, IFNULL(t2n.NAME,'') as NAME 
        FROM `T2_CROSS` t2c 
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID) 	
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_search' AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END) 
        GROUP BY t2c.BRAND_ID;");
        $n = $db->num_rows($r);

        if ($article_search !== "") {
            for ($i = 1; $i <= $n; $i++) {
                $art_id     = $db->result($r, $i - 1, "ART_ID");
                $search_nr  = $db->result($r, $i - 1, "SEARCH_NUMBER");
                $art_nr_ds  = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_id   = $db->result($r, $i - 1, "BRAND_ID");
                $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                $brand_link = $db->result($r, $i - 1, "BRAND_LINK");
                $art_name   = $db->result($r, $i - 1, "NAME");
                $count      = $this->countBrandItems($search_nr, $brand_id);

                if ($count === 0) {
                    $count_zero++;
                } else {
                    $exist_search_number    = strtolower($search_nr);
                    $exist_brand_link       = $brand_link;
                }
                $mas[$i] = compact("search_nr", "art_nr_ds", "brand_id", "brand_name", "brand_link", "count", "art_name", "art_id");
            }
            $nophoto = $this->noPhoto;
            usort($mas, "myBrandCmp");

            for ($i = 0; $i < $n; $i++) {
                $search_nr  = strtolower($mas[$i]["search_nr"]);
                $art_nr_ds  = $mas[$i]["art_nr_ds"];
                $brand_name = $mas[$i]["brand_name"];
                $brand_link = $mas[$i]["brand_link"];
                $count      = $mas[$i]["count"];
                $art_name   = $mas[$i]["art_name"];
                $photo_name = $showform->getArticleActivePhoto($mas[$i]["art_id"]);
                $link       = ($count === 0)
                    ? "showAlertModal(\"{brand_no_offer} `$art_nr_ds/$brand_name`\",\"{sorry_cap}\");"
                    : "location.href=\"" . $this->getSiteLink() . "$this->search_link/$search_nr/$brand_link/\";";

                $list .= "
                <tr onclick='$link'>
                    <td class=\"minify\">
                        <img itemprop=\"image\" data-src=\"$photo_name\" class=\"lazy\" alt=\"$art_nr_ds\" src=\"$nophoto\">
                    </td>
                    <td>$art_nr_ds</td>
                    <td>$brand_name</td>
                    <td>$art_name</td>
                    <td>$count</td>
                </tr>";
            }
            $form_brand = str_replace("{brand_list}", $list, $form_brand);
        } else {
            $result_index = "<br><span class=\"span-search text-uppercase\">{search_result_for} <b class=\"span-dark-red\">$article_nr_search</b> {nothing_found}</span>
            <br><br><p class=\"span-search\">{check_the_data}</p>";
            $search_form = str_replace(array("{search_results}", "{search_result_index}", "{search_result}"), array("{offers_request}", $result_index, ""), $search_form);
        }

        $result_index = "<span class=\"span-brand-search\">{search_request} <b>$article_search</b> {search_result_for_end}</span>";
        $search_form = str_replace(array("{search_results}", "{search_result_index}", "{art}", "{currency}", "{products_view}", "{search_result}"), array("{choose_brand_manuf}", $result_index, $result, "", "", $form_brand), $search_form);

        $form = str_replace(array("{search_filters}", "{search_form}"), array("", $search_form), $form);

        if ($count_zero === ($n - 1)) {
            header("Location: " . $this->getSiteLink() . "$this->search_link/$exist_search_number/$exist_brand_link/");
        }

        return $form;
    }

    /*
     * SEARCH COUNT BY BRAND
     * */
    public function countBrandItems($article_nr_search, $brand_id): int
    {
        $db = DbSingleton::getTokoDb();

        $art_ids    = [];
        $brand_id   = $this->getUrlNumber($brand_id);

        $r = $db->query("SELECT t2c.ART_ID 
        FROM `T2_CROSS` t2c
            LEFT OUTER JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID = t2c.BRAND_ID)
            LEFT OUTER JOIN `T2_NAMES` t2n ON (t2n.ART_ID = t2c.ART_ID)
        WHERE t2c.SEARCH_NUMBER = '$article_nr_search' AND t2c.BRAND_ID = $brand_id AND (CASE WHEN t2n.LANG_ID != NULL THEN t2n.LANG_ID = 16 ELSE TRUE END);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_ids[] = $db->result($r, $i - 1, "ART_ID");
        }
        $art_id_str = implode(",", $art_ids);

        return $this->searchListCount($art_id_str, $article_nr_search, $brand_id);
    }

    public function searchListCount($where_art_id_str, $article_nr_search = "", $brand_nr_search = ""): int
    {
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();

        $client_id  = $this->getClient();
        $tpoint_id  = $this->getTpointID();
        $cur        = $this->getCurrentExrate();

        session_start();
        $temp_key  = session_id();
        $mas = [];
        $count = 0;

        if ($where_art_id_str !== "") {
            $this->createTemporarySearchTable($temp_key);
            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search);
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
                    $format_name        = $this->getFormatAticle($article_nr_displ);

                    $price = $this->getArticlePrice($art_id);
                    if ($suppl_id > 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }
                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }

                    $deliveryData           = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
                    $delivery_info          = $deliveryData["info"];
                    $delivery_days          = (int)$deliveryData["days"];
                    $delivery_short_info    = $deliveryData["short"];

                    if ($suppl_id > 0) {
                        $deliveryData           = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = (int)$deliveryData["days"];
                        $delivery_short_info    = $deliveryData["short"];
                    }

                    if (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id === 0) ? 1 : 0;
                    }

                    if ($price > 0 || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                        if ($stock > 0 || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', \"$article_name\", '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");
                            }
                        }
                    }
                }

                $r = $db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY `status` DESC, `article_nr_displ`;");
                $n = $db->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $art_id                 = $db->result($r, $i - 1, "art_id");
                    $article_nr_displ       = $db->result($r, $i - 1, "article_nr_displ");
                    $brand_id               = $db->result($r, $i - 1, "brand_id");
                    $brand_name             = $db->result($r, $i - 1, "brand_name");
                    $article_name           = $db->result($r, $i - 1, "article_name");
                    $delivery_days          = $db->result($r, $i - 1, "delivery_days");
                    $delivery_info          = $db->result($r, $i - 1, "delivery_info");
                    $delivery_short_info    = $db->result($r, $i - 1, "delivery_short_info");
                    $stock                  = $db->result($r, $i - 1, "stock");
                    $price                  = $db->result($r, $i - 1, "price");
                    $suppl_id               = $db->result($r, $i - 1, "suppl_id");
                    $storage_id             = $db->result($r, $i - 1, "storage_id");
                    $return_days            = $db->result($r, $i - 1, "return_days");
                    $status                 = $db->result($r, $i - 1, "status");

                    $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
                }

                $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

                $mas = $this->deleteEmptyPosition($mas);
                $mas = $this->deleteSupplPosition($mas);
                $mas = $this->deleteRepeatPosition($mas);

                foreach ($mas as $mas_key => $mas_val) {
                    $mas[$mas_key] = $this->multiSort($mas[$mas_key], "delivery_days", "price");
                }

                $mas = $this->sortByMinStock($mas);
            }

            $count = count($mas);
        }

        return $count;
    }

    /*
     * SEARCH TEXT INPUT
     * */
    public function getSearchMatches($text): array
    {
        $text = mb_strtolower($text, 'windows-1251');
        $max_word = 4;
        $arr = [];

        if ($text !== "") {
            $text_arr = explode(" ", $text);
            $i = 0;
            foreach ($text_arr as $value) {
                $i++;
                if (mb_strlen($value) > 1) {
                    $arr[$i][] = $value;
                    if (mb_strlen($value) > $max_word) {
                        $format_value = substr($value, 0, -2);
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
                    $key_id     = (int)$db->result($r, $i - 1, "KEY_ID");
                    $type_id    = (int)$db->result($r, $i - 1, "TYPE_ID");
                    $str_count  = (int)$db->result($r, $i - 1, "str_count");
                    $k          = $key_id . "_" . $type_id;

                    if (!array_key_exists($k, $new)) {
                        $new[$k] = ["key_id" => $key_id, "type_id" => $type_id, "str_count" => $str_count];
                    } else {
                        $new[$k]["str_count"] += $str_count;
                    }

                    if (!in_array($key, (array)$new[$k]["key"], true)) {
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

    /*
     * SEARCH TEXT INPUT
     * */
    public function showSearchDropdown($text)
    {
        $db = DbSingleton::getTokoDb();
        $showform = new FormClass();

        $list = $list1 = $list2 = $list3 = "";
        if ($text === "") {
            $list = $showform->showHistoryList();
        }

        if ($text !== "" && mb_strlen($text) > 1) {
            $text = $this->getUrlString($text);
            $format_text = $text;
            $format_text = str_replace(str_split(' -,+\/:*?"<>|_.'), "", $format_text);

            $r = $db->query("SELECT `ART_ID`, `BRAND_ID`, `DISPLAY_NR`, MIN(`KIND`) as min_kind 
            FROM `T2_CROSS` 
            WHERE `SEARCH_NUMBER` = '$format_text' 
            GROUP BY `BRAND_ID` 
            ORDER BY `min_kind`;");
            $n1 = $db->num_rows($r);
            for ($i = 1; $i <= $n1; $i++) {
                $art_id     = $db->result($r, $i - 1, "ART_ID");
                $brand_id   = $db->result($r, $i - 1, "BRAND_ID");
                $min_kind   = $db->result($r, $i - 1, "min_kind");
                $display_nr = $db->result($r, $i - 1, "DISPLAY_NR");
                $brand_name = $this->getBrandName($brand_id);
                $brand_link = $this->getBrandLink($brand_id);
                $format_name = $this->getFormatAticle($display_nr);

                if ($min_kind === "0") {
                    $article_name   = $this->getArticleName($art_id);
                    $link           = $this->getSiteLink() . $this->search_link . "/" . $format_name . "/" . $brand_link . "/";
                    $str            = "$brand_name $display_nr $article_name";
                } else {
                    $link           = $this->getSiteLink() . $this->search_link . "/" . $format_name . "/" . $brand_link . "/";
                    $str            = "$brand_name $display_nr";
                }

                $list1 .= "
                <li>
                    <a href='$link'>$str </a>
                </li>";
            }

            $text = str_replace(str_split('+\/:*?"<>|'), "", $text);
            list($arr, $max_matches) = $this->getSearchMatches($text);

            $n = count($arr);
            foreach ($arr as $value) {
                $key_id     = $value["key_id"];
                $type_id    = $value["type_id"];
                $key        = $value["key"];

                if (count($key) >= $max_matches) {
                    if ($type_id === 1) {
                        $key_name   = $this->getGroupRowText($key_id);
                        $key_link   = $this->getGroupRowLink($key_id);
                        $link       = $this->getSiteLink() . $this->catalog_link . "/" . $key_link . "/";

                        $list2 .= "
                        <li>
                            <a href='$link'>$key_name</a>
                        <li>";
                    }
                    elseif ($type_id === 2) {
                        $catData    = $this->getCatRowData($key_id);
                        $key_name   = $catData["cat_name"];
                        $key_link   = $catData["cat_link"];
                        $head_id    = $this->getHeadCatRow($key_id);
                        $head_link  = $this->getHeadRowLink($head_id);
                        $link       = $this->getSiteLink() . $this->catalog_link . "/" . $head_link . "/" . $key_link . "/";

                        $list3 .= "
                        <li>
                            <a href='$link'>$key_name</a>
                        <li>";
                    }
                    elseif ($type_id === 3) {
                        $key_name   = $this->getHeadRowName($key_id);
                        $key_link   = $this->getHeadRowLink($key_id);
                        $link       = $this->getSiteLink() . $this->catalog_link . "/" . $key_link . "/";

                        $list3 .= "
                        <li>
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

                    if ($list2 !== "") {
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

                    if ($list3 !== "") {
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

    public function getUserStatusNulls($user_id): int
    {
        $status = 0;
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `status_nulls` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $status = (int)$db->result($r, 0, "status_nulls");
        }

        return $status;
    }

    /*
     * search position on top
     * sort - our position on top
     * delete suppl if we have our positions with `in_stock`
     * */
    public function sortSuppls($mas, $art_id_search): array
    {
        $arr = []; $mas2 = [];

        $mas_search = $mas[$art_id_search];
        unset($mas[$art_id_search]);

        foreach ($mas as $mas_key => $mas_val) {
            $i = 0;
            foreach ($mas_val as $key => $val) {
                if ($i === 0) {
                    $arr[$mas_key] = (int)$val["suppl_id"];
                }
                $i++;
            }
        }

        asort($arr);
        $arr = array_keys($arr);

        $mas2[$art_id_search] = $mas_search;

        foreach ($arr as $val) {
            $mas2[$val] = $mas[$val];
        }

        $del_arts = [];
        foreach ($mas2 as $mas_key => $mas_val) {
            $i = 0;
            foreach ($mas_val as $key => $val) {
                if ($i === 0 && (int)$val["suppl_id"] === 0 && (int)$val["stock"] > 0 && count($mas_val) > 1) {
                    $del_arts[] = $mas_key;
                }
            }
        }

        foreach ($del_arts as $art_id) {
            foreach ($mas2[$art_id] as $key => $val) {
                if ((int)$val["suppl_id"] > 0) {
                    unset($mas2[$art_id][$key]);
                }
            }
        }

        return $mas2;
    }

    public function getRealStock($art_id): int
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ID` FROM `T2_ARTICLES_PRICE_STOCK` WHERE `ART_ID` = $art_id LIMIT 1;");
        $n1 = $db->num_rows($r);

        $r = $db->query("SELECT `ID` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` = $art_id LIMIT 1;");
        $n2 = $db->num_rows($r);

        $res = 0;
        if ($n1 > 0 || $n2 > 0) {
            $res = 1;
        }

        return $res;
    }

    /*
     * CATALOG
     * */
    public function searchList($where_art_id_str, $article_nr_search = "", $brand_nr_search = ""): array
    {
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();

        $nulls = 0;
        $mas_nulls = [];
        if ($this->getUserStatusNulls($this->getUser())) {
            $nulls = 1;
        }

        $client_id  = $this->getClient();
        $tpoint_id  = $this->getTpointID();
        $cur        = $this->getCurrentExrate();

        session_start();
        $temp_key               = session_id();
        $mas                    = $filters = $brands = [];
        $list_brand             = "";
        $art_id_search          = 0;
        $filters["max_price"]   = $filters["max_dd"] = $main_brand = 0;
        $filters["min_price"]   = 99999999;

        if ($article_nr_search !== "") {
            $art_id_search = $this->getArticleId($article_nr_search, $brand_nr_search);
        }

        list($error, $list) = $this->getSearchMessages();

        if (!empty($where_art_id_str)) {
            $this->createTemporarySearchTable($temp_key);

            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, "", $nulls);
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
                    $format_name        = $this->getFormatAticle($article_nr_displ);

                    // price
                    $price = $this->getArticlePrice($art_id);
                    if ($suppl_id > 0) {
                        $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    }

                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }
                    $filter_price = $price;

                    // delivery
                    $deliveryData           = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
                    $delivery_info          = $deliveryData["info"];
                    $delivery_days          = (int)$deliveryData["days"];
                    $delivery_short_info    = $deliveryData["short"];

                    if ($suppl_id > 0) {
                        $deliveryData           = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = (int)$deliveryData["days"];
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
                    if (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id === 0) ? 1 : 0;
                    }

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if (($price > 0 || $nulls === 1) || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                        if (($stock > 0 || $nulls === 1) || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                            // visible suppl storage
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `article_nr_displ`, `brand_id`, `brand_name`, `article_name`, `delivery_info`, `stock`, `price`, `delivery_days`, `delivery_short_info`, `suppl_id`, `return_days`, `status`, `storage_id`) 
                                VALUES ('$art_id', '$article_nr_displ', '$brand_id', '$brand_name', \"$article_name\", '$delivery_info', $stock, $price, '$delivery_days', '$delivery_short_info', '$suppl_id', '$return_days', '$status', '$storage_id');");

                                if ($art_id === $art_id_search) {
                                    $main_brand = $brand_id;
                                }

                                if ($brand_name !== "") {
                                    if (($stock > 0 && $price > 0) || ($nulls === 1 && $price > 0)) {
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

                if (empty($mas)) {
                    $list = $this->getHtmlForm("error/nothing_found");
                    $list = str_replace("{error_nothing_found}", $this->err1, $list);
                    return array($list, "", "", 0);
                }

                // sort selected art
                if (!empty($mas[$art_id_search])) {
                    $mas[$art_id_search] = $this->deleteEmptyPositionMain($mas[$art_id_search]);
                }

                // delete nulls
                if ($nulls === 1) {
                    list($mas, $mas_nulls) = $this->deleteEmptyNulls($mas);
                }

                // sort by delivery and price
                foreach ($mas as $mas_key => $mas_val) {
                    $mas[$mas_key] = $this->multiSort($mas[$mas_key], "delivery_days", "price");
                }

                // sort like: first = min delivery, second = min price, else = default
                $mas = $this->sortByMinStock($mas);

                // $mas[$art_id1][0] = ['suppl_id' => 1]
                // $mas[$art_id2][0] = ['suppl_id' => 0]

                $mas = $this->sortSuppls($mas, $art_id_search);

                // sort by delivery days and price (all analogs list)
                $arr = [];
                foreach ($mas as $mas_key => $mas_val) {
                    if ($mas_key !== $art_id_search) {
                        $arr[$mas_key] = $mas[$mas_key][0];
                        $arr[$mas_key]['ART_ID'] = $mas_key;
                    }
                }

                $mas0[$art_id_search] = $mas[$art_id_search];
                $mas1 = [];
                $mas2 = [];
                foreach ($arr as $val) {
                    $art = $val['ART_ID'];
                    if ($art != $art_id_search) {
                        if ((int)$val['delivery_days'] === 0 && (int)$val['suppl_id'] === 0) {
                            $mas1[$art] = $mas[$art];
                        } else {
                            $mas2[$art] = $mas[$art];
                        }
                    }
                }

                $arr2 = [];
                foreach ($mas2 as $mas_key => $mas_val) {
                    if ($mas_key !== $art_id_search) {
                        $arr2[$mas_key] = $mas2[$mas_key][0];
                        $arr2[$mas_key]['ART_ID'] = $mas_key;
                    }
                }
                $sort = array();
                foreach($arr2 as $k=>$v) {
                    $sort['price'][$k] = $v['price'];
                    $sort['delivery_days'][$k] = $v['delivery_days'];
                }
                array_multisort($sort['price'], SORT_ASC, $sort['delivery_days'], SORT_ASC, $arr2);

                $mas22 = [];
                foreach ($arr2 as $val) {
                    $art = $val['ART_ID'];
                    $mas22[$art] = $mas2[$art];
                }

                if (!empty($mas0)) {
                    $mas1 = $mas0 + $mas1;
                }
                if (!empty($mas1)) {
                    $mas22 = $mas1 + $mas22;
                }
                $mas = $mas22;

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, 0);

                // show search list
                $list = $this->outSearchList3($list, $error, $mas, $art_id_search, $article_nr_search, $brand_nr_search, $other_storages, $nulls, $mas_nulls);

                // get filter brand list
                $list_brand = $this->getListBrand($brands, $main_brand, $cur);
            }

            if (count($mas) < 1) {
                $list                   = $error;
                $list_brand             = "";
                $filters                = [];
                $filters["max_price"]   = 0;
                $filters["max_dd"]      = 0;
            }

        }

        return array($list, $list_brand, $filters);
    }

    /*
     * CATALOG FILTER
     * */
    public function searchListFilter($where_art_id_str, $article_nr_search, $brand_filter, $cur, $price_min, $price_max, $del_min, $del_max, $brand_nr_search): array
    {
        $db = DbSingleton::getTokoDb();
        $kours = new ExRateClass();
        $client = new ClientClass();

        $nulls = 0;
        $mas_nulls = [];
        if ($this->getUserStatusNulls($this->getUser())) {
            $nulls = 1;
        }

        $client_id  = $this->getClient();
        $tpoint_id  = $this->getTpointID();
        $mas        = $filters = $brands = $current_value = array();
        $error      = $this->replaceLang("<h5 class=\"error_message\">$this->err1</h5>");
        $list       = $list_brand = "";

        $filters["max_price"] = $filters["max_dd"] = $main_brand = $art_id_search = 0;

        if ($article_nr_search !== "") {
            $art_id_search = $this->getArticleId($article_nr_search, $brand_nr_search);
        }

        if ($brand_filter !== "") {
            $brand_filter = str_replace("'", "", $brand_filter);
            $where_brands = " AND t2a.BRAND_ID IN ($brand_filter) ";
        } else {
            $where_brands = "";
        }

        if ($where_art_id_str !== "") {
            $articlePrices      = $this->getArticlePrices($where_art_id_str);
            $deliverInfo        = $this->getTpointDeliveryInfos($tpoint_id, $where_art_id_str);
            $articleSupplPrices = $this->getArticleSupplPrices($where_art_id_str);
            $supplDeliverInfo   = $this->getTpointSupplDeliveriesInfo($tpoint_id);

            $r = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search, $where_brands);
            $n = $db->num_rows($r);

            if ($where_brands === "") {
                $rs = $r;
                $ns = $n;
            } else {
                $rs = $this->getTemporarySearchTable($where_art_id_str, $article_nr_search, $brand_nr_search);
                $ns = $db->num_rows($rs);
            }

            if ($ns > 0) {
                // filters with default search
                for ($i = 1; $i <= $ns; $i++) {
                    $art_id             = (int)$db->result($rs, $i - 1, "ART_ID");
                    $brand_id           = (int)$db->result($rs, $i - 1, "BRAND_ID");
                    $brand_name         = $db->result($rs, $i - 1, "BRAND_NAME");
                    $article_nr_displ   = $db->result($rs, $i - 1, "ARTICLE_NR_DISPL");
                    $stock              = (int)$db->result($rs, $i - 1, "AMOUNT");
                    $suppl_id           = (int)$db->result($rs, $i - 1, "suppl_id");
                    $storage_id         = (int)$db->result($rs, $i - 1, "storage_id");
                    $format_name        = $this->getFormatAticle($article_nr_displ);

                    $price          = $articlePrices[$art_id] ?? 0;
                    $delivery_days  = $deliverInfo[$storage_id]["delivery_days"] ?? 0;

                    if ($suppl_id > 0) {
                        $price          = $articleSupplPrices[$art_id][$suppl_id][$storage_id];
                        $deliveryData   = $supplDeliverInfo[$suppl_id][$storage_id] ?? [
                                "info"                  => $this->err2,
                                "delivery_days"         => 0,
                                "delivery_short_info"   => $this->err2
                            ];
                        $delivery_days = $deliveryData["delivery_days"];
                    }

                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }
                    $filter_price = $price;

                    if ($filter_price > $filters["max_price"]) {
                        $filters["max_price"] = ceil($filter_price);
                    }
                    if ($delivery_days > $filters["max_dd"]) {
                        $filters["max_dd"] = $delivery_days;
                    }

                    if ($price > 0 || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                        if ($stock > 0 || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                            if ($art_id === $art_id_search) {
                                $main_brand = $brand_id;
                            }
                            if (($brand_name !== "") && $stock > 0 && $price > 0) {
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

            // get filter brand list
            $list_brand = $this->getListBrand($brands, $main_brand, $cur, $brand_filter);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $art_id             = (int)$db->result($r, $i - 1, "ART_ID");
                    $brand_id           = (int)$db->result($r, $i - 1, "BRAND_ID");
                    $brand_name         = $db->result($r, $i - 1, "BRAND_NAME");
                    $suppl_id           = (int)$db->result($r, $i - 1, "suppl_id");
                    $article_nr_displ   = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
                    $article_name       = $db->result($r, $i - 1, "NAME");
                    $return_days        = (int)$db->result($r, $i - 1, "return_delay");
                    $stock              = (int)$db->result($r, $i - 1, "AMOUNT");
                    $storage_id         = (int)$db->result($r, $i - 1, "storage_id");
                    $format_name        = $this->getFormatAticle($article_nr_displ);

                    $price = $articlePrices[$art_id] ?? 0;
                    if ($suppl_id > 0) {
                        $price = $articleSupplPrices[$art_id][$suppl_id][$storage_id];
                    }
                    $price = $kours->getKoursPrice($price, $cur);
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }
                    $filter_price = $price;

                    $delivery_info          = $deliverInfo[$storage_id]["info"];
                    $delivery_days          = $deliverInfo[$storage_id]["delivery_days"];
                    $delivery_short_info    = $deliverInfo[$storage_id]["delivery_short_info"];

                    if ($suppl_id > 0) {
                        $deliveryData = $supplDeliverInfo[$suppl_id][$storage_id] ?? [
                                "info"                  => $this->err2,
                                "delivery_days"         => 0,
                                "delivery_short_info"   => $this->err2
                            ];
                        $delivery_info          = $deliveryData["info"];
                        $delivery_days          = $deliveryData["delivery_days"];
                        $delivery_short_info    = $deliveryData["delivery_short_info"];
                    }

                    if ($filter_price > $filters["max_price"]) {
                        $filters["max_price"] = ceil($filter_price);
                    }
                    $current_value["min_price"] = $price_min;
                    $current_value["max_price"] = $price_max;
                    $current_value["min_dd"]    = $del_min;
                    $current_value["max_dd"]    = $del_max;

                    if (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search) {
                        $status = 2;
                    } else {
                        $status = ($suppl_id === 0) ? 1 : 0;
                    }

                    // show articles with suppl_id=0 or with price!=0 and stock!=0
                    if (($price > 0 || $nulls === 1) || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                        if (($stock > 0 || $nulls === 1) || (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search)) {
                            // visible suppl storage
                            if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                                $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");

                            }
                        }
                    }

//                    if (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && $brand_id === $brand_nr_search) {
//                        $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
//                    }
//                    elseif ($stock > 0) {
//                        if ($price >= $price_min && $price <= $price_max && $delivery_days >= $del_min && $delivery_days <= $del_max) {
//                            $mas[$art_id][$i] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "delivery_info", "stock", "price", "delivery_days", "delivery_short_info", "suppl_id", "return_days", "storage_id", "status");
//                        }
//                    }
                }

                if (empty($mas)) {
                    $list = $this->getHtmlForm("error/nothing_found");
                    $list = str_replace("{error_nothing_found}", $this->err1, $list);
                    return array($list, "", "", 0);
                }

                if (!empty($mas[$art_id_search])) {
                    $mas[$art_id_search] = $this->deleteEmptyPositionMain($mas[$art_id_search]);
                }

                // delete nulls
                if ($nulls === 1) {
                    list($mas, $mas_nulls) = $this->deleteEmptyNulls($mas);
                }

                // sort by delivery and price
                foreach ($mas as $mas_key => $mas_val) {
                    $mas[$mas_key] = $this->multiSort($mas[$mas_key], "delivery_days", "price");
                }

                // sort like: first = min delivery, second = min price, else = default
                $mas = $this->sortByMinStock($mas);

                // $mas[$art_id1][0] = ['suppl_id' => 1]
                // $mas[$art_id2][0] = ['suppl_id' => 0]
                $mas = $this->sortSuppls($mas, $art_id_search);

                // sort by delivery days and price (all analogs list)
                $arr = [];
                foreach ($mas as $mas_key => $mas_val) {
                    if ($mas_key !== $art_id_search) {
                        $arr[$mas_key] = $mas[$mas_key][0];
                        $arr[$mas_key]['ART_ID'] = $mas_key;
                    }
                }

                $mas0[$art_id_search] = $mas[$art_id_search];
                $mas1 = [];
                $mas2 = [];
                foreach ($arr as $val) {
                    $art = $val['ART_ID'];
                    if ($art != $art_id_search) {
                        if ((int)$val['delivery_days'] === 0 && (int)$val['suppl_id'] === 0) {
                            $mas1[$art] = $mas[$art];
                        } else {
                            $mas2[$art] = $mas[$art];
                        }
                    }
                }

                $arr2 = [];
                foreach ($mas2 as $mas_key => $mas_val) {
                    if ($mas_key !== $art_id_search) {
                        $arr2[$mas_key] = $mas2[$mas_key][0];
                        $arr2[$mas_key]['ART_ID'] = $mas_key;
                    }
                }
                $sort = array();
                foreach($arr2 as $k=>$v) {
                    $sort['price'][$k] = $v['price'];
                    $sort['delivery_days'][$k] = $v['delivery_days'];
                }
                array_multisort($sort['price'], SORT_ASC, $sort['delivery_days'], SORT_ASC, $arr2);

                $mas22 = [];
                foreach ($arr2 as $val) {
                    $art = $val['ART_ID'];
                    $mas22[$art] = $mas2[$art];
                }

                if (!empty($mas0)) {
                    $mas1 = $mas0 + $mas1;
                }
                if (!empty($mas1)) {
                    $mas22 = $mas1 + $mas22;
                }
                $mas = $mas22;

                // show other storages
                $other_storages = $this->showOtherStorages($mas, $cur, 0);

                // show search list
                FormClass::cacheArticlesPhotos($where_art_id_str);
                FormClass::cacheInfoTemplates($where_art_id_str);
                $list = $this->outSearchList3($list, $error, $mas, $art_id_search, $article_nr_search, $brand_nr_search, $other_storages, $nulls, $mas_nulls);
            }

            if (count($mas) === 0) {
                $list = $error;
            }
        }

        return array($list, $filters, $list_brand, $current_value);
    }

    public function outSearchList3($list, $error, $mas, $art_id_search, $article_nr_search, $brand_nr_search, $other_storages, $nulls = 0, $mas_nulls = [])
    {
        $cont   = $other_storages["content"];
        $class  = $other_storages["class"];
        $hide   = $other_storages["hide"];
        $border = $other_storages["border"];
        $none   = $other_storages["none"];

        $list_target    = "";
        $style_target   = "none";
        $style          = "";
        $i              = 0;
        $check          = 0;
        $dataArt        = $this->getArtDispl($article_nr_search, $brand_nr_search);

        if (!empty($mas)) {

            if (!empty($mas[$art_id_search])) {
                $style_target   = "";
                $check          = 1;
            }

            foreach ($mas[$art_id_search] as $val) {
                $art_id     = $art_id_search;
                $art_nr_ds  = $val["article_nr_displ"];
                $brand_id   = $val["brand_id"];
                $brand_name = $val["brand_name"];
                $art_name   = $val["article_name"];
                $stock      = $val["stock"];
                $del_info   = $val["delivery_info"];
                $price      = $val["price"];
                $del_days   = $val["delivery_days"];
                $del_short  = $val["delivery_short_info"];
                $suppl_id   = $val["suppl_id"];
                $ret_days   = $val["return_days"];
                $storage_id = $val["storage_id"];

                if (($stock > 0 && $price > 0)) {
                    $os             = ["content" => $cont[$i], "class" => $class[$i], "hide" => $hide[$i], "border" => $border[$i], "none" => $none[$i]];
                    $list_target    .= $this->printSearchList3($i, $art_id, $art_nr_ds, $brand_id, $brand_name, $art_name, $del_info, $stock, $price, $article_nr_search, $brand_nr_search, $os, $suppl_id, $ret_days, $del_days, $del_short, $storage_id);
                } elseif ($i === 1) {
                    $os             = ["content" => "", "class" => "", "hide" => "", "border" => "border-line", "none" => "dvisibility"];
                    $list_target    .= $this->printSearchList3($i, $art_id, $art_nr_ds, $brand_id, $brand_name, $art_name, $del_info, $stock, $price, $article_nr_search, $brand_nr_search, $os, $suppl_id, $ret_days, $del_days, $del_short, $storage_id);
                }

                $i++;
            }

            if ($check === 0) {
                $style_target   = "";
                $os             = ["content" => "", "class" => "", "hide" => "", "border" => "border-line", "none" => "dvisibility"];
                $list_target    .= $this->printSearchList3($i, $dataArt["art_id"], $dataArt["art"], $dataArt["brand_id"], $dataArt["brand"], "", "", 0, 0, $article_nr_search, $brand_nr_search, $os, 0, 0, 0, "", 0);
            }

            // analogs
            if (count($mas) > 1) {

                $style = "";
                foreach ($mas as $mas_key => $mas_val) {
                    if ($mas_key !== $art_id_search) {
                        foreach ($mas_val as $val) {
                            $art_id     = $mas_key;
                            $art_nr_ds  = $val["article_nr_displ"];
                            $brand_id   = $val["brand_id"];
                            $brand_name = $val["brand_name"];
                            $art_name   = $val["article_name"];
                            $stock      = $val["stock"];
                            $del_info   = $val["delivery_info"];
                            $price      = $val["price"];
                            $del_days   = $val["delivery_days"];
                            $del_short  = $val["delivery_short_info"];
                            $suppl_id   = $val["suppl_id"];
                            $ret_days   = $val["return_days"];
                            $storage_id = $val["storage_id"];
                            $os         = ["content" => $cont[$i], "class" => $class[$i], "hide" => $hide[$i], "border" => $border[$i], "none" => $none[$i]];

                            $list .= $this->printSearchList3($i, $art_id, $art_nr_ds, $brand_id, $brand_name, $art_name, $del_info, $stock, $price, $article_nr_search, $brand_nr_search, $os, $suppl_id, $ret_days, $del_days, $del_short, $storage_id);
                            $i++;
                        }
                    }
                }

                if ($nulls === 1) {
                    foreach ($mas_nulls as $mas_key => $mas_val) {
                        foreach ($mas_val as $val) {
                            $art_id     = $mas_key;
                            $art_nr_ds  = $val["article_nr_displ"];
                            $brand_id   = $val["brand_id"];
                            $brand_name = $val["brand_name"];
                            $art_name   = $val["article_name"];
                            $stock      = $val["stock"];
                            $del_info   = $val["delivery_info"];
                            $price      = $val["price"];
                            $del_days   = $val["delivery_days"];
                            $del_short  = $val["delivery_short_info"];
                            $suppl_id   = $val["suppl_id"];
                            $ret_days   = $val["return_days"];
                            $storage_id = $val["storage_id"];
                            $os         = ["content" => "", "class" => "", "hide" => "", "border" => "border-line", "none" => "dvisibility"];

                            $list .= $this->printSearchList3($i, $art_id, $art_nr_ds, $brand_id, $brand_name, $art_name, $del_info, $stock, $price, $article_nr_search, $brand_nr_search, $os, $suppl_id, $ret_days, $del_days, $del_short, $storage_id);
                            $i++;
                        }
                    }
                }
            } else {
                $style = "none";
            }

        } else {
            $list = $error;
        }

        $form = $this->getHtmlForm("catalog_exist/search");
        $form = str_replace(array("{article_nr_displ}", "{brand_name}", "{search_target_class}", "{search_target_list}", "{search_list}", "{search_list_style}"), array($dataArt["art"], $dataArt["brand"], $style_target, $list_target, $list, $style), $form);

        return $form;
    }

    public function printSearchList3($id, $art_id, $article_nr_displ, $brand_id, $brand_name, $article_name, $delivery_info, $stock, $price, $article_nr_search, $brand_nr_search, $os, $suppl_id, $return_days, $delivery_days, $delivery_short_info, $storage_id)
    {
        $return_days = $this->getUrlNumber($return_days);

        $showform   = new FormClass();
        $kours      = new ExRateClass();
        $client     = new ClientClass();
        $shop       = new ShopClass();

        $cur                = $this->getCurrentExrate();
        $kours_cap          = $this->getSymbolExrate($cur);
        $format_name        = $this->getFormatAticle($article_nr_displ);
        $format_brand_link  = $this->getBrandLink($brand_id);
        $return_days_alt    = $return_days_img = "";

        if ((int)$suppl_id > 0) {

            if ($return_days === 0) {
                $return_days_alt = "{no_exchange}";
                $return_days_img = $this->images . "/exchange/exchange2.png";
            }

            elseif ($return_days === 14) {
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

        $action_form    = "";
        $action_count   = "";

        if ($this->checkActionPrice($art_id)) {
            list(, $action_amount, $action_price, $action_max_amount, $action_data) = $this->checkActionPrice($art_id);
            $action_price = $kours->getKoursFromUSA($action_price, $cur);

            if ($cur === 1) {
                $action_price = $client->getClientPriceRounding($this->getClient(), $action_price);
            }

            $action_data_cap = ($action_data > 0) ? date("d.m.Y", strtotime($action_data)) : "{indefinitely_cap}";
            $action_max_amount_cap = ($action_max_amount > 0) ? "{yes_cap}" : "{no_cap}";

            $action_form = $this->getHtmlForm("search/action_box");
            $action_form = str_replace(array("{action_price}", "{action_amount}", "{action_data}", "{action_max_amount}", "{cur_cap}"), array($action_price, $action_amount, $action_data_cap, $action_max_amount_cap, $kours_cap), $action_form);
            $action_count = "oninput=\"changeActionCount('$id', '$action_price', '$action_amount');\"";
        }

        $product_link = (empty($stock) && empty($price)) ? "" : $this->getSiteLink() . "$this->products_link/$format_name-$format_brand_link-$art_id/";
        $product_link2 = (empty($stock) && empty($price)) ? "" : $this->getSiteLink() . "$this->search_link/$format_name/$format_brand_link/";

        $product_text = ($article_name === "") ? "{details_name_cap}" : $article_name;
        $format_product_text = ($article_name === "") ? "{details_name_cap}" : $this->formatArticleName($article_name);

        $product_stock = (int)$stock;
        if (empty($suppl_id) && (int)$stock > 10) {
            $product_stock = ">10";
        }

        $flagData = $showform->getCountryFlag($brand_id);
        $flagClass = (!$flagData) ? "none" : "";
        $instock = (empty($suppl_id) && !empty($stock)) ? "<b class=\"tables__instock\"> {in_stock}</b>" : "";

        $delivery_info = str_replace('"', "", $delivery_info);
        $delivery_short_info = str_replace("<br>", " ", $delivery_short_info);
        if (empty($delivery_days) && empty($suppl_id)) {
            $delivery_short_info = "<span class='delivery-green'>{send_done}</span>";
        }

        $tpoint_fname       = (empty($suppl_id)) ? $client->getArticleStorageTPoint($storage_id) : "";
        $product_main_photo = ($showform->getArticlePhoto($art_id) === "") ? $this->noPhoto : $showform->getArticlePhoto($art_id);
        $analog_display     = (($article_nr_displ === $article_nr_search || $format_name === $article_nr_search) && ($brand_id === $brand_nr_search)) ? "none" : "";

        $basket_amount      = $shop->getBasketArticleAmount($art_id, $storage_id);
        $basket_amount_cap  = ($basket_amount > 0) ? "{site_basket}: $basket_amount {amount_abbr}." : "";
        $return_display     = ($return_days === 14 || $return_days_img === "") ? "none" : "";

        $pvisibility        = (!empty($stock) && !empty($price)) ? "" : "dvisibility0";
        $pvisibility_price  = (empty($stock) && empty($price)) ? "dvisibility0" : "";
        $pvisibility_info   = (empty($stock) && empty($price)) ? "dvisibility0" : "";

        $photo_display  = $this->checkPhoto($art_id) ? "" : "none";
        $photo_src      = $showform->getArticleActivePhoto($art_id);
        $prod_title_del = str_replace("<br>", " ", $delivery_info);
        $prod_barcode   = $this->getBarcode($art_id);
        $index_type     = $this->getIndexTypeImage($art_id, $article_nr_search, $article_nr_displ, $format_name, $brand_id, $brand_nr_search);
        $product_info   = $showform->getArticleInfoForm($art_id, 1);

        $form = $this->getHtmlForm("product_card");
        $form = str_replace(
            array("{product_i}", "{art_id}", "{brand_id}", "{product_name}", "{product_brand}", "{page_product_link}", "{page_product_link2}", "{product_text}", "{format_product_text}", "{product_stock}", "{product_real_stock}", "{product_storage_id}", "{product_suppl_id}", "{return_days_img}", "{return_days_alt}", "{return_display}", "{photo_src}", "{photo_display}", "{product_main_photo}", "{product_del}", "{product_dd}", "{product_delivery_class}", "{product_delivery_short_info}", "{product_price}", "{product_true_price}", "{product_kours_cap}", "{product_action}", "{product_action_count}", "{product_title_del}", "{analog_display}", "{product_barcode}", "{style_border}", "{style_class}", "{style_none}", "{style_hide}", "{country_display}", "{flag_image}", "{country_name}", "{instock}", "{index_type}", "{tpoint_full_name}", "{product_info}", "{del_class}", "{basket_amount}", "{pvisibility}", "{pvisibility_price}", "{pvisibility_info}"),
            array($id, $art_id, $brand_id, $article_nr_displ, $brand_name, $product_link, $product_link2, $product_text, $format_product_text, $product_stock, $stock, $storage_id, $suppl_id, $return_days_img, $return_days_alt, $return_display, $photo_src, $photo_display, $product_main_photo, $delivery_info, $delivery_days, "", $delivery_short_info, $price . " " . $kours_cap, $price, $kours_cap, $action_form, $action_count, $prod_title_del, $analog_display, $prod_barcode, $os["border"], $os["class"], $os["none"], $os["hide"], $flagClass, $flagData["flag"], $flagData["country"], $instock, $index_type, $tpoint_fname, $product_info, "", $basket_amount_cap, $pvisibility, $pvisibility_price, $pvisibility_info),
            $form
        );

        $list = $form;
        $list .= $os["content"];

        $list = $this->replaceLang($list);

        return $list;
    }

}

function keywordCmp($a, $b) {
    if ($a["str_count"] === $b["str_count"]) {
        return 0;
    }
    return $a["str_count"] < $b["str_count"] ? 1 : -1;
}

function myBrandCmp($a, $b) {
    if ($a["count"] === $b["count"]) {
        return 0;
    }
    return $a["count"] < $b["count"] ? 1 : -1;
}