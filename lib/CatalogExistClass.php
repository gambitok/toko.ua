<?php

class CatalogExistClass extends CatalogueClass
{

    public $products_on_page    = 12;
    public $default_status_auto = 0;
    public $pagination_count    = 5;
    public $filters_count       = 7;
    public $max_count_arts      = 1000;

    /*
     * HEAD EXIST
     * */
    public function getHeadExistID($group_id, $status = 0): int
    {
        $db = DbSingleton::getTokoDb();
        $head_id    = 0;
        $where      = ($status > 0) ? " AND `POPULAR` = 1" : "";
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HCG_EXIST` WHERE `GROUP_ID` = $group_id $where LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $head_id = (int)$db->result($r, 0, "HEAD_ID");
        }
        return $head_id;
    }
    public function getHeadExistName($head_id)
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $head_name = 0;
        $r = $db->query("SELECT `TEX_" . $postfix . "` FROM `T2_TREE_HEAD_EXIST` WHERE `HEAD_ID` = $head_id LIMIT 1;");
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
    public function getGroupExistId($group_link): int
    {
        $db = DbSingleton::getTokoDb();
        $group_id = 0;
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE `TEX_LINK` = '$group_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $group_id = (int)$db->result($r, 0, "GROUP_ID");
        }
        return $group_id;
    }
    public function getGroupExistStatusAuto($group_id): int
    {
        $db = DbSingleton::getTokoDb();
        $status_auto = $this->default_status_auto;
        $r = $db->query("SELECT `STATUS_AUTO` FROM `T2_TREE_GROUP_EXIST` WHERE `GROUP_ID` = $group_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $status_auto = (int)$db->result($r, 0, "STATUS_AUTO");
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
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID` = $group_id AND `PARAM_LINK` = '$param_link' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $param_id = $db->result($r, 0, "PARAM_ID");
        }
        if ($param_link === "brandy") {
            $param_id = 0;
        }
        return $param_id;
    }
    public function getGroupParamName($param_id)
    {
        $language = new LangClass();
        $lang_id = $language->getOldLanguage($this->getLanguage());

        $param_id = $this->getUrlNumber($param_id);
        $db = DbSingleton::getTokoDb();
        $param_name = "";

        $r = $db->query("SELECT `PARAM_NAME` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` = $param_id AND `LANG_ID` = $lang_id LIMIT 1;");
        $n = (int)$db->num_rows($r);

        if ($n > 0) {
            $param_name = $db->result($r, 0, "PARAM_NAME");
        }
        if ((int)$param_id === 0) {
            $param_name = "{brands_cap}";
        }
        return $param_name;
    }
    public function getGroupParamLink($param_id)
    {
        $param_id = $this->getUrlNumber($param_id);
        $db = DbSingleton::getTokoDb();
        $param_name = "";
        $r = $db->query("SELECT `PARAM_LINK` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` = $param_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $param_name = $db->result($r, 0, "PARAM_LINK");
        }
        if ((int)$param_id === 0) {
            $param_name = "brandy";
        }
        return $param_name;
    }

    /*
     * VALUE EXIST
     * */
    public function getGroupValueID($group_id, $param_id, $value_link): int
    {
        $group_id   = $this->getUrlNumber($group_id);
        $param_id   = $this->getUrlNumber($param_id);
        $value_link = $this->getUrlString($value_link);

        $db = DbSingleton::getTokoDb();

        $value_id = 0;
        $r = $db->query("SELECT `VALUE_ID` FROM `T2_TREE_VALUE_EXIST` WHERE `GROUP_ID` = $group_id AND `PARAM_ID` = $param_id AND `VALUE_LINK` = '$value_link' AND `LANG_ID` = 16 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_id = (int)$db->result($r, 0, "VALUE_ID");
        }
        if ((int)$param_id === 0) {
            $r = $db->query("SELECT `BRAND_ID` FROM `T2_BRANDS` WHERE `BRAND_LINK` = '$value_link' LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $value_id = (int)$db->result($r, 0, "BRAND_ID");
            }
        }
        return $value_id;
    }
    public function getGroupValueName($value_id, $param_id = 0)
    {
        $value_id = $this->getUrlNumber($value_id);
        $param_id = $this->getUrlNumber($param_id);
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getOldLanguage($this->getLanguage());
        $value_name     = "";
        $where_param    = ($param_id > 0) ? "`PARAM_ID` = $param_id" : "1";
        $r = $db->query("SELECT `VALUE_NAME` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id AND $where_param AND `LANG_ID` = $lang_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_name = $db->result($r, 0, "VALUE_NAME");
        }
        if ((int)$param_id === 0) {
            $value_name = $this->getBrandName($value_id);
        }
        return $value_name;
    }
    public function getGroupValueLink($value_id, $param_id = 0)
    {
        $value_id = $this->getUrlNumber($value_id);
        $param_id = $this->getUrlNumber($param_id);
        $db = DbSingleton::getTokoDb();

        $value_name = "";
        $r = $db->query("SELECT `VALUE_LINK` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id AND `LANG_ID` = 16 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_name = $db->result($r, 0, "VALUE_LINK");
        }
        if ((int)$param_id === 0) {
            $value_name = $this->getBrandLink($value_id);
        }
        return $value_name;
    }
    public function getGroupValueH1($value_id, $param_id = 0)
    {
        $value_id = $this->getUrlNumber($value_id);
        $param_id = $this->getUrlNumber($param_id);
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $value_h1 = "";
        $r = $db->query("SELECT `VALUE_H1_" . $postfix . "` FROM `T2_TREE_VALUE_EXIST` WHERE `VALUE_ID` = $value_id AND `LANG_ID` = 16 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $value_h1 = $db->result($r, 0, "VALUE_H1_$postfix");
        }
        if ((int)$param_id === 0) {
            $value_h1 = "";
        }
        return $value_h1;
    }

    /*
     * get products limit
     * */
    public function getSearchLimit($page): string
    {
        $count  = $this->products_on_page;
        $off    = $count * $page - $count;
        return ($off >= 0) ? " LIMIT $count OFFSET $off" : "";
    }

    /*
     * check exist of group params table
     * */
    public function checkTable($group_id): int
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table`;");
            $n = (int)$dbc->result($r, 0, "col_arts");
        }

        return $n;
    }

    /*
     * check exist of group params table
     * */
    public function checkTableParams($group_id): int
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table_params';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table_params`;");
            $n = (int)$dbc->result($r, 0, "col_arts");
        }

        return $n;
    }

    /*
     * check exist of group mfa table
     * */
    public function checkTableMfa($group_id): int
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_MFA_$group_id";
        $r = $dbc->query("SHOW TABLES LIKE '$table';");
        $n = $dbc->num_rows($r);
        if ($n > 0) {
            $r = $dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `$table`;");
            $n = (int)$dbc->result($r, 0, "col_arts");
        }

        return $n;
    }

    /*
     * init products params cache
     * */
    public function initPartsParamsTable($group_id): string
    {
        $start = microtime(true);
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $dbc->query("DROP TABLE IF EXISTS `$table_params`;");

        $params = [];
        $params_str = "";
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID` = $group_id AND `STATUS` = 1 AND `LANG_ID` = 16;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id   = (int)$db->result($r, $i - 1, "PARAM_ID");
            $params[]   = $param_id;
            $params_str .= "`param_$param_id` VARCHAR(100),";
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
            $dbc->query("ALTER TABLE `$table_params` CHANGE `param_$param_id` `param_$param_id` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
        }

        $products = [];
        $r = $db->query("SELECT t2a.`ART_ID`, t2a.`PARAM_ID`, t2a.`VALUE_ID`
        FROM `T2_TREE_ARTS_PARAMS_VALUE_EXIST` t2a
        WHERE t2a.`ART_ID` IN (
            SELECT ex.`ART_ID` FROM toko_dba_cache.`$table` as ex);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id     = $db->result($r, $i - 1, "ART_ID");
            $param_id   = (int)$db->result($r, $i - 1, "PARAM_ID");
            $value_id   = (int)$db->result($r, $i - 1, "VALUE_ID");

            $products[$art_id][0] = 0;
            if ($param_id > 0) {
                $products[$art_id][$param_id][] = $value_id;
            }
        }

        foreach ($products as $art_id => $params) {
            $r = $dbc->query("SELECT `brand_id` FROM `$table` WHERE `art_id` = $art_id LIMIT 1;");
            $brand_id = (int)$dbc->result($r, 0, "brand_id");
            $products[$art_id][0] = $brand_id;
        }

        $count_add = 0;

        foreach ($products as $art_id => $params) {
            $params_values  = "";
            $params_column  = "";
            $set_column     = "";
            $brand_id       = $params[0];

            foreach ($params as $param_id => $values) {
                $params_arr = [];

                foreach ($values as $value_id) {
                    $params_arr[] = $value_id;
                }

                if ($param_id > 0) {
                    $params_values  .= "'" . implode(",", $params_arr) . "',";
                    $params_column  .= "`param_$param_id`,";
                    $set_column     .= "`param_$param_id` = '" . implode(",", $params_arr) . "',";
                }
            }

            $params_values  = rtrim($params_values, ",");
            $params_column  = rtrim($params_column, ",");
            $set_column     = rtrim($set_column, ",");

            $params_values  = ($params_values !== "") ? ", " . $params_values : $params_values;
            $params_column  = ($params_column !== "") ? ", " . $params_column : $params_column;
            $set_column     = ($set_column !== "") ? ", " . $set_column : $set_column;

            $r = $dbc->query("SELECT `id` FROM `$table_params` WHERE `art_id` = $art_id LIMIT 1;");
            $n = $dbc->num_rows($r);
            if ($n === 0) {
                $dbc->query("INSERT INTO `$table_params` (`art_id`, `brand_id`, `status` $params_column) VALUES ('$art_id', '$brand_id', 1 $params_values);");
            } else {
                $dbc->query("UPDATE `$table_params` SET `status` = 1 $set_column WHERE `art_id` = $art_id LIMIT 1;");
            }
            $count_add++;
        }

        $dbc->query("DELETE FROM `$table_params` WHERE `status` = 0;");

        $dbc->query("ALTER TABLE `$table_params` ADD INDEX `art_id` (`art_id`);");

        $time = microtime(true) - $start;
        print ("\n RUN TIME $group_id: $time (params) \n");

        return "\n ADDED: $count_add \n";
    }

    public function getArticlePriceStorage($art_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exRate = new ExRateClass();
        $cur = $this->getCurrentExRate();

        $r = $db->query("SELECT t2asc.STORAGE_ID as storage_id, 0 as suppl_id
        FROM `T2_ARTICLES` t2a
            LEFT JOIN `T2_ARTICLES_STRORAGE` t2asc ON (t2asc.ART_ID = t2a.ART_ID)
        WHERE t2a.ART_ID IN ($art_id) AND (t2asc.AMOUNT != NULL OR t2asc.AMOUNT != 0)
        GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
        UNION ALL
        SELECT t2si.client_storage_id as storage_id, t2si.suppl_id as suppl_id
        FROM `T2_ARTICLES` t2a
            LEFT JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id = t2a.ART_ID AND t2si.status = 1)
        WHERE t2a.ART_ID IN ($art_id)  AND (t2si.stock_suppl != NULL OR t2si.stock_suppl != 0)
        GROUP BY t2a.ART_ID, t2si.client_storage_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $suppl_id   = (int)$db->result($r, $i - 1, "suppl_id");
            $storage_id = (int)$db->result($r, $i - 1, "storage_id");

            $price = $this->getArticlePrice($art_id);
            if (!empty($suppl_id)) {
                $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
            }
            $price = $exRate->getExRatePrice($price, $cur);
            if ($cur === 1) {
                $price = $client->getClientPriceRounding($this->getClient(), $price);
            }

            if ($price > 0) {
                $arr[] = $price;
            }
        }

        sort($arr);

        return $arr[0];
    }

    public function getArticleStorage($group_id, $arts): bool
    {
        $db = DbSingleton::getTokoDb();

        if (!empty($arts)) {
            $where_arts = implode(",", $arts);
            $r = $db->query("
            SELECT DISTINCT t2si.art_id
            FROM `T2_SUPPL_IMPORT` t2si
                LEFT JOIN myparts_dba.`A_CLIENTS_STORAGE` cs ON (cs.id = t2si.client_storage_id)
            WHERE cs.visible = 1 AND t2si.art_id IN ($where_arts)
                UNION ALL
            SELECT DISTINCT t2asc.ART_ID as art_id
                FROM `T2_ARTICLES_STRORAGE` t2asc
            WHERE t2asc.AMOUNT > 0 AND t2asc.ART_ID IN ($where_arts)");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $dbc = DbSingleton::getTokoCacheDb();
                $table = "EX_TABLE_TREE_$group_id";
                $exRate = new ExRateClass();
                for ($i = 1; $i <= $n; $i++) {
                    $art_id = $db->result($r, $i - 1, "art_id");
                    $price  = $this->getArticlePriceStorage($art_id);
                    $price  = $exRate->getExRatePrice($price, 2);

                    $dbc->query("UPDATE `$table` SET `price` = '$price', `status` = 1 WHERE `art_id` = $art_id LIMIT 1;");
                }
            }
        }

        return true;
    }

    public function initPartsAvailableTables($group_id, $status = 0): string
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";

        $r = $dbc->query("SELECT DISTINCT `brand_id` FROM `$table`;");
        $n = $dbc->num_rows($r);
        $list = "n = $n\n";
        for ($i = 1; $i <= $n; $i++) {
            $brand_id   = (int)$dbc->result($r, $i - 1, "brand_id");

            $dbc->query("INSERT INTO `EX_TABLE_TREE_AVAILABLE_BRANDS` (`group_id`, `brand_id`, `status`) 
            SELECT $group_id, $brand_id, 1 FROM DUAL 
            WHERE NOT EXISTS (SELECT 1 FROM `EX_TABLE_TREE_AVAILABLE_BRANDS` 
                  WHERE `group_id` = $group_id AND `brand_id` = $brand_id AND `status` = 1)");
            $list .= "added group $group_id \n";
        }

        if ($n > 0) {

            if ($status === 0) {
                $dbc->query("INSERT INTO `EX_TABLE_TREE_AVAILABLE_GROUP` (`group_id`, `status`) VALUES ($group_id, 1);");
            }

            if ($status === 1) {
                $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE_GROUP` WHERE `group_id` = $group_id LIMIT 1;");
                $group_select_id = $dbc->result($r, 0, "group_id");

                if (empty($group_select_id)) {
                    $dbc->query("INSERT INTO `EX_TABLE_TREE_AVAILABLE_GROUP` (`group_id`, `status`) VALUES ($group_id, 1);");
                }
            }
        }

        return $list;
    }

    /*
     * create group table
     * */
    public function initPartsTable($group_id): string
    {
        $start = microtime(true);
        $dbc = DbSingleton::getTokoCacheDb();
        $table = "EX_TABLE_TREE_$group_id";

        $dbc->query("CREATE TABLE IF NOT EXISTS `$table` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(11) NOT NULL,
            `brand_id` INT(11), 
            `price` FLOAT,
            `status` TINYINT(2),
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");
        $dbc->query("TRUNCATE TABLE `$table`;");

        $time = microtime(true) - $start;
        print ("\n Run time $group_id before price: $time \n ");

        // 1. set art_ids
        $dbc->query("INSERT INTO `$table` (`art_id`, `brand_id`, `price`, `status`)
        SELECT `ART_ID`, `BRAND_ID`, 0, 1 FROM toko_dba.`T2_TREE_ARTS_EXIST` WHERE `GROUP_ID` = $group_id;");

        $arts = [];
        $r = $dbc->query("SELECT `art_id`, `brand_id` FROM `$table`;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id     = $dbc->result($r, $i - 1, "art_id");
            $arts[]     = $art_id;
        }

        // 2. set prices
        $this->getArticleStorage($group_id, $arts);

        $time = microtime(true) - $start;
        print ("\n Run time $group_id after price: $time \n ");

        $dbc->query("ALTER TABLE `$table` ADD INDEX `art_id` (`art_id`);");

        // 3. delete 0 prices
        $r = $dbc->query("SELECT COUNT(`art_id`) as count_arts FROM `$table` WHERE `price` > 0;");
        $count_arts = (int)$dbc->result($r, 0, "count_arts");

        if ($count_arts > $this->max_count_arts) {
            $dbc->query("DELETE FROM `$table` WHERE `price` = 0;");
        }

        return "\n ADDED $group_id (with prices) \n";
    }

    /*
     * init group mfa
     * */
    public function initPartsMfaTable($group_id): string
    {
        $start = microtime(true);
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";

        $arts = [];
        $r = $db->query("SELECT DISTINCT tl.`ART_ID`, tm.`MOD_MFA_ID`, tm.`Model` 
        FROM `T2_LINKS` tl
            LEFT JOIN `T_types` tt ON (tt.TYP_ID = tl.TYP_ID)
            LEFT JOIN `T_models` tm ON (tm.MOD_ID = tt.TYP_MOD_ID)
        WHERE tl.`ART_ID` IN (
          SELECT ex.`ART_ID` FROM toko_dba_cache.`$table` as ex
        ) AND tm.`MOD_MFA_ID` > 0;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $mfa_id = $db->result($r, $i - 1, "MOD_MFA_ID");
            $model  = $db->result($r, $i - 1, "Model");

            $arts[$art_id][$mfa_id][] = $model;
        }

        if ($this->checkTableMfa($group_id) > 0) {
            $dbc->query("UPDATE `$table_mfa` SET `status` = 0 WHERE 1;");
        }

        $dbc->query("TRUNCATE TABLE `$table_mfa`;");

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
        foreach ($arts as $art_id => $mfa_ids) {
            foreach ($mfa_ids as $mfa_id => $models) {
                foreach ($models as $model) {
                    $dbc->query("INSERT INTO `$table_mfa` (`art_id`, `mfa_id`, `model`, `status`) VALUES ($art_id, $mfa_id, '$model', 1);");
                    $count_add++;
                }
            }
        }

        $dbc->query("DELETE FROM `$table_mfa` WHERE `status` = 0;");

        $dbc->query("INSERT INTO `EX_TABLE_TREE_AVAILABLE_MFA` (`art_id`, `group_id`, `mfa_id`, `model`, `status`)
        SELECT `art_id`, $group_id, `mfa_id`, `model`, 1 FROM `$table_mfa` GROUP BY `mfa_id`, `model`;");

        $dbc->query("ALTER TABLE `$table_mfa` ADD INDEX `art_id` (`art_id`);");

        $time = microtime(true) - $start;
        print ("\n RUN TIME $group_id: $time (mfa) \n");

        return "\n ADDED to MFA: $count_add \n";
    }

    public function getCatalogBreadCrumb($group_id, $params, $filters_h1, $source_link, $mfa_id): array
    {
        $arr = [];

        $arr[] = [
            "name" => "{seo_site_toko}",
            "link" => $this->getSiteLink()
        ];
        $arr[] = [
            "name" => "{site_catalog}",
            "link" => $this->getSiteLink() . "$this->catalog_link/"
        ];

        if ($group_id > 0) {
            $head_id    = $this->getHeadExistID($group_id);
            $head_name  = $this->getHeadExistName($head_id);
            $head_link  = $this->getHeadExistLink($head_id);

            $arr[] = [
                "name" => $head_name,
                "link" => $this->getSiteLink() . "$this->catalog_link/$head_link/"
            ];

            $groupData = $this->getGroupRowData($group_id);
            $group_name = $groupData["name"];
            $group_link = $groupData["link"];

            $arr[] = [
                "name" => $group_name,
                "link" => $this->getSiteLink() . "$this->catalog_link/$group_link/"
            ];

            if (!empty($params)) {
                if (count($params) > 1) {
                    $arr2 = [];
                    foreach ($params as $param_id => $values) {
                        if (count($values) === 1) {
                            if ((int)$param_id === 0) {
                                $brand_link = $brand_name = "";
                                foreach ($values as $value_id) {
                                    $brand_name = $this->getBrandName($value_id);
                                    $brand_link = $this->getBrandLink($value_id);
                                }
                                $arr2[] = [
                                    "name" => "$group_name $brand_name",
                                    "link" => $this->getSiteLink() . "$this->catalog_link/$group_link/brandy=$brand_link/"
                                ];
                            }
                        } else {
                            $arr2 = [];
                            break;
                        }
                    }
                    $arr = array_merge($arr, $arr2);
                }
                $arr[] = [
                    "name" => $filters_h1,
                    "link" => $source_link
                ];
            }
            elseif ($mfa_id > 0) {
                $arr[] = [
                    "name" => $filters_h1,
                    "link" => $source_link
                ];
            }
        }

        return $arr;
    }

    public function getPaginationForm($text, $link, $class = ""): string
    {
        return "
        <li class=\"page-item $class\"><a class=\"page-link\" rel=\"noopener\" href=\"$link\">$text</a></li>";
    }

    /*
     * show pagination form
     * */
    public function getPartsPaginationForm($n, $page, $sort = 0)
    {
        $prefix = ($sort !== "") ? "?sort=$sort&" : "?";
        $count  = $this->products_on_page;
        $pages_count = (int)ceil($n / $count);
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
                    $active     = ($i === $page) ? "active" : "";
                    $link       = ($i > 1) ? $prefix . "page=$i" : ".";
                    $pagination .= $this->getPaginationForm($i, $link, $active);
                }

                $pagination .= $this->getPaginationForm("...", "#");
                $link       = ($pages_count > 1) ? $prefix . "page=$pages_count" : ".";
                $pagination .= $this->getPaginationForm($pages_count, $link);
            }

            elseif ($page > $max_count) {
                $pagination .= $this->getPaginationForm("1", "./");
                $pagination .= $this->getPaginationForm("...", "#");
                for ($i = $max_count; $i <= $pages_count; $i++) {
                    $active = ($i === $page) ? "active" : "";
                    $link = ($i > 1) ? $prefix . "page=$i" : ".";
                    $pagination .= $this->getPaginationForm($i, $link, $active);
                }
            }

            elseif ($page >= $min_count && $page <= $max_count) {
                $pagination .= $this->getPaginationForm("1", "./");
                $pagination .= $this->getPaginationForm("...", "#");

                $link       = ($pred_page > 1) ? $prefix . "page=$pred_page" : ".";
                $pagination .= $this->getPaginationForm($pred_page, $link);
                $link       = ($page > 1) ? $prefix . "page=$page" : ".";
                $pagination .= $this->getPaginationForm($page, $link, "active");
                $link       = ($next_page > 1) ? $prefix . "page=$next_page" : ".";
                $pagination .= $this->getPaginationForm($next_page, $link);

                $pagination .= $this->getPaginationForm("...", "#");
                $link       = ($pages_count > 1) ? $prefix . "page=$pages_count" : ".";
                $pagination .= $this->getPaginationForm($pages_count, $link);
            }

        } else {
            for ($i = 1; $i <= $pages_count; $i++) {
                $active     = ($i === $page) ? "active" : "";
                $link       = ($i > 1) ? $prefix . "page=$i" : ".";
                $pagination .= $this->getPaginationForm($i, $link, $active);
            }
        }

        $list = $this->getHtmlForm("catalog_exist/pagination");
        $list = str_replace(
            array("{pagination_range}", "{pred_disabled_class}", "{next_disabled_class}", "{link_pred}", "{link_next}"),
            array($pagination, ($page === 1) ? "disabled" : "", ($page === $pages_count) ? "disabled" : "", ($pred_page > 1) ? $prefix . "page=$pred_page" : ".", ($next_page > 1) ? $prefix . "page=$next_page" : "."),
            $list
        );

        if ($pages_count === 1) {
            $list = "";
        }

        return $this->replaceLang($list);
    }

    /*
     * get filter `param + value` ids
     * */
    public function getCheckedFilters($group_id, $filters): array
    {
        $params = [];
        if (!empty($filters)) {
            $params_arr = explode(";", $filters);

            foreach ($params_arr as $params_item) {
                list($param_link, $params_item_values) = explode("=", $params_item);
                $params_item_values_arr = explode(",", $params_item_values);

                foreach ($params_item_values_arr as $value_link) {
                    $param_id = $this->getGroupParamID($group_id, $param_link);
                    $value_id = $this->getGroupValueID($group_id, $param_id, $value_link);

                    if (!empty($value_id)) {
                        $params[$param_id][] = $value_id;
                    }
                }
            }
        }

        return $params;
    }

    public function checkRedirects($filters): array
    {
        $status = 0;
        $arr = [
            "neolux%D0%92%C2%AE",
            "JP%20GROUP",
            "HENGST%20FILTER",
            "continental%C2%A0rear-ctrl",
            "continental-aqua-ctrl%C2%A0set",
            "continental-aqua-ctrl%C2%A0multi"
        ];
        $arr_new = [
            "neolux%D0%92%C2%AE"                => "neolux",
            "JP%20GROUP"                        => "jp-group",
            "HENGST%20FILTER"                   => "hengst-filter",
            "continental%C2%A0rear-ctrl"        => "continental-rear-ctrl",
            "continental-aqua-ctrl%C2%A0set"    => "continental-aqua-ctrl-set",
            "continental-aqua-ctrl%C2%A0multi"  => "continental-aqua-ctrl-multi"
        ];

        if (!empty($filters)) {
            $params_arr = explode(";", $filters);
            foreach ($params_arr as $params_item) {
                $params_item_str        = explode("=", $params_item);
                $params_item_values     = $params_item_str[1];
                $params_item_values_arr = explode(",", $params_item_values);
                foreach ($params_item_values_arr as $value_link) {
                    if (in_array($value_link, $arr, true)) {
                        $status++;
                        $filters = str_replace($value_link, $arr_new[$value_link], $filters);
                    }
                }
            }
        }

        $status_error = 0;
        if (!empty($filters) && $filters !== "auto" && $status === 0 && strpos($filters, "=") === false) {
            $status_error = 1;
        }

        return array($status, $filters, $status_error);
    }

    /*
     * get params from selected group
     * */
    public function getExistedParams($group_id): array
    {
        $db = DbSingleton::getTokoDb();
        $params = [];
        $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `GROUP_ID` = $group_id AND `STATUS` = 1 AND `LANG_ID` = 16;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $param_id = (int)$db->result($r, $i - 1, "PARAM_ID");
            $params[] = $param_id;
        }

        return $params;
    }

    /*
     * get params/brands where for catalog filters
     * */
    public function getParamsWhere($where, $param_id, $values)
    {
        $param_name = ((int)$param_id === 0) ? "t.`brand_id`" : "tp.`param_$param_id`";

        if (!empty($values)) {
            $where .= " AND (";
            $count = 0;
            foreach ($values as $value_id) {
                $count++;
                $separator = ($count > 1) ? "OR" : "";
                $where .= " $separator ($param_name = '$value_id' OR $param_name LIKE '$value_id,%' OR $param_name LIKE '%,$value_id' OR $param_name LIKE '%,$value_id,%')";
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
            if ($sel_param_id !== $param_id) {
                $where = $this->getParamsWhere($where, $param_id, $values);
            }
        }
        return $where;
    }

    /*
     * get count parts from all group
     * */
    public function getPartsCountGroup($group_id, $params, $where_link_arts = "", $mfa_id = 0, $model = ""): int
    {
        $dbc = DbSingleton::getTokoCacheDb();

        $table          = "EX_TABLE_TREE_$group_id";
        $table_params   = "EX_TABLE_TREE_PARAMS_$group_id";
        $table_mfa      = "EX_TABLE_TREE_MFA_$group_id";

        $n = 0;
        $nc = $this->checkTableParams($group_id);

        if ($nc > 0) {
            if (empty($params)) {
                // with selected car (typ_id)
                if ($mfa_id === 0) {
                    $r = $dbc->query("SELECT COUNT(t.`art_id`) as count_arts FROM `$table` t WHERE 1 $where_link_arts ;");
                }
                // no selected car, selected just mfa_id / model
                elseif ($model === "") {
                    $r = $dbc->query("SELECT COUNT(ex.art_id) as count_arts FROM ( SELECT t.art_id FROM `$table_mfa` t WHERE t.`mfa_id` = $mfa_id GROUP BY t.`art_id` ) as ex;");
                } else {
                    $r = $dbc->query("SELECT COUNT(t.`art_id`) as count_arts FROM `$table_mfa` t WHERE t.`mfa_id` = $mfa_id AND t.`model` = '$model';");
                }
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
            $n = (int)$dbc->result($r, 0, "count_arts");
        }

        return $n;
    }

    /*
     * get products count
     * */
    public function getPartsCount($group_id, $query = ""): int
    {
        $dbc = DbSingleton::getTokoCacheDb();
        $n = 0;
        $nc = $this->checkTable($group_id);

        if (($nc > 0) && $query !== "") {
            $r = $dbc->query("SELECT COUNT(ex.art_id) as ex_count FROM ( $query ) as ex;");
            $n = (int)$dbc->result($r, 0, "ex_count");
        }

        return $n;
    }

    /*
     * get mfa where
     * */
    public function getMfaWhere($mfa_id = 0, $model = "", $status_auto = 0, $status_auto_type = 0): string
    {
        $where_mfa = "";

        if ($mfa_id > 0) {
            if ($status_auto === 0 || ($status_auto === 1 && $status_auto_type === 1)) {
                $where_mfa .= " AND tm.`mfa_id` = $mfa_id";
                if ($model !== "") {
                    $where_mfa .= " AND tm.`model` = '$model'";
                }
            }
        }

        return $where_mfa;
    }

    public function getArtsLinksWhere($status_auto = 0, $status_auto_type = 0, $typ_id = 0): string
    {
        $where_link_arts = "";

        if ($status_auto === 0 || ($status_auto === 1 && $status_auto_type === 1)) {

            if ($typ_id !== "") {
                $typ_arts = $this->getPartsCatalogueAuto($typ_id);
                if (!empty($typ_arts)) {
                    $where_link_arts = " AND t.`art_id` IN (" . implode(",", $typ_arts) . ") ";
                } else {
                    $where_link_arts = " AND t.`art_id` IN (0) ";
                }
            }
        }

        return $where_link_arts;
    }

    public function showPartsCatalogueError($group_id, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id, $filters_h1)
    {
        $autoObj = new AutoClass();

        $form = $this->getHtmlForm("catalog_exist/error");
        $form = str_replace("{form_car}", $this->getPartsCatalogueCars($group_id, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id), $form);
        $form = $this->replaceLang($form);
        $form = str_replace(
            array("{h1_text}", "{vin_text}", "{parts_cars}"),
            array("<b>$filters_h1</b>", "<a class=\"blue-a\" onclick=\"$('#VinFormPhone').modal('show');\">{vin_order}</a>", $this->drawLoader()),
            $form
        );

        $catalog_text = "{in_spare_parts_catalog}";
        $catalog_link = $this->getSiteLink() . $this->cars_link . "/";

        if ($mfa_id > 0) {
            $mfa_name       = $autoObj->getMfaBrand($mfa_id);
            $mfa_link       = $this->getManufactureLink($mfa_id);
            $catalog_text   .= " {on_cap} $mfa_name";
            $catalog_link   .= "$mfa_link/";

            if ($model !== "") {
                $model_link     = $this->getModelLink($model);
                $catalog_text   .= $model;
                $catalog_link   .= "$model_link/";
            }
        }

        $form = str_replace("{catalog_link}", "<a class=\"blue-a\" href=\"$catalog_link\">$catalog_text</a>", $form);

        return $this->replaceLang($form);
    }

    /*
     * show catalog form
     * */
    public function showPartsCatalogueParams($group_id, $page = 1, $filters = [], $params = [], $mfa_id = 0, $model = "", $model_id = 0, $status_auto = 0, $status_auto_type = 0, $source_link = "", $sort = 0, $count_brands = 0): array
    {
        $autoObj = new AutoClass();

        $dbc = DbSingleton::getTokoCacheDb();

        $table          = "EX_TABLE_TREE_$group_id";
        $table_mfa      = "EX_TABLE_TREE_MFA_$group_id";
        $table_params   = "EX_TABLE_TREE_PARAMS_$group_id";
        $limit          = $this->getSearchLimit($page);

        $time           = "";
        $typ_id         = $this->getCookieAuto();
        $group_text     = $this->getGroupRowName($group_id);
        $where_mfa      = $this->getMfaWhere($mfa_id, $model, $status_auto, $status_auto_type);
        $where_link_art = $this->getArtsLinksWhere($status_auto, $status_auto_type, $typ_id);

        $check_group    = $this->checkTable($group_id);
        $query_limit    = $query = $query_min = "";

        $order_status   = 0;
        $where_sort     = "ORDER BY t.price = 0, t.id";

        if ($sort === "asc") {
            $where_sort     = "ORDER BY t.price = 0, t.price";
            $order_status   = 1;
        }

        if ($sort === "desc") {
            $where_sort     = "ORDER BY t.price = 0, t.price DESC";
            $order_status   = 2;
        }

        $arts = [];

        if ($check_group) {

            if (empty($filters)) {
                $query = "SELECT DISTINCT t.art_id FROM `$table` t
                    LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
                    LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
                WHERE 1 $where_mfa $where_link_art
                $where_sort";

                $query_min = "SELECT t.art_id, t.price FROM `$table` t
                    LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id)
                    LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
                WHERE t.price > 0 $where_mfa $where_link_art
                ORDER BY t.price 
                LIMIT 1";
            } else {
                $where = $this->getFiltersWhere($params);
                $query = "SELECT DISTINCT t.art_id FROM `$table` t
                    LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id)
                    LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
                WHERE 1 $where $where_mfa $where_link_art
                $where_sort";

                $query_min = "SELECT t.art_id, t.price FROM `$table` t
                    LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id)
                    LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
                WHERE t.price > 0 $where $where_mfa $where_link_art
                ORDER BY t.price 
                LIMIT 1";
            }

            $query_limit = "$query $limit ";
        }

        $r = $dbc->query($query_min);
        $art_min_id = $dbc->result($r, 0, "art_id");
        $min_price  = $this->getArticlePriceStorage($art_min_id);
        $car_en     = $this->getMfaData($mfa_id)['mfa_brand'] . " " . $model;

        $r = $dbc->query($query_limit);
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $arts[] = $dbc->result($r, $i - 1, "art_id");
        }

        $art_id_str = implode(",", array_unique($arts));

        $list = $this->searchListCatalog($art_id_str, $mfa_id, $model, $status_auto, $order_status);

        $count = $this->getPartsCount($group_id, $query);

        $max_pages_count = (int)ceil($count / $this->products_on_page);

        $pagination_form = $this->getPartsPaginationForm($count, $page, $sort);

        list($h1_text, $filters_title, $filters_btn, $filters_count, $h1TextTranslate, $h1DescriptionTranslate, $params_title) = $this->getPartsFiltersItems($group_id, $page, $params, $mfa_id, $model, $model_id, $count_brands);

        $textTranslate = "";
        if ($mfa_id > 0) {
            $textTranslate = "
            <span style=\"font-weight: 400;\">" . $autoObj->getCarManufactureTranslate($mfa_id, $model) . "</span>";
        }

        $pager = "";
        if ($page > 1) {
            $pager = $this->replaceLang(" - {pager_cap} $page");
        }

        $breadcrumbs_script = "";
        if (empty($art_id_str)) {
            $form = $this->showPartsCatalogueError($group_id, $mfa_id, $model, $status_auto, $status_auto_type, $typ_id, $h1_text);
        } else {
            $form = $this->getHtmlForm("catalog_exist/form");
            $parts_h1           = "$h1_text $textTranslate $pager";
            $parts_count        = "{unselect_cap} $count " . $this->getGoodsCap($count);
            $parts_sort         = $this->getPartsSortForm($sort, $source_link);
            $parts_params       = $this->getPartsFiltersForm($group_id, $params, $mfa_id, $model, $model_id, $where_mfa, $where_link_art);
            $filters_status     = ($filters_count === 0) ? "none" : "";
            $breadcrumbsData    = $this->getBreadCrumbForm($this->getCatalogBreadCrumb($group_id, $params, $h1_text, $source_link, $mfa_id));
            $breadcrumbs_script = $breadcrumbsData["script"];
            $parts_params_cars  = $this->getPartsCatalogueParamsCars($group_id, $params, $status_auto, $status_auto_type, $typ_id, $mfa_id, $model, $model_id);
            $parts_seo          = $this->getPartsCatalogueSeo($group_id, $page, $params, $source_link, $h1_text, $mfa_id, $model, $model_id, $status_auto, $status_auto_type, $typ_id);
            $parts_states       = $this->getPartsCatalogueStates($group_id);

            $form = str_replace(
                array("{details_group_id}", "{parts_name}", "{parts_list}", "{parts_h1}", "{parts_count}", "{parts_filters}", "{filter_count}", "{parts_sort}", "{parts_pagination_list}", "{parts_params}", "{parts_breadcrumbs}", "{status_auto}", "{filters_count}", "{filters_style}", "{parts_cars}", "{parts_params_cars}", "{parts_seo}", "{parts_states}", "{parts_telegram}"),
                array($group_id, $group_text, $list, $parts_h1, $parts_count, $filters_btn, $filters_count, $parts_sort, $pagination_form, $parts_params, $breadcrumbsData["form"], $status_auto, $filters_count, $filters_status, $this->drawLoader(), $parts_params_cars, $parts_seo, $parts_states, $this->getTelegramForm()),
                $form
            );
        }

        $form = str_replace(
            array("{mfa_link}", "{model_link}", "{model_id_link}", "{cur_page}", "{max_page}"),
            array($this->getManufactureLink($mfa_id), $this->getModelLink($model), $this->getModelIDLink($model_id), $page, $max_pages_count),
            $form
        );

        $description = $this->replaceLang("{site_catalog_group_description}");
        $description = str_replace(
            array("{h1_text}", "{h1_text_translit}", "{h1_descr_translit}"),
            array($h1_text, $h1TextTranslate, $h1DescriptionTranslate),
            $description
        );
        $description .= $pager;

        if (!empty($filters)) {
            if ($count_brands > 0) {
                $description = $this->replaceLang("{site_catalog_brand_description}");
                $description = str_replace(
                    array("{h1_text}", "{h1_parrent}", "{h1_text_translit}", "{h1_descr_translit}"),
                    array($h1_text, $group_text, $h1TextTranslate, $h1DescriptionTranslate),
                    $description
                );
                $description .= $pager;
            }
        }

        $group_link = $this->getGroupRowLink($group_id);
        $postfix    = $this->getLangPostfix($this->getLanguage());

        $dbe = DbSingleton::getTokoEmojiDb();
        $r = $dbe->query("SELECT `TITLE_" . $postfix . "`, `DESCR_" . $postfix . "` FROM `T2_SEO_TITLE` WHERE `ROUTER` = 'catalog' AND `LINK` = '$group_link' AND `STATUS_AUTO` = 0 LIMIT 1;");
        $n = $dbe->num_rows($r);

        if ($n == 0 && $mfa_id > 0) {
            $r = $dbe->query("SELECT `TITLE_" . $postfix . "`, `DESCR_" . $postfix . "` FROM `T2_SEO_TITLE` WHERE `ROUTER` = 'catalog' AND `STATUS_AUTO` = 1 AND `LINK` = '$group_link' LIMIT 1;");
            $n = $dbe->num_rows($r);
        }

        if ($n > 0) {
            $filters_title  = $this->replaceLang($dbe->result($r, 0, "TITLE_$postfix"));
            $description    = $this->replaceLang($dbe->result($r, 0, "DESCR_$postfix"));

            $data = getSeoTitleData();
            if (!empty($data)) {
                $filters_title  = $data[0];
                $description    = $data[1];
            }

            $modelData = $this->getCatalogMfaModelInfo($mfa_id, $model);

            $modelName = (!empty($modelData['model_name'])) ? "" . $modelData['model_name'] : "";
            $modelNameTran = (!empty($modelData['model_transl'])) ? "" .  $modelData['model_transl'] : "";

            $filters_title = str_replace(
                array("{h1_text}", "{h1_text_translit}", "{h1_descr_translit}", "{MarkaMFA_Model}", "{MarkaMFA_Model_transl}", "{Params_str}"),
                array($h1_text, $h1TextTranslate, $h1DescriptionTranslate, $modelName, $modelNameTran, $params_title),
                $filters_title
            );
            $filters_title .= $pager;

            $description = str_replace(
                array("{h1_text}", "{price_text}", "{car_en}", "{h1_text_translit}", "{h1_descr_translit}", "{MarkaMFA_Model}", "{MarkaMFA_Model_transl}", "{Params_str}"),
                array($h1_text, $min_price, $car_en, $h1TextTranslate, $h1DescriptionTranslate, $modelName, $modelNameTran, $params_title),
                $description
            );
            $description .= $pager;
        }

        return array(
            "form"          => $form,
            "title"         => $filters_title,
            "h1"            => $h1_text,
            "pages_count"   => $max_pages_count,
            "description"   => $description,
            "script"        => $breadcrumbs_script,
            "time"          => $time
        );
    }

    public function getPartsSortForm($sort, $source_link)
    {
        $selected1 = $selected2 = $selected3 = "";

        if ($sort === "0") {
            $selected1 = "selected='selected'";
        }
        elseif ($sort === "asc") {
            $selected2 = "selected='selected'";
        }
        elseif ($sort === "desc") {
            $selected3 = "selected='selected'";
        }

        $list = "
        <select id='cat-products-sort' onchange=\"getPartsSortForm('$source_link');\">
            <option value='0' $selected1>-</option>
            <option value='1' $selected2>{sort_price_asc}</option>
            <option value='2' $selected3>{sort_price_desc}</option>
        </select>";

        return $this->replaceLang($list);
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
    public function getPartsFiltersItems($group_id, $page = 1, $params = [], $mfa_id = 0, $model = "", $model_id = 0, $count_brands = 0): array
    {
        $filters_btn = "";
        $count_values = 0;

        if (!empty($params)) {

            foreach ($params as $param_id => $values) {
                foreach ($values as $value_id) {
                    $count_values++;
                    $value_name = $this->getGroupValueName($value_id, $param_id);
                    $link       = $this->getPartsFilterLinks($group_id, $params, $param_id, $value_id, $mfa_id, $model, $model_id);

                    $filters_btn .= "
                    <a href=\"$link\" class=\"btn btn-sm\">$value_name &times;</a>";
                }
            }

            if ($count_values > 1) {
                $group_link = $this->getGroupRowLink($group_id);
                $car_link   = "$this->catalog_link/$group_link/";

                if ($mfa_id > 0) {
                    $mfa_link   = $this->getManufactureLink($mfa_id);
                    $car_link   .= "auto/$mfa_link/";
                }
                if ($model !== "") {
                    $model_link = $this->getModelLink($model);
                    $car_link   .= "$model_link/";
                }
                if ($model_id > 0) {
                    $model_id_link  = $this->getModelIDLink($model_id);
                    $car_link       .= "$model_id_link/";
                }

                $filters_btn = "
                <a class=\"btn btn-sm btn-white\" href=\"" . $this->getSiteLink() . "$car_link\">{filter_cap_empty}</a>" . $filters_btn;
            }
        }

        list($h1_text, $h1TextTranslate, $h1DescriptionTranslate, $params_title) = $this->getCatalogH1($group_id, $params, $mfa_id, $model, $model_id);
        $filters_title = $this->replaceLang("{site_catalog_group}");
        $filters_title = str_replace("{h1_text}", $h1_text, $filters_title);

        if (!empty($params)) {
            if ($count_brands > 0) {
                $filters_title = $this->replaceLang("{site_catalog_brand}");
                $filters_title = str_replace("{h1_text}", $h1_text, $filters_title);
            }
        }

        if ($page > 1) {
            $filters_title .= " - {pager_cap} $page";
            $filters_title = $this->replaceLang($filters_title);
        }

        return array($h1_text, $filters_title, $filters_btn, $count_values, $h1TextTranslate, $h1DescriptionTranslate, $params_title);
    }

    /*
     * get filters values (CATALOG PARAMS)
     * */
    public function getPartsFiltersArr($group_id, $params_check = [], $where_mfa = "", $where_link_arts = ""): array
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        $table = "EX_TABLE_TREE_$group_id";
        $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
        $table_params = "EX_TABLE_TREE_PARAMS_$group_id";

        $params = $checked_params_keys = $unchecked_params_keys = [];

        $exist_params = $this->getExistedParams($group_id);

        if (empty($params_check)) {
            $r = $dbc->query("SELECT tp.*, t.brand_id as brand_cur_id 
            FROM `$table` t
                LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
                LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
            WHERE 1 $where_mfa $where_link_arts AND t.price > 0
            GROUP BY t.art_id;");
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $brand_id       = (int)$dbc->result($r, $i - 1, "brand_cur_id");
                if ($brand_id > 0) {
                    $params[0][]    = $brand_id;
                }

                foreach ($exist_params as $param_id) {
                    $value_str = $dbc->result($r, $i - 1, "param_$param_id");

                    if (!empty($value_str)) {
                        foreach (explode(",", $value_str) as $item) {

                            if ((int)$item > 0) {
                                $params[$param_id][] = (int)$item;
                            }
                        }
                    }
                }
            }
        } else {
            $checked_params_keys    = array_keys($params_check);
            $existed_params_keys    = array_values($exist_params);
            $existed_params_keys[]  = 0;
            $unchecked_params_keys  = array_diff($existed_params_keys, $checked_params_keys);

            foreach ($checked_params_keys as $param_id) {
                $where      = $this->getFiltersWhereSelected($params_check, $param_id);
                $value_arr  = $this->getFiltersParamValues($group_id, $param_id, $where, $where_mfa, $where_link_arts);
                $params[$param_id] = $value_arr;
            }

            foreach ($unchecked_params_keys as $param_id) {
                $where      = $this->getFiltersWhere($params_check);
                $value_arr  = $this->getFiltersParamValues($group_id, $param_id, $where, $where_mfa, $where_link_arts);
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
                if ($key === "") {
                    $keys[$key_id] = 0;
                }
            }
            $keys = implode(",", $keys);

            $param_ids = [];
            $r = $db->query("SELECT `PARAM_ID` FROM `T2_TREE_PARAMS_EXIST` WHERE `PARAM_ID` IN ($keys) AND `LANG_ID` = 16 ORDER BY `POSITION`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $param_ids[] = (int)$db->result($r, $i - 1, "PARAM_ID");
            }

            $arr[0] = $params[0];

            foreach ($param_ids as $param_id) {
                $arr[$param_id] = $params[$param_id];
            }
        }

        return array(
            "arr"       => $arr,
            "checked"   => $checked_params_keys,
            "unchecked" => $unchecked_params_keys
        );
    }

    /*
     * show filter form (SHOW CATALOG PARAMS)
     * */
    public function getPartsFiltersForm($group_id, $params = [], $mfa_id = 0, $model = "", $model_id = 0, $where_mfa = "", $where_link_arts = "")
    {
        $paramData = $this->getPartsFiltersArr($group_id, $params, $where_mfa, $where_link_arts);
        $arr = $paramData["arr"];

        $list_params = "";
        if (!empty($arr)) {
            foreach ($arr as $param_id => $values) {

                if (!empty($values)) {
                    $param_name = $this->getGroupParamName($param_id);
                    $items      = [];

                    $list_params .= "
                    <div class=\"hidden-list\">
                        <div class=\"hidden-list-title\">$param_name</div>
                        <div class=\"hidden-list-search\">
                            <input type=\"text\" class=\"text-filter\" onkeyup=\"textParamSearch('$param_id');\" data-attr=\"$param_id\" placeholder=\"{search_by_name}\">
                        </div>
                        <div class=\"hidden-list-content\" data-attr=\"$param_id\">";

                    $count_brands = 0; $count_params = 0;
                    foreach ($values as $value_id) {
                        $value_name = $this->getGroupValueName($value_id, $param_id);
                        $checked    = (in_array($value_id, $params[$param_id], true));
                        $link       = $this->getPartsFilterLinks($group_id, $params, $param_id, $value_id, $mfa_id, $model, $model_id);
                        $count_arts = 0;

                        if ((int)$param_id === 0) {
                            if (in_array($value_id, $params[$param_id])) {
                                $count_brands++;
                            }
                        } elseif (in_array($value_id, $params[$param_id])) {
                            $count_params++;
                        }

                        $items[$value_id] = compact("value_name", "link", "checked", "count_arts", "value_id");
                    }

                    $arr_checked    = [];
                    $arr_value_name = [];
                    $arr_count_arts = [];

                    foreach ($items as $key => $row) {
                        $arr_checked[$key]      = $row["checked"];
                        $arr_value_name[$key]   = $row["value_name"];
                        $arr_count_arts[$key]   = $row["count_arts"];
                    }

                    if ((int)$param_id === 0) {
                        array_multisort($arr_checked, SORT_DESC, SORT_NUMERIC, $arr_value_name, SORT_ASC, SORT_STRING, $items);
                    } else {
                        array_multisort($arr_checked, SORT_DESC, SORT_NUMERIC, $arr_count_arts, SORT_DESC, SORT_NUMERIC, $items);
                    }

                    foreach ($items as $item) {
                        $value_name = $item["value_name"];
                        $link       = $item["link"];
                        $checked    = $item["checked"];

                        $checked_label = "<span class=\"fas fa-square unchecked\"></span>";
                        if ($checked) {
                            $checked_label = "<span class=\"fas fa-check-square checked\"></span>";
                        }

                        $index = "";
                        if ($count_brands > 0) {
                            $index = "rel=\"nofollow noindex\"";
                            if ($checked) {
                                $index = "";
                            }
                        }
                        if ($count_params > 0) {
                            $index = "rel=\"nofollow noindex\"";
                            if ($checked) {
                                $index = "";
                            }
                        }

                        if ($value_name !== "") {
                            $list_params .= "
                            <a href=\"$link\" $index class=\"hidden-list-content__item\">
                                <div class=\"hidden-list-content__item-left\" data-param-value=\"$param_id\">$checked_label <span>$value_name</span></div> 
                                <div class=\"hidden-list-content__item-right\"></div>
                            </a>";
                        }
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

        return $this->replaceLang($form);
    }

    public function getPartsFiltersForm2($group_id, $params = [], $filters_h1 = "", $sel_param_id = "")
    {
        $paramData = $this->getPartsFiltersArr($group_id, $params);
        $arr = $paramData["arr"];
        $count_params = 0;

        $list_params = "";
        if (!empty($arr)) {
            foreach ($arr as $param_id => $values) {
                // except selected param
                if ($sel_param_id === "" || ($sel_param_id !== "" && $sel_param_id !== $param_id)) {
                    if (!empty($values)) {
                        $count_params++;
                        $param_name = $this->getGroupParamName($param_id);
                        if ($count_params === 1) {
                            $list_params .= "
                            <span>{seo_catalog_filters_cap_1} $filters_h1 {seo_catalog_filters_cap_2} $param_name: ";
                        } else {
                            $list_params .= "
                            <span>$filters_h1 $param_name: ";
                        }

                        foreach ($values as $value_id) {
                            $value_name = $this->getGroupValueName($value_id, $param_id);
                            $link       = $this->getPartsFilterLinks($group_id, $params, $param_id, $value_id);
                            $checked    = (in_array($value_id, $params[$param_id], true));

                            if (!$checked) {
                                $list_params .= "
                                <a href=\"$link\">$value_name</a>, ";
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
    public function getFiltersParamValues($group_id, $param_id, $where = "", $where_mfa = "", $where_link_arts = ""): array
    {
        $dbc = DbSingleton::getTokoCacheDb();

        $table          = "EX_TABLE_TREE_$group_id";
        $table_mfa      = "EX_TABLE_TREE_MFA_$group_id";
        $table_params   = "EX_TABLE_TREE_PARAMS_$group_id";

        $value_arr = [];
        $r = $dbc->query("SELECT tp.*, t.brand_id as brand_cur_id 
        FROM `$table` t
            LEFT JOIN `$table_params` tp ON (tp.art_id = t.art_id) 
            LEFT JOIN `$table_mfa` tm ON (tm.art_id = t.art_id)
        WHERE 1 $where $where_mfa $where_link_arts AND t.price > 0
        GROUP BY t.art_id;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {

            if ((int)$param_id === 0) {
                $value_str = (int)$dbc->result($r, $i - 1, "brand_cur_id");
            } else {
                $value_str = $dbc->result($r, $i - 1, "param_$param_id");
            }

            if (!empty($value_str)) {
                foreach (explode(",", $value_str) as $item) {
                    if ((int)$item > 0 && !in_array($item, $value_arr, true)) {
                        $value_arr[] = (int)$item;
                    }
                }
            }
        }

        return $value_arr;
    }

    /*
     * get catalog link
     * */
    public function getPartsFilterLinks($group_id, $params, $param_id, $value_id, $mfa_id = 0, $model = "", $model_id = 0): string
    {
        $link = "";

        if (!empty($params)) {
            $unset = 0;
            foreach ($params as $param => $values) {
                foreach ($values as $key => $value) {
                    if ($param === $param_id && $value === $value_id) {
                        $unset++;
                        unset($params[$param_id][$key]);
                        if (empty($params[$param])) {
                            unset($params[$param]);
                        }
                    } elseif ($unset === 0 && !in_array($value_id, $params[$param_id], true)) {
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
            if ($link !== "") {
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
            if ($model !== "") {
                $model_link = $this->getModelLink($model);
                $list .= "$model_link/";
            }
            if ($model_id > 0) {
                $model_id_link = $this->getModelIDLink($model_id);
                $list .= "$model_id_link/";
            }
        }

        return $list;
    }

    /*
     * show param cars form
     * */
    public function getPartsCatalogueParamsCars($group_id, $params, $status_auto = 0, $status_auto_type = 0, $typ_id = 0, $mfa_id = 0, $model = "", $model_id = 0)
    {
        $autoObj = new AutoClass();
        $form = "";

        if ($status_auto === 1) {

            if ($typ_id !== "") {
                $car_checked = $all_checked = $car_count = $all_count = "";
                list($mfa_id_typ, $model_typ, $model_id_typ) = $autoObj->getCarInfo($typ_id);

                $mfa_name       = $autoObj->getMfaBrand($mfa_id_typ);
                $model_id_name  = $autoObj->getModIdName($model_id_typ);
                $typ_text       = "$mfa_name $model_typ $model_id_name";

                // all
                if ($status_auto_type === 0) {
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
                // checked car
                if ($status_auto_type === 1) {
                    $car_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                    $all_checked = "<i class=\"fas fa-circle unchecked\"></i>";

                    $count = $this->getPartsCountGroup($group_id, $params);
                    $all_count = "($count)";
                }
                $form = $this->getHtmlForm("catalog_exist/params_cars");
                $form = str_replace(
                    array("{typ_text}", "{on_car_checked}", "{on_car_count}", "{on_all_checked}", "{on_all_count}"),
                    array($typ_text, $car_checked, $car_count, $all_checked, $all_count),
                    $form
                );
            }

            elseif ($mfa_id > 0) {
                $car_checked = $all_checked = $car_count = $all_count = "";

                $mfa_name       = $autoObj->getMfaBrand($mfa_id);
                $model_id_name  = $autoObj->getModIdName($model_id);
                $typ_text       = "$mfa_name $model $model_id_name";

                if ($status_auto_type === 0) {
                    $car_checked = "<i class=\"fas fa-circle unchecked\"></i>";
                    $all_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                    $where_link_arts = "";
                    $typ_arts = $this->getPartsCatalogueAuto($typ_id);
                    if (!empty($typ_arts)) {
                        $where_link_arts = " AND t.art_id IN (" . implode(",", $typ_arts) . ") ";
                    }
                    $count = $this->getPartsCountGroup($group_id, $params, $where_link_arts, $mfa_id, $model);
                    $car_count = "($count)";
                }

                if ($status_auto_type === 1) {
                    $car_checked = "<i class=\"fas fa-check-circle checked\"></i>";
                    $all_checked = "<i class=\"fas fa-circle unchecked\"></i>";
                    $count = $this->getPartsCountGroup($group_id, $params);
                    $all_count = "($count)";
                }

                $form = $this->getHtmlForm("catalog_exist/params_cars");
                $form = str_replace(
                    array("{typ_text}", "{on_car_checked}", "{on_car_count}", "{on_all_checked}", "{on_all_count}"),
                    array($typ_text, $car_checked, $car_count, $all_checked, $all_count),
                    $form
                );
            }

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

        if ($status_auto === 0 || $status_auto === 1) {
            if ($typ_id !== "") {
                if ($status_auto === 0 || ($status_auto === 1 && $status_auto_type === 1)) {
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
    public function getPartsCatalogueAuto($typ_id): array
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $arts = [];
        $r = $db->query("SELECT DISTINCT `ART_ID` FROM `T2_LINKS` WHERE `TYP_ID` = $typ_id;");
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

        if (count($params) === 2) {
            $param_id_1 = array_keys($params)[0];
            $params_1[$param_id_1] = $params[$param_id_1];
            $filters_h1_1 = $this->getCatalogH1Seo($group_id, $params_1);
            $list = $this->getPartsFiltersForm2($group_id, $params_1, $filters_h1_1);

            $param_id_2 = array_keys($params)[0];
            $params_2[$param_id_1] = $params[$param_id_2];
            $filters_h1_2 = $this->getCatalogH1Seo($group_id, $params_2);
            $list .= $this->getPartsFiltersForm2($group_id, $params_2, $filters_h1_2);
        }

        if (count($params) === 1) {
            $sel_param_id = array_keys($params)[0];
            $filters_h1 = $this->getCatalogH1Seo($group_id, $params);
            $list = $this->getPartsFiltersForm2($group_id, $params, $filters_h1, $sel_param_id);
        }

        return $list;
    }

    public function getBodyInfo($mod_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BODY_ID` FROM `T_types` WHERE `TYP_MOD_ID` = $mod_id LIMIT 1;");
        $body_id = $db->result($r, 0, "BODY_ID") + 0;

        $r = $db->query("SELECT `TYPE_BODY` FROM `T_types_body_car` WHERE `BODY_ID` = $body_id AND `LANG_ID` = 16 LIMIT 1;");
        return $db->result($r, 0, "TYPE_BODY");
    }

    public function getBodyName($body_id)
    {
        $language = new LangClass();
        $lang_id = $language->getOldLanguage($this->getLanguage());
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TYPE_BODY` FROM `T_types_body_car` WHERE `BODY_ID` = $body_id AND `LANG_ID` = $lang_id LIMIT 1;");
        return $db->result($r, 0, "TYPE_BODY");
    }

    public function getCatalogSeoModsList($model_id): string
    {
        $list = "";
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `TYP_KW_FROM`, `TYP_HP_FROM`, `TYP_CCM`, `VOLUME_CM`, `FUEL_ID`, `TYP_MMT_TEXT`,
        IF (TYP_PCON_START = 0, '', TYP_PCON_START) AS TYP_PCON_START,
        IF (TYP_PCON_END = 0, '', TYP_PCON_END) AS TYP_PCON_END
        FROM `T_types` 
        WHERE `TYP_MOD_ID` = $model_id AND `ACTIVE` = 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $kw_from    = $db->result($r, $i - 1, "TYP_KW_FROM");
                $hp_from    = $db->result($r, $i - 1, "TYP_HP_FROM");
                $ccm        = $db->result($r, $i - 1, "VOLUME_CM");
                $fuel       = $this->getFuelName($db->result($r, $i - 1, "FUEL_ID"));

                if ($ccm === "") {
                    $ccm = $db->result($r, $i - 1, "TYP_CCM");
                }

                $list .= "
                <li>$fuel, $ccm cm3, $hp_from {horse_power_cap} / $kw_from {kilo_wat_cap}</li>";
            }
        }
        return $list;
    }

    public function getCatalogSeoCarsList($mfa_id, $model): string
    {
        $list = "";
        $db = DbSingleton::getTokoDb();

        if ($mfa_id > 0 && $model !== "") {
            $r = $db->query("SELECT `MOD_ID`, `TEX_TEXT`,      
            IF (MOD_PCON_START = 0, '', MOD_PCON_START) AS MOD_PCON_START,
            IF (MOD_PCON_END = 0, '', MOD_PCON_END) AS MOD_PCON_END
            FROM `T_models`
            WHERE `MOD_MFA_ID` = $mfa_id AND `Model` = '$model'
            ORDER BY `MOD_PCON_START`;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $list = "
                <ol>";
                for ($i = 1; $i <= $n; $i++) {
                    $mod_id     = $db->result($r, $i - 1, "MOD_ID");
                    $body_name  = $this->getBodyInfo($mod_id);
                    $text       = $db->result($r, $i - 1, "TEX_TEXT");
                    $d_start    = $db->result($r, $i - 1, "MOD_PCON_START");
                    $d_start    = substr($d_start, 0, 4);
                    $d_end      = $db->result($r, $i - 1, "MOD_PCON_END");
                    if ($d_end === "") {
                        $year_text = "{begin_produce_cap} $d_start";
                    } else {
                        $d_end = substr($d_end, 0, 4);
                        $year_text = "{was_issued} {with_cap} $d_start {to_cap} $d_end";
                    }

                    $list .= "
                    <li>
                        $text - $body_name, $year_text:
                        <ul>
                        " . $this->getCatalogSeoModsList($mod_id) . "
                        </ul>
                    </li>";
                }
                $list .= "
                </ol>";
            }
        }

        return $list;
    }

    public function getCatalogMfaModelInfo($mfa_id, $model = ""): array
    {
        $lang_id = $this->getLanguage();
        $mfaData = $this->getMfaData($mfa_id);
        $link = $this->getSiteLink() . $this->cars_link .  "/" . $mfaData["mfa_link"] . "/";
        $text = $mfaData["mfa_brand"];
        $text_transl = $mfaData["mfa_ru"];
        if ($lang_id === 2) {
            $text_transl = $mfaData["mfa_ua"];
        }

        if ($model !== "") {
            $model_link = $this->getModelLink($model);
            $link       = $this->getSiteLink() . $this->cars_link .  "/" . $mfaData["mfa_link"] . "/" . $model_link . "/";
            $text       .= " $model";
            $model_transl = $this->getModelTransl($model)['model_link_ru'];
            if ($lang_id === 2) {
                $model_transl = $this->getModelTransl($model)['model_link_ua'];
            }

            $text_transl .= " $model_transl";
        }

        $model_name = $text;
        $model_link = "<a href='$link'>$text</a>";
        $model_transl = $text_transl;

        return compact('model_name', 'model_link', 'model_transl');
    }

    public function getParamsLink($params): string
    {
        $link = "";
        foreach ($params as $param_id => $values) {

            if ((int)$param_id === 0) {
                $param_link = "brandy";
            } else {
                $param_link = $this->getParamLink($param_id);
            }

            $link .= "$param_link=";
            foreach ($values as $value_id) {
                $value_link = ((int)$param_id === 0) ? $this->getBrandLink($value_id) : $this->getValueLink($value_id);
                $link .= "$value_link,";
            }
            $link = rtrim($link, ",");
            $link .= ";";
        }

        return rtrim($link, ";");
    }

    public function getCatalogSeoFiltersGenerate($group_id, $params, $h1_text, $mfa_id, $model = "")
    {
        $text = "";
        $db = DbSingleton::getTokoDb();

        $groupData = $this->getGroupRowData($group_id);
        $group_name = $groupData["name"];
        $group_link = $groupData["link"];
        $main_link  = $this->getSiteLink() . $this->catalog_link . "/" . $group_link . "/";

        $head_id    = $this->getHeadExistID($group_id, 1);
        $head_name  = $this->getHeadExistName($head_id);
        $head_link  = $this->getSiteLink() . $this->catalog_link . "/" . $this->getHeadExistLink($head_id) . "/";
        $postfix    = $this->getLangPostfix($this->getLanguage());

        $n = 0;
        $r = "";

        if (empty($params)) {
            $r = $db->query("SELECT `TEXT_" . $postfix . "` FROM `T2_SEO_GENERATE` WHERE `ROUTER` = 'catalog' AND `LINK` = '$group_link' LIMIT 1;");
            $n = $db->num_rows($r);
        } else {

            if (count($params) === 2) {
                $param_ids = array_keys($params);

                if (in_array(0, $param_ids, true)) {
                    $param_ids1 = $param_ids[0];
                    $value_ids1 = $params[$param_ids1];
                    $param_ids2 = $param_ids[1];
                    $value_ids2 = $params[$param_ids2];

                    if (count($value_ids1) === 1 && count($value_ids2) === 1) {
                        $where = "'$group_link/" . $this->getParamsLink($params) . "'";
                        $r = $db->query("SELECT `TEXT_" . $postfix . "` FROM `T2_SEO_GENERATE` WHERE `ROUTER` = 'catalog' AND `LINK` = $where LIMIT 1;");
                        $n = $db->num_rows($r);

                        if ($n === 0) {
                            $r = $db->query("SELECT `TEXT_" . $postfix . "` FROM `T2_SEO_GENERATE` WHERE `ROUTER` = 'catalog' AND `LINK` = '$group_link' LIMIT 1;");
                            $n = $db->num_rows($r);
                        }
                    }
                }
            }

            if (count($params) === 1) {
                $param_ids = array_keys($params)[0];
                $value_ids = $params[$param_ids];

                if (count($value_ids) === 1) {
                    $where = "'$group_link/" . $this->getParamsLink($params) . "'";
                    $r = $db->query("SELECT `TEXT_" . $postfix . "` FROM `T2_SEO_GENERATE` WHERE `ROUTER` = 'catalog' AND `LINK` = $where LIMIT 1;");
                    $n = $db->num_rows($r);

                    if ($n === 0) {
                        $r = $db->query("SELECT `TEXT_" . $postfix . "` FROM `T2_SEO_GENERATE` WHERE `ROUTER` = 'catalog' AND `LINK` = '$group_link' LIMIT 1;");
                        $n = $db->num_rows($r);
                    }
                }
            }

        }

        if ($n > 0) {
            $text = $db->result($r, 0, "TEXT_$postfix");
            $modelData = $this->getCatalogMfaModelInfo($mfa_id, $model);
            $h1_text_small = strtolower($h1_text);
            $group_name_small = mb_strtolower($this->replaceLang($group_name), "UTF-8");

            $modData    = $this->getCatalogSeoModelsInfo($mfa_id, $model);
            $volume     = $modData["volumes"];
            $year       = $modData["years"];
            $types      = $modData["types"];

            $text = str_replace(
                array("{GET_PAGE_H1}", "{GET_PAGE_H1_small}", "{GET_PAGE_H1_LINK}", "{MarkaMFA_Model}", "{MarkaMFA_Model_transl}", "{MarkaMFA_Model_LINK}", "{Main_Category_H1}", "{Main_Category_H1_LINK}", "{Main_Category_H1_LINK_small}", "{Main_Category_H1_Main_Category_H1}", "{Main_Category_H1_Main_Category_H1_LINK}", "{Cars_List}", "{MarkaMfa_model_volume}", "{MarkaMfa_model_year}", "{MarkaMfa_model_types}"),
                array($h1_text, $h1_text_small, $h1_text, $modelData['model_name'], $modelData['model_transl'], $modelData['model_link'], $group_name, "<a href='$main_link'>$group_name</a>", "<a href='$main_link'>$group_name_small</a>", $head_name, "<a href='$head_link'>$head_name</a>", $this->getCatalogSeoCarsList($mfa_id, $model), $volume, $year, $types),
                $text
            );
        }
        
        return $text;
    }

    public function getCatalogSeoCarList($mod_ids): string
    {
        $db = DbSingleton::getTokoDb();
        $types = [];

        $r = $db->query("SELECT `TYP_MMT_TEXT`, `VOLUME_CM`, `TYP_HP_FROM` FROM `T_types` WHERE `TYP_MOD_ID` IN ($mod_ids);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $text   = $db->result($r, $i - 1, "TYP_MMT_TEXT");
            preg_match('#\((.*?)\)#', $text, $match);
            $text_m = (!empty($match)) ? $match[1] : "";
            $volume = $db->result($r, $i - 1, "VOLUME_CM");
            $hp     = $db->result($r, $i - 1, "TYP_HP_FROM");

            $types[$text_m][] = ['volume' => $volume, 'hp' => $hp];
        }

        $list = "<div class='types-form'>";
        foreach ($types as $type_id => $type) {
            $list .= "<div class='types-form__card'>
            <div class='types-form__card-title'>$type_id</div>";
            foreach ($type as $values) {
                $volume = $values['volume'];
                $hp     = $values['hp'];
                $list .= "<div>$volume ($hp " . $this->replaceLang("{horse_power_cap}") . ")</div>";
            }
            $list .= "</div>";
        }
        $list .= "</div>";

        return $list;
    }

    public function getCatalogSeoModelsInfo($mfa_id, $model): array
    {
        $db = DbSingleton::getTokoDb();
        $years = []; $mod_ids = []; $volumes = []; $types = "";

        $r = $db->query("SELECT `MOD_ID`, `MOD_PCON_START` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `Model` LIKE '$model';");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mod_id = $db->result($r, $i - 1, "MOD_ID");
            $year = $db->result($r, $i - 1, "MOD_PCON_START");
            $year = substr($year, 0, 4);
            $years[] = $year;
            $mod_ids[] = $mod_id;
        }

        $years = array_unique($years);
        sort($years);
        $years = implode(",", $years);
        $mod_ids = implode(",", $mod_ids);

        if (!empty($mod_ids)) {
            $r = $db->query("SELECT DISTINCT `VOLUME_CM` FROM `T_types` WHERE `TYP_MOD_ID` IN ($mod_ids) ORDER BY `VOLUME_CM`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $volume = $db->result($r, $i - 1, "VOLUME_CM");
                $volumes[] = $volume;
            }

            $types = $this->getCatalogSeoCarList($mod_ids);
        }

        $volumes = implode(",", $volumes);

        return array("years" => $years, "volumes" => $volumes, "types" => $types);
    }

    public function checkCatalogueSeoText($source_link): bool
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `CONTENT_" . $postfix . "` FROM `T2_SEO_TEXT` WHERE `ROUTER` = 'catalog' AND `LINK` = '$source_link' LIMIT 1;");
        $n = $db->num_rows($r);
        return ($n > 0);
    }

    /*
     * show products seo form
     * */
    public function getPartsCatalogueSeo($group_id, $page = 1, $params = [], $source_link = "", $h1_text = "", $mfa_id = 0, $model = "", $model_id = 0, $status_auto = 0, $status_auto_type = 0, $typ_id = 0)
    {
        $menu = new MenuClass();
        $form = $this->getHtmlForm("catalog_exist/seo");
        if ($page <= 1) {
            if ($status_auto === 0 || ($status_auto === 1 && $status_auto_type === 0)) {
                // SEO GENERATE
                $source_link = str_replace($this->getSiteLink() . $this->catalog_link . "/", "", $source_link);
                $source_link = rtrim($source_link, "/");

                if ($model_id === 0 && $mfa_id > 0 && !$this->checkCatalogueSeoText($source_link)) {
                    $list_filters = $this->getCatalogSeoFiltersGenerate($group_id, $params, $h1_text, $mfa_id, $model);
                    $form = str_replace(array("{seo_generate}", "{seo_generate_style}"), array($list_filters, ($list_filters === "") ? "none" : ""), $form);
                }

                // SEO filters
                if ($typ_id === "" || ($status_auto === 1 && $status_auto_type === 0)) {
                    if (!empty($params)) {
                        $list_filters = $this->getCatalogSeoFiltersForm($group_id, $params);
                        $form = str_replace(array("{seo_filters}", "{seo_filters_style}"), array($list_filters, ($list_filters === "") ? "none" : ""), $form);
                    }
                }

                // SEO details
                if ($typ_id === "" || ($status_auto === 1 && $status_auto_type === 0)) {
                    if ($mfa_id > 0) {
                        if ($model !== "") {
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
                if ($typ_id === "" || ($status_auto === 1 && $status_auto_type === 0)) {
                    $form = str_replace("{seo_popular}", $menu->getCatalogFaqForm($h1_text), $form);
                }
            }
        }

        $form = str_replace(
            array("{seo_filters}", "{seo_auto}", "{seo_popular}", "{seo_style}", "{seo_filters_style}", "{seo_generate}", "{seo_generate_style}"),
            array("", "", "", "none", "none", "", "none"),
            $form
        );

        return $this->replaceLang($form);
    }

    /*
     * get MOD ID list
     * */
    public function getGroupCarModIDList($group_id, $mfa_id_sel = 0, $model = "")
    {
        $group_id = $this->getUrlNumber($group_id);
        $db = DbSingleton::getTokoDb();

        $list       = "";
        $link       = $this->catalog_link;
        $det_cap    = "{all_type_models}";
        $no_photo    = $this->noPhoto;

        if ($group_id > 0) {
            $groupData = $this->getGroupRowData($group_id);
            $group_name = $groupData["name"];
            $group_link = $groupData["link"];
            $link       .= "/$group_link/auto";
            $det_cap    = $group_name . " {on_cap}";
        }

        $r = $db->query("SELECT mf.MFA_BRAND_LINK, mf.MFA_BRAND, md.Model_Link 
        FROM `T_manufacturers` mf
            LEFT JOIN `T_models` md ON (md.MOD_MFA_ID = mf.MFA_ID)
        WHERE mf.`MFA_ID` = $mfa_id_sel AND md.`Model` = '$model' 
        GROUP BY md.`Model`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_link   = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mfa_brand  = $db->result($r, $i - 1, "MFA_BRAND");
            $mod_link   = $db->result($r, $i - 1, "Model_Link");

            $list .= "
            <div>
                <span>$det_cap $mfa_brand $model</span>
            </div>";

            $list .= "
            <div class=\"seo-auto-list seo_details\">";

            $r2 = $db->query("SELECT `TEX_TEXT_link`, `TEX_TEXT`, `Car_pict`, `MOD_PCON_START`, `MOD_PCON_END` 
            FROM `T_models` 
            WHERE `MOD_MFA_ID` = $mfa_id_sel AND `Model` = '$model' 
            ORDER BY `MOD_PCON_START`;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $mod_id_lnk = $db->result($r2, $i2 - 1, "TEX_TEXT_link");
                $text       = $db->result($r2, $i2 - 1, "TEX_TEXT");
                $image      = $db->result($r2, $i2 - 1, "Car_pict");
                $d_start    = $db->result($r2, $i2 - 1, "MOD_PCON_START");
                $d_start    = substr($d_start, 0, 4);
                $d_end      = $db->result($r2, $i2 - 1, "MOD_PCON_END");
                $d_end      = substr($d_end, 0, 4);
                $d_end      = ($d_end === 0) ? "{cur_time}" : $d_end;

                $list .= "
                <a class=\"seo-li\" href=\"" . $this->getSiteLink() . "$link/$mfa_link/$mod_link/$mod_id_lnk/\">
                    <div class=\"row\">
                        <div class=\"col-4\">
                            <img data-src=\"/uploads/images/models/$image\" class=\"lazy\" alt=\"$text\" title=\"$text\" src=\"$no_photo\">
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

        $list .= $this->getGroupCarMfaList($group_id, $mfa_id_sel, 1);

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        
        return str_replace(
            array("{seo_auto_title}", "{seo_auto_list}", "{seo_auto_letters}"), 
            array("", $list, ""), 
        $form);
    }

    /*
     * get MOD ID list
     * */
    public function getGroupCarModList($group_id, $mfa_id_sel = 0)
    {
        $group_id   = $this->getUrlNumber($group_id);
        $mfa_id_sel = $this->getUrlNumber($mfa_id_sel);

        $dbc = DbSingleton::getTokoCacheDb();
        $autoObj = new AutoClass();

        $list = "";
        $link = $this->catalog_link;

        if ($group_id > 0) {
            $link       .= "/" . $this->getGroupRowLink($group_id) . "/auto";
            $det_cap    = $this->getGroupRowName($group_id) . " {on_cap}";
        } else {
            $det_cap    = "{details_on_cap}";
        }

        if ($this->checkTableMfa($group_id) > 0) {
            $mfa_brand  = $autoObj->getMfaBrand($mfa_id_sel);
            $mfa_link   = $this->getManufactureLink($mfa_id_sel);

            if ($mfa_id_sel === 0) {
                $list .= "
                <div>
                    <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/\">$det_cap $mfa_brand</a>
                </div>";
            } else {
                $list .= "
                <div>
                    $det_cap $mfa_brand
                </div>";
            }

            $list .= "
            <div class=\"seo-auto-list\">";

            $r = $dbc->query("SELECT `model` FROM `EX_TABLE_TREE_MFA_$group_id` WHERE `mfa_id` = $mfa_id_sel GROUP BY `mfa_id`, `model`;");
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model      = $dbc->result($r, $i - 1, "model");
                $model_link = $this->getModelLink($model);

                $list .= "
                <div class=\"seo-auto-list__item\">
                    <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/$model_link/\">
                        <span>$mfa_brand $model</span>
                    </a>
                </div>";
            }

            $list .= "
            </div>";
        }

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        
        return str_replace(
            array("{seo_auto_title}", "{seo_auto_list}", "{seo_auto_letters}"),
            array("", $list, ""),
            $form
        );
    }

    /*
     * get MFA ID list
     * */
    public function getGroupCarMfaList($group_id, $mfa_id_sel = 0, $status = 0, $letter = "")
    {
        $group_id   = $this->getUrlNumber($group_id);
        $mfa_id_sel = $this->getUrlNumber($mfa_id_sel);

        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $autoObj = new AutoClass();

        $list       = "";
        $link       = $this->catalog_link . "/";
        $det_cap    = "{details_on_cap}";
        $title      = "";

        if ($group_id > 0) {
            $groupData = $this->getGroupRowData($group_id);
            $group_name = $groupData["name"];
            $group_link = $groupData["link"];
            $det_cap    = $group_name;
            $link       .= "$group_link/auto";

            if ($mfa_id_sel > 0) {
                $title = "$det_cap {on_cap} {other_models} " . $autoObj->getMfaBrand($mfa_id_sel);
            } else {
                $title = $det_cap;
            }
            $det_cap .= " {on_cap}";
        }
        
        $where = "1";
        if ($status > 0) {
            $where = "exmf.`mfa_id` = $mfa_id_sel";
        }

        if ($letter !== "") {
            $mfaList = [];
            $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `ACTIVE` = 1 AND `MFA_BRAND` LIKE '$letter%';");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $mfaList[] = (int)$db->result($r, $i - 1, "MFA_ID");
            }
            if (!empty($mfaList)) {
                $mfaStr = implode(",", $mfaList);
                $where = "exmf.`mfa_id` IN ($mfaStr)";
            } else {
                $where = "0";
            }
        }

        if ($this->checkTableMfa($group_id) > 0) {

            $mfaList = [];
            $r = $dbc->query("SELECT DISTINCT exmf.`mfa_id`, exmf.`model`
            FROM `EX_TABLE_TREE_MFA_$group_id` exmf
            WHERE $where;");
            $n = $dbc->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $mfa_id     = (int)$dbc->result($r, $i - 1, "mfa_id");
                $model      = $dbc->result($r, $i - 1, "model");

                $mfaList[$mfa_id][] = $model;
            }

            if (!empty($mfaList)) {
                foreach ($mfaList as $mfa_id => $models) {
                    $mfaData    = $this->getMfaData($mfa_id);
                    $mfa_brand  = $mfaData["mfa_brand"];
                    $mfa_link   = $mfaData["mfa_link"];

                    $list .= "
                    <div class=\"seo-auto-content-title\">
                        <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/\">$det_cap $mfa_brand</a>
                    </div>";

                    $list .= "
                    <div class=\"seo-auto-list\">";

                    foreach ($models as $model) {
                        $model_link = $this->getModelLink($model);

                        $list .= "
                        <div class=\"seo-auto-list__item\">
                            <a href=\"" . $this->getSiteLink() . "$link/$mfa_link/$model_link/\">$mfa_brand $model</a>
                        </div>";
                    }

                    $list .= "
                    </div>";
                }
            } else {
                $list = "<div>{auto_nothing_found}</div>";
            }
        }

        $letters = "<ul class='alpha'>";
        $alphas = range('A', 'Z');
        foreach ($alphas as $alpha) {
            $letters .= "<li class='alpha-item'><a id=\"alpha-$alpha\" onclick=\"getGroupCarMfaList('$group_id','$mfa_id_sel','$status','$alpha',this)\">$alpha</a></li>";
        }
        $letters .= "</ul>";

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        $form = str_replace(array("{seo_auto_title}", "{seo_auto_list}", "{seo_auto_letters}"), array($title, $list, $letters), $form);
        
        return $this->replaceLang($form);
    }

    public function getCatalogH1Seo($group_id, $params = []): string
    {
        $car_text = "";
        $group_name = ($group_id > 0) ? $this->getGroupRowName($group_id) : "";
        $group_text = $group_name;

        if (!empty($params)) {

            // brand or param, >2 params
            if (count($params) > 1) {
                $count_params = 0;
                ksort($params);

                foreach ($params as $param_id => $values) {
                    $param_name = $this->getGroupParamName($param_id);

                    if ((int)$param_id === 0) {
                        foreach ($values as $brand_id) {
                            $brand_name = $this->getBrandName($brand_id);
                            $group_text .= " $brand_name, ";
                        }
                        $group_text = rtrim($group_text, ", ");
                    }

                    if ($param_id > 0) {
                        $count_params++;
                        if ($count_params === 1) {
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

                // only brands
                if (count($params) === 1) {
                    $group_text = $group_name;
                    if (count($params[0]) >= 1) {
                        foreach ($params[0] as $value_id) {
                            $brand_name = $this->getGroupValueName($value_id);
                            $group_text .= " $brand_name, ";
                        }
                        $group_text = rtrim($group_text, ", ");
                    }
                }

                // 1 brand + 1 param
                if (count($params) === 2) {
                    $group_text = $group_name;
                    $count_params = 0;
                    ksort($params);

                    foreach ($params as $param_id => $values) {

                        if ((int)$param_id === 0) {
                            foreach ($values as $brand_id) {
                                $brand_name = $this->getBrandName($brand_id);
                                $group_text .= " $brand_name";
                            }
                        }

                        if ($param_id > 0) {
                            $param_name = $this->getGroupParamName($param_id);
                            $count_params++;
                            if ($count_params === 1) {
                                $group_text .= ":";
                            }
                            $group_text .= " $param_name - ";

                            foreach ($values as $value_id) {
                                $value_name = $this->getGroupValueName($value_id, $param_id);
                                $value_h1_name = $this->getGroupValueH1($value_id, $param_id);

                                if (count($values) === 1) {
                                    if ($value_h1_name !== "") {
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
            }

            // no brands, only params
            if (!array_key_exists(0, $params) && count($params) === 1) {
                $group_text = $group_name;
                $count_params = 0;

                foreach ($params as $param_id => $values) {
                    $param_name = $this->getGroupParamName($param_id);
                    $count_params++;

                    if ($count_params === 1) {
                        $group_text .= ":";
                    }
                    if (count($values) > 1) {
                        $group_text .= " $param_name - ";
                    }
                    foreach ($values as $value_id) {
                        $value_name     = $this->getGroupValueName($value_id, $param_id);
                        $value_h1_name  = $this->getGroupValueH1($value_id, $param_id);

                        if ($value_h1_name !== "") {
                            $group_text .= " $value_h1_name, ";
                        } else {
                            $group_text .= " $value_name, ";
                        }
                    }
                    $group_text = rtrim($group_text, ", ");
                }
            }
        }

        $title = "$group_text $car_text";

        return rtrim($title, " ");
    }

    /*
     * catalog h1
     * */
    public function getCatalogH1($group_id, $params = [], $mfa_id = 0, $model = "", $model_id = 0, $status = 0): array
    {
        $autoObj = new AutoClass();
        $car_text = "";
        $car_text1 = "";
        $car_text2 = "";
        $group_name = ($group_id > 0) ? $this->getGroupRowName($group_id) : "";
        $group_text = $group_name;
        $params_text = "";

        if ($mfa_id > 0) {
            $mfa_name = $autoObj->getMfaBrand($mfa_id);
            $car_text = "{on_cap} $mfa_name";
            if ($model !== "") {
                $car_text .= " $model";
                if ($model_id > 0) {
                    $model_id_name = $autoObj->getModIdName($model_id);
                    $car_text .= " $model_id_name";
                }
            }
        }

        if ($status === 1) {
            $lang_id = $this->getLanguage();
            if ($lang_id < 3) {
                $car_text1 = $car_text . " " . $autoObj->getCarManufactureTranslate($mfa_id, $model);
            }
        }
        if ($status === 2) {
            $lang_id = $this->getLanguage();
            if ($lang_id < 3) {
                if ($mfa_id > 0) {
                    $car_text2 = "{on_cap}";
                }
                $car_text2 .= " " . $autoObj->getCarManufactureTranslate($mfa_id, $model, 2);
            }
        }

        if (!empty($params)) {

            // brand or param, >2 params
            if (count($params) > 1) {
                $count_params = 0;
                ksort($params);

                foreach ($params as $param_id => $values) {
                    $param_name = $this->getGroupParamName($param_id);

                    if ((int)$param_id === 0) {
                        foreach ($values as $brand_id) {
                            $brand_name = $this->getBrandName($brand_id);
                            $group_text .= " $brand_name, ";
                            $params_text .= " $brand_name, ";
                        }
                        $group_text = rtrim($group_text, ", ");
                        $params_text = rtrim($params_text, ", ");
                    }

                    if ($param_id > 0) {
                        $count_params++;
                        if ($count_params === 1) {
                            $group_text .= ":";
                            $params_text .= ":";
                        }
                        $group_text .= " $param_name - ";
                        $params_text .= " $param_name - ";
                        foreach ($values as $value_id) {
                            $value_name = $this->getGroupValueName($value_id, $param_id);
                            $group_text .= "$value_name, ";
                            $params_text .= "$value_name, ";
                        }
                        $group_text = rtrim($group_text, ", ");
                        $group_text .= "; ";
                        $params_text = rtrim($params_text, ", ");
                        $params_text .= "; ";
                    }
                }
                $group_text = rtrim($group_text, "; ");
                $params_text = rtrim($params_text, "; ");
            }

            // with brand
            if (array_key_exists(0, $params)) {

                // only brands
                if (count($params) === 1) {
                    $group_text = $group_name;
                    $params_text = "";
                    if (count($params[0]) >= 1) {
                        foreach ($params[0] as $value_id) {
                            $brand_name = $this->getGroupValueName($value_id);
                            $group_text .= " $brand_name, ";
                            $params_text .= " $brand_name, ";
                        }
                        $group_text = rtrim($group_text, ", ");
                        $params_text = rtrim($params_text, ", ");
                    }
                }

                // 1 brand + 1 param
                if (count($params) === 2) {
                    $group_text = $group_name;
                    $params_text = "";
                    $count_params = 0;
                    ksort($params);

                    foreach ($params as $param_id => $values) {

                        if ((int)$param_id === 0) {
                            foreach ($values as $brand_id) {
                                $brand_name = $this->getBrandName($brand_id);
                                $group_text .= " $brand_name";
                                $params_text .= " $brand_name";
                            }
                        }

                        if ($param_id > 0) {
                            $param_name = $this->getGroupParamName($param_id);
                            $count_params++;
                            if ($count_params === 1) {
                                $group_text .= ":";
                                $params_text .= ":";
                            }
                            $group_text .= " $param_name - ";
                            $params_text .= " $param_name - ";

                            foreach ($values as $value_id) {
                                $value_name = $this->getGroupValueName($value_id, $param_id);
                                $value_h1_name = $this->getGroupValueH1($value_id, $param_id);

                                if (count($values) === 1) {
                                    if ($value_h1_name !== "") {
                                        $group_text = $value_h1_name;
                                        $params_text = $value_h1_name;
                                    } else {
                                        $group_text .= " $value_name";
                                        $params_text .= " $value_name";
                                    }
                                } else {
                                    $group_text = " $group_text";
                                    $params_text = " $params_text";
                                }
                            }
                        }
                    }
                }
            }

            // no brands, only params
            if (!array_key_exists(0, $params) && count($params) === 1) {
                $group_text = $group_name;
                $params_text = "";
                $count_params = 0;

                foreach ($params as $param_id => $values) {
                    $param_name = $this->getGroupParamName($param_id);
                    $count_params++;

                    if ($count_params === 1) {
                        $group_text .= ":";
                        $params_text .= ":";
                    }
                    if (count($values) > 1) {
                        $group_text .= " $param_name - ";
                        $params_text .= " $param_name - ";
                    }
                    foreach ($values as $value_id) {
                        $value_name     = $this->getGroupValueName($value_id, $param_id);
                        $value_h1_name  = $this->getGroupValueH1($value_id, $param_id);

                        if ($value_h1_name !== "") {
                            $group_text .= " $value_h1_name, ";
                            $params_text .= " $value_h1_name, ";
                        } else {
                            $group_text .= " $value_name, ";
                            $params_text .= " $value_name, ";
                        }
                    }
                    $group_text = rtrim($group_text, ", ");
                    $params_text = rtrim($params_text, ", ");
                }
            }
        }

        $title = "$group_text $car_text";
        $title = rtrim($title, " ");

        $titleTextTranslate = "$group_text $car_text1";
        $titleTextTranslate = rtrim($titleTextTranslate, " ");
        $titleDescriptionTranslate = "$group_text $car_text2";
        $titleDescriptionTranslate = rtrim($titleDescriptionTranslate, " ");

        return array($title, $titleTextTranslate, $titleDescriptionTranslate, $params_text);
    }

    public function getPartsCatalogueStates($group_id): string
    {
        $menu = new MenuClass();
        $db = DbSingleton::getTokoDb();

        $list = "";
        if ($group_id > 0) {
            $postfix = $this->getLangPostfix($this->getLanguage());

            $r = $db->query("SELECT t2r.`ID`, t2r.`TITLE_" . $postfix . "` 
            FROM `T2_GROUP_REVIEW` t2gr 
                LEFT JOIN `T2_REVIEWS` t2r ON (t2r.`ID` = t2gr.`REVIEW_ID`)
            WHERE t2gr.`GROUP_ID` = $group_id AND t2r.`STATUS` = 1
            GROUP BY t2gr.`REVIEW_ID`;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $list = "
                <div class=\"reviews-list-title\">{states_cap}</div>
                    <div class=\"reviews-list\">";
            }

            for ($i = 1; $i <= $n; $i++) {
                $review_id      = $db->result($r, $i - 1, "ID");
                $review_title   = $db->result($r, $i - 1, "TITLE_$postfix");
                $transcript     = $menu->formatUrlText($review_title);
                $link           = "/reviews/state/$review_id/$transcript/";
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
        if ($head_link !== "") {
            $r = $db->query("SELECT `HEAD_ID` FROM `T2_TREE_HEAD_EXIST` WHERE `TEX_LINK` = '$head_link' AND `STATUS` = 1 LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $head_id = $db->result($r, 0, "HEAD_ID");
            }
        }
        return $head_id;
    }

    public function getGroupCatExistId($cat_link)
    {
        $db = DbSingleton::getTokoDb();
        $cat_id = 0;
        if ($cat_link !== "") {
            $r = $db->query("SELECT `CAT_ID` FROM `T2_TREE_CAT_EXIST` WHERE `TEX_LINK` = '$cat_link' AND `STATUS` = 1 LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $cat_id = $db->result($r, 0, "CAT_ID");
            }
        }
        return $cat_id;
    }

    public function getHeaderBreadCrumb($head_id, $cat_id = 0): array
    {
        $arr = [];

        $arr[] = [
            "name" => "{seo_site_toko}",
            "link" => $this->getSiteLink()
        ];
        $arr[] = [
            "name" => "{site_catalog}",
            "link" => $this->getSiteLink() . "$this->catalog_link/"
        ];

        if ($head_id > 0) {
            $head_name = $this->getHeadExistName($head_id);
            $head_link = $this->getHeadExistLink($head_id);
            $arr[] = [
                "name" => $head_name,
                "link" => $this->getSiteLink() . "$this->catalog_link/$head_link/"
            ];

            if ($cat_id > 0) {
                $catData    = $this->getCatRowData($cat_id);
                $cat_name   = $catData["cat_name"];
                $cat_link   = $catData["cat_link"];
                if ($head_id === 1) {
                    $cat_name .= " - " . $this->getHeadRowName($head_id);
                }
                $arr[] = [
                    "name" => $cat_name,
                    "link" => $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/"
                ];
            }
        }

        return $arr;
    }

    public function showGroupHeadForm($head_id): array
    {
        $db = DbSingleton::getTokoDb();
        $postfix = $this->getLangPostfix($this->getLanguage());
        $r = $db->query("SELECT `TEX_" . $postfix . "` FROM `T2_TREE_HEAD_EXIST` WHERE `STATUS` = 1 AND `HEAD_ID` = $head_id LIMIT 1;");
        $h1_text = $db->result($r, 0, "TEX_$postfix");

        $form = $this->getHtmlForm("catalog_exist/head_form");
        $form = str_replace(array("{head_h1}", "{head_list}"), array($h1_text, $this->getCatalogColListCat($head_id)), $form);
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

    public function showGroupCatForm($head_id, $cat_id): array
    {
        $h1_text = $this->getCatRowData($cat_id)["cat_name"];
        if ($head_id === 1) {
            $h1_text .= " - " . $this->getHeadRowName($head_id);
        }
        $form = $this->getHtmlForm("catalog_exist/cat_form");
        $form = str_replace(array("{cat_h1}", "{cat_list}"), array($h1_text, $this->getCatalogColListGroup($head_id, $cat_id)), $form);
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
    public function getCatalogParamsCount($params): array
    {
        $count_brands = $count_params = $count_values = 0;

        foreach ($params as $param_id => $values) {
            if ((int)$param_id === 0) {
                $count_brands += count($values);
            } else {
                $count_params += count($values);
            }
            if (count($values) > 1) {
                $count_values++;
            }
        }
        $real_count_brands = $count_brands;
        if ($count_brands > 1) {
            $count_brands = 1;
        }
        $real_count_params = $count_params;
        if ($count_params > 1) {
            $count_params = 1;
        }

        return array($count_brands, $count_params, $count_values, $real_count_params, $real_count_brands);
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
        return str_replace(
            array("{h1_meta_tag}", "{url_meta_tag}", "{main_image_cap}"),
            array($h1_text, $url_text, $img_text),
        $form);
    }

    public function getSitemapArray(): array
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        /*
         * sitemap-groups
         * sitemap-groups-manufactures
         * sitemap-groups-params
         * */
        $arr_groups = [];
        $arr_groups_models = [];
        $arr_groups_params = [];

        $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE_GROUP` WHERE 1;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id = (int)$db->result($r, $i - 1, "group_id");

            if ($group_id > 0 && $this->checkTable($group_id)) {
                $arr_groups[] = $group_id;

                if ($this->checkTableMfa($group_id) > 0) {
                    $r1 = $dbc->query("SELECT `mfa_id`, `model` FROM `EX_TABLE_TREE_MFA_" . $group_id . "`;");
                    $n1 = $dbc->num_rows($r1);
                    for ($i1 = 1; $i1 <= $n1; $i1++) {
                        $mfa_id = $dbc->result($r1, $i1 - 1, "mfa_id");
                        $model  = $dbc->result($r1, $i1 - 1, "model");
                        $arr_groups_models[$group_id][$mfa_id][] = $model;
                    }
                }

                if ($this->checkTableParams($group_id) > 0) {
                    $params = $this->getPartsFiltersArr($group_id)["arr"];
                    $arr_groups_params[$group_id] = $params;
                }
            }
        }

        /*
         * sitemap-groups-manufactures-params
         * */
        $arr_groups_models_params = [];

        foreach ($arr_groups_params as $group_id => $params) {
            $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
            $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
            foreach ($params as $param_id => $values) {

                if ($param_id > 0) {
                    foreach ($values as $value_id) {

                        if ($value_id > 0) {
                            $r = $dbc->query("SELECT tm.mfa_id, tm.model
                            FROM `$table_mfa` tm
                                LEFT JOIN `$table_params` tp ON tp.art_id = tm.art_id
                            WHERE `param_$param_id` = '$value_id' OR `param_$param_id` LIKE '$value_id,%' OR `param_$param_id` LIKE '%,$value_id' OR `param_$param_id` LIKE '%,$value_id,%';");
                            $n = $dbc->num_rows($r);

                            if ($n > 0) {
                                for ($i = 1; $i <= $n; $i++) {
                                    $mfa_id = (int)$dbc->result($r, $i - 1, "mfa_id");
                                    $model  = $dbc->result($r, $i - 1, "model");
                                    $arr_groups_models_params[$group_id][$param_id][$value_id][$mfa_id][] = $model;
                                }
                            }
                        }
                    }
                }
            }
        }

        /*
         * sitemap-pages
         * */
        $arr_modules = [];

        $r = $db->query("SELECT `MODULE` FROM `T2_MODULES` WHERE `STATUS` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $module = $db->result($r, $i - 1, "MODULE");
            $arr_modules[] = $module;
        }

        /*
         * sitemap-cars
         * */
        $arr_cars = [];

        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_ID`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = (int)$db->result($r, $i - 1, "MFA_ID");

            $r1 = $db->query("SELECT DISTINCT `Model` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 ORDER BY `Model`;");
            $n1 = $db->num_rows($r1);
            for ($i1 = 1; $i1 <= $n1; $i1++) {
                $model = $db->result($r1, $i1 - 1, "Model");
                $arr_cars[$mfa_id][] = $model;
            }
        }

        return compact("arr_modules", "arr_cars", "arr_groups", "arr_groups_params", "arr_groups_models", "arr_groups_models_params");
    }

}
