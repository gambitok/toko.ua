<?php

class ParametersClass {

    use Helper;
    use Variables;

    public $products_on_page=12;
    public $page_link="template";
    // $current_products = ALL products (NON Existed): ART_ID + PARAM_ID + VALUE_ID
    // $current_products_arts = ALL products (NON Existed): ART_ID
    // $active_products = FILTERED products (Existed): ART_ID
    // $active_filters = FILTERED filters: PARAM_ID + VALUE_ID
    // $status_filters = STATUS filters: 0 or 1

    function showProductsForm($template_id, $page=1, $link="") {

        $active_products = []; $status_filters = 0;

        $active_filters = $this->getTemplateLinkParams($template_id, $link);

        $current_products = $this->getCurrentProducts($template_id);

        if (!empty($active_filters)) {
            $status_filters = 1;
            $active_products = $this->getActiveProducts($current_products, $active_filters);
        }

        $form=$this->getHtmlForm("template/template");
        $form=str_replace("{template_id}", $template_id, $form);
        $form=str_replace("{template_page}", $page, $form);
        $form=str_replace("{template_link}", $link, $form);
        $form=str_replace("{template_active_filters}", json_encode($active_filters), $form);
        $form=str_replace("{template_name}", $this->getTemplateLink($template_id), $form);
        $form=str_replace("{template_title}", $this->showTemplateTitle($template_id, $active_filters), $form);
        $form=str_replace("{template_filters}","",$form);
        $form=str_replace("{template_count}","",$form);
        $form=str_replace("{template_checked}", "", $form);
        $form=str_replace("{template_pagination}","",$form);

        $products_form = $this->getProductsForm($template_id, $page, $current_products, $active_products, $status_filters);
        $form=str_replace("{template_products}", $products_form, $form);

        return $form;
    }

    function showTemplateTitle($template_id, $active_filters) {
        $name = $this->getTemplateName($template_id);
        $h1 = "<span class=\"span-red\">$name</span>";
        foreach ($active_filters as $param_id=>$values) {
            foreach ($values as $value_id) {
                if ($param_id==0) $value_name=$this->getBrandName($value_id); else $value_name=$this->getCatalogueValueName($value_id, $template_id);
                $h1.=" $value_name";
            }
        }
        $title="$h1";
        return $title;
    }

    function showCheckedFilters($template_id, $active_filters) {
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $title = "<div style=\"padding: 15px 0;\">";
        $template_name = $this->getTemplateLink($template_id);
        foreach ($active_filters as $param_id=>$values) {
            foreach ($values as $value_id) {
                if ($param_id==0) $value_name=$this->getBrandName($value_id); else $value_name=$this->getCatalogueValueName($value_id, $template_id);
                $new_link=$this->getTemplateFilterLink($active_filters, $param_id, $value_id, 1);
                $title.="<a class=\"btn btn-labeled btn-danger btn-xs\" style='margin-right: 15px;' href=\"https://toko.ua$prefix/$this->page_link/$template_name/$new_link\"><i class=\"fa fa-times\"></i> $value_name</a>";
            }
        }
        $title.="</div>";
        if (empty($active_filters)) $title="";
        return $title;
    }

    function getTemplateFilterLink($active_filters, $param_id, $value_id, $status=0) {
        $new_link = "";

        if ($status==1) {
            foreach ($active_filters as $param=>$values) {
                foreach ($values as $k=>$value) {
                    if ($value==$value_id) unset($active_filters[$param_id][$k]);
                }
            }
            if (empty($active_filters[$param_id])) unset($active_filters[$param_id]);
        } else {
            if (empty($active_filters[$param_id])) $active_filters[$param_id]=[];
            array_push($active_filters[$param_id], $value_id);
        }

        foreach ($active_filters as $param=>$values) {
            if ($param==0) {
                $brand_link="brandy";
                $new_link.="$brand_link/";
                foreach ($values as $value) {
                    $value_link=$this->getCatalogueBrandLink($value);
                    if ($value_link!="") $new_link.="$value_link/";
                }
            } else {
                $param_link=$this->getCatalogueParamLink($param);
                $new_link.="$param_link/";
                foreach ($values as $value) {
                    $value_link=$this->getCatalogueValueLink($value);
                    if ($value_link!="") $new_link.="$value_link/";
                }
            }
        }

        return $new_link;
    }

