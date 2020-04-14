<?php

class MenuClass {

    use Helper;
    use Variables;

    function getImages($content) {
        $dist="/images/";
        $content=str_replace("{image_logotype}", $dist."logo.png", $content);
        return $content;
    }

    function getNewsStateTitle($state_id) { $db = DbSingleton::getTokoDb();
        $state_id = $this->getUrlNumber($state_id);
        $r=$db->query("SELECT * FROM `news` WHERE `id`='$state_id' LIMIT 1;");
        $title=$db->result($r,0,"caption");
        $title=str_replace(str_split('.+\/:*?"<>|!?'),"", $title);
        if ($title=="") $title=$this->replaceLang("{news_one_cap}"."-$state_id");
        return $title;
    }

    function showNews() { $db = DbSingleton::getTokoDb();
        $cat=new CatalogueClass; $language=new LangClass; $prefix=$language->getLangPrefix();
        $lang=$language->getLanguage(); if ($lang==2) $lang=5;
        $err1=$this->err1; $date_cur=date("Y-m-d");
        $form=$this->getHtmlForm("news/news");
        $r=$db->query("SELECT * FROM `news` WHERE `lang_id`='$lang' AND `data`<='$date_cur' AND `status`=1 ORDER BY `data` DESC;"); $n=$db->num_rows($r); $list="";
        if ($n>0) {
            for ($i=1;$i<=$n;$i++){
                $state_id=$db->result($r,$i-1,"id");
                $title=$db->result($r,$i-1,"caption");
                if ($title=="") $title=$this->replaceLang("{news_one_cap}"."-$state_id");
                $format_title=$cat->formatUrlText($title);
                $short_desc=$db->result($r,$i-1,"short_desc");
                $date=$db->result($r,$i-1,"data");
                $img_file=$this->getNewsImage($state_id);
                $img_file!=""
                    ? $img="<img itemprop=\"image\" src=\"/thumb.php?image=news/$lang/$state_id/$img_file&size=280\">"
                    : $img="<img itemprop=\"image\" src=\"$this->noPhoto\">";
                $list.="
                <div itemprop=\"publisher\" itemtype=\"https://schema.org/Organization\" itemscope class=\"news-block__item row\">
                    <div class=\"col-8\">
                        <h4>$date</h4>
                        <h2 itemprop=\"name\">$title</h2>
                        <h3 itemprop=\"description\">$short_desc</h3><br>
                        <a itemprop=\"url\" href=\"$prefix/news/state/$state_id/$format_title/\">{details_cap} <span class=\"fas fa-angle-right\"></span></a>
                    </div>
                    <div class=\"col-4 pad15\">$img</div>
                </div>";
            }
        } else $list="<div class=\"content\"><h2>$err1<h2></div>";
        $form=str_replace("{news_range}",$list,$form);
        return $form;
    }

    function getNewsState($state_id) { $db = DbSingleton::getTokoDb();
        $language=new LangClass;
        $lang=$language->getLanguage(); if ($lang!=1) $lang=5;
        $form=$this->getHtmlForm("news/news_state");
        $state_id = $this->getUrlNumber($state_id);
        $r=$db->query("SELECT * FROM `news` WHERE `id`='$state_id';");
        $title=$db->result($r,0,"caption");
        if ($title=="") $title=$this->replaceLang("{news_one_cap}"."-$state_id");
        $text=$db->result($r,0,"desc");
        $date=$db->result($r,0,"data");
        $img_file=$this->getNewsImage($state_id);
        $img_file!="" ? $img="<p><img itemprop=\"image\" src=\"/uploads/images/news/$lang/$state_id/$img_file\"></p>" : $img="";
        $header = "<h1>$title</h1>";
        $footer = "<h1></h1>";
        $title=="" ? $header="" : $footer="";
        $list="
        <div class=\"news-state\">
            $header
            <h2>$date</h2>
            $img
            <div itemprop=\"description\">$text</div>
            $footer
        </div>";
        $form=str_replace("{state_id}",$state_id,$form);
        $form=str_replace("{state_info}",$state_id>0 ? $list : "<h1>$this->err1</h1>",$form);
        return $form;
    }

