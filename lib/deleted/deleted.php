<?php

/*=====BULB FORM======*/

//    function showLightBulbForm() {
//        $form=$this->getHtmlForm("group_light_bulb");
//        $list_front=$this->getLightBulbsFront();
//        $list_back=$this->getLightBulbsBack();
//        $form=str_replace("{car_front}",$list_front,$form);
//        $form=str_replace("{car_back}",$list_back,$form);
//        return $form;
//    }

//    function getParentTemplateForm($template_id) { $db=DbSingleton::getTokoDb();
//        //$template=new TemplateClass;
//        $pattern=new PatternClass;
//        if ($template_id==1) $form=$this->showLightBulbForm(); else {
//            $list="";
//            $r=$db->query("SELECT * FROM `T2_CATALOGUES_TEMPLATES` WHERE `PARENT_ID`='$template_id' AND `STATUS`=1;"); $n=$db->num_rows($r);
//            $list.="<ul class='list-inline goods mar0' style='justify-content: space-around;'>";
    //            for ($i=1;$i<=$n;$i++) {
    //                $template_link = $db->result($r,$i-1,"TEMPLATE_LINK");
    //                $text = $db->result($r,$i-1,"TEMPLATE_NAME");
    //                $descr = $db->result($r,$i-1,"TEMPLATE_DESCR");
    //                $img = $db->result($r,$i-1,"TEMPLATE_IMG");
    //                $img=="" ? $link=$this->noPhoto : $link=$this->images."/templates/$img";
    //                $url = "https://toko.ua/pattern/$template_link/";
    //                $list.="
    //                <li class=\"goods__item\">
        //                    <a href=\"$url\" onclick=\"showLoader();\">
            //                        <img src=\"$link\" alt=\"$text\" title=\"$text\">
            //                        <h2>$text</h2>
            //                        <input type='hidden' value='$descr' title='$text'>
            //                    </a>
        //                </li>";
    //            }
    //            $list.="</ul>";
//            $form=$this->getHtmlForm("group_parent");
//            $form=str_replace("{group_title}",$template_id==0 ? "{professional_catalogs_sh}" : $pattern->getTemplateName($template_id),$form);
//            $form=str_replace("{group_content}",$list,$form);
//        }
//        return $form;
//    }

//    function getLightBulbsBack() {
//        $form="
//        <div class=\"container\">
    //            <div class=\"row\">
        //                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('8')."</div>
    //                <div class=\"col-lg-7 col-12\">".$this->getBulbItem('9','float-right')."</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('10')."</div>
//                <div class=\"col-lg-7 col-12 adnone\">&nbsp;</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-7 col-12\">".$this->getBulbItem('11')."</div>
//                <div class=\"col-lg-5 col-12 adnone\">&nbsp;</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('12')."</div>
//                <div class=\"col-lg-7 col-12 adnone\">&nbsp;</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-4 col-12\">".$this->getBulbItem('13')."</div>
//                <div class=\"col-lg-4 col-12\">".$this->getBulbItem('14','float-right')."</div>
//                <div class=\"col-lg-4 col-12\">".$this->getBulbItem('15','float-right')."</div>
//            </div>
//        </div>";
//        return $form;
//    }
//
//    function getLightBulbsFront() {
//        $form="
//        <div class=\"container\">
    //            <div class=\"row\">
        //                <div class=\"col-lg-11 col-12\">".$this->getBulbItem('1')."</div>
    //                <div class=\"col-lg-1 col-12 adnone\">&nbsp;</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('2')."</div>
//                <div class=\"col-lg-2 col-12 adnone\">&nbsp;</div>
//                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('3','float-right')."</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-7 col-12 adnone\">&nbsp;</div>
//                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('4','float-right')."</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-7 col-12 adnone\">&nbsp;</div>
//                <div class=\"col-lg-5 col-12\">".$this->getBulbItem('7','float-right')."</div>
//            </div>
//            <div class=\"row\">
    //                <div class=\"col-lg-4 col-12\">".$this->getBulbItem('5')."</div>
//                <div class=\"col-lg-8 col-12\">".$this->getBulbItem('6')."</div>
//            </div>
//        </div>";
//        return $form;
//    }
//
//    function getBulbItem($category_id, $align="float-left") { $db=DbSingleton::getTokoDb();
//        $pattern=new PatternClass;
//        $list="";
//        $r=$db->query("SELECT * FROM `T2_GROUP_LAMP_CATEGORY` WHERE `id`='$category_id';");
//        $title=$db->result($r,0,"name");
//        $image=$db->result($r,0,"image");
//        $photo="<img src=\"$this->images/lamps/category/$image\" alt=\"$title\" title=\"$title\">";
//        $template_id=1;
//        $template_link=$pattern->getTemplateLink($template_id);
//
//        $r=$db->query("SELECT * FROM `T2_GROUP_LAMP` WHERE `category_id`='$category_id';"); $bulb_count=$db->num_rows($r);
//        for ($i=1; $i<=$bulb_count; $i++) {
//            $bulb_name=$db->result($r,$i-1,"name");
//            $bulb_image=$db->result($r,$i-1,"image");
//            $bulb_param_id=$db->result($r,$i-1,"param_id");
//            $bulb_value_id=$db->result($r,$i-1,"value_id");
//            $bulb_param_link=$pattern->getCatalogueParamLink($bulb_param_id);
//            $bulb_value_link=$pattern->getCatalogueValueLink($bulb_value_id);
//            if ($bulb_param_id>0 && $bulb_value_id>0) $link="href=\"https://toko.ua/catalogue/filter/$template_link/$bulb_param_link/$bulb_value_link\""; else $link="href=\"#\"";
//            $list.="<ul class=\"list-inline\">
    //                <li>
        //                    <a $link>
            //                        <span class=\"b-image\" style=\"background: url(/images/lamps/bulbs/$bulb_image) no-repeat;\">&nbsp</span>
            //                        <span class=\"b-name\">$bulb_name</span>
            //                    </a>
        //                </li>
    //            </ul>";
//        }
//
//        $form="<ul class=\"group-bulb $align\">
//            <li class=\"group-bulb__item\">
    //                <div class=\"bulb-title\">
        //                    <span>$title</span>
        //                </div>
    //                <div class=\"bulb-main\">
        //                    <div class=\"bulb-photo\">
            //                        $photo
            //                    </div>
        //                    <div class=\"bulb-list\">
            //                        $list
            //                    </div>
        //                </div>
    //            </li>
