<?php

class ProductsClass extends CatalogueClass {

    use Helper;
    use Variables;

    function getCookieAuto() {
        $auto_typ_id=$_COOKIE["auto_typ_id"];
        $selected_auto="";
        if ($auto_typ_id>0 && $auto_typ_id!="") $selected_auto=$auto_typ_id;
        return $selected_auto;
    }

//    function getCookieGarage() {
//        $auto_garage_id=$_COOKIE["auto_garage_id"];
//        $selected_auto="";
//        if ($auto_garage_id>0 && $auto_garage_id!="") $selected_auto=$auto_garage_id;
//        return $selected_auto;
//    }

//    function getCookieSelectedGarage($mfa_link, $model_link) {
//        $automan = new AutoClass;
//        $auto_garage_id=$_COOKIE["auto_garage_id"];
//        if ($auto_garage_id>0 && $auto_garage_id!="") {
//            list($mfa,$model,,)=$automan->getCookieCarInfo($auto_garage_id);
//            if ($mfa==$mfa_link && $model==$model_link) {
//                $typ_id=$auto_garage_id;
//            } else {
//                $typ_id="";
//            }
//        } else {
//            $typ_id="";
//        }
//        return $typ_id;
//    }

    function getCookieSelectedAuto($mfa_link, $model_link) {
        $automan = new AutoClass;
        $auto_typ_id=$_COOKIE["auto_typ_id"];
        if ($auto_typ_id>0 && $auto_typ_id!="") {
            list($mfa,$model,,)=$automan->getCookieCarInfo($auto_typ_id);
            if ($mfa==$mfa_link && $model==$model_link) {
                $typ_id=$auto_typ_id;
            } else {
                setcookie("auto_typ_id","",time()-3600,"/");
                $typ_id="";
            }
        } else {
            setcookie("auto_typ_id","",time()-3600,"/");
            $typ_id="";
        }
        return $typ_id;
    }

    /*car Details=====================================================*/

    function techCarModels($typ_id, $str_id) {

        setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");

        $kours=new ExRateClass; $automan=new AutoClass;
        $cur=$kours->getCurrentKours();

        list($str_level, $str_id_parrent) = $automan->getStrParams($str_id);
        $str_text = $automan->getStrNewDescr($str_id); if ($str_text=="") $str_text = $automan->getStrDescr($str_id);

        if ($str_level==NULL && $str_id_parrent==NULL) {
            $list = $this->searchList("0", 2)[0]; // error TREE
        } else {
            $list = $this->techModelsList($typ_id, $str_id, $str_level, $str_id_parrent)[0];
        }

        $form=$this->getHtmlForm("cat_new_search");
        $form=str_replace("{type_search}", 2, $form);
        $form=str_replace("{cur_value}", $cur, $form);

        $search_main=$this->getSearchMainTree($this->getHtmlForm("cat_search_main"), $list, $str_text, $typ_id, $str_id);

        $form=str_replace("{cat_search_main}", $search_main, $form);
        $form=str_replace("{cat_search_new_tree}", "", $form);
        $form=str_replace("{cat_search_tree}", "", $form);
        $form=str_replace("{cat_search_filters}", "", $form);
        $form=str_replace("{cat_search_brands}", "", $form);

        $form=str_replace("{search_typ_id}", $typ_id, $form);
        $form=str_replace("{search_str_id}", $str_id, $form);

        $form=$this->replaceLang($form);

        return $form;
    }

    function techCarModelsFilter($typ_id, $str_id) { $db = DbSingleton::getTokoDb();
        $language=new LangClass; $automan=new AutoClass;
        $lang=$language->getLanguage(); $lang=$language->getOldLanguage($lang);

        session_start(); $key=session_id()."_".time();

        list($str_level, $str_id_parrent) = $automan->getStrParams($str_id);

        $true_str_id=$str_id; $true_str_level=$str_level; $true_str_id_parrent=$str_id_parrent;

        $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `TEMP_ARTICLES_$key` (`art_id` INT NOT NULL ,`amount` INT( 2 ) NOT NULL ,`status` INT( 2 ) NOT NULL, `price` INT( 2 ) NOT NULL ) ENGINE = MYISAM;");

        $query="SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$typ_id' GROUP BY `ART_ID`;";
        $r=$db->query($query); $n=$db->num_rows($r); $art_id_str="0";
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");if ($art_id!=""){$art_id_str.=",$art_id";}
            $db->query("INSERT INTO `TEMP_ARTICLES_$key` (`art_id`, `amount`, `status`) VALUES ($art_id, 0, 0);");
        }

