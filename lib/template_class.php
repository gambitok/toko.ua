<?php

class TemplateClass extends CatalogueClass {

    use Helper;
    use Variables;

    public $products_on_page = 12;
    public $table_group = "AA_TABLE_GROUP_";


    /*
     * Create GROUP Tables (on CRON)
     * */
    function initGroupTable($group_id) { $db = DbSingleton::getTokoDb(); $dbc = DbSingleton::getTokoCacheDb();
        $table = $this->table_group.$group_id;
        $dbc->query("DROP TABLE `$table`;");

        $products = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_TREE_ARTS` WHERE `GROUP_ID`='$group_id' GROUP BY `ART_ID`;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $db->result($r, $i-1, "ART_ID");
            if (empty($products[$art_id])) $products[$art_id] = [];
        }

        $params_arr = [];
        $r = $db->query("SELECT `ART_ID`, `PARAM_ID`, `VALUE_ID` FROM `T2_TREE_ARTS_PARAMS_VALUE` WHERE `GROUP_ID`='$group_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $db->result($r, $i-1, "ART_ID");
            $param_id = $db->result($r, $i-1, "PARAM_ID");
            $value_id = $db->result($r, $i-1, "VALUE_ID");
            if (empty($products[$art_id][$param_id])) $products[$art_id][$param_id] = [];
            array_push($products[$art_id][$param_id], $value_id);
            if ($param_id>0) array_push($params_arr, $param_id);
        }

        $params_list = "";
        sort($params_arr);
        $params_arr = array_unique($params_arr);
        foreach ($params_arr as $param_id) {
            $params_list.="`param_$param_id` VARCHAR(50),";
        }

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `brand_id` INT(100),
            `status` TINYINT(2),
            $params_list
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $count_add = 0;
        foreach ($products as $art_id=>$params) {
            $brand_id = $this->getArticleBrand($art_id);
            $status = $this->searchRange($art_id);

            $params_values = ""; $params_column = "";
                foreach ($params as $param_id=>$values) {
                    if ($param_id>0) {
                        $params_arr = [];
                        foreach ($values as $value_id) {
                            array_push($params_arr, $value_id);
                        }
                        $params_values.="'".implode(",", $params_arr)."',";
                        $params_column.="`param_$param_id`,";
                    }
                }
                $params_values = rtrim($params_values, ",");
                $params_column = rtrim($params_column, ",");

                $r2 = $dbc->query("SELECT * FROM `$table` WHERE `art_id`='$art_id' LIMIT 1;"); $n = $dbc->num_rows($r2);
                if ($n==0) {
                    if ($params_column!="") $params_column = ", $params_column";
                    if ($params_values!="") $params_values = ", $params_values";
                    $dbc->query("INSERT INTO `$table` (`art_id`, `brand_id`, `status` $params_column) VALUES ('$art_id', '$brand_id', $status $params_values);");
                    $count_add++;
                }

        }

        return "TABLE: $table, ADDED: $count_add";
    }

    function checkArtPhoto($art_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT COUNT(`ART_ID`) as count_photos FROM `T2_PHOTOS` WHERE `ART_ID`='$art_id';");
        $count_photos = $db->result($r, 0, "count_photos");
        if ($count_photos==0) return false; else return true;
    }

    /*
     * Get Article Status
     * */
    function searchRange($art_id) { $db = DbSingleton::getTokoDb();
        $status = 0;
        // FROM TOKO
        $summ_amount = 0;
        $r = $db->query("SELECT `AMOUNT` FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID`='$art_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $amount = $db->result($r, $i-1, "AMOUNT");
            $summ_amount+=$amount;
        }
        if ($summ_amount>0) {
            $price = $this->getArticlePrice($art_id);
            if ($price>0) {
                $status = 1;
                if ($this->checkArtPhoto($art_id)) $status = 2;
            }
        }
        // FROM SUPPL
        else {
            $r = $db->query("SELECT `stock_suppl`, `suppl_id`, `client_storage_id` FROM `T2_SUPPL_IMPORT` WHERE `art_id`='$art_id';"); $n = $db->num_rows($r);
            for ($i=1; $i<=$n; $i++) {
                $amount = $db->result($r, $i-1, "stock_suppl");
                $suppl_id = $db->result($r, $i-1, "suppl_id");
                $storage_id = $db->result($r, $i-1, "client_storage_id");
                if ($amount>0) {
                    $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    if ($price>0) {
                        if ($this->getSuppLStorageVisible($suppl_id, $storage_id)) {
                            if ($this->checkArtPhoto($art_id)) $status = 2; else $status = 1;
                            break;
                        }
                    }
                }
            }
        }
        return $status;
    }

    /*
     * Get ARTICLES Limit
     * */
    function getSearchLimit($page) {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        $off>=0 ? $limit = " LIMIT $count OFFSET $off" : $limit = "";
        return $limit;
    }

    /*
     * Get CATALOG Form
     * */
    function getCatalogParamForm() {
        $form = $this->getHtmlForm("catalog/form");
        $form = str_replace("{catalog_range}", $this->getCatalogParamRange(), $form);
        return $form;
    }

    /*
     * Get CATALOG Range
     * */
    function getCatalogParamRange() { $db = DbSingleton::getTokoDb();
        $arr = [];
        $list = "";
        $r = $db->query("SELECT * FROM `T2_TREE_HCG` WHERE 1;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $head_id = $db->result($r, $i-1, "HEAD_ID");
            $cat_id = $db->result($r, $i-1, "CAT_ID");
            $group_id = $db->result($r, $i-1, "GROUP_ID");
            if (empty($arr[$head_id])) $arr[$head_id] = [];
            if (empty($arr[$head_id][$cat_id])) $arr[$head_id][$cat_id] = [];
            array_push($arr[$head_id][$cat_id], $group_id);
        }

        foreach ($arr as $head_id=>$cats) {
            $head_name = $this->getHeadName($head_id);
            $list.="<li><div><a>$head_name</a></div><ul>";
            foreach ($cats as $cat_id=>$groups) {
                $cat_name = $this->getCatName($cat_id);
                $list.="<li><div><a>$cat_name</a></div><ul>";
                foreach ($groups as $group_id) {
                    $group_name = $this->getGroupName($group_id);
                    $list.="<li><div><a href='https://toko.ua/test_catalog/$group_id'>$group_name</a></div></li>";
                }
                $list.="</ul></li>";
            }
            $list.="</ul></li>";
        }

        $form = $this->getHtmlForm("catalog/range");
        $form = str_replace("{catalog_range}", $list, $form);

        return $form;
    }

    /*
     * Get Active Filters IDS
     * */
    function getCheckedFilters($filters, $group_id) {
        $active_filters = [];

        foreach ($filters as $param=>$values) {
            // BRANDS
            if ($param=="brandy") {
                $active_filters[0] = [];
                foreach ($values as $brand) {
                    $brand_id = $this->getCatalogueBrandID($brand);
                    array_push($active_filters[0], $brand_id);
                }
            }
            // PARAMS
            else {
                $param_id = $this->getParamID($param, $group_id);
                $active_filters[$param_id] = [];
                foreach ($values as $value) {
                    $value_id = $this->getValueID($value, $param_id, $group_id);
                    array_push($active_filters[$param_id], $value_id);
                }
            }
        }
        return $active_filters;
    }

    /*
     * Get Active Filters WHERE
     * */
    function getFiltersWhere($filters) {
        $where = "";
        foreach ($filters as $param=>$values) {
            if ($param==0) {
                $where.=" AND `brand_id` IN (";
                foreach ($values as $value) {
                    $where.="$value, ";
                }
                $where = trim($where, ", ");
                $where.=")";
            } else {
                $where.=" AND `param_$param` IN (";
                foreach ($values as $value) {
                    $where.="$value, ";
                }
                $where = trim($where, ", ");
                $where.=")";
            }
        }
        return $where;
    }

    /*
     * Show Active Filters Form
     * */
    function showCheckedFilters($group_id, $active_filters) {
        $form = "<div style=\"padding: 15px 0;\">";
        foreach ($active_filters as $param=>$values) {
            foreach ($values as $value) {
                if ($param==0) {
                    $value_name = $this->getBrandName($value);
                } else {
                    $value_name = $this->getValueName($value);
                }
                $link = $this->getParamsListLink($active_filters, $param, $value);
                $form.="<a class=\"btn btn-labeled btn-danger btn-xs\" style='margin-right: 15px; margin-bottom: 15px;' href=\"/test_catalog/$group_id/$link\"><i class=\"fa fa-times\"></i> $value_name</a>";
            }
        }
        $form.="</div>";
        if (empty($active_filters)) $form = "";
        return $form;
    }

    /*
     * Get GROUP CATALOG form
     * */
    function getCatalogParamGroupForm($group_id, $page = 1, $filters = []) {
        $automan = new AutoClass;

        $auto_typ_id = $this->getCookieAuto();

        $checked_filters = $this->getCheckedFilters($this->getCatalogFilters($filters), $group_id);

        if (empty($filters)) {
            $active_filters = $this->getAllFilters($group_id);
        } else {
            $active_filters = $this->getActiveFilters($group_id, $checked_filters);
        }

        $data = $this->getCatalogParamGroupList($group_id, $page, $checked_filters, $auto_typ_id); // GROUP LIST

        $count_arts = $this->getGroupCount($group_id, $checked_filters, $auto_typ_id); // GROUP FULL COUNT

        $params = $this->getParamsList($group_id, $active_filters, $checked_filters); // PARAMS LIST SHOW

        $form = $this->getHtmlForm("catalog/list");
        $form = str_replace("{group_id}", $group_id, $form);
        $form = str_replace("{catalog_list}", $data["list"], $form);
        $form = str_replace("{catalog_params}", $params, $form);
        $form = str_replace("{catalog_title}", $this->getGroupName($group_id), $form);
        $form = str_replace("{catalog_amount}", $count_arts, $form);
        $form = str_replace("{catalog_amount_arts}", $this->products_on_page, $form);
        $form = str_replace("{catalog_page}", $page, $form);
        $form = str_replace("{catalog_pages}", $this->getPagesCount($count_arts), $form);
        $form = str_replace("{catalog_pagination}", $this->getPagination($count_arts, $page), $form);
        $form = str_replace("{catalog_checked_filters}", $this->showCheckedFilters($group_id, $checked_filters), $form);
        $form = str_replace("{catalog_params_auto}", $this->getParamsAuto($group_id, $auto_typ_id), $form);
        $form = str_replace("{catalog_auto}", "{choosen_auto}: ".($auto_typ_id!="" ? $automan->getCarDescription($auto_typ_id) : "-"), $form);

        return $form;
    }

    /*
     * Get ARTICLES Count
     * */
    function getGroupCount($group_id, $active_filters, $auto_typ_id) { $dbc = DbSingleton::getTokoCacheDb();
        $table = $this->table_group.$group_id;
        $count = 0;
        $where = $this->getFiltersWhere($active_filters);
        $r = $dbc->query("SHOW TABLES LIKE '$table';"); $n = $dbc->num_rows($r);
        if ($n>0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1 $where;");
            $n = $dbc->result($r, 0, "col_arts");
            $count = $n;
            if ($_SESSION["param-auto"]==2) {
                $count = 0;
                $r = $dbc->query("SELECT `art_id` FROM `$table` WHERE 1 $where;"); $n = $dbc->num_rows($r);
                for ($i=1; $i<=$n; $i++) {
                    $art_id = $dbc->result($r, $i-1, "art_id");
                    if ($this->checkT2Link($auto_typ_id, $art_id)) {
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    /*
     * Get GROUP CATALOG list
     * */
    function getCatalogParamGroupList($group_id, $page, $active_filters, $auto_typ_id) { $dbc = DbSingleton::getTokoCacheDb();
        $arts = [];
        $limit = $this->getSearchLimit($page);
        $where = $this->getFiltersWhere($active_filters);

        $r = $dbc->query("SELECT `art_id` FROM `AA_TABLE_GROUP_$group_id` WHERE 1 $where ORDER BY `status` DESC $limit;"); $n = $dbc->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $dbc->result($r, $i-1, "art_id");
            if ($_SESSION["param-auto"]==2) {
                if ($this->checkT2Link($auto_typ_id, $art_id)) {
                    array_push($arts, $art_id);
                }
            } else {
                array_push($arts, $art_id);
            }
        }

        $where_arts = implode(",", array_unique($arts));

        list($list) = $this->searchListTest($where_arts, 1, 1);

        return array("list"=>$list);
    }

    function getParamsList($group_id, $arr_params, $active_filters) {
        $params = "";
        $arr = [];

        foreach ($arr_params as $param_id=>$values) {
            $arr[$param_id] = [];
            foreach ($values as $value_id) {
                $param_id==0 ? $param_value = $this->getBrandName($value_id) : $param_value = $this->getValueName($value_id);
                $link = $this->getParamsListLink($active_filters, $param_id, $value_id);
                (in_array($value_id, $active_filters[$param_id])) ? $checked = 1 : $checked = 0;
                if ($value_id>0) $arr[$param_id][$value_id] = ['id'=>$value_id, 'name'=>$param_value, 'link'=>$link, 'status'=>$checked];
            }
        }

        foreach ($arr as $param_id=>$values) {
            $params_li = "";
            $param_id==0 ? $param_name = $this->replaceLang("{brands_cap}") : $param_name = $this->getParamName($param_id);

            $far_status = []; $far_name = [];
            foreach ($values as $key => $row) {
                $far_status[$key] = $row["status"];
                $far_name[$key] = $row["name"];
            }
            array_multisort($far_status, SORT_DESC, $far_name, SORT_ASC, $values);

            foreach ($values as $value_id=>$var) {
                // $id = $var["id"];
                $name = $var["name"];
                $link = $var["link"];
                $status = $var["status"];
                $status ? $checked = "<i class='fa fa-check-square'></i>" : $checked = "<i class='far fa-square'></i>";
                $params_li.="<li><a href=\"/test_catalog/$group_id/$link\">$checked $name</a></li>";
            }
            $params.="<div class=\"param-title\">$param_name:</div>
            <ul id=\"param-$param_id\" class=\"list-inline template-list list-hide\">
                $params_li
            </ul>";
            if (count($values)>5) {
                $params.="
                    <a class=\"pointer underline\" onclick=\"toggleListParams(this, $param_id);\">
                        <span class=\"show\">{more_cap}</span>
                        <span class=\"none\">{hide_cap}</span>
                    </a>
                ";
            }
        }
        return $params;
    }

    /*
     * Get PARAMS Link
     * */
    function getParamsListLink($active_filters, $param_id, $value_id) {
        // NO PARAMS
        if (!array_key_exists($param_id, $active_filters)) {
            $active_filters[$param_id] = [$value_id];
        }
        // WITH PARAM
        else {
            // + NO VALUE
            if (!in_array($value_id, $active_filters[$param_id])) {
                array_push($active_filters[$param_id], $value_id);
            }
            // + WITH VALUE
            else {
                foreach ($active_filters[$param_id] as $k=>$value) {
                    if ($value==$value_id) unset($active_filters[$param_id][$k]);
                }
            }
        }

        foreach ($active_filters as $param=>$values) {
            if (empty($values)) unset($active_filters[$param]);
        }

        $link = "";
        foreach ($active_filters as $param=>$values) {
            $param==0 ? $param_link = "brandy" : $param_link = $this->getParamLink($param);
            $link.="/$param_link=";
            foreach ($values as $value) {
                $param==0 ? $value_link = $this->getBrandLink($value) : $value_link = $this->getValueLink($value);
                $link.="$value_link,";
            }
            $link = rtrim($link, ",");
        }
        $link = ltrim($link, "/");

        return $link;
    }

    /*
     * Get Catalog Filters from URL
     * */
    function getCatalogFilters($filters) {
        $arr = [];
        foreach ($filters as $filter) {
            $string = explode("=", $filter);
            $param_id = $string[0];
            $values = explode(",", $string[1]);
            foreach ($values as $value_id) {
                if (empty($arr[$param_id])) $arr[$param_id] = [];
                array_push($arr[$param_id], $value_id);
            }
        }
        return $arr;
    }

    /*
     * Get PARAMETERS array
     * */
    function getCatArtParams($art_id) { $db = DbSingleton::getTokoDb();
        $arr = [];
        $r = $db->query("SELECT t2a.`PARAM_ID`, t2t.`PARAM_VALUE` 
        FROM `T2_TREE_ARTS_PARAMS_VALUE` t2a 
            LEFT JOIN `T2_TREE_VALUE` t2t ON (t2t.`VALUE_ID` = t2a.`VALUE_ID`)
        WHERE t2a.`ART_ID`='$art_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $param_value = $db->result($r, $i - 1, "PARAM_VALUE");
            if (empty($arr[$param_id])) $arr[$param_id] = [];
            array_push($arr[$param_id], $param_value);
            $arr[$param_id] = array_unique($arr[$param_id]);
        }
        return $arr;
    }

    /*
     * Get PARAMETERS text description
     * */
    function getParamsStr($arr) {
        $str = "";
        foreach ($arr as $param_id => $values) {
            $param_name = $this->getParamName($param_id);
            $str.="<b>$param_name:</b> ";
            foreach ($values as $value) {
                $str.="$value, ";
            }
            $str = trim($str, ", ");
            $str.="<br>";
        }
        return $str;
    }

    /*
     * Get PAGES COUNT
     * n - articles count
     * */
    function getPagesCount($n) {
        $count = $this->products_on_page;
        $pages_count = ceil($n / $count);
        if ($n<$count) $pages_count = 1;
        return $pages_count;
    }

    /*
     * Get PAGINATION Form
     * n - articles count
     * page - current page
     * */
    function getPagination($n, $page) {
        $count = $this->products_on_page;
        $pages_count = ceil($n / $count);
        if ($n<$count) $pages_count = 1;
        $pagination = "";
        $min_count = 5;
        $max_count = $pages_count - $min_count + 1;
        $pred_page = $page - 1;
        $next_page = $page + 1;
        if ($page==1) $disabled_pred = "disabled"; else $disabled_pred = "";
        if ($page==$pages_count) $disabled_next = "disabled"; else $disabled_next = "";

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

        $list = "<div class=\"row\">
            <nav aria-label=\"Page navigation\" class=\"img-center\" style='margin-top: 30px;'>
                <ul class=\"pagination\">
                    <li class=\"page-item $disabled_pred\"><a class=\"page-link\" href=\"?page=$pred_page\"><i class=\"fa fa-chevron-left\"></i> <span class=\"span-media\">{previous_cap}</span></a></li>
                    $pagination
                    <li class=\"page-item $disabled_next\"><a class=\"page-link\" href=\"?page=$next_page\"><span class=\"span-media\">{next_cap}</span> <i class=\"fa fa-chevron-right\"></i></a></li>
                </ul>
            </nav>
        </div>";
        if ($pages_count<=1) $list = "";

        return $list;
    }

    /*
     * Get PARAMS AUTO list
     * */
    function getParamsAuto($group_id, $auto_typ_id) { $db = DbSingleton::getTokoDb();
        $type = $_SESSION["param-auto"];
        $r = $db->query("SELECT `STATUS_AUTO` FROM `T2_TREE_GROUP` WHERE `GROUP_ID`='$group_id' LIMIT 1;");
        $status_auto = $db->result($r, 0, "STATUS_AUTO");

        if ($auto_typ_id=="") {
            $list = "";
        } else {
            if ($type==2) {
                $type2 = "<i class='fa fa-check-circle'></i>";
                $type1 = "<i class='far fa-circle'></i>";
            } else {
                $type1 = "<i class='fa fa-check-circle'></i>";
                $type2 = "<i class='far fa-circle'></i>";
            }
            $list = "
                <div class='param-title'>
                    {auto_cap}:
                </div>
                 <ul class='list-inline template-list'>
                    <li><a class='pointer' onclick='setParamsAuto($group_id, 1)'>$type1 {all_cap} {offer_pair_cap}</a></li>
                    <li><a class='pointer' onclick='setParamsAuto($group_id, 2)'>$type2 {auto_cap} {offer_pair_cap}</a></li>
                </ul>
            ";
        }

        if ($status_auto==1) $list = "";

        $list = $this->replaceLang($list);
        return $list;
    }

    function setParamsAuto($type) {
        session_start();
        $_SESSION["param-auto"] = $type;
        return true;
    }

    /*==== FILTER PARAMS ====*/

    function getAllFilters($group_id) { $db = DbSingleton::getTokoDb(); $dbc = DbSingleton::getTokoCacheDb();
        $params = [];
        $params[0] = [];

        $r = $db->query("SELECT COUNT(`PARAM_ID`) as count_params FROM `T2_TREE_PARAMS` WHERE 1;");
        $max_param = $db->result($r, 0, "count_params");

        $r = $dbc->query("SELECT * FROM `AA_TABLE_GROUP_$group_id` GROUP BY `brand_id`;"); $n = $dbc->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $brand_id = $dbc->result($r, $i - 1, "brand_id");
            array_push($params[0], $brand_id);

            for ($k=1; $k<=$max_param; $k++) {
                $values = $dbc->result($r, $i - 1, "param_$k");
                if ($values>0) {
                    $param_id = $k;
                    if (empty($params[$param_id])) $params[$param_id] = [];
                    foreach (explode(",", $values) as $value_id) {
                        array_push($params[$param_id], $value_id);
                    }
                    $params[$param_id] = array_unique($params[$param_id]);
                }
            }

        }
        return $params;
    }

    function getActiveFilters($group_id, $active_filters) {
        $new_filters = [];
//        $current_filters = $this->getCurrentFilters($group_id);
//        $current_products = $this->getCurrentProducts($group_id);

        list($current_filters, $current_products) = $this->getCurrentArticles($group_id);

        foreach ($current_filters as $param_id=>$values) {
            if (empty($new_filters[$param_id])) $new_filters[$param_id] = [];
            if (!empty($active_filters[$param_id])) {
                $new_filters[$param_id] = $this->getCheckParamFilters($current_products, $active_filters, $param_id, 1);
            } else {
                $new_filters[$param_id] = $this->getCheckParamFilters($current_products, $active_filters, $param_id);
            }
            if (empty($new_filters[$param_id])) unset($new_filters[$param_id]);
        }
        return $new_filters;
    }

    function getCurrentArticles($group_id) { $db = DbSingleton::getTokoDb(); $dbc = DbSingleton::getTokoCacheDb();
        $table = $this->table_group.$group_id;
        $current_filters = [];
        $current_products = [];

        $r = $db->query("SELECT COUNT(`PARAM_ID`) as count_params FROM `T2_TREE_PARAMS` WHERE 1;");
        $max_param = $db->result($r, 0, "count_params");

        $r = $dbc->query("SELECT * FROM `$table` WHERE 1;"); $n = $dbc->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $art_id = $dbc->result($r, $i-1, "art_id");
            $brand_id = $dbc->result($r, $i-1, "brand_id");

            if (empty($current_products[$art_id][0])) { $current_products[$art_id][0] = []; }
            if (empty($current_filters[$art_id][0])) { $current_filters[0] = [];  }

            array_push($current_products[$art_id][0], $brand_id);
            array_push($current_filters[0], $brand_id);

            for ($param_id=1; $param_id<=$max_param; $param_id++) {
                $value_ids = $dbc->result($r, $i-1, "param_$param_id");
                if ($value_ids!="") {
                    $current_products[$art_id][$param_id] = explode(",", $value_ids);
                    $current_filters[$param_id] = explode(",", $value_ids);
                }
            }
        }
        return array($current_filters, $current_products);
    }

//    function getCurrentFilters($group_id) { $db = DbSingleton::getTokoDb(); $dbc = DbSingleton::getTokoCacheDb();
//        $table = $this->table_group.$group_id;
//        $current_filters = [];  $current_filters[0] = [];
//
//        $r = $db->query("SELECT COUNT(`PARAM_ID`) as count_params FROM `T2_TREE_PARAMS` WHERE 1;");
//        $max_param = $db->result($r, 0, "count_params");
//
//        $r = $dbc->query("SELECT * FROM `$table` WHERE 1;"); $n = $dbc->num_rows($r);
//        for ($i=1; $i<=$n; $i++) {
//            $brand_id = $dbc->result($r, $i-1, "brand_id");
//            array_push($current_filters[0], $brand_id);
//            for ($param_id=1; $param_id<=$max_param; $param_id++) {
//                $value_ids = $dbc->result($r, $i-1, "param_$param_id");
//                if ($value_ids!="") $current_filters[$param_id] = explode(",", $value_ids);
//            }
//        }
//        return $current_filters;
//    }
//
//    function getCurrentProducts($group_id) { $db = DbSingleton::getTokoDb(); $dbc = DbSingleton::getTokoCacheDb();
//        $table = $this->table_group.$group_id;
//        $products = [];
//
//        $r = $db->query("SELECT COUNT(`PARAM_ID`) as count_params FROM `T2_TREE_PARAMS` WHERE 1;");
//        $max_param = $db->result($r, 0, "count_params");
//
//        $r = $dbc->query("SELECT * FROM `$table` WHERE 1;"); $n = $dbc->num_rows($r);
//        for ($i=1; $i<=$n; $i++) {
//            $art_id = $dbc->result($r, $i-1, "art_id");
//            $brand_id = $dbc->result($r, $i-1, "brand_id");
//            if (empty($products[$art_id][0])) $products[$art_id][0] = [];
//            array_push($products[$art_id][0], $brand_id);
//            for ($param_id=1; $param_id<=$max_param; $param_id++) {
//                $value_ids = $dbc->result($r, $i-1, "param_$param_id");
//                if ($value_ids!="") $products[$art_id][$param_id] = explode(",", $value_ids);
//            }
//        }
//        return $products;
//    }

    function getFiltersRequest($active_filters) {
        $where = "";
        foreach ($active_filters as $param_id=>$values) {
            if ($param_id==0) {
                $where.=" AND `brand_id` IN (".implode(",",$values).")";
            } else {
                $vls = [];
                if (!empty($values)) {
                    $where.=" AND `param_$param_id` IN (";
                    foreach ($values as $value_id) {
                        array_push($vls, $value_id);
                    }
                    $where.="".implode(",",$vls).")";
                }
            }
        }
        return $where;
    }

    function getCheckParamFilters($products, $sep_filters, $param_id, $status=0) {
        if ($status) unset($sep_filters[$param_id]);
        $fproducts = []; $rfilters = [];
        foreach ($products as $art_id=>$params) { $count_params = 0;
            foreach ($params as $par_id=>$values) { $count_values = 0;
                foreach ($values as $value_id) {
                    if (in_array($value_id, $sep_filters[$par_id])) $count_values++;
                }
                if ($count_values>0) $count_params++;
            }
            if ($count_params==count($sep_filters)) {
                if (empty($fproducts[$art_id])) $fproducts[$art_id] = [];
                $fproducts[$art_id] = $products[$art_id];
            }
        }
        foreach ($fproducts as $art_id=>$params) {
            foreach ($params as $par_id=>$values) {
                if ($par_id==$param_id) {
                    if (empty($rfilters[$par_id])) $rfilters[$par_id] = [];
                    foreach ($values as $value_id) {
                        if (!in_array($value_id, $rfilters[$par_id])) array_push($rfilters[$par_id], $value_id);
                    }
                }
            }
        }
        return $rfilters[$param_id];
    }

}