//        </ul>";
//
//        return $form;
//    }

    function insertAutoHistory($typ_id) { $db = DbSingleton::getTokoDb();
        $cookie=$_COOKIE["session_id"]; $date=date("Y-m-d H:i:s");
        $client_id=$this->getClient(); $user=$this->getUser(); $max_history_count=10;

        if ($user==0) $where="`cookie_id`='$cookie'"; else $where="`client_id`='$client_id' AND `client_user_id`='$user'";
        $r=$db->query("SELECT COUNT(`id`) as kilk FROM `AUTO_HISTORY` WHERE $where;"); $k=$db->result($r,0,"kilk");
        if ($k>$max_history_count) {
            $r=$db->query("SELECT `id` FROM `AUTO_HISTORY` WHERE $where ORDER BY `timestamp` ASC LIMIT 1;");
            $id=$db->result($r,0,"id");
            $db->query("UPDATE `AUTO_HISTORY` SET `typ_id`='$typ_id' WHERE `id`='$id';");
        } else {
            $r=$db->query("SELECT `id` FROM `AUTO_HISTORY` WHERE $where AND `typ_id`='$typ_id';"); $n=$db->num_rows($r);
            if ($n>0)
                $db->query("UPDATE `AUTO_HISTORY` SET `timestamp`='$date' WHERE $where AND `typ_id`='$typ_id';");
            else
                $db->query("INSERT INTO `AUTO_HISTORY` (`client_id`,`client_user_id`,`cookie_id`,`typ_id`)
                VALUES ('$client_id','$user','$cookie','$typ_id');");
        }
        return true;
    }

/*=====END INFO FORM======*/

/*====HISTORY====*/

//    function findLinks($link="") {
//        if ($link=="") $link="https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
//        $link=parse_url($link);
//        $durl=substr($link["path"],1);
//        $i=0; $linka=[];
//        while($durl!=""){
//            $pos=strpos($durl,"/");
//            if ($pos) {
//                $path=substr($durl,0,$pos+1);
//                $durl=str_replace_first($path,"",$durl);
//                $cur_path=substr($path, 0, -1);
//                if ($cur_path=="ua" || $cur_path=="ru" || $cur_path=="en") {
//                    if ($cur_path=="ru") $_SESSION["lang"]=1;
//                    if ($cur_path=="ua") $_SESSION["lang"]=2;
//                    if ($cur_path=="en") $_SESSION["lang"]=3;
//                    $i=0;
//                } else {
//                    $linka[$i]=$cur_path;
//                    $i++;
//                }
//            } else break;
//        }
//        return $linka;
//    }

//    function showAutoHistory() { $db=DbSingleton::getTokoDb();
//        $automan=new AutoClass(); $cookie=$_COOKIE["session_id"]; $list="";
//        $user=$this->getUser(); $client_id=$this->getClient();
//        if ($user==0) $where="cookie_id='$cookie'"; else $where="client_id='$client_id' AND client_user_id='$user'";
//        $r=$db->query("SELECT `typ_id` FROM `AUTO_HISTORY`
//        WHERE $where GROUP BY `typ_id` ORDER BY `timestamp` DESC LIMIT 10;");$n=$db->num_rows($r);
//        if ($n>0) {
//            for ($i=1;$i<=$n;$i++){
//                $typ_id=$db->result($r,$i-1,"typ_id");
//                list($manufacture,$model,$model_id)=$automan->getCarInfo($typ_id);
//                list($mf,$md,$md2,$gr)=$automan->getAutoDescr($manufacture,$model,$model_id,$typ_id);
//                $auto_title="$mf / $md / $md2 / $gr";
//                $list.="<p><a onclick='showLoader(); setCookie(\"auto_typ_id\",$typ_id); location.href=\"/details\";'>$auto_title</a></p>";
//            }
//        } else $list="{empty_history}";
//        $list=$this->replaceLang($list);
//        return $list;
//    }

//    function showAutoList($manufacture,$model,$model_id,$group) { $dbt=DbSingleton::getTokoDb();
//        $automan=new AutoClass;
//        $model=str_replace("%20"," ",$model);
//
//        $r=$dbt->query("SELECT * FROM `T_manufacturers` ORDER BY `MFA_BRAND`;"); $n=$dbt->num_rows($r);
//        $list_auto="<option value=\"0\">$this->mess1</option>";
//        for ($i=1;$i<=$n;$i++){
//            $auto=$dbt->result($r,$i-1,"MFA_BRAND");
//            $mfa_id=$dbt->result($r,$i-1,"MFA_ID");
//            if ($mfa_id==$manufacture) $selected="selected"; else $selected="";
//            $list_auto.="<option value=\"$mfa_id\" $selected>$auto</option>";
//        }
//
//        $r=$dbt->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$manufacture' GROUP BY `Model` ORDER BY `Model`;"); $n=$dbt->num_rows($r);
//        $list_model="<option value=\"0\">$this->mess1</option>";
//        for ($i=1;$i<=$n;$i++){
//            $model_text=$dbt->result($r,$i-1,"Model");
//            if ($model_text==$model) $selected="selected"; else $selected="";
//            $list_model.="<option value=\"$model_text\" $selected>$model_text</option>";
//        }
//
//        $r=$dbt->query("SELECT * FROM `T_models` WHERE `Model`='$model' GROUP BY `TEX_TEXT` ORDER BY `TEX_TEXT`;"); $n=$dbt->num_rows($r);
//        $list_modelid="<option value=\"0\">$this->mess1</option>";
//        for ($i=1;$i<=$n;$i++){
//            $mod_id=$dbt->result($r,$i-1,"MOD_ID");
//            $model_id_text=$dbt->result($r,$i-1,"TEX_TEXT");
//            if ($mod_id==$model_id) $selected="selected"; else $selected="";
//            $list_modelid.="<option value=\"$mod_id\" $selected>$model_id_text</option>";
//        }
//
//        $r=$dbt->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$model_id' AND `ACTIVE`=1 ORDER BY `TYP_TEXT`;"); $n=$dbt->num_rows($r);
//        $list_group="<option value=\"0\">$this->mess1</option>";
//        if ($n>0) {
//            for ($i=1;$i<=$n;$i++){
//                $typ_id=$dbt->result($r,$i-1,"TYP_ID");
//                $typ_text=$dbt->result($r,$i-1,"TYP_TEXT");
//                $d_start=$dbt->result($r,$i-1,"TYP_PCON_START");
//                if ($d_start==0) {$d_start="";} if (strlen($d_start)==6) {$d_start=substr($d_start,0,4).".".substr($d_start,4,2);}
//                $d_end=$dbt->result($r,$i-1,"TYP_PCON_END");
//                if ($d_end==0) {$d_end="";} if (strlen($d_end)==6) {$d_end=substr($d_end,0,4).".".substr($d_end,4,2);}
//                $fuel=$dbt->result($r,$i-1,"FUEL_ID"); $fuel_name=$automan->getFuelName($fuel);
//                $kw_from=$dbt->result($r,$i-1,"TYP_KW_FROM");
//                $hp_from=$dbt->result($r,$i-1,"TYP_HP_FROM");
//                $ccm=$dbt->result($r,$i-1,"TYP_CCM");
//                $eng_cod=$dbt->result($r,$i-1,"ENG_Cod");
//                if ($typ_id==$group) $selected="selected"; else $selected="";
//                if ($group!="") $list_group.="<option value=\"$typ_id\" $selected>$fuel_name - $typ_text ($d_start - $d_end) - ($hp_from / $kw_from) - $ccm - $eng_cod</option>";
//            }
//        }
//        return array($list_auto,$list_model,$list_modelid,$list_group);
//    }

//    function showRegistrationForm() {
//        $menu=new MenuClass;
//        $form=$this->getHtmlForm("registration");
//        $form=str_replace("{type_form}", $menu->showTypeForm(), $form);
//        $form=str_replace("{region_form}", $menu->getRegionForm(), $form);
//        $form=str_replace("{category_options}", $this->getManualOptions("customers_categories"), $form);
//        $form=str_replace("{tpoint_options}", $this->getRegionSelect(), $form);
//        return $form;
//    }

//    function getRegionSelect() { $db=DbSingleton::getDbm();
//        $r=$db->query("SELECT * FROM `T_POINT` WHERE `status`=1 ORDER BY `full_name` ASC;"); $n=$db->num_rows($r); $options="";
//        for ($i=1;$i<=$n;$i++) {
//            $id=$db->result($r, $i-1, "id");
//            $region=$db->result($r, $i-1, "full_name");
//            $address=$db->result($r, $i-1, "address");
//            $options.="<option value=\"$id\">$region ($address)</option>";
//        }
//        return $options;
//    }

//    function showAutoInfo($auto) { $db=DbSingleton::getTokoDb();
//        $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_BRAND`='$auto' LIMIT 1;");
//        $auto=$db->result($r,0,"MFA_BRAND");
//        $form=$this->getHtmlForm("cat_auto_info");
//        $form=str_replace("{auto_info}", $auto, $form);
//        return $form;
//    }

//    function autoDetails($auto_id) { $db=DbSingleton::getTokoDb();
//        $auto_id=$this->getUrlNumber($auto_id);
//        $form=$this->getHtmlForm("cat_auto");
//        $r=$db->query("SELECT * FROM `T_manufacturers` ORDER BY `MFA_BRAND`;"); $n=$db->num_rows($r);
//        $list="<option value=\"0\">$this->mess1</option>";
//        for ($i=1;$i<=$n;$i++){
//            $auto=$db->result($r,$i-1,"MFA_BRAND");
//            $id=$db->result($r,$i-1,"MFA_ID");
//            if ($id==$auto_id) $selected="selected"; else $selected="";
//            $list.="<option value=\"$id\" $selected>$auto</option>";
//        }
//        $mf=$this->getAutoDescr($auto_id,"","","")[0];
//        $text="$mf";
//        $mf=$this->getAutoIMG($auto_id, "", "")[0];
//        $img="<img class=\"wdt100\" src=\"/thumb.php?image=manufacturers/$mf&size=90\" alt=\"Manufactures\">";
//        $form=str_replace("{select_auto}", $list, $form);
//        $form=str_replace("{select_image}", $img, $form);
//        $form=str_replace("{select_descr}", $text, $form);
//        $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`=$auto_id GROUP BY `Model` ORDER BY `Model`;"); $n=$db->num_rows($r);
//        $list2="<option value=\"0\">$this->mess1</option>";
//        for ($i=1;$i<=$n;$i++){
//            $model=$db->result($r,$i-1,"Model");
//            $list2.="<option value=\"$model\">$model</option>";
//        }
//        $form=str_replace("{select_model}", $list2, $form);
//        return $form;
//    }

//    function getAutoData() {
//        define('RDD', dirname (__FILE__));
//        $linka = findLinks();
//        $manufacture = $linka[2];
//        $model = $linka[3];
//        $modelid = $linka[4];
//        $group = $linka[5];
//        $_SESSION["manufacture"] = $manufacture;
//        $_SESSION["model"] = $model;
//        $_SESSION["modelid"] = $modelid;
//        $_SESSION["group"] = $group;
//        return array($manufacture,$model,$modelid,$group);
//    }

?>
<!--<meta http-equiv="Content-Type" content="text/html" charset="windows-1251">-->

<!--<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,800&amp;subset=latin-ext,latin" rel="stylesheet">-->
<!--<link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">-->
<!--<link rel="stylesheet" href="/fonts/Open Sans.css" >-->
<!--<link rel="stylesheet" href="/css/style.min.css">-->

<!--function getExistProducts($current_products_arts) {-->
<!--$cat = new CatalogueClass;-->
<!---->
<!--foreach ($current_products_arts as $key=>$art_id) {-->
<!--$validate_art_count=0; $max_price_art=0;-->
<!--list($suppl_array,$storage_array,$stock_array,$last) = $this->getCatalogueSearchParams($art_id);-->
<!--for ($j=1;$j<=$last;$j++) {-->
<!--$suppl_id = $suppl_array[$j];-->
<!--$storage_id = $storage_array[$j];-->
<!--$stock = $stock_array[$j];-->
<!--if ($suppl_id==0) $price = $cat->getArticlePrice($art_id); else $price = $cat->getArticleSupplPrice($art_id,$suppl_id,$storage_id);-->
<!--if ($price>0 && $stock>0) {-->
<!--if ($price>$max_price_art) $max_price_art=$price;-->
<!--$validate_art_count++;-->
<!--}-->
<!--}-->
<!--if ($validate_art_count==0) {-->
<!--unset($current_products_arts[$key]);-->
<!--}-->
<!--}-->
<!--return $current_products_arts;-->
<!--}-->

<?php

//    function getCurrentProducts($template_id, $page, $link) {
//        $cur_products = $this->initProductsList($template_id);
//        $cur_products_arr = [];
//        foreach ($cur_products as $art_id=>$values) {
//            array_push($cur_products_arr, $art_id);
//        }
//        $cur_products_arr=$this->getExistProducts($cur_products_arr);
//
//        $cur_filters = $this->getTemplateLinkParams($template_id, $link);
//        $fproducts=[]; $active_filters=0;
//
//        if (!empty($cur_filters)) {
//            $active_filters = 1;
//            $fproducts = $this->buildProductsList($cur_products, $cur_filters);
//        }
//
//        return $this->getTemplateProducts($template_id, $page, $cur_products_arr, $fproducts, $active_filters);
//    }

//  function getTemplateLinkCount($template_id, $link) {
//
//        $cur_products = $this->initProductsList($template_id);
//        $cur_products_arr = [];
//        foreach ($cur_products as $art_id=>$values) {
//            array_push($cur_products_arr, $art_id);
//        }
//
//        $cur_filters = $this->getTemplateLinkParams($template_id, $link);
//        $fproducts=[];
//
//        if (!empty($cur_filters)) {
//            $fproducts = $this->buildProductsList($cur_products, $cur_filters);
//        }
//
//        return count($fproducts);
//    }

//    function getTemplateFilters($template_id, $page=1, $link="", $template_current_products="", $template_active_products="", $template_active_filters=0) { $db = DbSingleton::getTokoDb();
//        $filters = $this->getTemplateLinkParams($template_id,$link);
//        $template_name = $this->getTemplateLink($template_id);
//        $arr=[];
//
//        $where_arts="";
//        if ($template_current_products!="") {
//            $where_arts=" AND t2c.ART_ID IN ($template_current_products)";
//        }
//
//        $r=$db->query("SELECT t2a.`BRAND_ID` FROM `T2_CATALOGUES_ARTS` t2c
//            LEFT JOIN `T2_ARTICLES` t2a ON (t2a.ART_ID=t2c.ART_ID)
//        WHERE t2c.`TEMPLATE_ID`='$template_id' $where_arts
//        GROUP BY t2a.`BRAND_ID`;"); $n=$db->num_rows($r);
//        for ($i=1; $i<=$n; $i++) {
//            $brand_id = $db->result($r, $i-1, "BRAND_ID");
//            if (empty($arr[0])) $arr[0]=[];
//            if (!in_array($brand_id, $arr[0])) array_push($arr[0], $brand_id);
//        }
//
//        $r=$db->query("SELECT t2c.`PARAM_ID`, t2c.`VALUE_ID` FROM `T2_CATALOGUES_ARTS` t2c
//        WHERE t2c.`TEMPLATE_ID`='$template_id' $where_arts
//        GROUP BY t2c.`ART_ID`, t2c.`VALUE_ID`;"); $n=$db->num_rows($r);
//        for ($i=1; $i<=$n; $i++) {
//            $param_id = $db->result($r, $i-1, "PARAM_ID");
//            $value_id = $db->result($r, $i-1, "VALUE_ID");
//            if (empty($arr[$param_id])) $arr[$param_id]=[];
//            if (!in_array($value_id, $arr[$param_id])) array_push($arr[$param_id], $value_id);
//        }
//
//        $mas=[]; $amount_max=5; $amount_values=0; $filters_list="";
//
//        foreach ($arr as $param_id=>$values) {$i=0;
//            if (empty($mas[$param_id])) $mas[$param_id]=[];
//            foreach ($values as $value) {$i++;
//                if (in_array($value,$filters[$param_id])) $checked=1; else $checked=0;
//                if (empty($mas[$param_id][$i])) $mas[$param_id][$i]=[];
//                if ($param_id==0) $value_name=$this->getBrandName($value); else $value_name=$this->getCatalogueValueName($value, $template_id);
//                $mas[$param_id][$i]=["value_id"=>$value, "value_name"=>$value_name, "checked"=>$checked];
//            }
//        }
//
//        foreach ($mas as $param_id=>$values) {
//            $vc_array_checked=[]; $vc_array_name=[];
//            foreach ($values as $key => $row) {
//                $vc_array_checked[$key] = $row["checked"];
//                $vc_array_name[$key] = $row["value_name"];
//            }
//            array_multisort($vc_array_checked, SORT_DESC, $vc_array_name, SORT_ASC, $mas[$param_id]);
//        }
//
//        $fproducts = $this->buildProductsList($this->initProductsList($template_id), $this->getTemplateLinkParams($template_id,$link));
//        $products_count = $this->getTemplateProductsCount($template_id,$fproducts);
//        $values_list="";
//
//        foreach ($mas as $param_id=>$values) {
//
//            foreach ($values as $value) {
//                $value_id=$value["value_id"];
//                $value_name=$value["value_name"];
//                $checked=$value["checked"];
//                if ($checked) {$label="<i class=\"fa fa-check-square\"></i>";$style="span-red"; $status=1;} else {$label="<i class=\"far fa-square\"></i>";$style=""; $status=0;}
//                if ($value_id>0) {
//
//                    $new_link = $this->getTemplateFilterLink($template_id, $link, $param_id, $value_id, $status);
//
//                    $products_predict_count=1;
//                    if ($checked) $postfix=""; else {
//                        $products_predict_count = $this->getTemplateLinkCount($template_id, $new_link);
//                        if ($products_predict_count>$products_count) {
//                            $products_diff_count=$products_predict_count-$products_count;
//                            $postfix="(+$products_diff_count)";
//                        } else {
//                            $postfix="($products_predict_count)";
//                        }
//                        if ($products_predict_count==$products_count) $products_predict_count=0;
//                    }
//
//                    if ($products_predict_count>0) {
//                        $amount_values++;
//                        $values_list.="<li><a class=\"pointer $style\" style=\"font-size: 1em;\" href='$this->page_link/$template_name/$new_link'>$label $value_name $postfix</a></li>";
//                    }
//
//                }
//            }
//
//            if (count($values)<=$amount_max) $style_more="height:auto;"; else $style_more="";
//            if ($param_id==0) $param_name="{brands_cap}"; else $param_name=$this->getCatalogueParamName($param_id, $template_id);
//            if (count($values)>0 && $amount_values>0) $filters_list.="<h2>$param_name</h2><ul id=\"param-$param_id\" class=\"list-inline template-list list-hide\" style=\"margin: 0; $style_more\">";
//
//            $amount_values=$amount_values-$amount_max;
//            if ($amount_values<=0) $link_more=""; else $link_more="<a class=\"pointer underline\" onclick=\"toggleListParams(this, $param_id);\"><span class=\"show\">{more_cap} $amount_values</span> <span class=\"none\">{hide_cap}</span></a>";
//            $filters_list.="$values_list</ul>$link_more";
//            $amount_values=0; $values_list="";
//        }
//
//        $filters_list=$this->replaceLang($filters_list);
//
//        $current_page = $this->getTemplateCurrentPage($products_count,$page);
//
//        $pagination = $this->getTemplatePaginationForm($products_count,$page);
//        $pagination = $this->replaceLang($pagination);
//
//        $checked_filters = $this->getTemplateFiltersChecked($template_id,$link);
//
//        if ($template_active_products=="" && $template_active_filters==1) {
//            $current_page="";
//            $pagination="";
//        }
//
//        return array($filters_list,$current_page,$pagination,$checked_filters);
//    }

function showCarsSelect($str_text="",$mfa="",$model="",$year="",$modelid="") {
    $form=$this->getHtmlForm("cars_form"); $style_title="car_form-selected";
    $range_model=""; $range_year=""; $range_model_id=""; $range_type="";
    $mfa_search="{auto_cap}"; $model_search="{model_cap}"; $year_search="{year_cap}"; $modelid_search="{model_number}";
    $mfa_style=""; $model_style=""; $year_style=""; $modelid_style=""; $type_style="";

    if ($mfa=="") { //AUTO
        $title="{auto_search}";
        $mfa_style=$style_title;
        $range_manuf=$this->getCarManufList(); $range_manuf=$this->drawStyle($range_manuf);
    }
    elseif ($model=="") { //MANUFACTURE
        list($mfa_id,$mfa_brand) = $this->getCarManufVariables($mfa);
        $title="$mfa_brand";

        $range_manuf=""; $mfa_search=$mfa_brand;
        $model_style=$style_title;
        $range_model=$this->getCarModelsList($mfa_id); $range_model=$this->drawStyle($range_model);
    }
    elseif ($year=="") { //MODEL
        $model_cap = $this->getCarModelVariables($model);
        $mfa_brand = $this->getCarManufVariables($mfa)[1];
        $title=" $mfa_brand $model_cap";

        $range_manuf=""; $mfa_search=$mfa_brand;
        $range_model=""; $model_search=$model_cap;
        $year_style=$style_title;
        $range_year=$this->getCarYearList($model_cap); $range_year=$this->drawStyle($range_year);
    }
    elseif ($modelid=="") { //YEAR
        $model_cap = $this->getCarModelVariables($model);
        $mfa_brand = $this->getCarManufVariables($mfa)[1];
        $title=" $mfa_brand $model_cap $year";

        $range_manuf=""; $mfa_search=$mfa_brand;
        $range_model=""; $model_search=$model_cap;
        $range_year="";  $year_search=$year;
        $modelid_style=$style_title;
        $range_model_id=$this->getCarModelIdsList($year,$model_cap); $range_model_id=$this->drawStyle($range_model_id);
    }
    else { //MODEL_ID
        $text = $this->getCarModelIdVariables($modelid);
        $model_cap = $this->getCarModelVariables($model);
        $mfa_brand = $this->getCarManufVariables($mfa)[1];
        $title=" $mfa_brand $text";

        $range_manuf=""; $mfa_search=$mfa_brand;
        $range_model=""; $model_search=$model_cap;
        $range_year="";  $year_search=$year;
        $range_model_id=""; $modelid_search=$text;
        $type_style=$style_title;
        $range_type=$this->getCarTypeList($modelid,$str_text); $range_type=$this->drawStyle($range_type);
    }

    $form=str_replace("{cars_title}",$title,$form);
    $form=str_replace("{str_text_select}",$str_text,$form);

    $form=str_replace("{mfa_select}",$mfa,$form);
    $form=str_replace("{model_select}",$model,$form);
    $form=str_replace("{year_select}",$year,$form);
    $form=str_replace("{modelid_select}",$modelid,$form);

    $form=str_replace("{range_manuf}",$range_manuf,$form);
    $form=str_replace("{range_model}",$range_model,$form);
    $form=str_replace("{range_year}",$range_year,$form);
    $form=str_replace("{range_model_id}",$range_model_id,$form);
    $form=str_replace("{range_types}",$range_type,$form);

    $form=str_replace("{mfa_search}",$mfa_search,$form);
    $form=str_replace("{model_search}",$model_search,$form);
    $form=str_replace("{year_search}",$year_search,$form);
    $form=str_replace("{modelid_search}",$modelid_search,$form);

    $form=str_replace("{mfa_style}",$mfa_style,$form);
    $form=str_replace("{model_style}",$model_style,$form);
    $form=str_replace("{year_style}",$year_style,$form);
    $form=str_replace("{modelid_style}",$modelid_style,$form);
    $form=str_replace("{type_style}",$type_style,$form);

    $form=$this->replaceLang($form);
    return $form;
}

function getCarManufList() { $db = DbSingleton::getTokoDb();
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
                <a href='$mfa_search/'>
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
    return array($mfa_id,$mfa_brand);
}

function getCarModelsList($mfa_id) { $db = DbSingleton::getTokoDb();
    $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;"); $n=$db->num_rows($r); $list=""; $first=$second="";
    if ($n>0) {
        $list="<ul class=\"t_model\">"; $list=$this->replaceLang($list);
        for ($i=1;$i<=$n;$i++){
            $model=$db->result($r,$i-1,"Model");
            $model_search=$db->result($r,$i-1,"Model_Link");
            if ($first!=substr($model,0,1) && $second!=substr($model,0,1))
            {$first=substr($model,0,1); $second = substr($model,0,1); $main_class="class=\"search__cat-auto\"";}
            else {$first=""; $second=substr($model,0,1); $main_class="";}
            $list.="
                <a href='$model_search/'>
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

function getCarModelVariables($model_str) { $db = DbSingleton::getTokoDb();
    $r=$db->query("SELECT * FROM `T_models` WHERE `Model_Link`='$model_str' LIMIT 1;");
    $model=$db->result($r,0,"Model");
    return $model;
}

function getCarYearList($model) { $db = DbSingleton::getTokoDb();
    $list=""; $min_date_start=1947; $max_date_end=2019;
    $r=$db->query("SELECT MIN(`MOD_PCON_START`) as min_year, 
            CASE WHEN MIN(`MOD_PCON_END`)=0 THEN 0 ELSE MAX(`MOD_PCON_END`) END as max_year
        FROM `T_models` WHERE `Model`='$model';");

    $date_start = $db->result($r,0,"min_year");
    if ($date_start!=0) $date_start = substr($date_start, 0, -2)."";
    if ($date_start==0) $date_start = $min_date_start;

    $date_end = $db->result($r,0,"max_year");
    if ($date_end!=0) $date_end = substr($date_end, 0, -2)."";
    if ($date_end==0) $date_end = $max_date_end;

    for ($i=$date_end;$i>=$date_start;$i--) {
        $list.="<a href=\"#\" onclick=\"showCarsSelect(4,$i);\">$i</a><br>";
    }
    return $list;
}

function getCarModelIdsList($year,$model) { $db = DbSingleton::getTokoDb();
    $r=$db->query("SELECT * FROM `T_models` WHERE `Model`='$model' AND 
            ((`MOD_PCON_END`>=".$year."00 AND `MOD_PCON_END`<=".$year."12)
            OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`>=".$year."00)
            OR (`MOD_PCON_START`<=".$year."12 AND `MOD_PCON_END`=0));"); $n=$db->num_rows($r); $list="";
    for ($i=1;$i<=$n;$i++) {
        $mod_id=$db->result($r,$i-1,"MOD_ID");
        $tex_text=$db->result($r,$i-1,"TEX_TEXT");
        $list.="<a href=\"#\" onclick=\"showCarsSelect(5,$mod_id);\">$tex_text</a><br>";
    }
    return $list;
}

function getCarModelIdVariables($mod_id) { $db = DbSingleton::getTokoDb();
    $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
    $text=$db->result($r,0,"TEX_TEXT");
    return $text;
}

function getCarTypeList($mod_id,$str_text) { $db = DbSingleton::getTokoDb();
//DELETE???????
    $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id';"); $n=$db->num_rows($r); $list="";
    for ($i=1;$i<=$n;$i++) {
        $typ_id=$db->result($r,$i-1,"TYP_ID");
        $tex_text=$db->result($r,$i-1,"TYP_MMT_TEXT");
        $typ_text=$db->result($r,$i-1,"TYP_TEXT");
        $fuel_id=$db->result($r,$i-1,"FUEL_ID"); $fuel_name=$this->getFuelName($fuel_id);
        list($mfa_search, $model_search)=$this->getLinkVariables($mod_id);
        if ($str_text=="")
            $list.="<a onClick=\"setCookie('auto_typ_id','$typ_id')\" href='https://toko.ua/cars/$mfa_search/$model_search/$typ_id/'>$tex_text $typ_text ($fuel_name)</a><br>";
        else
            $list.="<a onClick=\"setCookie('auto_typ_id','$typ_id')\" href='https://toko.ua/cars/$mfa_search/$model_search/$typ_id/$str_text/'>$tex_text $typ_text ($fuel_name)</a><br>";
    }
    return $list;
}

function getLinkVariables($mod_id) { $db = DbSingleton::getTokoDb();
    $r=$db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
    $mfa_id=$db->result($r,0,"MOD_MFA_ID");
    $model_link=$db->result($r,0,"Model_Link");
    $r=$db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
    $mfa_link=$db->result($r,0,"MFA_BRAND_LINK");
    return array($mfa_link,$model_link);
}

function getTypesInfo($type_id) { $db = DbSingleton::getTokoDb();
    $r=$db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$type_id' LIMIT 1;");
    $tex_text=$db->result($r,0,"TYP_MMT_TEXT");
    $fuel_id=$db->result($r,0,"FUEL_ID"); $fuel_name=$this->getFuelName($fuel_id);
    $info="$tex_text ($fuel_name)";
    return $info;
}

function drawStyle($list) {
    return "<div class=\"car_form-select_card\">$list</div>";
}

/*/////////////////////////////////*/

    function translit($st) {
    //        $st=strtr($st,"àáâãäå¸çèéêëìíîïðñòóôõúûý_", "abvgdeeziyklmnoprstufh'iei");
    //        $st=strtr($st,"ÀÁÂÃÄÅ¨ÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÚÛÝ_", "ABVGDEEZIYKLMNOPRSTUFH'IEI");
    //        $st=strtr($st, array(
    //            "æ"=>"zh", "ö"=>"ts", "÷"=>"ch", "ø"=>"sh",
    //            "ù"=>"shch","ü"=>"", "þ"=>"yu", "ÿ"=>"ya",
    //            "Æ"=>"ZH", "Ö"=>"TS", "×"=>"CH", "Ø"=>"SH",
    //            "Ù"=>"SHCH","Ü"=>"", "Þ"=>"YU", "ß"=>"YA",
    //            "¿"=>"i", "¯"=>"Yi", "º"=>"ye", "ª"=>"Ye"
    //        ));
        $st = iconv("UTF-8", "windows-1251", $st);
        $st = mb_convert_encoding($st, "UTF-8", "Windows-1251");
        return $st;
    }

//sort like: default storage first
// $mas=$this->sortByStorage($mas,$tpoint);
    function sortByStorage($mas,$tpoint_id) {
        $storage_id=$this->getDefaultStorage($tpoint_id);
        foreach ($mas as $mas_key=>$mas_val) {
            foreach ($mas_val as $key=>$val) {
                if ($val["storage_id"]==$storage_id) {
                    if (isset($mas[$mas_key][$key])) {
                        $temp = $mas[$mas_key][$key];
                        $mas[$mas_key][$key] = $mas[$mas_key][0];
                        $mas[$mas_key][0] = $temp;
                    }
                }
            }
        }
        return $mas;
    }

    function getDefaultStorage($tpoint_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T_POINT_STORAGE` WHERE `tpoint_id`='$tpoint_id' AND `default`=1 LIMIT 1;");
        $storage_id=$db->result($r,0,"storage_id");
        return $storage_id;
    }

function getCatalogTemplateName($template_id) { $db = DbSingleton::getTokoDb();
    $template_id = $this->getUrlNumber($template_id);
    $r=$db->query("SELECT * FROM `T2_CATALOGUES_TEMPLATES` WHERE `TEMPLATE_ID`='$template_id' LIMIT 1;");
    $name=$db->result($r, 0, "TEMPLATE_NAME");
    return $name;
}

//    function getSingleParam($table, $colon, $param, $value, $type="") { $db = DbSingleton::getTokoDb();
//        $type==1 ? $result=0 : $result="";
//        $r=$db->query("SELECT `$colon` FROM `$table` WHERE `$param`='$value' LIMIT 1;"); $n=$db->num_rows($r);
//        if ($n>0) $result=$db->result($r,0,"$colon");
//        return $result;
//    }

    function checkTypeAnalog($art,$likebrand,$likeart) { $db = DbSingleton::getTokoDb();
        $kours=new ExRateClass; $cur=$kours->getCurrentKours();
        $art=str_replace(str_split('.,+-\/:*?"<>| '),"", $art); $analog=$result=0;
        $group_brand=" GROUP BY t2c.BRAND_ID "; $ak=array(); $where_art_id_str=$where_brand="";
        if ($likebrand!="" && $likebrand>0 && is_numeric($likebrand)) { $where_brand=" AND t2c.BRAND_ID='$likebrand' "; $group_brand=""; }
        $query="SELECT t2c.BRAND_ID, t2c.DISPLAY_NR, t2c.ART_ID, t2c.KIND, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME
        FROM `T2_CROSS` t2c
            INNER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2c.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2c.ART_ID
        WHERE t2c.SEARCH_NUMBER='$art' $where_brand AND (t2c.KIND=3 OR t2c.KIND=4) AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END)
        $group_brand ORDER BY t2n.NAME ASC;";
        $r=$db->query($query); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $ART_ID=$db->result($r,$i-1,"ART_ID");
            $KIND=$db->result($r,$i-1,"KIND");
            $where_art_id_str.="'$ART_ID'";if ($i<$n){$where_art_id_str.=",";}
            if (($ak[$ART_ID]=="") || ($KIND==0)){$ak[$ART_ID]=$KIND;}
        }
        $r=$this->getSearchList($where_art_id_str,"","",""); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $art_id = $db->result($r, $i-1, "ART_ID");
                $brand_id = $db->result($r, $i-1, "BRAND_ID");
                $suppl_id = $db->result($r, $i-1, "suppl_id");
                $storage_id = $db->result($r, $i-1, "storage_id");
                if ($suppl_id > 0) $storage_id = $db->result($r, $i-1, "client_storage_id");
                $name = $db->result($r, $i-1, "ARTICLE_NR_DISPL");
                $stock = intval($db->result($r, $i-1, "AMOUNT"));
                if ($suppl_id > 0) $stock = intval($db->result($r, $i-1, "stock_suppl"));
                $price = $this->getArticlePrice($art_id);
                if ($price == 0) $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                $price = $kours->getKoursPrice($price, $cur);
                $format_name = str_replace(str_split('.,+-\/:*?"<>| '), "", $name);
                if ($stock=="") $stock = 0;
                if ($price!=0 || (($name==$likeart || $format_name==$likeart) && $brand_id==$likebrand)) {
                    if ($stock>0 || (($name==$likeart || $format_name==$likeart) && $brand_id==$likebrand)) {
                        if ($suppl_id==0) $result=1;
                    }
                }
            }
        } else $result=$analog;
        return $result;
    }

    function checkAnalog($art_id, $likeart, $likebrand) { $db = DbSingleton::getTokoDb();
        $kours=new ExRateClass; $cur=$kours->getCurrentKours(); $k=0;
        $r=$db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2n.INFO, t2sai.suppl_id,
        s.id as storage_id, t2si.client_storage_id, t2si.stock_suppl, t2asc.AMOUNT, t2si.return_delay
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
            LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `T2_SUPPL_ARTICLES_IMPORT` t2sai ON (t2sai.art_id=t2a.ART_ID)
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
            LEFT OUTER JOIN `STORAGE` s ON s.id=t2asc.STORAGE_ID
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2sai.art_id AND t2si.suppl_id=t2sai.suppl_id AND t2si.status=1)
        WHERE t2a.ART_ID='$art_id' AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END);"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $art_id = $db->result($r,$i-1,"ART_ID");
                $brand_id = $db->result($r,$i-1,"BRAND_ID");
                $suppl_id = $db->result($r,$i-1,"suppl_id");
                $storage_id = $db->result($r,$i-1,"storage_id");
                if ($suppl_id>0) $storage_id = $db->result($r,$i-1,"client_storage_id");
                $name = $db->result($r,$i-1,"ARTICLE_NR_DISPL");
                $stock = intval($db->result($r,$i-1,"AMOUNT"));
                if ($suppl_id>0) $stock = $db->result($r,$i-1,"stock_suppl");
                $price = $this->getArticlePrice($art_id);
                if ($price==0) $price = $this->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
                $price = $kours->getKoursPrice($price,$cur);
                $format_name = str_replace(str_split('.,+-\/:*?"<>| '),"", $name);
                if ($price!=0 || (($name==$likeart || $format_name==$likeart) && $brand_id==$likebrand)) if ($suppl_id>0 && $stock>0) $k++;
            }
        }
        return $k;
    }

    function showCatalogueSearchForm($template_id) {
        $this->clearCataloguesValuesTemp();
        $form=$this->getHtmlForm("cat_search_form");
        list($list,$list_brand,$max_price,$pagin,$list_params)=$this->getCatalogueSearchList($template_id);
        $cat_search_filters=$this->getCatalogueSearchFilters($template_id,$max_price,$list_brand);
        $form=str_replace("{search_params}",$list_params,$form);
        $form=str_replace("{search_list}",$list,$form);
        $form=str_replace("{template_id}",$template_id,$form);
        $form=str_replace("{cat_search_filters}",$cat_search_filters,$form);
        $form=str_replace("{search_pagin}",$pagin,$form);
        $gg_name=$this->getCatalogTemplateName($template_id);
        $form=str_replace("{art}","<h1>{offers_request} <span class=\"span-red\">$gg_name</span> {and_analogs}</h1>",$form);
        return $form;
    }

