<?php

class CatalogExistClass extends CatalogueClass
{

    use Helper;
    use Variables;

    public $products_on_page = 25;

    /*
     * GROUP EXIST
     * */
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

    /*
     * PARAMS EXIST
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
            $param_name = "{brands_cap}";
        }
        return $param_name;
    }
    public function getGroupParamLink($param_id)
    {
        $db = DbSingleton::getTokoDb();
        $param_name = "";
        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID`='$param_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $param_name = $db->result($r, 0, "PARAM_LINK");
        }
        if ($param_id == 0) {
            $param_name = "brandy";
        }
        return $param_name;
    }

    /*
     * VALUE EXIST
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
        $value_name = "";
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_name = $db->result($r, 0, "VALUE_LINK");
        }
        if ($param_id == 0) {
            $value_name = $this->getBrandLink($value_id);
        }
        return $value_name;
    }
    public function getGroupValueH1($value_id, $param_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $value_h1 = "";
        $r = $db->query("SELECT `VALUE_H1_RU` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID`='$value_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_h1 = $db->result($r, 0, "VALUE_H1_RU");
        }
        if ($param_id == 0) {
            $value_h1 = "";
        }
        return $value_h1;
    }

    /*
     * get products limit
     * */
    public function getSearchLimit($page)
    {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        return ($off >= 0) ? " LIMIT $count OFFSET $off" : "";
    }

    /*======================================================================= PRODUCTS PARAMS =*/
    /*
     * show products params init form
     * */
    public function getInitParamsForm($group_id)
    {
        $result = $this->initPartsParamsTable($group_id);
        return "<div class='content'>$result</div>";
    }

