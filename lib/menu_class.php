<?php

class MenuClass extends CatalogueClass
{

    use Helper;
    use Variables;

    function getImages($content) {
        $dist = "/images/";
        $content = str_replace("{image_logotype}", $dist."logo.png", $content);
        $content = str_replace("{image_logotype_phone}", $dist."logo-phone.png", $content);
        return $content;
    }

    function getNewsStateTitle($state_id) { $db = DbSingleton::getTokoDb();
        $state_id = $this->getUrlNumber($state_id);
        $r = $db->query("SELECT `caption` FROM `news` WHERE `id`='$state_id' LIMIT 1;");
        $title = $db->result($r, 0, "caption");
        $title = str_replace(str_split('.+\/:*?"<>|!?'), "", $title);
        if ($title=="") $title = $this->replaceLang("{news_one_cap}"."-$state_id");
        return $title;
    }

    function getReviewStateTitle($state_id) { $db = DbSingleton::getTokoDb();
        $state_id = $this->getUrlNumber($state_id);
        $r = $db->query("SELECT `TITLE` FROM `T2_REVIEWS` WHERE `ID`='$state_id' LIMIT 1;");
        $title = $db->result($r, 0, "TITLE");
        $title = str_replace(str_split('.+\/:*?"<>|!?'), "", $title);
        if ($title=="") $title = $this->replaceLang("{state_one_cap}"."-$state_id");
        return $title;
    }