    function showSpecialOffers($update_actions) {
        $form=$this->getHtmlForm("menu/special_offers");
        list($list,$arts)=$this->getSpecialOffersList("",$update_actions);
        $form=str_replace("{special_offers_update}",$update_actions,$form);
        $form=str_replace("{special_offers_range}",$list,$form);
        $form=str_replace("{special_offers_filter}",$this->getSpecialOffersFilterList($arts),$form);
        return $form;
    }

    function getSpecialOffersList($template_id, $update_actions) { $db = DbSingleton::getDbm();
        $catalogue=new CatalogueClass; $language=new LangClass; $kours=new ExRateClass; $showform=new FormClass;
        $prefix=$language->getLangPrefix();
        $err1=$this->err1; $client_id=$this->getClient(); $categories=[]; $group_arts=[];
        $where_arts=""; $status_new=0; $cur_data=date("Y-m-d");

        if ($template_id!="" && $template_id!="0") {
            $arts=$this->getGoodsGroupArts($template_id);
            if ($arts!="") $where_arts="AND ac.art_id IN ($arts)";
        }

        $r=$db->query("SELECT * FROM `A_CLIENTS` WHERE `id`='$client_id';"); $nom=$db->num_rows($r);
        for ($i=1;$i<=$nom;$i++) {
            $category_id = $db->result($r, $i-1, "client_category");
            array_push($categories,$category_id);
        }
        $categories=implode(",",$categories);

        $r=$db->query("SELECT ac.* FROM `ACTION_CLIENTS` ac
            LEFT OUTER JOIN `ACTION_CLIENTS_LIST` acl ON (acl.action_id=ac.id)
            LEFT OUTER JOIN `ACTION_CLIENTS_CATEGORY` acc ON (acc.action_id=ac.id)
        WHERE (acl.client_id='$client_id' OR acc.category_id IN ($categories)) $where_arts AND ac.data>='$cur_data';"); $n=$db->num_rows($r);
        if ($n>0) {
            $list="<div class=\"news-block row\">"; $arr=[];
            for ($i=1;$i<=$n;$i++){
                $art_id=$db->result($r,$i-1,"art_id");
                $article_nr_displ=$this->getArticleDispl($art_id);
                $amount=$db->result($r,$i-1,"amount");
                $max_amount=$db->result($r,$i-1,"max_amount");
                $timestamp=$db->result($r,$i-1,"timestamp");
                $data=$db->result($r,$i-1,"data");
                $status=$db->result($r,$i-1,"status");
                $price=$db->result($r,$i-1,"price");
                $real_price=$catalogue->getArticlePrice($art_id);
                $real_price=$kours->getKoursFromUAH($real_price,2);
                $discount=(($real_price-$price)*100)/$real_price;
                $discount=round($discount);
                if ($update_actions!="") if ($status && $timestamp>"$update_actions 00:00:00") $status_new=1;
                $arr[$i]=["status_new"=>$status_new, "art_id"=>$art_id, "article_nr_displ"=>$article_nr_displ, "amount"=>$amount, "max_amount"=>$max_amount, "timestamp"=>$timestamp, "data"=>$data, "status"=>$status, "discount"=>$discount];
            }

            $far_status=$far_article=[];
            foreach ($arr as $key => $row) {
                $far_status[$key] = $row["status_new"];
                $far_article[$key] = $row["article_nr_displ"];
            }

            array_multisort($far_status, SORT_DESC, $far_article, SORT_ASC, $arr);

            for ($i=0;$i<$n;$i++) {
                $art_id=$arr[$i]["art_id"];
                $article_nr_displ=$arr[$i]["article_nr_displ"];
                $amount=$arr[$i]["amount"];
                $max_amount=$arr[$i]["max_amount"];
                $timestamp=$arr[$i]["timestamp"];
                $data=$arr[$i]["data"];
                $status=$arr[$i]["status"];
                $status_new=$arr[$i]["status_new"];
                $discount=$arr[$i]["discount"];
                array_push($group_arts,$art_id);
                $name=$catalogue->getArticleName($art_id);
                $article_nr_search=$this->getArticleSearch($art_id);
                list($brand_id,$brand_name)=$this->getBrandInfo($art_id);
                $data>0 ? $data=date("d.m.Y", strtotime($data)) : $data="{indefinitely_cap}";
                $max_amount>0 ? $max_amount="{yes_cap}" : $max_amount="{no_cap}";
                $link="https://toko.ua$prefix/search/$article_nr_search/$brand_id/$brand_name/";
                if ($status_new)
                    $status_new="
                    <span class=\"span-red float-right\" title=\"{new_cap} {offer_cap}\" style=\"margin-left: 10px\">
                        <span class=\"fa fa-bell\"></span>
                    </span>";
                else $status_new="";

                $article_info = $showform->getArticleInfoForm($art_id);
                $info="<span class=\"fas fa-info-circle tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" data-html=\"true\" title=\"$article_info\"></span>";

                if ($status) {
                    $list.="
                    <div class=\"col-lg-4 col-12\">
                        <div class=\"show-offers__item offer-discount\">
                            <div class=\"row\">
                                <div class=\"col-7\">
                                    <h4 itemprop=\"datePublished\">$timestamp</h4><br>
                                    <h2 itemprop=\"name\">
                                        <a class=\"a-blue\" href=\"$link\" target=\"_blank\">$article_nr_displ {from_cap} $amount {amount_abbr}.
                                        <br><span>$name</span></a>
                                    </h2>
                                    <h3 itemprop=\"description\">
                                        {offer_valid_until}: $data <br>
                                        {quantity_limited}: $max_amount 
                                    </h3>
                                </div>
                                <div class=\"col-5 text-right\">
                                    <span style='font-size:.9em;color: #606975;font-weight:bold'>{economy_cap}</span><br>
                                    <span style='font-size:1.2em;color: #606975;font-weight:bold'>$discount%</span>
                                </div>
                            </div>
                            <div class=\"row\">
                                <div class=\"col-8\">
                                    <a class=\"a-blue\" href=\"$link\" target=\"_blank\"><span class=\"fa fa-link\"></span> {go_to_offer}</a>
                                </div>
                                <div class=\"col-4 text-right\">
                                   <a class=\"pointer color000 a-blue\" onclick=\"showInfoForm($art_id, '$article_nr_displ', '$brand_name');\">$info</a>
                                   $status_new
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            }
            $list.="</div>";
        } else $list="<div class=\"content\"><h2>$err1<h2></div>";
        $list=$this->replaceLang($list);
        $group_arts=implode(",",$group_arts);
        return array($list,$group_arts);
    }

    function getSpecialOffersFilterList($arts="") { $db = DbSingleton::getTokoDb();
        $list=""; $arts=trim($arts,",");
        $arts!="" ? $where_arts="WHERE t2gg.ART_ID IN ($arts)" : $where_arts="";
        $r=$db->query("SELECT gg.* FROM `GOODS_GROUP` gg 
            LEFT OUTER JOIN `T2_GOODS_GROUP` t2gg ON (t2gg.GOODS_GROUP_ID=gg.ID)
        $where_arts GROUP BY t2gg.GOODS_GROUP_ID;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $id = $db->result($r, $i-1, "ID");
            $name = $db->result($r, $i-1, "NAME");
            $list.="<option value=\"$id\">$name</option>s";
        }
        return $list;
    }

    function getGoodsGroupArts($template_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_GOODS_GROUP` WHERE `GOODS_GROUP_ID`='$template_id';");$n=$db->num_rows($r);$arts=[];
        for($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            array_push($arts,$art_id);
        }
        $arts=implode(",",$arts);
        return $arts;
    }

    function getRegionList() { $db = DbSingleton::getDbm();
        $language=new LangClass; $client=new ClientClass;
        $tpoint_id=$client->getTpoint(); $lang=$language->getLanguage();
        $r=$db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT OUTER JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id=t2.id)
        WHERE t2.status=1 AND t2a.lang_id='$lang' ORDER BY t2.position DESC, t2a.full_name ASC;"); $n=$db->num_rows($r);
        $list="<form action=\"\" autocomplete=\"off\">"; $ch="";
        for ($i=1;$i<=$n;$i++) {
            $id=$db->result($r,$i-1,"id");
            $region=$db->result($r,$i-1,"full_name");
            $address=$db->result($r,$i-1,"address");
            $tpoint_id=="" ? : ($id==$tpoint_id ? $ch="checked='checked'" : $ch="");
            $list.="<label class=\"container_radio\"> $region ($address)<input type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onClick=\"selectRegion($id);\">
                <span class=\"radiomark\"></span>
            </label>";
        }
        $list.="</form>";
        return $list;
    }

    function getRegionListPhone() { $db = DbSingleton::getDbm();
        $language=new LangClass; $client=new ClientClass;
        $tpoint_id=$client->getTpoint(); $lang=$language->getLanguage();
        $r=$db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT OUTER JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id=t2.id)
        WHERE t2.status=1 AND t2a.lang_id='$lang' ORDER BY t2.position DESC, t2a.full_name ASC;"); $n=$db->num_rows($r);
        $list="<form action=\"\" autocomplete=\"off\">"; $ch="";
        for ($i=1;$i<=$n;$i++) {
            $id=$db->result($r,$i-1,"id");
            $region=$db->result($r,$i-1,"full_name");
            $tpoint_id=="" ? : ($id==$tpoint_id ? $ch="checked='checked'" : $ch="");
            $list.="<label class=\"container_radio-phone\">$region<input type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onClick=\"selectRegion($id)\">
                <span class=\"radiomark-phone\"></span>
            </label>";
        }
        $list.="</form>";
        return $list;
    }

    function getRegionSelect() { $db = DbSingleton::getDbm();
        $client=new ClientClass; $language=new LangClass;
        $lang=$language->getLanguage(); $tpoint_id=$client->getTpoint();
        $r=$db->query("SELECT t2.id, t2a.full_name, t2a.address 
        FROM `T_POINT` t2
            LEFT OUTER JOIN `T_POINT_ADDRESS` t2a ON (t2a.tpoint_id=t2.id)
        WHERE t2.id='$tpoint_id' AND t2a.lang_id='$lang' ORDER BY t2.position DESC, t2a.full_name ASC;"); $n=$db->num_rows($r); $list="";
        $region=$db->result($r,0,"full_name");
        $address=$db->result($r,0,"address");
        if ($n>0) {
            $list="<span><span class=\"fas fa-map-marker-alt\"></span> {choose_office}:</span>
            <a class=\"pointer\" onClick=\"showRegionForm();\">
                <span id=\"region_select\">
                    <span>$region ($address)</span>
                </span>
            </a>";
            $list=$this->replaceLang($list);
        }
        return $list;
    }

    function getLanguageSelect($id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `new_lang` WHERE `id`='$id' LIMIT 1;"); $n=$db->num_rows($r); $list="";
        if ($n>0) {
            $abr=$db->result($r,0,"abr");
            $list="<a class=\"lang_select pointer\" onClick=\"showLangForm();\">
                <span id=\"lang_select\">
                    {laguage_cap}: <span> $abr</span>
                </span>
            </a>";
        }
        return $list;
    }

    function showContacts() { $db = DbSingleton::getTokoDb();
        $language=new LangClass;
        $lang_id=$language->getLanguage();
        $r=$db->query("SELECT * FROM `contacts_new` WHERE `lang_id`='$lang_id' AND `status`=1;"); $n=$db->num_rows($r);
        if ($n>0) { $list="";
            for ($i=1;$i<=$n;$i++) {
                $title=$db->result($r,$i-1,"title");
                $address=$db->result($r,$i-1,"address");
                $schedule=$db->result($r,$i-1,"schedule");
                $phone=$db->result($r,$i-1,"phone");
                $list.="<li>
                    <p itemprop=\"addressLocality\">$title</p>
                    <span class=\"fas fa-map-marker-alt\"></span> <span itemprop=\"streetAddress\">$address</span><br>
                    <span class=\"fas fa-clock\"></span> <span itemprop=\"hoursAvailable\">$schedule</span><br>
                    <span class=\"fas fa-phone-square\"></span> <span itemprop=\"telephone\">$phone</span>
                </li>";
            }
        } else $list="<h2>$this->err1</h2>";
        $form=$this->getHtmlForm("menu/contacts");
        $form=str_replace("{contact_block}", $list, $form);
        return $form;
    }

    function getRegionForm($region=null) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT `STATE_ID`, `STATE_NAME` FROM `T2_STATE` ORDER BY `STATE_NAME` ASC;"); $n=$db->num_rows($r); $form="";
        for ($i=1;$i<=$n;$i++){
            $id=$db->result($r,$i-1,"STATE_ID");
            $caption=$db->result($r,$i-1,"STATE_NAME");
            $id==$region ? $checked="selected=\"selected\"" : $checked="";
            $form.="<option value=\"$id\" $checked>$caption</option>";
        }
        return $form;
    }

    function showTypeForm($org_type="") { $db = DbSingleton::getDbm();
        $form=""; if ($org_type=="" || $org_type==0) $org_type=1;
        $r=$db->query("SELECT * FROM `A_ORG_TYPE` ORDER BY `id` ASC;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++){
            $id=$db->result($r,$i-1,"id");
            $caption=$db->result($r,$i-1,"full_name");
            $id==$org_type ? $checked="selected=\"selected\"" : $checked="";
            $form.="<option value=\"$id\" $checked>$caption</option>";
        }
        return $form;
    }

    function getLanguageList() { $db = DbSingleton::getTokoDb();
        $lang=new LangClass;
        $language=$lang->getLanguage(); $ch=$style="";
        $r=$db->query("SELECT * FROM `new_lang`;"); $n=$db->num_rows($r);
        $list="<form action=\"\" autocomplete=\"off\"><span class=\"padr15\"></span>";
        for ($i=1;$i<=$n;$i++){
            $id=$db->result($r,$i-1,"id");
            $abr=$db->result($r,$i-1,"abr");
            $value=$db->result($r,$i-1,"value");
            if ($language!="") if ($id==$language) {$ch="checked='checked'"; $style="style=\"text-decoration: underline;\"";} else {$ch=""; $style="";}
            $list.="<label class=\"pointer mar0 padr15\" $style itemprop=\"availableLanguage\" itemtype=\"http://schema.org/Language\" itemscope>
                <input style=\"display:none;\" type=\"radio\" name=\"tpoint\" value=\"$id\" $ch onclick=\"setSiteLang($id)\"><span>$abr</span>
                <input itemprop=\"name\" type=\"hidden\" value=\"$value\">
            </label>";
        }
        $list.="</form>";
        return $list;
    }
	
    function getNewsImage($news_id) { $db=DbSingleton::getTokoDb();
        $language=new LangClass;
        $lang=$language->getLanguage(); if ($lang!=1) $lang=5;
        $r=$db->query("SELECT * FROM `news_galery` WHERE `cat`='$news_id' ORDER BY `main` DESC;"); $n=$db->num_rows($r); $file="";
        if ($n>0) {
            $id=$db->result($r,0,"id");
            if (file_exists("uploads/images/news/$lang/$news_id/$id.jpg")) { $file="$id.jpg"; }
            if (!file_exists("uploads/images/news/$lang/$news_id/$id.jpg")) { $file=""; }
        }
        return $file;
    }

    function showContactsBottom() { $db=DbSingleton::getTokoDb();
        $list="<div itemtype=\"http://schema.org/Organization\" itemscope>
        <span itemprop=\"name\" class=\"dnone\">{seo_shop_toko}</span>
        <ul>";

        // PHONE
        $r=$db->query("SELECT * FROM `contacts_bottom_new` WHERE `status`=1 AND `type_contact`=1;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $text=$db->result($r, $i-1, "text");
            $icon=$db->result($r, $i-1, "icon");
            $link=$db->result($r, $i-1, "link");
            $itemprop="";
            $list.="<li>
                <a href=\"$link\">
                    <span class=\"fas $icon\"></span>
                    <span $itemprop>$text</span>
                </a>
            </li>";
        }
        // EMAIL
        $r=$db->query("SELECT * FROM `contacts_bottom_new` WHERE `status`=1 AND `type_contact`=2;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $text=$db->result($r, $i-1, "text");
            $icon=$db->result($r, $i-1, "icon");
            $link=$db->result($r, $i-1, "link");
            $itemprop="itemprop=\"email\"";
            $list.="<li>
                <a href=\"$link\">
                    <span class=\"fas $icon\"></span>
                    <span $itemprop>$text</span>
                </a>
            </li>";
        }
        // ADDRESS
        $r=$db->query("SELECT * FROM `contacts_bottom_new` WHERE `status`=1 AND `type_contact`=3;"); $n=$db->num_rows($r);
        if ($n>0) $list.="<div itemprop=\"address\" itemscope itemtype=\"http://schema.org/PostalAddress\">";
        for ($i=1;$i<=$n;$i++) {
            $text=$db->result($r, $i-1, "text");
            $text_short=$db->result($r, $i-1, "text_short");
            $icon=$db->result($r, $i-1, "icon");
            $link=$db->result($r, $i-1, "link");
            $list.="<li>
                <a href=\"$link\">
                    <span class=\"fas $icon\"></span>
                    <span itemprop=\"addressLocality\">$text_short</span>
                    <span itemprop=\"streetAddress\">$text</span>
                </a>
            </li>";
        }
        if ($n>0) $list.="</div>";

        $list.="</ul></div>";
        $list=$this->replaceLang($list);
        return $list;
    }

    function showBannerBottom() { $db = DbSingleton::getTokoDb();
        $form=$this->getHtmlForm("menu/banner");
        $where=$list=""; $max_symbols=50;
        $r=$db->query("SELECT * FROM `T_ref_action` GROUP BY `REF` ORDER BY rand() LIMIT 0,18;");$n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++){
            $ref=$db->result($r,$i-1,"REF");
            $where.="t2a.ARTICLE_NR_DISPL='$ref'"; if ($i<$n) $where.=" OR ";
        }
        $where="AND ($where)";
        $query="SELECT t2a.*, t2b.BRAND_ID, t2b.BRAND_NAME, t2n.NAME, t2n.INFO 
        FROM `T2_ARTICLES` t2a 
            LEFT OUTER JOIN `T2_NAMES` t2n on t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_BRANDS` t2b on t2b.BRAND_ID=t2a.BRAND_ID
        WHERE t2n.LANG_ID=16 $where LIMIT 0,18;";
        $r=$db->query($query);$n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $article_nr_displ=$db->result($r,$i-1,"ARTICLE_NR_DISPL");
            $article_nr_search=$db->result($r,$i-1,"ARTICLE_NR_SEARCH");
            $name=$db->result($r,$i-1,"NAME");
            $brand=$db->result($r,$i-1,"BRAND_NAME");
            $info=$db->result($r,$i-1,"INFO");
            $image=$this->getCatPhoto($art_id);

            strlen($info)>$max_symbols ? $dots="..." : $dots="";
            $info=substr($info,0,$max_symbols).$dots;
            $list.="
            <div class=\"container\">
                <a href='/search/$article_nr_search/'>
                <div class=\"row owl-row\">
                    <div class=\"col-5 banner_img\">$image</div>
                    <div class=\"col-7 banner_text\">
                         <p>$name</p>
                         <p>$info</p>
                         <span>$article_nr_displ ($brand)</span>
                    </div> 
                </div>
                </a>
            </div>";
        }
        $form=str_replace("{banner_block}", $list, $form);
        return $form;
    }

    function getCatPhoto($art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `MAIN` DESC LIMIT 1;"); $n=$db->num_rows($r);
        list($article_nr_search, $brand_id) = $this->getArtMainInfo($art_id);
        $art_name = $this->getArticleName($art_id);
        $brand_name = $this->getBrandName($brand_id);
        $art_text = "$art_name $brand_name $article_nr_search";
        if ($n==1) {
            $photo_name=trim($db->result($r,0,"PHOTO_NAME"));
            $link="/thumb.php?image=catalogue/$photo_name&size=200";
            $image="<img class=\"d-block lazy\" data-src=\"$link\" alt=\"$art_text\" title=\"$art_text\">";
        } else $image="<img class=\"d-block lazy\" data-src=\"$this->noPhoto\" alt=\"$art_text\" title=\"$art_text\">";
        return $image;
    }

    function showSellBlock() {
        $form=$this->getHtmlForm("sell/sell_form");
        $form=str_replace("{terms_cap}",$this->getHtmlForm("sell/sell_cooperation"),$form);
        $form=str_replace("{deal_cap}",$this->getHtmlForm("sell/sell_deal"),$form);
        return $form;
    }

    function saveSellerForm($company, $name, $phone, $email, $city_id, $comment) { $db=DbSingleton::getDbm();
        $cookie_id=$_COOKIE["session_id"]; $max_bytes=10485760; $format_array=["txt","csv","xls","xlsx","dbf"];
        $r=$db->query("SELECT * FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id`='$cookie_id' ORDER BY `data` DESC LIMIT 1;"); $n=$db->num_rows($r);
        $file_name=$db->result($r,0,"file_name");
        $type=$db->result($r,0,"type");
        $size=$db->result($r,0,"size");
        if ($n>0) {
            $db->query("DELETE FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE 'cookie_id'='$cookie_id';");
        }
        if (in_array($type,$format_array) && $size<=$max_bytes) {
            $db->query("INSERT INTO `J_SUPPLIERS_COOPERATION` (`company`,`name`,`phone`,`email`,`city_id`,`commentary`,`file_id`,`status`) 
            VALUES ('$company','$name','$phone','$email','$city_id','$comment','$file_name',166);");
            return true;
        } else {
            return array($type,$size);
        }
    }

    function getSellerImage() { $db=DbSingleton::getDbm();
        $cookie_id=$_COOKIE["session_id"];
        $r=$db->query("SELECT `file_name`, `real_file_name` FROM `J_SUPPLIERS_COOPERATION_FILES` WHERE `cookie_id`='$cookie_id' ORDER BY `data` DESC LIMIT 1;");
        $real_file_name=$db->result($r,0,"real_file_name");
        return $real_file_name;
    }

    function showHeadTemplate($head_id) {
        $catalogue=new CatalogueClass; $automan=new AutoClass;
        $max_count=15;
        list($tex_text, $text_link)=$automan->getHeadNewDescr($head_id);
        $header="<a href=\"https://toko.ua/catalog/$text_link/\">$tex_text</a>";
        $list=$catalogue->getGroupTreeStr($head_id,"",$max_count);
        return array($list, $header);
    }

    function getGarageLink() {
        $automan=new AutoClass;
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $garage_count=$automan->getGarageAutoCount()[0];
        $garage_count=="" ? $garage_link="href=\"https://toko.ua$prefix/catalogue/auto/\"" : $garage_link="onclick=\"showDropGarage();\"";
        return $garage_link;
    }

}
