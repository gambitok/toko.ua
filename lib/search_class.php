<?php

class SearchClass extends CatalogueClass
{

    public $products_on_page = 12;

    public function getFiltersTitle($active_filters, $type = 0)
    {
        $filters = "";
        foreach ($active_filters as $brand_id) {
            $brand_name = $this->getBrandName($brand_id);
            $filters .= " $brand_name,";
        }
        $filters = rtrim($filters, ",");
        if (!$type) {
            if (count($active_filters) > 1) {
                $filters = "";
            }
        }
        if ($filters != "") {
            $filters = ": " . $filters;
        }
        return $filters;
    }

    public function getSearchLimit($page)
    {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        return ($off >= 0) ? " LIMIT $count OFFSET $off" : "";
    }

    public function getRandomBrands($limit = 1, $brand_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $where = ($brand_id != 0) ? "AND t2a.`BRAND_ID` != $brand_id" : "";
        $r = $db->query("SELECT t2b.`BRAND_NAME` FROM `T2_ARTICLES` t2a 
            LEFT JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID = t2a.BRAND_ID
        WHERE t2b.TOP = 1 $where
        ORDER BY RAND() LIMIT $limit;");
        $n = $db->num_rows($r);
        $brands = [];
        for ($i = 1; $i <= $n; $i++) {
            $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
            array_push($brands, $brand_name);
        }
        return $brands;
    }

    public function getRandomActiveBrands($active_brands, $limit = 1, $brand_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $brands = [];
        $where = ($brand_id != 0) ? "AND `BRAND_ID` != $brand_id" : "";
        if (!empty($active_brands)) {
            $active_brands_str = implode(",", $active_brands);
            $r = $db->query("SELECT `BRAND_NAME` FROM `T2_BRANDS` WHERE `TOP` = 1 AND `BRAND_ID` IN ($active_brands_str) $where ORDER BY RAND() LIMIT $limit;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
                array_push($brands, $brand_name);
            }
        }
        return $brands;
    }

    public function getRandomAcitveArts($where_arts, $limit = 1, $brand_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $arts = [];
        $where = ($brand_id != 0) ? "AND (t2b.`TOP` = 1 OR t2a.`BRAND_ID` = $brand_id)" : "AND t2b.`TOP` = 1";
        if ($where_arts != "") {
            $r = $db->query("SELECT t2a.`ART_ID` FROM `T2_ARTICLES` t2a 
                LEFT JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID = t2a.BRAND_ID
            WHERE t2a.`ART_ID` IN ($where_arts) $where  
            ORDER BY RAND() LIMIT $limit;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "ART_ID");
                array_push($arts, $art_id);
            }
        }
        return $arts;
    }

    public function getRandomGroups($limit = 1)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_RU` FROM `T2_TREE_GROUP` ORDER BY RAND() LIMIT $limit;");
        $n = $db->num_rows($r);
        $groups = [];
        for ($i = 1; $i <= $n; $i++) {
            $group_name = $db->result($r, $i - 1, "TEX_RU");
            array_push($groups, $group_name);
        }
        return $groups;
    }

    /*
     * SEO BLOCK: MFA + MODEL
     * toko.ua/cars/acura/mdx
     * */
    public function getSeoCarsLinking($mfa_link, $mod_link = "")
    {
        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
        list($mfa_text, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);

        $r = $db->query("SELECT `TEXT` FROM `SEO_STR_CARS` WHERE `MFA_ID`='$mfa_id' AND `MODEL`='$model' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n == 0) {
            $page_h1 = "{details_on_cap} $mfa_text $model_text";
            $translit = $this->getCarManufTranslit($mfa_id, $model);
            if ($translit != "") {
                $page_h1 .= " $translit";
            }
            $page_h1_lower = "{details_on_cap_min} $mfa_text $model_text";
            $translit = $this->getCarManufTranslit($mfa_id, $model);
            if ($translit != "") {
                $page_h1_lower .= " $translit";
            }

            list($brand1, $brand2, $brand3) = $this->getRandomBrands(3);

            list($group_name) = $this->getRandomGroups(1);
            $group_name = mb_strtolower($group_name, 'windows-1251');

            $geo_nominative = $this->getSeoLinkingParam("CITY", 1);

            $list = "$page_h1 {_on_shop} {_toko} {_repair_yourself} {_first_need} $mfa_text $model_text. {_toko_market} {_leading_brands} $brand1, $brand2, $brand3. {_wide_range} $mfa_text, {_go_shopping} $group_name {_buy_in_home} {_how_to_buy} $page_h1_lower? {_not_experienced_motorists} $group_name. {_whole_department} {_professionals_advise} $mfa_text $model_text. {_min_information} 
            <ul>
                <li> {_mfa_text} ($mfa_text $model_text); </li>
                <li> {_year_of_issue} </li>
                <li> {_personal_preferences} </li>
                <li> {_vin_code} </li>
            </ul>
            {_manager_help} $geo_nominative";

            $list = $this->replaceLang($list);
            $list = str_replace(str_split("{}"), "", $list);
            $list = explode(" ", $list);

            $seo_text = "";
            foreach ($list as $value) {
                $value = $this->getSeoListingValue($value);
                $seo_text .= "$value ";
            }
            $seo_text .= ".";

            $db->query("INSERT INTO `SEO_STR_CARS` (`MFA_ID`, `MODEL`, `TEXT`) VALUES ('$mfa_id', '$model', '$seo_text');");

        } else {
            $seo_text = $db->result($r, 0, "TEXT");
        }

        return $seo_text;
    }

    /*
     * Get selected Car text Translit
     * */
    public function getCarManufTranslit($mfa_id, $model = "")
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_BRAND_TRANSLIT` FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
        $mfa_translit = $db->result($r, 0, "MFA_BRAND_TRANSLIT");
        $text = "";
        if ($mfa_translit != "") {
            $text = "($mfa_translit)";
        }
        if ($model != "") {
            $r = $db->query("SELECT `Model_TRANSLIT` FROM `T_models` WHERE `Model`='$model' AND `Model_TRANSLIT`!='' LIMIT 1;");
            $model_translit = $db->result($r, 0, "Model_TRANSLIT");
            if ($model_translit != "") {
                $text = "($mfa_translit $model_translit)";
            }
        }
        return $text;
    }

    public function getSeoListingValue($value)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `SEO_LISTING` WHERE `LIST_KEY`='$value' ORDER BY RAND() LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $name = $db->result($r, 0, "TEXT");
        } else {
            $name = $value;
        }
        return $name;
    }

    public function getSeoLinkingParam($param, $count)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `SEO_LISTING_$param` ORDER BY RAND() LIMIT $count;");
        $n = $db->num_rows($r);
        $params = [];
        for ($i = 1; $i <= $n; $i++) {
            $param_name = $db->result($r, $i - 1, $param . "_NAME");
            array_push($params, $param_name);
        }
        $params = implode(", ", $params);
        return $params;
    }

}