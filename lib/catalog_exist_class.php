<?php

class CatalogExistClass extends CatalogueClass
{

    use Helper;
    use Variables;

    public $products_on_page = 12;
    public $default_status_auto = 0;

    /*
     * HEAD EXIST
     * */
    public function getHeadExistID($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $head_id = 0;
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HCG_EXIST` WHERE `GROUP_ID`='$group_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_id = $db->result($r, 0, "HEAD_ID");
        }
        return $head_id;
    }
    public function getHeadExistName($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $head_name = 0;
        $r = $db->query("SELECT `TEX_RU` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID`='$head_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_name = $db->result($r, 0, "TEX_RU");
        }
        return $head_name;
    }
    public function getHeadExistLink($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $head_link = 0;
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID`='$head_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_link = $db->result($r, 0, "TEX_LINK");
        }
        return $head_link;
    }

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
    public function getGroupExistStatusAuto($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $status_auto = $this->default_status_auto;
        $r = $db->query("SELECT `STATUS_AUTO` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID`='$group_id' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $status_auto = $db->result($r, 0, "STATUS_AUTO");
        }
        return $status_auto;
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

        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        if ($this->checkTableParams($group_id) > 0) {
            $dbc->query("UPDATE `$table_params` SET `status`=0 WHERE 1;");
        }

        $params_str = ""; $params = [];
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID`='$group_id' AND `STATUS`=1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $params[] = $param_id;
            $params_str .= "`param_$param_id` VARCHAR(50),";
        }

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table_params` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `brand_id` INT(100),
            `status` TINYINT(2),
            $params_str
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        foreach ($params as $param_id) {
            $dbc->query("
            SET @dbname = DATABASE();
            SET @tablename = '$table_params';
            SET @columnname = 'param_$param_id';
            SET @preparedStatement = (SELECT IF(
              (
                SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                WHERE
                  (table_name = @tablename)
                  AND (table_schema = @dbname)
                  AND (column_name = @columnname)
              ) > 0,
              'SELECT 1',
              CONCAT('ALTER TABLE ', @tablename, ' ADD ', @columnname, ' INT(11);')
            ));
            PREPARE alterIfNotExists FROM @preparedStatement;
            EXECUTE alterIfNotExists;
            DEALLOCATE PREPARE alterIfNotExists;
            ");
        }

        $products = [];
        $r = $db->query("SELECT t2a.`ART_ID`, t2a.`PARAM_ID`, t2a.`VALUE_ID`
        FROM `T2_TREE_ARTS_PARAMS_VALUE_EXIST` t2a
        WHERE t2a.`ART_ID` IN (
            SELECT ex.`ART_ID`
            FROM toko_dba_cache.`$table` as ex
        );");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $value_id = $db->result($r, $i - 1, "VALUE_ID");
            $products[$art_id][0] = 0;
            if ($param_id > 0) {
                $products[$art_id][$param_id][] = $value_id;
            }
        }

        foreach ($products as $art_id => $params) {
            $r = $dbc->query("SELECT `brand_id` FROM `$table` WHERE `art_id`='$art_id' LIMIT 1;");
            $brand_id = $dbc->result($r, 0, "brand_id");
            $products[$art_id][0] = $brand_id;
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

            $r = $dbc->query("SELECT * FROM `$table_params` WHERE `art_id`='$art_id' LIMIT 1;");
            $n = $dbc->num_rows($r);
            if ($n == 0) {
                $dbc->query("INSERT INTO `$table_params` (`art_id`, `brand_id`, `status` $params_column) VALUES ('$art_id', '$brand_id', 1 $params_values);");
                $count_add++;
            } else {
                $dbc->query("UPDATE `$table_params` SET `status`=1 $set_column WHERE `art_id`='$art_id' LIMIT 1;");
                $count_upd++;
            }
        }

        $r = $dbc->query("SELECT COUNT(*) as count_nulls FROM `$table_params` WHERE `status`=0;");
        $count_del = $dbc->result($r, 0, "count_nulls") + 0;
        $dbc->query("DELETE FROM `$table_params` WHERE `status`=0;");

        $dbc->query("ALTER TABLE `$table_params` ADD INDEX `art_id` (`art_id`);");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    /*======================================================================= MAIN =*/

    /*
     * init main table
     * */
    public function initMainTable()
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE";
        $table_available = "EX_TABLE_TREE_AVAILABLE";
        $count_add = 0;

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(11) NOT NULL,
            `group_id` SMALLINT(4),
            `brand_id` INT(11),
            `status` TINYINT(2),
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $dbc->query("TRUNCATE TABLE `$table`;");
        $dbc->query("TRUNCATE TABLE `$table_available`;");

        $r = $db->query("SELECT t2si.art_id, t2a.BRAND_ID
        FROM `T2_SUPPL_IMPORT` t2si
            LEFT JOIN `T2_ARTICLES` t2a ON t2a.ART_ID = t2si.art_id
            LEFT JOIN myparts_dba.`A_CLIENTS_STORAGE` cs ON (cs.id = t2si.client_storage_id)
        WHERE t2si.art_id > 0 AND t2si.stock_suppl > 0 AND cs.visible = 1
        GROUP BY t2si.art_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            $dbc->query("INSERT INTO `$table` (`art_id`, `group_id`, `brand_id`, `status`) VALUES ('$art_id', '0', '$brand_id', 1);");
            $count_add++;
        }

        $r = $db->query("SELECT t2a.ART_ID, t2a.BRAND_ID
        FROM `T2_ARTICLES` t2a
            LEFT JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID = t2a.ART_ID
        WHERE t2a.ART_ID > 0 AND t2asc.AMOUNT > 0
        GROUP BY t2a.art_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            $dbc->query("INSERT INTO `$table` (`art_id`, `group_id`, `brand_id`, `status`) VALUES ('$art_id', '0', '$brand_id', 1);");
            $count_add++;
        }

        // fixed brand_id = 0 in T2_SUPPL_IMPORT
        $db->query("UPDATE `T2_SUPPL_IMPORT` t2si
            INNER JOIN toko_dba_cache.`EX_TABLE_TREE` ex ON ex.art_id = t2si.art_id
        SET t2si.art_id = 0
        WHERE ex.brand_id = 0;");

        // fixed brand_id = 0 in T2_SUPPL_ARTICLES_IMPORT
        $db->query("DELETE t2sai
        FROM `T2_SUPPL_ARTICLES_IMPORT` t2sai
            INNER JOIN toko_dba_cache.`EX_TABLE_TREE` ex ON ex.art_id = t2sai.art_id
        WHERE ex.brand_id = 0;");

        // deleted nulls
        $dbc->query("DELETE FROM `$table` WHERE `brand_id`=0;");

        $dbc->query("INSERT INTO `$table_available` (`art_id`, `brand_id`, `group_id`, `status`)
        SELECT ex.art_id, ex.brand_id, tt.group_id, ex.status FROM `$table` ex
            LEFT JOIN toko_dba.`T2_TREE_ARTS_EXIST` tt ON tt.ART_ID = ex.art_id
        WHERE tt.group_id IS NOT NULL
        GROUP BY ex.art_id, tt.group_id;");

        return "ADDED: $count_add";
    }

    /*======================================================================= PRODUCTS =*/

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
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";
        $table_available = "EX_TABLE_TREE_AVAILABLE";

        $dbc->query("TRUNCATE TABLE `$table`;");

        $dbc->query("INSERT INTO `$table` (`art_id`, `brand_id`, `status`)
        SELECT `art_id`, `brand_id`, `status` FROM `$table_available`
        WHERE `group_id` = $group_id ;");

        return "UPDATED $group_id";
    }

    /*======================================================================= PRODUCTS MFA =*/

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

        $dbc->query("ALTER TABLE `$table_mfa` ADD INDEX `art_id` (`art_id`);");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    /*======================================================================= PARTS =*/

    /*
     * show products catalog
     * */
//    public function showPartsForm($status = 0)
//    {
//        $form = $this->getHtmlForm("catalog_exist/list_params");
//        $list = $this->showGroupExistList($status);
//        $form = str_replace("{parts_name}", "{spare_parts_catalog_cap}", $form);
//        $form = str_replace("{parts_list}", $list, $form);
//        return $form;
//    }

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
                $list .= "<li style='font-weight: bold; font-size: 18px; color: #0000ff;'>$cat_name</li><li><ul style='margin-bottom: 10px;'>";
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
                            <a href='/$this->catalog_exist_link/init/$group_link/'>$check_form</a>   
                            <a href='/$this->catalog_exist_link/show/$group_link/'>ZAPCHASTI $col</a>  
                        </div>
                        <div style='width: 20%; text-align: right;'>
                            <a href='/$this->catalog_exist_link/init_mfa/$group_link/'>$check_mfa_form</a>
                            <a href='/$this->catalog_exist_link/show_mfa/$group_link/'>MACHINU $col_mfa</a>
                        </div>
                        <div style='width: 20%; text-align: right;'>
                            <a href='/$this->catalog_exist_link/init_params/$group_link/'>$check_params_form</a>
                            <a href='/$this->catalog_exist_link/show_params/$group_link/'>PARAMS $col_params</a>
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

    /*======================================================================= FINAL CATALOG =*/

    /*
     * show breadcrumb form
     * */
    public function getPartsBreadcrumbsForm($group_id)
    {
        $list = "";
        if ($group_id > 0) {
            $group_name = $this->getGroupRowName($group_id);
            $head_id = $this->getHeadExistID($group_id);
            $head_name = $this->getHeadExistName($head_id);
            $head_link= $this->getHeadExistLink($head_id);
            $icon = "<i class=\"fa fa-chevron-right\"></i>";
            $list = "<a href=\"/\">{seo_shop_toko}</a> $icon <a href=\"/$this->catalog_exist_link\">{site_catalog}</a> $icon <a href=\"/$this->catalog_exist_link/$head_link\">$head_name</a> $icon $group_name";
        }
        return $list;
    }

    /*
     * show pagination form
     * */
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
                    $pagination .= "<li class=\"page-item $active\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$i\">$i</a></li>";
                }
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"#\">...</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

            if ($page > $max_count) {
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=1\">1</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"#\">...</a></li>";
                for ($i = $max_count; $i <= $pages_count; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    $pagination .= "<li class=\"page-item $active\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$i\">$i</a></li>";
                }
            }

            if ($page >= $min_count && $page <= $max_count) {
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=1\">1</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"#\">...</a></li>";

                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$pred_page\">$pred_page</a></li>";
                $pagination .= "<li class=\"page-item active\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$page\">$page</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$next_page\">$next_page</a></li>";

                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"#\">...</a></li>";
                $pagination .= "<li class=\"page-item\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

        } else {
            for ($i = 1; $i <= $pages_count; $i++) {
                $active = ($i == $page) ? "active" : "";
                $pagination .= "<li class=\"page-item $active\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$i\">$i</a></li>";
            }
        }

        $list = "<div class=\"row\">
            <nav aria-label=\"Page navigation\">
                <ul class=\"pagination\">
                    <li class=\"page-item $disabled_pred\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$pred_page\"><i class=\"fa fa-chevron-left\"></i> <span class=\"span-media\">{previous_cap}</span></a></li>
                    $pagination
                    <li class=\"page-item $disabled_next\"><a class=\"page-link\" rel=\"noopener\" href=\"?page=$next_page\"><span class=\"span-media\">{next_cap}</span> <i class=\"fa fa-chevron-right\"></i></a></li>
                </ul>
            </nav>
        </div>";

        if ($pages_count == 1) {
            $list = "";
        }

        $list = $this->replaceLang($list);

        return $list;
    }

    /*
     * get filter `param + value` ids
     * */
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

    /*
     * get group params
     * */
    public function getExistedParams($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $params = [];
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID`='$group_id' AND `STATUS`=1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $params[] = $param_id;
        }
        return $params;
    }

    /*
     * get filter where
     * */
    public function getFiltersWhere($group_id, $filters)
    {
        $params = $this->getCheckedFilters($group_id, $filters);
        $where = "";
        foreach ($params as $param_id => $values) {
            $param_name = ($param_id == 0) ? "t.`brand_id`" : "tp.`param_$param_id`";
            if (!empty($values)) {
                $where .= " AND (";
                $count = 0 ;
                foreach ($values as $value_id) {
                    $count++;
                    $separator = ($count > 1) ? "OR" : "";
                    $where .= " $separator ($param_name = '$value_id' OR $param_name LIKE '%,$value_id%' OR $param_name LIKE '%$value_id,%')";
                }
                $where .= ") ";
            }
        }
        return $where;
    }

    /*
     * get filter where will
     * */
    public function getFiltersWhereWill($group_id, $filters, $sel_param_id, $sel_value_id)
    {
        $params = $this->getCheckedFilters($group_id, $filters);
        $params[$sel_param_id][] = $sel_value_id;
        $where = "";
        foreach ($params as $param_id => $values) {
            $param_name = ($param_id == 0) ? "t.`brand_id`" : "tp.`param_$param_id`";
            if (!empty($values)) {
                $where .= " AND (";
                $count = 0 ;
                foreach ($values as $value_id) {
                    $count++;
                    $separator = ($count > 1) ? "OR" : "";
                    $where .= " $separator ($param_name = '$value_id' OR $param_name LIKE '%,$value_id%' OR $param_name LIKE '%$value_id,%')";
                }
                $where .= ") ";
            }
        }
        return $where;
    }

    /*
     * get filter where selected
     * */
    public function getFiltersWhereSelected($group_id, $filters, $sel_param_id)
    {
        $params = $this->getCheckedFilters($group_id, $filters);
        $where = "";
        foreach ($params as $param_id => $values) {
            if ($sel_param_id != $param_id) {
                $param_name = ($param_id == 0) ? "t.`brand_id`" : "tp.`param_$param_id`";
                if (!empty($values)) {
                    $where .= " AND (";
                    $count = 0 ;
                    foreach ($values as $value_id) {
                        $count++;
                        $separator = ($count > 1) ? "OR" : "";
                        $where .= " $separator ($param_name = '$value_id' OR $param_name LIKE '%,$value_id%' OR $param_name LIKE '%$value_id,%')";
                    }
                    $where .= ") ";
                }
            }
        }
        return $where;
    }

    /*
     * get count parts from all group
     * */
    public function getPartsCountGroup($group_id, $filters, $where_link_arts = "")
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $n = 0;
        $r = $dbc->query("SHOW TABLES LIKE '$table_params';");
        $nc = $dbc->num_rows($r);
        if ($nc > 0) {
            if (empty($filters)) {
                $r = $dbc->query("SELECT COUNT(t.`art_id`) as count_arts FROM `$table` t WHERE 1 $where_link_arts;");
            } else {
                $where = $this->getFiltersWhere($group_id, $filters);
                $r = $dbc->query("
                SELECT SUM(ex.col_arts) as count_arts FROM (
                    SELECT COUNT(t.`art_id`) as col_arts FROM `$table` t
                        LEFT JOIN `$table_params` tp ON tp.`art_id`=t.`art_id`
                    WHERE 1 $where $where_link_arts
                    GROUP BY t.`art_id`
                ) as ex ;");
            }
            $n = $dbc->result($r, 0, "count_arts");
        }
        return $n;
    }

    /*
     * get products count
     * */
    public function getPartsCount($group_id, $query = "")
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $n = 0;
        $r = $dbc->query("SHOW TABLES LIKE '$table_params';");
        $nc = $dbc->num_rows($r);
        if ($nc > 0) {
            if ($query != "") {
                $query_count = "SELECT COUNT(ex.art_id) as ex_count FROM ( $query ) as ex;";
                $r = $dbc->query($query_count);
                $n = $dbc->result($r, 0, "ex_count");
            }
        }
        return $n;
    }

    /*
     * get products count will
     * */
    public function getPartsCountWill($group_id, $filters, $sel_param_id, $sel_value_id, $where_mfa, $where_link_arts)
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $r = $dbc->query("SHOW TABLES LIKE '$table_params';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $where = $this->getFiltersWhereWill($group_id, $filters, $sel_param_id, $sel_value_id);
            if ($where_mfa == "") {
                $r = $dbc->query("
                SELECT SUM(ex.col_arts) as sum_arts FROM (
                    SELECT COUNT(t.`art_id`) as col_arts FROM `$table` t 
                        LEFT JOIN `$table_params` tp ON tp.`art_id`=t.`art_id`
                    WHERE 1 $where $where_link_arts
                    GROUP BY t.`art_id`
                ) as ex ;");
            } else {
                $r = $dbc->query("
                SELECT SUM(ex.col_arts) as sum_arts FROM (
                     SELECT COUNT(t.art_id) as col_arts FROM `$table` t
                        LEFT JOIN `$table_params` tp ON tp.art_id=t.art_id 
                        LEFT JOIN `$table_mfa` tm ON tm.art_id=t.art_id
                    WHERE 1 $where $where_mfa $where_link_arts
                    GROUP BY t.art_id
                ) as ex ;");
            }
            $n = $dbc->result($r, 0, "sum_arts");
        }
        return $n;
    }

    /*
     * get mfa where
     * */
    public function getMfaWhere($status_auto, $status_auto_type, $mfa_link = "", $model_link = "")
    {
        $auto = new AutoClass();
        $where_mfa = "";
        if (!empty($mfa_link)) {
            if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 1)) {
                $mfa_id = $auto->getMfaLink($mfa_link);
                $model = $auto->getModLink($model_link);
                if ($mfa_id > 0) {
                    $where_mfa .= " AND tm.`mfa_id`=$mfa_id";
                }
                if ($model != "") {
                    $where_mfa .= " AND tm.`model`='$model'";
                }
            }
        }
        return $where_mfa;
    }

    public function showPartsCatalogueError($group_id, $status_auto, $status_auto_type, $mfa_link, $model_link, $filters_h1)
    {
        $automan = new AutoClass();
        $form = $this->getHtmlForm("catalog_exist/error");
        $form = str_replace("{form_car}", $this->getPartsCatalogueCars($group_id, $status_auto, $status_auto_type, $mfa_link, $model_link), $form);
        $form = $this->replaceLang($form);
        $form = str_replace("{h1_text}", "<b>$filters_h1</b>", $form);
        $form = str_replace("{vin_text}", "<a class=\"blue-a\" onclick=\"$('#VinFormPhone').modal('show');\">{vin_order}</a>", $form);
        $catalog_text = "{in_catalog_strs}";
        $catalog_link = "https://toko.ua/cars/";
        if ($mfa_link != "") {
            $mfa_name = $automan->getMfaBrand($automan->getMfaLink($mfa_link));
            $catalog_text .= " {on_cap} $mfa_name";
            $catalog_link .= "$mfa_link/";
            if ($model_link != "") {
                $mod_name = $automan->getModLink($model_link);
                $catalog_text .= "$mod_name";
                $catalog_link .= "$model_link/";
            }
        }
        $form = str_replace("{catlog_link}", "<a class=\"blue-a\" href=\"$catalog_link\">$catalog_text</a>", $form);
        return $form;
    }

    /*
     * show catalog form
     * */
    public function showPartsCatalogueParams($group_id, $str_linka = "", $page = 1, $filters = [], $status_auto_type = 0, $mfa_link = "", $model_link = "")
    {
        $automan = new AutoClass();
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupRowName($group_id);
        $status_auto = $this->getGroupExistStatusAuto($group_id);
        $where_mfa = $this->getMfaWhere($status_auto, $status_auto_type, $mfa_link, $model_link);

        $where_link_arts = "";
        if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 1)) {
            $auto_typ_id = $this->getCookieAuto();
            if ($auto_typ_id != "") {
                $typ_arts = $this->getPartsCatalogueAuto($auto_typ_id);
                $where_link_arts = " AND t.art_id IN (" . implode(",", $typ_arts) . ") ";
            }
        }

        $arts = [];
        if (empty($filters)) {
            $query = "SELECT t.art_id FROM `$table` t
                LEFT JOIN `$table_params` tp ON tp.art_id=t.art_id 
                LEFT JOIN `$table_mfa` tm ON tm.art_id=t.art_id
            WHERE 1 $where_mfa $where_link_arts
            GROUP BY t.art_id";
        } else {
            $where = $this->getFiltersWhere($group_id, $filters);
            $query = "SELECT t.art_id FROM `$table` t
                LEFT JOIN `$table_params` tp ON tp.art_id=t.art_id 
                LEFT JOIN `$table_mfa` tm ON tm.art_id=t.art_id
            WHERE 1 $where $where_mfa $where_link_arts
            GROUP BY t.art_id";
        }
        $query_limit = "$query $limit ;";
        $r = $dbc->query($query_limit);
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($arts, $art_id);
        }

        $art_id_str = implode(",", array_unique($arts));
        list($list) = $this->searchList($art_id_str, 1, "", "", $status_auto, $mfa_link, $model_link);

        $count = $this->getPartsCount($group_id, $query);
        $filters_form = $this->getPartsFiltersForm($group_id, $filters, $where_mfa, $where_link_arts, $query, $mfa_link, $model_link);

        $pagination_form = $this->getPartsPaginationForm($count, $page);
        list($filters_h1, $filters_title, $filters_btn, $filters_count) = $this->getPartsFiltersItems($group_id, $str_linka, $filters, $mfa_link, $model_link);

        $form = $this->getHtmlForm("catalog_exist/form");

        if (empty($art_id_str)) {
            $form = $this->showPartsCatalogueError($group_id, $status_auto, $status_auto_type, $mfa_link, $model_link, $filters_h1);
        }

        $translit = "";
        if ($mfa_link != "") {
            $mfa_id = $automan->getMfaLink($mfa_link);
            $model = "";
            if ($model_link != "") {
                $model = $automan->getModLink($model_link);
            }
            $translit = $automan->getCarManufTranslit($mfa_id, $model);
            $translit = "<span style=\"font-weight: 400;\">$translit</span>";
        }

        $form = str_replace("{details_group_id}", $group_id, $form);
        $form = str_replace("{mfa_link}", $mfa_link, $form);
        $form = str_replace("{model_link}", $model_link, $form);
        $form = str_replace("{parts_name}", $group_text, $form);
        $form = str_replace("{parts_list}", $list, $form);
        $form = str_replace("{parts_h1}", "$filters_h1 $translit", $form);
        $form = str_replace("{parts_count}", "{unselect_cap} $count " . $this->getGoodsCap($count), $form);
        $form = str_replace("{parts_filters}", "$filters_btn", $form);
        $form = str_replace("{parts_pagination}", $pagination_form, $form);
        $form = str_replace("{parts_params}", $filters_form, $form);
        $form = str_replace("{parts_breadcrumbs}", $this->getPartsBreadcrumbsForm($group_id), $form);
        $form = str_replace("{status_auto}", $status_auto, $form);

        $form = str_replace("{filters_count}", $filters_count, $form);
        $form = str_replace("{filters_style}", ($filters_count == 0) ? "none" : "", $form);

//        $form = str_replace("{parts_cars}", $this->getPartsCatalogueCars($group_id, $status_auto, $status_auto_type, $mfa_link, $model_link), $form);
        $form = str_replace("{parts_cars}", $this->drawLoader(), $form);
        $form = str_replace("{parts_params_cars}", $this->getPartsCatalogueParamsCars($group_id, $filters, $status_auto, $status_auto_type), $form);
        $form = str_replace("{parts_seo}", $this->getPartsCatalogueSeo($group_id, $filters, $status_auto, $status_auto_type, $mfa_link, $model_link), $form);
        $form = str_replace("{parts_states}", $this->getPartsCatalogueStates($group_id), $form);

        return array("form" => $form, "title" => $filters_title);
    }

    public function drawLoader()
    {
        $form = $this->getHtmlForm("cars/loader-gear");
        $list = $this->getHtmlForm("loader");
        $form = str_replace("{form_range}", $list, $form);
        return $form;
    }

    /*
     * show filter items form
     * */
    public function getPartsFiltersItems($group_id, $str_linka = "", $filters = [], $mfa_link = "", $model_link = "")
    {
        $filters_btn = ""; $count_vals = 0;
        if (!empty($filters)) {
            $count_vals = 0;
            $params_check = $this->getCheckedFilters($group_id, $filters);
            foreach ($params_check as $param_id => $values) {
                foreach ($values as $value_id) {
                    $count_vals++;
                    $value_name = $this->getGroupValueName($value_id, $param_id);
                    $link = $this->getPartsFilterLinks($group_id, $filters, $param_id, $value_id, $mfa_link, $model_link);
                    $filters_btn .= "<a href='$link' class='btn btn-sm'>$value_name <i class='fa fa-times'></i></a>";
                }
            }
            if ($count_vals > 1) {
                $group_link = $this->getGroupRowLink($group_id);
                $auto_link = "";
                if ($mfa_link != "") {
                    $auto_link .= "$mfa_link/";
                }
                if ($model_link != "") {
                    $auto_link .= "$model_link/";
                }
                $filters_btn = "<a class=\"btn btn-sm btn-filter\" href=\"/$this->catalog_exist_link/$group_link/$auto_link\">{filter_cap_empty} <i class='fa fa-times'></i></a>" . $filters_btn;
            }
        }
        $filters_h1 = $this->getCatalogH1($group_id, $filters, $mfa_link, $model_link);

        $filters_title = $this->getCatalogTitleCache($str_linka);
        if ($filters_title == "") {
            $filters_title = $this->getCatalogTitle($group_id, $filters, $mfa_link, $model_link);
        }

        return array($filters_h1, $filters_title, $filters_btn, $count_vals);
    }

    /*
     * show filter form
     * */
    public function getPartsFiltersForm($group_id, $filters = [], $where_mfa = "", $where_link_arts = "", $query = "", $mfa_link = "", $model_link = "")
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $max_items = 7;
        $count_arts_full = $this->getPartsCount($group_id, $query);

        $params_check = $this->getCheckedFilters($group_id, $filters);

        $exist_params = $this->getExistedParams($group_id);

        $params = []; $checked_params_keys = []; $unchecked_params_keys = [];

        if (empty($filters)) {
            $r = $dbc->query("
            SELECT tp.*, t.brand_id as brand_cur_id FROM `$table` t
                LEFT JOIN `$table_params` tp ON tp.art_id=t.art_id 
                LEFT JOIN `$table_mfa` tm ON tm.art_id=t.art_id
            WHERE 1 $where_mfa $where_link_arts
            GROUP BY t.art_id ;");
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $brand_id = $dbc->result($r, $i - 1, "brand_cur_id");
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
            $existed_params_keys = array_values($exist_params); $existed_params_keys[] = 0;
            $unchecked_params_keys = array_diff($existed_params_keys, $checked_params_keys);

            foreach ($checked_params_keys as $param_id) {
                $where = $this->getFiltersWhereSelected($group_id, $filters, $param_id);
                $value_arr = [];
                $r = $dbc->query("
                SELECT tp.*, t.brand_id as brand_cur_id FROM `$table` t
                    LEFT JOIN `$table_params` tp ON tp.art_id=t.art_id 
                    LEFT JOIN `$table_mfa` tm ON tm.art_id=t.art_id
                WHERE 1 $where $where_mfa $where_link_arts
                GROUP BY t.art_id ;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    if ($param_id == 0) {
                        $value_str = $dbc->result($r, $i - 1, "brand_cur_id");
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
                $r = $dbc->query("
                SELECT tp.*, t.brand_id as brand_cur_id FROM `$table` t
                    LEFT JOIN `$table_params` tp ON tp.art_id=t.art_id 
                    LEFT JOIN `$table_mfa` tm ON tm.art_id=t.art_id
                WHERE 1 $where $where_mfa $where_link_arts
                GROUP BY t.art_id ;");
                $n = $dbc->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    if ($param_id == 0) {
                        $value_str = $dbc->result($r, $i - 1, "brand_cur_id");
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
            $keys = implode(",", (array_keys($params)));

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
                    $list_params .= "<div class=\"hidden-list\">
                    <div class=\"hidden-list-title\">$param_name</div>
                    <div class=\"hidden-list-search\">
                        <input type=\"text\" class=\"text-filter\" onkeyup=\"textParamSearch('$param_id')\" data-attr=\"$param_id\" placeholder=\"{search_by_name}\">
                    </div>
                    <div class=\"hidden-list-content\" data-attr=\"$param_id\">";
                    $items = [];
                    foreach ($values as $value_id) {
                        $value_name = $this->getGroupValueName($value_id, $param_id);
                        $link = $this->getPartsFilterLinks($group_id, $filters, $param_id, $value_id, $mfa_link, $model_link);
                        $checked = (in_array($value_id, $params_check[$param_id]));
                        $count_arts = 0;
                        if (!empty($filters)) {
                            if (in_array($param_id, $checked_params_keys)) {
                                $count_arts = $this->getPartsCountWill($group_id, $filters, $param_id, $value_id, $where_mfa, $where_link_arts);
                                $count_arts = $count_arts - $count_arts_full;
                            }
                            if (in_array($param_id, $unchecked_params_keys)) {
                                $count_arts = $this->getPartsCountWill($group_id, $filters, $param_id, $value_id, $where_mfa, $where_link_arts);
                            }
                        } else {
                            $count_arts = $this->getPartsCountWill($group_id, $filters, $param_id, $value_id, $where_mfa, $where_link_arts);
                        }
                        $items[$value_id] = compact("value_name", "link", "checked", "count_arts");
                    }

                    $arr_checked = []; $arr_value_name = []; $arr_count_arts = [];
                    foreach ($items as $key => $row) {
                        $arr_checked[$key]  = $row["checked"];
                        $arr_value_name[$key] = $row["value_name"];
                        $arr_count_arts[$key] = $row["count_arts"];
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
                        $checked_label = "<i class=\"fas fa-square unchecked\"></i>";
                        if ($checked) {
                            $checked_label = "<i class=\"fas fa-check-square checked\"></i>";
                            $count_arts_label = "";
                        }
                        $list_params .= "<a href=\"$link\" class=\"hidden-list-content__item\">
                            <div class=\"hidden-list-content__item-left\" data-param-value=\"$param_id\">$checked_label <span>$value_name</span></div> 
                            <div class=\"hidden-list-content__item-right\">$count_arts_label</div>
                        </a>";
                    }

                    $bottom = "";
                    if (count($values) > $max_items) {
                        $more_count = count($values) - $max_items;
                        $bottom = "<div class=\"hidden-list-more\" onclick=\"toggleSideMenu(this);\" data-attr-more=\"$param_id\">
                            <span>{more_cap} $more_count</span>
                            <span class=\"none\">{hide_cap}</span>
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

    /*
     * get catalog link
     * */
    public function getPartsFilterLinks($group_id, $filters_link, $param_id, $value_id, $mfa_link = "", $model_link = "")
    {
        $filters = $this->getCheckedFilters($group_id, $filters_link);
        $link = "";

        if (!empty($filters)) {
            $unset = 0;
            foreach ($filters as $param => $values) {
                foreach ($values as $key => $value) {
                    if ($param == $param_id && $value == $value_id) {
                        $unset++;
                        unset($filters[$param_id][$key]);
                        if (empty($filters[$param])) {
                            unset($filters[$param]);
                        }
                    } elseif (!in_array($value_id, $filters[$param_id]) && $unset == 0) {
                        $filters[$param_id][] = $value_id;
                    }
                }
            }
        } else {
            $filters[$param_id][] = $value_id;
        }

        ksort($filters);

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

        $group_link = $this->getGroupRowLink($group_id);
        $list = "https://toko.ua/$this->catalog_exist_link/";
        if ($group_id > 0) {
            $list .= "$group_link/";
            if ($link != "") {
                $list .= "$link/";
            } elseif ($mfa_link != "") {
                $list .= "auto/";
            } else {
                $list .= "";
            }
            if ($mfa_link != "") {
                $list .= "$mfa_link/";
            }
            if ($model_link != "") {
                $list .= "$model_link/";
            }
        }

        return $list;
    }

    /*======================================================================= STATUS AUTO =*/

    /*
     * show param cars form
     * */
    public function getPartsCatalogueParamsCars($group_id, $filters, $status_auto = 0, $status_auto_type = 0)
    {
        $auto = new AutoClass();
        $form = "";
        $auto_typ_id = $this->getCookieAuto();
        if ($status_auto == 1 && $auto_typ_id != "") {
            $car_checked = ""; $all_checked = "";
            $car_count = ""; $all_count = "";
            list($mfa_id, $model) = $auto->getCarInfo($auto_typ_id);
            $mfa_name = $auto->getMfaBrand($mfa_id);
            $typ_text = "$mfa_name $model";
            // всі запчастини
            if ($status_auto_type == 0) {
                $car_checked = "<i class=\"fas fa-circle unchecked\"></i>";
                $all_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                $where_link_arts = "";
                $typ_arts = $this->getPartsCatalogueAuto($auto_typ_id);
                if (!empty($typ_arts)) {
                    $where_link_arts = " AND t.art_id IN (" . implode(",", $typ_arts) . ") ";
                }
                $count = $this->getPartsCountGroup($group_id, $filters, $where_link_arts);
                $car_count = "($count)";
            }
            // вибрана машина
            if ($status_auto_type == 1) {
                $car_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                $all_checked = "<i class=\"fas fa-circle unchecked\"></i>";
                $count = $this->getPartsCountGroup($group_id, $filters);
                $all_count = "($count)";
            }
            $form = $this->getHtmlForm("catalog_exist/params_cars");
            $form = str_replace("{typ_text}", $typ_text, $form);
            $form = str_replace("{on_car_checked}", $car_checked, $form);
            $form = str_replace("{on_car_count}", $car_count, $form);
            $form = str_replace("{on_all_checked}", $all_checked, $form);
            $form = str_replace("{on_all_count}", $all_count, $form);
        }
        return $form;
    }

    /*
     * show cars form
     * */
    public function getPartsCatalogueCars($group_id, $status_auto = 0, $status_auto_type = 0, $mfa_link = "", $model_link = "")
    {
        $products = new ProductsClass();
        $form = "";
        $auto_typ_id = $this->getCookieAuto();
        if ($status_auto == 0 || $status_auto == 1) {
            if ($auto_typ_id != "") {
                if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 1)) {
                    $form = $products->getCarsGarage();
                } else {
                    $form = $products->getCarsSearch($mfa_link, $model_link, $group_id);
                }
            } else {
                $form = $products->getCarsSearch($mfa_link, $model_link, $group_id);
            }
        }
        return $form;
    }

    /*
     * get products from t2_links
     * */
    public function getPartsCatalogueAuto($auto_typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $arts = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID`='$auto_typ_id' GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i < $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[] = $art_id;
        }
        return $arts;
    }

    /*===================================================================================== SEO FORM =*/

    /*
     * show products seo form
     * */
    public function getPartsCatalogueSeo($group_id, $filters = [], $status_auto = 0, $status_auto_type = 0, $mfa_link = "", $mod_link = "", $mod_id_link = "")
    {
        $automan = new AutoClass();
        $menu = new MenuClass();
        $form = $this->getHtmlForm("catalog_exist/seo");
        $auto_typ_id = $this->getCookieAuto();
        if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 0)) {
            // SEO details
            if ($auto_typ_id == "" || ($status_auto == 1 && $status_auto_type == 0)) {
                if ($mfa_link != "") {
                    $mfa_id = $automan->getMfaLink($mfa_link);
                    if ($mod_link != "") {
                        if ($mod_id_link != "") {
                            $mod_id = $automan->getAutoModelIdLink($mod_id_link)["model_id"];
                            $form = str_replace("{seo_auto}", $this->getGroupCarTypeList($group_id, $mfa_id, $mod_id), $form);
                            $form = str_replace("{seo_style}", "", $form);
                        } else {
                            $model = $automan->getModLink($mod_link);
                            $form = str_replace("{seo_auto}", $this->getGroupCarModIDList($group_id, $mfa_id, $model), $form);
                            $form = str_replace("{seo_style}", "", $form);
                        }
                    } else {
                        $form = str_replace("{seo_auto}", $this->getGroupCarModList($group_id, $mfa_id), $form);
                        $form = str_replace("{seo_style}", "", $form);
                    }
                } else {
                    $form = str_replace("{seo_auto}", $this->getGroupCarMfaList($group_id), $form);
                    $form = str_replace("{seo_style}", "", $form);
                }
            }

            // SEO popular request
            if ($auto_typ_id == "" || ($status_auto == 1 && $status_auto_type == 0)) {
                $h1_text = $this->getCatalogH1($group_id, $filters, $mfa_link, $mod_link);
                $form = str_replace("{seo_popular}", $menu->getCatalogFaqForm($h1_text), $form);
            }
        }
        $form = str_replace("{seo_auto}", "", $form);
        $form = str_replace("{seo_popular}", "", $form);
        $form = str_replace("{seo_style}", "none", $form);

        return $form;
    }

    /*
     * get TYP list
     * */
    public function getGroupCarTypeList($group_id, $mfa_id = 0, $mod_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $auto = new AutoClass();
        $mfa_text = $auto->getMfaBrand($mfa_id);
        $mod_id_text = $auto->getModIdLink($mod_id);
        $title = "$mfa_text $mod_id_text";
        $details_cap = "{details_on_cap}";
        if ($group_id != "") {
            $details_cap = $this->getGroupRowName($group_id);
            $details_cap .= " {on_cap}";
        }
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id';");
        $n = $db->num_rows($r);
        $list = "<span class=\"title-b\">$details_cap $title</span>";
        $list .= "<div class=\"t_types\">";
        $mas = [];
        for ($i = 1; $i <= $n; $i++) {
            $fuel_id = $db->result($r, $i - 1, "FUEL_ID");
            $typ_text = $db->result($r, $i - 1, "TYP_TEXT");
            $kw_from = $db->result($r, $i - 1, "TYP_KW_FROM");
            $hp_from = $db->result($r, $i - 1, "TYP_HP_FROM");
            $link = $this->replaceLang("<span><b>$typ_text</b> ($hp_from {horse_power_cap}, $kw_from {kilo_wat_cap})</span>");
            $mas[$fuel_id][] = $link;
        }
        foreach ($mas as $fuel_id => $types) {
            $fuel_name = $this->getFuelName($fuel_id);
            $list .= "<div><span class=\"text-dark bold\">$fuel_name: </span>";
            foreach ($types as $typ) {
                $list .= "$typ";
            }
            $list .= "</div>";
        }
        $list .= "</div>";
        return $list;
    }

    /*
     * get MOD ID list
     * */
    public function getGroupCarModIDList($group_id, $mfa_id_sel = 0, $model = "")
    {
        $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $list = "";
        $link = "$this->catalog_exist_link";
        $details_cap = "{all_type_models}";
        if ($group_id != "") {
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $link .= "/$group_link/auto";
            $details_cap  = $group_name;
            $details_cap .= " {on_cap}";
        }

        $r = $db->query("SELECT mf.*, md.Model, md.Model_Link 
        FROM `T_manufacturers` mf
            LEFT JOIN `T_models` md ON md.MOD_MFA_ID=mf.MFA_ID
        WHERE mf.`MFA_ID`='$mfa_id_sel' AND md.`Model`='$model' 
        GROUP BY md.`Model`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mod_link = $db->result($r, $i - 1, "Model_Link");

            $list .= "<span class=\"title-b\">$details_cap $mfa_brand $model</span>";
            $list .= "<div class=\"seo_details\"><div class=\"seo-ul\">";

            $r2 = $db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id_sel' AND `Model`='$model' ORDER BY `MOD_PCON_START`;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $mod_id_link = $db->result($r2, $i2 - 1, "TEX_TEXT_link");
                $text = $db->result($r2, $i2 - 1, "TEX_TEXT");
                $image = $db->result($r2, $i2 - 1, "Car_pict");
                $d_start = $db->result($r2, $i2 - 1, "MOD_PCON_START");
                $d_start = substr($d_start, 0, 4);
                $d_end = $db->result($r2, $i2 - 1, "MOD_PCON_END");
                $d_end = substr($d_end, 0, 4);
                if ($d_end == 0) {
                    $d_end = "{cur_time}";
                }
                $list .= "<a class=\"seo-li seo-li-id\" href=\"https://toko.ua$prefix/$link/$mfa_link/$mod_link/$mod_id_link/\">
                    <div class=\"row mar0\">
                        <div class=\"col-4 pad0\"><img src=\"https://toko.ua/uploads/images/models/$image\" alt=\"$text\" title=\"$text\"></div>
                        <div class=\"col-8\"><span>$mfa_brand $text ($d_start - $d_end)</span></div>
                    </div>
                </a>";
            }
            $list .= "</div></div>";
        }
        $list .= $this->getGroupCarMfaList($group_id, $mfa_id_sel);

        return $list;
    }

    /*
     * get MOD ID list
     * */
    public function getGroupCarModList($group_id, $mfa_id_sel = 0)
    {
        $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();

        $link = "$this->catalog_exist_link";
        if ($group_id != "") {
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $link .= "/$group_link/auto";
            $details_cap = $group_name;
            $details_cap .= " {on_cap}";
        } else {
            $details_cap = "{details_on_cap}";
        }

        $where = ($mfa_id_sel != "") ? "AND `MFA_ID`='$mfa_id_sel'" : "";
        $list = "<ul>";
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 $where ORDER BY `MFA_BRAND`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = $db->result($r, $i - 1, "MFA_ID");
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");

            if ($mfa_id_sel == "") {
                $list .= "<li class=\"title\"><span class=\"bold\"><a href=\"https://toko.ua$prefix/$link/$mfa_link/\">$details_cap $mfa_brand</a></span>";
            } else {
                $list = "<span class=\"title-b\">$details_cap $mfa_brand</span>";
            }
            $list .= "<div class=\"seo_details\"><div class=\"seo-ul\">";

            $r2 = $db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $mod = $db->result($r2, $i2 - 1, "Model");
                $mod_link = $db->result($r2, $i2 - 1, "Model_Link");
                $list .= "<a class=\"seo-li\" href=\"https://toko.ua$prefix/$link/$mfa_link/$mod_link/\">
                    <span>$mfa_brand $mod</span>
                </a>";
            }
            $list .= "</div></div>";
        }
        if ($mfa_id_sel != "") {
            $list .= "</ul>";
        }
        return $list;
    }

    /*
     * get MFA ID list
     * */
    public function getGroupCarMfaList($group_id, $mfa_id_sel = 0)
    {
        $db = DbSingleton::getTokoDb();
        $auto = new AutoClass();
        $details_cap = "{details_on_cap}";
        $title = "";
        $link = "$this->catalog_exist_link";
        $where = ($mfa_id_sel != "") ? " AND `MFA_ID`='$mfa_id_sel'" : "";
        if ($group_id != "") {
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $details_cap = $group_name;
            $link .= "/$group_link/auto";
            if ($mfa_id_sel != 0) {
                $mfa_brand = $auto->getMfaBrand($mfa_id_sel);
                $title = "<div><span class=\"title-b\">$details_cap {on_cap} {other_models} $mfa_brand</span></div>";
            } else {
                $title = "<div><span class=\"title-b\">$details_cap</span></div>";
            }
            $details_cap .= " {on_cap}";
        }
        $list = "<div class=\"seo_auto\">$title";
        $mas = [];
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 $where ORDER BY `MFA_BRAND` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = $db->result($r, $i - 1, "MFA_ID");
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mas[$mfa_brand] = compact("mfa_id", "mfa_link");
        }
        foreach ($mas as $mfa_brand => $values) {
            $mfa_id = $values["mfa_id"];
            $mfa_link = $values["mfa_link"];
            if ($mfa_id_sel == "") {
                $list .= "<div class=\"title\"><a href=\"https://toko.ua/$link/$mfa_link/\">$details_cap $mfa_brand</a></div>";
            }
            $list .= "<ul class=\"list-inline\">";
            $r = $db->query("SELECT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model = $db->result($r, $i - 1, "Model");
                $model_link = $db->result($r, $i - 1, "Model_Link");
                $list .= "<li><a href=\"https://toko.ua/$link/$mfa_link/$model_link/\">$mfa_brand $model</a></li>";
            }
            $list .= "</ul>";
        }
        $list .= "</div>";
        return $list;
    }

    /*
     * catalog h1
     * */
    public function getCatalogH1($group_id, $filters = [], $mfa_link = "", $model_link = "")
    {
        $auto = new AutoClass();
        $group_text = ""; $car_text = "";

        if ($group_id > 0) {
            $group_text = $this->getGroupRowName($group_id);
        }

        if ($mfa_link != "") {
            $mfa_id = $auto->getMfaLink($mfa_link);
            $mfa_name = $auto->getMfaBrand($mfa_id);
            $car_text = "{on_cap} $mfa_name";
            if ($model_link != "") {
                $model = $auto->getModLink($model_link);
                $car_text .= " $model";
            }
        }

        if (!empty($filters)) {
            $params = $this->getCheckedFilters($group_id, $filters);
            if (array_key_exists(0, $params)) {
                // only 1 brand
                if (count($params) == 1) {
                    if (count($params[0]) == 1) {
                        foreach ($params[0] as $value_id) {
                            $brand_name = $this->getGroupValueName($value_id);
                            $group_text .= " $brand_name";
                        }
                    }
                }
                // 1 brand + 1 param
                if (count($params) == 2) {
                    krsort($params);
                    foreach ($params as $param_id => $values) {
                        if ($param_id > 0) {
                            foreach ($values as $value_id) {
                                $value_name = $this->getGroupValueName($value_id, $param_id);
                                $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                                if ($value_h1_name != "") {
                                    $group_text = $value_h1_name;
                                } else {
                                    $group_text .= " $value_name";
                                }
                            }
                        }
                        if ($param_id == 0) {
                            foreach ($values as $brand_id) {
                                $brand_name = $this->getBrandName($brand_id);
                                $group_text .= " $brand_name";
                            }
                        }
                    }
                }
            }
            // only 1 param
            elseif (count($params) == 1) {
                foreach ($params as $param_id => $values) {
                    foreach ($values as $value_id) {
                        $value_name = $this->getGroupValueName($value_id, $param_id);
                        $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                        if (count($values) == 1) {
                            if ($value_h1_name != "") {
                                $group_text = $value_h1_name;
                            } else {
                                $group_text .= " $value_name";
                            }
                        }
                    }
                }
            }
        }

        return "$group_text $car_text";
    }

    /*
     * catalog title cache
     * */
    public function getCatalogTitleCache($str_linka)
    {
        $db = DbSingleton::getTokoDb();
        $str_linka = $this->getUrlString($str_linka);
        $title = "";
        $r = $db->query("SELECT * FROM `T2_TITLES` WHERE `ROUTER`='$this->catalog_link' AND `LINK`='$str_linka' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $title = $db->result($r, 0, "TITLE_RU");
        }
        return $title;
    }

    /*
     * catalog title
     * */
    public function getCatalogTitle($group_id, $filters = [], $mfa_link = "", $model_link = "")
    {
        $auto = new AutoClass();
        $h1 = $this->getCatalogH1($group_id, $filters, $mfa_link, $model_link);
        $text = "$h1 | ";
        $brand_name = "";

        // 1
        // filtry-masljanye/
        if ($mfa_link == "" && $model_link == "" && empty($filters)) {
            $text .= $this->replaceLang("{seo_new_tilte_1}");
        }

        // 2
        // filtry-masljanye/auto/kia/
        if ($mfa_link != "" && $model_link == "" && empty($filters)) {
            $text .= $this->replaceLang("{seo_new_tilte_2}");
        }

        // 3
        // filtry-masljanye/auto/kia/sportage/
        if ($mfa_link != "" && $model_link != "" && empty($filters)) {
            $text .= $this->replaceLang("{seo_new_tilte_3}");
            $mfa_id = $auto->getMfaLink($mfa_link);
            $mfa_name = $auto->getMfaBrand($mfa_id);
            $text = str_replace("{mfnm}", $mfa_name, $text);
        }

        // 4
        // filtry-masljanye/filters/kia/sportage/
        if (!empty($filters)) {
            $params = $this->getCheckedFilters($group_id, $filters);
            //if brand
            if (array_key_exists(0, $params)) {
                // 1 brand
                if (count($params) == 1) {
                    if ($mfa_link == "") {
                        // 1 brand + auto
                        $text .= $this->replaceLang("{seo_new_tilte_4}");
                        $text = str_replace("{brnm}", $brand_name, $text);
                    } else {
                        // 1 brand + kia sportage
                        $text .= $this->replaceLang("{seo_new_tilte_5}");
                        $mfa_id = $auto->getMfaLink($mfa_link);
                        $mfa_name = $auto->getMfaBrand($mfa_id);
                        $text = str_replace("{mfnm}", $mfa_name, $text);
                        $text = str_replace("{brnm}", $brand_name, $text);
                    }
                }
                // 1 brand + 1 param
                if (count($params) == 2) {
                    if ($mfa_link == "") {
                        // 1 brand + 1 param + auto
                        $text .= $this->replaceLang("{seo_new_tilte_6}");
                        $text = str_replace("{brnm}", $brand_name, $text);
                        foreach ($params as $param_id => $values) {
                            foreach ($values as $value_id) {
                                $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                                if ($value_h1_name != "") {
                                    $text = str_replace("{grnm}", $value_h1_name, $text);
                                }
                            }
                        }
                    } else {
                        // 1 brand + 1 param + kia sportage
                        $text .= $this->replaceLang("{seo_new_tilte_7}");
                        $mfa_id = $auto->getMfaLink($mfa_link);
                        $mfa_name = $auto->getMfaBrand($mfa_id);
                        $text = str_replace("{mfnm}", $mfa_name, $text);
                        $text = str_replace("{brnm}", $brand_name, $text);
                        foreach ($params as $param_id => $values) {
                            foreach ($values as $value_id) {
                                $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                                if ($value_h1_name != "") {
                                    $text = str_replace("{grnm}", $value_h1_name, $text);
                                }
                            }
                        }
                    }
                }
            }
            // 1 param
            elseif (count($params) == 1) {
                if ($mfa_link == "") {
                    // 1 param + auto
                    $text .= $this->replaceLang("{seo_new_tilte_1}");
                }
                // 1 param + kia sportage
                elseif ($model_link == "") {
                    $text .= $this->replaceLang("{seo_new_tilte_2}");
                    foreach ($params as $param_id => $values) {
                        foreach ($values as $value_id) {
                            $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                            if (count($values) == 1) {
                                if ($value_h1_name != "") {
                                    $text = str_replace("{grnm}", $value_h1_name, $text);
                                }
                            }
                        }
                    }
                } else {
                    $text .= $this->replaceLang("{seo_new_tilte_3}");
                    $mfa_id = $auto->getMfaLink($mfa_link);
                    $mfa_name = $auto->getMfaBrand($mfa_id);
                    $text = str_replace("{mfnm}", $mfa_name, $text);
                    foreach ($params as $param_id => $values) {
                        foreach ($values as $value_id) {
                            $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                            if (count($values) == 1) {
                                if ($value_h1_name != "") {
                                    $text = str_replace("{grnm}", $value_h1_name, $text);
                                }
                            }
                        }
                    }
                }
            }
            // else
            else {
                $text .= $this->replaceLang("{seo_new_tilte_1}");
            }
        }

        $text = str_replace("{grnm}", $this->getGroupRowName($group_id), $text);

        $text = $this->replaceLang($text);

        return $text;
    }

    public function getPartsCatalogueStates($group_id) {
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $prefix = "";
        if ($lang_id == 2) {
            $prefix = "_UA";
        }
        if ($lang_id == 3) {
            $prefix = "_EN";
        }
        $list = "";
        if ($group_id > 0) {
            $r = $db->query("SELECT t2r.`ID`, t2r.`TITLE$prefix` FROM `T2_GROUP_REVIEW` t2gr 
                LEFT JOIN `T2_REVIEWS` t2r ON t2r.`ID` = t2gr.`REVIEW_ID`
            WHERE t2gr.`GROUP_ID` = '$group_id' AND t2r.`STATUS` = 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $list = "<div class=\"reviews-list-title\">{states_cap}</div><div class=\"reviews-list\">";
            }
            for ($i = 1; $i <= $n; $i++) {
                $review_id = $db->result($r, $i - 1, "ID");
                $review_title = $db->result($r, $i - 1, "TITLE$prefix");
                $transcript = $this->formatUrlText($review_title);
                $link = "$prefix/reviews/state/$review_id/$transcript";
                $list .= "<div class=\"reviews-list__item\"><a href=\"$link\"><i class=\"fa fa-circle\"></i> $review_title</a></div>";
            }
            if ($n > 0) {
                $list .= "</div>";
            }
        }
        return $list;
    }

    public function getGroupHeadExistId($head_link)
    {
        $db = DbSingleton::getTokoDb();
        $head_id = 0;
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HEAD_EXIST` WHERE `TEX_LINK`='$head_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_id = $db->result($r, 0, "HEAD_ID");
        }
        return $head_id;
    }

    public function getGroupCatExistId($cat_link)
    {
        $db = DbSingleton::getTokoDb();
        $cat_id = 0;
        $r = $db->query("SELECT `CAT_ID` FROM `T2_TREE_CAT_EXIST` WHERE `TEX_LINK`='$cat_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $cat_id = $db->result($r, 0, "CAT_ID");
        }
        return $cat_id;
    }

    public function showGroupHeadForm($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_RU`, `IMAGES` FROM `T2_TREE_HEAD_EXIST` WHERE `STATUS`=1 AND `HEAD_ID`='$head_id' LIMIT 1;");
        $head_title = $db->result($r, 0, "TEX_RU");
        $form = $this->getHtmlForm("catalog_exist/head_form");
        $form = str_replace("{head_title}", $head_title, $form);
        $form = str_replace("{head_list}",  $this->getCatalogColListCat($head_id), $form);
        return $form;
    }

    public function showGroupCatForm($head_id, $cat_id)
    {
        $cat_title = $this->getCatRowName($cat_id);
        $head_title = $this->getHeadRowName($head_id);
        $form = $this->getHtmlForm("catalog_exist/cat_form");
        $form = str_replace("{cat_title}", $cat_title, $form);
        $form = str_replace("{head_title}", "<a href=\"../\"><i class=\"fa fa-chevron-left\"></i> $head_title</a>", $form);
        $form = str_replace("{cat_list}", $this->getCatalogColListGroup($head_id, $cat_id), $form);
        return $form;
    }

    public function getGroupHeadList($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $arr = [];
        $r = $db->query("SELECT `CAT_ID`, `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID`='$head_id' GROUP BY `CAT_ID`, `GROUP_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $cat_id = $db->result($r, $i - 1, "CAT_ID");
            $group_id = $db->result($r, $i - 1, "GROUP_ID");
            $arr[$cat_id][] = $group_id;
        }
        if (!empty($arr)) {
            foreach ($arr as $cat_id => $groups) {
                $cat_name = $this->getCatRowName($cat_id);
                $cat_link = $this->getCatRowLink($cat_id);
                $list .= "<div><a href=\"./$cat_link\">$cat_name</a></div>";
                $list .= "<ul class=\"list-inline\">";
                foreach ($groups as $group_id) {
                    $group_name = $this->getGroupRowName($group_id);
                    $group_link = $this->getGroupRowLink($group_id);
                    $list .= "<li><a href=\"./$group_link\">$group_name</a></li>";
                }
                $list .= "</ul>";
            }
        }
        return $list;
    }

    public function getGroupCatList($head_id, $cat_id)
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `HEAD_ID`='$head_id' AND `CAT_ID`='$cat_id' GROUP BY `GROUP_ID`;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $list .= "<ul class=\"list-inline\">";
            for ($i = 1; $i <= $n; $i++) {
                $group_id = $db->result($r, $i - 1, "GROUP_ID");
                $group_name = $this->getGroupRowName($group_id);
                $group_link = $this->getGroupRowLink($group_id);
                $list .= "<li><a href=\"../../$group_link\">$group_name</a></li>";
            }
            $list .= "</ul>";
        }
        return $list;
    }

    /*
     *
     * 1. group + brand
     * 2. group + filter
     * 3. group + brand + filter
     * 4. group + brand || filter + mfa
     * 5. group + brand + filter + mfa
     * */

    public function getManufactureLink($mfa_id)
    {
        $db = DbSingleton::getTokoDb();
        $mfa_link = "";
        $r = $db->query("SELECT `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id AND `ACTIVE` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $mfa_link = $db->result($r, 0, "MFA_BRAND_LINK");
        }
        return $mfa_link;
    }

    public function getValueH1($value_id, $param_id = 0)
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

    public function getSeoLinks()
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $count = 0;

