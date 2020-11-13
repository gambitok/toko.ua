<?php

class ProductsClass extends CatalogueClass
{

    use Helper;
    use Variables;

    /*
     * CAR DETAILS (CATALOG)
     * */
    public function techCarModels($typ_id, $str_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $str_id = $this->getUrlNumber($str_id);

        $automan = new AutoClass();

        setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");

        $cur = $this->getCurrentExrate();

        list($str_level, $str_id_parrent) = $automan->getStrParams($str_id);
        $str_text = $automan->getStrNewDescr($str_id);
        if ($str_text == "") {
            $str_text = $automan->getStrDescr($str_id);
        }

        if ($str_level == NULL && $str_id_parrent == NULL) {
            $list = $this->searchList("0", 2)[0]; // error TREE
        } else {
            $list = $this->techModelsList($typ_id, $str_id)[0];
        }

        $form = $this->getHtmlForm("cat_new_search");
        $form = str_replace("{type_search}", 2, $form);
        $form = str_replace("{cur_value}", $cur, $form);
        $search_main = $this->getSearchMainTree($this->getHtmlForm("cat_search_main"), $list, $str_text, $typ_id, $str_id);
        $form = str_replace("{cat_search_main}", $search_main, $form);
        $form = str_replace("{cat_search_new_tree}", "", $form);
        $form = str_replace("{cat_search_tree}", "", $form);
        $form = str_replace("{cat_search_filters}", "", $form);
        $form = str_replace("{cat_search_brands}", "", $form);
        $form = str_replace("{search_typ_id}", $typ_id, $form);
        $form = str_replace("{search_str_id}", $str_id, $form);
        $form = str_replace("{garage_style}", ($this->getCookieAuto()) ? "none" : "", $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    public function techCarModelsFilter($typ_id, $str_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $str_id = $this->getUrlNumber($str_id);

        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $automan = new AutoClass();
        $lang = $this->getLanguage();
        $lang = $language->getOldLanguage($lang);

        session_start();
        $key = session_id() . "_" . time();

        list($str_level, $str_id_parrent) = $automan->getStrParams($str_id);

        $true_str_id = $str_id;
        $true_str_level = $str_level;
        $true_str_id_parrent = $str_id_parrent;

        $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `TEMP_ARTICLES_$key` (`art_id` INT NOT NULL ,`amount` INT( 2 ) NOT NULL ,`status` INT( 2 ) NOT NULL, `price` INT( 2 ) NOT NULL ) ENGINE = MYISAM;");

        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$typ_id' GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        $art_id_str = "0";
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            if ($art_id != "") {
                $art_id_str .= ",$art_id";
            }
            $db->query("INSERT INTO `TEMP_ARTICLES_$key` (`art_id`, `amount`, `status`) VALUES ($art_id, 0, 0);");
        }

        $r = $db->query("SELECT `ART_ID`, `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` IN ($art_id_str);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $amount = $db->result($r, $i - 1, "AMOUNT");
            if ($amount > 0) {
                $db->query("UPDATE `TEMP_ARTICLES_$key` SET `amount`=1 WHERE `art_id`='$art_id';");
            }
        }

        $r = $db->query("SELECT ta.art_id, t2si.suppl_id, t2si.client_storage_id FROM `TEMP_ARTICLES_$key` ta
        INNER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=ta.art_id AND t2si.status=1);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            $suppl_id = $db->result($r, $i - 1, "suppl_id");
            $suppl_storage_id = $db->result($r, $i - 1, "client_storage_id");
            $price = $this->getArticlePrice($art_id);
            if ($suppl_id > 0) {
                $price = $this->getArticleSupplPrice($art_id, $suppl_id, $suppl_storage_id);
            }
            if ($price > 0) {
                $db->query("UPDATE `TEMP_ARTICLES_$key` SET `status`=1 WHERE `art_id`='$art_id';");
            }
        }

        $r = $db->query("SELECT `art_id` FROM `TEMP_ARTICLES_$key` WHERE ((`amount`=1) OR (`status`=1));");
        $n = $db->num_rows($r);
        $art_id_str = "0";
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            $art_id_str .= ",$art_id";
        }

        $status_str = "";
        if ($true_str_level == NULL && $true_str_id_parrent == NULL) {
            $list_filters = "<div id=\"cat_search_filters\"></div>";
            $list_brand = "<div id=\"cat_search_brands\"></div>";
            $status_str = "dnone";
        } else {
            list(, $list_brand, $list_filters) = $this->techModelsList($typ_id, $str_id);
        }