    function getProductsForm($template_id, $page, $current_products, $active_products, $status_filters) { $db = DbSingleton::getTokoDb();
        $cat = new CatalogueClass;
        $limit = $this->getSearchLimit($page);

        if ($status_filters) {
            if (!empty($active_products)) {
                // filter_products
                $arts = implode(",", $active_products);
                $where_arts=" AND t2c.ART_ID IN ($arts)";
            } else {
                // none products
                $where_arts=" AND t2c.ART_ID IN (0)";
            }
        } else {
            // all products
            $current_products_arts = implode(",", array_keys($this->getExistedProducts($current_products)));
            $where_arts=" AND t2c.ART_ID IN ($current_products_arts)";
        }

        $art_ids=[];
        $r=$db->query("SELECT t2a.`ART_ID` FROM `T2_CATALOGUES_ARTS` t2c
            LEFT JOIN `T2_ARTICLES` t2a ON (t2a.ART_ID=t2c.ART_ID)
        WHERE t2c.`TEMPLATE_ID`='$template_id' $where_arts
        GROUP BY t2a.`ART_ID` $limit;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $db->result($r, $i-1, "ART_ID");
            array_push($art_ids, $art_id);
        }
        $where_art_ids=implode(",",$art_ids);

        if ($where_art_ids=="") {
            $list_arts = $this->replaceLang($this->getHtmlForm("error/404_found"));
        } else {
            $where_art_ids = trim($where_art_ids, ",");
            list($list_arts) = $cat->searchList($where_art_ids, 1, 1);
        }

        return $list_arts;
    }

