<?php

class MenuClass extends CatalogueClass
{

    use Helper;
    use Variables;

    /*
     * get main form images
     * */
    public function getImages($content)
    {
        $dist = "/images/";
        $content = str_replace("{image_logotype}", $dist . "logo.png", $content);
        $content = str_replace("{image_logotype_phone}", $dist . "logo-phone.png", $content);
        return $content;
    }

    /*
     * get news state title
     * */
    public function getNewsStateTitle($state_id)
    {
        $db = DbSingleton::getTokoDb();
        $state_id = $this->getUrlNumber($state_id);
        $r = $db->query("SELECT `caption` FROM `news` WHERE `id` = $state_id LIMIT 1;");
        $title = $db->result($r, 0, "caption");
        $title = str_replace(str_split('.+\/:*?"<>|!?'), "", $title);
        if ($title == "") {
            $title = $this->replaceLang("{news_one_cap}" . "-$state_id");
        }
        return $title;
    }

    /*
     * get reviews state title
     * */
    public function getReviewStateTitle($state_id)
    {
        $state_id = $this->getUrlNumber($state_id);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `TITLE_$postfix` FROM `T2_REVIEWS` WHERE `ID` = $state_id LIMIT 1;");
        $title = $db->result($r, 0, "TITLE_$postfix");
        $title = str_replace(str_split('.+\/:*?"<>|!?'), "", $title);
        if ($title == "") {
            $title = $this->replaceLang("{state_one_cap}" . "-$state_id");
        }
        return $title;
    }

