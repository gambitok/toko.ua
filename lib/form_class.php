<?php

class FormClass extends CatalogueClass
{

    use Helper;
    use Variables;

    private static $articlePhotos = [];
    private static $flags;
    private static $infoTemplates;

    public $max_history_count = 10;
    public $uploads_link = "https://toko.ua/uploads/images/catalogue";

    /*
     * show modal form
     * */
    public function showModalForm($name)
    {
        $name = $this->getNameString($name);
        $menu = new MenuClass();
        $form = $this->getHtmlForm("modals/$name");
        $form = $this->replaceLang($form);
        $form = str_replace("{site_main_link}", $this->getSiteLink(), $form);
        $form = str_replace("{region_list}", $menu->getRegionList(), $form);
        $form = str_replace("{region_list_phone}", $menu->getRegionListPhone(), $form);
        return $form;
    }

    /*
     * get brand country & flag
     * from BRAND_ID
     * */
    public function getCountryFlag($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $brand_id = $this->getUrlNumber($brand_id);
        if (self::$flags === null) {
            $r = $db->query("SELECT t2c.ALFA2, t2b.BRAND_ID, t2c.COUNTRY_NAME 
            FROM `T2_BRANDS` t2b
                LEFT JOIN `T2_COUNTRIES` t2c ON (t2c.COUNTRY_ID = t2b.COUNTRY_ID)");
            self::$flags = array_column(mysqli_fetch_all($r, MYSQLI_ASSOC), null, 'BRAND_ID');
        }
        $flag = self::$flags[$brand_id]["ALFA2"];
        $name_country = self::$flags[$brand_id]["COUNTRY_NAME"];
        $flag = mb_strtolower($flag);
        if ($name_country == "") {
            return false;
        } else {
            return array("flag" => $flag, "country" => $name_country);
        }
    }

    /*
     * show brand form
     * */
    public function showBrandForm($brand_id)
    {
        $brand_id = $this->getUrlNumber($brand_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `name`, `descr`, `link`, `logo_name` FROM `T2_BRAND_LINK` WHERE `brand_id` = $brand_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $info = $this->getHtmlForm("brand_form");
            $info = str_replace("{brand_form_name}", trim($db->result($r, 0, "name")), $info);
            $info = str_replace("{brand_form_country}", $this->getCountryFlag($brand_id)["flag"], $info);
            $info = str_replace("{brand_form_descr}", trim($db->result($r, 0, "descr")), $info);
            $info = str_replace("{brand_form_link}", trim($db->result($r, 0, "link")), $info);
            $info = str_replace("{brand_form_logo_name}", trim($db->result($r, 0, "logo_name")), $info);
        } else {
            $info = $this->err3;
        }
        return $info;
    }

    /*
     * show article form
     * */
    public function getArticleForm($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $auto = new AutoClass();
//        $client = new ClientClass();
        $auto_typ_id = $this->getCookieAuto();

        $form = $this->getHtmlForm("article/card");
        if ($auto_typ_id != "") {
            if ($this->checkT2Link($auto_typ_id, $art_id)) {
                $form = str_replace("{applicable_display}", "applicable-active", $form);
                $form = str_replace("{applicable_display_text}", "{is_applicable}", $form);
                list($manufacture, $model, $model_id) = $auto->getCarInfo($auto_typ_id);
                list($manufacture_cap, , $model_id_cap,) = $auto->getAutoDescr($manufacture, $model, $model_id, $auto_typ_id);
                $form = str_replace("{applicable_cap}", "<a href=\"/\">$manufacture_cap $model_id_cap</a>", $form);
            }
        }

        $articleData = $this->getArticleInfo($art_id);
        $article_nr_displ = $articleData["article_nr_displ"];
        $brand_id = $articleData["brand_id"];
        $brand_name = $articleData["brand_name"];
        $article_name = $articleData["article_name"];

//        if ($client->checkRetailClientCategory($this->getClient()) && $this->getCookieAuto() != "") {
//            $article_nr_displ = $this->getSecretString($article_nr_displ);
//        }
        $format_article = $this->getFormatAticle($article_nr_displ);

        $brand_link = "";
        $flagData = $this->getCountryFlag($brand_id);
        if ($flagData !== false) {
            $flag = $flagData["flag"];
            $country_name = $flagData["country"];
            $form = str_replace("{country_name}", $country_name, $form);
            $form = str_replace("{brand_link}", $brand_link, $form);
            $form = str_replace("{flag_name}", $flag, $form);
            $form = str_replace("{flag_visible}", "", $form);
        } else {
            $form = str_replace("{country_name}", $brand_name, $form);
            $form = str_replace("{brand_link}", $brand_link, $form);
        }

        $form = str_replace("{art_id}", $art_id, $form);
        $form = str_replace("{art_name}", $article_nr_displ, $form);
        $form = str_replace("{art_format_name}", $format_article, $form);
        $form = str_replace("{art_brand_id}", $brand_id, $form);
        $form = str_replace("{art_brand_name}", $brand_name, $form);
        $form = str_replace("{art_del}", str_replace("<br>", ", ", $articleData["delivery"]), $form);
        $form = str_replace("{del_class}", ($articleData["delivery_days"] == 0) ? "delivery-red" : (($articleData["delivery_days"] == 1) ? "delivery-blue" : (($articleData["delivery_days"] > 1) ? "delivery-dark" : "")), $form);
        $form = str_replace("{art_stock}", $articleData["stock"], $form);
        $form = str_replace("{art_price}", $articleData["price"], $form);
        $form = str_replace("{art_cur}", $articleData["currency"], $form);
        $form = str_replace("{art_basket}", $articleData["basket"], $form);
        $form = str_replace("{art_images}", $this->showArticlePhotoGallery($art_id), $form);

        $analogs = $this->shortSearchList($art_id);
        $h1 = "$article_name $brand_name $article_nr_displ";
        $form = str_replace("{analogs_list}", $analogs, $form);
        $form = str_replace("{analogs_display}", ($analogs == "") ? "dnone" : "", $form);
        $form = str_replace("{article_header}", "$h1", $form);
        $form = str_replace("{applicable_display}", "dnone", $form);
        $form = str_replace("{applicable_cap}", "", $form);
        $form = str_replace("{flag_visible}", "dnone", $form);
        $form = str_replace("{art_seo_text}", $this->getArticleSeoText($art_id, $h1), $form);

        $form = $this->replaceLang($form);

        $breadcrumbs = $this->getArticleBreadCrumb($art_id, $article_nr_displ, $brand_id);

        $title = $this->replaceLang("{site_article}");
        $title = str_replace("{h1_text}", $h1, $title);

        $description = $this->replaceLang("{site_article_description}");
        $description = str_replace("{h1_text}", $h1, $description);

        return compact("form", "title", "description", "breadcrumbs");
    }

    /*
     * show article form
     * */
    public function getArticleForm2($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $auto = new AutoClass();
        $shop = new ShopClass();
        $auto_typ_id = $this->getCookieAuto();

        $form = $this->getHtmlForm("article/new");
        if ($auto_typ_id != "") {
            if ($this->checkT2Link($auto_typ_id, $art_id)) {
                $form = str_replace("{applicable_display}", "applicable-active", $form);
                $form = str_replace("{applicable_display_text}", "{is_applicable}", $form);
                list($manufacture, $model, $model_id) = $auto->getCarInfo($auto_typ_id);
                list($manufacture_cap, , $model_id_cap,) = $auto->getAutoDescr($manufacture, $model, $model_id, $auto_typ_id);
                $form = str_replace("{applicable_cap}", "<a href=\"/\">$manufacture_cap $model_id_cap</a>", $form);
            }
        }

        $articleData = $this->getArticleInfo($art_id);
        $article_nr_displ = $articleData["article_nr_displ"];
        $brand_id = $articleData["brand_id"];
        $brand_name = $articleData["brand_name"];
        $article_name = $articleData["article_name"];

//        $client = new ClientClass();
//        if ($client->checkRetailClientCategory($this->getClient()) && $this->getCookieAuto() != "") {
//            $article_nr_displ = $this->getSecretString($article_nr_displ);
//        }
        $format_article = $this->getFormatAticle($article_nr_displ);

        $brand_link = "";
        $flagData = $this->getCountryFlag($brand_id);
        if ($flagData !== false) {
            $flag = $flagData["flag"];
            $country_name = $flagData["country"];
            $form = str_replace("{country_name}", $country_name, $form);
            $form = str_replace("{brand_link}", $brand_link, $form);
            $form = str_replace("{flag_name}", $flag, $form);
            $form = str_replace("{flag_visible}", "", $form);
        } else {
            $form = str_replace("{country_name}", $brand_name, $form);
            $form = str_replace("{brand_link}", $brand_link, $form);
        }

        $form = str_replace("{art_id}", $art_id, $form);
        $form = str_replace("{art_name}", $article_nr_displ, $form);
        $form = str_replace("{art_format_name}", $format_article, $form);
        $form = str_replace("{art_brand_id}", $brand_id, $form);
        $form = str_replace("{art_brand_name}", $brand_name, $form);
        $form = str_replace("{art_del}", str_replace("<br>", ", ", $articleData["delivery"]), $form);
        $form = str_replace("{del_class}", ($articleData["delivery_days"] == 0) ? "delivery-green" : ($articleData["delivery_days"] == 1 ? "delivery-blue" : ($articleData["delivery_days"] > 1 ? "delivery-dark" : "")), $form);
        $form = str_replace("{art_stock}", $articleData["stock"], $form);
        $form = str_replace("{art_price}", $articleData["price"], $form);
        $form = str_replace("{art_cur}", $articleData["currency"], $form);
        $form = str_replace("{art_basket}", $articleData["basket"], $form);
        $form = str_replace("{art_images}", $this->showPhotoGallery($art_id), $form);
        $article_info = $this->getArticleInfoForm($art_id, 1, 1);
        $form = str_replace("{art_info}", ($article_info != "") ? $article_info : $this->err1, $form);
        $brand_info = $this->showBrandForm($brand_id);
        $form = str_replace("{brand_info}", ($brand_info != "") ? $brand_info : $this->err1, $form);
        $form = str_replace("{art_applicable}", $this->getArticleApplForm($art_id), $form);
        $form = str_replace("{art_originals}", $this->getOriginalNumbers($art_id), $form);
        $form = str_replace("{art_proposed}", $shop->getProposedArts(), $form);

        $analogs = $this->shortSearchList($art_id);
        $form = str_replace("{analogs_list}", ($analogs != "") ? $analogs : $this->err1, $form);
        $h1 = "$article_name $brand_name $article_nr_displ";
        $form = str_replace("{article_header}", "$h1", $form);
        $form = str_replace("{applicable_display}", "dnone", $form);
        $form = str_replace("{applicable_cap}", "", $form);
        $form = str_replace("{flag_visible}", "dnone", $form);
        $form = str_replace("{art_seo_text}", $this->getArticleSeoText($art_id, $h1), $form);
        $form = str_replace("{applicable_form}", $this->getApplicableForm($art_id), $form);

        $form = str_replace("{location_fast}", "finishFastOrder('input_phone2');", $form);

        $form = str_replace("{art_id}", $art_id, $form);
        $form = str_replace("{brand_id}", $brand_id, $form);
        $form = str_replace("{suppl_id}", $articleData["suppl_id"], $form);
        $form = str_replace("{storage_id}", $articleData["storage_id"], $form);

        $form = $this->replaceLang($form);

        $breadcrumbs = $this->getArticleBreadCrumb($art_id, $article_nr_displ, $brand_id);

        $title = $h1 . " {seo_title_article}";

        $description = $this->replaceLang("{seo_article_title}");
        $description = str_replace("{h1_text}", $h1, $description);

        return compact("form", "title", "description", "breadcrumbs");
    }

    public function getApplicableForm($art_id)
    {
        $typ_id = $this->getCookieAuto();
        if ($typ_id != "") {
            $automan = new AutoClass();
            $typ_name = $automan->getCarDescription($typ_id);
            if ($this->checkT2Link($typ_id, $art_id)) {
                //success
                $form = "<img src=\"/images/applicable/success.webp\" alt=\"{applicable_success_cap}\"><span>{applicable_success_cap} $typ_name</span>";
            } else {
                $catalog = new CatalogueClass();
                $group_id = $catalog->getArticleGroupExist($art_id);
                $group_link = $this->getGroupRowLink($group_id);
                //danger
                $form = "
                <div class=\"article-info-row__applicable-danger\">
                    <div class=\"article-info-row__applicable-danger--row\">
                        <img src=\"/images/applicable/danger.webp\" alt=\"{applicable_danger_cap}\">
                        <span>{applicable_danger_cap}</span>
                    </div>
                    <div class=\"article-info-row__applicable-danger--row\">
                        <span>$typ_name <a onclick=\"deleteAutoGarage('$typ_id');\">&times;</a></span>
                    </div>
                    <div class=\"article-info-row__applicable-danger--row\">
                        <a href=\"" . $this->getSiteLink() ."$this->catalog_link/$group_link/\" class=\"btn btn-info\" style='color:white'>{choose_product_car}</a>
                        <button onclick=\"showGarageForm();\" class=\"btn btn-info btn-outline-info\">{choose_another_car}</button>
                    </div>
                </div>";
            }
        } else {
            //warning
            $form = "<img src=\"/images/applicable/warning.webp\" alt=\"{applicable_warning_cap}\"><span>{applicable_warning_cap}</span>";
        }
        return $form;
    }

    public function getSeoLinkCatalog($group_id, $brand_id = 0)
    {
        $catalog = new CatalogueClass();
        $group_name = $catalog->getGroupRowName($group_id);
        $group_link = $catalog->getGroupRowLink($group_id);
        $link = "<a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/\">$group_name</a>";
        if ($brand_id > 0) {
            $brand_name = $this->getBrandName($brand_id);
            $brand_link = $this->getBrandLink($brand_id);
            $link = "<a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/brandy=$brand_link/\">$brand_name</a>";
        }
        return $link;
    }

    public function getSeoLinkArticle($art_id_sel, $brand_id_sel)
    {
        $article_search = $this->getArticleSearch($art_id_sel);
        $article_displ = $this->getArticleDispl($art_id_sel);
        $article_name = $this->getArticleName($art_id_sel);
        $brand_name = $this->getBrandName($brand_id_sel);
        $brand_search = $this->getUrlString($brand_name);
        return "<a href=\"" . $this->getSiteLink() . "$this->article_link/$article_search/$brand_search/$art_id_sel/\">$article_name $brand_name $article_displ</a>";
    }

    public function getArticleSeoText($art_id, $h1)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $catalog = new CatalogueClass();
        $r = $db->query("SELECT `TEXT` FROM `SEO_STR_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $form = $db->result($r, 0, "TEXT");
        } else {
            $form = "
            {_still_search} {Main_Category_H1}? {_go_store_toko} {_toko} {_choose_best} {GET_PAGE_H1}. 
            {_lowest_prices} {Product_1} {_high_quality_category} {Product_Category_H1}. 
            {_cooperate} {_popular_brands} {Tags_brand_1} {and_cap} {Tags_brand_2}, {_presented_in_section} {Cat_random1} {and_cap} {Cat_random2}. 
            {_right_choice} {GET_PAGE_H2}. 
            {_fast_order} {Geo_nominative} {_other_cities}";

            $form = str_replace("{GET_PAGE_H1}", $h1, $form);

            $group_id = $catalog->getArticleGroupExist($art_id);
            $form = str_replace("{Main_Category_H1}", $this->getSeoLinkCatalog($group_id), $form);

            $r = $dbc->query("SELECT `art_id`, `brand_id`, `group_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE `group_id` != $group_id ORDER BY RAND() LIMIT 1;");
            $art_id_sel = $db->result($r, 0, "art_id");
            $brand_id_sel = $db->result($r, 0, "brand_id");
            $group_id_sel = $db->result($r, 0, "group_id");
            $form = str_replace("{Product_1}", $this->getSeoLinkArticle($art_id_sel, $brand_id_sel), $form);

            $group_id_sel_name = $this->getGroupRowName($group_id_sel);
            $group_id_sel_link = $this->getGroupRowLink($group_id_sel);
            $parrent_group = "<a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_id_sel_link/\">$group_id_sel_name</a>";
            $form = str_replace("{Product_Category_H1}", $parrent_group, $form);

            $r = $dbc->query("SELECT `brand_id`, `group_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE `group_id` != $group_id AND `group_id` != $group_id_sel ORDER BY RAND() LIMIT 2;");
            $n = $dbc->num_rows($r);
            $arr = [];
            for ($i = 1; $i <= $n; $i++) {
                $arr[] = ["group_id" => $dbc->result($r, $i - 1, "group_id"), "brand_id" => $dbc->result($r, $i - 1, "brand_id")];
            }
            $form = str_replace("{Tags_brand_1}", $this->getSeoLinkCatalog($arr[0]["group_id"], $arr[0]["brand_id"]), $form);
            $form = str_replace("{Tags_brand_2}", $this->getSeoLinkCatalog($arr[1]["group_id"], $arr[1]["brand_id"]), $form);

            $brand_id_sel1 = $arr[0]["brand_id"];
            $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE `brand_id` = $brand_id_sel1 ORDER BY RAND() LIMIT 1;");
            $group_id_sel = $dbc->result($r, 0, "group_id");
            $form = str_replace("{Cat_random1}", $this->getSeoLinkCatalog($group_id_sel), $form);

            $r = $dbc->query("SELECT `art_id`, `brand_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE `group_id` = $group_id_sel ORDER BY RAND() LIMIT 1;");
            $art_id_sel = $db->result($r, 0, "art_id");
            $brand_id_sel = $db->result($r, 0, "brand_id");
            $form = str_replace("{GET_PAGE_H2}", $this->getSeoLinkArticle($art_id_sel, $brand_id_sel), $form);

            $brand_id_sel2 = $arr[1]["brand_id"];
            $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE `brand_id` = $brand_id_sel2 ORDER BY RAND() LIMIT 1;");
            $group_id_sel2 = $dbc->result($r, 0, "group_id");
            $form = str_replace("{Cat_random2}", $this->getSeoLinkCatalog($group_id_sel2), $form);

            $r = $db->query("SELECT `CITY_NAME` FROM `SEO_LISTING_CITY` ORDER BY RAND() LIMIT 1;");
            $random_city = $db->result($r, 0, "CITY_NAME");
            $form = str_replace("{Geo_nominative}", $random_city, $form);

            $r = $db->query("SELECT `LIST_KEY` FROM `SEO_LISTING` WHERE 1;");
            $langVariables = array_column(mysqli_fetch_all($r), 0);

            foreach ($langVariables as $langVariable) {
                $form = str_replace("{" . $langVariable . "}", $this->getSeoListingName($langVariable), $form);
            }
            $form = $this->replaceLang($form);

            $db->query("INSERT INTO `SEO_STR_ARTICLES` (`ART_ID`, `TEXT`) VALUES ($art_id, '$form');");

        }
        return $form;
    }

