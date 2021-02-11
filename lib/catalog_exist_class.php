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
        $r = $db->query("SELECT `ART_ID` FROM `T2_TREE_ARTS_EXIST` WHERE `STR_ID`='$group_id' GROUP BY `ART_ID`;");
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

    public function getTypInfo($typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $mfa_id = 0; $model = "";
        $r = $db->query("SELECT tm.`MOD_MFA_ID`, tm.`Model` FROM `T_types` tt
            LEFT JOIN `T_models` tm ON tm.MOD_ID = tt.TYP_MOD_ID
        WHERE tt.TYP_ID = '$typ_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $mfa_id = $db->result($r, 0, "MOD_MFA_ID");
            $model = $db->result($r, 0, "Model");
        }
        return array($mfa_id, $model);
    }

    public function initPartsMfaTable($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";

        $where_arts = [];
        $r = $dbc->query("SELECT `art_id` FROM `$table` WHERE 1;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($where_arts, $art_id);
        }

        $where_arts = implode(",", $where_arts);

        $arts = [];
        $r = $db->query("SELECT `ART_ID`, `TYP_ID` FROM `T2_LINKS` WHERE `ART_ID` IN ($where_arts);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $typ_id = $db->result($r, $i - 1, "TYP_ID");
            list($mfa_id, $model) = $this->getTypInfo($typ_id);
            if (empty($arts[$art_id])) {
                $arts[$art_id] = [];
            }
            if (empty($arts[$art_id][$mfa_id])) {
                $arts[$art_id][$mfa_id] = [];
            }
            $arts[$art_id][$mfa_id][] = $model;
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

                    $r = $dbc->query("SELECT COUNT(`art_id`) as count_art FROM `$table_mfa` WHERE `art_id`='$art_id';");
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
//        foreach ($arts as $key => $values) {
//            $art_id = $values["art_id"];
//            $mfa_id = $values["mfa_id"];
//            $model = $values["model"];
//            $r = $dbc->query("SELECT COUNT(`art_id`) as count_art FROM `$table_mfa` WHERE `art_id`='$art_id';");
//            $n = $dbc->result($r, 0, "count_art") + 0;
//            if ($n == 0) {
//                $dbc->query("INSERT INTO `$table_mfa` (`art_id`, `mfa_id`, `model`, `status`) VALUES ('$art_id', '$mfa_id', '$model', 1);");
//                $count_add++;
//            } else {
//                $dbc->query("UPDATE `$table_mfa` SET `status`=1 WHERE `art_id`='$art_id';");
//                $count_upd++;
//            }
//        }

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
    public function showPartsForm()
    {
        $form = $this->getHtmlForm("parts/parts");
        $list = $this->showGroupExistList();
        $form = str_replace("{parts_name}", "{spare_parts_catalog_cap}", $form);
        $form = str_replace("{parts_list}", $list, $form);
        return $form;
    }

    /*
     * get TREE HCG LIST
     * */
    public function getGroupExistList()
    {
        $db = DbSingleton::getTokoDb();
        $arr = [];
        $r = $db->query("SELECT `HEAD_ID`, `CAT_ID`, `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE 1;");
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
    public function showGroupExistList()
    {
        $list = "";
        $arr = $this->getGroupExistList();
        $list .= "<ul>";
        foreach ($arr as $head_id => $cats) {
            $head_name = $this->getHeadRowName($head_id);
            $list .= "<li>$head_name</li><li><ul>";
            foreach ($cats as $cat_id => $groups) {
                $cat_name = $this->getCatRowName($cat_id);
                $list .= "<li>$cat_name</li><li><ul>";
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
                    $list .= "<li>
                        <a href='/catalog_exist/init/$group_link/'>
                            $check_form
                        </a>
                        <a href='/catalog_exist/show/$group_link/'>$group_name $col</a>
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
    public function showPartsCatalogue($group_id, $page = 1, $brandy = [])
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";

        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupExistName($group_id);

        $where_brands = "";
        if (!empty($brandy)) {
            $brand_list = implode(",", $brandy);
            if ($brand_list != "") {
                $where_brands = "WHERE `brand_id` IN ($brand_list)";
            }
        }

        $arts = [];
        $r = $dbc->query("SELECT * FROM `$table` $where_brands $limit;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($arts, $art_id);
        }

        $art_id_str = implode(",", array_unique($arts));
        list($list, , $filters, , $brands) = $this->searchList($art_id_str, 1, 1);

        $form = $this->getHtmlForm("parts/parts_list");
        $form = str_replace("{parts_name}", $group_text, $form);
        $form = str_replace("{parts_list}", $list, $form);

        return array("form" => $form, "filters" => $filters, "brands" => $brands);
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

}