function showPhotoForm($art_id) {
    $articlePhotos = $this->getArticlePhotos($art_id); $nophoto=$this->noPhoto;
    $info="<div class=\"row\">
        <div class=\"col-12 info-photo\">
            <div id=\"carouselExampleControls-$art_id\" class=\"carousel slide\" data-ride=\"carousel\">
                <div class=\"carousel-inner\" role=\"listbox\">";
    if (!$articlePhotos) {
        $info.="<div class=\"carousel-item active\"> 
                <img itemprop=\"image\" class=\"lazy d-block\" src=\"$nophoto\" data-src=\"$nophoto\">
            </div>";
    }
    foreach ($articlePhotos as $index => $articlePhoto) {
        $photo_name=trim($articlePhoto["PHOTO_NAME"]);
        $index==0 ? $active="active" : $active="";
        $photo_name=="" ? $photo_name="$nophoto" : $photo_name="https://toko.ua/thumb.php?image=catalogue/$photo_name&size=300";
        $info.="<div class=\"carousel-item $active\"> 
                <a target=\"_blank\" onclick=\"showPhotoGallery($art_id)\">
                <img itemprop=\"image\" class=\"d-block lazy\" src=\"$photo_name\" data-src=\"$photo_name\" alt=\"Slide $index\"></a>
            </div>";
    }
    $info.="</div>";
    if (count($articlePhotos)>1) $info.="
            <a class=\"carousel-control-prev\" href=\"#carouselExampleControls-$art_id\" role=\"button\" data-slide=\"prev\">
                <span class=\"carousel-control-prev-icon\" aria-hidden=\"true\"></span>
                <span class=\"sr-only\">Previous</span>
            </a>
            <a class=\"carousel-control-next\" href=\"#carouselExampleControls-$art_id\" role=\"button\" data-slide=\"next\">
                <span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>
                <span class=\"sr-only\">Next</span>
            </a>";
    $info.="</div></div></div>";
    return $info;
}

