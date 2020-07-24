<?php

/*DETAILS*/

class SearchClass extends CatalogueClass {

    public $products_on_page=12;

    function getActualLink() {
        $link = $_SERVER["REQUEST_URI"];
        $arr = explode("/", $link);
        foreach ($arr as $key=>$value) {
            if ((strpos($value, "=") !== false)) unset($arr[$key]);
        }
        $link = implode("/", $arr);
        $link = rtrim($link, "/")."/";
        return $link;
    }

    function showDetailsForm($form, $str_id, $page, $active_filters, $mfa_link, $mod_link, $mod_id_link) {
        $parts = new PartsClass;
        $h1 = $this->getCatalogTitle($str_id, $active_filters, $mfa_link, $mod_link, $mod_id_link, $page);
        $form=str_replace("{details_str_id}", $str_id, $form);
        $form=str_replace("{details_active_filters}", implode(",", $active_filters), $form);
        $form=str_replace("{details_page}", $page, $form);
        $form=str_replace("{details_count}", "", $form);
        $form=str_replace("{details_linking}", $this->getStrLinking($str_id), $form);
        $form=str_replace("{details_link}", $this->getActualLink(), $form);
        $form=str_replace("{details_title}", $h1, $form);

        if ($parts->checkTable($str_id)>0) {
            // TEMP TABLE
            $partsData = $parts->showPartsCatalogue($str_id, $page, $active_filters);
            $details_content = $partsData["form"];
            $filters = $partsData["filters"];
            $brands = $partsData["brands"];
            $str_type = 1;
            $count_arts = $parts->getPartsCount($str_id, $active_filters);
            $count = $parts->products_on_page;
            $pages_count = ceil($count_arts / $count);
            if ($count_arts<$count) $pages_count=1;

            $form=str_replace("{details_listing}", $this->getSeoLinking($str_id, $h1, $filters, $brands), $form);

            $where_arts = $parts->initPartsArts($str_id);
            $filters_form = $this->printBrandsList(array_unique($this->getBrandIds($where_arts)), array_unique($active_filters), $this->getActualLink());

            $form=str_replace("{details_brands}", $filters_form, $form);
        } else {
            // T2_TREE
            $str_type = 0;
            $pages_count=0;
            $details_content = "";
            $form=str_replace("{details_brands}", "", $form);
        }
        $form=str_replace("{details_str_type}", $str_type, $form);
        $form=str_replace("{details_content}", $details_content, $form);
        return array($form, $pages_count);
    }