//        $r = $dbc->query("SELECT `group_id`, `brand_id` FROM `EX_TABLE_TREE_AVAILABLE` WHERE 1 GROUP BY `group_id`, `brand_id`;");
//        $n = $dbc->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $group_id = $dbc->result($r, $i - 1, "group_id");
//            $brand_id = $dbc->result($r, $i - 1, "brand_id");
//            $dbc->query("INSERT INTO `AAA_EXPORT_LINKS_BRANDS` (`GROUP_ID`, `BRAND_ID`) VALUES ('$group_id', '$brand_id');");
//            $count++;
//        }

//        $r = $dbc->query("SELECT t2a.`GROUP_ID`, t2a.`PARAM_ID`, t2a.`VALUE_ID`
//        FROM `EX_TABLE_TREE_AVAILABLE` ex
//            LEFT JOIN toko_dba.`T2_TREE_ARTS_PARAMS_VALUE_EXIST` t2a ON (t2a.`ART_ID` = ex.`art_id` AND t2a.`GROUP_ID` = ex.`group_id`)
//        WHERE 1
//        GROUP BY t2a.`GROUP_ID`, t2a.`PARAM_ID`, t2a.`VALUE_ID`;");
//        $n = $dbc->num_rows($r);
//        $groups_params = [];
//        for ($i = 1; $i <= $n; $i++) {
//            $group_id = $dbc->result($r, $i - 1, "GROUP_ID");
//            $param_id = $dbc->result($r, $i - 1, "PARAM_ID");
//            $value_id = $dbc->result($r, $i - 1, "VALUE_ID");
//            $value_h1 = $this->getValueH1($value_id, $param_id);
//            if (!in_array($value_id, $groups_params[$group_id][$param_id]) && $group_id > 0 && $param_id > 0 && $value_id > 0 && $value_h1 != "") {
//                $groups_params[$group_id][$param_id][] = $value_id;
//            }
//        }
//        foreach ($groups_params as $group_id => $params) {
//            $status_auto = $this->getGroupExistStatusAuto($group_id);
//            foreach ($params as $param_id => $values) {
//                foreach ($values as $value_id) {
//                    if ($status_auto == 0 || $status_auto == 1) {
//                        $dbc->query("INSERT INTO `AAA_EXPORT_LINKS_PARAMS` (`GROUP_ID`, `PARAM_ID`, `VALUE_ID`) VALUES ('$group_id', '$param_id', '$value_id');");
//                        $count++;
//                    }
//                }
//            }
//        }

