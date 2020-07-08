<?php

class FormClass {

    use Helper;
    use Variables;

    private static $articlePhotos = [];
    private static $flags;
    private static $infoTemplates;

    public $uploads_link = "https://toko.ua/uploads/images/catalogue";

    function showModalForm($name) {
        $menu=new MenuClass; $language=new LangClass;
        $form=$this->getHtmlForm("modals/$name");
        $form=$this->replaceLang($form);
        // REGION MODAL
        $form=str_replace("{site_lang_prefix}", $language->getLangPrefix(), $form);
        $form=str_replace("{region_list}", $menu->getRegionList(), $form);
        $form=str_replace("{region_list_phone}", $menu->getRegionListPhone(), $form);
        return $form;
    }

    function getCountryFlag($id_brand) { $db=DbSingleton::getTokoDb();
        if (self::$flags === null) {
            $r=$db->query("SELECT t2c.ALFA2, t2b.BRAND_ID, t2c.COUNTRY_NAME FROM `T2_BRANDS` t2b
                LEFT OUTER JOIN `T2_COUNTRIES` t2c on (t2c.COUNTRY_ID=t2b.COUNTRY_ID)");
            self::$flags = array_column(mysqli_fetch_all($r, MYSQLI_ASSOC), null, 'BRAND_ID');
        }
        $flag=self::$flags[$id_brand]["ALFA2"];
        $name_country=self::$flags[$id_brand]["COUNTRY_NAME"];
        $flag=mb_strtolower($flag);
        if ($name_country=="") return false; else return array($flag,$name_country);
    }

    function showBrandForm($brand) { $db=DbSingleton::getTokoDb();
        $showform=new FormClass;
        $r=$db->query("SELECT * FROM `T2_BRAND_LINK` WHERE `brand_id`='$brand' LIMIT 1;"); $n=$db->num_rows($r);
        if ($n>0) {
            $info=$this->getHtmlForm("brand_form");
            $info=str_replace("{brand_form_name}",trim($db->result($r,0,"name")),$info);
            $info=str_replace("{brand_form_country}",$showform->getCountryFlag($brand)[0],$info);
            $info=str_replace("{brand_form_descr}",trim($db->result($r,0,"descr")),$info);
            $info=str_replace("{brand_form_link}",trim($db->result($r,0,"link")),$info);
            $info=str_replace("{brand_form_logo_name}",trim($db->result($r,0,"logo_name")),$info);
        } else $info=$this->err3;
        return $info;
    }

    function checkT2Link($typ_id, $art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_LINKS` WHERE `ART_ID`='$art_id' AND `TYP_ID`='$typ_id' LIMIT 1;");
        $n=$db->num_rows($r);
        if ($n==0) return false; else return true;
    }

    function getArtBrandLink($art_id, $brand_id) { $db=DbSingleton::getTokoDb();
        $link="";
        $r=$db->query("SELECT * FROM `T2_TREE` WHERE `ART_ID`='$art_id';"); $n=$db->num_rows($r);
        if ($n>0) {
            $str_text="";
            for ($i=1;$i<=$n;$i++) {
                $str_id=$db->result($r, $i-1, "STR_ID");
                $r1=$db->query("SELECT * FROM `T2_GROUP_TREE_STR` WHERE `STR_ID`='$str_id' LIMIT 1;"); $n1=$db->query($r1);
                if ($n1>0) $str_text=$db->result($r1, 0, "TEX_LINK");
                if ($str_text!="") break;
            }
            $brand_link=$this->getBrandLink($brand_id);
            if ($str_text!="") $link="https://toko.ua/catalog/$str_text/brandy=$brand_link/";
        }
        return $link;
    }

    function showArticle($art_id) {
        $cat=new CatalogueClass; $language=new LangClass; $prod=new ProductsClass; $auto=new AutoClass;
        $form=$this->getHtmlForm("cat_article");

        $auto_typ_id = $prod->getCookieAuto();
        if ($auto_typ_id!="") {
            if ($this->checkT2Link($auto_typ_id, $art_id)) {
                $form=str_replace("{applicable_display}", "", $form);
                list($manufacture, $model, $model_id)=$auto->getCarInfo($auto_typ_id);
                list($manufacture_cap,, $model_id_cap,)=$auto->getAutoDescr($manufacture, $model, $model_id, $auto_typ_id);
                $form=str_replace("{applicable_cap}", "<a href='https://toko.ua/catalog/'>$manufacture_cap $model_id_cap</a>", $form);
            }
        }

        $article = $this->getArticleInfo($art_id);
        $article_nr_displ = $article["article_nr_displ"]; $format_article=$this->getFormatAticle($article_nr_displ);
        $brand_id = $article["brand_id"];
        $brand_name = $article["brand_name"];
        $article_name = $article["text"];

        $brand_link=$this->getArtBrandLink($art_id, $brand_id);

        if ($this->getCountryFlag($brand_id)!==false) {
            list($flag, $country_name) = $this->getCountryFlag($brand_id);
            $brand_form="
            <a href=\"$brand_link\">
                <span title=\"$country_name\" class=\"search__brand\" data-title=\"{brand_cap}\">$brand_name</span>
            </a>
            <img class=\"flag flag-$flag flag-search\">";
        } else {
            $brand_form="
            <a href=\"$brand_link\">
                <span title=\"$brand_name\" class=\"search__brand\" data-title=\"{brand_cap}\">$brand_name</span>
            </a>";
        }

        $form=str_replace("{art_id}", $art_id, $form);
        $form=str_replace("{art_name}", $article_nr_displ, $form);
        $form=str_replace("{art_brand_id}", $brand_id, $form);
        $form=str_replace("{art_brand_name}", $brand_name, $form);
        $form=str_replace("{art_brand_form}", $brand_form, $form);
        $form=str_replace("{art_text}", $article_name, $form);
        $form=str_replace("{art_del}", str_replace("<br>", ", ", $article["delivery"]), $form);
        $form=str_replace("{del_class}", ($article["delivery_days"]==0) ? "delivery-red" : ($article["delivery_days"]==1 ? "delivery-blue" : ($article["delivery_days"]>1 ? "delivery-dark" : "")), $form);
        $form=str_replace("{art_stock}", $article["stock"], $form);
        $form=str_replace("{art_price}", $article["price"], $form);
        $form=str_replace("{art_cur}", $article["currency"], $form);
        $form=str_replace("{art_basket}", $article["basket"], $form);
        $form=str_replace("{art_images}", $this->showArticlePhotoGallery($art_id), $form);
        $form=str_replace("{analogs_capa}", "$article_nr_displ $brand_name", $form);
        $form=str_replace("{analogs_link}", "https://toko.ua".$language->getLangPrefix()."/search/$format_article/$brand_id/$brand_name/", $form);

        $analogs=$cat->shortSearchList($art_id);
        $form=str_replace("{analogs_list}", $analogs, $form);
        $form=str_replace("{analogs_display}", $analogs=="" ? "dnone" : "", $form);
        $form=str_replace("{article_header}", "<h1>$article_name $brand_name $article_nr_displ</h1>", $form);
        $form=str_replace("{applicable_display}", "none", $form);

        $form=$this->replaceLang($form);
        return $form;
    }

    function getArticleInfo($art_id) { $db=DbSingleton::getTokoDb();
        $cat=new CatalogueClass; $client=new ClientClass; $kours=new ExRateClass;
        $tpoint=$client->getTpoint(); $cur=$kours->getCurrentKours(); $client_id=$this->getClient();

        $r=$db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2asc.AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
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

        $article_nr_displ = $db->result($r,0,"ARTICLE_NR_DISPL");
        $brand_id = $db->result($r,0,"BRAND_ID");
        $brand_name = $db->result($r,0,"BRAND_NAME");
        $text = $db->result($r,0,"NAME");
        $suppl_id = $db->result($r,0,"suppl_id");
        $stock = intval($db->result($r,0,"AMOUNT"));
        $storage_id = $db->result($r,0,"storage_id");

        $price = $cat->getArticlePrice($art_id);
        if ($suppl_id!=0) $price = $cat->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
        $price = $kours->getKoursPrice($price, $cur);
        if ($cur==1) $price = $client->getClientPriceRounding($client_id, $price);

        list(,$delivery_days,$short_info) = $cat->getTpointDeliveryInfo($tpoint,$storage_id);
        if ($suppl_id!=0) list(,$delivery_days,$short_info) = $cat->getTpointSupplDeliveryInfo($tpoint,$suppl_id,$storage_id);

        $real_stock=$stock; if ($stock>10) $stock=">10";
        $basket="moveBasket(0,'$art_id','$brand_id','$real_stock','$storage_id',$suppl_id,1);";
        $cur_cap=$kours->getKoursCaption($cur);

        $article = ["article_nr_displ"=>$article_nr_displ, "brand_id"=>$brand_id, "brand_name"=>$brand_name, "text"=>$text, "stock"=>$stock, "delivery"=>$short_info, "price"=>$price, "currency"=>$cur_cap, "delivery_days"=>$delivery_days, "basket"=>$basket];
        return $article;
    }

    function getCurrencyForm($type_filter, $template_id, $cur) {
        $kours=new ExRateClass; $client=new ClientClass;
        $jsFilter=$cash_add=""; $ch1=$ch2=$ch3=0;
        list($client_id,$user)=$client->getClient(); $cash_id=$client->getClientCurrency($client_id);
        if ($cur==2) $ch2="checked=\"checked\""; elseif ($cur==3) $ch3="checked=\"checked\""; else $ch1="checked=\"checked\"";
        switch ($type_filter) {
            case 1: {$jsFilter = "catalogueFilter();"; break;}
            case 2: {$jsFilter = "tecModelsFilter();"; break;}
            case 3: {$jsFilter = "catGroupFilter($template_id);"; break;}
            case 4: {$jsFilter = "showBasketForm();"; break;}
        }
        $list="";
        if ($cash_id==2) $cash_add="<input id=\"radio_usd\" type=\"radio\" name=\"cur\" value=\"$cash_id\" $ch2 onclick=\"$jsFilter\"><label for=\"radio_usd\">$</label>";
        if ($cash_id==3) $cash_add="<input id=\"radio_eur\" type=\"radio\" name=\"cur\" value=\"$cash_id\" $ch3 onclick=\"$jsFilter\"><label for=\"radio_eur\">€</label>";
        if ($user!=0) {
            $cur_usd=$kours->getKours("dollar"); $cur_eur=$kours->getKours("euro");
            $list.="<input id=\"radio_uah\" type=\"radio\" name=\"cur\" value=\"1\" $ch1 onclick=\"$jsFilter\">
                <label for=\"radio_uah\" class=\"tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"{currency_cap}: &#xA USD - $cur_usd &#xA EURO - $cur_eur\">{uah_cap}</label>
            $cash_add";
        } else {
            $list="";
        }
        return $list;
    }

    function showCityForm($city_like, $city_id=""){ $db=DbSingleton::getDbm();
        if ($city_id=="") $city_id=0; $mas=array();
        if ($city_like!="") $where="WHERE `CITY_NAME` LIKE '%$city_like%'"; else $where="WHERE `CITY_ID` IN ($city_id,10108,13549,4074,22739)";
        $r=$db->query("SELECT * FROM `T2_CITY` t2c
            LEFT JOIN `T2_REGION` t2r ON (t2r.REGION_ID=t2c.REGION_ID)
            LEFT JOIN `T2_STATE` t2s ON (t2s.STATE_ID=t2r.STATE_ID)
        $where;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $id=$db->result($r,$i-1,"CITY_ID");
            $city=$db->result($r,$i-1,"CITY_NAME");
            $region=$db->result($r,$i-1,"REGION_NAME");
            $state=$db->result($r,$i-1,"STATE_NAME");
            if ($region=="") $location="$city"; else $location="$city - $region - $state";
            if ($id==$city_id) $selected=true; else $selected=false;
            $mas[$i]=["id"=>$id,"value"=>$location,"selected"=>$selected];
        }
        return $mas;
    }

    function showCityFormSelected($city_like, $city_id) { $db=DbSingleton::getDbm();
        if ($city_id=="") $city_id=0; $list="";
        if ($city_like!="") $where="WHERE `CITY_NAME` LIKE '%$city_like%'"; else $where="WHERE `CITY_ID` IN ($city_id,10108,13549,4074,22739)";
        $r=$db->query("SELECT * FROM `T2_CITY` t2c
            LEFT JOIN `T2_REGION` t2r ON (t2r.REGION_ID=t2c.REGION_ID)
            LEFT JOIN `T2_STATE` t2s ON (t2s.STATE_ID=t2r.STATE_ID)
        $where;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $id=$db->result($r,$i-1,"CITY_ID");
            $city=$db->result($r,$i-1,"CITY_NAME");
            $region=$db->result($r,$i-1,"REGION_NAME");
            $state=$db->result($r,$i-1,"STATE_NAME");
            if ($region=="") $location="$city"; else $location="$city - $region - $state";
            if ($id==$city_id) $checked="selected=\"selected\""; else $checked="";
            $list.="<option value=\"$id\" $checked>$location</option>";
        }
        return $list;
    }

    function showInfoTemplate($art_id) { $db=DbSingleton::getTokoDb();
        $info="";
        if (!isset(self::$infoTemplates[$art_id])) {
            $r=$db->query("SELECT `TEXT`, `VALUE` FROM `T2_INFO` WHERE `ART_ID`='$art_id' AND `LANG_ID`='16' ORDER BY `SORT` ASC;");
            self::$infoTemplates[$art_id] = mysqli_fetch_all($r, MYSQLI_ASSOC);
        }
        if (self::$infoTemplates[$art_id]) {
            $info="<ul class=\"inline-list\">";
            foreach (self::$infoTemplates[$art_id] as $infoTemplate) {
                $info.="<li><span class=\"bold\">{$infoTemplate['TEXT']}</span>: {$infoTemplate['VALUE']}</li>";
            }
            $info.="</ul>";
        }
        return $info;
    }

    public static function cacheInfoTemplates($where_art_id_str) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT `TEXT`, `VALUE`, `ART_ID` FROM `T2_INFO` WHERE `ART_ID` IN ($where_art_id_str) AND `LANG_ID`='16' ORDER BY `SORT` ASC;");
        $infoTemplates = mysqli_fetch_all($r, MYSQLI_ASSOC);
        foreach ($infoTemplates as $infoTemplate) {
            self::$infoTemplates[$infoTemplate['ART_ID']][] = $infoTemplate;
        }
    }

    /*==== HISTORY ====*/
    function insertHistory($article_nr_displ, $brand_id) { $db = DbSingleton::getTokoDb();
        session_start(); $ses=session_id(); $cookie=$_COOKIE["session_id"];
        $date=date("Y-m-d H:i:s"); $client_id=$this->getClient(); $user=$this->getUser();
        $artData=$this->getBrandId($article_nr_displ); $max_history_count=10;
        $art_id=$artData[1];
        if ($brand_id>0 && is_numeric($brand_id)) {
            if ($user==0) $where="`cookie_id`='$cookie'"; else $where="`client_id`='$client_id' AND `client_user_id`='$user'";
            $r=$db->query("SELECT COUNT(`id`) as kilk FROM `CLIENT_HISTORY` WHERE $where;"); $k=$db->result($r,0,"kilk");
            if ($k>$max_history_count) {
                $r=$db->query("SELECT `id` FROM `CLIENT_HISTORY` WHERE $where ORDER BY `data` ASC LIMIT 1;");
                $id=$db->result($r,0,"id");
                $db->query("UPDATE `CLIENT_HISTORY` SET `data`='$date', `article_nr_displ`='$article_nr_displ', `brand_id`='$brand_id', `art_id`='$art_id' WHERE `id`='$id';");
            } else {
                $r=$db->query("SELECT `id` FROM `CLIENT_HISTORY` WHERE $where AND `article_nr_displ`='$article_nr_displ' AND `brand_id`='$brand_id';"); $n=$db->num_rows($r);
                if ($n>0)
                    $db->query("UPDATE `CLIENT_HISTORY` SET `data`='$date' WHERE $where AND `article_nr_displ`='$article_nr_displ' AND `brand_id`='$brand_id';");
                else
                    $db->query("INSERT INTO `CLIENT_HISTORY` (`client_id`, `client_user_id`, `ses_id`, `cookie_id`, `article_nr_displ`, `brand_id`, `data`, `art_id`) 
                    VALUES ('$client_id','$user','$ses','$cookie','$article_nr_displ','$brand_id','$date','$art_id');");
            }
        }
        return true;
    }

    function showHistoryForm() {
        $cat=new CatalogueClass;
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $list=$this->getHistory(); $result="";
        for ($i=0;$i<count($list);$i++) { $col=$i+1;
            $article_nr_displ=$list[$i]["article_nr_displ"];
            $brand=$list[$i]["brand"];
            $brand_link=$list[$i]["brand_link"];
            $result.="<li>$col. <a href=\"https://toko.ua$prefix/$cat->search_link/$article_nr_displ/$brand_link/\">$article_nr_displ ($brand)</a></li>";
        }
        !empty($list) ? : $result.="<p>{empty_history}</p>";
        $form=$this->getHtmlForm("menu/history_block");
        $form=str_replace("{history_block}", $result, $form);
        return $form;
    }

    // PHONE HISTORY
    function showHistoryList() {
        $cat=new CatalogueClass;
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $list=$this->getHistory(); $max_count=9;
        $list_history="";
        for ($i=0; $i<count($list); $i++) {
            $id=$list[$i]["id"];
            $article_nr_displ=$list[$i]["article_nr_displ"];
            $brand=$list[$i]["brand"];
            $brand_link=$list[$i]["brand_link"];
            $list_history.="<li class=\"search-nav__item\">
                <div class='container'>
                <div class='row'>
                    <div class='col-10'>
                        <a href=\"https://toko.ua$prefix/$cat->search_link/$article_nr_displ/$brand_link/\">
                            <i class='fa fa-history'></i>
                            $brand $article_nr_displ
                        </a>
                    </div>
                    <div class='col-2'>
                        <a class='float-right' onclick='deleteHistoryItem($id)'><i class='fa fa-times'></i></a>
                    </div>
                </div>
                </div>
            </li>";
            if ($i==$max_count) break;
        }
        $form=$this->getHtmlForm("menu/history_list");
        $form=str_replace("{history_range}",$list_history,$form);
        if (count($list)==0) $form="";
        $form=$this->replaceLang($form);
        return $form;
    }

    function deleteHistoryItem($id) { $db=DbSingleton::getTokoDb();
        if ($id=="") {
            $cookie=$_COOKIE["session_id"];
            $client_id=$this->getClient(); $user=$this->getUser();
            if ($user==0) $where="`cookie_id`='$cookie'"; else $where="`client_id`='$client_id' AND `client_user_id`='$user'";
        } else {
            $where = "`id`='$id'";
        }
        $db->query("DELETE FROM `CLIENT_HISTORY` WHERE $where;");
        return true;
    }

    /*==== /HISTORY ====*/

    /*==== PHOTO GALLERY ====*/
    function getArticleMainPhoto($art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC LIMIT 1;"); $n=$db->num_rows($r); $photo_name="";
        for ($i=1;$i<=$n;$i++){
            $photo_name=trim($db->result($r,$i-1,"PHOTO_NAME"));
        }
        if ($photo_name=="") $photo_name=$this->noPhoto;
        return $photo_name;
    }

    function getArticleActivePhoto($art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC LIMIT 1;"); $n=$db->num_rows($r); $photo_name="";
        for ($i=1;$i<=$n;$i++){
            $photo_name=trim($db->result($r,$i-1,"PHOTO_NAME"));
        }
        $photo_name=="" ? $photo_name=$this->noPhoto : $photo_name="$this->uploads_link/$photo_name";
        return $photo_name;
    }

    function checkArticlePhoto($art_id) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC LIMIT 1;"); $n=$db->num_rows($r);
        if ($n>0) return 1; else return 0;
    }

    function getShortArticlePhoto($art_id) { $db=DbSingleton::getTokoDb();
        $photo_name="";
        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC LIMIT 1;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $photo_name=trim($db->result($r,$i-1,"PHOTO_NAME"));
        }
        $photo_name=="" ? $photo_name=$this->noPhoto : $photo_name="$this->uploads_link/".$photo_name;
        $photo="<img itemprop=\"image\" src=\"$photo_name\" alt=\"Article\">";
        return $photo;
    }

    function getArticlePhotos($art_id) {
        if (!isset(self::$articlePhotos[$art_id])) {
            $db=DbSingleton::getTokoDb();
            $r = $db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC;");
            self::$articlePhotos[$art_id] = mysqli_fetch_all($r, MYSQLI_ASSOC);
        }
        return self::$articlePhotos[$art_id];
    }

    public static function cacheArticlesPhotos($where_art_id_str) { $db=DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID` IN ($where_art_id_str) AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC;");
        $photos = mysqli_fetch_all($r, MYSQLI_ASSOC);
        foreach ($photos as $photo) {
            self::$articlePhotos[$photo['ART_ID']][] = $photo;
        }
    }

    /*==== /PHOTO GALLERY ====*/

    /*==== INFO FORM ====*/
    function showPhotoGallery($art_id, $display=0) { $db=DbSingleton::getTokoDb();
        $cat=new CatalogueClass;
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $nophoto=$this->noPhoto;

        $article_name=$cat->getArticleSearch($art_id);
        $brand_name=$cat->getBrandName($cat->getBrandFromArtId($art_id));
        $format_name=$cat->getFormatAticle($article_name);
        $format_brand=$cat->getFormatBrand($brand_name);

        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC;"); $n=$db->num_rows($r); $list="";
        for ($i=1;$i<=$n;$i++){
            $photo_name=trim($db->result($r,$i-1,"PHOTO_NAME"));
            $i==1 ? $active="active" : $active="";

            if ($display==1) {
                $list.="<div class=\"carousel-item $active\">
                    <a itemprop=\"url\" href=\"https://toko.ua$prefix/article/$format_name/$format_brand/$art_id/\">
                        <img itemprop=\"image\" class=\"d-block img-fluid lazy\" data-src=\"$this->uploads_link/$photo_name\" alt=\"Slide $i\">
                    </a>
                </div>";
            } else {
                $list.="<div class=\"carousel-item $active\">
                    <img itemprop=\"image\" class=\"d-block img-fluid lazy\" data-src=\"$this->uploads_link/$photo_name\" alt=\"Slide $i\">
                    <div class=\"carousel-caption\">{page_cap} $i {of_cap} $n</div>
                </div>";
            }
        }
        if ($n>0) {
            $info="<div class=\"row\">
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
            $info="<div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\">
                        <div class=\"carousel-inner\" role=\"listbox\">
                            <div class=\"carousel-item active\">
                                <img itemprop=\"image\" class=\"d-block img-fluid lazy\" data-src=\"https://toko.ua$nophoto\" alt=\"Slide 1\">
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        }
        $info=$this->replaceLang($info);
        return $info;
    }

    function showArticlePhotoGallery($art_id) { $db=DbSingleton::getTokoDb();
        $cat = new CatalogueClass;

        $article_nr_dspl=$cat->getArticleDispl($art_id);
        $brand_name=$cat->getBrandName($cat->getBrandFromArtId($art_id));
        $article_name=$cat->getArticleName($art_id);
        $article_info="$article_name $brand_name $article_nr_dspl - {photo_card_cap}";
        $nophoto=$this->noPhoto;

        $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC;"); $n=$db->num_rows($r); $list="";
        for ($i=1;$i<=$n;$i++) {
            $photo_name = trim($db->result($r,$i-1,"PHOTO_NAME"));
            $i==1 ? $active="active" : $active="";
            $page_cap="<div class=\"carousel-caption\">{page_cap} $i {of_cap} $n</div>";
            $list.="<div class=\"carousel-item $active\">
                <div class=\"search__photo\" style='height: 400px'>
                    <img itemprop=\"image\" src=\"$this->uploads_link/$photo_name\" alt=\"$article_info #$i\" title=\"$article_info #$i\">
                </div>
                $page_cap
            </div>";
        }
        if ($n==0) {
            $gallery="<div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\" style='border: 1px solid #e9e9e9;border-radius: .25em;'>
                        <div class=\"carousel-inner\" role=\"listbox\">
                            <div class=\"carousel-item active\">
                                <div class=\"search__photo\">
                                    <img itemprop=\"image\" src=\"https://toko.ua$nophoto\" alt=\"$article_info\" title=\"$article_info\">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>";
        } else {
            $gallery="<div class=\"row\">
                <div class=\"col-12\">
                    <div id=\"carouselGalleryControls\" class=\"carousel slide\" data-ride=\"carousel\" style='border: 1px solid #e9e9e9;border-radius: .25em;'>
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

        $info = $this->getArticleInfoForm($art_id,1);
        if ($info!="") $info = "<div style='border:1px solid #e9e9e9;border-radius:.25em; padding:10px;'>$info</div>";
        $applicability = $this->getApplManufTCD($art_id);
        $originals = $cat->getOriginalNumbers($art_id);

        $form="
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

        $form=$this->replaceLang($form);
        return $form;
    }

    function showInfoForm($art_id) {
        $catalogue=new CatalogueClass;
        $article_nr_displ=$catalogue->getArticleDispl($art_id);
        $brand_name=$catalogue->getBrandName($catalogue->getBrandFromArtId($art_id));
        $title="<span class=\"text-dark bold\">$brand_name</span> $article_nr_displ";
        $form=$this->getHtmlForm("modals/form_info");
        $form=str_replace("{info-main__photo}",$this->showPhotoGallery($art_id),$form);
        $form=str_replace("{info-main__parameters}",$this->getArticleInfoForm($art_id),$form);
        $form=str_replace("{info-applicability}",$this->getApplManufTCD($art_id),$form);
        $form=str_replace("{info-original}",$catalogue->getOriginalNumbers($art_id),$form);
        $form=$this->replaceLang($form);
        return array($form, $title);
    }

    function getApplManufTCD($art_id) { $db = DbSingleton::getTokoDb();
        $typ_id_str=$list="";
        $r=$db->query("SELECT `TYP_ID` FROM `T2_LINKS` WHERE `ART_ID`='$art_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $typ_id=$db->result($r,$i-1,"TYP_ID");
            $typ_id_str.="$typ_id";
            if ($i<$n) {$typ_id_str.=",";}
        }
        if ($typ_id_str!="") {
            $r=$db->query("SELECT man.MFA_ID, man.MFA_BRAND 
            FROM `T_types` tt 
                INNER JOIN `T_models` tm ON tm.MOD_ID=tt.TYP_MOD_ID 
                INNER JOIN `T_manufacturers` man ON man.MFA_ID=tm.MOD_MFA_ID 
            WHERE tt.TYP_ID IN ($typ_id_str) AND tt.ACTIVE=1 
            GROUP BY man.MFA_ID ORDER BY man.MFA_BRAND ASC;"); $n=$db->num_rows($r);
            if ($n>0) {
                for ($i=1;$i<=$n;$i++){
                    $brand_id=$db->result($r,$i-1,"MFA_ID");
                    $brand=$db->result($r,$i-1,"MFA_BRAND");
                    $list.="<a class=\"padr15 load_app pointer\" onclick='loadApplicModels2(\"$art_id\",\"$brand_id\",this)'><i class=\"fas fa-car\"></i>$brand</a>";
                }
            } else $list=$this->err1;
        }
        else $list=$this->err1;
        return $list;
    }

    function getApplModelTCD($art_id, $mfa) { $db = DbSingleton::getTokoDb();
        $list="<div class=\"search__appl-tcd\">"; $typ_id_str="";
        $r=$db->query("SELECT `TYP_ID` FROM `T2_LINKS` WHERE `ART_ID`='$art_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $typ_id=$db->result($r,$i-1,"TYP_ID");
            $typ_id_str.="$typ_id";
            if ($i<$n){$typ_id_str.=",";}
        }
        $r=$db->query("SELECT tt.*, tm.TEX_TEXT, tm.MOD_ID, tm.MOD_PCON_START, tm.MOD_PCON_END 
        FROM `T_types` tt 
            INNER JOIN `T_models` tm ON tm.MOD_ID=tt.TYP_MOD_ID 
            INNER JOIN `T_manufacturers` man ON man.MFA_ID=tm.MOD_MFA_ID
        WHERE tt.TYP_ID IN ($typ_id_str) AND tm.MOD_MFA_ID='$mfa' AND tt.ACTIVE=1 
        GROUP BY tt.TYP_ID ORDER BY tm.Model ASC;");$n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $typ_id=$db->result($r,$i-1,"TYP_ID");
            $model=$db->result($r,$i-1,"TYP_MMT_TEXT");
            $d_start=$db->result($r,$i-1,"TYP_PCON_START");
            if ($d_start==0){$d_start="-";} if (strlen($d_start)==6){$d_start=substr($d_start,0,4).".".substr($d_start,4,2);}
            $d_end=$db->result($r,$i-1,"TYP_PCON_END");if ($d_end==0){$d_end="-";}
            if (strlen($d_end)==6){$d_end=substr($d_end,0,4).".".substr($d_end,4,2);}
            if ($d_end=="" || $d_end=="-") $d_end="{cur_time}";
            $list.="<li class=\"list-inline\">
                <a onclick=\"loadApplicModelsInfo2('$art_id','$typ_id')\" id=\"mm_car$typ_id\">$model ($d_start-$d_end)</a> 
                <div id=\"AMI$typ_id\"></div>
            </li>";
        }
        if ($n>20){$list="<div>$list</div>";}
        $list.="</div>";
        $list=$this->replaceLang($list);
        return $list;
    }

    function getApplModelInfoTCD($art_id, $typ_id) { $db = DbSingleton::getTokoDb();
        //DELETE????????
        $automan=new AutoClass;
        $form=$this->getHtmlForm("cat_modif_group_form");
        $r=$db->query("SELECT tt.*, man.MFA_ID, tm.Model, tm.MOD_ID
        FROM `T2_LINKS` tl 
            INNER JOIN `T_types` tt ON tt.TYP_ID=tl.TYP_ID 
            INNER JOIN `T_models` tm ON tm.MOD_ID=tt.TYP_MOD_ID 
            INNER JOIN `T_manufacturers` man ON man.MFA_ID=tm.MOD_MFA_ID
        WHERE tl.ART_ID='$art_id' AND tt.TYP_ID='$typ_id' AND tt.ACTIVE=1 
        GROUP BY tm.MOD_ID ORDER BY tt.TYP_TEXT ASC;"); $n=$db->num_rows($r); $list="";
        for ($i=1;$i<=$n;$i++) {
            $TYP_TEXT=$db->result($r,$i-1,"TYP_TEXT");
            $fuel=$db->result($r,$i-1,"FUEL_ID"); $fuel_name=$automan->getFuelName($fuel);
            $start=$db->result($r,$i-1,"TYP_PCON_START"); if ($start==0){$start="";}if (strlen($start)==6){$start=substr($start,0,4).".".substr($start,4,2);}
            $end=$db->result($r,$i-1,"TYP_PCON_END"); if ($end==0){$end="";}if (strlen($end)==6){$end=substr($end,0,4).".".substr($end,4,2);}
            $TYP_KW_FROM=$db->result($r,$i-1,"TYP_KW_FROM");
            $TYP_HP_FROM=$db->result($r,$i-1,"TYP_HP_FROM");
            $TYP_CCM=$db->result($r,$i-1,"TYP_CCM");
            $ENG_Cod=$db->result($r,$i-1,"ENG_Cod");
            $list.="<tr class=\"pointer\" href=\"/catalog\" style=\"font-size: .8em;\">
                <td>$fuel_name</td>
                <td>$TYP_TEXT</td>
                <td>$start - $end</td>
                <td>$TYP_KW_FROM / $TYP_HP_FROM</td>
                <td>$TYP_CCM</td>
                <td>$ENG_Cod</td>
            </tr>";
        }
        $form=str_replace("{list}",$list,$form);
        $form="<div>".$form."</div>";
        $form=$this->replaceLang($form);
        return $form;
    }

    function getArticleInfoForm($art_id, $display=0) { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT `TEXT`, `VALUE` FROM `T2_INFO` WHERE `ART_ID`='$art_id' AND `LANG_ID`='16' ORDER BY `SORT` ASC;"); $n=$db->num_rows($r); $info="";
        if ($n>0) {
            !$display ? $class="info__table" : $class="info__table_min";
            $info.="<table class='$class'>";
            for ($i=1;$i<=$n;$i++) {
                $text=$db->result($r, $i-1, "TEXT");
                $value=$db->result($r, $i-1, "VALUE");
                $info.="<tr>
                    <td class='text-left bold'>$text</td> 
                    <td class=\"text-right\">$value</td>
                </tr>";
            }
            $info.="</table>";
            !$display ?: $info="<div style='padding: 10px;'>$info</div>";
        } else {
            !$display ? $info="{info_cap}" : $info="";
        }
        $info=str_replace('"',"",$info);
        return $info;
    }

    function getCarsBanner() { $db=DbSingleton::getTokoDb();
        $form=$this->getHtmlForm("home/banner");
        $indicators=""; $items=""; $k=0;
        $r=$db->query("SELECT * FROM `banner` WHERE `STATUS`=1 ORDER BY `POSITION` ASC;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $title = $db->result($r, $i - 1, "TITLE");
            $text = $db->result($r, $i - 1, "TEXT");
            $image = $db->result($r, $i - 1, "IMAGE");

            $k==0 ? $class="active" : $class="";
            $indicators.="<li data-target=\"#carouselBanner\" data-slide-to=\"$k\" class=\"$class\"></li>";
            $items.="<div class=\"carousel-item $class\">".$this->getCarsBannerItem($title, $text, "/images/banners/".$image)."</div>";
            $k++;
        }
        $form=str_replace("{carousel_indicators}", $indicators, $form);
        $form=str_replace("{carousel_items}", $items, $form);

        return $form;
    }

    function getCarsBannerItem($title, $text, $image) {
        $form=$this->getHtmlForm("home/banner_item");
        $form=str_replace("{banner_title}", $title, $form);
        $form=str_replace("{banner_text}", $text, $form);
        $form=str_replace("{banner_image}", $image, $form);
        return $form;
    }

    function showHomeCars() { $db=DbSingleton::getTokoDb();
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $list="<div class='seo_details'><div class='seo-ul'>";
        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 ORDER BY `POSITION` DESC LIMIT 0,25;"); $n=$db->num_rows($r);
        $arr = [];
        for ($i=1;$i<=$n;$i++) {
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mfa_image = $db->result($r, $i - 1, "LOGO_SVG");
            $arr[$i]=["brand"=>$mfa_brand, "link"=>$mfa_link, "image"=>$mfa_image];
        }
        sort($arr);
        foreach ($arr as $value) {
            $mfa_brand = $value["brand"];
            $mfa_link = $value["link"];
            $mfa_image = $value["image"];
            $list.="<a class='seo-li seo-li-min' href='https://toko.ua$prefix/cars/$mfa_link/'>
                <img src=\"https://toko.ua/uploads/images/manufacturers/svg/$mfa_image\" alt=\"$mfa_brand\">
                <span>$mfa_brand</span>
            </a>";
        }
        $list.="</div></div>";
        return $list;
    }

}