    function getCatalogTitle($str_id, $active_filters, $mfa_link, $mod_link, $mod_id_link, $page) {
        $cat = new CatalogueClass; $automan = new AutoClass; $prod = new ProductsClass;

        list($mfa_id, $model)=$automan->getAutoIdsLink($mfa_link, $mod_link);
        $translit=$prod->getCarManufTranslit($mfa_id, $model);

        list($mfa_text, $mod_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
        $mod_id_text = $automan->getAutoModelIdLink($mod_id_link)["text"]; if ($mod_id_text!="") $mod_text=$mod_id_text;
        $auto_text = "$mfa_text $mod_text";

        $str_text = $automan->getStrNewDescr($str_id);
        $str_link = $automan->getStrNewLink($str_id);

        $h1_text = $cat->getStaticH1("/catalog/$str_link/");
        if ($h1_text!="") $str_text=$h1_text;
        $h1 = $str_text;
        if ($auto_text!="" && $auto_text!=" ") $h1.=" {for_cap} $auto_text $translit";

        $filters=$this->getFiltersTitle($active_filters,1);

        $pager=$this->getPagerTitle($page);

        return $h1.$filters.$pager;
    }

    function getFiltersTitle($active_filters, $type=0) {
        $filters = "";
        foreach ($active_filters as $brand_id) {
            $brand_name = $this->getBrandName($brand_id);
            $filters.=" $brand_name,";
        }
        $filters=rtrim($filters, ",");
        if (!$type) {
            if (count($active_filters)>1) $filters="";
        }
        if ($filters!="") $filters=": ".$filters;
        return $filters;
    }

    function getPagerTitle($page) {
        $pager="";
        if ($page>1) {
            $pager=" - {page_cap} ¹$page";
            $pager=$this->replaceLang($pager);
        }
        return $pager;
    }

    function getExistedProducts($products) {
        foreach ($products as $art_id=>$values) {
            $validate_art_count=0; $max_price_art=0;
            list($suppl_array, $storage_array, $stock_array, $last) = $this->getExistedSearchParams($art_id);
            for ($j=1;$j<=$last;$j++) {
                $suppl_id = $suppl_array[$j];
                $storage_id = $storage_array[$j];
                $stock = $stock_array[$j];
                if ($suppl_id==0) $price = $this->getArticlePrice($art_id); else $price = $this->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
                if ($price>0 && $stock>0) {
                    if ($price>$max_price_art) $max_price_art=$price;
                    $validate_art_count++;
                }
            }
            if ($validate_art_count==0) {
                unset($products[$art_id]);
            }
        }
        return $products;
    }

    function getExistedSearchParams($art_id) { $db = DbSingleton::getTokoDb();
        $suppl_array=$storage_array=$stock_array=[];
        $r=$db->query("SELECT t2asc.STORAGE_ID as storage_id, 0 as suppl_id, t2asc.AMOUNT
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc on t2asc.ART_ID=t2a.ART_ID
        WHERE t2a.ART_ID='$art_id' AND t2asc.STORAGE_ID>0 
        UNION ALL
        SELECT t2si.client_storage_id, t2si.suppl_id, t2si.stock_suppl
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si on (t2si.art_id=t2a.ART_ID AND t2si.status=1)
        WHERE t2a.ART_ID='$art_id' AND t2si.client_storage_id>0 AND t2si.stock_suppl>0;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $suppl_array[$i] = $db->result($r,$i-1,"suppl_id");
            $storage_array[$i] = $db->result($r,$i-1,"storage_id");
            $stock_array[$i] = $db->result($r,$i-1,"AMOUNT");
        }
        return array($suppl_array, $storage_array, $stock_array, $n);
    }

    function catalogRouter($link, $some_link, $some_link2="") {
        $prod=new ProductsClass; $automan=new AutoClass;

        $page=$_GET["page"]; $page!=NULL ?: $page=1;

        $details_form=$prod->getHtmlForm("details_offers");

        $form1=""; $car_content=""; $pages_count=0; $str_id="";

        $str_link=""; $mfa_link=""; $mod_link=""; $mod_id_link=""; $filters="";

        $cookie_typ_id=$prod->getCookieAuto();

        $arr=explode("/", $link);

        if (!empty($arr[0])) $str_link = $arr[0];
        if (!empty($arr[3])) ((strpos($arr[4], "=") !== false)) ? $filters = $arr[4] : $filters = "";
        if (!empty($arr[3])) ((strpos($arr[3], "=") !== false)) ? $filters = $arr[3] : $mod_id_link = $arr[3];
        if (!empty($arr[2])) ((strpos($arr[2], "=") !== false)) ? $filters = $arr[2] : $mod_link = $arr[2];
        if (!empty($arr[1])) ((strpos($arr[1], "=") !== false)) ? $filters = $arr[1] : $mfa_link = $arr[1];

        $brand_ids = $this->getActiveFilters($filters);

        if ($cookie_typ_id!="" && $mfa_link!="") {
            $cookieData = $automan->getCookieCarInfo($cookie_typ_id);
            $mfa_link2 = $cookieData["mfa_link"];
            $mod_link2 = $cookieData["model_link"];

            if ($mfa_link2!=$mfa_link || ($mod_link!="" && $mod_link2!=$mod_link)) {
                $cookie_typ_id="";
                setcookie("auto_typ_id", "", time() + (86400 * 30), "/");
            }
        }

        if ($str_link=="" && $filters=="" && $mfa_link=="" && $mod_link=="") {
            // 1: /catalog
            if ($cookie_typ_id=="") {
                $str_ids = "";
                $typ_id = "";
                $car_content = $prod->getCarsSearch("", $mfa_link, $mod_link);
            } else {
                $str_ids = $prod->getStrIds($cookie_typ_id);
                $typ_id = $cookie_typ_id;
                $car_content = $prod->getCarsGarage();
            }
            $form1 = $prod->getCarDetails($str_ids, $typ_id);
        }

        if ($cookie_typ_id!="" && $mfa_link=="" && $mod_link=="") {
            $cookieData = $automan->getCookieCarInfo($cookie_typ_id);
            $mfa_link = $cookieData["mfa_link"];
            $mod_link = $cookieData["model_link"];
        }

        // 1: /catalog/to_filtri
        $head_id = $automan->getHeadNewLinkStr($some_link);

        if ($head_id!="") {
            if ($some_link2!="") $cat_id = $automan->getCatNewLinkStr($head_id, $some_link2); else $cat_id = "";
            $head_list = $automan->getDetailsList($head_id, $cat_id);
            $form1 = "<div class=\"content\">".$head_list."</div>";
            $car_content = "";
        } else {

            if ($str_link!="" && ($filters=="" || $filters!="") && $mfa_link=="" && $mod_link=="") {
                // 2: /catalog/shrus
                // 3: /catalog/shrus/brandy=bosch
                $str_id = $automan->getStrNewLinkStr($str_link);
                list($details_form, $pages_count) = $this->showDetailsForm($details_form, $str_id, $page, $brand_ids[0], $mfa_link, $mod_link, $mod_id_link);
                $car_content = $prod->getCarsSearch($str_id, $mfa_link, $mod_link);
                $form1 = $details_form;
            }

            if ($str_link!="" && ($filters=="" || $filters!="") && $mfa_link!="") {
                // 4: /catalog/shrus/kia
                // 5: /catalog/shrus/kia/brandy=bosch
                // 6: /catalog/shrus/kia/sportage
                // 7: /catalog/shrus/kia/sportage/brandy=bosch
                if ($cookie_typ_id=="") {
                    $str_id=$automan->getStrNewLinkStr($str_link);
                    list($details_form, $pages_count) = $this->showDetailsForm($details_form, $str_id, $page, $brand_ids[0], $mfa_link, $mod_link, $mod_id_link);

                    if ($mfa_link!="" && $mod_link!="") {
                        if ($mod_id_link!="") {
                            // 7: /catalog/shrus/kia/sportage/sl-8751/ = list(, $mod_id) = $automan->getAutoModelIdLink($mod_id_link);
                            $car_content = $prod->getCarsSearch($str_id, $mfa_link, $mod_link);
                        } else {
                            // 7: /catalog/shrus/kia/sportage/
                            $car_content = $prod->getCarsSearch($str_id, $mfa_link, $mod_link);
                        }
                    } else {
                        $car_content = $prod->getCarsSearch($str_id, $mfa_link, $mod_link);
                    }
                    $form1 = $details_form;
                }

                if ($cookie_typ_id!="") {
                    // 8: /catalog/shrus + TYP
                    // 9: /catalog/shrus/brandy=bosch + TYP
                    $str_id = $automan->getStrNewLinkStr($str_link);
                    $car_content = $prod->getCarsGarage();
                    $form1 = $prod->techCarModels($cookie_typ_id, $str_id);
                }

            }

            if ($cookie_typ_id!="") {
                setcookie("auto_typ_id", $cookie_typ_id, time() + (86400 * 30), "/");
                $automan->insertAutoHistory($cookie_typ_id);
            }
        }

        if ($mfa_link!="") {
            if ($mod_link!="") {
                if ($mod_id_link!="") {
                    $form1=str_replace("{seo_auto}", $automan->getAutoTypeList($automan->getMfaLink($mfa_link), $automan->getAutoModelIdLink($mod_id_link)["model_id"], $str_id, $brand_ids[0]), $form1);
                } else {
                    $form1=str_replace("{seo_auto}", $automan->getAutoModIDList($automan->getMfaLink($mfa_link), $automan->getModLink($mod_link), $str_id, $brand_ids[0]), $form1);
                }
            } else {
                $form1=str_replace("{seo_auto}", $automan->getAutoModList($automan->getMfaLink($mfa_link), $str_id, $brand_ids[0]), $form1);
            }
        } else {
            $form1=str_replace("{seo_auto}", $automan->getAutoMfaModelList($str_id, $brand_ids[0]), $form1);
        }

        return array($form1, $car_content, $pages_count);
    }

    function getActiveFilters($filters) {
        $active_filters = [];
        $arr = explode(";", $filters);

        foreach ($arr as $variable) {
            $param = substr($variable, 0, strpos($variable, "="));
            if ($param=="brandy") {
                $active_filters[0]=[];
                $value = str_replace($param."=", "", $variable);
                $value_arr = explode(",", $value);
                foreach ($value_arr as $value_id) {
                    $value_id = $this->getCatalogueBrandID($value_id);
                    array_push($active_filters[0], $value_id);
                }
            }
        }

        return $active_filters;
    }

    function getDetailsForm($str_id, $page, $current_products, $active_products, $status_filters) { $db = DbSingleton::getTokoDb();

        $limit = $this->getSearchLimit($page);

        if ($status_filters) {
            if (!empty($active_products)) {
                // filter_products
                $arts = implode(",", $active_products);
                $where_arts=" AND t2t.ART_ID IN ($arts)";
            } else {
                // none products
                $where_arts=" AND t2t.ART_ID IN (0)";
            }
        } else {
            // all products
            $current_products_arts = implode(",", array_keys($this->getExistedProducts($current_products)));
            $where_arts=" AND t2t.ART_ID IN ($current_products_arts)";
        }

        $art_ids=[];
        $r=$db->query("SELECT t2t.`ART_ID`, t2a.`BRAND_ID` FROM `T2_TREE` t2t
            LEFT JOIN `T2_ARTICLES` t2a ON t2a.ART_ID=t2t.ART_ID
        WHERE t2t.`STR_ID`=$str_id $where_arts GROUP BY t2t.ART_ID $limit;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $db->result($r, $i-1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $where_art_ids=implode(",", $art_ids);

        if ($where_art_ids=="") {
            $list = $this->replaceLang($this->getHtmlForm("error/404_found"));
        } else {
            $where_art_ids = trim($where_art_ids, ",");
            list($list) = $this->searchList($where_art_ids, 1, 1);
        }

        return $list;
    }

    function getCurrentDetails($str_id) { $db = DbSingleton::getTokoDb();
        $products=[];
        $r=$db->query("SELECT t2t.`ART_ID`, t2a.`BRAND_ID` FROM `T2_TREE` t2t
            LEFT JOIN `T2_ARTICLES` t2a ON t2a.ART_ID=t2t.ART_ID
        WHERE t2t.`STR_ID`=$str_id GROUP BY t2t.ART_ID;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $brand_id=$db->result($r,$i-1,"BRAND_ID");
            if (empty($products[$art_id][0])) $products[$art_id][0]=[];
            $products[$art_id][0]=[$brand_id];
        }

        $current_products = $this->getExistedProducts($products);
        return $current_products;
    }

    function getActiveDetails($current_products, $active_filters) {
        $active_products = [];

        if (!empty($active_filters)) {
            foreach ($current_products as $art_id=>$params) { $count_params=0;
                foreach ($params as $param_id=>$values) { $count_values=0;
                    foreach ($values as $value_id) {
                        if (in_array($value_id, $active_filters[$param_id])) $count_values++;
                    }
                    if ($count_values>0) $count_params++;
                }
                if ($count_params==count($active_filters)) {
                    if (empty($active_products[$art_id])) $active_products[$art_id]=[];
                    $active_products[$art_id] = $current_products[$art_id];
                }
            }
        } else {
            $active_products = $current_products;
        }

        $active_products = array_keys($this->getExistedProducts($active_products));

        return $active_products;
    }

    function getSearchLimit($page) {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        $off>=0 ? $limit = " LIMIT $count OFFSET $off": $limit = "";
        return $limit;
    }

    function getPagination($n, $page) {
        $count = $this->products_on_page;
        $pages_count = ceil($n / $count);
        if ($n<$count) $pages_count = 1;
        $pagination="";

        $min_count = 5;
        $max_count = $pages_count - $min_count + 1;
        $pred_page = $page-1; $next_page = $page+1;
        if ($page==1) $disabled_pred="disabled"; else $disabled_pred="";
        if ($page==$pages_count) $disabled_next="disabled"; else $disabled_next="";

        if ($pages_count>5) {

            if ($page<$min_count) {
                for ($i=1; $i<=$min_count; $i++) {
                    $i==$page ? $active="active" : $active="";
                    $pagination.="<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
                }
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

            if ($page>$max_count) {
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                for ($i=$max_count; $i<=$pages_count; $i++) {
                    $i==$page ? $active="active" : $active="";
                    $pagination.="<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
                }
            }

            if ($page>=$min_count && $page<=$max_count) {
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";

                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pred_page\">$pred_page</a></li>";
                $pagination.="<li class=\"page-item active\"><a class=\"page-link\" href=\"?page=$page\">$page</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$next_page\">$next_page</a></li>";

                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

        } else {
            for ($i=1; $i<=$pages_count; $i++) {
                $i==$page ? $active="active" : $active="";
                $pagination.="<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
            }
        }

        $list="<div class=\"row\">
            <nav aria-label=\"Page navigation\" class=\"img-center\" style='margin-top: 2em'>
                <ul class=\"pagination\">
                    <li class=\"page-item $disabled_pred\"><a class=\"page-link\" href=\"?page=$pred_page\"><i class=\"fa fa-chevron-left\"></i> <span class=\"span-media\">{previous_cap}</span></a></li>
                    $pagination
                    <li class=\"page-item $disabled_next\"><a class=\"page-link\" href=\"?page=$next_page\"><span class=\"span-media\">{next_cap}</span> <i class=\"fa fa-chevron-right\"></i></a></li>
                </ul>
            </nav>
        </div>";

        if ($pages_count<=1) $list="";

        return $list;
    }

    function getTrueSearchArts($str_id, $brandy) { $db = DbSingleton::getTokoDb();
        $where_brands = "";
        if ($brandy!="") {
            $brand_list=$this->getBrandsList($brandy);
            if ($brand_list!="") $where_brands="AND t2a.BRAND_ID IN ($brand_list)";
        }

        $art_ids=[];
        $r=$db->query("SELECT t2t.`ART_ID` FROM `T2_TREE` t2t
            LEFT JOIN `T2_ARTICLES` t2a ON t2a.ART_ID=t2t.ART_ID
        WHERE t2t.`STR_ID`=$str_id $where_brands GROUP BY t2t.ART_ID;"); $n=$db->num_rows($r);

        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            array_push($art_ids, $art_id);
        }

        $art_ids = $this->getExistProducts($art_ids, $where_brands);
        $where_arts = implode(",", $art_ids);

        return $where_arts;
    }

    function initTrueSearchArts($str_id) { $db = DbSingleton::getTokoDb();
        $art_ids=[]; $where_brands="";

        $r=$db->query("SELECT t2t.`ART_ID` FROM `T2_TREE` t2t
        WHERE t2t.`STR_ID`=$str_id GROUP BY t2t.ART_ID;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            array_push($art_ids, $art_id);
        }

        $cur_products_arr = $this->getExistProducts($art_ids, $where_brands);
        $where_arts = implode(",", $cur_products_arr);

        return $where_arts;
    }

    function getCountSearchList($art_ids, $where_brands) { $db = DbSingleton::getTokoDb();
        $r=$db->query("
        SELECT COUNT(*) as count_arts FROM (
        SELECT AA.ART_ID FROM (
            SELECT t2a.ART_ID FROM `T2_ARTICLES` t2a
                LEFT JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
                LEFT JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID=t2a.BRAND_ID)
            WHERE t2a.ART_ID IN ($art_ids) $where_brands AND t2si.stock_suppl>0 
            UNION ALL
            SELECT t2a.ART_ID FROM T2_ARTICLES t2a
                LEFT JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
                LEFT JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID=t2a.BRAND_ID)
            WHERE t2a.ART_ID IN ($art_ids) $where_brands AND t2asc.AMOUNT>0 
        ) as AA GROUP BY AA.ART_ID 
        ) as AB
        "); $n=$db->result($r,0,"count_arts")+0;
        return $n;
    }

    function getBrandIds($art_ids) { $db=DbSingleton::getTokoDb();
        $r=$db->query("       
        SELECT AA.BRAND_ID FROM (
            SELECT t2a.BRAND_ID FROM `T2_ARTICLES` t2a
                LEFT JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
                LEFT JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID=t2a.BRAND_ID)
            WHERE t2a.ART_ID IN ($art_ids) AND t2si.stock_suppl>0 
            UNION ALL
            SELECT t2a.BRAND_ID FROM T2_ARTICLES t2a
                LEFT JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
                LEFT JOIN `T2_BRANDS` t2b ON (t2b.BRAND_ID=t2a.BRAND_ID)
            WHERE t2a.ART_ID IN ($art_ids) AND t2asc.AMOUNT>0 
        ) as AA GROUP BY AA.BRAND_ID 
        "); $n=$db->num_rows($r); $brands=[];
        for ($i=1;$i<=$n;$i++) {
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            array_push($brands, $brand_id);
        }
        return array_unique($brands);
    }

    function getBrandsList($brandy) { $db=DbSingleton::getTokoDb();
        $brandy=explode(",", $brandy); $brands="";
        foreach ($brandy as $brand) {
            $r=$db->query("SELECT * FROM `T2_BRANDS` WHERE `BRAND_LINK`='$brand' LIMIT 1;");
            $brand_id=$db->result($r,0,"BRAND_ID");
            $brands.="$brand_id,";
        }
        $brands=rtrim($brands, ",");
        return $brands;
    }

    function showSearchParameters($str_id, $page, $brandy_str, $type=0) {
        $parts = new PartsClass;
        $number=""; $pagination_form="";

        if ($type==1) {
            $active_filters=explode(",", $brandy_str);
            $number = $parts->getPartsCount($str_id, $active_filters);
            $pagination_form = $this->replaceLang($parts->getPartsPaginationForm($number, $page));
        }

        $number_form = $this->replaceLang("($number {offer_tenths_cap})");

        return array($number_form, $pagination_form);
    }

    function printBrandsList($brands, $brands_ch, $link) {
        $list="
        <div class=\"details-offers__brands\">
        <input class=\"text-filter\" type=\"text\" id=\"brandSearchInput\" onkeyup=\"searchBrandInput();\" placeholder=\"{search_by_brand}\" title=\"{search_by_brand}\">
        <ul id=\"brandSearchList\">";

        $brands = array_unique($brands);
        $sort_brands = [];
        foreach ($brands as $brand_id) {
            $brand_name = $this->getBrandName($brand_id);
            $sort_brands[$brand_id] = $brand_name;
        }
        asort($sort_brands);

        foreach ($sort_brands as $brand_id=>$brand_name) {
            $label="<i class=\"far fa-square\"></i>";
            if (!empty($brands_ch)) {
                if (in_array($brand_id, $brands_ch)) $label="<i class=\"fa fa-check-square\"></i>";
            }
            $brand_link=$this->getSearchLink($brand_id, $brands_ch, $link);
            $list.="<li><a href=\"$brand_link/\">$label $brand_name</a></li>";
        }

        $list.="</ul></div>";

        return $this->replaceLang($list);
    }

    function getSearchLink($brand_id, $brands_ch, $actual_link) {
        $link="";
        if (!empty($brands_ch[0])) {
            foreach ($brands_ch as $brand) {
                if ($brand!=$brand_id) {
                    $brand_link=$this->getBrandLink($brand);
                    $link.="$brand_link,";
                }
            }
            if (!in_array($brand_id, $brands_ch)) $link.=$this->getBrandLink($brand_id);
        } else {
            $link.=$this->getBrandLink($brand_id);
        }
        $link=rtrim($link,",");
        if ($link!="") $link=$actual_link."brandy=$link"; else $link=$actual_link;
        return $link;
    }

    function getExistProducts($fproducts, $where_brands) {
        foreach ($fproducts as $key=>$art_id) {
            $validate_art_count=0; $max_price_art=0;
            list($suppl_array,$storage_array,$stock_array,$last) = $this->getCatalogueSearchParams($art_id, $where_brands);
            for ($j=1;$j<=$last;$j++) {
                $suppl_id = $suppl_array[$j];
                $storage_id = $storage_array[$j];
                $stock = $stock_array[$j];
                if ($suppl_id==0) $price = $this->getArticlePrice($art_id); else $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                if ($price>0 && $stock>0) {
                    if ($price>$max_price_art) $max_price_art=$price;
                    $validate_art_count++;
                }
            }
            if ($validate_art_count==0) {
                unset($fproducts[$key]);
            }
        }
        return $fproducts;
    }

    function getCatalogueSearchParams($art_id, $where_brands) { $db = DbSingleton::getTokoDb();
        $suppl_array=$storage_array=$stock_array=[];
        $r=$db->query("SELECT t2asc.STORAGE_ID as storage_id, 0 as suppl_id, t2asc.AMOUNT
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc on t2asc.ART_ID=t2a.ART_ID
        WHERE t2a.ART_ID='$art_id' $where_brands AND t2asc.STORAGE_ID>0 
        UNION ALL
        SELECT t2si.client_storage_id, t2si.suppl_id, t2si.stock_suppl
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si on (t2si.art_id=t2a.ART_ID AND t2si.status=1)
        WHERE t2a.ART_ID='$art_id' $where_brands AND t2si.client_storage_id>0 AND t2si.stock_suppl>0;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $suppl_array[$i] = $db->result($r,$i-1,"suppl_id");
            $storage_array[$i] = $db->result($r,$i-1,"storage_id");
            $stock_array[$i] = $db->result($r,$i-1,"AMOUNT");
        }
        return array($suppl_array, $storage_array, $stock_array, $n);
    }

//    function getTrueSearchList($art_ids, $page=1, $brandy="") { $db=DbSingleton::getTokoDb();
//        $kours=new ExRateClass; $client=new ClientClass;
//        $client_id=$this->getClient();
//        $limit=$this->getSearchLimit($page);
//        $cur=$kours->getCurrentKours();
//        $tpoint=$client->getTpoint();
//
//        $where_brands="";
//        if ($brandy!="") {
//            $brand_list=$this->getBrandsList($brandy);
//            if ($brand_list!="") $where_brands="AND t2b.BRAND_ID IN ($brand_list)";
//        }
//
//        $r=$db->query("SELECT AA.* FROM (
//            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2b.BRAND_POSITION, t2n.INFO, t2asc.AMOUNT, t2asc.STORAGE_ID as storage_id, 0 as suppl_id, 0 as return_delay
//            FROM `T2_ARTICLES` t2a
//                LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
//                LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
//                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
//            WHERE t2a.ART_ID IN ($art_ids) $where_brands AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) AND (t2asc.AMOUNT!=NULL OR t2asc.AMOUNT!=0)
//            UNION ALL
//            SELECT t2a.ART_ID, t2a.BRAND_ID, t2a.ARTICLE_NR_DISPL, t2b.BRAND_NAME, IFNULL(t2n.NAME,'') as NAME, t2b.BRAND_POSITION, t2n.INFO, t2si.stock_suppl, t2si.client_storage_id, t2si.suppl_id, t2si.return_delay
//            FROM `T2_ARTICLES` t2a
//                LEFT OUTER JOIN `T2_BRANDS` t2b ON t2b.BRAND_ID=t2a.BRAND_ID
//                LEFT OUTER JOIN `T2_NAMES` t2n ON t2n.ART_ID=t2a.ART_ID
//                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
//            WHERE t2a.ART_ID IN ($art_ids) $where_brands AND t2b.`VISIBLE`='1' AND (CASE WHEN t2n.LANG_ID!=NULL THEN t2n.LANG_ID=16 ELSE TRUE END) AND (t2si.stock_suppl!=NULL OR t2si.stock_suppl!=0)
//        ) as AA GROUP BY AA.ART_ID ORDER BY AA.BRAND_POSITION DESC $limit
//        "); $n=$db->num_rows($r);
//
//        $mas=[];
//        for ($i=1;$i<=$n;$i++) {
//            $art_id = $db->result($r,$i-1,"ART_ID");
//            $brand_id = $db->result($r,$i-1,"BRAND_ID");
//            $brand = $db->result($r,$i-1,"BRAND_NAME");
//            $name = $db->result($r,$i-1,"ARTICLE_NR_DISPL");
//            $article_name = $db->result($r,$i-1,"NAME");
//            $suppl_id = $db->result($r,$i-1,"suppl_id");
//            $stock = intval($db->result($r,$i-1,"AMOUNT"));
//            $storage_id = $db->result($r,$i-1,"storage_id");
//
//            $price = $this->getArticlePrice($art_id);
//            if ($suppl_id!=0) $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
//            $price = $kours->getKoursPrice($price, $cur);
//            if ($cur==1) { $price=$client->getClientPriceRounding($client_id, $price); }
//
//            list(,$delivery_days,$delivery_short_info) = $this->getTpointDeliveryInfo($tpoint,$storage_id);
//            if ($suppl_id!=0) list(,$delivery_days,$delivery_short_info) = $this->getTpointSupplDeliveryInfo($tpoint,$suppl_id,$storage_id);
//
//            $mas[$i]=["art_id"=>$art_id, "article_nr_displ"=>$name, "brand_id"=>$brand_id, "brand_name"=>$brand, "article_name"=>$article_name, "price"=>$price, "stock"=>$stock, "delivery_info"=>$delivery_short_info, "delivery_days"=>$delivery_days, "storage_id"=>$storage_id, "suppl_id"=>$suppl_id];
//        }
//
//        $list="<div class=\"row\">";
//        foreach ($mas as $key=>$row) {
//            $list.=$this->showSearchList($key, $row["art_id"], $row["article_nr_displ"], $row["brand_id"], $row["brand_name"], $row["article_name"], $row["price"], $row["stock"], $row["delivery_info"], $row["delivery_days"], $row["storage_id"], $row["suppl_id"]);
//        }
//        $list.="</div>";
//
//        return $list;
//    }

    // details
    function showSearchList($id, $art_id, $article_nr_displ, $brand_id, $brand_name, $article_name, $price, $stock, $delivery_info, $delivery_days, $storage_id, $suppl_id) {
        $showform=new FormClass; $language=new LangClass; $kours=new ExRateClass;
        $prefix=$language->getLangPrefix();
        $format_name=$this->getFormatAticle($article_nr_displ);
        $format_brand=$this->getFormatBrand($brand_name);
        $cur=$kours->getCurrentKours();
        $kours_cap=$kours->getKoursSymbol($cur);
        $form=$this->getHtmlForm("article_card");
        $form=str_replace("{product_i}",$id,$form);
        $form=str_replace("{art_id}",$art_id,$form);
        $form=str_replace("{brand_id}",$brand_id,$form);
        $form=str_replace("{product_name}",$article_nr_displ,$form);
        $form=str_replace("{product_brand}",$brand_name,$form);
        $form=str_replace("{product_format_name}",$format_name,$form);
        $form=str_replace("{product_lang_prefix}",$prefix,$form);
        $form=str_replace("{product_brand_link}",$this->getBrandLink($brand_id),$form);
        $form=str_replace("{product_format_brand}",$format_brand,$form);
        $form=str_replace("{product_text}",$article_name,$form);
        $form=str_replace("{product_price}",$price==0 ? "{sold_out_cap}" : $price." $kours_cap",$form);
        $form=str_replace("{product_true_price}",$price,$form);
        $form=str_replace("{product_button}",$price==0 ? "none" : "",$form);
        $form=str_replace("{product_stock}",$suppl_id==0 ? ($stock>10 ? ">10" : $stock) : $stock,$form);
        $form=str_replace("{product_real_stock}",$stock,$form);
        $form=str_replace("{product_storage_id}",$storage_id,$form);
        $form=str_replace("{product_suppl_id}",$suppl_id,$form);
        $form=str_replace("{prefix_url}",$language->getLangPrefix(),$form);
        $form=str_replace("{product_info}",$showform->getArticleInfoForm($art_id, 1),$form);
        $form=str_replace("{product_delivery_short_info}",str_replace("<br>", " ", $delivery_info),$form);
        $form=str_replace("{product_delivery_class}",($delivery_days==0) ? "delivery-red" : ($delivery_days==1 ? "delivery-blue" : ($delivery_days>1 ? "delivery-dark" : "")),$form);
        $form=str_replace("{product_image}",$showform->getArticleActivePhoto($art_id),$form);
        $form=str_replace("{product_title}","$article_name $brand_name $article_nr_displ",$form);
        $form=$this->replaceLang($form);
        return $form;
    }

    function getStrLinking($str_id) { $db = DbSingleton::getTokoDb();
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $list="<div class=\"details-offers__linking\">";
        $list.="<div><span>{popular_cap}</span> <i class='fa fa-angle-down'></i></div>";
        $list.="<div><ul>";

        $r=$db->query("SELECT * FROM `T_LINKING_PAGE` WHERE `STR_ID`='$str_id';"); $n=$db->num_rows($r);
        if ($n==0) {
            $this->initStrLinking($str_id);
            $r=$db->query("SELECT * FROM `T_LINKING_PAGE` WHERE `STR_ID`='$str_id';"); $n=$db->num_rows($r);
        }

        for ($i=1;$i<=$n;$i++) {
            $page_id = $db->result($r, $i-1, "PAGE_ID");
            $sort_id = $db->result($r, $i-1, "SORT_ID");
            list($page_link, $page_text) = $this->getStrLinkingPage($page_id);
            $link = $page_link."#".$page_id."-".$sort_id;
            $list.="<li><a href='https://toko.ua$prefix$link'>$page_text</a></li>";
        }

        $list.="</ul></div></div>";

        $list=$this->replaceLang($list);
        return $list;
    }

    function getStrLinkingPage($page_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT `LINK`, `TEXT` FROM `T_LINKING` WHERE `ID`='$page_id' LIMIT 1;");
        $link = $db->result($r, 0, "LINK");
        $text = $db->result($r, 0, "TEXT");
        return array($link, $text);
    }

    function initStrLinking($str_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ID` FROM `T_LINKING` ORDER BY RAND() LIMIT 6;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $page_id = $db->result($r, $i-1, "ID");
            $db->query("INSERT INTO `T_LINKING_PAGE` (`STR_ID`, `PAGE_ID`, `SORT_ID`) VALUES ('$str_id', '$page_id', '$i');");
        }
        return true;
    }

    function getSeoLinkingArticle() { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `ARTICLE_NR_DISPL` FROM `T2_ARTICLES` ORDER BY RAND() LIMIT 3;"); $n = $db->num_rows($r);
        $arts = [];
        for ($i=1; $i<=$n; $i++) {
            $article_nr_displ = $db->result($r, $i - 1, "ARTICLE_NR_DISPL");
            array_push($arts, $article_nr_displ);
        }

        return $arts;
    }

    function getSeoLinkingBrand() { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` ORDER BY RAND() LIMIT 2;"); $n = $db->num_rows($r);
        $brands = [];
        for ($i=1; $i<=$n; $i++) {
            $brand_id = $db->result($r, $i - 1, "BRAND_ID"); $brand_name = $this->getBrandName($brand_id);
            array_push($brands, $brand_name);
        }

        return $brands;
    }

    function getSeoArticleLinking($art_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `SEO_ART_STR` WHERE `ART_ID`='$art_id' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {

            $main_h1 = ""; // CATALOG PARRENT

            $text = $this->getArticleName($art_id);
            $brand_name = $this->getBrandName($this->getArticleBrand($art_id));
            $article_nr_displ = $this->getArticleDispl($art_id);

            $page_h1 = "$text $brand_name $article_nr_displ - {seo_title_article}";

            $dataProduct = $this->getSeoLinkingArticle();
            $product1 = $dataProduct[0];
            $product2 = $dataProduct[1];
            $product3 = $dataProduct[2];

            $dataBrand = $this->getSeoLinkingBrand();
            $tags_brand_1 = $dataBrand[0];
            $tags_brand_2 = $dataBrand[1];

//            $product1=$product2=$product3=$tags_brand_1=$tags_brand_2="";

            $geo_nominative = $this->getSeoLinkingParam("CITY", 1);
            $cat_random = $this->getSeoLinkingParam("CATEGORY", 1);
            $bigramma_random = $this->getSeoLinkingParam("GRAMMA", 1);

            $list = "{_still_search} $main_h1? {_go_store_toko} {_choose_best} : $product1, $product2 {or_cap} $product3. {_lowest_prices} $cat_random {_high_quality_category} $main_h1. {_cooperate} {_popular_brands} $tags_brand_1 {and_cap} $tags_brand_2, {_presented_in_section} $cat_random. {_most_reliable} $bigramma_random. {_right_choice} $page_h1. {_fast_order} $geo_nominative {_other_cities} .";

            $list = $this->replaceLang($list);
            $list = str_replace(str_split("{}"), "", $list);
            $list = explode(" ", $list);
            $seo_text = "";

            foreach ($list as $value) {
                $value = $this->getSeoListingValue($value);
                $seo_text.="$value ";
            }

//            $db->query("INSERT INTO `SEO_ART_STR` (`ART_ID`, `TEXT`) VALUES ('$art_id', '$seo_text');");

        } else {
            $seo_text = $db->result($r, 0, "TEXT");
        }

        return $seo_text;
    }

    function getSeoLinking($str_id, $h1, $filters, $brands) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `SEO_STR` WHERE `STR_ID`='$str_id' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $automan=new AutoClass; $kours=new ExRateClass;

            $head_id = $automan->getHeadStr($str_id);
            list($head_text) = $automan->getHeadNewDescr($head_id);
            $parrent_h1 = $head_text;

            $brand = $this->getBrandName(array_rand($brands));
            $cur = $kours->getCurrentKours();
            $kours_cap = $kours->getKoursCaptionLang($cur);

            $min_price = $filters["min_price"]." $kours_cap";
            $max_price = $filters["max_price"]." $kours_cap";

            $city = $this->getSeoLinkingParam("CITY", 3);
            $category = $this->getSeoLinkingParam("CATEGORY", 2);
            $ngramma = $this->getSeoLinkingParam("GRAMMA", 1);

            $list = "{_offer} {_buy} $h1 {_for} {_best_price} {_delivery} {_and} {_choose} $parrent_h1 {_such} {_models} {_example} $brand {_in} {_shop} {_toko} . {_call} {_manager} {_help} $ngramma {_for} {_best_price} {_from} $min_price {_to} $max_price {_exactly} {_auto} {_shop2} {_toko} {_bring_category} $category {_in_city} $city {_other_city}";

            $list = $this->replaceLang($list);
            $list = str_replace(str_split("{}"), "", $list);
            $list = explode(" ", $list);
            $seo_text = "";

            foreach ($list as $value) {
                $value = $this->getSeoListingValue($value);
                $seo_text.="$value ";
            }

            $db->query("INSERT INTO `SEO_STR` (`STR_ID`, `TEXT`) VALUES ('$str_id', '$seo_text');");

        } else {
            $seo_text = $db->result($r, 0, "TEXT");
        }

        return $seo_text;
    }

    function getSeoListingValue($value) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT `TEXT` FROM `SEO_LISTING` WHERE `LIST_KEY`='$value' ORDER BY RAND() LIMIT 1;"); $n=$db->num_rows($r);
        if ($n>0) {
            $name = $db->result($r, 0, "TEXT");
        } else {
            $name = $value;
        }
        return $name;
    }

    function getSeoLinkingParam($param, $count) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `SEO_LISTING_$param` ORDER BY RAND() LIMIT $count;"); $n=$db->num_rows($r);
        $params=[];
        for ($i=1;$i<=$n;$i++) {
            $param_name=$db->result($r, $i-1, $param."_NAME");
            array_push($params, $param_name);
        }
        $params=implode(", ",$params);
        return $params;
    }