function showArticleInfoForm($art_id,$article_nr_displ,$brand_name) { $db=DbSingleton::getTokoDb();
    $catalogue=new CatalogueClass; $info=$list="";
    $r=$db->query("SELECT * FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id' AND `ACTIVE`=1 ORDER BY `PHOTO_NAME` ASC;"); $n=$db->num_rows($r);
    if ($n>0) {
        for ($i=1;$i<=$n;$i++) {
            $photo_name=trim($db->result($r,$i-1,"PHOTO_NAME"));
            $i==1 ? $active="active" : $active="";
            $list.="<div class=\"carousel-item $active\">
                    <a target=\"_blank\" onclick=\"showPhotoGallery($art_id);\">
                        <img itemprop=\"image\" class=\"d-block img-fluid\" src=\"https://toko.ua/thumb.php?image=catalogue/$photo_name&size=300\" alt=\"slide $i\">
                    </a>
                </div> ";
        }
        $info.="<div class=\"row pad10\">
            <div class=\"col-lg-12 col-12 info-photo\">
                <div id=\"carouselExampleControls\" class=\"carousel slide\" data-ride=\"carousel\">
                    <div class=\"carousel-inner\" role=\"listbox\">$list</div>
                    <a class=\"carousel-control-prev\" href=\"#carouselExampleControls\" role=\"button\" data-slide=\"prev\">
                        <span class=\"carousel-control-prev-icon\" aria-hidden=\"true\"></span>
                        <span class=\"sr-only\">Previous</span>
                    </a>
                    <a class=\"carousel-control-next\" href=\"#carouselExampleControls\" role=\"button\" data-slide=\"next\">
                        <span class=\"carousel-control-next-icon\" aria-hidden=\"true\"></span>
                        <span class=\"sr-only\">Next</span>
                    </a>
                </div>
            </div>
            <div class=\"col-lg-12 col-12 font10\" style=\"padding: 30px 0;\"><table class=\"info-table\">";
    } else {
        $list.="<div class=\"carousel-item active\"> 
                <a><img itemprop=\"image\" class=\"d-block img-fluid\" src=\"$this->noPhoto\" alt=\"Slider\"></a>
            </div>";
        $info.="<div class=\"row pad10\">
            <div class=\"col-lg-12 col-12 info-photo\">
                <div id=\"carouselExampleControls\" class=\"carousel slide\" data-ride=\"carousel\">
                    <div class=\"carousel-inner\" role=\"listbox\">$list</div>
                </div>
            </div>
            <div class=\"col-lg-12 col-12 font10\" style=\"padding: 30px 0;\"><table class=\"info-table\">";
    }

    $r=$db->query("SELECT `TEXT`, `VALUE` FROM `T2_INFO` WHERE `ART_ID`='$art_id' AND `LANG_ID`='16' ORDER BY `SORT` ASC;"); $n=$db->num_rows($r);
    for ($i=1;$i<=$n;$i++){
        $text=$db->result($r,$i-1,"TEXT");
        $value=$db->result($r,$i-1,"VALUE");
        $info.="<tr><td><span class=\"bold\">$text</span></td> <td>$value</td></tr>";
    }
    $info.="</table></div></div>";

    $original_numbers=$catalogue->getOriginalNumbers($art_id);
    $prim=$this->getApplManufTCD($art_id);

    $info2="<div class=\"row\"><div class=\"col-lg-12\">$prim</div></div>";
    $info3="<div class=\"row\"><div class=\"col-lg-12\">$original_numbers</div></div>";
    $title="<h4 class=\"modal-title\"><span>$brand_name</span> $article_nr_displ</h4>";

    $info=$this->replaceLang($info); $info2=$this->replaceLang($info2); $info3=$this->replaceLang($info3);

    return array($info,$info2,$info3,$title);
}

