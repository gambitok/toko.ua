<?php

class CatalogExistClass extends CatalogueClass
{

    use Helper;
    use Variables;

    public $products_on_page = 25;

    public function getGroupExistId($group_link)
    {
        $db = DbSingleton::getTokoDb();
        $group_id = 0;
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE `TEX_LINK`='$group_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $group_id = $db->result($r, 0, "GROUP_ID");
        }
        return $group_id;
    }

    public function getGroupExistName($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $group_name = "";
        $r = $db->query("SELECT `TEX_RU` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID`='$group_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $group_name = $db->result($r, 0, "TEX_RU");
        }
        return $group_name;
    }

    public function getInitForm($group_id)
    {
        $result = $this->initPartsTable($group_id);
        return "<div class='content'>$result</div>";
    }

    /*
     * create group table
     * */
    public function initPartsTable($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";

        if ($this->checkTable($group_id) > 0) {
            $dbc->query("UPDATE `$table` SET `status`=0 WHERE 1;");
        }

        $arts = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_TREE_ARTS_EXIST` WHERE `GROUP_ID`='$group_id' GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[$i]["art_id"] = $art_id;
        }

        foreach ($arts as $key => $values) {
            $art_id = $values["art_id"];

            $r = $db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2asc.AMOUNT
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
            WHERE t2a.ART_ID IN ($art_id) AND (t2asc.AMOUNT!=NULL OR t2asc.AMOUNT!=0) 
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
            UNION ALL
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2si.stock_suppl
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
            WHERE t2a.ART_ID IN ($art_id) AND (t2si.stock_suppl!=NULL OR t2si.stock_suppl!=0)
            GROUP BY t2a.ART_ID, t2si.client_storage_id;");

            $stock = $db->num_rows($r);
            $brand_id = $db->result($r, 0, "BRAND_ID");

            $arts[$key]["brand_id"] = $brand_id;

            if ($stock == 0) {
                unset($arts[$key]);
            }
        }

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `brand_id` INT(100),
            `status` TINYINT(2),
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $count_add = 0;
        $count_upd = 0;
        foreach ($arts as $key => $values) {
            $art_id = $values["art_id"];
            $brand_id = $values["brand_id"];
            $r = $dbc->query("SELECT COUNT(`ART_ID`) as count_art FROM `$table` WHERE `ART_ID`='$art_id';");
            $n = $dbc->result($r, 0, "count_art") + 0;
            if ($n == 0) {
                $dbc->query("INSERT INTO `$table` (`art_id`, `brand_id`, `status`) VALUES ('$art_id', '$brand_id', 1);");
                $count_add++;
            } else {
                $dbc->query("UPDATE `$table` SET `status`=1 WHERE `ART_ID`='$art_id';");
                $count_upd++;
            }
        }

        $r = $dbc->query("SELECT COUNT(*) as count_nulls FROM `$table` WHERE `status`=0");
        $count_del = $dbc->result($r, 0, "count_nulls") + 0;
        $dbc->query("DELETE FROM `$table` WHERE `status`=0;");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    public function getInitMfaForm($group_id)
    {
        $result = $this->initPartsMfaTable($group_id);
        return "<div class='content'>$result</div>";
    }

    /*
     * init tree manufacturers
     * */
    public function initPartsMfaTable($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";

        $arts = [];
        $r = $db->query("SELECT tl.`ART_ID`, tm.MOD_MFA_ID, tm.Model 
        FROM `T2_LINKS` tl
            LEFT JOIN `T_types` tt ON tt.TYP_ID = tl.TYP_ID
            LEFT JOIN `T_models` tm ON tm.MOD_ID = tt.TYP_MOD_ID
        WHERE `ART_ID` IN (
          SELECT ex.`ART_ID`
          FROM toko_dba_cache.`$table` as ex
        )
        GROUP BY tl.ART_ID, tm.MOD_MFA_ID, tm.Model;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $mfa_id = $db->result($r, $i - 1, "MOD_MFA_ID");
            $model = $db->result($r, $i - 1, "Model");
            if ($mfa_id > 0) {
                if (empty($arts[$art_id])) {
                    $arts[$art_id] = [];
                }
                if (empty($arts[$art_id][$mfa_id])) {
                    $arts[$art_id][$mfa_id] = [];
                }
                $arts[$art_id][$mfa_id][] = $model;
            }
        }

        if ($this->checkTableMfa($group_id) > 0) {
            $dbc->query("UPDATE `$table_mfa` SET `status`=0 WHERE 1;");
        }

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table_mfa` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `mfa_id` INT(100),
            `model` VARCHAR(250),
            `status` TINYINT(2),
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $count_add = 0; $count_upd = 0;
        foreach ($arts as $art_id => $mfas) {
            foreach ($mfas as $mfa_id => $models) {
                foreach ($models as $model) {
                    $r = $dbc->query("SELECT COUNT(`art_id`) as count_art FROM `$table_mfa` WHERE `art_id`='$art_id' AND `mfa_id`='$mfa_id' AND `model`='$model';");
                    $n = $dbc->result($r, 0, "count_art") + 0;
                    if ($n == 0) {
                        $dbc->query("INSERT INTO `$table_mfa` (`art_id`, `mfa_id`, `model`, `status`) VALUES ('$art_id', '$mfa_id', '$model', 1);");
                        $count_add++;
                    } else {
                        $dbc->query("UPDATE `$table_mfa` SET `status`=1 WHERE `art_id`='$art_id';");
                        $count_upd++;
                    }
                }
            }
        }

        $r = $dbc->query("SELECT COUNT(*) as count_nulls FROM `$table_mfa` WHERE `status`=0");
        $count_del = $dbc->result($r, 0, "count_nulls") + 0;
        $dbc->query("DELETE FROM `$table_mfa` WHERE `status`=0;");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    /*
     * check exist of group table
     * */
    public function checkTable($group_id)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    public function checkTableMfa($group_id)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_MFA_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    /*
     * show `Parts` catalog
     * */
    public function showPartsForm($status = 0)
    {
        $form = $this->getHtmlForm("catalog_exist/form");
        $list = $this->showGroupExistList($status);
        $form = str_replace("{parts_name}", "{spare_parts_catalog_cap}", $form);
        $form = str_replace("{parts_list}", $list, $form);
        return $form;
    }

    /*
     * get TREE HCG LIST
     * */
    public function getGroupExistList($status)
    {
        $db = DbSingleton::getTokoDb();
        $arr = [];
        $r = $db->query("SELECT `HEAD_ID`, `CAT_ID`, `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE 1;");
        if ($status) {
            $r = $db->query("SELECT cs.`HEAD_ID`, cs.`CAT_ID`, he.`GROUP_ID` 
            FROM `T2_TREE_CONSTRUCTOR_STR` cs
                LEFT JOIN `T2_TREE_HCG_EXIST` he ON (he.HEAD_ID = cs.HEAD_ID AND he.CAT_ID = cs.CAT_ID)
                LEFT JOIN `T2_TREE_GROUP_EXIST` ge ON (ge.GROUP_ID = he.GROUP_ID)
            WHERE cs.`CAT_ID` > 0 AND ge.`STATUS` = 1;");
        }
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $head_id = $db->result($r, $i - 1, "HEAD_ID");
            $cat_id = $db->result($r, $i - 1, "CAT_ID");
            $group_id = $db->result($r, $i - 1, "GROUP_ID");
            if (empty($arr[$head_id])) {
                $arr[$head_id] = [];
            }
            if (empty($arr[$head_id][$cat_id])) {
                $arr[$head_id][$cat_id] = [];
            }
            $arr[$head_id][$cat_id][] = $group_id;
        }
        return $arr;
    }

    /*
     * show TREE HCG LIST
     * */
    public function showGroupExistList($status)
    {
        $list = "";
        $arr = $this->getGroupExistList($status);
        $list .= "<ul class='list-inline'>";
        foreach ($arr as $head_id => $cats) {
            $head_name = $this->getHeadRowName($head_id);
            $list .= "<li style='font-weight: bold; font-size: 20px; color: black;'>$head_name</li><li><ul class='list-inline' style='margin-bottom: 30px;'>";
            foreach ($cats as $cat_id => $groups) {
                $cat_name = $this->getCatRowName($cat_id);
                $list .= "<li style='font-weight: bold; font-size: 18px; color: blue;'>$cat_name</li><li><ul style='margin-bottom: 10px;'>";
                foreach ($groups as $group_id) {
                    $group_name = $this->getGroupRowName($group_id);
                    $group_link = $this->getGroupRowLink($group_id);
                    $check = $this->checkTable($group_id);
                    if ($check > 0) {
                        $check_form = "<span class='span-red'><i class='fa fa-edit'></i> UPDATE</span>";
                        $col = "($check)";
                    } else {
                        $check_form = "<span class='span-grey'><i class='fa fa-download'></i> CREATE</span>";
                        $col = "";
                    }
                    $check_mfa = $this->checkTableMfa($group_id);
                    if ($check_mfa > 0) {
                        $check_mfa_form = "<span class='span-red'><i class='fa fa-edit'></i> UPDATE</span>";
                        $col_mfa = "($check_mfa)";
                    } else {
                        $check_mfa_form = "<span class='span-grey'><i class='fa fa-download'></i> CREATE</span>";
                        $col_mfa = "";
                    }
                    $list .= "<li style='display:flex; justify-content: space-between;'>
                        <div style='width: 50%;'>
                            $group_name
                        </div>
                        <div style='width: 25%; text-align: right;'>
                            <a href='/catalog_exist/init/$group_link/'>$check_form</a>   
                            <a href='/catalog_exist/show/$group_link/'>ZAPCHASTI $col</a>  
                        </div>
                        <div style='width: 25%; text-align: right;'>
                            <a href='/catalog_exist/init_mfa/$group_link/'>$check_mfa_form</a>
                            <a href='/catalog_exist/show_mfa/$group_link/'>MACHINU $col_mfa</a>
                        </div>
                    </li>";
                }
                $list .= "</ul></li>";
            }
            $list .= "</ul></li>";
        }
        $list .= "</ul>";
        return $list;
    }

    /*
     * show `PARTS` form
     * */
    public function showPartsCatalogue($group_id, $page = 1, $filters = [])
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";

        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupExistName($group_id);

//        $where_brands = "";
//        if (!empty($brandy)) {
//            $brand_list = implode(",", $brandy);
//            if ($brand_list != "") {
//                $where_brands = "WHERE `brand_id` IN ($brand_list)";
//            }
//        }

        $checked_filters = $this->getCheckedFilters($group_id, $filters);
        if (!empty($checked_filters)) {
            var_dump($checked_filters);
        }

        $arts = [];
        $r = $dbc->query("SELECT * FROM `$table` $limit;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($arts, $art_id);
        }

        $art_id_str = implode(",", array_unique($arts));
        list($list, , $active_filters, , $brands) = $this->searchList($art_id_str, 1, 1);
        $count = $this->getPartsCount($group_id, []);
        $pagination_form = $this->getPartsPaginationForm($count, $page);

        $form = $this->getHtmlForm("catalog_exist/list");
        $form = str_replace("{parts_name}", $group_text, $form);
        $form = str_replace("{parts_list}", $list, $form);
        $form = str_replace("{parts_count}", $count, $form);
        $form = str_replace("{parts_pagination}", $pagination_form, $form);

        return array("form" => $form, "filters" => $active_filters, "brands" => $brands);
    }

    /*
     * get `Parts` form limit
     * */
    public function getSearchLimit($page)
    {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        return ($off >= 0) ? " LIMIT $count OFFSET $off" : "";
    }

    public function showPartsCatalogueForm($group_id)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $automan = new AutoClass();
        $table = "EX_TABLE_TREE_MFA_$group_id";

        $form = $this->getHtmlForm("catalog_exist/list");
        $group_text = $this->getGroupExistName($group_id);

        $list = "";
        $arts = [];
        $r = $dbc->query("SELECT * FROM `$table` WHERE 1;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            $mfa_id = $dbc->result($r, $i - 1, "mfa_id");
            $model = $dbc->result($r, $i - 1, "model");
            $arts[$art_id][$mfa_id][] = $model;
        }

        foreach ($arts as $art_id => $mfas) {
            $article_nr_displ = $automan->getArticleDispl($art_id);
            $list .= "<ul><li><b>$article_nr_displ:</b></li><li>";
            foreach ($mfas as $mfa_id => $models) {
                $mfa_name = $automan->getMfaBrand($mfa_id);
                $mfa_link = $automan->getMfaBrandLink($mfa_id);
                $list .= "<ul><li><a href='./$mfa_link'><b>$mfa_name:</b></a> ";
                foreach ($models as $model) {
                    $model_link = $automan->getModBrandLink($model);
                    $list .= "<a href='./$mfa_link/$model_link'>$model</a>; ";
                }
                $list .= "</li></ul>";
            }
            $list .= "</li></ul>";
        }

        $form = str_replace("{parts_name}", $group_text, $form);
        $form = str_replace("{parts_list}", $list, $form);
        $form = str_replace("{parts_count}", "-", $form);
        $form = str_replace("{parts_pagination}", "", $form);
        return $form;
    }

    public function showPartsCatalogueMfa($group_id, $mfa_id = 0, $model = "", $page = 1)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_MFA_$group_id";

        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupExistName($group_id);

        $where_mfa = "";
        if ($mfa_id > 0) {
            $where_mfa .= " AND `mfa_id`='$mfa_id'";
            if ($model != "") {
                $where_mfa .= " AND `model`='$model'";
            }
        }

        $arts = [];
        $r = $dbc->query("SELECT * FROM `$table` WHERE 1 $where_mfa $limit;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($arts, $art_id);
        }

        $art_id_str = implode(",", array_unique($arts));
        list($list, , $filters, , $brands) = $this->searchList($art_id_str, 1, 1);
        $count = $this->getPartsCountMfa($group_id, $mfa_id, $model);
        $pagination_form = $this->getPartsPaginationForm($count, $page);

        $form = $this->getHtmlForm("catalog_exist/list");
        $form = str_replace("{parts_name}", $group_text, $form);
        $form = str_replace("{parts_list}", $list, $form);
        $form = str_replace("{parts_count}", $count, $form);
        $form = str_replace("{parts_pagination}", $pagination_form, $form);

        return array("form" => $form, "filters" => $filters, "brands" => $brands);
    }

    public function getPartsCount($group_id, $brandy)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $where_brands = "";
        if (!empty($brandy)) {
            $brand_list = implode(",", $brandy);
            if ($brand_list != "") {
                $where_brands = "WHERE `brand_id` IN ($brand_list)";
            }
        }
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` $where_brands;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    public function getPartsCountMfa($group_id, $mfa_id, $model)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_MFA_$group_id";

        $where_mfa = "";
        if ($mfa_id > 0) {
            $where_mfa .= " AND `mfa_id`='$mfa_id'";
            if ($model != "") {
                $where_mfa .= " AND `model`='$model'";
            }
        }

        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1 $where_mfa;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    public function getPartsPaginationForm($n, $page)
    {
        $count = $this->products_on_page;
        $pages_count = ceil($n / $count);
        if ($n < $count) {
            $pages_count = 1;
        }
        $pagination = "";

        $min_count = 5;
        $max_count = $pages_count - $min_count + 1;
        $pred_page = $page - 1;
        $next_page = $page + 1;
        $disabled_pred = ($page == 1) ? "disabled" : "";
        $disabled_next = ($page == $pages_count) ? "disabled" :"";

        if ($pages_count > 5) {

            if ($page < $min_count) {
                for ($i = 1; $i <= $min_count; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    $pagination .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
                }
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

            if ($page > $max_count) {
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                for ($i = $max_count; $i <= $pages_count; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    $pagination .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
                }
            }

            if ($page >= $min_count && $page <= $max_count) {
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";

                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pred_page\">$pred_page</a></li>";
                $pagination .= "<li class=\"page-item active\"><a class=\"page-link\" href=\"?page=$page\">$page</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$next_page\">$next_page</a></li>";

                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

        } else {
            for ($i = 1; $i <= $pages_count; $i++) {
                $active = ($i == $page) ? "active" : "";
                $pagination .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
            }
        }

        $list = "<div class=\"row\">
            <nav aria-label=\"Page navigation\">
                <ul class=\"pagination\">
                    <li class=\"page-item $disabled_pred\"><a class=\"page-link\" href=\"?page=$pred_page\"><i class='fa fa-chevron-left'></i> <span class='span-media'>{previous_cap}</span></a></li>
                    $pagination
                    <li class=\"page-item $disabled_next\"><a class=\"page-link\" href=\"?page=$next_page\"><span class='span-media'>{next_cap}</span> <i class='fa fa-chevron-right'></i></a></li>
                </ul>
            </nav>
        </div>";

        if ($pages_count == 1) {
            $list = "";
        }

        $list = $this->replaceLang($list);

        return $list;
    }

//    public function getFiltredArts($group_id, $page, $filters)
//    {
//        $db = DbSingleton::getTokoDb();
//        $dbc = DbSingleton::getTokoCacheDb();
//
//        $table = "EX_TABLE_TREE_$group_id";
//        $limit = $this->getSearchLimit($page);
//
//        $arts = [];
//        $r = $dbc->query("SELECT * FROM `$table` $limit;");
//        $n = $dbc->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $art_id = $dbc->result($r, $i - 1, "art_id");
//            array_push($arts, $art_id);
//        }
//
//        return $arts;
//    }

/*
    T2_TREE_GROUP_EXIST - группы
    T2_TREE_PARAMS_EXIST - параметры
    T2_TREE_VALUE_EXIST - значение
    T2_TREE_ARTS_PARAMS_VALUE_EXIST - связка
 * */

    /*
     * T2_TREE_PARAMS_EXIST
     * get Params
     * */
    public function getGroupParamID($group_id, $param_link)
    {
        $db = DbSingleton::getTokoDb();
        $param_id = "";
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID`='$group_id' AND `PARAM_LINK`='$param_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $param_id = $db->result($r, 0, "PARAM_ID");
        }
        if ($param_link == "brandy") {
            $param_id = 0;
        }
        return $param_id;
    }
    public function getGroupParamName($param_id)
    {
        $db = DbSingleton::getTokoDb();
        $param_name = "";
        $r = $db->query("SELECT `PARAM_NAME` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $param_name = $db->result($r, 0, "PARAM_NAME");
        }
        if ($param_id == 0) {
            $param_name = "Brandy";
        }
        return $param_name;
    }
    public function getGroupParamLink($param_id)
    {
        $db = DbSingleton::getTokoDb();
        $param_link = "";
        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $param_link = $db->result($r, 0, "PARAM_LINK");
        }
        if ($param_id == 0) {
            $param_link = "brandy";
        }
        return $param_link;
    }

    /*
     * T2_TREE_VALUE_EXIST
     * get Values
     * */
    public function getGroupValueID($group_id, $param_id, $value_link)
    {
        $db = DbSingleton::getTokoDb();
        $value_id = "";
        $r = $db->query("SELECT `VALUE_ID` FROM `T2_TREE_VALUE_EXIST` WHERE `GROUP_ID`='$group_id' AND `PARAM_ID`='$param_id' AND `VALUE_LINK`='$value_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_id = $db->result($r, 0, "VALUE_ID");
        }
        if ($param_id == 0) {
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE `BRAND_LINK`='$value_link' LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $value_id = $db->result($r, 0, "BRAND_ID");
            }
        }
        return $value_id;
    }
    public function getGroupValueName($value_id, $param_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $value_name = "";
        $r = $db->query("SELECT `VALUE_NAME` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_name = $db->result($r, 0, "VALUE_NAME");
        }
        if ($param_id == 0) {
            $value_name = $this->getBrandName($value_id);
        }
        return $value_name;
    }
    public function getGroupValueLink($value_id, $param_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $value_link = "";
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_link = $db->result($r, 0, "VALUE_LINK");
        }
        if ($param_id == 0) {
            $value_link = $this->getBrandLink($value_id);
        }
        return $value_link;
    }

    public function getCheckedFilters($group_id, $filters)
    {
        $params = [];
        if (!empty($filters)) {
            $params_arr = explode(";", $filters);
            foreach ($params_arr as $params_item)
            {
                $params_item_str = explode("=", $params_item);
                $param_link = $params_item_str[0];
                $params_item_values = $params_item_str[1];
                $params_item_values_arr = explode(",", $params_item_values);
                foreach ($params_item_values_arr as $value_link)
                {
                    // $params[$param_link][] = $value_link;
                    $param_id = $this->getGroupParamID($group_id, $param_link);
                    $value_id = $this->getGroupValueID($group_id, $param_id, $value_link);
                    if ($value_id != 0) {
                        $params[$param_id][] = $value_id;
                    }
                }
            }
        }
        return $params;
    }

}