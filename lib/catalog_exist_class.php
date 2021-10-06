<?php

class CatalogExistClass extends CatalogueClass
{

    use Helper;
    use Variables;

    public $products_on_page = 12;
    public $default_status_auto = 0;
    public $pagination_count = 5;
    public $filters_count = 7;

    /*
     * HEAD EXIST
     * */
    public function getHeadExistID($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $head_id = 0;
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HCG_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_id = $db->result($r, 0, "HEAD_ID");
        }
        return $head_id;
    }
    public function getHeadExistName($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $head_name = 0;
        $r = $db->query("SELECT `TEX_$postfix` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_name = $db->result($r, 0, "TEX_$postfix");
        }
        return $head_name;
    }
    public function getHeadExistLink($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $head_link = 0;
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");
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
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE `TEX_LINK` = '$group_link' LIMIT 1;");
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
        $r = $db->query("SELECT `STATUS_AUTO` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $status_auto = $db->result($r, 0, "STATUS_AUTO");
        }
        return $this->getUrlNumber($status_auto);
    }

    /*
     * PARAMS EXIST
     * */
    public function getGroupParamID($group_id, $param_link)
    {
        $db = DbSingleton::getTokoDb();
        $param_id = "";
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID` = $group_id AND `PARAM_LINK` = '$param_link' LIMIT 1;");
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
        $r = $db->query("SELECT `PARAM_NAME` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` = $param_id LIMIT 1;");
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
        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` = $param_id LIMIT 1;");
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
        $group_id = $this->getUrlNumber($group_id);
        $param_id = $this->getUrlNumber($param_id);
        $value_link = $this->getUrlString($value_link);
        $db = DbSingleton::getTokoDb();
        $value_id = "";
        $r = $db->query("SELECT `VALUE_ID` FROM `T2_TREE_VALUE_EXIST` WHERE `GROUP_ID` = $group_id AND `PARAM_ID` = $param_id AND `VALUE_LINK` = '$value_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_id = $db->result($r, 0, "VALUE_ID");
        }
        if ($param_id == 0) {
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE `BRAND_LINK` = '$value_link' LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $value_id = $db->result($r, 0, "BRAND_ID");
            }
        }
        return $value_id;
    }
    public function getGroupValueName($value_id, $param_id = 0)
    {
        $value_id = $this->getUrlNumber($value_id);
        $db = DbSingleton::getTokoDb();
        $value_name = "";
        $r = $db->query("SELECT `VALUE_NAME` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id LIMIT 1;");
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
        $value_id = $this->getUrlNumber($value_id);
        $db = DbSingleton::getTokoDb();
        $value_name = "";
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id LIMIT 1;");
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
        $value_id = $this->getUrlNumber($value_id);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $value_h1 = "";
        $r = $db->query("SELECT `VALUE_H1_$postfix` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_h1 = $db->result($r, 0, "VALUE_H1_$postfix");
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

    /*
     * check exist of group params table
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
            $dbc->query("UPDATE `$table_params` SET `status` = 0 WHERE 1;");
        }

        $params = [];
        $params_str = "";
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID` = $group_id AND `STATUS` = 1;");
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
            SELECT ex.`ART_ID` FROM toko_dba_cache.`$table` as ex
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
            $r = $dbc->query("SELECT `brand_id` FROM `$table` WHERE `art_id` = $art_id LIMIT 1;");
            $brand_id = $dbc->result($r, 0, "brand_id");
            $products[$art_id][0] = $brand_id;
        }

        $count_add = 0;
        $count_upd = 0;

        foreach ($products as $art_id => $params) {
            $params_values = "";
            $params_column = "";
            $set_column = "";
            $brand_id = $params[0];
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
            $params_values = rtrim($params_values, ",");
            $params_column = rtrim($params_column, ",");
            $set_column = rtrim($set_column, ",");
            if ($params_values != "") {
                $params_values = ", " . $params_values;
            }
            if ($params_column != "") {
                $params_column = ", " . $params_column;
            }
            if ($set_column != "") {
                $set_column = ", " . $set_column;
            }

            $r = $dbc->query("SELECT * FROM `$table_params` WHERE `art_id` = $art_id LIMIT 1;");
            $n = $dbc->num_rows($r);
            if ($n == 0) {
                $dbc->query("INSERT INTO `$table_params` (`art_id`, `brand_id`, `status` $params_column) VALUES ('$art_id', '$brand_id', 1 $params_values);");
                $count_add++;
            } else {
                $dbc->query("UPDATE `$table_params` SET `status` = 1 $set_column WHERE `art_id` = $art_id LIMIT 1;");
                $count_upd++;
            }
        }

        $r = $dbc->query("SELECT COUNT(*) as count_nulls FROM `$table_params` WHERE `status` = 0;");
        $count_del = $dbc->result($r, 0, "count_nulls") + 0;
        $dbc->query("DELETE FROM `$table_params` WHERE `status` = 0;");

        $dbc->query("ALTER TABLE `$table_params` ADD INDEX `art_id` (`art_id`);");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

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
            `price` FLOAT,
            `status` TINYINT(2),
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $dbc->query("TRUNCATE TABLE `$table`;");
        $dbc->query("TRUNCATE TABLE `$table_available`;");

        $r = $db->query("SELECT t2si.art_id, t2a.BRAND_ID
        FROM `T2_SUPPL_IMPORT` t2si
            LEFT JOIN `T2_ARTICLES` t2a ON (t2a.ART_ID = t2si.art_id)
            LEFT JOIN myparts_dba.`A_CLIENTS_STORAGE` cs ON (cs.id = t2si.client_storage_id)
        WHERE t2si.art_id > 0 AND t2si.stock_suppl > 0 AND cs.visible = 1
        GROUP BY t2si.art_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            $price = 0;
            $rr = $db->query("SELECT `price_12` FROM `T2_ARTICLES_PRICE_RATING` WHERE `art_id` = $art_id AND `in_use` = 1 LIMIT 1;");
            $nn = $db->num_rows($rr);
            if ($nn > 0) {
                $price = $db->result($rr, 0, "price_12");
            }
            $dbc->query("INSERT INTO `$table` (`art_id`, `group_id`, `brand_id`, `price`, `status`) VALUES ('$art_id', '0', '$brand_id', '$price', 1);");
            $count_add++;
        }

        $r = $db->query("SELECT t2a.ART_ID, t2a.BRAND_ID
        FROM `T2_ARTICLES` t2a
            LEFT JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.ART_ID = t2a.ART_ID)
        WHERE t2a.ART_ID > 0 AND t2asc.AMOUNT > 0
        GROUP BY t2a.art_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $brand_id = $db->result($r, $i - 1, "BRAND_ID");
            $price = 0;
            $rr = $db->query("SELECT `price_12` FROM `T2_ARTICLES_PRICE_RATING` WHERE `art_id` = $art_id AND `in_use` = 1 LIMIT 1;");
            $nn = $db->num_rows($rr);
            if ($nn > 0) {
                $price = $db->result($rr, 0, "price_12");
            }
            $dbc->query("INSERT INTO `$table` (`art_id`, `group_id`, `brand_id`, `price`, `status`) VALUES ('$art_id', '0', '$brand_id', '$price', 1);");
            $count_add++;
        }

        // fixed brand_id = 0 in T2_SUPPL_IMPORT
        $db->query("UPDATE `T2_SUPPL_IMPORT` t2si
            INNER JOIN toko_dba_cache.`EX_TABLE_TREE` ex (ON ex.art_id = t2si.art_id)
        SET t2si.art_id = 0
        WHERE ex.brand_id = 0;");

        // fixed brand_id = 0 in T2_SUPPL_ARTICLES_IMPORT
        $db->query("DELETE t2sai
        FROM `T2_SUPPL_ARTICLES_IMPORT` t2sai
            INNER JOIN toko_dba_cache.`EX_TABLE_TREE` ex ON (ex.art_id = t2sai.art_id)
        WHERE ex.brand_id = 0;");

        // deleted nulls
        $dbc->query("DELETE FROM `$table` WHERE `brand_id` = 0;");

        $dbc->query("INSERT INTO `$table_available` (`art_id`, `brand_id`, `group_id`, `price`, `status`)
        SELECT ex.art_id, ex.brand_id, tt.group_id, tt.price, ex.status 
        FROM `$table` ex
            LEFT JOIN toko_dba.`T2_TREE_ARTS_EXIST` tt ON (tt.ART_ID = ex.art_id)
        WHERE tt.group_id IS NOT NULL
        GROUP BY ex.art_id, tt.group_id;");

        return "ADDED: $count_add";
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
            SELECT `art_id`, `brand_id`, `status` FROM `$table_available` WHERE `group_id` = $group_id;");
        return "UPDATED $group_id";
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
            LEFT JOIN `T_types` tt ON (tt.TYP_ID = tl.TYP_ID)
            LEFT JOIN `T_models` tm ON (tm.MOD_ID = tt.TYP_MOD_ID)
        WHERE `ART_ID` IN (
          SELECT ex.`ART_ID` FROM toko_dba_cache.`$table` as ex
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
            $dbc->query("UPDATE `$table_mfa` SET `status` = 0 WHERE 1;");
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

        $count_add = 0;
        $count_upd = 0;
        foreach ($arts as $art_id => $mfa_ids) {
            foreach ($mfa_ids as $mfa_id => $models) {
                foreach ($models as $model) {
                    $r = $dbc->query("SELECT COUNT(`art_id`) as count_art FROM `$table_mfa` WHERE `art_id` = $art_id AND `mfa_id` = $mfa_id AND `model` = '$model';");
                    $n = $dbc->result($r, 0, "count_art") + 0;
                    if ($n == 0) {
                        $dbc->query("INSERT INTO `$table_mfa` (`art_id`, `mfa_id`, `model`, `status`) VALUES ($art_id, $mfa_id, '$model', 1);");
                        $count_add++;
                    } else {
                        $dbc->query("UPDATE `$table_mfa` SET `status` = 1 WHERE `art_id` = $art_id;");
                        $count_upd++;
                    }
                }
            }
        }

        $r = $dbc->query("SELECT COUNT(*) as count_nulls FROM `$table_mfa` WHERE `status` = 0;");
        $count_del = $dbc->result($r, 0, "count_nulls") + 0;
        $dbc->query("DELETE FROM `$table_mfa` WHERE `status` = 0;");

        $dbc->query("ALTER TABLE `$table_mfa` ADD INDEX `art_id` (`art_id`);");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    public function getCatalogBreadCrumb($group_id, $params, $filters_h1, $source_link)
    {
        $arr = [];

        $arr[] = ["name" => "{seo_site_toko}", "link" => $this->getSiteLink()];
        $arr[] = ["name" => "{site_catalog}", "link" => $this->getSiteLink() . "$this->catalog_link/"];

        if ($group_id > 0) {
            $head_id = $this->getHeadExistID($group_id);
            $head_name = $this->getHeadExistName($head_id);
            $head_link = $this->getHeadExistLink($head_id);

            $arr[] = ["name" => "$head_name", "link" => $this->getSiteLink() . "$this->catalog_link/$head_link/"];

            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);

            $arr[] = ["name" => "$group_name", "link" => $this->getSiteLink() . "$this->catalog_link/$group_link/"];

            if (!empty($params)) {
                if (count($params) > 1) {
                    $arr2 = [];
                    foreach ($params as $param_id => $values) {
                        if (count($values) == 1) {
                            if ($param_id == 0) {
                                $brand_link = $brand_name = "";
                                foreach ($values as $value_id) {
                                    $brand_name = $this->getBrandName($value_id);
                                    $brand_link = $this->getBrandName($value_id);
                                }
                                $arr2[] = ["name" => "$group_name $brand_name", "link" => $this->getSiteLink() . "$this->catalog_link/$group_link/brandy=$brand_link/"];
                            }
                        } else {
                            $arr2 = [];
                            break;
                        }
                    }
                    $arr = array_merge($arr, $arr2);
                }
                $arr[] = ["name" => "$filters_h1", "link" => "$source_link"];
            }
        }

        return $arr;
    }

    public function getPaginRow($text, $link, $class = "")
    {
        $form = "<li class=\"page-item {pagin_class}\"><a class=\"page-link\" rel=\"noopener\" href=\"{pagin_link}\">{pagin_text}</a></li>";
        $form = str_replace("{pagin_text}", $text, $form);
        $form = str_replace("{pagin_link}", $link, $form);
        $form = str_replace("{pagin_class}", $class, $form);
        return $form;
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

        $min_count = $this->pagination_count;
        $max_count = $pages_count - $min_count + 1;
        $pred_page = $page - 1;
        $next_page = $page + 1;

        if ($pages_count > $min_count) {

            if ($page < $min_count) {
                for ($i = 1; $i <= $min_count; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    $link = ($i > 1) ? "?page=$i" : ".";
                    $pagination .= $this->getPaginRow($i, $link, $active);
                }
                $pagination .= $this->getPaginRow("...", "#");
                $link = ($pages_count > 1) ? "?page=$pages_count" : ".";
                $pagination .= $this->getPaginRow($pages_count, $link);
            }

            elseif ($page > $max_count) {
                $pagination .= $this->getPaginRow("1", "./");
                $pagination .= $this->getPaginRow("...", "#");
                for ($i = $max_count; $i <= $pages_count; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    $link = ($i > 1) ? "?page=$i" : ".";
                    $pagination .= $this->getPaginRow($i, $link, $active);
                }
            }

            elseif ($page >= $min_count && $page <= $max_count) {
                $pagination .= $this->getPaginRow("1", "./");
                $pagination .= $this->getPaginRow("...", "#");

                $link = ($pred_page > 1) ? "?page=$pred_page" : ".";
                $pagination .= $this->getPaginRow($pred_page, $link);
                $link = ($page > 1) ? "?page=$page" : ".";
                $pagination .= $this->getPaginRow($page, $link, "active");
                $link = ($next_page > 1) ? "?page=$next_page" : ".";
                $pagination .= $this->getPaginRow($next_page, $link);

                $pagination .= $this->getPaginRow("...", "#");
                $link = ($pages_count > 1) ? "?page=$pages_count" : ".";
                $pagination .= $this->getPaginRow($pages_count, $link);
            }

        } else {
            for ($i = 1; $i <= $pages_count; $i++) {
                $active = ($i == $page) ? "active" : "";
                $link = ($i > 1) ? "?page=$i" : ".";
                $pagination .= $this->getPaginRow($i, $link, $active);
            }
        }

        $list = $this->getHtmlForm("catalog_exist/pagination");
        $list = str_replace("{pagination_range}", $pagination, $list);
        $list = str_replace("{pred_disabled_class}", ($page == 1) ? "disabled" : "", $list);
        $list = str_replace("{next_disabled_class}", ($page == $pages_count) ? "disabled" : "", $list);
        $list = str_replace("{link_pred}", ($pred_page > 1) ? "?page=$pred_page" : ".", $list);
        $list = str_replace("{link_next}", ($next_page > 1) ? "?page=$next_page" : ".", $list);

        if ($pages_count == 1) {
            $list = "";
        }

        return $this->replaceLang($list);
    }

    /*
     * get filter `param + value` ids
     * */
    public function getCheckedFilters($group_id, $filters)
    {
        $params = [];
        if (!empty($filters)) {
            $params_arr = explode(";", $filters);
            foreach ($params_arr as $params_item) {
                $params_item_str = explode("=", $params_item);
                $param_link = $params_item_str[0];
                $params_item_values = $params_item_str[1];
                $params_item_values_arr = explode(",", $params_item_values);
                foreach ($params_item_values_arr as $value_link) {
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

    public function checRedirects($filters)
    {
        $status = 0;
        $arr = ["neolux%D0%92%C2%AE", "JP%20GROUP", "HENGST%20FILTER", "continental%C2%A0rear-ctrl", "continental-aqua-ctrl%C2%A0set", "continental-aqua-ctrl%C2%A0multi"];
        $arr_new = [
            "neolux%D0%92%C2%AE" => "neolux",
            "JP%20GROUP" => "jp-group",
            "HENGST%20FILTER" => "hengst-filter",
            "continental%C2%A0rear-ctrl" => "continental-rear-ctrl",
            "continental-aqua-ctrl%C2%A0set" => "continental-aqua-ctrl-set",
            "continental-aqua-ctrl%C2%A0multi" => "continental-aqua-ctrl-multi"
        ];

        if (!empty($filters)) {
            $params_arr = explode(";", $filters);
            foreach ($params_arr as $params_item) {
                $params_item_str = explode("=", $params_item);
                $params_item_values = $params_item_str[1];
                $params_item_values_arr = explode(",", $params_item_values);
                foreach ($params_item_values_arr as $value_link) {
                    if (in_array($value_link, $arr)) {
                        $status++;
                        $filters = str_replace("$value_link", $arr_new["$value_link"], $filters);
                    }
                }
            }
        }

        return array($status, $filters);
    }

    /*
     * get params from selected group
     * */
    public function getExistedParams($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $params = [];
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID` = $group_id AND `STATUS` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $params[] = $param_id;
        }
        return $params;
    }

    /*
     * get params/brands where for catalog filters
     * */
    public function getParamsWhere($where, $param_id, $values)
    {
        $param_name = ($param_id == 0) ? "t.`brand_id`" : "tp.`param_$param_id`";
        if (!empty($values)) {
            $where .= " AND (";
            $count = 0;
            foreach ($values as $value_id) {
                $count++;
                $separator = ($count > 1) ? "OR" : "";
                $where .= " $separator ($param_name = '$value_id' OR $param_name LIKE '%,$value_id%' OR $param_name LIKE '%$value_id,%')";
            }
            $where .= ") ";
        }
        return $where;
    }

    /*
     * get filter where
     * */
    public function getFiltersWhere($params)
    {
        $where = "";
        foreach ($params as $param_id => $values) {
            $where = $this->getParamsWhere($where, $param_id, $values);
        }
        return $where;
    }

    /*
     * get filter where selected
     * */
    public function getFiltersWhereSelected($params, $sel_param_id)
    {
        $where = "";
        foreach ($params as $param_id => $values) {
            if ($sel_param_id != $param_id) {
                $where = $this->getParamsWhere($where, $param_id, $values);
            }
        }
        return $where;
    }

    /*
     * get count parts from all group
     * */
    public function getPartsCountGroup($group_id, $params, $where_link_arts = "")
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $n = 0;
        $r = $dbc->query("SHOW TABLES LIKE '$table_params';");
        $nc = $dbc->num_rows($r);
        if ($nc > 0) {
            if (empty($params)) {
                $r = $dbc->query("SELECT COUNT(t.`art_id`) as count_arts FROM `$table` t WHERE 1 $where_link_arts ;");
            } else {
                $where = $this->getFiltersWhere($params);
                $r = $dbc->query("SELECT SUM(ex.col_arts) as count_arts FROM (
                    SELECT COUNT(t.`art_id`) as col_arts 
                    FROM `$table` t
                        LEFT JOIN `$table_params` tp ON (tp.`art_id` = t.`art_id`)
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
//    public function getPartsCountWill($group_id, $params, $sel_param_id, $sel_value_id, $where_mfa, $where_link_arts)
//    {
//        $dbc = DbSingleton::getTokoCacheDb();
//        $table = "EX_TABLE_TREE_$group_id";
//        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
//        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
//
//        $r = $dbc->query("SHOW TABLES LIKE '$table_params';");
//        $n = $dbc->num_rows($r);
//        if ($n > 0) {
//            $params[$sel_param_id][] = $sel_value_id;
//            $where = "";
//            foreach ($params as $param_id => $values) {
//                $where = $this->getParamsWhere($where, $param_id, $values);
//            }
//
//            if ($where_mfa == "") {
//                $r = $dbc->query("SELECT SUM(ex.col_arts) as sum_arts FROM (
//                    SELECT COUNT(t.`art_id`) as col_arts
//                    FROM `$table` t
//                        LEFT JOIN `$table_params` tp ON (tp.`art_id` = t.`art_id`)
//                    WHERE 1 $where $where_link_arts
//                    GROUP BY t.`art_id`
//                ) as ex ;");
//            } else {
//                $r = $dbc->query("SELECT SUM(ex.col_arts) as sum_arts FROM (
//                     SELECT COUNT(t.art_id) as col_arts
//                     FROM `$table` t
//                        LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id)
//                        LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
//                    WHERE 1 $where $where_mfa $where_link_arts
//                    GROUP BY t.art_id
//                ) as ex ;");
//            }
//            $n = $dbc->result($r, 0, "sum_arts");
//        }
//        return $n;
//    }

    /*
     * get mfa where
     * */
    public function getMfaWhere($mfa_id = 0, $model = "", $status_auto = 0, $status_auto_type = 0)
    {
        $where_mfa = "";
        if ($mfa_id > 0) {
            if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 1)) {
                $where_mfa .= " AND tm.`mfa_id` = $mfa_id";
                if ($model != "") {
                    $where_mfa .= " AND tm.`model` = '$model'";
                }
            }
        }
        return $where_mfa;
    }

    public function getArtsLinksWhere($status_auto = 0, $status_auto_type = 0, $typ_id = 0)
    {
        $where_link_arts = "";
        if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 1)) {
            if ($typ_id != "") {
                $typ_arts = $this->getPartsCatalogueAuto($typ_id);
                $where_link_arts = " AND t.art_id IN (" . implode(",", $typ_arts) . ") ";
            }
        }
        return $where_link_arts;
    }

    public function showPartsCatalogueError($group_id, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id, $filters_h1)
    {
        $automan = new AutoClass();
        $form = $this->getHtmlForm("catalog_exist/error");
        $form = str_replace("{form_car}", $this->getPartsCatalogueCars($group_id, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id), $form);
        $form = $this->replaceLang($form);
        $form = str_replace("{h1_text}", "<b>$filters_h1</b>", $form);
        $form = str_replace("{vin_text}", "<a class=\"blue-a\" onclick=\"$('#VinFormPhone').modal('show');\">{vin_order}</a>", $form);
        $form = str_replace("{parts_cars}", $this->drawLoader(), $form);
        $catalog_text = "{in_catalog_strs}";
        $catalog_link = $this->getSiteLink() . $this->cars_link . "/";
        if ($mfa_id > 0) {
            $mfa_name = $automan->getMfaBrand($mfa_id);
            $mfa_link = $this->getManufactureLink($mfa_id);
            $catalog_text .= " {on_cap} $mfa_name";
            $catalog_link .= "$mfa_link/";
            if ($model != "") {
                $model_link = $this->getModelLink($model);
                $catalog_text .= "$model";
                $catalog_link .= "$model_link/";
            }
        }
        $form = str_replace("{catlog_link}", "<a class=\"blue-a\" href=\"$catalog_link\">$catalog_text</a>", $form);
        return $this->replaceLang($form);
    }

    /*
     * show catalog form
     * */
    public function showPartsCatalogueParams($group_id, $page = 1, $filters = [], $params = [], $mfa_id = 0, $model = "", $model_id = 0, $status_auto = 0, $status_auto_type = 0, $source_link = "")
    {
        $typ_id = $this->getCookieAuto();
        $automan = new AutoClass();
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $limit = $this->getSearchLimit($page);
        $group_text = $this->getGroupRowName($group_id);
        $where_mfa = $this->getMfaWhere($mfa_id, $model, $status_auto, $status_auto_type);
        $where_link_arts = $this->getArtsLinksWhere($status_auto, $status_auto_type, $typ_id);

        $check_group = $this->checkTable($group_id);

        $arts = [];
        if ($check_group) {
            if (empty($filters)) {
                $query = "SELECT t.art_id FROM `$table` t
                    LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
                    LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
                WHERE 1 $where_mfa $where_link_arts
                GROUP BY t.art_id";
            } else {
                $where = $this->getFiltersWhere($params);
                $query = "SELECT t.art_id FROM `$table` t
                    LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id)
                    LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
                WHERE 1 $where $where_mfa $where_link_arts
                GROUP BY t.art_id";
            }
            $query_limit = "$query $limit ;";
        }

        $r = $dbc->query($query_limit);
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $dbc->result($r, $i - 1, "art_id");
            array_push($arts, $art_id);
        }

        $art_id_str = implode(",", array_unique($arts));
        list($list) = $this->searchList($art_id_str, 1, "", "", $mfa_id, $model, $status_auto);

        $count = $this->getPartsCount($group_id, $query);

        $pagination_form = $this->getPartsPaginationForm($count, $page);

        list($h1_text, $filters_title, $filters_btn, $filters_count) = $this->getPartsFiltersItems($group_id, $page, $params, $mfa_id, $model, $model_id);

        $translit = "";
        if ($mfa_id > 0) {
            $translit = $automan->getCarManufTranslit($mfa_id, $model);
            $translit = "<span style=\"font-weight: 400;\">$translit</span>";
        }

        $pager = "";
        if ($page > 1) {
            $pager = " - {pager_cap} $page";
            $pager = $this->replaceLang($pager);
        }

        $breadcrumbs_script = "";
        if (empty($art_id_str)) {
            $form = $this->showPartsCatalogueError($group_id, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id, $h1_text);
            $form = str_replace("{mfa_link}", $this->getManufactureLink($mfa_id), $form);
            $form = str_replace("{model_link}", $this->getModelLink($model), $form);
        } else {
            $form = $this->getHtmlForm("catalog_exist/form");
            $form = str_replace("{details_group_id}", $group_id, $form);
            $form = str_replace("{mfa_link}", $this->getManufactureLink($mfa_id), $form);
            $form = str_replace("{model_link}", $this->getModelLink($model), $form);
            $form = str_replace("{parts_name}", $group_text, $form);
            $form = str_replace("{parts_list}", $list, $form);
            $form = str_replace("{parts_h1}", "$h1_text $translit $pager", $form);
            $form = str_replace("{parts_count}", "{unselect_cap} $count " . $this->getGoodsCap($count), $form);
            $form = str_replace("{parts_filters}", "$filters_btn", $form);
            $form = str_replace("{parts_pagination_list}", $pagination_form, $form);
            $filterData = $this->getPartsFiltersForm($group_id, $params, $mfa_id, $model, $where_mfa, $where_link_arts);
            $form = str_replace("{parts_params}", $filterData["form"], $form);

            $breadcrumbsData = $this->getBreadCrumbForm($this->getCatalogBreadCrumb($group_id, $params, $h1_text, $source_link));
            $breadcrumbs_script = $breadcrumbsData["script"];
            $form = str_replace("{parts_breadcrumbs}", $breadcrumbsData["form"], $form);
            $form = str_replace("{status_auto}", $status_auto, $form);
            $form = str_replace("{filters_count}", $filters_count, $form);
            $form = str_replace("{filters_style}", ($filters_count == 0) ? "none" : "", $form);
            $form = str_replace("{parts_cars}", $this->drawLoader(), $form);
            $form = str_replace("{parts_params_cars}", $this->getPartsCatalogueParamsCars($group_id, $params, $status_auto, $status_auto_type, $typ_id), $form);
            $form = str_replace("{parts_seo}", $this->getPartsCatalogueSeo($group_id, $page, $params, $h1_text, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id), $form);
            $form = str_replace("{parts_states}", $this->getPartsCatalogueStates($group_id), $form);
        }

        $max_pages_count = ceil($count / $this->products_on_page);

//        $description = $this->replaceLang("{site_description_catalog}");
//        $description = str_replace("{h1_caption}", $h1_text, $description);
//        $description = str_replace("{h1_caption_parrent}", $group_text, $description);

        $description = $this->replaceLang("{site_catalog_group_description}");
        $description = str_replace("{h1_text}", $h1_text, $description);

        if (!empty($filters)) {
            list($count_brands) = $this->getCatalogParamsCount($params);
            if ($count_brands > 0) {
                $description = $this->replaceLang("{site_catalog_brand_description}");
                $description = str_replace("{h1_text}", $h1_text, $description);
                $description = str_replace("{h1_parrent}", $group_text, $description);
            }
        }

        return array("form" => $form, "title" => $filters_title, "h1" => $h1_text, "pages_count" => $max_pages_count, "description" => $description, "script" => $breadcrumbs_script);
    }

    public function drawLoader()
    {
        $form = $this->getHtmlForm("cars/loader-gear");
        $list = $this->getHtmlForm("loader");
        $form = str_replace("{form_range}", $list, $form);
        return $this->replaceLang($form);
    }

    /*
     * show filter items form
     * */
    public function getPartsFiltersItems($group_id, $page = 1, $params = [], $mfa_id = 0, $model = "", $model_id = 0)
    {
        $filters_btn = "";
        $count_values = 0;
        if (!empty($params)) {
            $count_values = 0;
            foreach ($params as $param_id => $values) {
                foreach ($values as $value_id) {
                    $count_values++;
                    $value_name = $this->getGroupValueName($value_id, $param_id);
                    $link = $this->getPartsFilterLinks($group_id, $params, $param_id, $value_id, $mfa_id, $model);
                    $filters_btn .= "<a href=\"$link\" class=\"btn btn-sm\">$value_name &times;</a>";
                }
            }
            if ($count_values > 1) {
                $group_link = $this->getGroupRowLink($group_id);
                $car_link = "$this->catalog_link/$group_link/";
                if ($mfa_id > 0) {
                    $mfa_link = $this->getManufactureLink($mfa_id);
                    $car_link .= "$mfa_link/";
                }
                if ($model != "") {
                    $model_link = $this->getModelLink($model);
                    $car_link .= "$model_link/";
                }
                $filters_btn = "<a class=\"btn btn-sm\" href=\"" . $this->getSiteLink() . "$car_link\">{filter_cap_empty} &times;</a>" . $filters_btn;
            }
        }

        $h1_text = $this->getCatalogH1($group_id, $params, $mfa_id, $model, $model_id);

//        $filters_title = $this->getCatalogTitleCache($str_link);
//        if ($filters_title == "") {
//            $filters_title = $this->getCatalogTitle($group_id, $params, $h1_text, $mfa_id, $model);
//        }

        $filters_title = $this->replaceLang("{site_catalog_group}");
        $filters_title = str_replace("{h1_text}", $h1_text, $filters_title);

        if (!empty($params)) {
            list($count_brands) = $this->getCatalogParamsCount($params);
            if ($count_brands > 0) {
                $filters_title = $this->replaceLang("{site_catalog_brand}");
                $filters_title = str_replace("{h1_text}", $h1_text, $filters_title);
            }
        }

        if ($page > 1) {
            $filters_title .= " - {pager_cap} $page";
            $filters_title = $this->replaceLang($filters_title);
        }

        return array($h1_text, $filters_title, $filters_btn, $count_values);
    }

    /*
     * get filters values
     * */
    public function getPartsFiltersArr($group_id, $params_check = [], $where_mfa = "", $where_link_arts = "")
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $params = [];
        $checked_params_keys = [];
        $unchecked_params_keys = [];

        $exist_params = $this->getExistedParams($group_id);

        if (empty($params_check)) {
            $r = $dbc->query("SELECT tp.*, t.brand_id as brand_cur_id 
            FROM `$table` t
                LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
                LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
            WHERE 1 $where_mfa $where_link_arts
            GROUP BY t.art_id ;");
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $brand_id = $dbc->result($r, $i - 1, "brand_cur_id");
                $params[0][] = $brand_id;
                foreach ($exist_params as $param_id) {
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
            $existed_params_keys = array_values($exist_params);
            $existed_params_keys[] = 0;
            $unchecked_params_keys = array_diff($existed_params_keys, $checked_params_keys);

            foreach ($checked_params_keys as $param_id) {
                $where = $this->getFiltersWhereSelected($params_check, $param_id);
                $value_arr = $this->getFiltersParamValues($group_id, $param_id, $where, $where_mfa, $where_link_arts);
                $params[$param_id] = $value_arr;
            }

            foreach ($unchecked_params_keys as $param_id) {
                $where = $this->getFiltersWhere($params_check);
                $value_arr = $this->getFiltersParamValues($group_id, $param_id, $where, $where_mfa, $where_link_arts);
                $params[$param_id] = $value_arr;
            }
        }

        foreach ($params as $param_id => $values) {
            $params[$param_id] = array_unique($values);
        }

        $arr = [];
        if (!empty($params)) {
            // error page key '' and 0 error
            $keys = array_keys($params);
            foreach ($keys as $key_id => $key) {
                if ($key == "") {
                    $keys[$key_id] = 0;
                }
            }
            $keys = implode(",", $keys);

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

        return array("arr" => $arr, "checked" => $checked_params_keys, "unchecked" => $unchecked_params_keys);
    }

    /*
     * show filter form
     * , $query = ""
     * */
    public function getPartsFiltersForm($group_id, $params = [], $mfa_id = 0, $model = "", $where_mfa = "", $where_link_arts = "")
    {
        $start = microtime(true);
        $paramData = $this->getPartsFiltersArr($group_id, $params, $where_mfa, $where_link_arts);
        $arr = $paramData["arr"];

//        $count_arts_full = $this->getPartsCount($group_id, $query);
//        $checked_params_keys = $paramData["checked"];
//        $unchecked_params_keys = $paramData["unchecked"];

        $list_params = "";
        if (!empty($arr)) {
            foreach ($arr as $param_id => $values) {
                $param_name = $this->getGroupParamName($param_id);
                if (!empty($values)) {
                    $list_params .= "
                    <div class=\"hidden-list\">
                        <div class=\"hidden-list-title\">$param_name</div>
                        <div class=\"hidden-list-search\">
                            <input type=\"text\" class=\"text-filter\" onkeyup=\"textParamSearch('$param_id')\" data-attr=\"$param_id\" placeholder=\"{search_by_name}\">
                        </div>
                        <div class=\"hidden-list-content\" data-attr=\"$param_id\">";
                    $items = [];
                    foreach ($values as $value_id) {
                        $value_name = $this->getGroupValueName($value_id, $param_id);
                        $checked = (in_array($value_id, $params[$param_id]));
                        $link = $this->getPartsFilterLinks($group_id, $params, $param_id, $value_id, $mfa_id, $model);

                        $count_arts = 0;
//                        if (!empty($params)) {
//                            if (in_array($param_id, $checked_params_keys)) {
//                                $count_arts = $this->getPartsCountWill($group_id, $params, $param_id, $value_id, $where_mfa, $where_link_arts);
//                                $count_arts = $count_arts - $count_arts_full;
//                            }
//                            if (in_array($param_id, $unchecked_params_keys)) {
//                                $count_arts = $this->getPartsCountWill($group_id, $params, $param_id, $value_id, $where_mfa, $where_link_arts);
//                            }
//                        } else {
//                            $count_arts = $this->getPartsCountWill($group_id, $params, $param_id, $value_id, $where_mfa, $where_link_arts);
//                        }
                        $items[$value_id] = compact("value_name", "link", "checked", "count_arts");
                    }

                    $arr_checked = [];
                    $arr_value_name = [];
                    $arr_count_arts = [];
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
//                        $count_arts = $item["count_arts"];
//                        $count_arts_label = "($count_arts)";
//                        if (!empty($params)) {
//                            if (in_array($param_id, $checked_params_keys)) {
//                                $count_arts_label = "[+$count_arts]";
//                            }
//                        }
                        $checked_label = "<span class=\"fas fa-square unchecked\"></span>";
                        if ($checked) {
                            $checked_label = "<span class=\"fas fa-check-square checked\"></span>";
//                            $count_arts_label = "";
                        }
//                        $count_arts_label
                        $list_params .= "
                        <a href=\"$link\" class=\"hidden-list-content__item\">
                            <div class=\"hidden-list-content__item-left\" data-param-value=\"$param_id\">$checked_label <span>$value_name</span></div> 
                            <div class=\"hidden-list-content__item-right\"></div>
                        </a>";
                    }

                    $bottom = "";
                    if (count($values) > $this->filters_count) {
                        $more_count = count($values) - $this->filters_count;
                        $bottom = "
                        <div class=\"hidden-list-more\" onclick=\"toggleSideMenu(this);\" data-attr-more=\"$param_id\">
                            <span>{more_cap} $more_count</span>
                            <span class=\"none\">{hide_cap}</span>
                        </div>";
                    }
                    $list_params .= "
                    </div>$bottom</div>";
                }
            }
        }

        $form = $this->getHtmlForm("catalog_exist/params");
        $form = str_replace("{list_params}", $list_params, $form);

        $time = microtime(true) - $start;

        return array("form" => $this->replaceLang($form), "time" => $time);
    }

    public function getPartsFiltersForm2($group_id, $params = [], $filters_h1 = "", $sel_param_id = "")
    {
        $paramData = $this->getPartsFiltersArr($group_id, $params, "", "");
        $arr = $paramData["arr"];
        $count_params = 0;

        $list_params = "";
        if (!empty($arr)) {
            foreach ($arr as $param_id => $values) {
                // except select param
                if ($sel_param_id === "" || ($sel_param_id !== "" && $sel_param_id != $param_id)) {
                    if (!empty($values)) {
                        $count_params++;
                        $param_name = $this->getGroupParamName($param_id);
                        if ($count_params == 1) {
                            $list_params .= "<span>{seo_catalog_filters_cap_1} $filters_h1 {seo_catalog_filters_cap_2} $param_name: ";
                        } else {
                            $list_params .= "<span>$filters_h1 $param_name: ";
                        }
                        foreach ($values as $value_id) {
                            $value_name = $this->getGroupValueName($value_id, $param_id);
                            $link = $this->getPartsFilterLinks($group_id, $params, $param_id, $value_id, 0, "");
                            $checked = (in_array($value_id, $params[$param_id]));
                            if (!$checked) {
                                $list_params .= "<a href=\"$link\">$value_name</a>, ";
                            }
                        }
                        $list_params = rtrim($list_params, ", ");
                        $list_params .= ". </span><br>";
                    }
                }
            }
        }

        return $this->replaceLang($list_params);
    }

    /*
     * get values
     * */
    public function getFiltersParamValues($group_id, $param_id, $where = "", $where_mfa = "", $where_link_arts = "")
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $value_arr = [];
        $r = $dbc->query("SELECT tp.*, t.brand_id as brand_cur_id 
        FROM `$table` t
            LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
            LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
        WHERE 1 $where $where_mfa $where_link_arts
        GROUP BY t.art_id;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            if ($param_id == 0) {
                $value_str = $dbc->result($r, $i - 1, "brand_cur_id");
            } else {
                $value_str = $dbc->result($r, $i - 1, "param_$param_id");
            }
            if (!empty($value_str)) {
                foreach (explode(",", $value_str) as $item) {
                    if (!in_array($item, $value_arr)) {
                        $value_arr[] = $item;
                    }
                }
            }
        }
        return $value_arr;
    }

    /*
     * get catalog link
     * */
    public function getPartsFilterLinks($group_id, $params, $param_id, $value_id, $mfa_id = 0, $model = "")
    {
        $link = "";

        if (!empty($params)) {
            $unset = 0;
            foreach ($params as $param => $values) {
                foreach ($values as $key => $value) {
                    if ($param == $param_id && $value == $value_id) {
                        $unset++;
                        unset($params[$param_id][$key]);
                        if (empty($params[$param])) {
                            unset($params[$param]);
                        }
                    }
                    elseif (!in_array($value_id, $params[$param_id]) && $unset == 0) {
                        $params[$param_id][] = $value_id;
                    }
                }
            }
        } else {
            $params[$param_id][] = $value_id;
        }

        ksort($params);

        foreach ($params as $param => $values) {
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
        $list = $this->getSiteLink() . "$this->catalog_link/";
        if ($group_id > 0) {
            $list .= "$group_link/";
            if ($link != "") {
                $list .= "$link/";
            }
            elseif ($mfa_id > 0) {
                $list .= "auto/";
            } else {
                $list .= "";
            }
            if ($mfa_id > 0) {
                $mfa_link = $this->getManufactureLink($mfa_id);
                $list .= "$mfa_link/";
            }
            if ($model != "") {
                $model_link = $this->getModelLink($model);
                $list .= "$model_link/";
            }
        }

        return $list;
    }

    /*
     * show param cars form
     * */
    public function getPartsCatalogueParamsCars($group_id, $params, $status_auto = 0, $status_auto_type = 0, $typ_id = 0)
    {
        $automan = new AutoClass();
        $form = "";
        if ($status_auto == 1 && $typ_id != "") {
            $car_checked = $all_checked = $car_count = $all_count = "";
            list($mfa_id, $model) = $automan->getCarInfo($typ_id);
            $mfa_name = $automan->getMfaBrand($mfa_id);
            $typ_text = "$mfa_name $model";
            // всі запчастини
            if ($status_auto_type == 0) {
                $car_checked = "<i class=\"fas fa-circle unchecked\"></i>";
                $all_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                $where_link_arts = "";
                $typ_arts = $this->getPartsCatalogueAuto($typ_id);
                if (!empty($typ_arts)) {
                    $where_link_arts = " AND t.art_id IN (" . implode(",", $typ_arts) . ") ";
                }
                $count = $this->getPartsCountGroup($group_id, $params, $where_link_arts);
                $car_count = "($count)";
            }
            // вибрана машина
            if ($status_auto_type == 1) {
                $car_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                $all_checked = "<i class=\"fas fa-circle unchecked\"></i>";
                $count = $this->getPartsCountGroup($group_id, $params);
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
    public function getPartsCatalogueCars($group_id, $mfa_link = "", $model_link = "", $status_auto = 0, $status_auto_type = 0, $typ_id = 0)
    {
        $products = new ProductsClass();
        $form = "";
        if ($status_auto == 0 || $status_auto == 1) {
            if ($typ_id != "") {
                if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 1)) {
                    $form = $products->getCarsGarage();
                } else {
                    $form = $products->getCarsSearch($mfa_link, $model_link, $group_id);
                }
            } else {
                $form = $products->getCarsSearch($mfa_link, $model_link, $group_id);
            }
        }
        return $this->replaceLang($form);
    }

    /*
     * get products from t2_links
     * */
    public function getPartsCatalogueAuto($typ_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $arts = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID` = $typ_id GROUP BY `ART_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $arts[] = $art_id;
        }
        return $arts;
    }

    /*
     * show catalog seo filters form
     * */
    public function getCatalogSeoFiltersForm($group_id, $params)
    {
        $list = "";
        if (count($params) == 2) {
            $param_id_1 = array_keys($params)[0];
            $params_1[$param_id_1] = $params[$param_id_1];
            $filters_h1_1 = $this->getCatalogH1($group_id, $params_1);
            $list = $this->getPartsFiltersForm2($group_id, $params_1, $filters_h1_1);

            $param_id_2 = array_keys($params)[0];
            $params_2[$param_id_1] = $params[$param_id_2];
            $filters_h1_2 = $this->getCatalogH1($group_id, $params_2);
            $list .= $this->getPartsFiltersForm2($group_id, $params_2, $filters_h1_2);
        }
        if (count($params) == 1) {
            $sel_param_id = array_keys($params)[0];
            $filters_h1 = $this->getCatalogH1($group_id, $params);
            $list = $this->getPartsFiltersForm2($group_id, $params, $filters_h1, $sel_param_id);
        }
        return $list;
    }

    /*
     * show products seo form
     * */
    public function getPartsCatalogueSeo($group_id, $page = 1, $params = [], $h1_text = "", $mfa_id = 0, $model = "", $status_auto = 0, $status_auto_type = 0, $typ_id = 0)
    {
        $menu = new MenuClass();
        $form = $this->getHtmlForm("catalog_exist/seo");
        if ($page <= 1) {
            if ($status_auto == 0 || ($status_auto == 1 && $status_auto_type == 0)) {
                // SEO filters
                if ($typ_id == "" || ($status_auto == 1 && $status_auto_type == 0)) {
                    $list_filters = $this->getCatalogSeoFiltersForm($group_id, $params);
                    $form = str_replace("{seo_filters}", $list_filters, $form);
                    $form = str_replace("{seo_filters_style}", ($list_filters == "") ? "none" : "", $form);
                }
                // SEO details
                if ($typ_id == "" || ($status_auto == 1 && $status_auto_type == 0)) {
                    if ($mfa_id > 0) {
                        if ($model != "") {
                            $form = str_replace("{seo_auto}", $this->getGroupCarModIDList($group_id, $mfa_id, $model), $form);
                        } else {
                            $form = str_replace("{seo_auto}", $this->getGroupCarModList($group_id, $mfa_id), $form);
                        }
                    } else {
                        $form = str_replace("{seo_auto}", $this->getGroupCarMfaList($group_id), $form);
                    }
                    $form = str_replace("{seo_style}", "", $form);
                }
                // SEO popular request
                if ($typ_id == "" || ($status_auto == 1 && $status_auto_type == 0)) {
                    $form = str_replace("{seo_popular}", $menu->getCatalogFaqForm($h1_text), $form);
                }
            }
        }
        $form = str_replace("{seo_filters}", "", $form);
        $form = str_replace("{seo_auto}", "", $form);
        $form = str_replace("{seo_popular}", "", $form);
        $form = str_replace("{seo_style}", "none", $form);
        $form = str_replace("{seo_filters_style}", "none", $form);
        return $this->replaceLang($form);
    }

    /*
     * get MOD ID list
     * */
    public function getGroupCarModIDList($group_id, $mfa_id_sel = 0, $model = "")
    {
        $group_id = $this->getUrlNumber($group_id);
        $db = DbSingleton::getTokoDb();
        $link = "$this->catalog_link";
        $details_cap = "{all_type_models}";
        if ($group_id > 0) {
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $link .= "/$group_link/auto";
            $details_cap = $group_name . " {on_cap}";
        }

        $list = "";
        $r = $db->query("SELECT mf.MFA_BRAND_LINK, mf.MFA_BRAND, md.Model_Link 
        FROM `T_manufacturers` mf
            LEFT JOIN `T_models` md ON (md.MOD_MFA_ID = mf.MFA_ID)
        WHERE mf.`MFA_ID` = $mfa_id_sel AND md.`Model` = '$model' 
        GROUP BY md.`Model`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mod_link = $db->result($r, $i - 1, "Model_Link");

            $list .= "
            <div>
                <span>$details_cap $mfa_brand $model</span>
            </div>";

            $list .= "
            <div class=\"seo-auto-list seo_details\">";
            $r2 = $db->query("SELECT `TEX_TEXT_link`, `TEX_TEXT`, `Car_pict`, `MOD_PCON_START`, `MOD_PCON_END` 
            FROM `T_models` 
            WHERE `MOD_MFA_ID` = $mfa_id_sel AND `Model` = '$model' 
            ORDER BY `MOD_PCON_START`;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $mod_id_link = $db->result($r2, $i2 - 1, "TEX_TEXT_link");
                $text = $db->result($r2, $i2 - 1, "TEX_TEXT");
                $image = $db->result($r2, $i2 - 1, "Car_pict");
                $d_start = $db->result($r2, $i2 - 1, "MOD_PCON_START");
                $d_end = $db->result($r2, $i2 - 1, "MOD_PCON_END");
                $d_start = substr($d_start, 0, 4);
                $d_end = substr($d_end, 0, 4);
                if ($d_end == 0) {
                    $d_end = "{cur_time}";
                }
                $list .= "
                <a class=\"seo-li\" href=\"" . $this->getSiteLink() . "$link/$mfa_link/$mod_link/$mod_id_link/\">
                    <div class=\"row \">
                        <div class=\"col-4\">
                            <img src=\"https://toko.ua/uploads/images/models/$image\" alt=\"$text\" title=\"$text\">
                        </div>
                        <div class=\"col-8\">
                            <span>$mfa_brand $text ($d_start - $d_end)</span>
                        </div>
                    </div>
                </a>";
            }
            $list .= "
            </div>";
        }
        $list .= $this->getGroupCarMfaList($group_id, $mfa_id_sel);

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        $form = str_replace("{seo_auto_title}", "", $form);
        $form = str_replace("{seo_auto_list}", $list, $form);

        return $form;
    }

    /*
     * get MOD ID list
     * */
    public function getGroupCarModList($group_id, $mfa_id_sel = 0)
    {
        $group_id = $this->getUrlNumber($group_id);
        $mfa_id_sel = $this->getUrlNumber($mfa_id_sel);
        $db = DbSingleton::getTokoDb();
        $link = "$this->catalog_link";

        if ($group_id > 0) {
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $link .= "/$group_link/auto";
            $details_cap = $group_name . " {on_cap}";
        } else {
            $details_cap = "{details_on_cap}";
        }

        $list = "";
        $where = ($mfa_id_sel > 0) ? "AND `MFA_ID` = $mfa_id_sel" : "";
        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 $where ORDER BY `MFA_BRAND`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = $db->result($r, $i - 1, "MFA_ID") + 0;
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");

            if ($mfa_id_sel == 0) {
                $list .= "
                <div>
                    <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/\">$details_cap $mfa_brand</a>
                </div>";
            } else {
                $list .= "
                <div>
                    $details_cap $mfa_brand
                </div>";
            }

            $list .= "
            <div class=\"seo-auto-list\">";
            $r2 = $db->query("SELECT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id GROUP BY `Model`;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $mod = $db->result($r2, $i2 - 1, "Model");
                $mod_link = $db->result($r2, $i2 - 1, "Model_Link");
                $list .= "
                <div class=\"seo-auto-list__item\">
                    <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/$mod_link/\">
                        <span>$mfa_brand $mod</span>
                    </a>
                </div>";
            }
            $list .= "
            </div>";
        }

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        $form = str_replace("{seo_auto_title}", "", $form);
        $form = str_replace("{seo_auto_list}", $list, $form);

        return $form;
    }

    /*
     * get MFA ID list
     * */
    public function getGroupCarMfaList($group_id, $mfa_id_sel = 0)
    {
        $group_id = $this->getUrlNumber($group_id);
        $mfa_id_sel = $this->getUrlNumber($mfa_id_sel);
        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();
        $details_cap = "{details_on_cap}";
        $title = "";
        $link = "$this->catalog_link";
        $where = ($mfa_id_sel > 0) ? " AND `MFA_ID` = $mfa_id_sel" : "";
        if ($group_id > 0) {
            $group_name = $this->getGroupRowName($group_id);
            $group_link = $this->getGroupRowLink($group_id);
            $details_cap = $group_name;
            $link .= "/$group_link/auto";
            if ($mfa_id_sel > 0) {
                $mfa_brand = $automan->getMfaBrand($mfa_id_sel);
                $title = "$details_cap {on_cap} {other_models} $mfa_brand";
            } else {
                $title = $details_cap;
            }
            $details_cap .= " {on_cap}";
        }

        $mas = [];
        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 $where ORDER BY `MFA_BRAND` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = $db->result($r, $i - 1, "MFA_ID") + 0;
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mas[$mfa_brand] = compact("mfa_id", "mfa_link");
        }

        $list = "";
        foreach ($mas as $mfa_brand => $values) {
            $mfa_id = $values["mfa_id"];
            $mfa_link = $values["mfa_link"];
            if ($mfa_id_sel == 0) {
                $list .= "
                <div>
                    <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/\">$details_cap $mfa_brand</a>
                </div>";
            }
            $list .= "
            <div class=\"seo-auto-list\">";
            $r = $db->query("SELECT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id GROUP BY `Model`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model = $db->result($r, $i - 1, "Model");
                $model_link = $db->result($r, $i - 1, "Model_Link");
                $list .= "
                <div class=\"seo-auto-list__item\">
                    <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/$model_link/\">$mfa_brand $model</a>
                </div>";
            }
            $list .= "
            </div>";
        }

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        $form = str_replace("{seo_auto_title}", $title, $form);
        $form = str_replace("{seo_auto_list}", $list, $form);

        return $form;
    }

    /*
     * catalog h1
     * */
    public function getCatalogH1($group_id, $params = [], $mfa_id = 0, $model = "", $model_id = 0)
    {
        $automan = new AutoClass();
        $car_text = "";
        $group_name = ($group_id > 0) ? $this->getGroupRowName($group_id) : "";
        $group_text = $group_name;

        if ($mfa_id > 0) {
            $mfa_name = $automan->getMfaBrand($mfa_id);
            $car_text = "{on_cap} $mfa_name";
            if ($model != "") {
                $car_text .= " $model";
                if ($model_id > 0) {
                    $model_id_name = $automan->getModIdName($model_id);
                    $car_text .= " $model_id_name";
                }
            }
        }

        if (!empty($params)) {
            // brand or not
            // >2 param
            if (count($params) > 1) {
                $group_text = $group_name;
                $count_params = 0;
                ksort($params);
                foreach ($params as $param_id => $values) {
                    $param_name = $this->getGroupParamName($param_id);
                    if ($param_id == 0) {
                        foreach ($values as $brand_id) {
                            $brand_name = $this->getBrandName($brand_id);
                            $group_text .= " $brand_name";
                        }
                    }
                    if ($param_id > 0) {
                        $count_params++;
                        if ($count_params == 1) {
                            $group_text .= ":";
                        }
                        $group_text .= " $param_name - ";
                        foreach ($values as $value_id) {
                            $value_name = $this->getGroupValueName($value_id, $param_id);
                            $group_text .= "$value_name, ";
                        }
                        $group_text = rtrim($group_text, ", ");
                        $group_text .= "; ";
                    }
                }
                $group_text = rtrim($group_text, "; ");
            }
            // with brand
            if (array_key_exists(0, $params)) {
                // only 1 brand
                if (count($params) == 1) {
                    $group_text = $group_name;
                    if (count($params[0]) == 1) {
                        foreach ($params[0] as $value_id) {
                            $brand_name = $this->getGroupValueName($value_id);
                            $group_text .= " $brand_name";
                        }
                    }
                }
                // 1 brand + 1 param
                if (count($params) == 2) {
                    $group_text = $group_name;
                    krsort($params);
                    $endpoint = 0;
                    $count_params = 0;
                    foreach ($params as $param_id => $values) {
                        if ($param_id > 0) {
                            $param_name = $this->getGroupParamName($param_id);
                            $count_params++;
                            if ($count_params == 1) {
                                $group_text .= ":";
                            }
                            $group_text .= " $param_name - ";
                            foreach ($values as $value_id) {
                                $value_name = $this->getGroupValueName($value_id, $param_id);
                                $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                                if (count($values) == 1) {
                                    if ($value_h1_name != "") {
                                        $group_text = $value_h1_name;
                                    } else {
                                        $group_text .= " $value_name";
                                    }
                                } else {
                                    $group_text = " $group_text";
                                    $endpoint++;
                                }
                            }
                        }
                        if ($param_id == 0 && !$endpoint) {
                            foreach ($values as $brand_id) {
                                $brand_name = $this->getBrandName($brand_id);
                                $group_text .= " $brand_name";
                            }
                        }
                    }
                }
            }
            // without brand
            // only 1 param
            if (count($params) == 1 && !array_key_exists(0, $params)) {
                $group_text = $group_name;
                $count_params = 0;
                foreach ($params as $param_id => $values) {
                    $param_name = $this->getGroupParamName($param_id);
                    $count_params++;
                    if ($count_params == 1) {
                        $group_text .= ":";
                    }
                    $group_text .= " $param_name - ";
                    foreach ($values as $value_id) {
                        $value_name = $this->getGroupValueName($value_id, $param_id);
                        $value_h1_name = $this->getGroupValueH1($value_id, $param_id);
                        if (count($values) == 1) {
                            if ($value_h1_name != "") {
                                $group_text = $value_h1_name;
                            } else {
                                $group_text .= " $value_name";
                            }
                        } else {
                            $group_text = " $group_text";
                        }
                    }
                }
            }
        }
        $title = "$group_text $car_text";
        $title = rtrim($title, " ");
        return $title;
    }

    /*
     * catalog title cache
     * */
    public function getCatalogTitleCache($str)
    {
        $str = $this->getUrlString($str);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $title = "";
        $r = $db->query("SELECT `TITLE_$postfix` FROM `T2_TITLES` WHERE `ROUTER` = '$this->catalog_link' AND `LINK` = '$str' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $title = $db->result($r, 0, "TITLE_$postfix");
        }
        return $title;
    }

    /*
     * catalog title
     * */
    public function getCatalogTitle($group_id, $params = [], $h1_text = "", $mfa_id = 0, $model = "")
    {
        $automan = new AutoClass();
        $text = "$h1_text | ";
        $brand_name = "";

        // 1
        // group_name/
        if ($mfa_id == 0 && $model == "" && empty($params)) {
            $text .= $this->replaceLang("{seo_new_tilte_1}");
        }

        // 1.1
        $filters_count = count($params);
        if ($mfa_id == 0 && $model == "" && $filters_count > 2) {
            $text = "$h1_text | ";
            $text .= $this->replaceLang("{seo_new_tilte_1}");
        }

        // 2
        // group_name/auto/mfa/
        if ($mfa_id > 0 && $model == "" && empty($params)) {
            $text .= $this->replaceLang("{seo_new_tilte_2}");
        }

        // 3
        // group_name/auto/mfa/model/
        if ($mfa_id > 0 && $model != "" && empty($params)) {
            $text .= $this->replaceLang("{seo_new_tilte_3}");
            $mfa_name = $automan->getMfaBrand($mfa_id);
            $text = str_replace("{mfnm}", $mfa_name, $text);
        }

        // 4
        // group_name/filters/mfa/model/
        if (!empty($params)) {
            //if brand
            if (array_key_exists(0, $params)) {
                // 1 brand
                if (count($params) == 1) {
                    if ($mfa_id == 0) {
                        // 1 brand + auto
                        $text .= $this->replaceLang("{seo_new_tilte_4}");
                    } else {
                        // 1 brand + mfa model
                        $text .= $this->replaceLang("{seo_new_tilte_5}");
                        $mfa_name = $automan->getMfaBrand($mfa_id);
                        $text = str_replace("{mfnm}", $mfa_name, $text);
                    }
                    $text = str_replace("{brnm}", $brand_name, $text);
                }
                // 1 brand + 1 param
                if (count($params) == 2) {
                    if ($mfa_id == 0) {
                        // 1 brand + 1 param + auto
                        $text .= $this->replaceLang("{seo_new_tilte_8}");
                    } else {
                        // 1 brand + 1 param + mfa model
                        $text .= $this->replaceLang("{seo_new_tilte_7}");
                        $mfa_name = $automan->getMfaBrand($mfa_id);
                        $text = str_replace("{mfnm}", $mfa_name, $text);
                    }
                    $text = str_replace("{brnm}", $brand_name, $text);
                    foreach ($params as $param_id => $values) {
                        foreach ($values as $value_id) {
                            if (count($values) == 1) {
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
                if ($mfa_id == 0) {
                    // 1 param + auto
                    $text .= $this->replaceLang("{seo_new_tilte_1}");
                }
                // 1 param + mfa model
                elseif ($model == "") {
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
                    $mfa_name = $automan->getMfaBrand($mfa_id);
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
            } else {
                $text = "$h1_text | ";
                $text .= $this->replaceLang("{seo_new_tilte_1}");
            }
        }

        $text = str_replace("{grnm}", $this->getGroupRowName($group_id), $text);

        return $this->replaceLang($text);
    }

    public function getPartsCatalogueStates($group_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $list = "";
        if ($group_id > 0) {
            $r = $db->query("SELECT t2r.`ID`, t2r.`TITLE_$postfix` 
            FROM `T2_GROUP_REVIEW` t2gr 
                LEFT JOIN `T2_REVIEWS` t2r ON (t2r.`ID` = t2gr.`REVIEW_ID`)
            WHERE t2gr.`GROUP_ID` = $group_id AND t2r.`STATUS` = 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $list = "
                <div class=\"reviews-list-title\">{states_cap}</div>
                    <div class=\"reviews-list\">";
            }
            for ($i = 1; $i <= $n; $i++) {
                $review_id = $db->result($r, $i - 1, "ID");
                $review_title = $db->result($r, $i - 1, "TITLE_$postfix");
                $transcript = $this->formatUrlText($review_title);
                $link = "/reviews/state/$review_id/$transcript/";
                $list .= "
                <div class=\"reviews-list__item\">
                    <a href=\"$link\">$review_title</a>
                </div>";
            }
            if ($n > 0) {
                $list .= "
                </div>";
            }
        }
        return $list;
    }

    public function getGroupHeadExistId($head_link)
    {
        $db = DbSingleton::getTokoDb();
        $head_id = 0;
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HEAD_EXIST` WHERE `TEX_LINK` = '$head_link' LIMIT 1;");
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
        $r = $db->query("SELECT `CAT_ID` FROM `T2_TREE_CAT_EXIST` WHERE `TEX_LINK` = '$cat_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $cat_id = $db->result($r, 0, "CAT_ID");
        }
        return $cat_id;
    }

    public function getHeaderBreadCrumb($head_id, $cat_id = 0)
    {
        $arr = [];

        $arr[] = ["name" => "{seo_site_toko}", "link" => $this->getSiteLink()];
        $arr[] = ["name" => "{site_catalog}", "link" => $this->getSiteLink() . "$this->catalog_link/"];

        if ($head_id > 0) {
            $head_name = $this->getHeadExistName($head_id);
            $head_link = $this->getHeadExistLink($head_id);
            $arr[] = ["name" => "$head_name", "link" => $this->getSiteLink() . "$this->catalog_link/$head_link/"];

            if ($cat_id > 0) {
                $cat_name = $this->getCatRowName($cat_id);
                if ($head_id == 1) {
                    $cat_name .= " - " . $this->getHeadRowName($head_id);
                }
                $cat_link = $this->getCatRowLink($cat_id);
                $arr[] = ["name" => "$cat_name", "link" => $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/"];
            }
        }
        return $arr;
    }

    public function showGroupHeadForm($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `TEX_$postfix` FROM `T2_TREE_HEAD_EXIST` WHERE `STATUS` = 1 AND `HEAD_ID` = $head_id LIMIT 1;");
        $h1_text = $db->result($r, 0, "TEX_$postfix");

        $form = $this->getHtmlForm("catalog_exist/head_form");
        $form = str_replace("{head_h1}", $h1_text, $form);
        $form = str_replace("{head_list}", $this->getCatalogColListCat($head_id), $form);
        $form = $this->replaceLang($form);

        $title = $this->replaceLang("{site_catalog_header}");
        $title = str_replace("{h1_text}", $h1_text, $title);
        $description = $this->replaceLang("{site_catalog_header_description}");
        $description = str_replace("{h1_text}", $h1_text, $description);

        $breadcrumbsData = $this->getBreadCrumbForm($this->getHeaderBreadCrumb($head_id));
        $breadcrumb = $breadcrumbsData["form"];
        $script = $breadcrumbsData["script"];

        return compact("form", "title", "description", "breadcrumb", "script");
    }

    public function showGroupCatForm($head_id, $cat_id)
    {
        $h1_text = $this->getCatRowName($cat_id);
        if ($head_id == 1) {
            $h1_text .= " - " . $this->getHeadRowName($head_id);
        }
        $form = $this->getHtmlForm("catalog_exist/cat_form");
        $form = str_replace("{cat_h1}", $h1_text, $form);
        $form = str_replace("{cat_list}", $this->getCatalogColListGroup($head_id, $cat_id), $form);
        $form = $this->replaceLang($form);

        $title = $this->replaceLang("{site_catalog_header}");
        $title = str_replace("{h1_text}", $h1_text, $title);
        $description = $this->replaceLang("{site_catalog_header_description}");
        $description = str_replace("{h1_text}", $h1_text, $description);

        $breadcrumbsData = $this->getBreadCrumbForm($this->getHeaderBreadCrumb($head_id, $cat_id));
        $breadcrumb = $breadcrumbsData["form"];
        $script = $breadcrumbsData["script"];

        return compact("form", "title", "description", "breadcrumb", "script");
    }

    /*
     * get filters and brands count
     * */
    public function getCatalogParamsCount($params)
    {
        $count_brands = $count_params = $count_values = 0;
        foreach ($params as $param_id => $values) {
            if ($param_id == 0) {
                $count_brands += count($values);
            } else {
                $count_params += count($values);
            }
            if (count($values) > 1) {
                $count_values++;
            }
        }
        if ($count_brands > 1) {
            $count_brands = 1;
        }
        if ($count_params > 1) {
            $count_params = 1;
        }
        return array($count_brands, $count_params, $count_values);
    }

    /*
     * get catalog meta tag
     * */
    public function getCatalogMetaTags($group_id, $h1_text)
    {
        $group_link = $this->getGroupRowLink($group_id);
        $url_text = $this->getSiteLink() . $this->catalog_link . "/" . $group_link . "/";
        $img_text = "https://toko.ua/images/tree-group/" . $this->getGroupRowImage($group_id);
        $form = $this->getHtmlForm("article/social");
        $form = str_replace("{h1_meta_tag}", $h1_text, $form);
        $form = str_replace("{url_meta_tag}", $url_text, $form);
        $form = str_replace("{main_image_cap}", $img_text, $form);
        return $form;
    }

    public function getSiteConsole($text)
    {
        $form = "";
        $user_id = $this->getUser();
        if ($user_id == 15) {
            $form = $this->getHtmlForm("console");
            $form = str_replace("{console_range}", $text, $form);
        }
        return $form;
    }

    /*
     * check exist of group table
     * */
//    public function checkTable($group_id)
//    {
//        $dbc = DbSingleton::getTokoCacheDb();
//        $table = "EX_TABLE_TREE_$group_id";
//        $r = $dbc->query("SHOW TABLES LIKE '$table';");
//        $n = $dbc->num_rows($r);
//        if ($n > 0) {
//            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table` WHERE 1;");
//            $n = $dbc->result($r, 0, "col_arts");
//        }
//        return $n;
//    }

//    public function checkGroupParamExist($group_id, $param_id, $value_id, $brand_id)
//    {
//        $dbc = DbSingleton::getTokoCacheDb();
//        $where_brand = "";
//        if ($brand_id > 0) {
//            $where_brand = "AND `brand_id` = $brand_id";
//        }
//        $r = $dbc->query("SELECT COUNT(`art_id`) as count_arts FROM `EX_TABLE_TREE_PARAMS_$group_id`
//        WHERE (`param_$param_id` = '$value_id' OR `param_$param_id` LIKE '%,$value_id%' OR `param_$param_id` LIKE '%$value_id,%') $where_brand;");
//        $n = $dbc->result($r, 0, "count_arts") + 0;
//        return ($n > 0);
//    }

    public function getSeoLinks()
    {
//        $dbc = DbSingleton::getTokoCacheDb();
//        $count = 0;

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
//            $value_h1 = $this->getGroupValueH1($value_id, $param_id);
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

//        $link = "https://toko.ua/catalog/";

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
        return 0;
    }

    public function getCatalogFilterSeo()
    {
        return "
            <!--ss_selected_filters_info|FilterName|FilterValue-->
            <!--seoshield_formulas--fil-traciya-->
        ";
    }

}