        $r = $db->query("SELECT `STR_ID`, `ART_ID` FROM `T2_TREE` WHERE `ART_ID` IN ($art_id_str);");
        $n = $db->num_rows($r);
        $str_id_str = "0";
        $str_id_a = array();
        for ($i = 1; $i <= $n; $i++) {
            $str_id = $db->result($r, $i - 1, "STR_ID");
            $str_id_str .= ",$str_id";
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $str_id_a[$str_id][$art_id] = $art_id;
        }
        $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$key`;");

        $r = $db->query("SELECT `STR_ID`, `STR_ID_PARENT`, `STR_LEVEL`, `DISP_TEXT`, `POSITION` FROM `T2_GROUP_TREE` 
        WHERE `STR_ID` IN ($str_id_str) AND `LNG_ID`=$lang;");
        $n = $db->num_rows($r);
        $td_array = [];
        for ($i = 1; $i <= $n; $i++) {
            $group_str_id = $db->result($r, $i - 1, "STR_ID");
            $group_str_id_parrent = $db->result($r, $i - 1, "STR_ID_PARENT");
            if ($group_str_id_parrent == "") {
                $group_str_id_parrent = 0;
            }
            $group_str_level = $db->result($r, $i - 1, "STR_LEVEL");
            $tex_text = $db->result($r, $i - 1, "DISP_TEXT");
            $position = $db->result($r, $i - 1, "POSITION");
            $child = $this->getTecGroupTreeChilds($group_str_id);
            $art_ids = implode(",", $str_id_a[$group_str_id]);
            $td_array[$i]["id_tree"] = $group_str_id;
            $td_array[$i]["id_parent"] = $group_str_id_parrent;
            $td_array[$i]["level"] = $group_str_level;
            $td_array[$i]["name"] = $tex_text;
            $td_array[$i]["child"] = $child;
            $td_array[$i]["art_ids"] = $art_ids;
            $td_array[$i]["position"] = $position;
        }

        $position_fare = [];
        $parent_fare = [];
        foreach ($td_array as $key => $row) {
            $parent_fare[$key] = $row["id_parent"];
            $position_fare[$key] = $row["position"];
        }

        array_multisort($parent_fare, SORT_ASC, $position_fare, SORT_DESC, $td_array);

        $cat_search_new_tree = $this->getCarDetailsMin($str_id_str, "");
        $cat_search_tree = $this->getSearchTree($this->getHtmlForm("cat_search_tree"), $td_array, $typ_id, $status_str, $true_str_id);
        $cat_search_filters = $list_filters;
        $cat_search_brands = $list_brand;

        return array($cat_search_new_tree, $cat_search_tree, $cat_search_filters, $cat_search_brands);
    }

    public function getTecGroupTreeChilds($str_id_parent)
    {
        $db = DbSingleton::getTokoDb();
        $str_id_parent = $this->getUrlNumber($str_id_parent);
        $r = $db->query("SELECT COUNT(`STR_ID`) as kol FROM `T2_GROUP_TREE` WHERE `STR_ID_PARENT`=$str_id_parent;");
        return intval($db->result($r, 0, "kol"));
    }

    public function getStrIds($typ_id)
    {
        $db = DbSingleton::getTokoDb();
        session_start();
        $key = session_id() . "_" . time();
        $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `TEMP_ARTICLES_$key` (`art_id` INT NOT NULL ,`amount` INT( 2 ) NOT NULL ,`status` INT( 2 ) NOT NULL, `price` INT( 2 ) NOT NULL ) ENGINE = MYISAM ;");

        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$typ_id' GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        $art_ids = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            array_push($art_ids, $art_id);
            $db->query("INSERT INTO `TEMP_ARTICLES_$key` (`art_id`, `amount`, `status`) VALUES ($art_id, 0, 0);");
        }
        $art_id_str = implode(",", $art_ids);

        $r = $db->query("SELECT `ART_ID`, `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` IN ($art_id_str);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $amount = $db->result($r, $i - 1, "AMOUNT");
            if ($amount > 0) {
                $db->query("UPDATE `TEMP_ARTICLES_$key` SET `amount`=1 WHERE `art_id`='$art_id';");
            }
        }

        $r = $db->query("SELECT ta.art_id, t2si.suppl_id, t2si.client_storage_id FROM `TEMP_ARTICLES_$key` ta
        INNER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=ta.art_id AND t2si.status=1);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            $suppl_id = $db->result($r, $i - 1, "suppl_id");
            $suppl_storage_id = $db->result($r, $i - 1, "client_storage_id");
            $price = $this->getArticlePrice($art_id);
            if ($suppl_id > 0) {
                $price = $this->getArticleSupplPrice($art_id, $suppl_id, $suppl_storage_id);
            }
            if ($price > 0) {
                $db->query("UPDATE `TEMP_ARTICLES_$key` SET `status`=1 WHERE `art_id`='$art_id';");
            }
        }

        $r = $db->query("SELECT `art_id` FROM `TEMP_ARTICLES_$key` WHERE ((`amount`=1) OR (`status`=1));");
        $n = $db->num_rows($r);
        $art_ids = [];
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            array_push($art_ids, $art_id);
        }
        $art_id_str = implode(",", $art_ids);

        $r = $db->query("SELECT `STR_ID`, `ART_ID` FROM `T2_TREE` WHERE `ART_ID` IN ($art_id_str);");
        $n = $db->num_rows($r);
        $str_ids = [];
        for ($i = 1; $i <= $n; $i++) {
            $str_id = $db->result($r, $i - 1, "STR_ID");
            array_push($str_ids, $str_id);
        }
        $str_id_str = implode(",", $str_ids);

        $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$key`;");