        $query="SELECT `ART_ID`, `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` IN ($art_id_str);";
        $r=$db->query($query); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $amount=$db->result($r,$i-1,"AMOUNT");
            if ($amount>0) $db->query("UPDATE `TEMP_ARTICLES_$key` SET `amount`=1 WHERE `art_id`='$art_id';");
        }

        $query="SELECT ta.art_id, t2si.suppl_id, t2si.client_storage_id FROM `TEMP_ARTICLES_$key` ta
            INNER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=ta.art_id AND t2si.status=1);";
        $r=$db->query($query); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"art_id");
            $suppl_id=$db->result($r,$i-1,"suppl_id");
            $suppl_storage_id=$db->result($r,$i-1,"client_storage_id");
            $price=$this->getArticlePrice($art_id);
            if($suppl_id>0) $price=$this->getArticleSupplPrice($art_id, $suppl_id, $suppl_storage_id);
            if($price>0) $db->query("UPDATE `TEMP_ARTICLES_$key` SET `status`=1 WHERE `art_id`='$art_id';");
        }

        $query="SELECT `art_id`, `amount`, `status` FROM `TEMP_ARTICLES_$key` WHERE ((`amount`=1) OR (`status`=1));";
        $r=$db->query($query); $n=$db->num_rows($r); $art_id_str="0";
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"art_id");
            $art_id_str.=",$art_id";
        }

        $str_level=$automan->getAutoStrData()[1];
        $str_id_parrent=$automan->getAutoStrData()[2];
        $status_str="";

        if ($true_str_level==NULL && $true_str_id_parrent==NULL) {
            $list_filters="<div id=\"cat_search_filters\"></div>";
            $list_brand="<div id=\"cat_search_brands\"></div>";
            $status_str="dnone";
        } else {
            list(, $list_brand, $list_filters) = $this->techModelsList($typ_id, $str_id, $str_level, $str_id_parrent);
        }

        $query="SELECT * FROM `T2_TREE` WHERE `ART_ID` IN ($art_id_str);";
        $r=$db->query($query); $n=$db->num_rows($r); $str_id_str="0"; $str_id_a=array();
        for ($i=1;$i<=$n;$i++) {
            $str_id=$db->result($r,$i-1,"STR_ID"); $str_id_str.=",$str_id";
            $art_id=$db->result($r,$i-1,"ART_ID"); $str_id_a[$str_id][$art_id]=$art_id;
        }
        $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$key`;");

        $query="SELECT `STR_ID`, `STR_ID_PARENT`, `STR_LEVEL`, `DISP_TEXT`, `POSITION` FROM `T2_GROUP_TREE` 
        WHERE `STR_ID` IN ($str_id_str) AND `LNG_ID`=$lang;";
        $r=$db->query($query); $n=$db->num_rows($r); $td_array=[];
        for ($i=1;$i<=$n;$i++) {
            $str_id=$db->result($r,$i-1,"STR_ID");
            $str_id_parrent=$db->result($r,$i-1,"STR_ID_PARENT");if ($str_id_parrent==""){$str_id_parrent=0;}
            $str_level=$db->result($r,$i-1,"STR_LEVEL");
            $tex_text=$db->result($r,$i-1,"DISP_TEXT");
            $position=$db->result($r,$i-1,"POSITION");
            $child=$this->getTecGroupTreeChilds($str_id);
            $art_ids=implode(",", $str_id_a[$str_id]);
            $td_array[$i]["id_tree"] = $str_id;
            $td_array[$i]["id_parent"] = $str_id_parrent;
            $td_array[$i]["level"] = $str_level;
            $td_array[$i]["name"] = $tex_text;
            $td_array[$i]["child"] = $child;
            $td_array[$i]["art_ids"] = $art_ids;
            $td_array[$i]["position"] = $position;
        }

        $position_fare=[]; $parent_fare=[];
        foreach ($td_array as $key => $row) {
            $parent_fare[$key] = $row["id_parent"];
            $position_fare[$key] = $row["position"];
        }

        array_multisort($parent_fare, SORT_ASC, $position_fare, SORT_DESC, $td_array);

        $cat_search_new_tree = $this->getCarDetailsMin($str_id_str, "");
        $cat_search_tree = $this->getSearchTree($this->getHtmlForm("cat_search_tree"), $td_array, $typ_id, $status_str, $true_str_id); $cat_search_tree=$this->replaceLang($cat_search_tree);
        $cat_search_filters = $list_filters;
        $cat_search_brands = $list_brand;

        return array($cat_search_new_tree, $cat_search_tree, $cat_search_filters, $cat_search_brands);
    }

    function getStrIds($typ_id) { $db = DbSingleton::getTokoDb();
        session_start(); $key=session_id()."_".time();

        $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `TEMP_ARTICLES_$key` (`art_id` INT NOT NULL ,`amount` INT( 2 ) NOT NULL ,`status` INT( 2 ) NOT NULL, `price` INT( 2 ) NOT NULL ) ENGINE = MYISAM ;");

        $query="SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$typ_id' GROUP BY `ART_ID`;";
        $r=$db->query($query); $n=$db->num_rows($r); $art_id_str="0";
        for ($i=1;$i<=$n;$i++){
            $art_id=$db->result($r,$i-1,"ART_ID");if ($art_id!=""){$art_id_str.=",$art_id";}
            $db->query("INSERT INTO `TEMP_ARTICLES_$key` (`art_id`, `amount`, `status`) VALUES ($art_id, 0, 0);");
        }

        $query="SELECT `ART_ID`, `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` IN ($art_id_str);";
        $r=$db->query($query); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++){
            $art_id=$db->result($r,$i-1,"ART_ID");
            $amount=$db->result($r,$i-1,"AMOUNT");
            if ($amount>0) $db->query("UPDATE `TEMP_ARTICLES_$key` SET `amount`=1 WHERE `art_id`='$art_id';");
        }

        $query="SELECT ta.art_id, t2si.suppl_id, t2si.client_storage_id FROM `TEMP_ARTICLES_$key` ta
            INNER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=ta.art_id AND t2si.status=1);";
        $r=$db->query($query); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++){
            $art_id=$db->result($r,$i-1,"art_id");
            $suppl_id=$db->result($r,$i-1,"suppl_id");
            $suppl_storage_id=$db->result($r,$i-1,"client_storage_id");
            $price=$this->getArticlePrice($art_id);
            if ($suppl_id>0) $price=$this->getArticleSupplPrice($art_id,$suppl_id,$suppl_storage_id);
            if ($price>0) $db->query("UPDATE `TEMP_ARTICLES_$key` SET `status`=1 WHERE `art_id`='$art_id';");
        }

        $query="SELECT `art_id`, `amount`, `status` FROM `TEMP_ARTICLES_$key` WHERE ((`amount`=1) OR (`status`=1));";
        $r=$db->query($query); $n=$db->num_rows($r); $art_id_str="0";
        for ($i=1;$i<=$n;$i++){
            $art_id=$db->result($r,$i-1,"art_id");
            $art_id_str.=",$art_id";
        }

        $query="SELECT * FROM `T2_TREE` WHERE `ART_ID` IN ($art_id_str);";
        $r=$db->query($query); $n=$db->num_rows($r); $str_id_str="0"; $str_id_a=array();
        for ($i=1;$i<=$n;$i++){
            $str_id=$db->result($r,$i-1,"STR_ID"); $str_id_str.=",$str_id";
            $art_id=$db->result($r,$i-1,"ART_ID"); $str_id_a[$str_id][$art_id]=$art_id;
        }
        $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$key`;");

        return $str_id_str;
    }

    // link: /catalog
    function getCarDetails($str_id_str, $typ_id) { $db = DbSingleton::getTokoDb();
        $language=new LangClass;
        $lang_id=$language->getLanguage(); $lang_cap=$language->getTexCapLanguage($lang_id);
        $form=$this->getHtmlForm("cat_car_details");
        $r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD`;"); $n=$db->num_rows($r);
        $list="<ul class='head-list'>";
        for ($i=1;$i<=$n;$i++) {
            $head_id=$db->result($r,$i-1,"HEAD_ID");
            $tex_text=$db->result($r,$i-1,"TEX_$lang_cap");
            $images=$db->result($r,$i-1,"IMAGES");
            $status=$db->result($r,$i-1,"STATUS");
            $check_amount=$this->getGroupTreeAmount($head_id, $str_id_str);
            $header_list="";
            if ($images=="") $photo=$this->noPhoto; else $photo="/uploads/images/group_tree_head/$images";
            if ($status && $check_amount) {
                $list.="<li id=\"head_id_$head_id\" onclick=\"showCarDetailsStr($head_id);\">
                    <div id=\"tree_head-$head_id\" class=\"row align-items-center tree-head pointer-el\">
                        <div class=\"col-lg-4 col-12\"><img class=\"lazy\" data-src=\"$photo\" alt=\"$tex_text\" title=\"$tex_text\"></div>
                        <div class=\"col-lg-7 col-10\"><span>$tex_text</span></div>
                        <div class=\"col-lg-1 col-2 text-right\"><i class=\"fa fa-chevron-right rotate_animation\"></i></div>
                    </div>
                    <div id=\"manufacture_head$head_id\" class=\"tree-list dnone\">$header_list</div>
                </li>";
            }
        }
        $list.="</ul>";
        $form=str_replace("{tree_headers}",$list,$form);
        $form=str_replace("{typ_id}",$typ_id,$form);
        $form=str_replace("{typ_display}",$typ_id=="" ? "none" : "",$form);
        $form=str_replace("{tree_str_ids}",$str_id_str,$form);
        $form=$this->replaceLang($form);
        return $form;
    }

    // link: toko.ua
    function getCarDetailsFull() { $db = DbSingleton::getTokoDb();
        $language=new LangClass;
        $lang_id=$language->getLanguage(); $lang_cap=$language->getTexCapLanguage($lang_id);
        $r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD`;"); $n=$db->num_rows($r);
        $list="<ul class='head-list bordered'>";
        for ($i=1;$i<=$n;$i++) {
            $head_id=$db->result($r,$i-1,"HEAD_ID");
            $tex_text=$db->result($r,$i-1,"TEX_$lang_cap");
            $images=$db->result($r,$i-1,"IMAGES");
            $status=$db->result($r,$i-1,"STATUS");
            $header_list=$this->showCarDetailsStr($head_id);
            if ($images=="") $photo=$this->noPhoto; else $photo="/uploads/images/group_tree_head/$images";
            if ($status) {
                $list.="
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
        $list.="</ul>";
        $list=$this->replaceLang($list);
        return $list;
    }

    // link: /catalog/shrus/kia/sportage
    function getCarDetailsMin($str_id_str, $typ_id) { $db = DbSingleton::getTokoDb();
        $language=new LangClass; $automan=new AutoClass;
        $lang_id=$language->getLanguage(); $lang_cap=$language->getTexCapLanguage($lang_id);
        $form=$this->getHtmlForm("cat_car_min_details");
        $r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD`;"); $n=$db->num_rows($r);
        $list="<ul style=\"list-style:none; padding:0;\">";
        for ($i=1;$i<=$n;$i++) {
            $head_id=$db->result($r,$i-1,"HEAD_ID");
            $tex_text=$db->result($r,$i-1,"TEX_$lang_cap");
            $images=$db->result($r,$i-1,"IMAGES");
            $status=$db->result($r,$i-1,"STATUS");
            $check_amount=$this->getGroupTreeAmount($head_id, $str_id_str);
            $header_list="";
            if ($images=="") $photo=$this->noPhoto; else $photo="/uploads/images/group_tree_head/$images";
            if ($status && $check_amount) {
                $list.="<li id=\"head_id_$head_id\" onclick=\"showCarDetailsStrMin($head_id);\">
                    <div id=\"tree_head-$head_id\" class=\"row align-items-center tree-head tree-head_min pointer-el\">
                        <div class=\"col-lg-4 col-8\"><img src=\"$photo\" alt=\"$images\" title=\"$tex_text\"></div>
                        <div class=\"col-lg-8 col-4\"><span>$tex_text</span></div>
                    </div>
                    <div id=\"manufacture_head$head_id\" class=\"tree-list_min dnone\">$header_list</div>
                </li>";
            }
        }
        $list.="</ul>";
        $form=str_replace("{tree_headers}",$list,$form);
        $form=str_replace("{typ_id}",$typ_id,$form);
        $form=str_replace("{typ_display}",$typ_id=="" ? "none" : "",$form);
        $form=str_replace("{tree_title}",$automan->getCarDescription($typ_id),$form);
        $form=str_replace("{tree_str_ids}",$str_id_str,$form);
        $form=$this->replaceLang($form);
        return $form;
    }

    // get HEAD TREE STR
    function showCarDetailsStr($head_id, $str_id_str="") { $db=DbSingleton::getTokoDb();
        $automan=new AutoClass;
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $lang_id=$language->getLanguage(); $lang_cap=$language->getTexCapLanguage($lang_id);

        $arr=[]; $list="";
        list(, $head_link)=$automan->getHeadNewDescr($head_id);

        if ($str_id_str!="") $where_str="AND cs.STR_ID IN ($str_id_str)"; else $where_str="";

        $r=$db->query("SELECT cs.*, cat.CAT_ID
        FROM `T2_GROUP_TREE_HEAD_STR` cs 
            LEFT OUTER JOIN `T2_GROUP_TREE_HEAD_CAT` cat ON cat.CAT_ID=cs.CAT_ID
		WHERE cs.HEAD_ID='$head_id' $where_str ORDER BY cat.POSITION ASC, cs.POSITION ASC;"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $CAT_ID=$db->result($r,$i-1,"CAT_ID");
                $DISP_TEXT=$db->result($r,$i-1,"TEX_$lang_cap");
                $TEX_LINK=$db->result($r,$i-1,"TEX_LINK");
                $IMAGES=$db->result($r,$i-1,"IMAGES");
                $STR_ID=$db->result($r,$i-1,"STR_ID");
                $arr[$CAT_ID][$i]=["text"=>$DISP_TEXT, "link"=>$TEX_LINK, "image"=>$IMAGES, "str_id"=>$STR_ID];
            }
            foreach ($arr as $key=>$value) {
                list($CAT_NAME, $CAT_LINK) = $automan->getCatNewDescr($key);
                $list.="<div class=\"tree-title\"><span><a href=\"https://toko.ua$prefix/$this->catalog_link/$head_link/$CAT_LINK/\">$CAT_NAME</a></span></div>"; $list.="<ul class=\"tree-str\">";
                foreach ($value as $v) {
                    $text=$v["text"];
                    $link=$v["link"];
                    $img=$v["image"];
                    if ($img=="") $photo = $this->noPhoto; else $photo = "/uploads/images/group_tree_str/$img";
                    $list.="<li class=\"tree-item\">
                        <a href=\"https://toko.ua$prefix/$this->catalog_link/$link/\">
                            <img src=\"$photo\" alt=\"$text\" title=\"$text\">
                            <span>$text</span>
                        </a>
                    </li>";
                }
                $list.="</ul>";
            }
        }
        if ($n==0) $list="";
        $list=$this->replaceLang($list);
        return $list;
    }

    /*SELECT CAR =====================================================*/

    function showCarsSelect($str_text="", $mfa="", $model="") {
        $form=$this->getHtmlForm("cars_form");
        $style_title="car_form-selected";
        $range_model="";
        $mfa_search="{auto_cap}"; $model_search="{model_cap}";
        $mfa_style=""; $model_style="";

        if ($mfa=="") { //AUTO
            $title="{auto_search}";
            $mfa_style=$style_title;
            $range_manuf=$this->getCarManufList(); $range_manuf=$this->drawStyle($range_manuf);
        }
        else { //MANUFACTURE
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title="$mfa_brand";
            $translit=$this->getCarManufTranslit($mfa_id, "");
            if ($translit!="") $title.=" $translit";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $model_style=$style_title;
            $range_model=$this->getCarModelsList($mfa_id); $range_model=$this->drawStyle($range_model);
        }

        $form=str_replace("{cars_title}",$title,$form);
        $form=str_replace("{str_text_select}",$str_text,$form);
        $form=str_replace("{mfa_select}",$mfa,$form);
        $form=str_replace("{model_select}",$model,$form);
        $form=str_replace("{range_manuf}",$range_manuf,$form);
        $form=str_replace("{range_model}",$range_model,$form);
        $form=str_replace("{mfa_search}",$mfa_search,$form);
        $form=str_replace("{model_search}",$model_search,$form);
        $form=str_replace("{mfa_style}",$mfa_style,$form);
        $form=str_replace("{model_style}",$model_style,$form);

        $form=$this->replaceLang($form);
        return $form;
    }

    function getCarManufList($prefix="") { $db = DbSingleton::getTokoDb();
        $first=$second="";
        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 ORDER BY `MFA_BRAND`;"); $n=$db->num_rows($r);
        $list="<ul class=\"t_mfa\">";
        for ($i=1;$i<=$n;$i++) {
            $name=$db->result($r,$i-1,"MFA_BRAND");
            $id=$db->result($r,$i-1,"MFA_ID");
            $mfa_search=$db->result($r,$i-1,"MFA_BRAND_LINK");
            if ($first!=substr($name,0,1) && $second!=substr($name,0,1)) {
                $first = substr($name,0,1);
                $second = substr($name,0,1);
                $main_class = "class=\"search__cat-auto\"";
            } else {
                $first="";$main_class="";
                $second=substr($name,0,1);
            }
            $list.="
            <a href=\"$prefix$mfa_search/\">
                <span class=\"searchtab_model\">$first</span>
                <li $main_class>
                    <span id=\"auto-$id\" class=\"auto-list\">$name</span>
                </li>
            </a>";
        }
        $list.="</ul>";
        return $list;
    }

    function getCarManufVariables($mfa_search) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_BRAND_LINK`='$mfa_search' LIMIT 1;");
        $mfa_id=$db->result($r,0,"MFA_ID");
        $mfa_brand=$db->result($r,0,"MFA_BRAND");
        return array($mfa_id, $mfa_brand);
    }

    function getCarManufTranslit($mfa_id, $model="") { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
        $mfa_translit=$db->result($r,0,"MFA_BRAND_TRANSLIT"); $text="";
        if ($mfa_translit!="") $text="($mfa_translit)";
        if ($model!="") {
            $r=$db->query("SELECT * FROM `T_models` WHERE `Model`='$model' AND `Model_TRANSLIT`!='' LIMIT 1;");
            $model_translit=$db->result($r,0,"Model_TRANSLIT");
            if ($model_translit!="") $text="($mfa_translit $model_translit)";
        }
        return $text;
    }

    function getCarModelsList($mfa_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;"); $n=$db->num_rows($r);
        $list=""; $first=$second="";
        if ($n>0) {
            $list="<ul class=\"t_model\">"; $list=$this->replaceLang($list);
            for ($i=1;$i<=$n;$i++){
                $model=$db->result($r,$i-1,"Model");
                $model_search=$db->result($r,$i-1,"Model_Link");
                if ($first!=substr($model,0,1) && $second!=substr($model,0,1)) {$first=substr($model,0,1); $second = substr($model,0,1); $main_class="class=\"search__cat-auto\"";}
                else {$first=""; $second=substr($model,0,1); $main_class="";}
                $list.="
                <a href=\"$model_search/\">
                    <span class=\"searchtab_model\">$first</span>
                    <li $main_class>
                        <span id=\"model-$model\" class=\"model-list\">$model</span>
                    </li>
                </a>";
            }
            $list.="</ul>";
        }
        return $list;
    }

    function getCarModelVariables($mfa_link, $model_link) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_BRAND_LINK`='$mfa_link' LIMIT 1;");
        $mfa_id=$db->result($r,0,"MFA_ID");
        $r=$db->query("SELECT * FROM `T_models` WHERE `Model_Link`='$model_link' AND `MOD_MFA_ID`=$mfa_id LIMIT 1;");
        $model=$db->result($r,0,"Model");
        return $model;
    }

    function getCarModelIdVariables($mod_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        $text=$db->result($r,0,"TEX_TEXT");
        return $text;
    }

    function getTypesInfo($type_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$type_id' LIMIT 1;");
        $tex_text=$db->result($r,0,"TYP_MMT_TEXT");
        $fuel_id=$db->result($r,0,"FUEL_ID"); $fuel_name=$this->getFuelName($fuel_id);
        $info="$tex_text ($fuel_name)";
        return $info;
    }

    function getTypesShortInfo($type_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$type_id' LIMIT 1;");
        $typ_text=$db->result($r,0,"TYP_TEXT");
        $fuel_id=$db->result($r,0,"FUEL_ID"); $fuel_name=$this->getFuelName($fuel_id);
        $info="$fuel_name $typ_text";
        return $info;
    }

    function drawStyle($list) {
        return "<div class=\"car_form-select_card\">$list</div>";
    }

    /*SELECT CAR MIN======================================*/

    function showCarsSelectMin($str_text, $mfa, $model, $year="", $model_id="", $typ_id="", $fuel_id="", $ajax=false) {
        $automan=new AutoClass;
        $str_id=$automan->getStrNewLinkStr($str_text);

        if (!$ajax)
        if ($this->getCookieAuto()!="") {
            $typ_id=$this->getCookieSelectedAuto($mfa, $model);
            if ($typ_id!="") {
                list($mfa, $model, $year, $model_id)=$automan->getCookieCarInfo($typ_id);
            }
        }

        $form=$this->getHtmlForm("cars_form_min");
        $style_title="car_form-selected"; $style_disabled="car_form-disabled";
        $range_model=""; $range_year=""; $range_model_id=""; $range_type=""; $range_modification="";
        $mfa_style=""; $model_style=""; $year_style=""; $modelid_style=""; $type_style=""; $modification_style="dnone";
        $mfa_search="{auto_cap}"; $model_search="{model_cap}"; $year_search="{year_cap}"; $modelid_search="{model_number}"; $typ_search="{engine_cap}"; $modification_search="{modification_cap}";

        if ($mfa=="") { //AUTO
            $title="{auto_search}";
            $mfa_style=$style_title;
            $range_manuf=$this->getCarManufListMin(); $range_manuf=$this->drawStyle($range_manuf);
        }
        elseif ($model=="") { //MANUFACTURE
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title = "$mfa_brand";
            $translit = $this->getCarManufTranslit($mfa_id, "");
            if ($translit!="") $title.=" $translit";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $model_style=$style_title;
            $range_model=$this->getCarModelsListMin($mfa_id); $range_model=$this->drawStyle($range_model);
        }
        elseif ($year=="") { //MODEL
            $model_cap = $this->getCarModelVariables($mfa, $model);
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title = " $mfa_brand $model_cap";
            $translit=$this->getCarManufTranslit($mfa_id, $model_cap);
            if ($translit!="") $title.=" $translit";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $year_style=$style_title;
            $range_year=$this->getCarYearListMin($model_cap, $mfa_id); $range_year=$this->drawStyle($range_year);
        }
        elseif ($model_id=="") { //YEAR
            $model_cap = $this->getCarModelVariables($mfa, $model);
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title = " $mfa_brand $model_cap $year"; if ($year=="all") $title=" $mfa_brand $model_cap";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $range_year=""; $year_search=$year;
            $modelid_style=$style_title;
            $range_model_id=$this->getCarModelIdsListMin($year, $model_cap, $mfa_id); $range_model_id=$this->drawStyle($range_model_id);
            $check_mod_id=$this->checkCarModelIdsListMin($year, $model_cap, $mfa_id);
            if ($check_mod_id>0) { //=>MODEL_ID
                $model_id = $check_mod_id;
                $text = $this->getCarModelIdVariables($model_id);
                $model_cap = $this->getCarModelVariables($mfa, $model);
                $mfa_brand = $this->getCarManufVariables($mfa)[1];
                $title = " $mfa_brand $text";
                $range_manuf=""; $mfa_search=$mfa_brand;
                $range_model=""; $model_search=$model_cap;
                $range_year=""; $year_search=$year;
                $range_model_id=""; $modelid_search=$text;
                $modelid_style=$style_disabled;
                $type_style=$style_title;
                $range_type=$this->getCarTypeListMin($model_id, $str_id); $range_type=$this->drawStyle($range_type);
            }
        }
        elseif ($typ_id=="") { //MODEL_ID
            $text = $this->getCarModelIdVariables($model_id);
            $model_cap = $this->getCarModelVariables($mfa, $model);
            $mfa_brand = $this->getCarManufVariables($mfa)[1];
            $title = " $mfa_brand $text";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $range_year=""; $year_search=$year;
            $range_model_id=""; $modelid_search=$text;
            $type_style=$style_title;
            $range_type=$this->getCarTypeListMin($model_id, $str_id); $range_type=$this->drawStyle($range_type);
        }
        else {
            $text = $this->getCarModelIdVariables($model_id);
            $model_cap = $this->getCarModelVariables($mfa, $model);
            $mfa_brand = $this->getCarManufVariables($mfa)[1];

            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $range_year=""; $year_search=$year;
            $range_model_id=""; $modelid_search=$text;
            $fuel_name=$automan->getFuelName($fuel_id);
            $range_type=""; $typ_search="$fuel_name $typ_id "; $type_style="";
            $modification_style=$style_title;
            $title = "$mfa_brand $text $fuel_name $typ_id";

            $range_modification=$this->getCarModificationListMin($model_id, $typ_id, $fuel_id, $str_id); $range_modification=$this->drawStyle($range_modification);
        }

        $form=str_replace("{cars_title}",$title,$form);
        $form=str_replace("{str_text_select}",$str_text,$form);

        $form=str_replace("{mfa_select}",$mfa,$form);
        $form=str_replace("{model_select}",$model,$form);
        $form=str_replace("{year_select}",$year,$form);
        $form=str_replace("{modelid_select}",$model_id,$form);
        $form=str_replace("{typ_id_select}",$typ_id,$form);
        $form=str_replace("{fuel_id_select}",$fuel_id,$form);

        $form=str_replace("{range_manuf}",$range_manuf,$form);
        $form=str_replace("{range_model}",$range_model,$form);
        $form=str_replace("{range_year}",$range_year,$form);
        $form=str_replace("{range_model_id}",$range_model_id,$form);
        $form=str_replace("{range_types}",$range_type,$form);
        $form=str_replace("{range_modification}",$range_modification,$form);

        $form=str_replace("{mfa_search}",$mfa_search,$form);
        $form=str_replace("{model_search}",$model_search,$form);
        $form=str_replace("{year_search}",$year=="all" ? "{all_years}" : $year_search,$form);
        $form=str_replace("{modelid_search}",$modelid_search,$form);
        $form=str_replace("{typ_search}",$typ_search,$form);
        $form=str_replace("{modification_search}",$modification_search,$form);

        $form=str_replace("{mfa_style}",$mfa_style,$form);
        $form=str_replace("{model_style}",$model_style,$form);
        $form=str_replace("{year_style}",$year_style,$form);
        $form=str_replace("{modelid_style}",$modelid_style,$form);
        $form=str_replace("{type_style}",$type_style,$form);
        $form=str_replace("{modification_style}",$modification_style,$form);

        $form=$this->replaceLang($form);
        return $form;
    }

    /*================================================================================================================*/

    function showCarsSelected($mfa="", $model="", $year="", $model_id="", $typ_id="") {

        $form=$this->getHtmlForm("cars_form_min");
        $style_title="car_form-selected"; $style_disabled="car_form-disabled";
        $range_model=""; $range_year=""; $range_model_id=""; $range_type=""; $range_modification="";
        $mfa_style=""; $model_style=""; $year_style=""; $modelid_style=""; $type_style=""; $modification_style="";
        $mfa_search="{auto_cap}"; $model_search="{model_cap}"; $year_search="{year_cap}"; $modelid_search="{model_number}"; $typ_search="{engine_cap}"; $modification_search="{modification_cap}";

        if ($mfa=="") { //AUTO
            $title="{auto_search}";
            $mfa_style=$style_title;
            $range_manuf=$this->getCarManufListMin(1); $range_manuf=$this->drawStyle($range_manuf);
        }
        elseif ($model=="") { //MANUFACTURE
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title="$mfa_brand";
            $translit=$this->getCarManufTranslit($mfa_id, "");
            if ($translit!="") $title.=" $translit";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $model_style=$style_title;
            $range_model=$this->getCarModelsListMin($mfa_id, 1); $range_model=$this->drawStyle($range_model);
        }
        elseif ($year=="") { //MODEL
            $model_cap = $this->getCarModelVariables($mfa, $model);
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title=" $mfa_brand $model_cap";
            $translit=$this->getCarManufTranslit($mfa_id, $model_cap);
            if ($translit!="") $title.=" $translit";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $year_style=$style_title;
            $range_year=$this->getCarYearListMin($model_cap, $mfa_id, 1); $range_year=$this->drawStyle($range_year);
        }
        elseif ($model_id=="") { //YEAR
            $model_cap = $this->getCarModelVariables($mfa, $model);
            list($mfa_id, $mfa_brand) = $this->getCarManufVariables($mfa);
            $title=" $mfa_brand $model_cap $year";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $range_year=""; $year_search=$year;
            $modelid_style=$style_title;
            $range_model_id=$this->getCarModelIdsListMin($year, $model_cap, $mfa_id, 1); $range_model_id=$this->drawStyle($range_model_id);
            $check_mod_id=$this->checkCarModelIdsListMin($year, $model_cap, $mfa_id);
            if ($check_mod_id>0) { //MODEL_ID
                $model_id=$check_mod_id;
                $text = $this->getCarModelIdVariables($model_id);
                $model_cap = $this->getCarModelVariables($mfa, $model);
                $mfa_brand = $this->getCarManufVariables($mfa)[1];
                $title=" $mfa_brand $text";
                $range_manuf=""; $mfa_search=$mfa_brand;
                $range_model=""; $model_search=$model_cap;
                $range_year=""; $year_search=$year;
                $range_model_id=""; $modelid_search=$text;
                $modelid_style=$style_disabled;
                $type_style=$style_title;
                $range_type=$this->getCarTypeListMin($model_id, "", 1); $range_type=$this->drawStyle($range_type);
            }
        }
        elseif ($typ_id=="") { //MODEL_ID
            $text = $this->getCarModelIdVariables($model_id);
            $model_cap = $this->getCarModelVariables($mfa, $model);
            $mfa_brand = $this->getCarManufVariables($mfa)[1];
            $title=" $mfa_brand $text";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $range_year=""; $year_search=$year;
            $range_model_id=""; $modelid_search=$text;
            $type_style=$style_title;
            $range_type=$this->getCarTypeListMin($model_id, "", 1); $range_type=$this->drawStyle($range_type);
        }
        else { //TYPE_ID
            $text = $this->getCarModelIdVariables($model_id);
            $model_cap = $this->getCarModelVariables($mfa, $model);
            $mfa_brand = $this->getCarManufVariables($mfa)[1];
            $typ_text = $this->getTypesInfo($typ_id);
            $title="$typ_text";
            $range_manuf=""; $mfa_search=$mfa_brand;
            $range_model=""; $model_search=$model_cap;
            $range_year=""; $year_search=$year;
            $range_model_id=""; $modelid_search=$text;
            $range_type=""; $typ_search=$typ_text;
            $type_style="";
        }

        $form=str_replace("{cars_title}",$title,$form);

        $form=str_replace("{mfa_select}",$mfa,$form);
        $form=str_replace("{model_select}",$model,$form);
        $form=str_replace("{year_select}",$year,$form);
        $form=str_replace("{modelid_select}",$model_id,$form);
        $form=str_replace("{typ_id_select}",$typ_id,$form);

        $form=str_replace("{range_manuf}",$range_manuf,$form);
        $form=str_replace("{range_model}",$range_model,$form);
        $form=str_replace("{range_year}",$range_year,$form);
        $form=str_replace("{range_model_id}",$range_model_id,$form);
        $form=str_replace("{range_types}",$range_type,$form);
        $form=str_replace("{range_modification}",$range_modification,$form);

        $form=str_replace("{mfa_search}",$mfa_search,$form);
        $form=str_replace("{model_search}",$model_search,$form);
        $form=str_replace("{year_search}",$year=="all" ? "{all_years}" : $year_search,$form);
        $form=str_replace("{modelid_search}",$modelid_search,$form);
        $form=str_replace("{typ_search}",$typ_search,$form);
        $form=str_replace("{modification_search}",$modification_search,$form);

        $form=str_replace("{mfa_style}",$mfa_style,$form);
        $form=str_replace("{model_style}",$model_style,$form);
        $form=str_replace("{year_style}",$year_style,$form);
        $form=str_replace("{modelid_style}",$modelid_style,$form);
        $form=str_replace("{type_style}",$type_style,$form);
        $form=str_replace("{modification_style}",$modification_style,$form);

        $form=$this->replaceLang($form);
        return $form;
    }

    /*================================================================================================================*/

    function getCarManufListMin($type=0) { $db = DbSingleton::getTokoDb();
        $first=$second="";
        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 ORDER BY `MFA_BRAND`;"); $n=$db->num_rows($r);
        $list="<ul class=\"t_mfa\">";
        for ($i=1;$i<=$n;$i++) {
            $name=$db->result($r,$i-1,"MFA_BRAND");
            $id=$db->result($r,$i-1,"MFA_ID");
            $mfa_search=$db->result($r,$i-1,"MFA_BRAND_LINK");
            if ($first!=substr($name,0,1) && $second!=substr($name,0,1)) {
                $first = substr($name,0,1);
                $second = substr($name,0,1);
                $main_class = "class=\"search__cat-auto\"";
            } else {
                $first="";$main_class="";
                $second=substr($name,0,1);
            }
            $type==0 ? $onclick="showCarsSelectMin(2,'$mfa_search');" : $onclick="showCarsSelected(2,'$mfa_search');";
            $list.="
            <a href=\"#\" onclick=\"$onclick\">
                <span class=\"searchtab_model\">$first</span>
                <li $main_class>
                    <span id=\"auto-$id\" class=\"auto-list\">$name</span>
                </li>
            </a>";
        }
        $list.="</ul>";
        return $list;
    }

    function getCarModelsListMin($mfa_id, $type=0) { $db = DbSingleton::getTokoDb();
        $list=$first=$second="";
        $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;"); $n=$db->num_rows($r);
        if ($n>0) {
            $list="<ul class=\"t_model\">"; $list=$this->replaceLang($list);
            for ($i=1;$i<=$n;$i++){
                $model=$db->result($r,$i-1,"Model");
                $model_search=$db->result($r,$i-1,"Model_Link");
                if ($first!=substr($model,0,1) && $second!=substr($model,0,1)) {$first=substr($model,0,1); $second = substr($model,0,1); $main_class="class=\"search__cat-auto\"";}
                else {$first=""; $second=substr($model,0,1); $main_class="";}
                $type==0 ? $onclick="showCarsSelectMin(3,'$model_search');" : $onclick="showCarsSelected(3,'$model_search');";
                $list.="
                <a href=\"#\" onclick=\"$onclick\">
                    <span class=\"searchtab_model\">$first</span>
                    <li $main_class>
                        <span id=\"model-$model\" class=\"model-list\">$model</span>
                    </li>
                </a>";
            }
            $list.="</ul>";
        }
        return $list;
    }

    function getCarYearListMin($model, $mfa_id, $type=0) { $db = DbSingleton::getTokoDb();
        $min_date_start=1947; $max_date_end=2019;
        $r=$db->query("SELECT MIN(`MOD_PCON_START`) as min_year, 
            CASE WHEN MIN(`MOD_PCON_END`)=0 THEN 0 ELSE MAX(`MOD_PCON_END`) END as max_year
        FROM `T_models` WHERE `Model`='$model' AND `MOD_MFA_ID`='$mfa_id';");
        $date_start = $db->result($r,0,"min_year");
        $date_start = substr($date_start, 0, -2)."";
        $date_end = $db->result($r,0,"max_year");
        if ($date_end!=0) $date_end = substr($date_end, 0, -2)."";
        if ($date_end==0) $date_end=$max_date_end;
        if ($date_start=="" || $date_start==0) $date_start=$min_date_start;

        $type==0 ? $onclick1="showCarsSelectMin(4,'all');" : $onclick1="showCarsSelected(4,'all');";
        $list="
         <div style='margin:10px;'>
             <a href=\"#\" onclick=\"$onclick1\">
                <span id=\"year-all\" class=\"year-list btn btn-secondary\" style=\"width: 150px;\">{all_years}</span>
            </a>
        </div>";
        $list.="<div class=\"t_year\">";

        for ($i=$date_end;$i>=$date_start;$i--) {
            if (($i+1)%10==0 || $i==$date_end) {
                $mod = substr($i, 0, -1)."0 - e";
                $list.="<ul class=\"list-inline\"><li class=\"year-title\">".$mod."</li>";
            }
            $type==0 ? $onclick="showCarsSelectMin(4,'$i');" : $onclick="showCarsSelected(4,'$i');";

            $list.="<a href=\"#\" onclick=\"$onclick\">
                <li><span id=\"year-$i\" class=\"year-list\">$i</span></li>
            </a>";
            if (($i+1)%10==1) {
                $list.="</ul>";
            }
        }
        $list.="</div>";

        return $list;
    }

    function checkCarModelIdsListMin($year, $mod_id, $mfa_id) { $db = DbSingleton::getTokoDb();
        if ($year=="all") {
            $where="";
        } else {
            $where="AND 
                ((`MOD_PCON_END`>=".$year."00 AND `MOD_PCON_END`<=".$year."12)
                OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`>=".$year."00)
                OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`=0))";
        }
        $r=$db->query("SELECT * FROM `T_models` WHERE `Model`='$mod_id' AND `MOD_MFA_ID`='$mfa_id' $where;"); $n=$db->num_rows($r);
        if ($n==1) {
            $mod_id=$db->result($r,0,"MOD_ID");
        } else {
            $mod_id=0;
        }
        return $mod_id;
    }

    function getCarModelIdsListMin($year, $mod_id, $mfa_id, $type=0) { $db = DbSingleton::getTokoDb();
        if ($year=="all") {
            $where="";
        } else {
            $where="AND 
                ((`MOD_PCON_END`>=".$year."00 AND `MOD_PCON_END`<=".$year."12)
                OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`>=".$year."00)
                OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`=0))";
        }
        $r=$db->query("SELECT * FROM `T_models` WHERE `Model`='$mod_id' AND `MOD_MFA_ID`='$mfa_id' $where ORDER BY `MOD_PCON_START`;"); $n=$db->num_rows($r);
        $list="<div class=\"t_model_id\">";
        for ($i=1;$i<=$n;$i++) {
            $model_id=$db->result($r,$i-1,"MOD_ID");
            $tex_text=$db->result($r,$i-1,"TEX_TEXT");
            $image=$db->result($r,$i-1,"Car_pict");
            $path="https://toko.ua/uploads/images/models/$image";
            $d_start=$db->result($r,$i-1,"MOD_PCON_START"); $d_start=substr($d_start,0,4);
            $d_end=$db->result($r,$i-1,"MOD_PCON_END"); $d_end=substr($d_end,0,4); if ($d_end==0) $d_end="{cur_time}";
            $type==0 ? $onclick="showCarsSelectMin(5,'$model_id');" : $onclick="showCarsSelected(5,'$model_id');";
            list($body_name, $body_path) = $this->getBodyCarImage($model_id);
            $list.="<a href=\"#\" onclick=\"$onclick\"><div class='row'> 
                <div class='col-2 col-lg-1 body-car'><img src='$body_path' alt='$body_name' title='$body_name'></div>
                <div class='col-4 col-lg-2 image-car'><img src='$path' alt='$tex_text' title='$tex_text'></div>
                <div class='col-3 col-lg-6'><b>$tex_text</b></div>
                <div class='col-3 col-lg-3 text-right'>$d_start - $d_end</div>
            </div></a>";
        }
        $list.="</div>";
        return $list;
    }

    function getBodyCarImage($mod_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id' LIMIT 1;");
        $body_id = $db->result($r, 0, "BODY_ID");
        $r=$db->query("SELECT * FROM `T_types_body_car` WHERE `BODY_ID`='$body_id' AND `LANG_ID`=16 LIMIT 1;");
        $image = $db->result($r, 0, "LOGO");
        $name = $db->result($r, 0, "TYPE_BODY");
        $path="https://toko.ua/uploads/images/body-types/$image";
        return array($name, $path);
    }

    function getCarTypeListMin($mod_id, $str_id="", $type=0) { $db = DbSingleton::getTokoDb();
        $automan=new AutoClass;
        $str_link = $automan->getStrNewLink($str_id);
        $list="<div class=\"t_modification\">";
        $r=$db->query("SELECT COUNT(`TYP_ID`) as count_types, `TYP_ID`, `VOLUME_CM`, `FUEL_ID`, `TYP_KW_FROM`, `TYP_HP_FROM` FROM `T_types` 
        WHERE `TYP_MOD_ID`='$mod_id' GROUP BY `VOLUME_CM`, `FUEL_ID` ORDER BY `VOLUME_CM`, `FUEL_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $count_types = $db->result($r, $i-1, "count_types");
            $typ_id = $db->result($r, $i-1, "TYP_ID");
            $typ_text = $db->result($r, $i-1, "VOLUME_CM");
            $fuel_id = $db->result($r, $i-1, "FUEL_ID"); $fuel_name=$automan->getFuelName($fuel_id);
            $type==0 ?
                $onclick="setCookie('auto_typ_id','$typ_id'); location.href='https://toko.ua/catalog/$str_link/';" :
                $onclick="setCookie('auto_typ_id','$typ_id'); location.href='https://toko.ua/catalog/';";
            if ($count_types<=1) {
                $list.="<div><a href=\"#\" onclick=\"$onclick\">
                    <b>$typ_text $fuel_name</b>
                </a></div>";
            } else {
                $onclick="showCarsSelectMin(6,'$typ_text','$fuel_id');";
                $list.="<div><a href=\"#\" onclick=\"$onclick\">
                    <b>$typ_text $fuel_name</b>
                </a></div>";
            }
        }
        $list.="</div>";
        return $list;
    }

    function getCarModificationListMin($mod_id, $typ_id, $fuel_id, $str_id="", $type=0) { $db = DbSingleton::getTokoDb();
        $automan=new AutoClass;
        $str_link = $automan->getStrNewLink($str_id);
        $list="<div class='t_modification'>";
        $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id' AND `VOLUME_CM`='$typ_id' AND `FUEL_ID`='$fuel_id' AND `ACTIVE`=1 ORDER BY `TYP_HP_FROM`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $typ_id = $db->result($r, $i-1, "TYP_ID");
            $typ_text = $db->result($r, $i-1, "TYP_TEXT");
            $kw_from = $db->result($r,$i-1,"TYP_KW_FROM");
            $hp_from = $db->result($r,$i-1,"TYP_HP_FROM");
            $d_start=$db->result($r,$i-1,"TYP_PCON_START");
            if ($d_start==0) {$d_start="";} if (strlen($d_start)==6) {$d_start=substr($d_start,0,4).".".substr($d_start,4,2);}
            $d_end=$db->result($r,$i-1,"TYP_PCON_END");
            if ($d_end==0) {$d_end="{cur_time_min}";} if (strlen($d_end)==6) {$d_end=substr($d_end,0,4).".".substr($d_end,4,2);}
            $eng_cod = $db->result($r,$i-1,"ENG_Cod");
            $type==0 ?
                $onclick="setCookie('auto_typ_id','$typ_id'); location.href='https://toko.ua/catalog/$str_link/';" :
                $onclick="setCookie('auto_typ_id','$typ_id'); location.href='https://toko.ua/catalog/';";
            $list.="<div><a href=\"#\" onclick=\"$onclick\">
                <b>$typ_text</b> 
                <table style='font-size: 11px; width: 100%;'>
                    <tr><td>{date_release}:</td><td class='text-right'>$d_start - $d_end</td></tr>
                    <tr><td>{engine_model}:</td><td class='text-right'>$eng_cod</td></tr>
                    <tr><td>{power_cap}:</td><td class='text-right'>$hp_from {horse_power_cap}, $kw_from {kilo_wat_cap}</td></tr>
                </table>
            </a></div>";
        }
        $list.="</div>";
        return $list;
    }

    function getCarsSearch() {
        $form=$this->getHtmlForm("cars/cars");
        $form=str_replace("{cars_manufactures}", $this->getCarsSearchContent("")[0], $form);
//        $form=str_replace("{cars_manufactures}", $this->getCarsSearchContent("manuf", "648")[0], $form);
//        $form=str_replace("{cars_manufactures}", $this->getCarsSearchContent("model", "648-Sportage")[0], $form);
//        $form=str_replace("{cars_manufactures}", $this->getCarsSearchContent("years", "648-Sportage-2015")[0], $form);
//        $form=str_replace("{cars_manufactures}", $this->getCarsSearchContent("bodyc", "8751")[0], $form);
//        $form=str_replace("{cars_manufactures}", $this->getCarsSearchContent("engin", "8751-2.0-15")[0], $form);
        $form=$this->replaceLang($form);
        return $form;
    }

    function getCarsSearchContent($type="", $value="") { $db = DbSingleton::getTokoDb();
        $automan=new AutoClass;
        $list = ""; $title=""; $n=0;
        $nav=""; $tab="";

        // MANUFACTURE
        if ($type=="") {
            $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 ORDER BY `MFA_BRAND`;"); $n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $mfa_id = $db->result($r, $i - 1, "MFA_ID");
                $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
                $list.="<div data-url=\"manuf/$mfa_id\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$mfa_brand</div>";
            }
            $title = "{auto_cap}";
            $nav="{auto_cap}"; $tab="cars-tab1";
        }

        // MODEL
        if ($type=="manuf") {
            $mfa_id = $value;
            $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;"); $n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $model = $db->result($r, $i - 1, "Model");
                $model_cap = $mfa_id."_".$model;
                $list.="<div data-url=\"model/$model_cap\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$model</div>";
            }
            $title = $automan->getMfaBrand($mfa_id);
            $nav="manuf"; $tab="cars-tab2";
        }

        // YEAR
        if ($type=="model") {
            list($mfa_id, $model) = explode("_", $value);
            $min_date_start=1947; $max_date_end=2019; $n=1;
            $r=$db->query("SELECT MIN(`MOD_PCON_START`) as min_year, 
                CASE WHEN MIN(`MOD_PCON_END`)=0 THEN 0 ELSE MAX(`MOD_PCON_END`) END as max_year
            FROM `T_models` WHERE `Model`='$model' AND `MOD_MFA_ID`='$mfa_id';");
            $date_start = $db->result($r,0,"min_year");
            $date_start = substr($date_start, 0, -2)."";
            $date_end = $db->result($r,0,"max_year");
            if ($date_end!=0) $date_end = substr($date_end, 0, -2).""; else $date_end = $max_date_end;
            if ($date_start=="" || $date_start==0) $date_start = $min_date_start;
            for ($i=$date_end; $i>=$date_start; $i--) {
                $year = $i;
                $year_cap = $mfa_id."_".$model."_".$year;
                $list.="<div data-url=\"years/$year_cap\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$year</div>";
            }
            $title = $model;
            $nav="model"; $tab="cars-tab3";
        }

        // BODY (MODEL_ID)
        if ($type=="years") {
            list($mfa_id, $model, $year) = explode("_", $value);
            $where = "AND 
                ((`MOD_PCON_END`>=".$year."00 AND `MOD_PCON_END`<=".$year."12)
                OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`>=".$year."00)
                OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`=0))";
            $r=$db->query("SELECT * FROM `T_models` WHERE `Model`='$model' AND `MOD_MFA_ID`='$mfa_id' $where;"); $n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $mod_id = $db->result($r, $i - 1, "MOD_ID");
                $tex_text = $db->result($r, $i - 1, "TEX_TEXT");
                $image=$db->result($r, $i - 1, "Car_pict");
                $img_path="https://toko.ua/uploads/images/models/$image";
                list($body_name, $body_path) = $this->getBodyCarImage($mod_id);
                $d_start=$db->result($r,$i-1,"MOD_PCON_START"); $d_start=substr($d_start,0,4);
                $d_end=$db->result($r,$i-1,"MOD_PCON_END"); $d_end=substr($d_end,0,4); if ($d_end==0) $d_end="{cur_time}";

                $list.="<div data-url=\"bodyc/$mod_id\" class=\"cars-tab__block-item cars-tab__block-item-body\" onclick=\"toggleCarsTab(this)\">
                    <div class='bodyc'>
                        <div class='bodyc-head'>
                            <div class='bodyc__title'>$tex_text</div>
                            <div class='bodyc__type'><img src='$body_path' alt='$body_name' title='$body_name'></div></div>
                        </div>    
                        <div class='bodyc-content'>
                            <div class='bodyc__descr'>
                                {model_number_type}: $body_name
                                <br>
                                {year_issue}: $d_start - $d_end
                            </div>
                            <div class='bodyc__image'>
                                <img src='$img_path' alt='$tex_text' title='$tex_text'>
                            </div>
                        </div>
                    </div>
                </div>";
            }
            $title = $year;
            $nav="years"; $tab="cars-tab4";
        }

        // ENGINE
        if ($type=="bodyc") {
            $mod_id = $value;
            $r=$db->query("SELECT COUNT(`TYP_ID`) as count_types, `TYP_ID`, `VOLUME_CM`, `FUEL_ID`, `TYP_KW_FROM`, `TYP_HP_FROM` FROM `T_types` 
            WHERE `TYP_MOD_ID`='$mod_id' GROUP BY `VOLUME_CM`, `FUEL_ID` ORDER BY `VOLUME_CM`, `FUEL_ID`;"); $n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $volume_cm = $db->result($r, $i-1, "VOLUME_CM");
                $fuel_id = $db->result($r, $i-1, "FUEL_ID"); $fuel_text=$this->getFuelName($fuel_id);
                $fuel_cap = $mod_id."_".$volume_cm."_".$fuel_id;
                $list.="<div data-url=\"engin/$fuel_cap\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$volume_cm $fuel_text</div>";
            }
            $title = $this->getModIdText($mod_id);
            $nav="bodyc"; $tab="cars-tab5";
        }

        // MODIFICATION
        if ($type=="engin") {
            list($mod_id, $volume_cm, $fuel_id) = explode("_", $value);
            $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id' AND `VOLUME_CM`='$volume_cm' AND `FUEL_ID`='$fuel_id' AND `ACTIVE`=1 ORDER BY `TYP_HP_FROM`;"); $n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $typ_id = $db->result($r, $i-1, "TYP_ID");
                $typ_text = $db->result($r, $i-1, "TYP_TEXT");
                $kw_from = $db->result($r,$i-1,"TYP_KW_FROM");
                $hp_from = $db->result($r,$i-1,"TYP_HP_FROM");
                $d_start=$db->result($r,$i-1,"TYP_PCON_START"); if ($d_start==0) {$d_start="";} if (strlen($d_start)==6) {$d_start=substr($d_start,0,4).".".substr($d_start,4,2);}
                $d_end=$db->result($r,$i-1,"TYP_PCON_END"); if ($d_end==0) {$d_end="{cur_time_min}";} if (strlen($d_end)==6) {$d_end=substr($d_end,0,4).".".substr($d_end,4,2);}
                $eng_cod = $db->result($r,$i-1,"ENG_Cod");
                $onclick="setCookie('auto_typ_id','$typ_id'); location.href='https://toko.ua/catalog/';";
                $list.="<div class=\"cars-tab__block-item cars-tab__block-item-modif\"><a href=\"#\" onclick=\"$onclick\">
                <b>$typ_text</b> 
                    <table>
                        <tr><td>{date_release}:</td><td class='text-right'>$d_start - $d_end</td></tr>
                        <tr><td>{engine_model}:</td><td class='text-right'>$eng_cod</td></tr>
                        <tr><td>{power_cap}:</td><td class='text-right'>$hp_from {horse_power_cap}, $kw_from {kilo_wat_cap}</td></tr>
                    </table>
                </a></div>";
            }
            $title = $volume_cm." ".$this->getFuelName($fuel_id);
            $nav="engin"; $tab="cars-tab6";
        }

        // TYP SELECTED
        if ($type=="modif") {
            $typ_id = $value;
            $title = $this->getTypIdText($typ_id);
            $nav="modif"; $tab="cars-tab6";
        }

        if ($n==0) { $list="<div style='margin: 30px auto;'>{nothing_found}</div>"; }

        $list=$this->replaceLang($list);

        return array($list, $title, $nav, $tab);
    }

    function getModIdText($mod_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        $tex_text=$db->result($r, 0, "TEX_TEXT");
        return $tex_text;
    }

    function getTypIdText($typ_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' LIMIT 1;");
        $tex_text=$db->result($r, 0, "TYP_TEXT");
        return $tex_text;
    }

    function clearCarsBlock($sel_tab, $cur_tab) {
        $disabled="cars-nav__item-disabled";
        $hidden="cars-nav__item-hidden";

        if ($sel_tab == ($cur_tab + 1)) {
            $disabled="";
            $hidden="";
        }
        switch ($sel_tab) {
            case "1": {$classes=""; $text="{cars_manufacturer}"; break;}
            case "2": {$classes="$disabled"; $text="{cars_model}"; break;}
            case "3": {$classes="$disabled"; $text="{cars_year}"; break;}
            case "4": {$classes="$disabled $hidden"; $text="{cars_body}"; break;}
            case "5": {$classes="$disabled $hidden"; $text="{cars_engine}"; break;}
            case "6": {$classes="$disabled $hidden"; $text="{cars_modification}"; break;}
            default:  {$classes=""; $text="{cars_manufacturer}"; break;}
        }
        $text=$this->replaceLang($text);
        return array($classes, $text);
    }

    function getCarsGarage() {
        $automan=new AutoClass;
        $auto_typ_id = $this->getCookieAuto();
        $form=$this->getHtmlForm("garage/garage_typ_block");
        $form=str_replace("{typ_id}",$auto_typ_id,$form);
        list($manufacture,$model,$model_id)=$automan->getCarInfo($auto_typ_id);
        list($manufacture_cap,,$model_id_cap,)=$automan->getAutoDescr($manufacture, $model, $model_id, $auto_typ_id);
        list(,,$models_img)=$automan->getAutoIMG($manufacture,$model,$model_id);
        $form=str_replace("{manufacture_cap}",$manufacture_cap,$form);
        $form=str_replace("{model_id_cap}",$model_id_cap,$form);
        $form=str_replace("{typ_text}",$automan->getGroupInfo($auto_typ_id),$form);
        $form=str_replace("{models_img}",$models_img,$form);
        if ($auto_typ_id!="") {
            if ($automan->checkUserGarage($auto_typ_id)) {
                $button="btn-img-disabled";
            } else {
                $button="";
            }
        } else {
            $button="";
        }
        $form=str_replace("{garage_button}",$button,$form);
        $form=$this->replaceLang($form);
        return $form;
    }

}