    function showNews() { $db = DbSingleton::getTokoDb();
        $lang = $this->getLanguage(); if ($lang==2) $lang = 5;
        $prefix = $this->getLangPrefix();
        $list = ""; $err1 = $this->err1; $date_cur = date("Y-m-d");
        $r = $db->query("SELECT * FROM `news` WHERE `lang_id`='$lang' AND `data`<='$date_cur' AND `status`=1 ORDER BY `data` DESC;"); $n = $db->num_rows($r);
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $state_id = $db->result($r,$i-1,"id");
                $title = $db->result($r,$i-1,"caption"); if ($title=="") $title = $this->replaceLang("{news_one_cap}"."-$state_id"); $format_title = $this->formatUrlText($title);
                $short_desc = $db->result($r,$i-1,"short_desc");
                $date = $db->result($r,$i-1,"data");
                $img_file = $this->getNewsImage($state_id);
                $img_file!=""
                    ? $img = "<img itemprop=\"image\" src=\"/thumb.php?image=news/$lang/$state_id/$img_file&size=280\" alt=\"image\">"
                    : $img = "";
                $list.="<div itemprop=\"publisher\" itemtype=\"https://schema.org/Organization\" itemscope class=\"row news-block__item\">
                    <div class=\"col-8\">
                        <h4>$date</h4>
                        <h2 itemprop=\"name\">$title</h2>
                        <h3 itemprop=\"description\">$short_desc</h3><br>
                        <a itemprop=\"url\" href=\"$prefix/news/state/$state_id/$format_title/\">{details_cap} <span class=\"fas fa-angle-right\"></span></a>
                    </div>
                    <div class=\"col-4 pad10\">$img</div>
                </div>";
            }
        } else $list = "<div class=\"content\"><h2>$err1<h2></div>";
        $form = $this->getHtmlForm("news/news");
        $form = str_replace("{news_range}", $list, $form);
        return $form;
    }

    function getNewsState($state_id) { $db = DbSingleton::getTokoDb();
        $lang = $this->getLanguage(); if ($lang!=1) $lang = 5;
        $state_id = $this->getUrlNumber($state_id);
        $r = $db->query("SELECT * FROM `news` WHERE `id`='$state_id';");
        $title = $db->result($r, 0, "caption"); if ($title=="") $title = $this->replaceLang("{news_one_cap}"."-$state_id");
        $text = $db->result($r, 0, "desc");
        $date = $db->result($r, 0, "data");
        $img_file = $this->getNewsImage($state_id);
        $img_file!="" ? $img = "<p><img itemprop=\"image\" src=\"/uploads/images/news/$lang/$state_id/$img_file\" alt=\"state\"></p>" : $img = "";
        $list = "<div class=\"news-state\">
            <h1>$title</h1>
            <h2>$date</h2>
            $img
            <div itemprop=\"description\">$text</div>
        </div>";
        $form = $this->getHtmlForm("news/news_state");
        $form = str_replace("{state_id}", $state_id, $form);
        $form = str_replace("{state_info}", $state_id>0 ? $list : "<h1>$this->err1</h1>", $form);
        return $form;
    }

    function showSpecialOffers($update_actions) {
        $form = $this->getHtmlForm("menu/special_offers");
        list($list, $arts) = $this->getSpecialOffersList("", $update_actions);
        $form = str_replace("{special_offers_update}", $update_actions, $form);
        $form = str_replace("{special_offers_range}", $list, $form);
        $form = str_replace("{special_offers_filter}", $this->getSpecialOffersFilterList($arts), $form);
        return $form;
    }

    function getSpecialOffersList($template_id, $update_actions) { $db = DbSingleton::getDbm();
        $kours = new ExRateClass; $showform = new FormClass;
        $prefix = $this->getLangPrefix();
        $err1 = $this->err1; $client_id = $this->getClient(); $categories = []; $group_arts = [];
        $where_arts = ""; $status_new = 0; $cur_data = date("Y-m-d");

        if ($template_id!="" && $template_id!="0") {
            $arts = $this->getGoodsGroupArts($template_id);
            if ($arts!="") $where_arts = "AND ac.art_id IN ($arts)";
        }

        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id`='$client_id';"); $nom = $db->num_rows($r);
        for ($i=1; $i<=$nom; $i++) {
            $category_id = $db->result($r, $i-1, "client_category");
            array_push($categories, $category_id);
        }
        $categories = implode(",", $categories);

        $r = $db->query("SELECT ac.* FROM `ACTION_CLIENTS` ac
            LEFT JOIN `ACTION_CLIENTS_LIST` acl ON (acl.action_id=ac.id)
            LEFT JOIN `ACTION_CLIENTS_CATEGORY` acc ON (acc.action_id=ac.id)
        WHERE (acl.client_id='$client_id' OR acc.category_id IN ($categories)) $where_arts AND ac.data>='$cur_data';"); $n = $db->num_rows($r);
        if ($n>0) {
            $list = "<div class=\"row\">"; $arr = [];
            for ($i=1; $i<=$n; $i++){
                $art_id = $db->result($r,$i-1,"art_id");
                $article_nr_displ = $this->getArticleDispl($art_id);
                $amount = $db->result($r,$i-1,"amount");
                $max_amount = $db->result($r,$i-1,"max_amount");
                $timestamp = $db->result($r,$i-1,"timestamp");
                $data = $db->result($r,$i-1,"data");
                $status = $db->result($r,$i-1,"status");
                $price = $db->result($r,$i-1,"price");
                $real_price = $this->getArticlePrice($art_id);
                $real_price = $kours->getKoursFromUAH($real_price,2);
                $discount = round((($real_price-$price)*100)/$real_price);
                if ($update_actions!="") if ($status && $timestamp>"$update_actions 00:00:00") $status_new = 1;
                $arr[$i] = ["status_new"=>$status_new, "art_id"=>$art_id, "article_nr_displ"=>$article_nr_displ, "amount"=>$amount, "max_amount"=>$max_amount, "timestamp"=>$timestamp, "data"=>$data, "status"=>$status, "discount"=>$discount];
            }

            $far_status = $far_article = [];
            foreach ($arr as $key => $row) {
                $far_status[$key] = $row["status_new"];
                $far_article[$key] = $row["article_nr_displ"];
            }
            array_multisort($far_status, SORT_DESC, $far_article, SORT_ASC, $arr);

            for ($i=0; $i<$n; $i++) {
                $art_id = $arr[$i]["art_id"];
                $article_nr_displ = $arr[$i]["article_nr_displ"];
                $amount = $arr[$i]["amount"];
                $max_amount = $arr[$i]["max_amount"];
                $timestamp = $arr[$i]["timestamp"];
                $data = $arr[$i]["data"];
                $status = $arr[$i]["status"];
                $status_new = $arr[$i]["status_new"];
                $discount = $arr[$i]["discount"];
                array_push($group_arts, $art_id);
                $name = $this->getArticleName($art_id);
                $article_nr_search = $this->getArticleSearch($art_id);

                $brand_id = $this->getArticleBrand($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $brand_link = $this->getBrandLink($brand_id);

                $data>0 ? $data = date("d.m.Y", strtotime($data)) : $data = "{indefinitely_cap}";
                $max_amount>0 ? $max_amount = "{yes_cap}" : $max_amount = "{no_cap}";
                $link = "https://toko.ua$prefix/search/$article_nr_search/$brand_link/";
                if ($status_new) $status_new = "<span class=\"special-offers-item__bell\" title=\"{new_cap} {offer_cap}\"><span class=\"fa fa-bell\"></span></span>"; else $status_new = "";

                $article_info = $showform->getArticleInfoForm($art_id);
                $info = "<span class=\"fas fa-info-circle tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" data-html=\"true\" title=\"$article_info\"></span>";

                if ($status) {
                    $list.="
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
                                   <a class=\"special-offers-item__info\" onclick=\"showInfoForm($art_id, '$article_nr_displ', '$brand_name');\">$info</a>
                                   $status_new
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            }
            $list.="</div>";
        } else $list = "<div class=\"content\"><h2>$err1<h2></div>";

        $list = $this->replaceLang($list);
        $group_arts = implode(",", $group_arts);
        return array($list, $group_arts);
    }

    function getSpecialOffersFilterList($arts="") { $db = DbSingleton::getTokoDb();
        $list = ""; $arts = trim($arts, ",");
        $arts!="" ? $where_arts = "WHERE t2gg.ART_ID IN ($arts)" : $where_arts = "";
        $r = $db->query("SELECT gg.* FROM `GOODS_GROUP` gg 
            LEFT OUTER JOIN `T2_GOODS_GROUP` t2gg ON (t2gg.GOODS_GROUP_ID=gg.ID)
        $where_arts GROUP BY t2gg.GOODS_GROUP_ID;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "ID");
            $name = $db->result($r, $i-1, "NAME");
            $list.="<option value=\"$id\">$name</option>s";
        }
        return $list;
    }

    function getGoodsGroupArts($template_id) { $db = DbSingleton::getTokoDb();
        $arts = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_GOODS_GROUP` WHERE `GOODS_GROUP_ID`='$template_id';"); $n = $db->num_rows($r);
        for($i=1; $i<=$n; $i++) {
            $art_id = $db->result($r, $i-1, "ART_ID");
            array_push($arts, $art_id);
        }
        $arts = implode(",", $arts);
        return $arts;
    }

    /*
     * GET Tpoint Modal Form
     * */
    function getRegionList() { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        $tpoint_id = $client->getTpoint();
        $lang = $this->getLanguage();
        $r = $db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id=t2.id)
        WHERE t2.status=1 AND t2a.lang_id='$lang' ORDER BY t2.position DESC, t2a.full_name ASC;"); $n = $db->num_rows($r);
        $list = "<form action=\"\" autocomplete=\"off\">"; $ch = "";
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r,$i-1,"id");
            $region = $db->result($r,$i-1,"full_name");
            $address = $db->result($r,$i-1,"address");
            $tpoint_id=="" ? : ($id==$tpoint_id ? $ch = "checked='checked'" : $ch = "");
            $list.="<label class=\"container_radio\"> $region ($address)<input type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onClick=\"selectRegion($id);\">
                <span class=\"radiomark\"></span>
            </label>";
        }
        $list.="</form>";
        return $list;
    }

    function getRegionListPhone() { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        $tpoint_id = $client->getTpoint();
        $lang = $this->getLanguage();
        $r = $db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id=t2.id)
        WHERE t2.status=1 AND t2a.lang_id='$lang' ORDER BY t2.position DESC, t2a.full_name ASC;"); $n = $db->num_rows($r);
        $list = "<form action=\"\" autocomplete=\"off\">"; $ch = "";
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "id");
            $region = $db->result($r, $i-1, "full_name");
            $tpoint_id=="" ? : ($id==$tpoint_id ? $ch = "checked='checked'" : $ch = "");
            $list.="<label class=\"container_radio-phone\">$region<input type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onClick=\"selectRegion($id);\">
                <span class=\"radiomark-phone\"></span>
            </label>";
        }
        $list.="</form>";
        return $list;
    }

    /*
     * GET Tpoint Form
     * (choose office)
     * */
    function getRegionSelect() { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        $lang = $this->getLanguage();
        $tpoint_id = $client->getTpoint();
        $list = "";
        $r = $db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id=t2.id)
        WHERE t2.id='$tpoint_id' AND t2a.lang_id='$lang' ORDER BY t2.position DESC, t2a.full_name ASC;"); $n = $db->num_rows($r);
        $region = $db->result($r, 0, "full_name");
        $address = $db->result($r, 0, "address");
        if ($n>0) {
            $list = "<span><span class=\"fas fa-map-marker-alt\"></span> {choose_office}:</span>
            <a class=\"pointer\" onClick='showRegionForm();'>
                <span id=\"region_select\">
                    <span>$region ($address)</span>
                </span>
            </a>";
            $list = $this->replaceLang($list);
        }
        return $list;
    }

    function showContacts() { $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $r = $db->query("SELECT * FROM `contacts_new` WHERE `lang_id`='$lang_id' AND `status`=1;"); $n = $db->num_rows($r);
        $list = "";
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $title = $db->result($r,$i-1,"title");
                $address = $db->result($r,$i-1,"address");
                $schedule = $db->result($r,$i-1,"schedule");
                $phone = $db->result($r,$i-1,"phone");
                $list.="<li>
                    <p itemprop=\"addressLocality\">$title</p>
                    <span class=\"fas fa-map-marker-alt\"></span> <span itemprop=\"streetAddress\">$address</span><br>
                    <span class=\"fas fa-clock\"></span> <span itemprop=\"hoursAvailable\">$schedule</span><br>
                    <span class=\"fas fa-phone-square\"></span> <span itemprop=\"telephone\">$phone</span>
                </li>";
            }
        } else $list = "<h2>$this->err1</h2>";
        $form = $this->getHtmlForm("menu/contacts");
        $form = str_replace("{contact_block}", $list, $form);
        return $form;
    }

    function getRegionForm($region = null) { $db = DbSingleton::getTokoDb();
        $form = "";
        $r = $db->query("SELECT `STATE_ID`, `STATE_NAME` FROM `T2_STATE` ORDER BY `STATE_NAME` ASC;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "STATE_ID");
            $caption = $db->result($r, $i-1, "STATE_NAME");
            $id==$region ? $checked = "selected=\"selected\"" : $checked = "";
            $form.="<option value=\"$id\" $checked>$caption</option>";
        }
        return $form;
    }

    function showTypeForm($org_type="") { $db = DbSingleton::getDbm();
        $form = ""; if ($org_type=="" || $org_type==0) $org_type = 1;
        $r = $db->query("SELECT * FROM `A_ORG_TYPE` ORDER BY `id` ASC;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "id");
            $caption = $db->result($r, $i-1, "full_name");
            $id==$org_type ? $checked = "selected=\"selected\"" : $checked = "";
            $form.="<option value=\"$id\" $checked>$caption</option>";
        }
        return $form;
    }

    function getLanguageList() { $db = DbSingleton::getTokoDb();
        $language = $this->getLanguage();
        $r = $db->query("SELECT * FROM `new_lang`;"); $n = $db->num_rows($r);
        $list = "<form action=\"\" autocomplete=\"off\">";
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r,$i-1,"id");
            $abr = $db->result($r,$i-1,"abr");
            $value = $db->result($r,$i-1,"value");
            $ch = ""; $style = "";
            if ($language!="") if ($id==$language) { $ch = "checked='checked'"; $style = "style=\"text-decoration: underline;\""; }
            $list.="<label class=\"pointer mar0 padr15\" $style itemprop=\"availableLanguage\" itemtype=\"http://schema.org/Language\" itemscope>
                <input style=\"display:none;\" type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onclick=\"setSiteLang($id)\"><span>$abr</span>
                <input itemprop=\"name\" type=\"hidden\" value=\"$value\">
            </label>";
        }
        $list.="</form>";
        return $list;
    }
	
    function getNewsImage($news_id) { $db = DbSingleton::getTokoDb();
        $lang = $this->getLanguage(); if ($lang!=1) $lang = 5;
        $file = "";
        $r = $db->query("SELECT `id` FROM `news_galery` WHERE `cat`='$news_id' ORDER BY `main` DESC;"); $n = $db->num_rows($r);
        if ($n>0) {
            $id = $db->result($r, 0, "id");
            if (file_exists("uploads/images/news/$lang/$news_id/$id.jpg")) { $file = "$id.jpg"; }
        }
        return $file;
    }

    function showContactsBottom() { $db = DbSingleton::getTokoDb();
        $list_phone = ""; $list_email = ""; $list_address = "";
        // PHONE
        $r = $db->query("SELECT * FROM `contacts_bottom_new` WHERE `status`=1 AND `type_contact`=1;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $text = $db->result($r, $i-1, "text");
            $icon = $db->result($r, $i-1, "icon");
            $link = $db->result($r, $i-1, "link");
            $list_phone.="<li>
                <a href=\"tel:$link\">
                    <span class=\"fas $icon\"></span>
                    <span>$text</span>
                </a>
            </li>";
        }
        // EMAIL
        $r = $db->query("SELECT * FROM `contacts_bottom_new` WHERE `status`=1 AND `type_contact`=2;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $text = $db->result($r, $i-1, "text");
            $icon = $db->result($r, $i-1, "icon");
            $link = $db->result($r, $i-1, "link");
            $list_email.="<li>
                <a href=\"$link\">
                    <span class=\"fas $icon\"></span>
                    <span itemprop=\"email\">$text</span>
                </a>
            </li>";
        }
        // ADDRESS
        $r = $db->query("SELECT * FROM `contacts_bottom_new` WHERE `status`=1 AND `type_contact`=3;"); $n = $db->num_rows($r);
        if ($n>0) $list_address.="<div itemprop=\"address\" itemscope itemtype=\"http://schema.org/PostalAddress\">";
        for ($i=1; $i<=$n; $i++) {
            $text = $db->result($r, $i-1, "text");
            $text_short = $db->result($r, $i-1, "text_short");
            $icon = $db->result($r, $i-1, "icon");
            $link = $db->result($r, $i-1, "link");
            $list_address.="<li>
                <a href=\"$link\">
                    <span class=\"fas $icon\"></span>
                    <span itemprop=\"addressLocality\">$text_short</span>
                    <span itemprop=\"streetAddress\">$text</span>
                </a>
            </li>";
        }
        if ($n>0) $list_address.="</div>";

        $form = $this->getHtmlForm("menu/contacts_bottom");
        $form = str_replace("{list_phone}", $list_phone, $form);
        $form = str_replace("{list_email}", $list_email, $form);
        $form = str_replace("{list_address}", $list_address, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function showSellBlock() {
        $form = $this->getHtmlForm("sell/sell_form");
        $form = str_replace("{terms_cap}", $this->getHtmlForm("sell/sell_cooperation"), $form);
        $form = str_replace("{deal_cap}", $this->getHtmlForm("sell/sell_deal"), $form);
        return $form;
    }

    function saveSellerForm($company, $name, $phone, $email, $city_id, $comment) { $db = DbSingleton::getDbm();
        $cookie_id = $_COOKIE["session_id"];
        $max_bytes = 10485760;
        $format_arr = ["txt", "csv", "xls", "xlsx", "dbf"];
        $r = $db->query("SELECT * FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id`='$cookie_id' ORDER BY `data` DESC LIMIT 1;"); $n = $db->num_rows($r);
        $file_name = $db->result($r, 0, "file_name");
        $type = $db->result($r, 0, "type");
        $size = $db->result($r, 0, "size");
        if ($n>0) {
            $db->query("DELETE FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE 'cookie_id'='$cookie_id';");
        }
        if (in_array($type, $format_arr) && $size<=$max_bytes) {
            $db->query("INSERT INTO `J_SUPPLIERS_COOPERATION` (`company`,`name`,`phone`,`email`,`city_id`,`commentary`,`file_id`,`status`) 
            VALUES ('$company','$name','$phone','$email','$city_id','$comment','$file_name',166);");
            return true;
        } else {
            return array($type, $size);
        }
    }

    function getSellerImage() { $db = DbSingleton::getDbm();
        $cookie_id = $_COOKIE["session_id"];
        $r = $db->query("SELECT `real_file_name` FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id`='$cookie_id' ORDER BY `data` DESC LIMIT 1;");
        $real_file_name = $db->result($r, 0, "real_file_name");
        return $real_file_name;
    }

    function showHeadTemplate($head_id) {
        $automan = new AutoClass;
        list($tex_text, $text_link) = $automan->getHeadNewDescr($head_id);
        $header = "<a href=\"https://toko.ua/catalog/$text_link/\">$tex_text</a>";
        $list = $this->getGroupTreeStr($head_id, "");
        $footer = "<a href=\"https://toko.ua/catalog/$text_link\">{show_all_cap} <i class=\"fa fa-chevron-right\"></i></a>";
        $footer = $this->replaceLang($footer);
        return array($list, $header, $footer);
    }

    function getGarageLink() {
        $automan = new AutoClass;
        $prefix = $this->getLangPrefix();
        $garage_count = $automan->getGarageAutoCount()[0];
        $garage_count=="" ? $garage_link = "href=\"https://toko.ua$prefix/catalogue/auto/\"" : $garage_link = "onclick=\"showGarageForm();\"";
        return $garage_link;
    }

    function getMediaNavPanel() {
        $profile = new ProfileClass;
        $form = $this->getHtmlForm("media/nav_panel");
        $form = str_replace("{site_lang_prefix}", $this->getLangPrefix(), $form);
        $form = str_replace("{lang_select}", $this->getLanguageList(), $form);
        if (!$profile->getClientInfo()) {
            $form = str_replace("{region_select}", $this->getRegionSelect(), $form);
            $form = str_replace("{region_select_phone}", "<li>".$this->getRegionSelect()."</li>", $form);
        } else {
            $form = str_replace("{region_select}", "", $form);
            $form = str_replace("{region_select_phone}", "", $form);
        }
        return $form;
    }

    /*
     * Reviews Form
     * */
    function showReviews() { $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $list = "";
        $r = $db->query("SELECT * FROM `T2_REVIEWS` WHERE `STATUS`=1 ORDER BY `data` DESC;"); $n = $db->num_rows($r);
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $state_id = $db->result($r, $i-1, "ID");
                $title = $db->result($r, $i-1, "TITLE");
                $date = $db->result($r, $i-1, "DATA");
                $img = $db->result($r, $i-1, "IMG");
                $transcript = $this->formatUrlText($title);
                $list.="<div itemprop=\"publisher\" itemtype=\"https://schema.org/Organization\" itemscope class=\"reviews-block-item\">
                    <div class=\"reviews-block-item__date\">$date</div>
                    <div class=\"reviews-block-item__title\" itemprop=\"name\">$title</div>
                    <div class=\"reviews-block-item__image\"><img src=\"https://portal.myparts.pro/uploads/images/saved/$img\" alt=\"$title\"></div>
                    <div class=\"reviews-block-item__link\"><a itemprop=\"url\" href=\"$prefix/reviews/state/$state_id/$transcript\">{details_cap} <span class=\"fas fa-angle-right\"></span></a></div>
                </div>";
            }
        }
        $form = $this->getHtmlForm("reviews/form");
        $form = str_replace("{form_range}", $list, $form);
        return $form;
    }

    /*
     * Reviews Item Form
     * */
    function getReviewsState($state_id) { $db = DbSingleton::getTokoDb();
        $state_id = $this->getUrlNumber($state_id);
        $r = $db->query("SELECT * FROM `T2_REVIEWS` WHERE `ID`='$state_id';");
        $title = $db->result($r, 0, "TITLE");
        $text = $db->result($r, 0, "TEXT");
        $date = $db->result($r, 0, "DATA");
        $list = "<div class=\"reviews\">
            <div class=\"reviews-block-item__date\">$date</div>
            <div class=\"reviews-block-item__title\" itemprop=\"name\"><h1>$title</h1></div>
            <div class=\"reviews-block-item__text\" itemprop=\"description\">$text</div>
        </div>";
        $form = $this->getHtmlForm("reviews/card");
        $form = str_replace("{state_id}", $state_id, $form);
        $form = str_replace("{state_info}", $state_id>0 ? $list : "<h1>$this->err1</h1>", $form);
        return $form;
    }

    /*
     * Scan Form (Bonus)
     * */
    function showScanForm() {
        $form = $this->getHtmlForm("bonus/scan");
        return $form;
    }
//
    function showScanPhoneForm($phone) {
        $form = $this->getHtmlForm("bonus/phone_valid");
        $form = str_replace("{text_phone}", $phone, $form);
        return $form;
    }

}