    /*
     * check exist of group params table
     * */
    public function checkTableParams($group_id)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_PARAMS_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    /*
     * init products params cache
     * */
    public function initPartsParamsTable($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_PARAMS_$group_id";

        if ($this->checkTableParams($group_id) > 0) {
            $dbc->query("UPDATE `$table` SET `status`=0 WHERE 1;");
        }

        $params_str = "";
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID`='$group_id';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $params_str .= "`param_$param_id` VARCHAR(50),";
        }

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `brand_id` INT(100),
            `status` TINYINT(2),
            $params_str
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $products = [];
        $r = $db->query("SELECT t2a.`ART_ID`, t2p.`PARAM_ID`, t2p.`VALUE_ID` 
        FROM `T2_TREE_ARTS_EXIST` t2a
            LEFT JOIN `T2_TREE_ARTS_PARAMS_VALUE_EXIST` t2p ON t2p.ART_ID=t2a.ART_ID
        WHERE t2a.`GROUP_ID`='$group_id';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $value_id = $db->result($r, $i - 1, "VALUE_ID");
            $products[$art_id][0] = 0;
            if (empty($products[$art_id])) {
                $products[$art_id] = [];
            }
            if (empty($products[$art_id][$param_id])) {
                $products[$art_id][$param_id] = [];
            }
            if ($param_id > 0) {
                array_push($products[$art_id][$param_id], $value_id);
            }
        }

        foreach ($products as $art_id => $params) {
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

            $products[$art_id][0] = $brand_id;

            if ($stock == 0) {
                unset($products[$art_id]);
            }
        }

        $count_add = 0;
        $count_upd = 0;

        foreach ($products as $art_id => $params) {
            $params_values = "";
            $params_column = "";
            $set_column = "";
            $brand_id = $products[$art_id][0];
            foreach ($params as $param_id => $values) {
                $params_arr = [];
                foreach ($values as $value_id) {
                    array_push($params_arr, $value_id);
                }
                if ($param_id > 0) {
                    $params_values .= "'" . implode(",", $params_arr) . "',";
                    $params_column .= "`param_$param_id`,";
                    $set_column .= "`param_$param_id`='" . implode(",", $params_arr) . "',";
                }
            }
            $params_values = rtrim($params_values, ","); if ($params_values != "") $params_values = ", " . $params_values;
            $params_column = rtrim($params_column, ","); if ($params_column != "") $params_column = ", " . $params_column;
            $set_column = rtrim($set_column, ","); if ($set_column != "") $set_column = ", " . $set_column;

            $r = $dbc->query("SELECT * FROM `$table` WHERE `art_id`='$art_id' LIMIT 1;");
            $n = $dbc->num_rows($r);
            if ($n == 0) {
                $dbc->query("INSERT INTO `$table` (`art_id`, `brand_id`, `status` $params_column) VALUES ('$art_id', '$brand_id', 1 $params_values);");
                $count_add++;
            } else {
                $dbc->query("UPDATE `$table` SET `status`=1 $set_column WHERE `art_id`='$art_id' LIMIT 1;");
                $count_upd++;
            }
        }

        $r = $dbc->query("SELECT COUNT(*) as count_nulls FROM `$table` WHERE `status`=0");
        $count_del = $dbc->result($r, 0, "count_nulls") + 0;
        $dbc->query("DELETE FROM `$table` WHERE `status`=0;");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    /*======================================================================= PRODUCTS =*/
    /*
     * show products init form
     * */
    public function getInitForm($group_id)
    {
        $result = $this->initPartsTable($group_id);
        return "<div class='content'>$result</div>";
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

    /*======================================================================= PRODUCTS MFA =*/
    /*
     * show products mfa init form
     * */
    public function getInitMfaForm($group_id)
    {
        $result = $this->initPartsMfaTable($group_id);
        return "<div class='content'>$result</div>";
    }

    /*
     * check exist of group mfa table
     * */
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
     * init group mfa
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
     * show products catalog
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
                    $check_params = $this->checkTableParams($group_id);
                    if ($check_params > 0) {
                        $check_params_form = "<span class='span-red'><i class='fa fa-edit'></i> UPDATE</span>";
                        $col_params = "($check_params)";
                    } else {
                        $check_params_form = "<span class='span-grey'><i class='fa fa-download'></i> CREATE</span>";
                        $col_params = "";
                    }
                    $list .= "<li style='display:flex; justify-content: space-between;'>
                        <div style='width: 40%;'>
                            $group_name
                        </div>
                        <div style='width: 20%; text-align: right;'>
                            <a href='/catalog_exist/init/$group_link/'>$check_form</a>   
                            <a href='/catalog_exist/show/$group_link/'>ZAPCHASTI $col</a>  
                        </div>
                        <div style='width: 20%; text-align: right;'>
                            <a href='/catalog_exist/init_mfa/$group_link/'>$check_mfa_form</a>
                            <a href='/catalog_exist/show_mfa/$group_link/'>MACHINU $col_mfa</a>
                        </div>
                        <div style='width: 20%; text-align: right;'>
                            <a href='/catalog_exist/init_params/$group_link/'>$check_params_form</a>
                            <a href='/catalog_exist/show_params/$group_link/'>PARAMS $col_params</a>
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
     * show products form
     * */
    public function showPartsCatalogue($group_id, $page = 1)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";

        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupExistName($group_id);

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
     * show cars form
     * */
    public function showCarsCatalogue($group_id)
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

    public function getPartsCount($group_id, $filters)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            if (empty($filters)) {
                $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table`;");
            } else {
                $where = $this->getFiltersWhere($group_id, $filters);
                $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table_params` WHERE 1 $where;");
            }
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    public function getPartsCountWill($group_id, $filters, $sel_param_id, $sel_value_id)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $where = $this->getFiltersWhereWill($group_id, $filters, $sel_param_id, $sel_value_id);
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table_params` WHERE 1 $where;");
            $n = $dbc->result($r, 0, "col_arts");
        }
        return $n;
    }

    public function getFiltersWhereWill($group_id, $filters, $sel_param_id, $sel_value_id)
    {
        $params = $this->getCheckedFilters($group_id, $filters);
        $params[$sel_param_id][] = $sel_value_id;
        $where = "";
        foreach ($params as $param_id => $values) {
            $param_name = ($param_id == 0) ? "brand_id" : "param_$param_id";
            if (!empty($values)) {
                $where .= " AND (";
                $count = 0 ;
                foreach ($values as $value_id) {
                    $count++;
                    $separator = ($count > 1) ? "OR" : "";
                    $where .= " $separator (`$param_name` = '$value_id' OR `$param_name` LIKE '%,$value_id%' OR `$param_name` LIKE '%$value_id,%')";
                }
                $where .= ") ";
            }
        }
        return $where;
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

    public function getParamsValuesName($params)
    {
        $str = [];
        foreach ($params as $param_id => $values) {
            $param_name = $this->getGroupParamName($param_id);
            foreach ($values as $value_id) {
                $value_name = $this->getGroupValueName($value_id, $param_id);
                $str[$param_name][] = $value_name;
            }
        }
        return $str;
    }

    public function getFiltersWhere($group_id, $filters)
    {
        $params = $this->getCheckedFilters($group_id, $filters);
        $where = "";
        foreach ($params as $param_id => $values) {
            $param_name = ($param_id == 0) ? "brand_id" : "param_$param_id";
            if (!empty($values)) {
                $where .= " AND (";
                $count = 0 ;
                foreach ($values as $value_id) {
                    $count++;
                    $separator = ($count > 1) ? "OR" : "";
                    $where .= " $separator (`$param_name` = '$value_id' OR `$param_name` LIKE '%,$value_id%' OR `$param_name` LIKE '%$value_id,%')";
                }
                $where .= ") ";
            }
        }
        return $where;
    }

    public function showPartsCatalogueParams($group_id, $page = 1, $filters = [])
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupExistName($group_id);

        $arts = [];

        if (empty($filters)) {
            $r = $dbc->query("SELECT * FROM `$table` $limit;");
        } else {
            $where = $this->getFiltersWhere($group_id, $filters);
            $r = $dbc->query("SELECT * FROM `$table_params` WHERE 1 $where $limit;");
        }

        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($arts, $art_id);
        }

        $art_id_str = implode(",", array_unique($arts));
        list($list, , $active_filters, , $brands) = $this->searchList($art_id_str, 1, 1);
        $count = $this->getPartsCount($group_id, $filters);
        $pagination_form = $this->getPartsPaginationForm($count, $page);
        $filters_form = $this->getPartsFiltersForm($group_id, $filters);
        list($filters_title, $filters_btn) = $this->getPartsFiltersItems($group_id, $filters);

        $form = $this->getHtmlForm("catalog_exist/list_params");
        $form = str_replace("{parts_name}", $group_text, $form);
        $form = str_replace("{parts_list}", $list, $form);
        $form = str_replace("{parts_title}", "$filters_title", $form);
        $form = str_replace("{parts_count}", "($count {offer_tenths_cap})", $form);
        $form = str_replace("{parts_filters}", "$filters_btn", $form);
        $form = str_replace("{parts_pagination}", $pagination_form, $form);
        $form = str_replace("{parts_params}", $filters_form, $form);

        return array("form" => $form, "filters" => $active_filters, "brands" => $brands);
    }

    public function getPartsFiltersItems($group_id, $filters)
    {
        $filters_btn = "";
        $filters_title = $this->getGroupExistName($group_id);
        if (!empty($filters)) {
            $count_vals = 0; $value_id = 0; $param_id = 0;
            $params_check = $this->getCheckedFilters($group_id, $filters);
            foreach ($params_check as $param_id => $values) {
                foreach ($values as $value_id) {
                    $count_vals++;
                    $value_name = $this->getGroupValueName($value_id, $param_id);
                    $link = $this->getPartsFilterLinks($group_id, $filters, $param_id, $value_id);
                    $filters_btn .= "<a href='$link' class='btn btn-sm'>$value_name <i class='fa fa-times'></i></a>";
                }
            }
            if ($count_vals == 1) {
                $value_h1 = $this->getGroupValueH1($value_id, $param_id);
                if ($value_h1 != "") {
                    $filters_title = $value_h1;
                } else {
                    $value_name = $this->getGroupValueName($value_id, $param_id);
                    $filters_title .= " $value_name";
                }
            }
        }
        return array($filters_title, $filters_btn);
    }

    public function getGroupExistParams($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $params = [];
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID`='$group_id';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $params[] = $param_id;
        }
        return $params;
    }

    public function getFiltersWhereSelected($group_id, $filters, $sel_param_id)
    {
        $params = $this->getCheckedFilters($group_id, $filters);
        $where = "";
        foreach ($params as $param_id => $values) {
            if ($sel_param_id != $param_id) {
                $param_name = ($param_id == 0) ? "brand_id" : "param_$param_id";
                if (!empty($values)) {
                    $where .= " AND (";
                    $count = 0 ;
                    foreach ($values as $value_id) {
                        $count++;
                        $separator = ($count > 1) ? "OR" : "";
                        $where .= " $separator (`$param_name` = '$value_id' OR `$param_name` LIKE '%,$value_id%' OR `$param_name` LIKE '%$value_id,%')";
                    }
                    $where .= ") ";
                }
            }
        }
        return $where;
    }

    public function getPartsFiltersForm($group_id, $filters)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_PARAMS_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $max_items = 7;

        $count_arts_full = $this->getPartsCount($group_id, $filters);

        $params_check = $this->getCheckedFilters($group_id, $filters);

        $exist_params = $this->getGroupExistParams($group_id);

        $params = []; $checked_params_keys = []; $unchecked_params_keys = [];

        if (empty($filters)) {
            $r = $dbc->query("SELECT * FROM `$table`;");
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $brand_id = $dbc->result($r, $i - 1, "brand_id");
                $params[0][] = $brand_id;
                foreach($exist_params as $param_id) {
                    $value_str = $dbc->result($r, $i - 1, "param_$param_id");
                    if (!empty($value_str)) {
                        foreach (explode(",", $value_str) as $item) {
                            $params[$param_id][] = $item;
                        }
                    }
                }
            }
        } else {
            $checked_params_keys = array_keys($params_check);
            $existed_params_keys = array_keys($exist_params);
            $unchecked_params_keys = array_diff($existed_params_keys, $checked_params_keys);

            foreach ($checked_params_keys as $param_id) {
                $where = $this->getFiltersWhereSelected($group_id, $filters, $param_id);
                $value_arr = [];
                $r = $dbc->query("SELECT * FROM `$table_params` WHERE 1 $where;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    if ($param_id == 0) {
                        $value_str = $dbc->result($r, $i - 1, "brand_id");
                    } else {
                        $value_str = $dbc->result($r, $i - 1, "param_$param_id");
                    }
                    if (!empty($value_str)) {
                        foreach (explode(",", $value_str) as $item) {
                            $value_arr[] = $item;
                        }
                    }
                }
                $params[$param_id] = $value_arr;
            }

            foreach ($unchecked_params_keys as $param_id) {
                $where = $this->getFiltersWhere($group_id, $filters);
                $value_arr = [];
                $r = $dbc->query("SELECT * FROM `$table_params` WHERE 1 $where;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    if ($param_id == 0) {
                        $value_str = $dbc->result($r, $i - 1, "brand_id");
                    } else {
                        $value_str = $dbc->result($r, $i - 1, "param_$param_id");
                    }
                    if (!empty($value_str)) {
                        foreach (explode(",", $value_str) as $item) {
                            $value_arr[] = $item;
                        }
                    }
                }
                $params[$param_id] = $value_arr;
            }

        }

        foreach ($params as $param_id => $values) {
            $params[$param_id] = array_unique($params[$param_id]);
        }

        if (!empty($params)) {
            $keys = implode(",", array_keys($params));

            $param_ids = [];
            $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` IN ($keys) ORDER BY `POSITION` ASC;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $param_id = $db->result($r, $i - 1, "PARAM_ID");
                $param_ids[] = $param_id;
            }

            $arr = [];
            $arr[0] = $params[0];
            foreach ($param_ids as $param_id) {
                $arr[$param_id] = $params[$param_id];
            }

        }

        $list_params = "";
        if (!empty($arr)) {
            foreach ($arr as $param_id => $values) {
                $param_name = $this->getGroupParamName($param_id);
                if (!empty($values)) {
                    $input = "";
                    if (count($values) > $max_items) {
                        $input = "<div class='hidden-list-search'>
                            <input type='text' class='text-filter' onkeyup=\"textParamSearch('$param_id')\" data-attr='$param_id' placeholder='{search_by_name}'>
                        </div>";
                    }
                    $list_params .= "<div class='hidden-list'>
                    <div class='hidden-list-title'>$param_name</div>
                    $input
                    <div class='hidden-list-content' data-attr='$param_id'>";
                    $items = [];
                    foreach ($values as $value_id) {
                        $value_name = $this->getGroupValueName($value_id, $param_id);
                        $link = $this->getPartsFilterLinks($group_id, $filters, $param_id, $value_id);
                        $checked = (in_array($value_id, $params_check[$param_id]));
                        $count_arts = 0;
                        if (!empty($filters)) {
                            if (in_array($param_id, $checked_params_keys)) {
                                $count_arts = $this->getPartsCountWill($group_id, $filters, $param_id, $value_id);
                                $count_arts = $count_arts - $count_arts_full;
                            }
                            if (in_array($param_id, $unchecked_params_keys)) {
                                $count_arts = $this->getPartsCountWill($group_id, $filters, $param_id, $value_id);
                            }
                        } else {
                            $count_arts = $this->getPartsCountWill($group_id, $filters, $param_id, $value_id);
                        }
                        $items[$value_id] = compact("value_name", "link", "checked", "count_arts");
                    }

                    $arr_checked = []; $arr_value_name = []; $arr_count_arts = [];
                    foreach ($items as $key => $row) {
                        $arr_checked[$key]  = $row['checked'];
                        $arr_value_name[$key] = $row['value_name'];
                        $arr_count_arts[$key] = $row['count_arts'];
                    }
                    if ($param_id == 0) {
                        array_multisort($arr_checked, SORT_DESC, SORT_NUMERIC, $arr_value_name, SORT_ASC, SORT_STRING, $items);
                    } else {
                        array_multisort($arr_checked, SORT_DESC, SORT_NUMERIC, $arr_count_arts, SORT_DESC, SORT_NUMERIC, $items);
                    }

                    foreach ($items as $item) {
                        $value_name = $item["value_name"];
                        $link = $item["link"];
                        $checked = $item["checked"];
                        $count_arts = $item["count_arts"];

                        $count_arts_label = "($count_arts)";
                        if (!empty($filters)) {
                            if (in_array($param_id, $checked_params_keys)) {
                                $count_arts_label = "[+$count_arts]";
                            }
                        }
                        $checked_label = "<i class='fas fa-square unchecked'></i>";
                        if ($checked) {
                            $checked_label = "<i class='fas fa-check-square checked'></i>";
                            $count_arts_label = "";
                        }
                        $list_params .= "<a href='$link' class='hidden-list-content__item'>
                            <div class='hidden-list-content__item-left'>$checked_label $value_name</div> 
                            <div class='hidden-list-content__item-right'>$count_arts_label</div>
                        </a>";
                    }

                    $bottom = "";
                    if (count($values) > $max_items) {
                        $more_count = count($values) - $max_items;
                        $bottom = "<div class='hidden-list-more' onclick=\"toggleSideMenu(this);\">
                            <span>{more_cap} $more_count</span>
                            <span class='none'>{hide_cap}</span>
                        </div>";
                    }
                    $list_params .= "</div>
                    $bottom
                    </div>";
                }
            }
        }

        $form = $this->getHtmlForm("catalog_exist/params");
        $form = str_replace("{list_params}", $list_params, $form);
        return $form;
    }

    public function getPartsFilterLinks($group_id, $filters_link, $param_id, $value_id)
    {
        $filters = $this->getCheckedFilters($group_id, $filters_link);
        $link = "";
        if (!empty($filters)) {
            $link = "../$link";
        }

        if (!empty($filters)) {
            foreach ($filters as $param => $values) {
                foreach ($values as $key => $value) {
                    if ($param == $param_id && $value == $value_id) {
                        unset($filters[$param_id][$key]);
                    } elseif (!in_array($value_id, $filters[$param_id])) {
                        $filters[$param_id][] = $value_id;
                    }
                }
            }
        } else {
            $filters[$param_id][] = $value_id;
        }

        foreach ($filters as $param => $values) {
            $param_link = $this->getGroupParamLink($param);
            if (!empty($values)) {
                $link .= "$param_link=";
                foreach ($values as $value) {
                    $value_link = $this->getGroupValueLink($value, $param);
                    $link .= "$value_link,";
                }
                $link = rtrim($link, ",");
                $link .= ";";
            }
            $link = rtrim($link, ",");
        }
        $link = rtrim($link, ";");

        return $link;
    }

}