    /*
     * show news form
     * */
    public function showNews()
    {
        $db = DbSingleton::getTokoDb();
        $language_id = $this->getLanguage();
        if ($language_id == 2) {
            $language_id = 5;
        }
        $list = "";
        $nophoto = $this->noPhoto;
        $err1 = $this->err1;
        $date_cur = date("Y-m-d");
        $r = $db->query("SELECT `id`, `caption`, `short_desc`, `data` FROM `news` WHERE `lang_id` = $language_id AND `data` <= '$date_cur' AND `status` = 1 ORDER BY `data` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $state_id = $db->result($r, $i - 1, "id");
                $title = $db->result($r, $i - 1, "caption");
                if ($title == "") {
                    $title = $this->replaceLang("{news_one_cap}" . "-$state_id");
                }
                $format_title = $this->formatUrlText($title);
                $short_desc = $db->result($r, $i - 1, "short_desc");
                $date = $db->result($r, $i - 1, "data");
                $img_file = $this->getNewsImage($state_id);
                $img = ($img_file != "")
                    ? "<img itemprop=\"image\" class=\"lazy\" data-src=\"https://toko.ua/uploads/images/news/$language_id/$state_id/$img_file\" src=\"https://toko.ua$nophoto\" alt=\"image\">"
                    : "";
                $list .= "
                <div itemprop=\"publisher\" itemtype=\"https://schema.org/Organization\" itemscope class=\"row news-block__item\">
                    <div class=\"col-8\">
                        <h4>$date</h4>
                        <h2 itemprop=\"name\">$title</h2>
                        <h3 itemprop=\"description\">$short_desc</h3><br>
                        <a itemprop=\"url\" href=\"" . $this->getSiteLink() . "$this->news_link/state/$state_id/$format_title/\">{details_cap} <span class=\"fas fa-angle-right\"></span></a>
                    </div>
                    <div class=\"col-4\" style=\"padding: 10px 0;\">$img</div>
                </div>";
            }
        } else {
            $list = "<div class=\"content\"><h2>$err1<h2></div>";
        }
        $form = $this->getHtmlForm("news/form");
        $form = str_replace("{news_range}", $list, $form);
        return $form;
    }

    public function getNewsData($state_id)
    {
        $state_id = $this->getUrlNumber($state_id);
        $db = DbSingleton::getTokoDb();
        $language_id = $this->getLanguage();
        if ($language_id != 1) {
            $language_id = 5;
        }
        $r = $db->query("SELECT `caption`, `desc`, `data` FROM `news` WHERE `id` = $state_id;");
        $title = $db->result($r, 0, "caption");
        if ($title == "") {
            $title = $this->replaceLang("{news_one_cap}" . "-$state_id");
        }
        $text = $db->result($r, 0, "desc");
        $date = $db->result($r, 0, "data");
        $img_file_name = $this->getNewsImage($state_id);
        $img_file = "/uploads/images/news/$language_id/$state_id/" . $img_file_name;
        $img = ($img_file_name != "") ? "<p><img itemprop=\"image\" src=\"$img_file\" alt=\"state\"></p>" : "";
        $format_title = $this->formatUrlText($title);
        $url = $this->getSiteLink() . "$this->news_link/state/$state_id/$format_title/";
        return compact("title", "date", "img_file", "img", "text", "url");
    }

    /*
     * show news state form
     * */
    public function showNewsState($state_id)
    {
        $newsData = $this->getNewsData($state_id);
        $list = "
        <div class=\"news-state\">
            <h1>" . $newsData['title'] . "</h1>
            <h2>" . $newsData['date'] . "</h2>
            " . $newsData['img'] . "
            <div itemprop=\"description\">" . $newsData['text'] . "</div>
        </div>";
        $form = $this->getHtmlForm("news/card");
        $form = str_replace("{state_id}", $state_id, $form);
        $form = str_replace("{state_info}", ($state_id > 0) ? $list : "<h1>$this->err1</h1>", $form);
        return $form;
    }

    public function getNewsMetaTags($state_id)
    {
        $newsData = $this->getNewsData($state_id);
        $form = $this->getHtmlForm("article/social");
        $form = str_replace("{h1_meta_tag}", $newsData["title"], $form);
        $form = str_replace("{url_meta_tag}", $newsData["url"], $form);
        $form = str_replace("{main_image_cap}", $newsData["img_file"], $form);
        return $form;
    }

    /*
     * show special offers form
     * */
    public function showSpecialOffers($update_actions)
    {
        $form = $this->getHtmlForm("menu/special_offers");
        list($list, $arts) = $this->getSpecialOffersList("", $update_actions);
        $form = str_replace("{special_offers_update}", $update_actions, $form);
        $form = str_replace("{special_offers_range}", $list, $form);
        $form = str_replace("{special_offers_filter}", $this->getSpecialOffersFilterList($arts), $form);
        return $form;
    }

    /*
     * show special offers list
     * */
    public function getSpecialOffersList($template_id, $update_actions)
    {
        $template_id = $this->getUrlNumber($template_id);
        $update_actions = $this->getNameString($update_actions);
        $db = DbSingleton::getDbm();
        $kours = new ExRateClass();
        $showform = new FormClass();
        $client_id = $this->getClient();
        $err1 = $this->err1;
        $group_arts = [];
        $status_new = 0;
        $cur_data = date("Y-m-d");

        $where_arts = "";
        if ($template_id != "" && $template_id != "0") {
            $arts = $this->getGoodsGroupArts($template_id);
            if ($arts != "") {
                $where_arts = "AND ac.art_id IN ($arts)";
            }
        }

        $r = $db->query("SELECT ac.* 
        FROM `ACTION_CLIENTS` ac
            LEFT JOIN `ACTION_CLIENTS_LIST` acl ON (acl.action_id=ac.id)
            LEFT JOIN `ACTION_CLIENTS_CATEGORY` acc ON (acc.action_id=ac.id)
        WHERE (acl.client_id = $client_id OR acc.category_id IN (
            SELECT `client_category` FROM `A_CLIENTS` WHERE `id` = $client_id
        )) $where_arts AND ac.data >= '$cur_data';");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $list = "
            <div class=\"row\">";
            $arr = [];
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $article_nr_displ = $this->getArticleDispl($art_id);
                $amount = $db->result($r, $i - 1, "amount");
                $max_amount = $db->result($r, $i - 1, "max_amount");
                $timestamp = $db->result($r, $i - 1, "timestamp");
                $data = $db->result($r, $i - 1, "data");
                $status = $db->result($r, $i - 1, "status");
                $price = $db->result($r, $i - 1, "price");
                $real_price = $this->getArticlePrice($art_id);
                $real_price = $kours->getKoursFromUAH($real_price, 2);
                $discount = round((($real_price - $price) * 100) / $real_price);
                if ($update_actions != "") {
                    if ($status && $timestamp > "$update_actions 00:00:00") {
                        $status_new = 1;
                    }
                }
                $arr[$i] = compact("status_new", "art_id", "article_nr_displ", "amount", "max_amount", "timestamp", "data", "status", "discount");
            }

            $far_status = $far_article = [];
            foreach ($arr as $key => $row) {
                $far_status[$key] = $row["status_new"];
                $far_article[$key] = $row["article_nr_displ"];
            }
            array_multisort($far_status, SORT_DESC, $far_article, SORT_ASC, $arr);

            for ($i = 0; $i < $n; $i++) {
                $art_id = $arr[$i]["art_id"];
                $article_nr_displ = $arr[$i]["article_nr_displ"];
                $amount = $arr[$i]["amount"];
                $max_amount = $arr[$i]["max_amount"];
                $timestamp = $arr[$i]["timestamp"];
                $data = $arr[$i]["data"];
                $status = $arr[$i]["status"];
                $status_new = $arr[$i]["status_new"];
                $discount = $arr[$i]["discount"];

                $group_arts[] = $art_id;
                $name = $this->getArticleName($art_id);
                $article_nr_search = $this->getArticleSearch($art_id);
                $brand_id = $this->getArticleBrand($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $brand_link = $this->getBrandLink($brand_id);

                $data = ($data > 0) ? date("d.m.Y", strtotime($data)) : "{indefinitely_cap}";
                $max_amount = ($max_amount > 0) ? "{yes_cap}" : "{no_cap}";
                $link = $this->getSiteLink() . "$this->search_link/$article_nr_search/$brand_link/";
                $status_new = ($status_new) ? "<span class=\"special-offers-item__bell\" title=\"{new_cap} {offer_cap}\"><span class=\"fa fa-bell\"></span></span>" : "";

                $article_info = $showform->getArticleInfoForm($art_id);
                $info = "
                <span class=\"fas fa-info-circle tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" data-html=\"true\" title=\"$article_info\"></span>";

                if ($status) {
                    $list .= "
                    <div class=\"col-lg-4 col-12\">
                        <div class=\"special-offers-item special-offers-item-discount\">
                            <div class=\"row\">
                                <div class=\"col-7\">
                                    <span class=\"special-offers-item__date\" itemprop=\"datePublished\">$timestamp</span><br>
                                    <div class=\"special-offers-item__name\" itemprop=\"name\">
                                        <a href=\"$link\" target=\"_blank\">$article_nr_displ {from_cap} $amount {amount_abbr}.<br><span>$name</span></a>
                                    </div>
                                    <div class=\"special-offers-item__descr\" itemprop=\"description\">
                                        {offer_valid_until}: $data <br>
                                        {quantity_limited}: $max_amount 
                                    </div>
                                </div>
                                <div class=\"col-5 special-offers-item-eco\">
                                    <span class=\"special-offers-item-eco__text\">{economy_cap}</span><br>
                                    <span class=\"special-offers-item-eco__number\">$discount%</span>
                                </div>
                            </div>
                            <div class=\"row\">
                                <div class=\"col-8\">
                                    <a class=\"special-offers-item__link\" href=\"$link\" target=\"_blank\"><span class=\"fa fa-link\"></span> {go_to_offer}</a>
                                </div>
                                <div class=\"col-4 text-right\">
                                   <a class=\"special-offers-item__info\" onclick=\"showInfoForm('$art_id','$article_nr_displ','$brand_name');\">$info</a>
                                   $status_new
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            }
            $list .= "</div>";
        } else {
            $list = "
            <div class=\"content\"><h2>$err1<h2></div>";
        }

        $list = $this->replaceLang($list);
        $group_arts = implode(",", $group_arts);
        return array($list, $group_arts);
    }

    /*
     * show special offers list filter
     * */
    public function getSpecialOffersFilterList($arts = "")
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $arts = trim($arts, ",");
        $where_arts = ($arts != "") ? "WHERE t2gg.ART_ID IN ($arts)" : "";
        $r = $db->query("SELECT gg.ID, gg.NAME 
        FROM `GOODS_GROUP` gg 
            LEFT OUTER JOIN `T2_GOODS_GROUP` t2gg ON (t2gg.GOODS_GROUP_ID = gg.ID)
        $where_arts 
        GROUP BY t2gg.GOODS_GROUP_ID;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "ID");
            $name = $db->result($r, $i - 1, "NAME");
            $list .= "<option value=\"$id\">$name</option>";
        }
        return $list;
    }

    /*
     * get arts from goods group
     * */
    public function getGoodsGroupArts($template_id)
    {
        $db = DbSingleton::getTokoDb();
        $arts = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_GOODS_GROUP` WHERE `GOODS_GROUP_ID` = $template_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[] = $art_id;
        }
        $arts = implode(",", $arts);
        return $arts;
    }

    /*
     * GET T point Modal Form
     * */
    public function getRegionList()
    {
        $db = DbSingleton::getDbm();
        $list = "";
        $tpoint_id = $this->getTpointID();
        $language_id = $this->getLanguage();
        $r = $db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id = t2.id)
        WHERE t2.status = 1 AND t2a.lang_id = $language_id 
        ORDER BY t2.position DESC, t2a.full_name ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $list = "
            <form action=\"\" autocomplete=\"off\">";
            $ch = "";
            for ($i = 1; $i <= $n; $i++) {
                $id = $db->result($r, $i - 1, "id");
                $region = $db->result($r, $i - 1, "full_name");
                $address = $db->result($r, $i - 1, "address");
                ($tpoint_id == "") ?: ($ch = ($id == $tpoint_id) ? "checked='checked'" : "");
                $list .= "
                <label class=\"container_radio\"> 
                    $region ($address)<input type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onClick=\"selectRegion('$id');\">
                    <span class=\"radiomark\"></span>
                </label>";
            }
            $list .= "</form>";
        }
        return $list;
    }

    /*
     * get modal phone regions
     * */
    public function getRegionListPhone()
    {
        $db = DbSingleton::getDbm();
        $list =  "";
        $tpoint_id = $this->getTpointID();
        $language_id = $this->getLanguage();
        $r = $db->query("SELECT t2.id, t2a.full_name
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id = t2.id)
        WHERE t2.status = 1 AND t2a.lang_id = $language_id 
        ORDER BY t2.position DESC, t2a.full_name ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $list = "
            <form action=\"\" autocomplete=\"off\">";
            $ch = "";
            for ($i = 1; $i <= $n; $i++) {
                $id = $db->result($r, $i - 1, "id");
                $region = $db->result($r, $i - 1, "full_name");
                ($tpoint_id == "") ?: ($ch = ($id == $tpoint_id) ? "checked='checked'" : "");
                $list .= "
                <label class=\"container_radio-phone\">
                    $region<input type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onClick=\"selectRegion('$id');\">
                    <span class=\"radiomark-phone\"></span>
                </label>";
            }
            $list .= "</form>";
        }
        return $list;
    }

    /*
     * GET TPoint Form
     * (choose office)
     * */
    public function getRegionSelect()
    {
        $db = DbSingleton::getDbm();
        $language_id = $this->getLanguage();
        $tpoint_id = $this->getTpointID();
        $list = "";
        $r = $db->query("SELECT t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id = t2.id)
        WHERE t2.id = $tpoint_id AND t2a.lang_id = $language_id 
        ORDER BY t2.position DESC, t2a.full_name ASC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $region = $db->result($r, 0, "full_name");
            $address = $db->result($r, 0, "address");
            $list = "
            <span>{choose_office}:</span>
            <a onClick=\"showRegionForm();\">
                <span id=\"region_select\">
                    <span>$region ($address)</span>
                </span>
            </a>";
            $list = $this->replaceLang($list);
        }
        return $list;
    }

    /*
     * show contact form
     * */
    public function showContacts()
    {
        $db = DbSingleton::getTokoDb();
        $language_id = $this->getLanguage();
        $list = "";
        $r = $db->query("SELECT `title`, `address`, `schedule`, `phone` FROM `contacts_new` WHERE `lang_id` = $language_id AND `status` = 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $form_range = $this->getHtmlForm("menu/contacts_range");
                $form_range = str_replace("{contact_title}", $db->result($r, $i - 1, "title"), $form_range);
                $form_range = str_replace("{contact_address}", $db->result($r, $i - 1, "address"), $form_range);
                $form_range = str_replace("{contact_schedule}", $db->result($r, $i - 1, "schedule"), $form_range);
                $form_range = str_replace("{contact_phone}", $db->result($r, $i - 1, "phone"), $form_range);
                $list .= $form_range;
            }
        } else {
            $list = "<h2>$this->err1</h2>";
        }
        $form = $this->getHtmlForm("menu/contacts");
        $form = str_replace("{contact_block}", $list, $form);
        return $form;
    }

        /*
         * get region select (registration)
         * */
    public function getRegionForm($region = 0)
    {
        $db = DbSingleton::getTokoDb();
        $form = "";
        $r = $db->query("SELECT `STATE_ID`, `STATE_NAME` FROM `T2_STATE` ORDER BY `STATE_NAME` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "STATE_ID");
            $caption = $db->result($r, $i - 1, "STATE_NAME");
            $checked = ($id == $region) ? "selected=\"selected\"" : "";
            $form .= "
            <option value=\"$id\" $checked>$caption</option>";
        }
        return $form;
    }

    /*
     * get client type select (registration)
     * */
    public function showTypeForm($org_type = "")
    {
        $db = DbSingleton::getDbm();
        $form = "";
        if ($org_type == "" || $org_type == 0) {
            $org_type = 1;
        }
        $r = $db->query("SELECT `id`, `full_name` FROM `A_ORG_TYPE` ORDER BY `id` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "id");
            $caption = $db->result($r, $i - 1, "full_name");
            $checked = ($id == $org_type) ? "selected=\"selected\"" : "";
            $form .= "<option value=\"$id\" $checked>$caption</option>";
        }
        return $form;
    }

    /*
     * get news image
     * */
    public function getNewsImage($news_id)
    {
        $db = DbSingleton::getTokoDb();
        $language_id = $this->getLanguage();
        if ($language_id != 1) {
            $language_id = 5;
        }
        $file = "";
        $r = $db->query("SELECT `id` FROM `news_galery` WHERE `cat` = $news_id ORDER BY `main` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $id = $db->result($r, 0, "id");
            if (file_exists("uploads/images/news/$language_id/$news_id/$id.jpg")) {
                $file = "$id.jpg";
            }
        }
        return $file;
    }

    /*
     * show contacts bottom form
     * */
    public function showContactsBottom()
    {
        $db = DbSingleton::getTokoDb();
        $list_phone = $list_email = $list_address = "";
        // PHONE
        $r = $db->query("SELECT `text`, `icon`, `link` FROM `contacts_bottom_new` WHERE `status` = 1 AND `type_contact` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $text = $db->result($r, $i - 1, "text");
            $icon = $db->result($r, $i - 1, "icon");
            $link = $db->result($r, $i - 1, "link");
            $list_phone .= "
            <li>
                <a href=\"tel:$link\">
                    <span class=\"fas $icon\"></span>
                    <span>$text</span>
                </a>
            </li>";
        }
        // EMAIL
        $r = $db->query("SELECT `text`, `icon`, `link` FROM `contacts_bottom_new` WHERE `status` = 1 AND `type_contact` = 2;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $text = $db->result($r, $i - 1, "text");
            $icon = $db->result($r, $i - 1, "icon");
            $link = $db->result($r, $i - 1, "link");
            $list_email .= "
            <li>
                <a href=\"$link\">
                    <span class=\"fas $icon\"></span>
                    <span itemprop=\"email\">$text</span>
                </a>
            </li>";
        }
        // ADDRESS
        $r = $db->query("SELECT `text`, `text_short`, `icon`, `link` FROM `contacts_bottom_new` WHERE `status` = 1 AND `type_contact` = 3;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $list_address .= "
            <div itemprop=\"address\" itemscope itemtype=\"http://schema.org/PostalAddress\"><ul>";
            for ($i = 1; $i <= $n; $i++) {
                $text = $db->result($r, $i - 1, "text");
                $text_short = $db->result($r, $i - 1, "text_short");
                $icon = $db->result($r, $i - 1, "icon");
                $link = $db->result($r, $i - 1, "link");
                $list_address .= "
                <li>
                    <a href=\"$link\">
                        <span class=\"fas $icon\"></span>
                        <span itemprop=\"addressLocality\">$text_short</span>
                        <span itemprop=\"streetAddress\">$text</span>
                    </a>
                </li>";
            }
            $list_address .= "</ul></div>";
        }
        $form = $this->getHtmlForm("menu/contacts_bottom");
        $form = str_replace("{list_phone}", $list_phone, $form);
        $form = str_replace("{list_email}", $list_email, $form);
        $form = str_replace("{list_address}", $list_address, $form);
        return $this->replaceLang($form);
    }

    /*
     * show seller form
     * */
    public function showSellBlock()
    {
        $form = $this->getHtmlForm("sell/sell_form");
        $form = str_replace("{terms_cap}", $this->getHtmlForm("sell/sell_cooperation"), $form);
        $form = str_replace("{deal_cap}", $this->getHtmlForm("sell/sell_deal"), $form);
        return $form;
    }

    /*
     * send seller form (with file)
     * */
    public function saveSellerForm($company, $name, $phone, $email, $city_id, $comment)
    {
        $client = new ClientClass();
        $company = $this->getNameString($company);
        $name = $this->getNameString($name);
        $email = $this->getNameString($email);
        $comment = $this->getNameString($comment);
        $phone = $client->formatValidPhone($phone);
        $city_id = $this->getUrlNumber($city_id);
        $db = DbSingleton::getDbm();
        $cookie_id = $this->getSessionID();
        $max_bytes = 10485760;
        $format_arr = ["txt", "csv", "xls", "xlsx", "dbf"];
        $r = $db->query("SELECT `file_name`, `type`, `size` FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id` = '$cookie_id' ORDER BY `data` DESC LIMIT 1;");
        $n = $db->num_rows($r);
        $file_name = $db->result($r, 0, "file_name");
        $type = $db->result($r, 0, "type");
        $size = $db->result($r, 0, "size");
        if ($n > 0) {
            $db->query("DELETE FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id` = '$cookie_id';");
        }
        if (in_array($type, $format_arr) && $size <= $max_bytes) {
            $db->query("INSERT INTO `J_SUPPLIERS_COOPERATION` (`company`, `name`, `phone`, `email`, `city_id`, `commentary`, `file_id`, `status`) VALUES ('$company', '$name', '$phone', '$email', '$city_id', '$comment', '$file_name', 166);");
            return true;
        } else {
            return array($type, $size);
        }
    }

    /*
     * get seller image
     * */
    public function getSellerImage()
    {
        $db = DbSingleton::getDbm();
        $cookie_id = $this->getSessionID();
        $r = $db->query("SELECT `real_file_name` FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id` = '$cookie_id' ORDER BY `data` DESC LIMIT 1;");
        return $db->result($r, 0, "real_file_name");
    }

    /*
     * get garage navigation link
     * */
    public function getGarageLink()
    {
        $automan = new AutoClass();
        $garage_count = $automan->getGarageAutoCount();
        return ($garage_count == "")
            ? "href=\"" . $this->getSiteLink() . "$this->catalog_link/auto/\""
            : "onclick=\"showGarageForm();\"";
    }

    /*
     * show reviews form
     * */
    public function showReviews()
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $list = "";
        $r = $db->query("SELECT `ID`, `DATA`, `IMG`, `TITLE_$postfix` FROM `T2_REVIEWS` WHERE `STATUS` = 1 ORDER BY `data` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $title = $db->result($r, $i - 1, "TITLE_$postfix");
                $state_id = $db->result($r, $i - 1, "ID");
                $transcript = $this->formatUrlText($title);
                $form_range = $this->getHtmlForm("reviews/form_range");
                $form_range = str_replace("{review_title}", $title, $form_range);
                $form_range = str_replace("{review_date}", $db->result($r, $i - 1, "DATA"), $form_range);
                $form_range = str_replace("{review_img}", $db->result($r, $i - 1, "IMG"), $form_range);
                $form_range = str_replace("{page_review_link}", $this->getSiteLink() . "$this->reviews_link/state/$state_id/$transcript/", $form_range);
                $list .= $form_range;
            }
        }
        $form = $this->getHtmlForm("reviews/form");
        $form = str_replace("{form_range}", $list, $form);
        return $form;
    }

    public function getReviewsData($state_id)
    {
        $state_id = $this->getUrlNumber($state_id);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `DATA`, `TITLE_$postfix`, `TEXT_$postfix` FROM `T2_REVIEWS` WHERE `ID` = $state_id;");
        $date = $db->result($r, 0, "DATA");
        $title = $db->result($r, 0, "TITLE_$postfix");
        $text = $db->result($r, 0, "TEXT_$postfix");
        $format_title = $this->formatUrlText($title);
        $url = $this->getSiteLink() . "$this->reviews_link/state/$state_id/$format_title/";
        return compact("title", "text", "date", "url");
    }

    /*
     * show reviews state form
     * */
    public function getReviewsState($state_id)
    {
        $reviewsData = $this->getReviewsData($state_id);
        $list = $this->getHtmlForm("reviews/card_range");
        $list = str_replace("{review_date}", $reviewsData["date"], $list);
        $list = str_replace("{review_title}", $this->replaceTextTags($reviewsData["title"], ""), $list);
        $list = str_replace("{review_text}", $this->replaceTextTags($reviewsData["text"], $reviewsData["title"]), $list);
        $form = $this->getHtmlForm("reviews/card");
        $form = str_replace("{state_id}", $state_id, $form);
        $form = str_replace("{state_info}", ($state_id > 0) ? $list : "<h1>$this->err1</h1>", $form);
        return $form;
    }

    public function getReviewsMetaTags($state_id)
    {
        $reviewsData = $this->getReviewsData($state_id);
        $form = $this->getHtmlForm("article/social");
        $form = str_replace("{h1_meta_tag}", $reviewsData["title"], $form);
        $form = str_replace("{url_meta_tag}", $reviewsData["url"], $form);
        $form = str_replace("{main_image_cap}", "", $form);
        return $form;
    }
    
    public function replaceTextTags($text, $h1_text = "")
    {
        $text = str_replace("<h1>", "", $text);
        $text = str_replace("</h1>", "", $text);

        if ($h1_text != "") {
            $imgs = [];

            $text_end = $text;
            for ($i = 0; $i <= strlen($text); $i++) {
                $pos_start = strpos($text_end, "<img", $i);
                if ($text_end == substr($text_end, $pos_start)) {
                    break;
                }
                $text_end = substr($text_end, $pos_start);
                $pos_end = (strpos($text_end, ">", 0)) + 1;
                $text_img = substr($text_end, 0, $pos_end);
                $imgs[] = $text_img;
            }

            $count = 0;
            foreach ($imgs as $img) {
                if (strpos($img, "alt") === false) {
                    $count++;
                    $formate_img = str_replace("<img ", "<img alt='$h1_text - {photo_cap} $count' title='$h1_text - {photo_cap} $count'", $img);
                    $formate_img = $this->replaceLang($formate_img);
                } else {
                    $formate_img = $img;
                }
                $text = str_replace("$img", "$formate_img", $text);
            }
        }

        return $text;
    }

    /*
     * show scan form (Bonus) Validate
     * */
    public function showScanPhoneForm($phone)
    {
        $form = $this->getHtmlForm("bonus/phone_valid");
        $form = str_replace("{text_phone}", $phone, $form);
        return $form;
    }

    /*
     * show catalog FAQ form
     * */
    public function getCatalogFaqForm($h1 = "")
    {
        $form = $this->getHtmlForm("faq/form");
        $form = str_replace("{form_request}", $this->getHtmlForm("faq/request"), $form);
        $form = $this->replaceLang($form);
        $form = str_replace("{faq_h1}", $h1, $form);
        $form = str_replace("{help_form}", $this->getHtmlForm("faq/help"), $form);
        return $form;
    }

    /*
     * site warning message
     * */
    public function getSiteWarningMessage()
    {
        $db = DbSingleton::getTokoDb();
        $form = "";
        $r = $db->query("SELECT `TEXT`, `STYLES`, `STATUS` FROM `T2_SITE_CONFIGS` WHERE `BLOCK` = 'site_warning_message' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $text = $db->result($r, 0, "TEXT");
            $styles = $db->result($r, 0, "STYLES");
            $status = $db->result($r, 0, "STATUS");
            if ($status) {
                $form = "		
                <div class=\"row dblock\" style='$styles'>
                    <span>$text</span>
                </div>";
            }
        }
        return $this->replaceLang($form);
    }

    /*
     * get phone nav menu
     * */
    public function getMenuBar($head_id_sel = 0)
    {
        $head_id_sel = $this->getUrlNumber($head_id_sel);
        $db = DbSingleton::getTokoDb();
        $catalogue = new CatalogueClass();
        $list = "";
        if (empty($head_id_sel)) {
            $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_CONSTRUCTOR` WHERE 1 ORDER BY `POSITION` ASC;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $list = $this->getHtmlForm("bar/main");
                $head_list = "";
                for ($i = 1; $i <= $n; $i++) {
                    $head_id = $db->result($r, $i - 1, "HEAD_ID");
                    $head_name = $this->getHeadRowName($head_id);
                    $head_list .= "
                    <div class=\"menu-bar-head__item\" onclick=\"getMenuBar('$head_id')\">
                        $head_name
                    </div>";
                }
                $list = str_replace("{head_list}", $head_list, $list);
                $list = str_replace("{media_list}", $this->getPhoneNav(), $list);
                $list = str_replace("{contacts_list}", $this->getPhoneContacts(), $list);
            }
        } else {
            $arr = [];
            $head_name = $this->getHeadRowName($head_id_sel);
            $r = $db->query("SELECT he.`CAT_ID`, he.`GROUP_ID`, he.`POPULAR`
            FROM `T2_TREE_CONSTRUCTOR_STR` cs
                LEFT JOIN `T2_TREE_HCG_EXIST` he ON (he.HEAD_ID = cs.HEAD_ID AND he.CAT_ID = cs.CAT_ID)
                LEFT JOIN `T2_TREE_GROUP_EXIST` ge ON (ge.GROUP_ID = he.GROUP_ID)
            WHERE cs.`CAT_ID` > 0 AND ge.`STATUS` = 1 AND cs.`HEAD_ID` = $head_id_sel
            ORDER BY he.`POPULAR` DESC, he.`CAT_ID` ASC, he.`GROUP_ID` ASC;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $cat_id = $db->result($r, $i - 1, "CAT_ID");
                $group_id = $db->result($r, $i - 1, "GROUP_ID");
                $popular = $db->result($r, $i - 1, "POPULAR");
                if ($popular == 1) {
                    $arr[0][] = $group_id;
                }
                $arr[$cat_id][] = $group_id;
            }
            if (!empty($arr)) {
                $list .= "
                <div class=\"menu-bar-head__title\" onclick=\"getMenuBar('0');\">
                    < $head_name
                </div>";
                $list .= "
                <div class=\"menu-bar-cat\">";
                foreach ($arr as $cat_id => $groups) {
                    $cat_name = $this->getCatRowName($cat_id);
                    $icon = "";
                    if ($cat_id == 0) {
                        $icon = "<span style=\"color: #f44438; margin-right: 5px;\">o</span>";
                    }
                    $list .= "
                    <div class=\"menu-bar-cat__title\">
                        $icon$cat_name
                    </div>";
                    $list .= "
                    <div class=\"menu-bar-group\">";
                    foreach ($groups as $group_id) {
                        $group_name = $this->getGroupRowName($group_id);
                        $group_link = $this->getGroupRowLink($group_id);
                        $list .= "
                        <div class=\"menu-bar-group__item\">
                            <a href=\"" . $this->getSiteLink() . "$catalogue->catalog_link/$group_link/\">$group_name</a>
                        </div>";
                    }
                    $list .= "</div>";
                }
                $list .= "</div>";
            }
        }

        $form = $this->getHtmlForm("bar/form");
        $form = str_replace("{bar_list}", $list, $form);
        return $this->replaceLang($form);
    }

    /*
     * show mobile navigation
     * */
    public function getPhoneNav()
    {
        $profile = new ProfileClass();
        $shop = new ShopClass();
        $form = $this->getHtmlForm("bar/nav");
        $form = str_replace("{site_main_link}", $this->getSiteLink(), $form);
        $form = str_replace("{profile_mobile}", $profile->getProfileInfoMobile(), $form);
        $form = str_replace("{basket_summ}", $shop->countSummBasket(), $form);
        return $form;
    }

    public function getPhoneContacts()
    {
        $profile = new ProfileClass();
        $language = new LangClass();
        $form = $this->getHtmlForm("bar/contacts");
        if (!$profile->getProfileClientInfo()) {
            $form = str_replace("{region_select}", $this->getRegionSelect(), $form);
        } else {
            $form = str_replace("{region_select}", "", $form);
        }
        $form = str_replace("{lang_select}", $language->getLanguageMenuList($this->getLanguage()), $form);
        return $form;
    }

}