//        $r = $dbc->query("SELECT `GROUP_ID`, `PARAM_ID`, `VALUE_ID` FROM `AAA_EXPORT_LINKS_PARAMS` WHERE 1;");
//        $n = $dbc->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $group_id = $dbc->result($r, $i - 1, "GROUP_ID");
//            $param_id = $dbc->result($r, $i - 1, "PARAM_ID");
//            $value_id = $dbc->result($r, $i - 1, "VALUE_ID");
//            $rc = $dbc->query("SELECT * FROM `AAA_EXPORT_LINKS_BRANDS` WHERE `GROUP_ID` = '$group_id';");
//            $nc = $dbc->num_rows($rc);
//            for ($j = 1; $j <= $nc; $j++) {
//                $brand_id = $dbc->result($rc, $j - 1, "BRAND_ID");
//                $dbc->query("INSERT INTO `AAA_EXPORT_LINKS_PARAMS` (`GROUP_ID`, `BRAND_ID`, `PARAM_ID`, `VALUE_ID`) VALUES ('$group_id', '$brand_id', '$param_id', '$value_id');");
//                $count++;
//            }
//        }

        $link = "https://toko.ua/catalog/";

//        $r = $dbc->query("SELECT `GROUP_ID`, `BRAND_ID` FROM `AAA_EXPORT_LINKS_BRANDS` GROUP BY `GROUP_ID`, `BRAND_ID`");
//        $n = $dbc->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $group_id = $dbc->result($r, $i - 1, "GROUP_ID");
//            $group_link = $this->getGroupRowLink($group_id);
//            $brand_id = $dbc->result($r, $i - 1, "BRAND_ID");
//            $brand_link = $this->getBrandLink($brand_id);
//            if ($this->checkTableMfa($group_id) > 0 && $this->checkTable($group_id)) {
//                $where_brand = "";
//                if ($brand_id > 0) {
//                    $where_brand = "AND ex.brand_id = $brand_id";
//                }
//                $rc = $dbc->query("SELECT mf.`mfa_id` FROM `EX_TABLE_TREE_MFA_$group_id` mf
//                    LEFT JOIN `EX_TABLE_TREE_$group_id` ex ON (ex.art_id = mf.art_id)
//                WHERE 1 $where_brand
//                GROUP BY mf.`mfa_id`;");
//                $nc = $dbc->num_rows($rc);
//                for ($j = 1; $j <= $nc; $j++) {
//                    $mfa_id = $dbc->result($rc, $j - 1, "mfa_id");
//                    if ($mfa_id > 0) {
//                        $mfa_link = $this->getManufactureLink($mfa_id);
//                        $result_link = $link . $group_link . "/brandy=" . $brand_link . "/" . $mfa_link . "/";
//                        $dbc->query("INSERT INTO `AAA_EXPORT_LINKS_MFA` (`LINK`) VALUES ('$result_link');");
//                        $count++;
//                    }
//                }
//            }
//        }