//    function getTypList($mfa_link,$model_link) { $db = DbSingleton::getTokoDb();
//        $r=$db->query("SELECT tt.TYP_ID FROM `T_types` tt
//            LEFT JOIN `T_models` tm ON tt.TYP_MOD_ID=tm.MOD_ID
//            LEFT JOIN `T_manufacturers` tf ON tm.MOD_MFA_ID=tf.MFA_ID
//        WHERE tf.MFA_BRAND_LINK='$mfa_link' AND tm.Model_Link='$model_link';"); $n=$db->num_rows($r); $types=[];
//        for ($i=1;$i<=$n;$i++) {
//            $typ_id=$db->result($r,$i-1,"TYP_ID");
//            array_push($types,$typ_id);
//        }
//        $types=implode(",",$types);
//        return $types;
//    }

    function getCatalogueSearchPagin($template_id,$n,$ch=1) {
        $list="";
        for ($i=1;$i<=$n;$i++) {
            if ($i==$ch) $checked="pagin_checked"; else $checked="pagin_btn";
            $list.="<button class='btn $checked' onclick='getCatalogueSearchList($template_id,$i,this)'>$i</button>";
        }
        if ($n==1) $list="";
        return $list;
    }

    function getCatalogueSearchFilterParamaters($template_id,$params) { $db=DbSingleton::getTokoDb();
        $mas=[]; $arts=[];
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' GROUP BY `ART_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $r2=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n2=$db->num_rows($r2);
            for ($j=1;$j<=$n2;$j++) {
                $param_id=$db->result($r2,$j-1,"PARAM_ID");
                $mas[$art_id][$param_id]="";
            }
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            $mas[$art_id][$param_id]=$value_id;
        }

        foreach ($mas as $key=>$value) {
            $art_id=$key;
            $max=count($params);$col=0;
            foreach ($params as $k=>$item) {
                if (in_array($value[$k],$item)) $col++;
            }
            if ($col==$max) array_push($arts,$art_id);
        }

        $arts=implode(",",$arts);

        return $arts;
    }

    function getCatalogueSearchFilterParams($template_id,$values_filter=[],$param_values='') { $db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r); $list="";
        for ($i=1;$i<=$n;$i++) {
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $param_name=$db->result($r,$i-1,"PARAM_NAME");
            list($param_list,$kol,)=$this->getCatalogueSearchFilterParamsValues($template_id,$param_id,$values_filter,$param_values);
            if ($kol>4) {
                $style = "";
                $toggle = "<a class=\"pointer underline\" onclick=\"toggleListParams(this,$param_id);\"><span class=\"show\">{more_cap} $kol</span> <span class=\"none\">{hide_cap}</span></a>";
            } else {
                $style ="height:auto;";
                $toggle = "";
            }
            $list.="
            <span class=\"bold\" style=\"margin-top: 2em;display: block;\">$param_name</span>
            <ul class=\"list-inline list-hide\" id=\"param-$param_id\" style=\"margin: 0; $style\">
                $param_list
            </ul>
            $toggle ";
        }
        $list=$this->replaceLang($list);
        return $list;
    }

    function getCatalogueSearchFilterParamsValues($template_id,$param_id,$values_filter,$param_values) { $db=DbSingleton::getTokoDb();
        $list="";$arr=[];
        if ($param_values!="") $where_params=" AND `VALUE_ID` IN ($param_values)"; else $where_params="";
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES` WHERE `TEMPLATE_ID`='$template_id' AND `PARAM_ID`='$param_id' $where_params;"); $n=$db->num_rows($r);

        for ($i=0;$i<$n;$i++) {
            $value_id=$db->result($r,$i,"VALUE_ID");
            $param_value=$db->result($r,$i,"PARAM_VALUE");
            if (in_array($value_id,$values_filter)) $ch=0; else $ch=1;
            $arr[$i]=["value_id"=>$value_id,"param_value"=>$param_value,"checked"=>$ch];
        }

        usort($arr,"cmpChecked");

        for ($i=0;$i<$n;$i++) {
            $value_id=$arr[$i]["value_id"];
            $param_value=$arr[$i]["param_value"];
            $checked=$arr[$i]["checked"];
            if ($checked==0) $ch="checked=\"checked\""; else $ch="";
            $list.="
            <label class=\"container_check params\">$param_value<input type=\"checkbox\" value=\"$value_id\" $ch class=\"$param_id\" onclick=\"getCatalogueSearchList($template_id,1,'',this);\">
                <span class=\"checkmark\"></span>
            </label>";
        }
        return array($list,$n);
    }

    function getCatalogueSearchFilters($template_id,$max_price,$brand_filters) {
        $form=$this->getHtmlForm("cat_search_filters");
        $form=str_replace("{sideblock_max_price}",ceil($max_price),$form);
        $form=str_replace("{brand_filters}",$brand_filters,$form);
        $form=str_replace("{template_id}",$template_id,$form);
        return $form;
    }

    function getCatalogueSearchParams($art_id) { $db = DbSingleton::getTokoDb();
        $suppl_array=$storage_array=$brand_array=$storage_array=$stock_array=[];
        $r=$db->query("
        SELECT t2asc.STORAGE_ID as storage_id, 0 as suppl_id, t2asc.AMOUNT
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc on t2asc.ART_ID=t2a.ART_ID
        WHERE t2a.ART_ID='$art_id' AND t2asc.STORAGE_ID>0

        UNION ALL

        SELECT t2si.client_storage_id, t2si.suppl_id, t2si.stock_suppl
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si on (t2si.art_id=t2a.ART_ID AND t2si.status=1)
        WHERE t2a.ART_ID='$art_id' AND t2si.client_storage_id>0 AND t2si.stock_suppl>0");
        $n=$db->num_rows($r);

        for ($i=1;$i<=$n;$i++) {
            $suppl_id = $db->result($r,$i-1,"suppl_id");
            $suppl_array[$i]=$suppl_id;
            $storage_id = $db->result($r,$i-1,"storage_id");
            $storage_array[$i]=$storage_id;
            $stock = $db->result($r,$i-1,"AMOUNT");
            $stock_array[$i]=$stock;
        }
        return array($suppl_array,$storage_array,$stock_array,$n);
    }

    function getArtsParamValue($arts) { $db = DbSingleton::getTokoDb();
        $values=[];
        if ($arts!="") {
            $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `ART_ID` IN ($arts);");$n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $value_id = $db->result($r, $i-1, "VALUE_ID");
                array_push($values,$value_id);
            }
            $values=array_unique($values);
        }
        $values=implode(",",$values);
        return $values;
    }

    function clearCataloguesValuesTemp() { $db = DbSingleton::getTokoDb();
        $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_STATUS`=0, `DISPLAY_STATUS`=0, `CHECKED_LAST`=0, `RESERV_CHECKED`=0;");
        return true;
    }

    function getCatalogueParamByValue($value_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES` WHERE `VALUE_ID`='$value_id' LIMIT 1;"); $n=$db->num_rows($r); $param_id=0;
        if ($n>0) $param_id=$db->result($r,0,"PARAM_ID");
        return $param_id;
    }

    function getCatalogueSearchFilterValue($template_id,$params,$arts_all) { $db = DbSingleton::getTokoDb();
        $mas=[]; $arts=[]; $arts_all=trim($arts_all,","); $template=new TemplateClass;

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' AND `ART_ID` IN ($arts_all) GROUP BY `ART_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $r2=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n2=$db->num_rows($r2);
            for ($j=1;$j<=$n2;$j++) {
                $param_id=$db->result($r2,$j-1,"PARAM_ID");
                $mas[$art_id][$param_id]=[];
            }
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' AND `ART_ID` IN ($arts_all);"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            array_push($mas[$art_id][$param_id],$value_id);
        }

        foreach ($mas as $art_id=>$param_values) {$col=0;
            $max=count($params);
            foreach ($params as $param_id=>$values) {$col_val=0;
                foreach ($values as $key=>$value_id) {
                    if (in_array($param_values[$param_id][$key],$values)) $col_val++;
                }
                if ($col_val>0) $col++;
            }
            if ($col==$max) {
                array_push($arts,$art_id);
            }
        }

        $list="<table class=\"table\" style=\"font-size:0.5em;\"><thead><td>ART_ID</td>";
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i-1, "PARAM_ID");
            $param_name = $db->result($r, $i-1, "PARAM_NAME");
            $list.="<td>$param_id. $param_name</td>";
        }
        $list.="</thead>";

        foreach ($arts as $art_id) {
            $list.="<tr><td>$art_id</td>";
            foreach ($mas[$art_id] as $param_id=>$values) {$value_str="";
                foreach ($values as $value_id) {
                    $value_name=$template->getCatalogueValueName($value_id);
                    $value_str.="$value_name($value_id) ";
                }
                $list.="<td>$value_str</td>";
            }
            $list.="</tr>";
        }
        $list.="</table>";

        $arts=implode(",",$arts);

        return array($arts,$list);
    }

    function getParamsFilter($template_id,$checked_value,$arts_all) { $db = DbSingleton::getTokoDb();

        $value_id=$checked_value; $param_id=$this->getCatalogueParamByValue($checked_value);

        $checked_param[$param_id]=[$value_id]; $log="LOGS";

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `CHECKED_STATUS`=1 AND `PARAM_ID`='$param_id';"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $values_checked = $db->result($r, $i-1, "VALUE_ID");
                array_push($checked_param[$param_id],$values_checked);
            }
        }

        list($arts,$list_arts)=$this->getCatalogueSearchFilterValue($template_id,$checked_param,$arts_all);

        $value_str=$this->getArtsParamValue($arts);

        if ($value_str!="") {

            $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `CHECKED_STATUS`=1;"); $n=$db->num_rows($r);

            // FIRST RUN
            if ($n==0) {
                $value_par_str="";
                $rpar=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `PARAM_ID`='$param_id' AND `DISPLAY_STATUS`=1;"); $npar=$db->num_rows($rpar);
                for ($i = 1; $i <= $npar; $i++) {
                    $value_par_id=$db->result($rpar,$i-1,"VALUE_ID");
                    $value_par_str.="$value_par_id"; if ($i<$npar) $value_par_str.=",";
                }

                $this->clearCataloguesValuesTemp();

                $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_STATUS`=1, `DISPLAY_STATUS`=1, `CHECKED_LAST`=2 WHERE `VALUE_ID`='$value_id' LIMIT 1;");

                $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_str);");

                if ($value_par_str!="") $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_par_str);");
            }

            // NEXT RUN
            if ($n>0) {

                $rch=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `CHECKED_STATUS`=1 AND `VALUE_ID`='$value_id';"); $nch=$db->num_rows($rch);

                // CHECK VALUES
                if ($nch==0) {

                    $value_par_str="";
                    $rpar=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `PARAM_ID`='$param_id' AND `DISPLAY_STATUS`=1;"); $npar=$db->num_rows($rpar);
                    for ($i = 1; $i <= $npar; $i++) {
                        $value_par_id=$db->result($rpar,$i-1,"VALUE_ID");
                        $value_par_str.="$value_par_id"; if ($i<$npar) $value_par_str.=",";
                    }

                    $rsellast=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `CHECKED_LAST`=2 LIMIT 1;"); $nsellast=$db->num_rows($rsellast);
                    if ($nsellast>0) {
                        $sellast_param=$db->result($rsellast,0,"PARAM_ID");
                        $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `RESERV_CHECKED`=2
                        WHERE `DISPLAY_STATUS`=1 AND `PARAM_ID`='$sellast_param' AND `CHECKED_STATUS`!=1;");
                    }

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=0 WHERE `CHECKED_STATUS`!=1 AND `RESERV_CHECKED`!=2;");

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_LAST`=0 WHERE `CHECKED_LAST`=1 LIMIT 1;");
                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_LAST`=1 WHERE `CHECKED_LAST`=2 LIMIT 1;");

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_STATUS`=1, `DISPLAY_STATUS`=1, `CHECKED_LAST`=2 WHERE `VALUE_ID`='$value_id' LIMIT 1;");

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_par_str);");

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_str);");
                }

                // UNCHECK VALUES
                if ($nch>0) {

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_LAST`=2 WHERE `CHECKED_LAST`=1 LIMIT 1;");

                    $rlast=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `CHECKED_LAST`=2;"); $nlast=$db->num_rows($rlast);

                    $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_STATUS`=0, `CHECKED_LAST`=0 WHERE `VALUE_ID`='$value_id' LIMIT 1;");

                    // UNCHECK ALL VALUES
                    if ($nlast==0) {
                        $this->clearCataloguesValuesTemp();

                        $checked_param2=[];
                        list($arts,$list_arts)=$this->getCatalogueSearchFilterValue($template_id,$checked_param2,$arts_all);

                        $value_str=$this->getArtsParamValue($arts);

                        $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `CHECKED_STATUS`=0, `CHECKED_LAST`=0 WHERE `VALUE_ID`='$value_id' LIMIT 1;");

                        $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_str);");
                    }

                    // UNCHECK SELECTED VALUE
                    else {
                        $rf=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE PARAM_ID='$param_id' AND CHECKED_STATUS=1 AND VALUE_ID!='$value_id';"); $nf=$db->num_rows($rf);

                        // UNCHECK and HAVE NOT CHECKED in PARAM
                        if ($nf==0) {

                            $f_param=[];$f_param[$param_id]=[];

                            $rff=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE PARAM_ID='$param_id' AND DISPLAY_STATUS=1;"); $nff=$db->num_rows($rff);
                            for($i=1;$i<=$nff;$i++) {
                                $f_value_id=$db->result($rff,$i-1,"VALUE_ID");
                                array_push($f_param[$param_id],$f_value_id);
                            }

                            list($last_arts,$list_arts)=$this->getCatalogueSearchFilterValue($template_id,$f_param,$arts_all);

                            $last_value_str=$this->getArtsParamValue($last_arts);

                            $value_par_str="";
                            $rpar=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `PARAM_ID`='$param_id' AND `DISPLAY_STATUS`=1;"); $npar=$db->num_rows($rpar);
                            for ($i=1;$i<=$npar;$i++) {
                                $value_par_id=$db->result($rpar,$i-1,"VALUE_ID");
                                $value_par_str.="$value_par_id"; if ($i<$npar) $value_par_str.=",";
                            }

                            $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=0 WHERE `CHECKED_STATUS`!=1 AND `RESERV_CHECKED`!=2;");

                            $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_par_str);");

                            $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($last_value_str);");
                        }

                        // UNCHECK and HAVE CHECKED in PARAM
                        if ($nf>0) {

                            $f_param=[];$f_param[$param_id]=[];
                            for($i=1;$i<=$nf;$i++) {
                                $f_value_id=$db->result($rf,$i-1,"VALUE_ID");
                                array_push($f_param[$param_id],$f_value_id);
                            }

                            list($last_arts,$list_arts)=$this->getCatalogueSearchFilterValue($template_id,$f_param,$arts_all);

                            $last_value_str=$this->getArtsParamValue($last_arts);

                            $value_par_str="";
                            $rpar=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `PARAM_ID`='$param_id' AND `DISPLAY_STATUS`=1;"); $npar=$db->num_rows($rpar);
                            for ($i=1;$i<=$npar;$i++) {
                                $value_par_id=$db->result($rpar,$i-1,"VALUE_ID");
                                $value_par_str.="$value_par_id"; if ($i<$npar) $value_par_str.=",";
                            }

                            $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=0 WHERE `CHECKED_STATUS`!=1 AND `RESERV_CHECKED`!=2;");

                            $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($value_par_str);");

                            $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `VALUE_ID` IN ($last_value_str);");
                        }

                    }

                    // DISPLAY LAST PARAM VALUES
                    $rsellast=$db->query("SELECT * FROM T2_CATALOGUES_VALUES_TEMP` WHERE `CHECKED_LAST`=2 LIMIT 1;"); $nsellast=$db->num_rows($rsellast);
                    if ($nsellast>0) {
                        $sellast_param=$db->result($rsellast,0,"PARAM_ID");
                        $db->query("UPDATE `T2_CATALOGUES_VALUES_TEMP` SET `DISPLAY_STATUS`=1 WHERE `PARAM_ID`='$sellast_param' AND `RESERV_CHECKED`=2;");
                    }

                }
            }

            $array_checked=[];$array_values=[];
            $r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES_TEMP` WHERE `DISPLAY_STATUS`=1;"); $n=$db->num_rows($r);
            for ($i=1;$i<=$n;$i++) {
                $value_id = $db->result($r, $i-1, "VALUE_ID");
                $checked = $db->result($r, $i-1, "CHECKED_STATUS");
                array_push($array_values,$value_id);
                if ($checked==1) array_push($array_checked,$value_id);
            }

            $str_values=implode(",",$array_values);
            $list=$this->getCatalogueSearchFilterParams($template_id,$array_checked,$str_values);
        }

        else {
            $list=$this->getCatalogueSearchFilterParams($template_id);
            $this->clearCataloguesValuesTemp();
        }

        return array($list,$log."<br>ARTS: arts<br>VALUES: value_str"."<br>$list_arts");
    }

    function getCatalogueSearchList($template_id=1,$values_filter=[],$brand_filter=[],$price_filter=[],$cur_page=1,$count_page=20,$checked_value=[]) { $db=DbSingleton::getTokoDb();
        $full_price=0; $min_price=0; $max_price=0; $col=1;
        $where_art_id_str_all=""; $where_art_id_str=""; $where_values_arts=""; $brand_str=""; $values_string="";
        $values_array=[]; $brands=[]; $param_values_array=[];
        $max_page=$cur_page*$count_page; $min_page=$max_page-$count_page+1;

        if (!empty($values_filter)) {
            $where_values_arts_str=$this->getCatalogueSearchFilterParamaters($template_id,$values_filter);
            $where_values_arts=" AND `ART_ID` IN ($where_values_arts_str)";
            foreach ($values_filter as $key=>$value) {
                foreach ($value as $k=>$item) {
                    $values_string.="$item,";
                }
            } $values_string=trim($values_string,",");
        }

        if (!empty($price_filter)) {
            $max_price=$price_filter[1];
            $min_price=$price_filter[0];
        }

        if (!empty($brand_filter)) {
            $brand_str=implode(",",$brand_filter);
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' $where_values_arts;"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $max_price_art=0;$validate_art_count=0;
                $art_id=$db->result($r, $i-1, "ART_ID");
                $value_id=$db->result($r, $i-1, "VALUE_ID");
                list($suppl_array,$storage_array,,$last)=$this->getCatalogueSearchParams($art_id);
                $brand_id=$this->getBrandFromArtId($art_id);
                $brand=$this->getBrandName($brand_id);

                if (in_array($brand_id,$brand_filter) || $brand_str=="") {
                    for ($j=1;$j<=$last;$j++) {
                        $suppl_id=$suppl_array[$j];
                        $storage_id=$storage_array[$j];
                        $price = $this->getArticlePrice($art_id);
                        if ($suppl_id!=0) $price = $this->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
                        if ($price>0) {
                            if ($price>$max_price_art) $max_price_art=$price;
                            $validate_art_count++;
                        }
                    }
                }

                if (($max_price_art<$min_price || $max_price_art>$max_price) && !empty($price_filter)) {
                    $validate_art_count=0;
                }

                if ($validate_art_count>0) {
                    if ($col<=$max_page && $col>=$min_page) {
                        $where_art_id_str.="'$art_id'";if ($i<$n){$where_art_id_str.=",";}
                    }
                    $where_art_id_str_all.="'$art_id'";if ($i<$n){$where_art_id_str_all.=",";}
                    $brands[$art_id]["brand"] = $brand;
                    $brands[$art_id]["brand_id"] = $brand_id;
                    if (!empty($brands[$art_id]["price"])) {
                        if ($max_price_art < $brands[$art_id]["price"]) $brands[$art_id]["price"] = round($max_price_art,2);
                    } else $brands[$art_id]["price"] = round($max_price_art,2);
                    array_push($param_values_array,$value_id);
                    $col++;
                }

                if ($max_price_art>$full_price) $full_price=$max_price_art;
            }
        }

        $full_price=round($full_price,2);
        $amount=$max_page-$min_page+1;

        $where_art_id_str=trim($where_art_id_str,",");

        if ($max_price==0) $max_price=$full_price;

        $jsFilterModel=$this->getSearchMessages(4,1)[1];
        $brand_filter_str=implode(",",$brand_filter);
        $list_brand=$this->getListBrand($brands,0,1,$jsFilterModel,$brand_filter_str);

        $pagin_count=round($col/$amount);
        $pagin=$this->getCatalogueSearchPagin($template_id,$pagin_count,$cur_page);

        if (!empty($values_filter))
            foreach ($values_filter as $item) {
                $values_array=array_merge($values_array,$item);
            }

        list($list_params,$log)=$this->getParamsFilter($template_id,$checked_value,$where_art_id_str_all);

        $title="<h2>Count = $amount($count_arts_filter); All amount = $col; Current page = $cur_page;<br>
        Params: $values_string; Brands Filter: $brand_filter_str; Prices: $min_price - $max_price;<br>LOGS: $log</h2>";

        $list=$this->replaceLang($list);
        $form=$title.$list;

        return array($form,$list_brand,$max_price,$pagin,$list_params);
    }