    function getCurrentProducts($template_id) { $db=DbSingleton::getTokoDb();
        $products = [];

        $r=$db->query("SELECT t2c.`ART_ID`, t2a.`BRAND_ID` 
        FROM `T2_CATALOGUES_ARTS` t2c 
            LEFT JOIN `T2_ARTICLES` t2a ON t2a.ART_ID=t2c.ART_ID
        WHERE t2c.`TEMPLATE_ID`='$template_id' GROUP BY t2c.`ART_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $brand_id=$db->result($r,$i-1,"BRAND_ID");
            if (empty($products[$art_id][0])) $products[$art_id][0]=[];
            $products[$art_id][0]=[$brand_id];
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            if (empty($products[$art_id][$param_id])) $products[$art_id][$param_id]=[];
            array_push($products[$art_id][$param_id], $value_id);
        }

        return $products;
    }

    function getActiveProducts($current_products, $active_filters) {
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

    function getExistedProducts($products) {
        $cat = new CatalogueClass;
        foreach ($products as $art_id=>$values) {
            $validate_art_count=0; $max_price_art=0;
            list($suppl_array, $storage_array, $stock_array, $last) = $this->getExistedSearchParams($art_id);
            for ($j=1;$j<=$last;$j++) {
                $suppl_id = $suppl_array[$j];
                $storage_id = $storage_array[$j];
                $stock = $stock_array[$j];
                if ($suppl_id==0) $price = $cat->getArticlePrice($art_id); else $price = $cat->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
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

    function getExistedSearchParams($art_id) { $db=DbSingleton::getTokoDb();
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

    function getActiveFilters($template_id, $current, $active) {
        $new_filters = [];
        $current_products = $this->getExistedProducts($this->getCurrentProducts($template_id));

        foreach ($current as $param_id=>$values) {
            if (empty($new_filters[$param_id])) $new_filters[$param_id]=[];
            if (!empty($active[$param_id])) {
                $new_filters[$param_id] = $this->getCheckParamFilters($current_products, $active, $param_id, 1);
            } else {
                $new_filters[$param_id] = $this->getCheckParamFilters($current_products, $active, $param_id);
            }
        }

        return $new_filters;
    }

    function getCheckParamFilters($products, $sep_filters, $param_id, $status=0) {
        if ($status) unset($sep_filters[$param_id]);
        $fproducts = []; $rfilters = [];
        foreach ($products as $art_id=>$params) { $count_params=0;
            foreach ($params as $par_id=>$values) { $count_values=0;
                foreach ($values as $value_id) {
                    if (in_array($value_id, $sep_filters[$par_id])) $count_values++;
                }
                if ($count_values>0) $count_params++;
            }
            if ($count_params==count($sep_filters)) {
                if (empty($fproducts[$art_id])) $fproducts[$art_id]=[];
                $fproducts[$art_id]=$products[$art_id];
            }
        }
        foreach ($fproducts as $art_id=>$params) {
            foreach ($params as $par_id=>$values) {
               if ($par_id==$param_id) {
                   if (empty($rfilters[$par_id])) $rfilters[$par_id]=[];
                   foreach ($values as $value_id) {
                       if (!in_array($value_id, $rfilters[$par_id])) array_push($rfilters[$par_id], $value_id);
                   }
               }
            }
        }
        return $rfilters[$param_id];
    }

    function showFiltersForm($template_id, $active_filters=[]) { $db = DbSingleton::getTokoDb();
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $template_name = $this->getTemplateLink($template_id);

        $current_products_arts = implode(",", array_keys($this->getExistedProducts($this->getCurrentProducts($template_id))));

        $where_arts="";
        if ($current_products_arts!="") {
            $where_arts=" AND t2c.ART_ID IN ($current_products_arts)";
        }

        $arr=[];
        $r=$db->query("SELECT t2a.`BRAND_ID` FROM `T2_CATALOGUES_ARTS` t2c
            LEFT JOIN `T2_ARTICLES` t2a ON (t2a.ART_ID=t2c.ART_ID)
        WHERE t2c.`TEMPLATE_ID`='$template_id' $where_arts 
        GROUP BY t2a.`BRAND_ID`;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $brand_id = $db->result($r, $i-1, "BRAND_ID");
            if (empty($arr[0])) $arr[0]=[];
            if (!in_array($brand_id, $arr[0])) array_push($arr[0], $brand_id);
        }

        $r=$db->query("SELECT t2c.`PARAM_ID`, t2c.`VALUE_ID` FROM `T2_CATALOGUES_ARTS` t2c
        WHERE t2c.`TEMPLATE_ID`='$template_id' $where_arts 
        GROUP BY t2c.`ART_ID`, t2c.`VALUE_ID`;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $param_id = $db->result($r, $i-1, "PARAM_ID");
            $value_id = $db->result($r, $i-1, "VALUE_ID");
            if (empty($arr[$param_id])) $arr[$param_id]=[];
            if (!in_array($value_id, $arr[$param_id])) array_push($arr[$param_id], $value_id);
        }

        $mas=[]; $amount_max=5; $amount_values=0; $filters_list="";

        $current_filters = $arr;
        //$active_filters = $this->getTemplateLinkParams($template_id, $link);
        $arr = $this->getActiveFilters($template_id, $current_filters, $active_filters);

        foreach ($arr as $param_id=>$values) {$i=0;
            if (empty($mas[$param_id])) $mas[$param_id]=[];
            foreach ($values as $value) {$i++;
                if (in_array($value, $active_filters[$param_id])) $checked=1; else $checked=0;
                if (empty($mas[$param_id][$i])) $mas[$param_id][$i]=[];
                if ($param_id==0) $value_name=$this->getBrandName($value); else $value_name=$this->getCatalogueValueName($value, $template_id);
                $mas[$param_id][$i] = ["value_id"=>$value, "value_name"=>$value_name, "checked"=>$checked];
            }
        }

        foreach ($mas as $param_id=>$values) {
            $vc_array_checked=[]; $vc_array_name=[];
            foreach ($values as $key => $row) {
                $vc_array_checked[$key] = $row["checked"];
                $vc_array_name[$key] = $row["value_name"];
            }
            array_multisort($vc_array_checked, SORT_DESC, $vc_array_name, SORT_ASC, $mas[$param_id]);
        }

        $values_list="";
        foreach ($mas as $param_id=>$values) {
            foreach ($values as $value) {
                $value_id=$value["value_id"];
                $value_name=$value["value_name"];
                $checked=$value["checked"];
                if ($checked) {$label="<i class=\"fa fa-check-square\"></i>"; $style="span-red"; $status=1;} else {$label="<i class=\"far fa-square\"></i>"; $style=""; $status=0;}
                if ($value_id>0) {
                    $new_link = $this->getTemplateFilterLink($active_filters, $param_id, $value_id, $status);
                    $amount_values++;
                    $values_list.="<li><a class=\"pointer $style\" style=\"font-size: 1em;\" href='https://toko.ua$prefix/$this->page_link/$template_name/$new_link/'>$label $value_name</a></li>";
                }
            }

            if (count($values)<=$amount_max) $style_more="height:auto;"; else $style_more="";
            if ($param_id==0) $param_name="{brands_cap}"; else $param_name=$this->getCatalogueParamName($param_id, $template_id);
            if (count($values)>0 && $amount_values>0) $filters_list.="<h2>$param_name</h2><ul id=\"param-$param_id\" class=\"list-inline template-list list-hide\" style=\"margin: 0; $style_more\">";

            $amount_values=$amount_values-$amount_max;
            if ($amount_values<=0) $link_more=""; else $link_more="<a class=\"pointer underline\" onclick=\"toggleListParams(this, $param_id);\"><span class=\"show\">{more_cap} $amount_values</span> <span class=\"none\">{hide_cap}</span></a>";
            $filters_list.="$values_list</ul>$link_more";
            $amount_values=0; $values_list="";
        }

        $filters_list=$this->replaceLang($filters_list);

        return array($filters_list);
    }

    function showFilterOptionsForm($template_id, $page=1, $active_filters=[]) {
        //$active_filters = $this->getTemplateLinkParams($template_id, $link);
        $active_products = $this->getActiveProducts($this->getCurrentProducts($template_id), $active_filters);
        $products_count = $this->getActiveProductsCount($template_id, $active_products);
        $checked_filters = $this->showCheckedFilters($template_id, $active_filters);
        if (!empty($active_filters) && empty($active_products)) {
            $current_page="";
            $pagination="";
        } else {
            $current_page = $this->getTemplateCurrentPage($products_count, $page);
            $pagination = $this->getTemplatePaginationForm($products_count, $page);
        }
        return array($current_page, $pagination, $checked_filters);
    }

    function getActiveProductsCount($template_id, $active_products) { $db = DbSingleton::getTokoDb();
        $where_arts="";
        if (!empty($active_products)) {
            $arts = implode(",", $active_products);
            $where_arts = " AND t2c.ART_ID IN ($arts)";
        }
        $r=$db->query("SELECT COUNT(*) as count_arts FROM ( 
            SELECT t2c.`ART_ID` FROM `T2_CATALOGUES_ARTS` t2c
            WHERE t2c.`TEMPLATE_ID`='$template_id' $where_arts
            GROUP BY t2c.`ART_ID`
        ) as AB;"); $products_count=$db->result($r,0,"count_arts")+0;
        return $products_count;
    }

    function getSearchLimit($page) {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        $off>=0 ? $limit = " LIMIT $count OFFSET $off" : $limit = "";
        return $limit;
    }

    function getTemplateCurrentPage($n, $page) {
        $max_page = $page * $this->products_on_page;
        $min_page = $max_page - $this->products_on_page + 1;
        if ($max_page>$n) $max_page=$n;
        if ($max_page==0) $range_page="0"; else $range_page="$min_page-$max_page";
        $list="{results_cap}: $range_page {of_cap} $n ({page_cap} $page)";
        $list=$this->replaceLang($list);
        return $list;
    }

    function getTemplatePaginationForm($n, $page) {
        $count = $this->products_on_page;
        $pages_count = ceil($n / $count);
        if ($n<$count) $pages_count=1;
        $pagination="";

        $min_count = 5;
        $max_count = $pages_count-$min_count+1;
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
                    <li class=\"page-item $disabled_pred\"><a class=\"page-link\" href=\"?page=$pred_page\"><i class='fa fa-chevron-left'></i> <span class='span-media'>{previous_cap}</span></a></li>
                    $pagination
                    <li class=\"page-item $disabled_next\"><a class=\"page-link\" href=\"?page=$next_page\"><span class='span-media'>{next_cap}</span> <i class='fa fa-chevron-right'></i></a></li>
                </ul>
            </nav>
        </div>";

        if ($pages_count==1) $list="";

        $list=$this->replaceLang($list);

        return $list;
    }

    function getTemplateLinkParams($template_id, $link) { $db=DbSingleton::getTokoDb();
        $params=["brandy"];

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_link=$db->result($r,$i-1,"PARAM_LINK");
            array_push($params, $param_link);
        }

        $arr=explode("/", $link); $values=[]; $cur_par="";
        foreach ($arr as $key=>$val) {
            if (in_array($val, $params)) {
                $values[$val]=[];
                $cur_par=$val;
            } else {
                $cur_val=$val;
                array_push($values[$cur_par], $cur_val);
            }
        }

        $values_ids=[];
        foreach ($values as $vpar=>$vval) {
            if ($vpar=="brandy") {
                $par_id=0;
                $values_ids[$par_id]=[];
                foreach ($vval as $vv) {
                    if ($vv!="") {
                        $val_id=$this->getCatalogueBrandID($vv);
                        array_push($values_ids[$par_id], $val_id);
                    }
                }
            } else {
                $par_id=$this->getCatalogueParamID($vpar, $template_id);
                $values_ids[$par_id]=[];
                foreach ($vval as $vv) {
                    if ($vv!="") {
                        $val_id=$this->getCatalogueValueID($vv, $par_id, $template_id);
                        array_push($values_ids[$par_id], $val_id);
                    }
                }
            }
        }

        foreach ($values_ids as $param=>$values) {
            foreach ($values as $key=>$value) {
                if ($value==0 || empty($value)) unset($values_ids[$param][$key]);
            }
            if ($values==0 || empty($values)) unset($values_ids[$param]);
        }
        if ($values_ids==0 || empty($values_ids)) $values_ids=[];

        return $values_ids;
    }

}