    function getCatalogParamForm() {
        $form = $this->getHtmlForm("catalog/form");
        $form = str_replace("{catalog_range}", $this->getCatalogParamList(), $form);
        return $form;
    }

    function getCatalogParamList() { $db = DbSingleton::getTokoDb();
        $list = "<ul class='list-inline'>";
        $r = $db->query("SELECT * FROM `T2_TREE_GROUP` WHERE 1 ORDER BY `TEX_RU`;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $group_id = $db->result($r, $i-1, "GROUP_ID");
            $text = $db->result($r, $i-1, "TEX_RU");
            $list.="<li>
                <a href='https://toko.ua/test_catalog/$group_id'>$text</a>
            </li>";
        }
        $list.="</ul>";
        return $list;
    }

    /*==== TEST CATALOG ====*/

    function getCatalogParamGroup($group_id) {
        $form = $this->getHtmlForm("catalog/list");
        $data = $this->getCatalogParamGroupList($group_id);
        $form = str_replace("{catalog_range}", $data[0], $form);
        $form = str_replace("{catalog_params}", $data[1], $form);
        return $form;
    }

    function getCatalogParamGroupList($group_id) { $db = DbSingleton::getTokoDb();
        $list = "<table class='table'>
            <thead>
                <tr>
                    <td>ART_ID</td>
                    <td>ARTICLE</td>
                    <td>BRAND</td>
                    <td>NAME</td>
                </tr>
            </thead>
        ";
        $arr = [];
        $r = $db->query("SELECT * FROM `T2_TREE_ARTS` WHERE `GROUP_ID`='$group_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $db->result($r, $i-1, "ART_ID");
            $article_nr_displ = $this->getArticleDispl($art_id);
            $brand_name = $this->getBrandName($this->getArticleBrand($art_id));
            $text = $this->getArticleName($art_id);
            $arr = $this->getCatArtParams($art_id, $arr);

            $list.="<tr>
                <td>$art_id</td>
                <td>$article_nr_displ</td>
                <td>$brand_name</td>
                <td>$text</td>
            </tr>";
        }
        $list.="</table>";

        $params="";
        foreach ($arr as $param_id=>$mas) {
            $param_name = $this->getParamName($param_id);
            $params.="<b>$param_name:</b><br>";
            foreach ($mas as $param_value) {
                $params.="$param_value<br>";
            }
        }

        return array($list, $params);
    }

    function getCatArtParams($art_id, $arr) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PARAM_ID`, `PARAM_VALUE` FROM `T2_GOODS_GROUP_PARAMS_VALUE` WHERE `ART_ID`='$art_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $param_value = $db->result($r, $i - 1, "PARAM_VALUE");
            if (empty($arr[$param_id])) $arr[$param_id] = [];
            array_push($arr[$param_id], $param_value);
            $arr[$param_id] = array_unique($arr[$param_id]);
        }
        return $arr;
    }

    function getParamName($param_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `NAME` FROM `GOODS_GROUP_TEMPLATE_PARAMS` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        $param_name = $db->result($r, 0, "NAME");
        return $param_name;
    }


}