    public function getSeoListingName($key)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `SEO_LISTING` WHERE `LIST_KEY` = '$key' ORDER BY RAND() LIMIT 1;");
        return $db->result($r, 0, "TEXT");
    }

    public function getArticleBreadCrumb($art_id, $article_nr_displ, $brand_id)
    {
        $catalog = new CatalogueClass();
        $catalog_exist = new CatalogExistClass();
        $arr = [];

        $arr[] = ["name" => "{seo_site_toko}", "link" => $catalog->getSiteLink()];
        $arr[] = ["name" => "{site_catalog}", "link" => $catalog->getSiteLink() . "$catalog->catalog_link/"];

        $group_id = $catalog->getArticleGroupExist($art_id);
        $brand_name = $catalog->getBrandName($brand_id);
        $brand_link = $catalog->getBrandLink($brand_id);
        $article_name = $this->getArticleName($art_id);

        if ($group_id > 0) {
            $head_id = $catalog_exist->getHeadExistID($group_id);
            $head_name = $catalog_exist->getHeadExistName($head_id);
            $head_link = $catalog_exist->getHeadExistLink($head_id);

            $arr[] = ["name" => "$head_name", "link" => $catalog->getSiteLink() . "$catalog->catalog_link/$head_link/"];

            $group_name = $catalog->getGroupRowName($group_id);
            $group_link = $catalog->getGroupRowLink($group_id);

            $arr[] = ["name" => "$group_name", "link" => $catalog->getSiteLink() . "$catalog->catalog_link/$group_link/"];

            $arr[] = ["name" => "$group_name $brand_name", "link" => $catalog->getSiteLink() . "$catalog->catalog_link/$group_link/brandy=$brand_link/"];
        }

        $article_text = "$article_name $brand_name $article_nr_displ";

        $format_article_search = $this->getFormatAticle($article_nr_displ);
        $format_brand_name = $this->getFormatBrand($this->getBrandName($brand_id));

        $arr[] = ["name" => "$article_text", "link" => $catalog->getSiteLink() . "$catalog->article_link/$format_article_search/$format_brand_name/$art_id/"];

        return $arr;
    }

    public function getDeliveryData($tpoint, $storage_id, $suppl_id)
    {
        $deliveryData = $this->getTpointDeliveryInfo($tpoint, $storage_id);
        $delivery_days = $deliveryData["days"];
        $delivery_short_info = $deliveryData["short"];
        if ($suppl_id != 0) {
            $deliveryData = $this->getTpointSupplDeliveryInfo($tpoint, $suppl_id, $storage_id);
            $delivery_days = $deliveryData["days"];
            $delivery_short_info = $deliveryData["short"];
        }
        return array($delivery_days, $delivery_short_info);
    }

    /*
     * get article info
     * */
    public function getArticleInfo($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $art_id = $this->getUrlNumber($art_id);
        $client = new ClientClass();
        $kours = new ExRateClass();
        $tpoint = $this->getTpointID();
        $cur = $this->getCurrentExrate();
        $cur_cap = $kours->getKoursCaptionLang($cur);

        $arr = [];
        $r = $db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2asc.AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
        WHERE t2a.ART_ID IN ($art_id) AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) AND (t2asc.AMOUNT!=NULL OR t2asc.AMOUNT!=0)
        GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
        UNION ALL
        SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2si.stock_suppl, t2si.client_storage_id, t2si.suppl_id, t2si.return_delay
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
        WHERE t2a.ART_ID IN ($art_id) AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) AND (t2si.stock_suppl!=NULL OR t2si.stock_suppl!=0)
        GROUP BY t2a.ART_ID, t2si.client_storage_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            $brand_name = $db->result($r, $i - 1, "BRAND_NAME");
            $article_name = $db->result($r, $i - 1, "NAME");
            $suppl_id = $db->result($r, $i - 1, "suppl_id");
            $stock = intval($db->result($r, $i - 1, "AMOUNT"));
            $storage_id = $db->result($r, $i - 1, "storage_id");

            $price = $this->getArticlePrice($art_id);
            if ($suppl_id != 0) {
                $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
            }
            $price = $kours->getKoursPrice($price, $cur);
            if ($cur == 1) {
                $price = $client->getClientPriceRounding($this->getClient(), $price);
            }

            list($delivery_days, $delivery_short_info) = $this->getDeliveryData($tpoint, $storage_id, $suppl_id);

            $real_stock = $stock;
            if ($stock > 10) {
                $stock = "{more_then} 10";
            }

            $basket = "moveBasket('one','$art_id','$brand_id','$real_stock','$storage_id',$suppl_id,1);";

            $arr[] = compact("article_nr_displ", "brand_id", "brand_name", "article_name", "stock", "delivery_short_info", "price", "cur_cap", "delivery_days", "basket", "storage_id", "suppl_id");
        }

        $arr = $this->multiSort($arr, "delivery_days", "price");

        $article_nr_displ = $arr[0]["article_nr_displ"];
        $brand_id = $arr[0]["brand_id"];
        $brand_name = $arr[0]["brand_name"];
        $article_name = $arr[0]["article_name"];
        $stock = $arr[0]["stock"];
        $delivery_short_info = $arr[0]["delivery_short_info"];
        $price = $arr[0]["price"];
        $cur_cap = $arr[0]["cur_cap"];
        $delivery_days = $arr[0]["delivery_days"];
        $basket = $arr[0]["basket"];
        $suppl_id = $arr[0]["suppl_id"];
        $storage_id = $arr[0]["storage_id"];

        return [
            "article_nr_displ" => $article_nr_displ,
            "brand_id" => $brand_id,
            "brand_name" => $brand_name,
            "article_name" => $article_name,
            "stock" => $stock,
            "delivery" => $delivery_short_info,
            "price" => $price,
            "currency" => $cur_cap,
            "delivery_days" => $delivery_days,
            "basket" => $basket,
            "suppl_id" => $suppl_id,
            "storage_id" => $storage_id
        ];
    }

    /*
     * show currency form
     * */
    public function getCurrencyForm($cur, $type = 0)
    {
        $kours = new ExRateClass();
        $client = new ClientClass();
        $jsFilter = $cash_add = "";
        $ch1 = $ch2 = $ch3 = 0;
        $cash_id = $client->getClientCurrency($this->getClient());
        if ($cur == 2) {
            $ch2 = "checked=\"checked\"";
        }
        elseif ($cur == 3) {
            $ch3 = "checked=\"checked\"";
        } else {
            $ch1 = "checked=\"checked\"";
        }
        if ($type == 0) {
            $jsFilter = "catalogueFilter();";
        }
        if ($type == 1) {
            $jsFilter = "showBasketForm();";
        }
        if ($cash_id == 2) {
            $cash_add = "
            <input id=\"radio_usd\" type=\"radio\" name=\"cur\" value=\"$cash_id\" $ch2 onclick=\"$jsFilter\"><label for=\"radio_usd\">$</label>";
        }
        if ($cash_id == 3) {
            $cash_add = "
            <input id=\"radio_eur\" type=\"radio\" name=\"cur\" value=\"$cash_id\" $ch3 onclick=\"$jsFilter\"><label for=\"radio_eur\">€</label>";
        }
        if ($this->getUser() != 0) {
            $cur_usd = $kours->getKours("dollar");
            $cur_eur = $kours->getKours("euro");
            $list = "
            <input id=\"radio_uah\" type=\"radio\" name=\"cur\" value=\"1\" $ch1 onclick=\"$jsFilter\">
                <label for=\"radio_uah\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{currency_cap}: &#xA USD - $cur_usd &#xA EURO - $cur_eur\">{uah_cap}</label>
            $cash_add";
        } else {
            $list = "";
        }
        return $list;
    }

    /*
     * get city form
     * */
    public function showCityForm($city_like, $city_id_sel = "")
    {
        $db = DbSingleton::getDbm();
        $city_like = $this->getNameString($city_like);
        $mas = [];
        if ($city_id_sel == "") {
            $city_id_sel = 0;
        }
        if ($city_like != "") {
            $where = "WHERE `CITY_NAME` LIKE '%$city_like%'";
        } else {
            $where = "WHERE `CITY_ID` IN ($city_id_sel, 10108, 13549, 4074, 22739)";
        }
        $r = $db->query("SELECT t2c.CITY_ID, t2c.CITY_NAME, t2r.REGION_NAME, t2s.STATE_NAME  
        FROM `T2_CITY` t2c
            LEFT JOIN `T2_REGION` t2r ON (t2r.REGION_ID = t2c.REGION_ID)
            LEFT JOIN `T2_STATE` t2s ON (t2s.STATE_ID = t2r.STATE_ID)
        $where;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_id = $db->result($r, $i - 1, "CITY_ID");
            $city = $db->result($r, $i - 1, "CITY_NAME");
            $region = $db->result($r, $i - 1, "REGION_NAME");
            $state = $db->result($r, $i - 1, "STATE_NAME");
            $location = ($region == "") ? "$city" : "$city - $region - $state";
            $selected = ($city_id == $city_id_sel);
            $mas[$i] = ["id" => $city_id, "value" => $location, "selected" => $selected];
        }
        return $mas;
    }

    public static function cacheInfoTemplates($where_art_id_str)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT`, `VALUE`, `ART_ID` FROM `T2_INFO` WHERE `ART_ID` IN ($where_art_id_str) AND `LANG_ID` = 16 ORDER BY `SORT` ASC;");
        $infoTemplates = mysqli_fetch_all($r, MYSQLI_ASSOC);
        foreach ($infoTemplates as $infoTemplate) {
            self::$infoTemplates[$infoTemplate['ART_ID']][] = $infoTemplate;
        }
    }

    /*
     * show history form
     * */
    public function showHistoryForm()
    {
        $client = new ClientClass();
        $list = $client->getClientHistory();
        $result = "";
        for ($i = 0; $i < count($list); $i++) {
            $col = $i + 1;
            $article_nr_displ = $list[$i]["article_nr_displ"];
            $brand = $list[$i]["brand"];
            $brand_link = $list[$i]["brand_link"];
            $result .= "
            <li>$col. <a href=\"" . $this->getSiteLink() . "$this->search_link/$article_nr_displ/$brand_link/\">$article_nr_displ ($brand)</a></li>";
        }
        !empty($list) ?: $result .= "<p>{empty_history}</p>";
        $form = $this->getHtmlForm("menu/history_block");
        $form = str_replace("{history_block}", $result, $form);
        return $form;
    }

    /*
     * show history form (`Phone`)
     * */
    public function showHistoryList()
    {
        $client = new ClientClass();
        $cat = new CatalogueClass();
        $list = $client->getClientHistory();
        $list_history = "";
        for ($i = 0; $i < count($list); $i++) {
            $id = $list[$i]["id"];
            $article_nr_displ = $list[$i]["article_nr_displ"];
            $format_article = $cat->getFormatAticle($article_nr_displ);
            $brand = $list[$i]["brand"];
            $brand_link = $list[$i]["brand_link"];
            $history_form = $this->getHtmlForm("history/card");
            $history_form = str_replace("{history_id}", $id, $history_form);
            $history_form = str_replace("{history_link}", "" . $this->getSiteLink() . "$this->search_link/$format_article/$brand_link/", $history_form);
            $history_form = str_replace("{history_brand}", $brand, $history_form);
            $history_form = str_replace("{history_article}", $article_nr_displ, $history_form);
            $list_history .= $history_form;
            if ($i == $this->max_history_count) break;
        }
        $form = $this->getHtmlForm("menu/history_list");
        $form = str_replace("{history_range}", $list_history, $form);
        if (count($list) == 0) {
            $form = "";
        }
        return $this->replaceLang($form);
    }

    /*
     * delete history line
     * */
    public function deleteHistoryItem($history_id)
    {
        $history_id = $this->getUrlNumber($history_id);
        $db = DbSingleton::getTokoDb();
        if ($history_id == "") {
            $cookie = $this->getSessionID();
            $client_id = $this->getClient();
            $user_id = $this->getUser();
            $where = ($user_id == 0) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";
        } else {
            $where = "`id` = $history_id";
        }
        $db->query("DELETE FROM `CLIENT_HISTORY` WHERE $where;");
        return true;
    }

    public function getArticlePhoto($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $photo_name = "";
        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID` = $art_id AND `ACTIVE` = 1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $photo_name = trim($db->result($r, 0, "PHOTO_NAME"));
        }
        return $photo_name;
    }

    public function getArticleActivePhoto($art_id)
    {
        $photo_name = $this->getArticlePhoto($art_id);
        $photo_name = ($photo_name == "") ? $this->noPhoto : "$this->uploads_link/$photo_name";
        return $photo_name;
    }

    public static function cacheArticlesPhotos($where_art_id_str)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID` IN ($where_art_id_str) AND `ACTIVE` = 1 ORDER BY `PHOTO_NAME` ASC;");
        $photos = mysqli_fetch_all($r, MYSQLI_ASSOC);
        foreach ($photos as $photo) {
            self::$articlePhotos[$photo['ART_ID']][] = $photo;
        }
    }

    public function showPhotoCertificates($brand_id)
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $arr = [];
        if ($client->checkRetailClientCategory($this->getClient())) {
            $date_cur = date("Y-m-d");
            $r = $db->query("SELECT `photo_link` FROM `T2_CERTIFICATES` WHERE `brand_id` = $brand_id AND `date_from` <= '$date_cur' AND `date_to` >= '$date_cur' AND `status` = 1;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $photo_link = $db->result($r, $i - 1, "photo_link");
                $link = "https://toko.ua/uploads/images/certificates/$photo_link";
                array_push($arr, $link);
            }
        }
        return $arr;
    }

    public function showPhotoGallery($art_id, $display = 0)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $nophoto = $this->noPhoto;
        $list = "";
        $article_name = $this->getArticleSearch($art_id);
        $brand_id = $this->getArticleBrand($art_id);
        $brand_name = $this->getBrandName($brand_id);
        $format_name = $this->getFormatAticle($article_name);
        $format_brand = $this->getFormatBrand($brand_name);
        $arr = [];

        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID` = $art_id AND `ACTIVE` = 1 ORDER BY `PHOTO_NAME` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $photo_name = trim($db->result($r, $i - 1, "PHOTO_NAME"));
            $link = "$this->uploads_link/$photo_name";
            array_push($arr, $link);
        }

        if (!empty($this->showPhotoCertificates($brand_id))) {
            $arr = array_merge($arr, $this->showPhotoCertificates($brand_id));
        }

        $i = 0; $count_pages = count($arr);
        foreach ($arr as $link) {
            $i++;
            $active = ($i == 1) ? "active" : "";
            if ($display == 1) {
                $list .= "
                <div class=\"carousel-item $active\">
                    <a itemprop=\"url\" href=\"" . $this->getSiteLink() . "$this->article_link/$format_name/$format_brand/$art_id/\">
                        <img itemprop=\"image\" class=\"lazy\" data-src=\"$link\" alt=\"Slide $i\">
                    </a>
                </div>";
            } else {
                $list .= "
                <div class=\"carousel-item $active\">
                    <img itemprop=\"image\" class=\"lazy\" data-src=\"$link\" alt=\"Slide $i\">
                    <div class=\"carousel-caption\">{photo_card_cap} $i {of_cap} $count_pages</div>
                </div>";
            }
        }

        if ($n > 0) {
            $info = "
            <div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\">
                        <div class=\"carousel-inner\" role=\"listbox\">$list</div>
                        <a class=\"carousel-control-prev\" href=\"#carouselGalleryControls\" role=\"button\" data-slide=\"prev\">
                            <span class=\"carousel-control-prev-icon\" aria-hidden=\"true\"></span>
                            <span class=\"sr-only\">Previous</span>
                        </a>
                        <a class=\"carousel-control-next\" href=\"#carouselGalleryControls\" role=\"button\" data-slide=\"next\">
                            <span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>
                            <span class=\"sr-only\">Next</span>
                        </a>
                    </div>
                </div>
            </div>";
        } else {
            $info = "
            <div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\">
                        <div class=\"carousel-inner\" role=\"listbox\">
                            <div class=\"carousel-item active\">
                                <img itemprop=\"image\" class=\"lazy\" data-src=\"https://toko.ua$nophoto\" alt=\"Slide 1\">
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        return $this->replaceLang($info);
    }

    public function showArticlePhotoGallery($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $article_nr_dspl = $this->getArticleDispl($art_id);
        $brand_name = $this->getBrandName($this->getArticleBrand($art_id));
        $article_name = $this->getArticleName($art_id);
        $article_info = "$article_name $brand_name $article_nr_dspl - {photo_card_cap}";
        $nophoto = $this->noPhoto;
        $list = "";

        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID` = $art_id AND `ACTIVE` = 1 ORDER BY `PHOTO_NAME` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $photo_name = trim($db->result($r, $i - 1, "PHOTO_NAME"));
            $active = ($i == 1) ? "active" : "";
            $list .= "
            <div class=\"carousel-item $active\">
                <div class=\"search__photo\" style=\"height: 400px;\">
                    <img class=\"lazy\" itemprop=\"image\" data-src=\"$this->uploads_link/$photo_name\" alt=\"$article_info #$i\" title=\"$article_info #$i\">
                </div>
                <div class=\"carousel-caption\">
                    {page_cap} $i {of_cap} $n
                </div>
            </div>";
        }
        if ($n == 0) {
            $gallery = "
            <div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\" style=\"border: 1px solid #e9e9e9; border-radius: .25em;\">
                        <div class=\"carousel-inner\" role=\"listbox\">
                            <div class=\"carousel-item active\">
                                <div class=\"search__photo\">
                                    <img class=\"lazy\" itemprop=\"image\" data-src=\"https://toko.ua$nophoto\" alt=\"$article_info\" title=\"$article_info\">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        } else {
            $gallery = "
            <div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\" style=\"border: 1px solid #e9e9e9; border-radius: .25em;\">
                        <div class=\"carousel-inner\" role=\"listbox\">$list</div>
                        <a class=\"carousel-control-prev\" href=\"#carouselGalleryControls\" role=\"button\" data-slide=\"prev\">
                            <span class=\"carousel-control-prev-icon\" aria-hidden=\"true\"></span>
                            <span class=\"sr-only\">Previous</span>
                        </a>
                        <a class=\"carousel-control-next\" href=\"#carouselGalleryControls\" role=\"button\" data-slide=\"next\">
                            <span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>
                            <span class=\"sr-only\">Next</span>
                        </a>
                    </div>
                </div>
            </div>";
        }

        $info = $this->getArticleInfoForm($art_id, 1, 1);
        if ($info != "") {
            $info = "<div style=\"border: 1px solid #e9e9e9; border-radius: .25em; padding: 10px;\">$info</div>";
        }
        $applicability = $this->getArticleApplForm($art_id);
        $originals = $this->getOriginalNumbers($art_id);

        $form = "
        <nav id=\"nav-Content\">
            <div class=\"nav nav-tabs\" id=\"nav-tab\" role=\"tablist\">
                <a class=\"nav-item nav-link active\" id=\"nav-1-tab\" data-toggle=\"tab\" href=\"#nav-1\" role=\"tab\" aria-controls=\"nav-1\" aria-selected=\"true\">{info_cap}</a>
                <a class=\"nav-item nav-link\" id=\"nav-2-tab\" data-toggle=\"tab\" href=\"#nav-2\" role=\"tab\" aria-controls=\"nav-2\" aria-selected=\"false\">{applicability_cap}</a>
                <a class=\"nav-item nav-link\" id=\"nav-3-tab\" data-toggle=\"tab\" href=\"#nav-3\" role=\"tab\" aria-controls=\"nav-3\" aria-selected=\"false\">{original_numbers}</a>
            </div>
        </nav>
        <div class=\"tab-content\" id=\"nav-tabContent\">
            <div class=\"tab-pane fade show active\" id=\"nav-1\" role=\"tabpanel\" aria-labelledby=\"nav-1-tab\">$gallery<br>$info</div>
            <div class=\"tab-pane fade\" id=\"nav-2\" role=\"tabpanel\" aria-labelledby=\"nav-2-tab\">
                $applicability
                <div id=\"info3_more\"></div>
            </div>
            <div class=\"tab-pane fade\" id=\"nav-3\" role=\"tabpanel\" aria-labelledby=\"nav-3-tab\">$originals</div>
        </div>";

        return $this->replaceLang($form);
    }

    public function drawLoader()
    {
        $form = $this->getHtmlForm("cars/loader-gear");
        $list = $this->getHtmlForm("loader");
        $form = str_replace("{form_range}", $list, $form);
        return $form;
    }

    /*
     * show info form
     * */
    public function showInfoForm($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $article_nr_displ = $this->getArticleDispl($art_id);
        $brand_name = $this->getBrandName($this->getArticleBrand($art_id));
        $title = "
        <span class=\"text-dark bold\">$brand_name</span> $article_nr_displ";
        $form = $this->getHtmlForm("modals/form_info");
        $form = str_replace("{info-main__photo}", $this->showPhotoGallery($art_id), $form);
        $form = str_replace("{info-main__parameters}", $this->getArticleInfoForm($art_id), $form);
        $form = str_replace("{info-applicability}", $this->getArticleApplForm($art_id), $form);
        $form = str_replace("{info-original}", $this->getOriginalNumbers($art_id), $form);
        $form = $this->replaceLang($form);
        return array($form, $title);
    }

    /*
     * применяемость
     * */
    public function getArticleApplForm($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT man.MFA_ID, man.MFA_BRAND 
        FROM `T_types` tt 
            INNER JOIN `T_models` tm ON (tm.MOD_ID = tt.TYP_MOD_ID) 
            INNER JOIN `T_manufacturers` man ON (man.MFA_ID = tm.MOD_MFA_ID) 
        WHERE tt.TYP_ID IN (
            SELECT `TYP_ID` FROM `T2_LINKS` WHERE `ART_ID` = $art_id
        ) AND tt.ACTIVE = 1 
        GROUP BY man.MFA_ID 
        ORDER BY man.MFA_BRAND ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $brand_id = $db->result($r, $i - 1, "MFA_ID");
                $brand = $db->result($r, $i - 1, "MFA_BRAND");
                $list .= "
                <a class=\"info__applicability-checked\" onclick='getArticleApplModelForm(\"$art_id\", \"$brand_id\", this)'><i class=\"fas fa-car\"></i>$brand</a>";
            }
        } else {
            $list = $this->err1;
        }
        return $list;
    }

    // применяемость на машину
    public function getArticleApplModelForm($art_id, $mfa_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $mfa_id = $this->getUrlNumber($mfa_id);
        $db = DbSingleton::getTokoDb();
        $list = "
        <div class=\"search__appl-tcd\">";
        $r = $db->query("SELECT tt.TYP_ID, tt.TYP_MMT_TEXT, 
        CASE WHEN tt.TYP_PCON_START = 0 THEN '-' ELSE tt.TYP_PCON_START END AS TYP_PCON_START,
        CASE WHEN tt.TYP_PCON_END = 0 THEN '-' ELSE tt.TYP_PCON_END END AS TYP_PCON_END
        FROM `T_types` tt 
            INNER JOIN `T_models` tm ON (tm.MOD_ID = tt.TYP_MOD_ID)
            INNER JOIN `T_manufacturers` man ON (man.MFA_ID = tm.MOD_MFA_ID)
        WHERE tt.TYP_ID IN (
            SELECT `TYP_ID` FROM `T2_LINKS` WHERE `ART_ID` = $art_id
        ) AND tm.MOD_MFA_ID = $mfa_id AND tt.ACTIVE = 1 
        GROUP BY tt.TYP_ID 
        ORDER BY tm.Model ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $typ_id = $db->result($r, $i - 1, "TYP_ID");
            $model = $db->result($r, $i - 1, "TYP_MMT_TEXT");
            $d_start = $db->result($r, $i - 1, "TYP_PCON_START");
            $d_end = $db->result($r, $i - 1, "TYP_PCON_END");
            if (strlen($d_start) == 6) {
                $d_start = substr($d_start, 0, 4) . "." . substr($d_start, 4, 2);
            }
            if (strlen($d_end) == 6) {
                $d_end = substr($d_end, 0, 4) . "." . substr($d_end, 4, 2);
            }
            if ($d_end == "" || $d_end == "-") {
                $d_end = "{cur_time}";
            }
            $list .= "
            <li class=\"list-inline\">
                <a onclick=\"getArticleApplModelInfoForm('$art_id', '$typ_id')\">$model ($d_start-$d_end)</a> 
                <div id=\"AMI$typ_id\"></div>
            </li>";
        }
        if ($n > 20) {
            $list = "
            <div>$list</div>";
        }
        $list .= "
        </div>";
        $list = $this->replaceLang($list);
        return $list;
    }

    // применяемость на машину и модель
    public function getArticleApplModelInfoForm($art_id, $typ_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        $list = "";
        $r = $db->query("SELECT tt.TYP_TEXT, tt.FUEL_ID, tt.TYP_KW_FROM, tt.TYP_HP_FROM, tt.TYP_CCM, tt.ENG_Cod, 
            CASE WHEN tt.TYP_PCON_START = 0 THEN '' ELSE tt.TYP_PCON_START END AS TYP_PCON_START,
            CASE WHEN tt.TYP_PCON_END = 0 THEN '' ELSE tt.TYP_PCON_END END AS TYP_PCON_END
        FROM `T2_LINKS` tl 
            INNER JOIN `T_types` tt ON (tt.TYP_ID = tl.TYP_ID) 
            INNER JOIN `T_models` tm ON (tm.MOD_ID = tt.TYP_MOD_ID) 
            INNER JOIN `T_manufacturers` man ON (man.MFA_ID = tm.MOD_MFA_ID)
        WHERE tl.ART_ID = $art_id AND tt.TYP_ID = $typ_id AND tt.ACTIVE = 1 
        GROUP BY tm.MOD_ID 
        ORDER BY tt.TYP_TEXT ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $TYP_TEXT = $db->result($r, $i - 1, "TYP_TEXT");
            $fuel = $db->result($r, $i - 1, "FUEL_ID");
            $fuel_name = $automan->getFuelName($fuel);
            $start = $db->result($r, $i - 1, "TYP_PCON_START");
            $end = $db->result($r, $i - 1, "TYP_PCON_END");
            $TYP_KW_FROM = $db->result($r, $i - 1, "TYP_KW_FROM");
            $TYP_HP_FROM = $db->result($r, $i - 1, "TYP_HP_FROM");
            $TYP_CCM = $db->result($r, $i - 1, "TYP_CCM");
            $ENG_Cod = $db->result($r, $i - 1, "ENG_Cod");
            if (strlen($start) == 6) {
                $start = substr($start, 0, 4) . "." . substr($start, 4, 2);
            }
            if (strlen($end) == 6) {
                $end = substr($end, 0, 4) . "." . substr($end, 4, 2);
            }
            $list .= "
            <tr class=\"pointer\" href=\"" . $this->getSiteLink() . "$this->catalog_link/\" style=\"font-size: .8em;\">
                <td>$fuel_name</td>
                <td>$TYP_TEXT</td>
                <td>$start - $end</td>
                <td>$TYP_KW_FROM / $TYP_HP_FROM</td>
                <td>$TYP_CCM</td>
                <td>$ENG_Cod</td>
            </tr>";
        }
        $form = $this->getHtmlForm("cat_modif_group_form");
        $form = str_replace("{cat_modif_list}", $list, $form);
        $form = "<div>$form</div>";
        return $this->replaceLang($form);
    }

    public function getArticleInfoForm($art_id, $display = 0, $type = 0)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $info = "";
        $article_name = $this->getArticleSearch($art_id);
        $brand_name = $this->getBrandName($this->getArticleBrand($art_id));
        $format_name = $this->getFormatAticle($article_name);
        $format_brand = $this->getFormatBrand($brand_name);
        $r = $db->query("SELECT `TEXT`, `VALUE` FROM `T2_INFO` WHERE `ART_ID` = $art_id AND `LANG_ID` = 16 ORDER BY `SORT` ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $class = (!$display) ? "info__table" : "info__table_min";
            $info .= "<table class='$class'>";
            $max = $n;
            $type ?: ($n <= 5) ?: $max = 5;
            for ($i = 1; $i <= $max; $i++) {
                $text = $db->result($r, $i - 1, "TEXT");
                $value = $db->result($r, $i - 1, "VALUE");
                $info .= "<tr>
                    <td class=\"text-left bold\">$text</td> 
                    <td class=\"text-right\">$value</td>
                </tr>";
            }
            $info .= "</table>";
            $type ?: ($n <= 5) ?: $info .= "<p style='font-weight: bold; margin-bottom: 0; margin-top: 15px; text-align: center;'>
                <a class=\"search__more\" href=\"" . $this->getSiteLink() . "$this->article_link/$format_name/$format_brand/$art_id/\">
                    {more_read}
                </a>    
            </p>";
            (!$display) ?: $info = "<div>$info</div>";
        } else {
            $info = (!$display) ? "{info_cap}" : "";
        }
        $info = str_replace('"', "", $info);
        return $this->replaceLang($info);
    }

    /*
     * show banner form (`Home page`)
     * */
    public function getCarsBanner()
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $indicators = $items = "";
        $k = 0;
        $r = $db->query("SELECT `TITLE_$postfix`, `TEXT_$postfix`, `IMAGE` FROM `T2_BANNERS` WHERE `STATUS` = 1 ORDER BY `POSITION` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $title = $db->result($r, $i - 1, "TITLE_$postfix");
            $text = $db->result($r, $i - 1, "TEXT_$postfix");
            $image = $db->result($r, $i - 1, "IMAGE");
            $class = ($k == 0) ? "active" : "";
            $indicators .= "<li class=\"$class\" data-target=\"#carouselBanner\" data-slide-to=\"$k\"></li>";
            $items .= "<div class=\"carousel-item $class\">" . $this->getCarsBannerItem($title, $text, "/images/banners/" . $image) . "</div>";
            $k++;
        }
        $form = $this->getHtmlForm("home/banner");
        $form = str_replace("{carousel_indicators}", $indicators, $form);
        $form = str_replace("{carousel_items}", $items, $form);
        return $form;
    }

    /*
     * show banner item
     * */
    public function getCarsBannerItem($title, $text, $image)
    {
        $form = $this->getHtmlForm("home/banner_item");
        $form = str_replace("{banner_title}", $title, $form);
        $form = str_replace("{banner_text}", $text, $form);
        $form = str_replace("{banner_image}", $image, $form);
        return $form;
    }

    public function showSitemap()
    {
        $db = DbSingleton::getTokoDb();
        $site_link = $this->getSiteLink();
        $list = "<a href=\"$site_link\">{site_main}</a>";

        $arr = [];

        $r = $db->query("SELECT thcg.`HEAD_ID`, thcg.`CAT_ID`, thcg.`GROUP_ID` 
        FROM `T2_TREE_HCG_EXIST` thcg
            LEFT JOIN `T2_TREE_HEAD_EXIST` th ON (th.`HEAD_ID` = thcg.`HEAD_ID`)
            LEFT JOIN `T2_TREE_CAT_EXIST` tc ON (tc.`CAT_ID` = thcg.`CAT_ID`)
            LEFT JOIN `T2_TREE_GROUP_EXIST` tg ON (tg.`GROUP_ID` = thcg.`GROUP_ID`)
        WHERE th.`STATUS` = 1 AND tc.`STATUS` = 1 AND tg.`STATUS` = 1
        ORDER BY thcg.`POPULAR` DESC, thcg.`HEAD_ID`, thcg.`CAT_ID`, thcg.`GROUP_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $cat_id = $db->result($r, $i - 1, "CAT_ID");
            $group_id = $db->result($r, $i - 1, "GROUP_ID");
            $arr[$head_id][$cat_id][] = $group_id;
        }

        $list .= "<ul>";
        foreach ($arr as $head_id => $cats) {
            $head_name = $this->getHeadRowName($head_id);
            $head_link = $this->getHeadRowLink($head_id);
            $list .= "<li><a href=\"$site_link$head_link/\">$head_name</a>";
            $list .= "<ul>";
            foreach ($cats as $cat_id => $groups) {
                $cat_name = $this->getCatRowName($cat_id);
                $cat_link = $this->getCatRowLink($cat_id);
                $list .= "<li><a href=\"$site_link$head_link/$cat_link/\">$cat_name</a>";
                $list .= "<ul>";
                foreach ($groups as $group_id) {
                    $group_name = $this->getGroupRowName($group_id);
                    $group_link = $this->getGroupRowLink($group_id);
                    $list .= "<li><a href=\"$site_link$group_link/\">$group_name</a></li>";
                }
                $list .= "</ul>";
                $list .= "</li>";
            }
            $list .= "</ul>";
            $list .= "</li>";
        }
        $list .= "</ul>";

        $form = $this->getHtmlForm("menu/sitemap");
        $form = str_replace("{sitemap_list}", $list, $form);
        return $form;
    }

}
