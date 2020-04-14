<?php

class PatternClass extends CatalogueClass {

    use Helper;
    use Variables;

    public $products_on_page=25;

    function initTemplateTable($template_id) { $db = DbSingleton::getTokoDb();

        $products=[];
        $r=$db->query("SELECT `ART_ID`, `PARAM_ID`, `VALUE_ID` FROM `T2_CATALOGUES_ARTS` 
        WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            if (empty($products[$art_id])) $products[$art_id]=[];
            if (empty($products[$art_id][$param_id])) $products[$art_id][$param_id]=[];
            array_push($products[$art_id][$param_id],$value_id);
        }

        $params="";
        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r,$i-1,"PARAM_ID");
            $params.="`param_$param_id` VARCHAR(50),";
        }

        $db->query("CREATE TABLE IF NOT EXISTS `XX_TABLE_TEMPLATE_$template_id` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `brand_id` INT(100),
            $params
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $count_add=0; $count_upd=0;
        foreach ($products as $art_id=>$params) {
            $r=$db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2asc.AMOUNT
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
            WHERE t2a.ART_ID IN ($art_id) AND (t2asc.AMOUNT!=NULL OR t2asc.AMOUNT!=0) 
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
            UNION ALL
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2si.stock_suppl
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
            WHERE t2a.ART_ID IN ($art_id) AND (t2si.stock_suppl!=NULL OR t2si.stock_suppl!=0)
            GROUP BY t2a.ART_ID, t2si.client_storage_id;"); $stock=$db->num_rows($r);

            $brand_id = $db->result($r,0,"BRAND_ID");

            $params_values=""; $params_column=""; $set_column="";
            foreach ($params as $param_id=>$values) { $params_arr = [];
                foreach ($values as $value_id) {
                    array_push($params_arr, $value_id);
                }
                $params_values .= "'".implode(",",$params_arr)."',";
                $params_column .= "`param_$param_id`,";

                $set_column .= "`param_$param_id`='".implode(",",$params_arr)."',";
            }
            $params_values=rtrim($params_values,",");
            $params_column=rtrim($params_column,",");
            $set_column=rtrim($set_column,",");

            if ($stock>0) {
                $r=$db->query("SELECT * FROM `XX_TABLE_TEMPLATE_$template_id` WHERE `art_id`='$art_id' LIMIT 1;"); $n=$db->num_rows($r);
                if ($n==0) {
                    $db->query("INSERT INTO `XX_TABLE_TEMPLATE_$template_id` (`art_id`, `brand_id`, $params_column) VALUES ('$art_id', '$brand_id', $params_values);");
                    $count_add++;
                } else {
                    $db->query("UPDATE `XX_TABLE_TEMPLATE_$template_id` SET $set_column WHERE `art_id`='$art_id' LIMIT 1;");
                    $count_upd++;
                }
            }
        }

        return array($count_add, $count_upd);
    }

    function getFiltersRequest($active_filters) {
        $where="";
        foreach ($active_filters as $param_id=>$values) {
            if ($param_id==0) {
                $where.=" AND `brand_id` IN (".implode(",",$values).")";
            } else {
                $vls=[];
                $where.=" AND `param_$param_id` IN (";
                foreach ($values as $value_id) {
                    array_push($vls,$value_id);
                }
                $where.="".implode(",",$vls).")";
            }
        }
        return $where;
    }

    function showProductsForm($template_id, $page=1, $link="") { $db = DbSingleton::getTokoDb(); $count_arts=0;
        $language=new LangClass;
        $r=$db->query("SHOW TABLES LIKE 'XX_TABLE_TEMPLATE_$template_id';"); $n=$db->num_rows($r);
        if ($n>0) {
            $form = $this->getHtmlForm("patterns");
            $active_filters = $this->getTemplateLinkParams($template_id, $link);
            $products = $this->getCurrentProducts($template_id, $page, $active_filters);
            $products_form = $this->getProductsForm($products);
            $count_arts = $this->getProductsCount($template_id,$active_filters);

            $form=str_replace("{products_list}",$products_form,$form);
            $form=str_replace("{products_name}",$this->getTemplateLink($template_id),$form);
            $form=str_replace("{products_title}",$this->showTemplateTitle($template_id, $active_filters),$form);
            $form=str_replace("{products_checked}",$this->showCheckedFilters($template_id, $active_filters),$form);

            $form=str_replace("{products_pagination}",$this->getTemplatePaginationForm($count_arts,$page),$form);
            $form=str_replace("{products_count}",$this->getTemplateCurrentPage($count_arts,$page),$form);
            $form=str_replace("{products_filters}",$this->showFiltersForm($template_id, $active_filters),$form);
            $form=str_replace("{products_lang_prefix}",$language->getLangPrefix(),$form);
        } else {
            $form = $this->getHtmlForm("error/404");
        }

        $form=$this->replaceLang($form);

        $count = $this->products_on_page;
        $pages_count = ceil($count_arts / $count);
        if ($count_arts<$count) $pages_count=1;

        return array($form, $pages_count);
    }

    function showTemplateTitle($template_id, $active_filters) {
        $name = $this->getTemplateName($template_id);
        $h1 = "$name";
        foreach ($active_filters as $param_id=>$values) {
            foreach ($values as $value_id) {
                if ($param_id==0) $value_name=$this->getBrandName($value_id); else $value_name=$this->getCatalogueValueName($value_id, $template_id);
                $h1.=" $value_name";
            }
        }
        $title="$h1";
        return $title;
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

    function showCheckedFilters($template_id, $active_filters) {
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $title = "<div style=\"padding: 15px 0;\">";
        $template_name = $this->getTemplateLink($template_id);
        foreach ($active_filters as $param_id=>$values) {
            foreach ($values as $value_id) {
                if ($param_id==0) $value_name=$this->getBrandName($value_id); else $value_name=$this->getCatalogueValueName($value_id, $template_id);
                $new_link=$this->getTemplateFilterLink($active_filters, $param_id, $value_id, 1);
                $title.="<a class=\"btn btn-labeled btn-danger btn-xs\" style='margin-right: 15px;' href=\"https://toko.ua$prefix/$this->products_link/$template_name/$new_link\"><i class=\"fa fa-times\"></i> $value_name</a>";
            }
        }
        $title.="</div>";
        if (empty($active_filters)) $title="";
        return $title;
    }

    function getProductsForm($products) {
        $where_arts=implode(",",array_keys($products));
        list($list)=$this->searchList($where_arts, 1, 1);
        return $list;
    }

    function showFiltersForm($template_id, $active_filters=[]) {
        $language=new LangClass; $prefix=$language->getLangPrefix();
        $template_name = $this->getTemplateLink($template_id);

        $mas=[]; $amount_max=5; $amount_values=0; $filters_list="";

        $current_filters = $this->getCurrentFilters($template_id);

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
                    $values_list.="<li><a class=\"pointer $style\" style=\"font-size: 1em;\" href=\"https://toko.ua$prefix/$this->products_link/$template_name/$new_link/\">$label $value_name</a></li>";
                }
            }

            if (count($values)<=$amount_max) $style_more="height:auto;"; else $style_more="";
            if ($param_id==0) $param_name="{brands_cap}"; else $param_name=$this->getCatalogueParamName($param_id, $template_id);
            if (count($values)>0 && $amount_values>0) $filters_list.="<div class=\"param-title\">$param_name</div><ul id=\"param-$param_id\" class=\"template-list list-inline list-hide\" style=\"margin: 0; $style_more\">";

            $amount_values=$amount_values-$amount_max;
            if ($amount_values<=0) $link_more=""; else $link_more="<a class=\"pointer underline\" onclick=\"toggleListParams(this, $param_id);\"><span class=\"show\">{more_cap} $amount_values</span> <span class=\"none\">{hide_cap}</span></a>";
            $filters_list.="$values_list</ul>$link_more";
            $amount_values=0; $values_list="";
        }

        $filters_list=$this->replaceLang($filters_list);

        return $filters_list;
    }

    function getActiveFilters($template_id, $current, $active) {
        $new_filters = [];
        $current_products = $this->getCurrentProducts($template_id);

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

    function getCurrentProducts($template_id, $page=0, $active_filters=[]) { $db = DbSingleton::getTokoDb();
        $limit=""; if ($page>0) $limit=$this->getSearchLimit($page);
        $where=""; if (!empty($active_filters)) $where=$this->getFiltersRequest($active_filters);
        $products=[];
        list($min,$max)=$this->getMinMaxParams($template_id);
        $r=$db->query("SELECT * FROM `XX_TABLE_TEMPLATE_$template_id` WHERE 1 $where $limit;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id = $db->result($r,$i-1,"art_id");
            $brand_id = $db->result($r,$i-1,"brand_id");
            if (empty($products[$art_id][0])) $products[$art_id][0]=[];
            array_push($products[$art_id][0],$brand_id);
            for($param_id=$min;$param_id<=$max;$param_id++) {
                $value_ids = $db->result($r,$i-1,"param_$param_id");
                $products[$art_id][$param_id]=explode(",",$value_ids);
            }
        }
        return $products;
    }

    function getCurrentFilters($template_id) { $db = DbSingleton::getTokoDb();
        $current_filters=[];  $current_filters[0]=[];
        list($min,$max)=$this->getMinMaxParams($template_id);
        $r=$db->query("SELECT * FROM `XX_TABLE_TEMPLATE_$template_id` WHERE 1;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $brand_id = $db->result($r,$i-1,"brand_id");
            array_push($current_filters[0],$brand_id);
            for($param_id=$min;$param_id<=$max;$param_id++) {
                $value_ids = $db->result($r,$i-1,"param_$param_id");
                $current_filters[$param_id]=explode(",",$value_ids);
            }
        }
        return $current_filters;
    }

    function getProductsCount($template_id, $active_filters) { $db = DbSingleton::getTokoDb();
        $where="";
        if (!empty($active_filters)) $where=$this->getFiltersRequest($active_filters);
        $r=$db->query("SELECT COUNT(`art_id`) as count_arts FROM `XX_TABLE_TEMPLATE_$template_id` WHERE 1 $where;");
        $count_arts=$db->result($r,0,"count_arts");
        return $count_arts;
    }

    function getMinMaxParams($template_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT MIN(`PARAM_ID`) as min_param, MAX(`PARAM_ID`) as max_param FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';");
        $min = $db->result($r,0,"min_param");
        $max = $db->result($r,0,"max_param");
        return array($min,$max);
    }

    function getSearchLimit($page) {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        $off>=0 ? $limit = " LIMIT $count OFFSET $off" : $limit = "";
        return $limit;
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


}