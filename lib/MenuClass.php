<?php

class MenuClass extends CatalogueClass
{

    /*
     * get main form images
     * */
    public function getImages($content)
    {
        $dist = "/images/";

        return str_replace(
            array("{image_logotype}", "{image_logotype_phone}"), 
            array($dist . "logo.png", $dist . "logo-phone.png"), 
        $content);
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

        return ($title === "")
            ? $this->replaceLang("{news_one_cap}" . "-$state_id")
            : $title;
    }

    /*
     * get reviews state title
     * */
    public function getReviewStateTitle($state_id)
    {
        $state_id = $this->getUrlNumber($state_id);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());

        $r = $db->query("SELECT `TITLE_" . $postfix . "` FROM `T2_REVIEWS` WHERE `ID` = $state_id LIMIT 1;");
        $title = $db->result($r, 0, "TITLE_$postfix");
        $title = str_replace(str_split('.+\/:*?"<>|!?'), "", $title);

        return ($title === "")
            ? $this->replaceLang("{state_one_cap}" . "-$state_id")
            : $title;
    }

    /*
     * show news form
     * */
    public function showNews($db)
    {
        $language_id = $this->getLanguage();

        if ($language_id === 2) {
            $language_id = 5;
        }

        $list       = "";
        $no_photo   = $this->noPhoto;
        $err1       = $this->err1;
        $date_cur   = date("Y-m-d");

        $r = $db->query("SELECT `id`, `caption`, `short_desc`, `data` FROM `news` 
        WHERE `lang_id` = $language_id AND `data` <= '$date_cur' AND `status` = 1 
        ORDER BY `data` DESC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $state_id       = $db->result($r, $i - 1, "id");
                $title          = $db->result($r, $i - 1, "caption");
                $title          = ($title === "") ? $this->replaceLang("{news_one_cap}" . "-$state_id") : $title;
                $format_title   = $this->formatUrlText($title);
                $short_desc     = $db->result($r, $i - 1, "short_desc");
                $date           = $db->result($r, $i - 1, "data");
                $img_file       = $this->getNewsImage($state_id);
                $link = $this->getSiteLink() . "$this->news_link/state/$state_id/$format_title/";
                $img            = ($img_file !== "")
                    ? "<img itemprop=\"image\" class=\"lazy\" data-src=\"https://toko.ua/uploads/images/news/$language_id/$state_id/$img_file\" src=\"https://toko.ua$no_photo\" alt=\"image\">"
                    : "";
                $list .= str_replace(
                    array("{date}", "{title}", "{link}", "{img}", "{short_desc}"),
                    array($date, $title, $link, $img, $short_desc),
                $this->getHtmlForm("news/list"));
            }
        } else {
            $list = str_replace("{message}", $err1, $this->getHtmlForm("news/error"));
        }

        $form = $this->getHtmlForm("news/form");

        return str_replace("{news_range}", $list, $form);
    }

    public function getNewsData($db, $state_id): array
    {
        $state_id = $this->getUrlNumber($state_id);

        $language_id = $this->getLanguage();

        if ($language_id !== 1) {
            $language_id = 5;
        }

        $r = $db->query("SELECT `caption`, `desc`, `data` FROM `news` WHERE `id` = $state_id;");
        $title      = ($db->result($r, 0, "caption") === "") ? $this->replaceLang("{news_one_cap}" . "-$state_id") : $db->result($r, 0, "caption");
        $text       = $db->result($r, 0, "desc");
        $date       = $db->result($r, 0, "data");
        $img_name   = $this->getNewsImage($state_id);
        $img_file   = "/uploads/images/news/$language_id/$state_id/" . $img_name;
        $img        = ($img_name !== "")
            ? "<p><img itemprop=\"image\" src=\"$img_file\" alt=\"state\"></p>"
            : "";
        $format_url     = $this->formatUrlText($title);
        $url            = $this->getSiteLink() . "$this->news_link/state/$state_id/$format_url/";

        return compact("title", "date", "img_file", "img", "text", "url");
    }

    /*
     * show news state form
     * */
    public function showNewsState($db, $state_id)
    {
        $newsData = $this->getNewsData($db, $state_id);
        $list = str_replace(
            array("{title}", "{date}", "{img}", "{text}"),
            array($newsData['title'], $newsData['date'], $newsData['img'], $newsData['text']),
        $this->getHtmlForm("news/title"));

        return str_replace(
            array("{state_id}", "{state_info}"),
            array($state_id, ($state_id > 0) ? $list : $this->getHtmlTag("h1", $this->err1)),
        $this->getHtmlForm("news/card"));
    }

    public function getNewsMetaTags($db, $state_id)
    {
        $newsData = $this->getNewsData($db, $state_id);

        return str_replace(
            array("{h1_meta_tag}", "{url_meta_tag}", "{main_image_cap}"),
            array($newsData["title"], $newsData["url"], $newsData["img_file"]),
        $this->getHtmlForm("article/social"));
    }

    /*
     * show special offers form
     * */
    public function showSpecialOffers($update_actions)
    {
        list($list, $arts) = $this->getSpecialOffersList("", $update_actions);

        return str_replace(
            array("{special_offers_update}", "{special_offers_range}", "{special_offers_filter}"),
            array($update_actions, $list, $this->getSpecialOffersFilterList($arts)),
        $this->getHtmlForm("menu/special_offers"));
    }

    /*
     * show special offers list
     * */
    public function getSpecialOffersList($template_id, $update_actions): array
    {
        $template_id = $this->getUrlNumber($template_id);
        $update_actions = $this->getNameString($update_actions);

        $db = DbSingleton::getDbm();
        $exRate = new ExRateClass();
        $formObj = new FormClass();

        $client_id  = $this->getClient();
        $err1       = $this->err1;
        $group_arts = [];
        $status_new = 0;
        $cur_data   = date("Y-m-d");

        $where_arts = "";

        if ($template_id !== "" && $template_id !== 0) {
            $arts = $this->getGoodsGroupArts($template_id);

            if ($arts !== "") {
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
            $arr = [];
            $list = "
            <div class=\"row\">";

            for ($i = 1; $i <= $n; $i++) {
                $art_id     = $db->result($r, $i - 1, "art_id");
                $art_nr_ds  = $this->getArticleDisplay($art_id);
                $amount     = $db->result($r, $i - 1, "amount");
                $max_amount = $db->result($r, $i - 1, "max_amount");
                $timestamp  = $db->result($r, $i - 1, "timestamp");
                $data       = $db->result($r, $i - 1, "data");
                $status     = $db->result($r, $i - 1, "status");
                $price      = $db->result($r, $i - 1, "price");
                $real_price = $this->getArticlePrice($art_id);
                $real_price = $exRate->getExRateFromUAH($real_price, 2);
                $discount   = round((($real_price - $price) * 100) / $real_price);

                if (($update_actions !== "") && $status && $timestamp > "$update_actions 00:00:00") {
                    $status_new = 1;
                }

                $arr[$i] = compact("status_new", "art_id", "art_nr_ds", "amount", "max_amount", "timestamp", "data", "status", "discount");
            }

            $far_status = $far_article = [];
            foreach ($arr as $key => $row) {
                $far_status[$key] = $row["status_new"];
                $far_article[$key] = $row["art_nr_ds"];
            }
            array_multisort($far_status, SORT_DESC, $far_article, SORT_ASC, $arr);

            for ($i = 0; $i < $n; $i++) {
                $art_id     = $arr[$i]["art_id"];
                $art_nr_ds  = $arr[$i]["art_nr_ds"];
                $amount     = $arr[$i]["amount"];
                $timestamp  = $arr[$i]["timestamp"];
                $status     = $arr[$i]["status"];
                $discount   = $arr[$i]["discount"];
                $name       = $this->getArticleName($art_id);
                $art_nr_src = $this->getArticleSearch($art_id);
                $brand_id   = $this->getArticleBrand($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $brand_link = $this->getBrandLink($brand_id);
                $data       = ($arr[$i]["data"] > 0) ? date("d.m.Y", strtotime($arr[$i]["data"])) : "{indefinitely_cap}";
                $max_amount = ($arr[$i]["max_amount"] > 0) ? "{yes_cap}" : "{no_cap}";
                $link       = $this->getSiteLink() . "$this->search_link/$art_nr_src/$brand_link/";
                $status_new = ($arr[$i]["status_new"]) ? "<span class=\"special-offers-item__bell\" title=\"{new_cap} {offer_cap}\"><span class=\"fa fa-bell\"></span></span>" : "";
                $art_info   = $formObj->getArticleInfoForm($art_id);

                $group_arts[] = $art_id;

                $info = "
                <span class=\"fas fa-info-circle tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" data-html=\"true\" title=\"$art_info\"></span>";

                if ($status) {
                    $list .= str_replace(
                        array("{link}", "{timestamp}", "{art_nr_ds}", "{amount}", "{name}", "{data}", "{max_amount}", "{discount}", "{art_id}", "{brand_name}", "{info}", "{status_new}"),
                        array($link, $timestamp, $art_nr_ds, $name, $amount, $data, $max_amount, $discount, $art_id, $brand_name, $info, $status_new),
                    $this->getHtmlForm("special_offers/list"));
                }
            }

            $list .= "
            </div>";
        } else {
            $list = str_replace("{message}", $err1, $this->getHtmlForm("special_offers/error"));
        }

        $list = $this->replaceLang($list);
        $group_arts = implode(",", $group_arts);

        return array($list, $group_arts);
    }

    /*
     * show special offers list filter
     * */
    public function getSpecialOffersFilterList($arts = ""): string
    {
        $db = DbSingleton::getTokoDb();

        $list = "";
        $arts = trim($arts, ",");
        $where_arts = ($arts !== "") ? "WHERE t2gg.ART_ID IN ($arts)" : "";

        $r = $db->query("SELECT gg.ID, gg.NAME 
        FROM `GOODS_GROUP` gg 
            LEFT OUTER JOIN `T2_GOODS_GROUP` t2gg ON (t2gg.GOODS_GROUP_ID = gg.ID)
        $where_arts 
        GROUP BY t2gg.GOODS_GROUP_ID;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "ID");
            $name = $db->result($r, $i - 1, "NAME");

            $list .= str_replace(
                array("{value}", "{name}", "{checked}"),
                array($id, $name, ""),
            $this->getHtmlForm("helper/select_option"));
        }

        return $list;
    }

    /*
     * get arts from goods group
     * */
    public function getGoodsGroupArts($template_id): string
    {
        $db = DbSingleton::getTokoDb();
        $arts = [];

        $r = $db->query("SELECT `ART_ID` FROM `T2_GOODS_GROUP` WHERE `GOODS_GROUP_ID` = $template_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[] = $art_id;
        }

        return implode(",", $arts);
    }

    /*
     * GET T point Modal Form
     * */
    public function getRegionList(): string
    {
        $db = DbSingleton::getDbm();

        $salePoint  = $this->getTpointID();
        $lang_id    = $this->getLanguage();
        $form = "";

        $r = $db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id = t2.id)
        WHERE t2.status = 1 AND t2a.lang_id = $lang_id 
        ORDER BY t2.position DESC, t2a.full_name;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $list = "";
            for ($i = 1; $i <= $n; $i++) {
                $id         = $db->result($r, $i - 1, "id");
                $region     = $db->result($r, $i - 1, "full_name");
                $address    = $db->result($r, $i - 1, "address");

                (empty($salePoint)) ? $ch = "" : ($ch = ($id === $salePoint) ? "checked='checked'" : "");

                $list .= str_replace(
                    array("{id}", "{check}", "{region}", "{address}"),
                    array($id, $ch, $region, $address),
                $this->getHtmlForm("region/list"));
            }

            $form = str_replace("{list}", $list, $this->getHtmlForm("region/form"));
        }

        return $form;
    }

    /*
     * get modal phone regions
     * */
    public function getRegionListPhone(): string
    {
        $db = DbSingleton::getDbm();

        $salePoint  = $this->getTpointID();
        $lang_id    = $this->getLanguage();
        $form = "";

        $r = $db->query("SELECT t2.id, t2a.full_name
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id = t2.id)
        WHERE t2.status = 1 AND t2a.lang_id = $lang_id 
        ORDER BY t2.position DESC, t2a.full_name;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $list = "";
            for ($i = 1; $i <= $n; $i++) {
                $id     = $db->result($r, $i - 1, "id");
                $region = $db->result($r, $i - 1, "full_name");

                (empty($salePoint)) ? $ch = "" : ($ch = ($id === $salePoint) ? "checked='checked'" : "");

                $list .= str_replace(
                    array("{id}", "{check}", "{region}"),
                    array($id, $ch, $region),
                $this->getHtmlForm("region/phone_list"));
            }

            $form = str_replace("{list}", $list, $this->getHtmlForm("region/phone_form"));
        }

        return $form;
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
                $form_range = str_replace(
                    array("{contact_title}", "{contact_address}", "{contact_schedule}", "{contact_phone}"),
                    array($db->result($r, $i - 1, "title"), $db->result($r, $i - 1, "address"), $db->result($r, $i - 1, "schedule"), $db->result($r, $i - 1, "phone")),
                $this->getHtmlForm("menu/contacts_range"));

                $list .= $form_range;
            }
        } else {
            $list = $this->getHtmlTag("h2", $this->err1);
        }

        return str_replace("{contact_block}", $list, $this->getHtmlForm("menu/contacts"));
    }

    public function getRegionForm($region = 0): string
    {
        $db = DbSingleton::getTokoDb();
        $form = "";

        $r = $db->query("SELECT `STATE_ID`, `STATE_NAME` FROM `T2_STATE` ORDER BY `STATE_NAME`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id         = (int)$db->result($r, $i - 1, "STATE_ID");
            $caption    = $db->result($r, $i - 1, "STATE_NAME");
            $checked    = ($id === $region) ? "selected=\"selected\"" : "";

            $form .= str_replace(
                array("{value}", "{name}", "{checked}"),
                array($id, $caption, $checked),
            $this->getHtmlForm("helper/select_option"));
        }

        return $form;
    }

    /*
     * get client type select (registration)
     * */
    public function showTypeForm($org_type = 0): string
    {
        $db = DbSingleton::getDbm();
        $form = "";

        if (empty($org_type)) {
            $org_type = 1;
        }

        $r = $db->query("SELECT `id`, `full_name` FROM `A_ORG_TYPE` ORDER BY `id`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id         = (int)$db->result($r, $i - 1, "id");
            $caption    = $db->result($r, $i - 1, "full_name");
            $checked    = ($id === $org_type) ? "selected=\"selected\"" : "";

            $form .= str_replace(
                array("{value}", "{name}", "{checked}"),
                array($id, $caption, $checked),
            $this->getHtmlForm("helper/select_option"));
        }

        return $form;
    }

    /*
     * get news image
     * */
    public function getNewsImage($news_id): string
    {
        $db = DbSingleton::getTokoDb();
        $language_id = $this->getLanguage();

        if ($language_id !== 1) {
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

    public function showPopularBrands()
    {
        $formObj = new FormClass();
        $db = DbSingleton::getTokoDb();
        $list = "";

        $r = $db->query("SELECT `BRAND_ID` FROM `POPULAR_BRANDS` WHERE 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $brand_id   = $db->result($r, $i - 1, "BRAND_ID");
            $brand_name = $this->getBrandName($brand_id);
            $brand_link = $this->getBrandLink($brand_id);
            $link       = $this->getSiteLink() . "brands/" . $brand_link . "/";
            $image      = $formObj->showBrandPhoto($brand_id)["logo_name"];
            $list       .= $this->showPopularBrandsCard($image, $brand_name, $link);
        }

        $form = str_replace("{brands_range}", $list, $this->getHtmlForm("brands/form"));

        if ($n === 0) {
            $form = "";
        }

        return $form;
    }

    public function showPopularBrandsCard($image, $brand_name, $link)
    {
        return str_replace(
            array("{image}", "{brand_name}", "{page_link}"),
            array($image, $brand_name, $link),
        $this->getHtmlForm("brands/card"));
    }

    /*
     * show contacts bottom form
     * */
    public function showContactsBottom()
    {
        $db = DbSingleton::getTokoDb();
        $list_phone = $list_email = $form_address = "";

        $r = $db->query("SELECT `text`, `icon`, `link` FROM `contacts_bottom_new` WHERE `status` = 1 AND `type_contact` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $text = $db->result($r, $i - 1, "text");
            $icon = $db->result($r, $i - 1, "icon");
            $link = $db->result($r, $i - 1, "link");

            $list_phone .= str_replace(
                array("{link}", "{icon}", "{text}"),
                array($link, $icon, $text),
            $this->getHtmlForm("contacts/phone"));
        }

        $r = $db->query("SELECT `text`, `icon`, `link` FROM `contacts_bottom_new` WHERE `status` = 1 AND `type_contact` = 2;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $text = $db->result($r, $i - 1, "text");
            $icon = $db->result($r, $i - 1, "icon");
            $link = $db->result($r, $i - 1, "link");

            $list_email .= str_replace(
                array("{link}", "{icon}", "{text}"),
                array($link, $icon, $text),
            $this->getHtmlForm("contacts/mail"));
        }

        $r = $db->query("SELECT `text`, `text_short`, `icon`, `link` FROM `contacts_bottom_new` WHERE `status` = 1 AND `type_contact` = 3;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $list_address = "";
            for ($i = 1; $i <= $n; $i++) {
                $text       = $db->result($r, $i - 1, "text");
                $text_short = $db->result($r, $i - 1, "text_short");
                $icon       = $db->result($r, $i - 1, "icon");
                $link       = $db->result($r, $i - 1, "link");

                $list_address .= str_replace(
                    array("{link}", "{icon}", "{text}", "{text_short}"),
                    array($link, $icon, $text, $text_short),
                $this->getHtmlForm("contacts/address_list"));
            }

            $form_address = str_replace("{list}", $list_address, $this->getHtmlForm("contacts/address_form"));
        }

        $form = str_replace(
            array("{list_phone}", "{list_email}", "{list_address}"),
            array($list_phone, $list_email, $form_address),
        $this->getHtmlForm("menu/contacts_bottom"));

        return $this->replaceLang($form);
    }

    /*
     * show seller form
     * */
    public function showSellBlock()
    {
        return str_replace(
            array("{terms_cap}", "{deal_cap}"), 
            array($this->getHtmlForm("sell/sell_cooperation"), $this->getHtmlForm("sell/sell_deal")),
        $this->getHtmlForm("sell/sell_form"));
    }

    /*
     * send seller form (with file)
     * */
    public function saveSellerForm($company, $name, $phone, $email, $city_id, $comment)
    {
        $db = DbSingleton::getDbm();
        $client = new ClientClass();

        $company    = $this->getNameString($company);
        $name       = $this->getNameString($name);
        $email      = $this->getNameString($email);
        $comment    = $this->getNameString($comment);
        $phone      = $client->formatValidPhone($phone);
        $city_id    = $this->getUrlNumber($city_id);

        $cookie_id  = $this->getSessionID();
        $max_bytes  = 10485760;
        $format_arr = ["txt", "csv", "xls", "xlsx", "dbf"];

        $r = $db->query("SELECT `file_name`, `type`, `size` FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id` = '$cookie_id' ORDER BY `data` DESC LIMIT 1;");
        $n = $db->num_rows($r);
        $file_name  = $db->result($r, 0, "file_name");
        $type       = $db->result($r, 0, "type");
        $size       = $db->result($r, 0, "size");

        if ($n > 0) {
            $db->query("DELETE FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id` = '$cookie_id';");
        }

        if ($size <= $max_bytes && in_array($type, $format_arr, true)) {
            $db->query("INSERT INTO `J_SUPPLIERS_COOPERATION` (`company`, `name`, `phone`, `email`, `city_id`, `commentary`, `file_id`, `status`) VALUES ('$company', '$name', '$phone', '$email', '$city_id', '$comment', '$file_name', 166);");
            return true;
        }

        return array($type, $size);
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
    public function getGarageLink(): string
    {
        $autoObj = new AutoClass();
        $garage_count = $autoObj->getGarageAutoCount();

        return ($garage_count === "")
            ? "href=\"" . $this->getSiteLink() . "$this->catalog_link/auto/\""
            : "onclick=\"showGarageForm();\"";
    }

    /*
     * show reviews form
     * */
    public function showReviews()
    {
        $db = DbSingleton::getTokoDb();
        $list = "";

        $r = $db->query("SELECT * FROM `T2_REVIEWS` WHERE `STATUS` = 1 ORDER BY `DATA_CREATE` DESC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $postfix = $this->getLangPostfix($this->getLanguage());
            for ($i = 1; $i <= $n; $i++) {
                $title      = $db->result($r, $i - 1, "TITLE_$postfix");
                $title_ru   = $db->result($r, $i - 1, "TITLE_RU");
                $state_id   = $db->result($r, $i - 1, "ID");
                $transcript = $this->formatUrlText($title_ru);

                $transcript = str_replace(str_split('.,+\/:*?"<>|_'), "", $transcript);
                $transcript = str_replace(array("–", "---"), "-", $transcript);

                $form_range = str_replace(
                    array("{review_title}", "{review_date}", "{review_img}", "{page_review_link}"),
                    array($title, $db->result($r, $i - 1, "DATA_CREATE"), $db->result($r, $i - 1, "IMG"), $this->getSiteLink() . "$this->reviews_link/state/$state_id/$transcript/"),
                $this->getHtmlForm("reviews/form_range"));

                $list .= $form_range;
            }
        }

        return str_replace("{form_range}", $list, $this->getHtmlForm("reviews/form"));
    }

    public function getReviewsMetaTags($state_id = 0)
    {
        if ($state_id === 0) {
            $text       = $this->replaceLang("{site_reviews}");
            $text       = str_replace("{h1_text}", "{review_state_cap}", $text);
            $url_text   = $this->getSiteLink() . $this->reviews_link . "/";
            $img_text   = "";
        } else {
            $dataReview = $this->getReviewsData($state_id);
            $text       = $dataReview["title"];
            $url_text   = $dataReview["url"];
            $img_text   = $dataReview["img"];
        }

        return str_replace(
            array("{h1_meta_tag}", "{url_meta_tag}", "{main_image_cap}"), 
            array($text, $url_text, $img_text),
        $this->getHtmlForm("article/social"));
    }

    public function getReviewsData($state_id): array
    {
        $state_id = $this->getUrlNumber($state_id);
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT * FROM `T2_REVIEWS` WHERE `ID` = $state_id ORDER BY `DATA_CREATE` DESC;");
        $postfix    = $this->getLangPostfix($this->getLanguage());
        $date       = $db->result($r, 0, "DATA");
        $date_create = $db->result($r, 0, "DATA_CREATE");
        $site_title = $db->result($r, 0, "T_$postfix");
        $site_description = $db->result($r, 0, "D_$postfix");
        $title      = $db->result($r, 0, "TITLE_$postfix");
        $title_ru   = $db->result($r, 0, "TITLE_RU");
        $text       = $db->result($r, 0, "TEXT_$postfix");
        $img_file   = $db->result($r, 0, "IMG");
        $transcript = $this->formatUrlText($title_ru);
        $transcript = str_replace(str_split('.,+\/:*?"<>|_'), "", $transcript);
        $transcript = str_replace(array("–", "---"), "-", $transcript);
        $url        = $this->getSiteLink() . "$this->reviews_link/state/$state_id/$transcript/";
        $img        = "https://portal.myparts.pro/uploads/images/saved/$img_file";

        return compact("title", "text", "date", "date_create", "url", "img", "site_title", "site_description", "transcript");
    }

    /*
     * show reviews state form
     * */
    public function getReviewsState($state_id)
    {
        $reviewsData = $this->getReviewsData($state_id);

        $list = str_replace(
            array("{review_date}", "{review_title}", "{review_text}"),
            array($reviewsData["date_create"], $this->replaceTextTags($reviewsData["title"]), $this->replaceTextTags($reviewsData["text"], $reviewsData["title"])),
        $this->getHtmlForm("reviews/card_range"));

        return str_replace(
            array("{state_id}", "{state_info}", "{state_catalog}"),
            array($state_id, ($state_id > 0) ? $list : $this->getHtmlTag("h1", $this->err1), $this->getReviewsStateCatalog($state_id)),
        $this->getHtmlForm("reviews/card"));
    }

    /*
     * catalog in reviews
     * */
    public function getReviewsStateCatalog($state_id): string
    {
        $catalog_exist = new CatalogExistClass();
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $form = "";

        $r = $db->query("SELECT `GROUP_ID` FROM `T2_GROUP_REVIEW` WHERE `REVIEW_ID` = $state_id LIMIT 1;");
        $group_id = $db->result($r, 0, "GROUP_ID");

        if ($group_id > 0) {
            $limit          = $catalog_exist->getSearchLimit(1);
            $table          = "EX_TABLE_TREE_$group_id";
            $table_mfa      = "EX_TABLE_TREE_MFA_$group_id";
            $table_params   = "EX_TABLE_TREE_PARAMS_$group_id";
            $where_sort     = "ORDER BY t.price = 0, t.id";

            $query = "SELECT DISTINCT t.art_id FROM `$table` t
                LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
                LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
            $where_sort";

            $query_limit = "$query $limit";
            $arts = [];

            $r = $dbc->query($query_limit);
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $arts[] = $dbc->result($r, $i - 1, "art_id");
            }

            $art_id_str = implode(",", array_unique($arts));

            $list = $catalog_exist->searchListCatalog($art_id_str);

            if (!empty($list)) {
                $form = $this->getHtmlTag("div", $list, ['class' => 'content']);
            }
        }

        return $form;
    }
    
    public function replaceTextTags($text, $h1_text = "")
    {
        $text = str_replace(array("<h1>", "</h1>"), "", $text);

        if ($h1_text !== "") {
            $imageList = [];

            $text_end = $text;
            for ($i = 0, $iMax = strlen($text); $i <= $iMax; $i++) {
                $pos_start = strpos($text_end, "<img", $i);

                if ($text_end === substr($text_end, $pos_start)) {
                    break;
                }

                $text_end   = substr($text_end, $pos_start);
                $pos_end    = (strpos($text_end, ">")) + 1;
                $text_img   = substr($text_end, 0, $pos_end);
                $imageList[]= $text_img;
            }

            $count = 0;
            foreach ($imageList as $img) {

                if (strpos($img, "alt") === false) {
                    $count++;
                    $imageFormatted = str_replace("<img ", "<img alt=\"$h1_text - {photo_cap} $count\" title=\"$h1_text - {photo_cap} $count\"", $img);
                    $imageFormatted = $this->replaceLang($imageFormatted);
                } else {
                    $imageFormatted = $img;
                }
                $text = str_replace($img, $imageFormatted, $text);
            }
        }

        return $text;
    }

    /*
     * show scan form (Bonus) Validate
     * */
    public function showScanPhoneForm($phone)
    {
        return str_replace("{text_phone}", $phone, $this->getHtmlForm("bonus/phone_valid"));
    }

    /*
     * show catalog FAQ form
     * */
    public function getCatalogFaqForm($h1 = "", $min = 0, $max = 0)
    {
        $form = str_replace("{form_request}", $this->getHtmlForm("faq/request"), $this->getHtmlForm("faq/form"));
        $form = $this->replaceLang($form);

        $faq_answer_4 = $faq_answer_5 = "";
        $faq_answer_6 = "{from_cap} $min UAH {to_cap} $max UAH";
        return str_replace(
            array("{faq_h1}", "{help_form}", "{faq_answer_4}", "{faq_answer_5}", "{faq_answer_6}"),
            array($h1, $this->getHtmlForm("faq/help"), $faq_answer_4, $faq_answer_5, $faq_answer_6),
        $form);
    }

    /*
     * site warning message
     * */
    public function getSiteWarningMessage()
    {
        $db = DbSingleton::getTokoDb();
        $form = "";

        $r = $db->query("SELECT `TEXT`, `STYLES`, `STATUS` FROM `T2_SITE_CONFIGS` WHERE `BLOCK` = 'site_warning_message' AND `STATUS` = 1 LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $text   = $db->result($r, 0, "TEXT");
            $styles = $db->result($r, 0, "STYLES");
            $status = $db->result($r, 0, "STATUS");
            $text   = $this->replaceLang($text);

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
            $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_CONSTRUCTOR` WHERE `STATUS` = 1 ORDER BY `POSITION`;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $head_list = "";
                for ($i = 1; $i <= $n; $i++) {
                    $head_id    = $db->result($r, $i - 1, "HEAD_ID");
                    $head_name  = $this->getHeadRowName($head_id);

                    $head_list .= "
                    <div class=\"menu-bar-head__item\" onclick=\"getMenuBar('$head_id')\">
                        $head_name
                    </div>";
                }

                $list = str_replace(
                    array("{head_list}", "{media_list}", "{contacts_list}"),
                    array($head_list, $this->getPhoneNav(), $this->getPhoneContacts()),
                $this->getHtmlForm("bar/main"));
            }
        } else {
            $arr = [];
            $head_name = $this->getHeadRowName($head_id_sel);
            $r = $db->query("SELECT he.`CAT_ID`, he.`GROUP_ID`, he.`POPULAR`
            FROM `T2_TREE_CONSTRUCTOR_STR` cs
                LEFT JOIN `T2_TREE_HCG_EXIST` he ON (he.HEAD_ID = cs.HEAD_ID AND he.CAT_ID = cs.CAT_ID)
                LEFT JOIN `T2_TREE_GROUP_EXIST` ge ON (ge.GROUP_ID = he.GROUP_ID)
            WHERE cs.`CAT_ID` > 0 AND cs.`STATUS` = 1 AND ge.`STATUS` = 1 AND cs.`HEAD_ID` = $head_id_sel
            ORDER BY he.`POPULAR` DESC, he.`CAT_ID`, he.`GROUP_ID`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $cat_id     = (int)$db->result($r, $i - 1, "CAT_ID");
                $group_id   = (int)$db->result($r, $i - 1, "GROUP_ID");
                $popular    = (int)$db->result($r, $i - 1, "POPULAR");

                if ($popular === 1) {
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
                    $cat_name = $this->getCatRowData($cat_id)["cat_name"];
                    $icon = "";

                    if ($cat_id === 0) {
                        $icon = "<span style=\"color: #f44438; margin-right: 5px;\">o</span>";
                    }

                    $list .= "
                    <div class=\"menu-bar-cat__title\">
                        $icon$cat_name
                    </div>";

                    $list .= "
                    <div class=\"menu-bar-group\">";
                    foreach ($groups as $group_id) {
                        $groupData = $this->getGroupRowData($group_id);
                        $group_name = $groupData["name"];
                        $group_link = $groupData["link"];

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

        $form = str_replace("{bar_list}", $list, $this->getHtmlForm("bar/form"));

        return $this->replaceLang($form);
    }

    public function getPhoneNav()
    {
        return str_replace(
            array("{site_main_link}", "{profile_mobile}", "{basket_sum}"),
            array($this->getSiteLink(), (new ProfileClass)->getProfileInfoMobile(), (new ShopClass)->countSumBasket()),
        $this->getHtmlForm("bar/nav"));
    }

    public function getPhoneContacts()
    {
        return str_replace(
            array("{region_select}", "{lang_select}"),
            array("", (new LangClass())->getLanguageMenuList($this->getLanguage())),
        $this->getHtmlForm("bar/contacts"));
    }

    public function getFooterForm($router_sel = "")
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());

        $list1 = $list2 = "";
        $where = "1";

        if ($router_sel === "catalog") {
            $group_link = findLinks()[1];
            $where = "`LINK` != '$group_link'";
        }

        $r = $db->query("SELECT `ROUTER`, `LINK`, `TEXT_" . $postfix . "` FROM `T2_SEO_FOOTER` WHERE $where ORDER BY `TEXT_RU` LIMIT 0,20;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $router = $db->result($r, $i - 1, "ROUTER");
            $link   = $db->result($r, $i - 1, "LINK");
            $text   = $db->result($r, $i - 1, "TEXT_$postfix");

            if ($i <= 10) {
                $list1 .= "
                <li><a href=\"" . $this->getSiteLink() . "$router/$link/\"> $text</a></li>";
            }

            if ($i > 10 && $i <= 20) {
                $list2 .= "
                <li><a href=\"" . $this->getSiteLink() . "$router/$link/\"> $text</a></li>";
            }
        }

        $footer_1 = str_replace("{list}", $list1, $this->getHtmlForm("helper/ul_inline"));
        $footer_2 = str_replace("{list}", $list2, $this->getHtmlForm("helper/ul_inline"));

        return str_replace(
            array("{popular_catalogs_list1}", "{popular_catalogs_list2}", "{footer_cities}"),
            array($footer_1, $footer_2, $this->getSeoCitiesList()),
        getHtmlForm("main/footer"));
    }

    public function getSeoCitiesList(): string
    {
        $list = "";
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());

        $r = $db->query("SELECT `CITY_NAME_" . $postfix . "`, `LINK_NAME` FROM `SEO_LISTING_CITY` WHERE 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_name = $db->result($r, $i - 1, "CITY_NAME_$postfix");
            $city_link = $db->result($r, $i - 1, "LINK_NAME");
            $list .= $this->getHtmlTag("a", $city_name, ['href' => "https://toko.ua/catalog/?city=$city_link"]);

            if ($i < $n) {
                $list .= ", ";
            }
        }

        return $list;
    }

    public function getGroupsList(): string
    {
        $db = DbSingleton::getTokoDb();
        $list = "";

        $r = $db->query("SELECT `GROUP_ID`, `TEX_RU` FROM `T2_TREE_GROUP_EXIST` WHERE `STATUS` = 1 ORDER BY `TEX_RU`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id   = $db->result($r, $i - 1, "GROUP_ID");
            $group_name = $db->result($r, $i - 1, "TEX_RU");

            $list .= str_replace(
                array("{value}", "{name}", "{checked}"),
                array($group_id, $group_name, ""),
            $this->getHtmlForm("helper/select_option"));
        }

        return $list;
    }

    public function getGroupsListValues($group_id = 0): string
    {
        $db = DbSingleton::getTokoDb();
        $list = $this->replaceLang($this->getHtmlForm("helper/select_not_option"));
        $lang_id = $this->getOldLanguage($this->getLanguage());

        $r = $db->query("SELECT `VALUE_ID`, `VALUE_NAME`, `PARAM_ID` FROM `T2_TREE_VALUE_EXIST` 
        WHERE `GROUP_ID` = $group_id AND `LANG_ID` = $lang_id 
        ORDER BY `VALUE_NAME`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $value_id   = $db->result($r, $i - 1, "VALUE_ID");
            $param_id   = $db->result($r, $i - 1, "PARAM_ID");
            $value_name = $db->result($r, $i - 1, "VALUE_NAME");

            $list .= str_replace(
                array("{value}", "{name}", "{checked}", "{data}"),
                array($value_id, $value_name, "", "data-value-param=\"$param_id\""),
            $this->getHtmlForm("helper/select_option_data"));
        }

        return $list;
    }

    public function getGroupsLinks($group_id = 0, $param_id = 0, $value_id = 0): string
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $group_link = $this->getGroupRowLink($group_id);
        $list = "";
        $count = 0;

        if ($group_id > 0) {
            $list = "
            <table class='table'>
            <thead><tr>
                <th>#</th>
                <th>link</th>
                <th>count</th>
                <th>seo</th>
            </tr></thead><tbody>";

            if ($value_id === 0) {
                $r = $dbc->query("SELECT DISTINCT `mfa_id`, `model`, COUNT(`art_id`) as count_arts  FROM `EX_TABLE_TREE_MFA_$group_id`;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $mfa_id     = $dbc->result($r, $i - 1, "mfa_id");
                    $model      = $dbc->result($r, $i - 1, "model");
                    $count_arts = $dbc->result($r, $i - 1, "count_arts");

                    if ($mfa_id > 0) {
                        $mfa_link   = $this->getManufactureLink($mfa_id);
                        $model_link = $this->getModelLink($model);
                        $link_cat   = "$group_link/auto/$mfa_link/$model_link";
                        $link       = "https://toko.ua/catalog/$link_cat/";
                        $seo_status = (int)$this->checkSeoText("catalog", $link_cat);
                        $count++;

                        $list .= str_replace(
                            array("{count}", "{link}", "{count_arts}", "{seo_status}"),
                            array($count, $link, $count_arts, $seo_status),
                        $this->getHtmlForm("groups/table"));
                    }
                }
            } else {
                $param_link = $this->getParamLink($param_id);
                $value_link = $this->getValueLink($value_id);

                $r = $dbc->query("SELECT COUNT(tm.`art_id`) as count_arts 
                FROM `EX_TABLE_TREE_PARAMS_$group_id` tp
                    LEFT JOIN `EX_TABLE_TREE_$group_id` tm ON (tm.art_id = tp.art_id)
                WHERE (tp.`param_$param_id` = '$value_id' OR tp.`param_$param_id` LIKE '%,$value_id%' OR tp.`param_$param_id` LIKE '%$value_id,%');");
                $count_arts = (int)$dbc->result($r, 0, "count_arts");
                $link = "https://toko.ua/catalog/$group_link/$param_link=$value_link/";
                $seo_status = (int)$this->checkSeoText("catalog", $link);
                $count++;

                $list .= str_replace(
                    array("{count}", "{link}", "{count_arts}", "{seo_status}"),
                    array($count, $link, $count_arts, $seo_status),
                $this->getHtmlForm("groups/table"));

                $r = $dbc->query("SELECT tm.`mfa_id`, COUNT(tm.`art_id`) as count_arts 
                FROM `EX_TABLE_TREE_PARAMS_$group_id` tp
                    LEFT JOIN `EX_TABLE_TREE_MFA_$group_id` tm ON (tm.art_id = tp.art_id)
                WHERE (tp.`param_$param_id` = '$value_id' OR tp.`param_$param_id` LIKE '%,$value_id%' OR tp.`param_$param_id` LIKE '%$value_id,%')
                GROUP BY tm.`mfa_id`;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $mfa_id     = $dbc->result($r, $i - 1, "mfa_id");
                    $count_arts = $dbc->result($r, $i - 1, "count_arts");

                    if ($mfa_id > 0) {
                        $mfa_link   = $this->getManufactureLink($mfa_id);
                        $link       = "https://toko.ua/catalog/$group_link/$param_link=$value_link/$mfa_link/";
                        $seo_status = (int)$this->checkSeoText("catalog", $link);
                        $count++;

                        $list .= str_replace(
                            array("{count}", "{link}", "{count_arts}", "{seo_status}"),
                            array($count, $link, $count_arts, $seo_status),
                        $this->getHtmlForm("groups/table"));
                    }
                }

                $r = $dbc->query("SELECT tm.`mfa_id`, tm.`model`, COUNT(tm.`art_id`) as count_arts 
                FROM `EX_TABLE_TREE_PARAMS_$group_id` tp
                    LEFT JOIN `EX_TABLE_TREE_MFA_$group_id` tm ON (tm.art_id = tp.art_id)
                WHERE (tp.`param_$param_id` = '$value_id' OR tp.`param_$param_id` LIKE '%,$value_id%' OR tp.`param_$param_id` LIKE '%$value_id,%')
                GROUP BY tm.`mfa_id`, tm.`model`;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $mfa_id     = $dbc->result($r, $i - 1, "mfa_id");
                    $model      = $dbc->result($r, $i - 1, "model");
                    $count_arts = $dbc->result($r, $i - 1, "count_arts");

                    if ($mfa_id > 0) {
                        $mfa_link = $this->getManufactureLink($mfa_id);
                        $model_link = $this->getModelLink($model);
                        $link = "https://toko.ua/catalog/$group_link/$param_link=$value_link/$mfa_link/$model_link/";
                        $seo_status = (int)$this->checkSeoText("catalog", $link);
                        $count++;

                        $list .= str_replace(
                            array("{count}", "{link}", "{count_arts}", "{seo_status}"),
                            array($count, $link, $count_arts, $seo_status),
                        $this->getHtmlForm("groups/table"));
                    }
                }
            }

            if ($count === 0) {
                $list .= "
                <tr><td colspan='4'>" . $this->replaceLang('{nothing_found}') . "</td></tr>";
            }

            $list .= "
            </tbody></table>";
        }

        return $list;
    }

    /*
     * Tree List Headers
     * */
    public function getSiteNavigation($head_id_sel = 0, $cat_id_sel = 0, $group_id_sel = 0)
    {
        $db = DbSingleton::getTokoDb();
        $list = "";

        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_CONSTRUCTOR` WHERE `STATUS` = 1 ORDER BY `POSITION`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $head_id    = (int)$db->result($r, $i - 1, "HEAD_ID");
            $head_name  = $this->getHeadRowName($head_id);
            $head_link  = $this->getHeadRowLink($head_id);

            if ($head_id_sel === $head_id) {
                $link = $this->getHtmlTag("a", $head_name, ['rel' => "noopener"]);
            } else {
                $link = $this->getHtmlTag("a", $head_name, ['rel' => "noopener", 'href' => $this->getSiteLink() . "$this->catalog_link/$head_link/\""]);
            }

            $list .= $this->getHtmlTag("li", $link, ['class' => 'header-nav__li', 'data-nav-id' => $head_id]);
        }

        return str_replace(
            array("{catalog_range}", "{cat_id}", "{group_id}"),
            array($list, $cat_id_sel, $group_id_sel),
        $this->getHtmlForm("main/navigation"));
    }

    public function checkSeoText($router, $link): bool
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `CONTENT_RU` FROM `T2_SEO_TEXT` WHERE `ROUTER` = '$router' AND `LINK` = '$link' LIMIT 1;");
        $content_ru = $db->result($r, 0, "CONTENT_RU");

        return ($content_ru !== "");
    }

    /*
    * format text for URL
    * */
    public function formatUrlText($str): string
    {
        $format_text = $this->getFormattedTranslatedText($str);
        $format_text = str_replace(array(str_split('.,+-\/:*?"<>|_'), " ", "'"), array("", "-", ""), $format_text);

        return mb_strtolower($format_text);
    }

    public function getTelegramBotForm()
    {
        $db = DbSingleton::getTokoDb();

        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT * FROM `TELEGRAM_BOT_INFO` WHERE `STATUS` = 1 ORDER BY `DATE_CREATE` DESC;");
        $n = $db->num_rows($r);

        $form = "";

        if ($n > 0) {
            $list = "";
            for ($i = 1; $i <= $n; $i++) {
                $text = $db->result($r, $i - 1, "TEXT_$postfix");
                $date = $db->result($r, $i - 1, "DATE_CREATE");
                $date = date("d-m-Y", strtotime($date));
                $list .= str_replace(array("{date}", "{text}"), array($date, $text), $this->getHtmlForm("bot/list"));
            }
            $form = str_replace("{list}", $list, $this->getHtmlForm("bot/form"));
        }

        return str_replace("{telegram_bot_updates}", $form, $this->getHtmlForm("menu/telegram_bot"));
    }

}