//        $r = $dbc->query("SELECT `GROUP_ID`, `PARAM_ID`, `VALUE_ID`, `BRAND_ID` FROM `AAA_EXPORT_LINKS_PARAMS` GROUP BY `GROUP_ID`, `PARAM_ID`, `VALUE_ID`, `BRAND_ID`;");
//        $n = $dbc->num_rows($r);
//        for ($i = 1; $i <= $n; $i++) {
//            $group_id = $dbc->result($r, $i - 1, "GROUP_ID");
//            $group_link = $this->getGroupRowLink($group_id);
//            $brand_id = $dbc->result($r, $i - 1, "BRAND_ID");
//            $param_id = $dbc->result($r, $i - 1, "PARAM_ID");
//            $param_link = $this->getGroupParamLink($param_id);
//            $value_id = $dbc->result($r, $i - 1, "VALUE_ID");
//            $value_link = $this->getGroupValueLink($value_id, $param_id);
//            if ($this->checkTableMfa($group_id) > 0 && $this->checkTable($group_id) && $this->checkGroupParamExist($group_id, $param_id, $value_id, $brand_id)) {
//                $where_brand = "";
//                if ($brand_id > 0) {
//                    $where_brand = "AND ex.brand_id = $brand_id";
//                }
//                $rc = $dbc->query("SELECT mf.`mfa_id` FROM `EX_TABLE_TREE_MFA_$group_id` mf
//                    LEFT JOIN `EX_TABLE_TREE_$group_id` ex ON (ex.art_id = mf.art_id)
//                WHERE 1 $where_brand
//                GROUP BY mf.`mfa_id`;");
//                $nc = $dbc->num_rows($rc);
//                for ($j = 1; $j <= $nc; $j++) {
//                    $mfa_id = $dbc->result($rc, $j - 1, "mfa_id");
//                    if ($mfa_id > 0) {
//                        $mfa_link = $this->getManufactureLink($mfa_id);
//                        if ($brand_id == 0) {
//                            $result_link = $link . $group_link . "/$param_link=" . $value_link . "/" . $mfa_link . "/";
//                        } else {
//                            $brand_link = $this->getBrandLink($brand_id);
//                            $result_link = $link . $group_link . "/brandy=" . $brand_link . ";$param_link=" . $value_link . "/" . $mfa_link . "/";
//                        }
//                        $dbc->query("INSERT INTO `AAA_EXPORT_LINKS_MFA` (`LINK`) VALUES ('$result_link');");
//                        $count++;
//                    }
//                }
//            }
//        }

        return $count;
    }

    public function checkGroupParamExist($group_id, $param_id, $value_id, $brand_id) {
        $dbc = DbSingleton::getTokoCacheDb();
        $where_brand = "";
        if ($brand_id > 0) {
            $where_brand = "AND `brand_id` = $brand_id";
        }
        $r = $dbc->query("SELECT COUNT(`art_id`) as count_arts FROM `EX_TABLE_TREE_PARAMS_$group_id` 
        WHERE (`param_$param_id` = '$value_id' OR `param_$param_id` LIKE '%,$value_id%' OR `param_$param_id` LIKE '%$value_id,%') $where_brand;");
        $n = $dbc->result($r, 0, "count_arts") + 0;
        return ($n > 0);
    }

}