        return $str_id_str;
    }

    /*
     * check existing of header
     * */
    public function getGroupTreeAmount($head_id, $str_id_str)
    {
        $db = DbSingleton::getTokoDb();
        $where_str = ($str_id_str != "") ? "AND cs.STR_ID IN ($str_id_str)" : "";
        $r = $db->query("SELECT cs.*, cat.TEX_RU as CAT_NAME_RU, cat.TEX_UA as CAT_NAME_UA, cat.TEX_EN as CAT_NAME_EN 
        FROM `T2_GROUP_TREE_STR` cs 
            LEFT JOIN `T2_GROUP_TREE_CATEGORY` cat ON cat.CAT_ID=cs.CAT_ID
		WHERE cs.HEAD_ID='$head_id' $where_str ORDER BY cat.POSITION ASC, cs.POSITION ASC;");
        $n = $db->num_rows($r);
        return ($n > 0);
    }

    // link: /catalog
    public function getCarDetails($str_id_str, $typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $lang_id = $this->getLanguage();
        $lang_cap = $language->getTexCapLanguage($lang_id);
        $r = $db->query("SELECT * FROM `T2_GROUP_TREE_HEAD`;");
        $n = $db->num_rows($r);
        $list = "<ul class=\"head-list\">";
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $tex_text = $db->result($r, $i - 1, "TEX_$lang_cap");
            $images = $db->result($r, $i - 1, "IMAGES");
            $status = $db->result($r, $i - 1, "STATUS");
            $check_amount = $this->getGroupTreeAmount($head_id, $str_id_str);
            $header_list = "";
            $photo = ($images == "") ? $this->noPhoto : "/uploads/images/group_tree_head/$images";
            if ($status && $check_amount) {
                $list .= "<li id=\"head_id_$head_id\" onclick=\"showCarDetailsStr('$head_id');\">
                    <div id=\"tree_head-$head_id\" class=\"row align-items-center tree-head pointer-el\">
                        <div class=\"col-lg-4 col-12\"><img class=\"lazy\" data-src=\"$photo\" alt=\"$tex_text\" title=\"$tex_text\"></div>
                        <div class=\"col-lg-7 col-10\"><span>$tex_text</span></div>
                        <div class=\"col-lg-1 col-2 text-right\"><i class=\"fa fa-chevron-right rotate_animation\"></i></div>
                    </div>
                    <div id=\"manufacture_head$head_id\" class=\"tree-list dnone\">$header_list</div>
                </li>";
            }
        }
        $list .= "</ul>";
        $form = $this->getHtmlForm("cat_car_details");
        $form = str_replace("{tree_headers}", $list, $form);
        $form = str_replace("{typ_id}", $typ_id, $form);
        $form = str_replace("{typ_display}", ($typ_id == "") ? "none" : "", $form);
        $form = str_replace("{tree_str_ids}", $str_id_str, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    /*
     * catalog details from `Home page`
     * */
    public function getCarDetailsFull()
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $lang_id = $this->getLanguage();
        $lang_cap = $language->getTexCapLanguage($lang_id);
        $r = $db->query("SELECT * FROM `T2_GROUP_TREE_HEAD`;");
        $n = $db->num_rows($r);
        $list = "<ul class=\"head-list bordered\">";
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $tex_text = $db->result($r, $i - 1, "TEX_$lang_cap");
            $images = $db->result($r, $i - 1, "IMAGES");
            $status = $db->result($r, $i - 1, "STATUS");
            $header_list = $this->showCarDetailsStr($head_id);
            $photo = ($images == "") ? $this->noPhoto : "/uploads/images/group_tree_head/$images";
            if ($status) {
                $list .= "
                <li id=\"head_id_$head_id\" class=\"head-list__item\">
                    <input type=\"checkbox\" id=\"toggle-head-$head_id\">
                    <label for=\"toggle-head-$head_id\">
                        <div id=\"tree_head-$head_id\" class=\"row align-items-center tree-head pointer-el\">
                            <div class=\"col-lg-4 col-12\"><img class=\"lazy\" data-src=\"$photo\" alt=\"$tex_text\" title=\"$tex_text\"></div>
                            <div class=\"col-lg-7 col-10\"><span>$tex_text</span></div>
                            <div class=\"col-lg-1 col-2 text-right\"><i class=\"fa fa-chevron-right rotate_animation\"></i></div>
                        </div>
                    </label>
                    <div id=\"manufacture_head$head_id\" class=\"tree-list\" style='display: none'>
                        $header_list
                    </div>
                </li>";
            }
        }
        $list .= "</ul>";
        $list = $this->replaceLang($list);
        return $list;
    }

    // get HEAD TREE STR
    public function showCarDetailsStr($head_id, $str_id_str = "")
    {
        $head_id = $this->getUrlNumber($head_id);
        $str_id_str = $this->getNameString($str_id_str);

        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        $language = new LangClass();
        $prefix = $this->getLangPrefix();
        $lang_id = $this->getLanguage();
        $lang_cap = $language->getTexCapLanguage($lang_id);
        $head_id = $this->getUrlNumber($head_id);

        $arr = [];
        $head_link = $automan->getHeadNewDescr($head_id)["link"];
        $where_str = ($str_id_str != "") ? "AND cs.STR_ID IN ($str_id_str)" : "";

        $list = "<div class=\"tree-block\">";
        $r = $db->query("SELECT cs.*, cat.CAT_ID
        FROM `T2_GROUP_TREE_STR` cs 
            LEFT OUTER JOIN `T2_GROUP_TREE_CATEGORY` cat ON cat.CAT_ID=cs.CAT_ID
		WHERE cs.HEAD_ID='$head_id' $where_str ORDER BY cat.POSITION ASC, cs.POSITION ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $CAT_ID = $db->result($r, $i - 1, "CAT_ID");
                $DISP_TEXT = $db->result($r, $i - 1, "TEX_$lang_cap");
                $TEX_LINK = $db->result($r, $i - 1, "TEX_LINK");
                $IMAGES = $db->result($r, $i - 1, "IMAGES");
                $STR_ID = $db->result($r, $i - 1, "STR_ID");
                $arr[$CAT_ID][$i] = ["text" => $DISP_TEXT, "link" => $TEX_LINK, "image" => $IMAGES, "str_id" => $STR_ID];
            }
            foreach ($arr as $key => $value) {
                list($cat_name, $cat_link) = $automan->getCatNewDescr($key);
                $list .= "<div class=\"tree-item\">";
                $list .= "<div class=\"tree-item-title\"><span><a href=\"https://toko.ua$prefix/$this->catalog_link/$head_link/$cat_link/\">$cat_name</a></span></div>";
                $list .= "<div class=\"tree-item-list\">";
                foreach ($value as $v) {
                    $text = $v["text"];
                    $link = $v["link"];
                    $list .= "<div class=\"tree-item-list__element\">
                        <a href=\"https://toko.ua$prefix/$this->catalog_link/$link/\">
                            <span>$text</span>
                        </a>
                    </div>";
                }
                $list .= "</div>";
                $list .= "</div>";
            }
        }
        $list .= "</div>";
        if ($n == 0) {
            $list = "";
        }
        $list = $this->replaceLang($list);
        return $list;
    }

    // MIN DETAILS LIST (CATALOG)
    public function getCarDetailsMin($str_id_str, $typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $language = new LangClass();
        $automan = new AutoClass();
        $lang_id = $this->getLanguage();
        $lang_cap = $language->getTexCapLanguage($lang_id);
        $form = $this->getHtmlForm("cat_car_min_details");
        $r = $db->query("SELECT * FROM `T2_GROUP_TREE_HEAD`;");
        $n = $db->num_rows($r);
        $list = "<ul style=\"list-style:none; padding:0;\">";
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $tex_text = $db->result($r, $i - 1, "TEX_$lang_cap");
            $images = $db->result($r, $i - 1, "IMAGES");
            $status = $db->result($r, $i - 1, "STATUS");
            $check_amount = $this->getGroupTreeAmount($head_id, $str_id_str);
            $photo = ($images == "") ? $this->noPhoto : "/uploads/images/group_tree_head/$images";
            if ($status && $check_amount) {
                $list .= "<li id=\"head_id_$head_id\" onclick=\"showCarDetailsStrMin('$head_id');\">
                    <div id=\"tree_head-$head_id\" class=\"row align-items-center tree-head tree-head_min pointer-el\">
                        <div class=\"col-lg-4 col-8\"><img src=\"$photo\" alt=\"$images\" title=\"$tex_text\"></div>
                        <div class=\"col-lg-8 col-4\"><span>$tex_text</span></div>
                    </div>
                    <div id=\"manufacture_head$head_id\" class=\"tree-list_min dnone\"></div>
                </li>";
            }
        }
        $list .= "</ul>";
        $form = str_replace("{tree_headers}", $list, $form);
        $form = str_replace("{typ_id}", $typ_id, $form);
        $form = str_replace("{typ_display}", ($typ_id == "") ? "none" : "", $form);
        $form = str_replace("{tree_title}", $automan->getCarDescription($typ_id), $form);
        $form = str_replace("{tree_str_ids}", $str_id_str, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    public function getCarsSearch($mfa_link = "", $mod_link = "", $str_id = 0)
    {
        $automan = new AutoClass();
        $form = $this->getHtmlForm("cars/cars");
        if ($mfa_link != "") {
            $mfa_id = $automan->getMfaLink($mfa_link);
            $mfa_brand = $automan->getMfaBrand($mfa_id);
            $list_model = $this->getCarsSearchContent("manuf", $mfa_id, $str_id)[0];
            $form = str_replace("{cars_models}", $list_model, $form);
            $form = str_replace("{selected_manuf}", $mfa_id, $form);
            $form = str_replace("{cars_manufacturer}", $mfa_brand, $form);
            if ($mod_link != "") {
                $model = $automan->getModLink($mod_link);
                $form = str_replace("{cars_years}", $this->getCarsSearchContent("model", $mfa_id . "_" . $model, $str_id)[0], $form);
                $form = str_replace("{selected_model}", $mfa_id . "_" . $model, $form);
                $form = str_replace("{cars_model}", $model, $form);
                $form = str_replace("{active_nav}", "years", $form);
            }
            $form = str_replace("{active_nav}", "model", $form);
        }
        $form = str_replace("{cars_manufactures}", $this->getCarsSearchContent()[0], $form);
        $form = str_replace("{selected_manuf}", 0, $form);
        $form = str_replace("{selected_model}", 0, $form);
        $form = str_replace("{active_nav}", "", $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    public function getYearsForm($date_start, $date_end, $mfa_id, $model)
    {
        $min_date_start = 1947;
        $max_date_end = 2020;

        if ($date_end != "" && $date_end != 0) {
            $date_end = substr($date_end, 0, -2) . "";
        } else {
            $date_end = $max_date_end;
        }
        if ($date_start != "" && $date_start != 0) {
            $date_start = substr($date_start, 0, -2) . "";
        } else {
            $date_start = $min_date_start;
        }

        $mas = [];
        for($i = $date_end; $i >= $date_start; $i--) {
            $mas[] = $i;
        }
        $headers = [];
        foreach($mas as $val) {
            $item = floor($val / 10) * 10;
            if (!in_array($item, $headers)) {
                $headers[] = $item;
            }
        }
        $res = [];
        foreach($headers as $head) {
            foreach($mas as $val) {
                if (($val - $head) < 10 && ($val - $head) >= 0) {
                    $res[$head][] = $val;
                }
            }
        }
        $form = "";
        foreach ($res as $key => $value) {
            $form .= "<div class=\"cars-tab__block-item-years\"><div class=\"cars-tab__block-item cars-tab__block-item-min cars-tab__block-item-min-disabled\">$key - e</div>";
            foreach ($value as $year) {
                $year_cap = $mfa_id . "_" . $model . "_" . $year;
                $form .= "<div class=\"cars-tab__block-item cars-tab__block-item-min\" data-url=\"years/$year_cap\" onclick=\"toggleCarsTab(this)\">$year</div>";
            }
            $form .= "</div>";
        }
        return $form;
    }

    public function getCarsSearchContent($type = "", $value = "", $str_id = 0)
    {
        $type =  $this->getNameString($type);
        $value =  $this->getNameString($value);
        $str_id = $this->getUrlNumber($str_id);

        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        $n = 0;
        $list = $title = $nav = $tab = "";
        $str_link = $automan->getStrNewLink($str_id);
        $skip = 0;

        // MANUFACTURE
        if ($type == "") {
            $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND` FROM `T_manufacturers` WHERE `ACTIVE`=1 ORDER BY `MFA_BRAND`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $mfa_id = $db->result($r, $i - 1, "MFA_ID");
                $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
                $list .= "<div data-url=\"manuf/$mfa_id\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$mfa_brand</div>";
            }
            $title = "{auto_cap}";
            $nav = "{auto_cap}";
            $tab = "cars-tab1";
        }

        // MODEL
        if ($type == "manuf") {
            $mfa_id = $value;
            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' AND `ACTIVE`=1 GROUP BY `Model`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model = $db->result($r, $i - 1, "Model");
                $model_cap = $mfa_id . "_" . $model;
                $list .= "<div data-url=\"model/$model_cap\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$model</div>";
            }
            $title = $automan->getMfaBrand($mfa_id);
            $nav = "manuf";
            $tab = "cars-tab2";
        }

        // YEAR
        if ($type == "model") {
            list($mfa_id, $model) = explode("_", $value);
            $n = 1;
            $r = $db->query("SELECT MIN(`MOD_PCON_START`) as min_year, 
                CASE WHEN MIN(`MOD_PCON_END`)=0 THEN 0 ELSE MAX(`MOD_PCON_END`) END as max_year
            FROM `T_models` WHERE `Model`='$model' AND `MOD_MFA_ID`='$mfa_id';");
            $date_start = $db->result($r, 0, "min_year");
            $date_end = $db->result($r, 0, "max_year");
            $list .= $this->getYearsForm($date_start, $date_end, $mfa_id, $model);
            $title = $model;
            $nav = "model";
            $tab = "cars-tab3";
        }

        // BODY (MODEL_ID)
        if ($type == "years") {
            list($mfa_id, $model, $year) = explode("_", $value);
            $where = "AND 
                ((`MOD_PCON_END`>=" . $year . "00 AND `MOD_PCON_END`<=" . $year . "12)
                OR (`MOD_PCON_START`<=" . $year . "12 AND `MOD_PCON_END`>=" . $year . "00)
                OR (`MOD_PCON_START`<=" . $year . "12 AND `MOD_PCON_END`=0))";
            $r = $db->query("SELECT * FROM `T_models` WHERE `Model`='$model' AND `MOD_MFA_ID`='$mfa_id' AND `ACTIVE`=1 $where;");
            $n = $db->num_rows($r);
            if ($n == 1) {
                $skip = $db->result($r, 0, "MOD_ID");
            }
            for ($i = 1; $i <= $n; $i++) {
                $mod_id = $db->result($r, $i - 1, "MOD_ID");
                $tex_text = $db->result($r, $i - 1, "TEX_TEXT");
                $image = $db->result($r, $i - 1, "Car_pict");
                list($body_name, $body_path) = $this->getBodyCarImage($mod_id);
                $d_start = $db->result($r, $i - 1, "MOD_PCON_START");
                $d_start = substr($d_start, 0, 4);
                $d_end = $db->result($r, $i - 1, "MOD_PCON_END");
                $d_end = substr($d_end, 0, 4);
                if ($d_end == 0) {
                    $d_end = "{cur_time}";
                }
                $list .= "<div data-url=\"bodyc/$mod_id\" class=\"cars-tab__block-item cars-tab__block-item-body\" onclick=\"toggleCarsTab(this)\">
                <div class=\"bodyc\">
                    <div class=\"bodyc-head\">
                        <div class=\"bodyc__title\">$tex_text</div>
                        <div class=\"bodyc__type\"><img src=\"$body_path\" alt=\"$body_name\" title=\"$body_name\"></div></div>
                    </div>    
                    <div class=\"bodyc-content\">
                        <div class=\"bodyc__descr\">
                            {model_number_type}: $body_name
                            <br>
                            {year_issue}: $d_start - $d_end
                        </div>
                        <div class=\"bodyc__image\">
                            <img src=\"https://toko.ua/uploads/images/models/$image\" alt=\"$tex_text\" title=\"$tex_text\">
                        </div>
                    </div>
                </div>
            </div>";
            }
            $title = $year;
            $nav = "years";
            $tab = "cars-tab4";
        }

        // ENGINE
        if ($type == "bodyc") {
            $mod_id = $value;
            $r = $db->query("SELECT COUNT(`TYP_ID`) as count_types, `TYP_ID`, `VOLUME_CM`, `FUEL_ID`, `TYP_KW_FROM`, `TYP_HP_FROM` FROM `T_types` 
            WHERE `TYP_MOD_ID`='$mod_id' AND `ACTIVE`=1 GROUP BY `VOLUME_CM`, `FUEL_ID` ORDER BY `VOLUME_CM`, `FUEL_ID`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $typ_id = $db->result($r, $i - 1, "TYP_ID");
                $count_types = $db->result($r, $i - 1, "count_types");
                $volume_cm = $db->result($r, $i - 1, "VOLUME_CM");
                $fuel_id = $db->result($r, $i - 1, "FUEL_ID");
                $fuel_text = $this->getFuelName($fuel_id);
                $fuel_cap = $mod_id . "_" . $volume_cm . "_" . $fuel_id;
                $onclick = ($count_types == 1) ? "finishGarage('$typ_id', '$str_link')" : "toggleCarsTab(this)";
                $list .= "<div data-url=\"engin/$fuel_cap\" class=\"cars-tab__block-item\" onclick=\"$onclick\">$volume_cm $fuel_text</div>";
            }
            $title = $this->getModIdText($mod_id);
            $nav = "bodyc";
            $tab = "cars-tab5";
        }

        // MODIFICATION
        if ($type == "engin") {
            list($mod_id, $volume_cm, $fuel_id) = explode("_", $value);
            $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id' AND `VOLUME_CM`='$volume_cm' AND `FUEL_ID`='$fuel_id' AND `ACTIVE`=1 ORDER BY `TYP_HP_FROM`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $typ_id = $db->result($r, $i - 1, "TYP_ID");
                $typ_text = $db->result($r, $i - 1, "TYP_TEXT");
                $kw_from = $db->result($r, $i - 1, "TYP_KW_FROM");
                $hp_from = $db->result($r, $i - 1, "TYP_HP_FROM");
                $d_start = $db->result($r, $i - 1, "TYP_PCON_START");
                if ($d_start == 0) {
                    $d_start = "";
                }
                if (strlen($d_start) == 6) {
                    $d_start = substr($d_start, 0, 4) . "." . substr($d_start, 4, 2);
                }
                $d_end = $db->result($r, $i - 1, "TYP_PCON_END");
                if ($d_end == 0) {
                    $d_end = "{cur_time_min}";
                }
                if (strlen($d_end) == 6) {
                    $d_end = substr($d_end, 0, 4) . "." . substr($d_end, 4, 2);
                }
                $eng_cod = $db->result($r, $i - 1, "ENG_Cod");
                $onclick = "finishGarage('$typ_id', '$str_link')";
                $list .= "<div class=\"cars-tab__block-item cars-tab__block-item-modif\"><a href=\"#\" onclick=\"$onclick\">
                <b>$typ_text</b> 
                    <table>
                        <tr><td>{date_release}:</td><td class=\"text-right\">$d_start - $d_end</td></tr>
                        <tr><td>{engine_model}:</td><td class=\"text-right\">$eng_cod</td></tr>
                        <tr><td>{power_cap}:</td><td class=\"text-right\">$hp_from {horse_power_cap}, $kw_from {kilo_wat_cap}</td></tr>
                    </table>
                </a></div>";
            }
            $title = $volume_cm . " " . $this->getFuelName($fuel_id);
            $nav = "engin";
            $tab = "cars-tab6";
        }

        // TYP SELECTED
        if ($type == "modif") {
            $typ_id = $value;
            $title = $this->getTypIdText($typ_id);
            $nav = "modif";
            $tab = "cars-tab6";
        }

        if ($n == 0) {
            $list = "<div style=\"margin: 30px auto;\">{nothing_found}</div>";
        }

        $list = $this->replaceLang($list);


        return array($list, $title, $nav, $tab, $skip);
    }

    public function getBodyCarImage($mod_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BODY_ID` FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id' LIMIT 1;");
        $body_id = $db->result($r, 0, "BODY_ID");
        $r = $db->query("SELECT `LOGO`, `TYPE_BODY` FROM `T_types_body_car` WHERE `BODY_ID`='$body_id' AND `LANG_ID`=16 LIMIT 1;");
        $image = $db->result($r, 0, "LOGO");
        $name = $db->result($r, 0, "TYPE_BODY");
        $path = "https://toko.ua/uploads/images/body-types/$image";
        return array($name, $path);
    }

    public function getModIdText($mod_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        return $db->result($r, 0, "TEX_TEXT");
    }

    public function getTypIdText($typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' LIMIT 1;");
        return $db->result($r, 0, "TYP_TEXT");
    }

    public function clearCarsBlock($sel_tab, $cur_tab)
    {
        $sel_tab = $this->getUrlNumber($sel_tab);
        $cur_tab = $this->getUrlNumber($cur_tab);
        $disabled = "cars-nav__item-disabled";
        $hidden = "cars-nav__item-hidden";
        if ($sel_tab == ($cur_tab + 1)) {
            $disabled = "";
            $hidden = "";
        }
        switch ($sel_tab) {
            case "2":
            {
                $classes = "$disabled";
                $text = "{cars_model}";
                break;
            }
            case "3":
            {
                $classes = "$disabled";
                $text = "{cars_year}";
                break;
            }
            case "4":
            {
                $classes = "$disabled $hidden";
                $text = "{cars_body}";
                break;
            }
            case "5":
            {
                $classes = "$disabled $hidden";
                $text = "{cars_engine}";
                break;
            }
            case "6":
            {
                $classes = "$disabled $hidden";
                $text = "{cars_modification}";
                break;
            }
            case "1":
            default:
            {
                $classes = "";
                $text = "{cars_manufacturer}";
                break;
            }
        }
        $text = $this->replaceLang($text);
        return array($classes, $text);
    }

    /*
     * Get User Garage selected car
     * */
    public function getCarsGarage()
    {
        $automan = new AutoClass();
        $auto_typ_id = $this->getCookieAuto();
        list($manufacture, $model, $model_id) = $automan->getCarInfo($auto_typ_id);
        list($manufacture_cap, , $model_id_cap,) = $automan->getAutoDescr($manufacture, $model, $model_id, $auto_typ_id);
        $models_img = $automan->getAutoIMG($manufacture, $model, $model_id)["model_id_image"];
        $form = $this->getHtmlForm("garage/garage_typ_block");
        $form = str_replace("{typ_id}", $auto_typ_id, $form);
        $form = str_replace("{manufacture_cap}", $manufacture_cap, $form);
        $form = str_replace("{model_id_cap}", $model_id_cap, $form);
        $form = str_replace("{typ_text}", $automan->getGroupInfo($auto_typ_id), $form);
        $form = str_replace("{models_img}", $models_img, $form);
        $form = str_replace("{garage_button}", (($auto_typ_id != "") ? (!($automan->checkUserGarage($auto_typ_id)) ? "btn-img-disabled" : "") : ""), $form);
        $form = str_replace("{typ_id}", $auto_typ_id, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    // Modal Cars Form
    public function showCarsForm()
    {
        return $this->getCarsSearch();
    }

    // Modal Cars Form
    public function showCarsForm2()
    {
        $form = $this->getCarsSearch();
        $auto_typ_id = $this->getCookieAuto();
        $status = 0;
        if ($auto_typ_id != "") {
            $status = 1;
        }
        return array($form, $status);
    }

    // SELECTED CAR FORM
    public function showCarsSelectedForm()
    {
        return $this->getCarsGarage();
    }

}
