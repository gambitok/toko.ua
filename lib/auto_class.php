<?php

class AutoClass extends CatalogueClass
{

    use Helper;
    use Variables;

    /*
     * get text translate of selected car
     * */
    public function getCarManufTranslit($mfa_id, $model = "")
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $model = $this->getUrlString($model);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_BRAND_TRANSLIT` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        $mfa_translate = $db->result($r, 0, "MFA_BRAND_TRANSLIT");
        $text = "";
        if ($mfa_translate != "") {
            $text = "($mfa_translate)";
        }
        if ($model != "") {
            $r = $db->query("SELECT `Model_TRANSLIT` FROM `T_models` WHERE `Model` = '$model' AND `Model_TRANSLIT` != '' LIMIT 1;");
            $model_translate = $db->result($r, 0, "Model_TRANSLIT");
            if ($model_translate != "") {
                $text = "($mfa_translate $model_translate)";
            }
        }
        return $text;
    }

    /*
     * get car full description
     * from ID
     * */
    public function getCarDescription($typ_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TYP_MOD_ID`, `TYP_TEXT` FROM `T_types` WHERE `TYP_ID` = $typ_id LIMIT 1;");
        $model_id = $db->result($r, 0, "TYP_MOD_ID") + 0;
        $typ_cap = $db->result($r, 0, "TYP_TEXT");
        $r = $db->query("SELECT `MOD_MFA_ID`, `TEX_TEXT` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
        $mfa_id = $db->result($r, 0, "MOD_MFA_ID") + 0;
        $mod_cap = $db->result($r, 0, "TEX_TEXT");
        $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        $mfa_cap = $db->result($r, 0, "MFA_BRAND");
        $car_cap = "$mfa_cap $mod_cap $typ_cap";
        if ($typ_id == 0) {
            $car_cap = $this->replaceLang("{choose_spare}");
        }
        return $car_cap;
    }

    /*
     * get car mfa, model, model_id ids
     * from TYP ID
     * */
    public function getCarInfo($typ_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TYP_MOD_ID` FROM `T_types` WHERE `TYP_ID` = $typ_id LIMIT 1;");
        $model_id = $db->result($r, 0, "TYP_MOD_ID") + 0;
        $r = $db->query("SELECT `MOD_MFA_ID`, `Model` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
        $mfa_id = $db->result($r, 0, "MOD_MFA_ID") + 0;
        $model = $db->result($r, 0, "Model");
        return array($mfa_id, $model, $model_id);
    }

    /*
     * get car mfa, model, model_id, typ text
     * from IDs
     * */
    public function getAutoDescr($mfa_id, $model = "", $model_id = 0, $typ_id = 0)
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $model = $this->getUrlString($model);
        $model_id = $this->getUrlNumber($model_id);
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $mfa_text = $model_text = $model_id_text = $typ_text = "";
        if ($mfa_id > 0) {
            $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
            $mfa_text = $db->result($r, 0, "MFA_BRAND");
        }
        if ($model != "") {
            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model` = '$model' LIMIT 1;");
            $model_text = $db->result($r, 0, "Model");
        }
        if ($model_id > 0) {
            $r = $db->query("SELECT `TEX_TEXT` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
            $model_id_text = $db->result($r, 0, "TEX_TEXT");
        }
        if ($typ_id > 0) {
            $r = $db->query("SELECT `TYP_TEXT` FROM `T_types` WHERE `TYP_ID` = $typ_id AND `ACTIVE` = 1 LIMIT 1;");
            $typ_text = $db->result($r, 0, "TYP_TEXT");
        }
        return array($mfa_text, $model_text, $model_id_text, $typ_text);
    }

    /*
     * get car mfa & model names
     * from LINK
     * */
    public function getAutoDescrLink($mfa_link, $model_link)
    {
        $mfa_link = $this->getUrlString($mfa_link);
        $model_link = $this->getUrlString($model_link);
        $db = DbSingleton::getTokoDb();
        $mfa_brand = $model = "";
        if ($mfa_link != "") {
            $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' LIMIT 1;");
            $mfa_brand = $db->result($r, 0, "MFA_BRAND");
        }
        if ($model_link != "") {
            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link` = '$model_link' LIMIT 1;");
            $model = $db->result($r, 0, "Model");
        }
        return array($mfa_brand, $model);
    }

    /*
     * get car mfa & model ids
     * from LINK
     * */
    public function getAutoIdsLink($mfa_link, $model_link)
    {
        $mfa_link = $this->getUrlString($mfa_link);
        $model_link = $this->getUrlString($model_link);
        $db = DbSingleton::getTokoDb();
        $mfa_id = $model = "";
        if ($mfa_link != "") {
            $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' LIMIT 1;");
            $mfa_id = $db->result($r, 0, "MFA_ID");
        }
        if ($model_link != "") {
            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link` = '$model_link' LIMIT 1;");
            $model = $db->result($r, 0, "Model");
        }
        return array($mfa_id, $model);
    }

    /*
     * get car mfa name
     * from ID
     * */
    public function getMfaBrand($mfa_id)
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        return $db->result($r, 0, "MFA_BRAND");
    }

    /*
     * get car mfa id
     * from LINK
     * */
    public function getMfaLink($mfa_link)
    {
        $mfa_link = $this->getUrlString($mfa_link);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' LIMIT 1;");
        return intval($db->result($r, 0, "MFA_ID"));
    }

    /*
     * get car model name
     * from LINK
     * */
    public function getModLink($mod_link)
    {
        $mod_link = $this->getUrlString($mod_link);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link` = '$mod_link' LIMIT 1;");
        return $db->result($r, 0, "Model");
    }

    /*
     * get car model name
     * from LINK
     * */
    public function getModIdName($mod_id)
    {
        $mod_id = $this->getUrlNumber($mod_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_TEXT` FROM `T_models` WHERE `MOD_ID` = '$mod_id' LIMIT 1;");
        return $db->result($r, 0, "TEX_TEXT");
    }

    /*
     * get car model name
     * from LINK
     * */
    public function getModIdLink($mod_id_link)
    {
        $mod_id_link = $this->getUrlString($mod_id_link);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MOD_ID` FROM `T_models` WHERE `TEX_TEXT_link` = '$mod_id_link' LIMIT 1;");
        return $db->result($r, 0, "MOD_ID");
    }

    /*
     * get car mfa, model, model_id images
     * from ID
     * */
    public function getAutoIMG($mfa_id, $model, $model_id = 0)
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $model = $this->getUrlString($model);
        $model_id = $this->getUrlNumber($model_id);
        $db = DbSingleton::getTokoDb();
        $mfa_image = $model_image = $model_id_image = "";
        if ($mfa_id > 0) {
            $r = $db->query("SELECT `LOGO` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
            $mfa_image = $db->result($r, 0, "LOGO");
        }
        if ($model != "") {
            $r = $db->query("SELECT `Car_pict` FROM `T_models` WHERE `Model` = '$model' ORDER BY `Active_pict` DESC LIMIT 1;");
            $model_image = $db->result($r, 0, "Car_pict");
        }
        if ($model_id > 0) {
            $r = $db->query("SELECT `Car_pict` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
            $model_id_image = $db->result($r, 0, "Car_pict");
        }
        return compact("mfa_image", "model_image", "model_id_image");
    }

    /*
     * Get GROUP text info
     * */
    public function getGroupInfo($typ_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TYP_KW_FROM`, `TYP_HP_FROM`, `TYP_CCM`, `FUEL_ID`, `ENG_Cod`, `TYP_MMT_TEXT`,
        CASE WHEN TYP_PCON_START = 0 THEN '' ELSE TYP_PCON_START END AS TYP_PCON_START,
        CASE WHEN TYP_PCON_END = 0 THEN '' ELSE TYP_PCON_END END AS TYP_PCON_END
        FROM `T_types` 
        WHERE `TYP_ID` = $typ_id AND `ACTIVE` = 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $kw_from = $db->result($r, 0, "TYP_KW_FROM");
            $hp_from = $db->result($r, 0, "TYP_HP_FROM");
            $ccm = $db->result($r, 0, "TYP_CCM");
            $fuel = $db->result($r, 0, "FUEL_ID");
            $eng_cod = $db->result($r, 0, "ENG_Cod");
            $full_name = $db->result($r, 0, "TYP_MMT_TEXT");
            $d_start = $db->result($r, 0, "TYP_PCON_START");
            $d_end = $db->result($r, 0, "TYP_PCON_END");
            $fuel = $this->getFuelName($fuel);
            if (mb_strlen($d_start) == 6) {
                $d_start = substr($d_start, 0, 4) . "." . substr($d_start, 4, 2);
            }
            if (mb_strlen($d_end) == 6) {
                $d_end = substr($d_end, 0, 4) . "." . substr($d_end, 4, 2);
            }
            $text = "$full_name ($d_start - $d_end)<br>$fuel, $hp_from {horse_power_cap} / $kw_from {kilo_wat_cap}, $ccm cm3, $eng_cod";
        } else {
            $text = "";
        }
        return $text;
    }

    /*
     * get garage chosen form
     * */
    public function getChosenAutoGarage($client_id, $user_id)
    {
        $db = DbSingleton::getTokoDb();
        $cookie = $this->getSessionID();
        $where = ($user_id == 0) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";
        $r = $db->query("SELECT `typ_id` FROM `AUTO_GARAGE` WHERE $where AND `status` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $typ_id = $db->result($r, 0, "typ_id");
            $typ_text = $this->getGroupInfo($typ_id);
            list($mfa_id, $model, $model_id) = $this->getCarInfo($typ_id);
            list($mfa_cap, , $model_id_cap) = $this->getAutoDescr($mfa_id, $model, $model_id, $typ_id);
            $model_id_image = $this->getAutoIMG($mfa_id, $model, $model_id)["model_id_image"];
            $auto_form = $this->getHtmlForm("garage/garage_selected");
            $auto_form = str_replace("{manufacture_cap}", $mfa_cap, $auto_form);
            $auto_form = str_replace("{model_id_cap}", $model_id_cap, $auto_form);
            $auto_form = str_replace("{models_img}", $model_id_image, $auto_form);
            $auto_form = str_replace("{typ_text}", $typ_text, $auto_form);
        } else {
            $auto_form = "{choose_auto_first}";
        }
        return $this->replaceLang($auto_form);
    }

    /*
     * delete garage item
     * */
    public function deleteAutoGarage($auto_id)
    {
        $auto_id = $this->getUrlNumber($auto_id);
        $db = DbSingleton::getTokoDb();
        $db->query("DELETE FROM `AUTO_GARAGE` WHERE `id` = $auto_id;");
        $client_id = $this->getClient();
        $user_id = $this->getUser();
        $cookie = $this->getSessionID();
        $where = ($user_id == 0) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";
        $r = $db->query("SELECT `id`, `typ_id` FROM `AUTO_GARAGE` WHERE $where ORDER BY `timestamp` DESC LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            setcookie("auto_typ_id", "", time() - 3600, "/");
            return false;
        } else {
            $id = $db->result($r, 0, "id");
            $typ_id = $db->result($r, 0, "typ_id");
            $db->query("UPDATE `AUTO_GARAGE` SET `status` = 1 WHERE `id` = $id LIMIT 1;");
            setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");
            return true;
        }
    }

    public function getAutoGarageData()
    {
        $client_id = $this->getClient();
        $user_id = $this->getUser();
        $cookie = $this->getSessionID();
        $where = ($user_id == 0) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";
        return "SELECT `id`, `typ_id` FROM `AUTO_GARAGE` WHERE $where;";
    }

    /*
     * show garage form
     * */
    public function showGarageForm()
    {
        $db = DbSingleton::getTokoDb();
        $form = $this->getHtmlForm("garage/garage");
        $list = $auto_form = "";
        $query = $this->getAutoGarageData();
        $r = $db->query($query);
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $id = $db->result($r, $i - 1, "id");
                $typ_id = $db->result($r, $i - 1, "typ_id");
                list($mfa_id, $model, $model_id) = $this->getCarInfo($typ_id);
                if ($typ_id != $this->getCookieAuto()) {
                    $status_cap = "{select_cap}";
                    $status_disable = "";
                    $status_btn = "onclick='showGarageForm($id);'";
                } else {
                    $status_cap = "{unselect_cap}";
                    $status_disable = "disabled";
                    $status_btn = "";
                }
                list($mfa_cap, , $model_id_cap, $typ_text) = $this->getAutoDescr($mfa_id, $model, $model_id, $typ_id);
                $list .= "
                <li class=\"garage-row\">
                    <div class=\"garage-row__text\">
                        $mfa_cap $model_id_cap <br>
                        <span>$typ_text</span>
                    </div>
                    <div class=\"garage-row__buttons\"> 
                        <button class=\"btn btn-primary btn-sm\" $status_btn $status_disable>$status_cap</button>
                        <button class=\"btn btn-primary btn-sm\" onclick=\"deleteAutoGarage('$id');\">&times;</button>
                    </div>
                </li>";
            }
            $auto_form = $this->getChosenAutoGarage($this->getClient(), $this->getUser());
        }
        $form = str_replace("{garage_list}", $list, $form);
        $form = str_replace("{auto_form}", $auto_form, $form);

        if ($n == 0) {
            $form = $this->getHtmlForm("error/404_garage");
        }
        return $this->replaceLang($form);
    }

    /*
     * add item to garage
     * */
    public function addToGarage($typ_id)
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient();
        $user_id = $this->getUser();
        list($mfa_id, $model, $model_id) = $this->getCarInfo($typ_id);
        $cookie = $this->getSessionID();
        $max_auto = 5;
        $where = ($user_id == 0) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";
        if ($mfa_id > 0 && $model != "" && $model_id > 0 && $typ_id > 0) {
            $count = $this->getGarageAutoCount();
            if ($count <= $max_auto) {
                $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `AUTO_GARAGE` WHERE $where AND `typ_id` = $typ_id;");
                $n = $db->result($r, 0, "count_ids");
                if ($n == 0) {
                    $rs = $db->query("SELECT `id` FROM `AUTO_GARAGE` WHERE $where AND `status` = 1;");
                    $ns = $db->num_rows($rs);
                    for ($i = 1; $i <= $ns; $i++) {
                        $id = $db->result($rs, $i - 1, "id");
                        $db->query("UPDATE `AUTO_GARAGE` SET `status` = 0 WHERE `id` = $id;");
                    }
                    $db->query("INSERT INTO `AUTO_GARAGE` (`client_id`, `user_id`, `cookie_id`, `typ_id`, `status`) VALUES ($client_id, $user_id, '$cookie', $typ_id, 1);");
                    list($mfa_cap, , $model_id_cap, $typ_text) = $this->getAutoDescr($mfa_id, $model, $model_id, $typ_id);
                    setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");
                    $result = $this->replaceLang("{auto_cap} $mfa_cap $model_id_cap $typ_text {garage_added}");
                } else {
                    $result = true;
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /*
     * get amount of garage items
     * */
    public function getGarageAutoCount()
    {
        $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient();
        $user_id = $this->getUser();
        $cookie = $this->getSessionID();
        $where = ($user_id == 0) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";
        $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `AUTO_GARAGE` WHERE $where;");
        $n = $db->result($r, 0, "count_ids");
        return ($n > 0) ? $n : "!";
    }

    /*
     * check user auto
     * */
    public function checkUserGarage($typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient();
        $user_id = $this->getUser();
        $cookie = $this->getSessionID();
        $where = ($user_id == 0) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";
        $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `AUTO_GARAGE` WHERE $where AND `typ_id` = $typ_id;");
        $n = $db->result($r, 0, "count_ids");
        return ($n == 0);
    }

    /*
    * Show Car History
    * */
    public function showAutoHistory()
    {
        $db = DbSingleton::getTokoDb();
        $cookie = $this->getSessionID();
        $user_id = $this->getUser();
        $client_id = $this->getClient();
        $where = ($user_id == 0) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";
        $r = $db->query("SELECT `id`, `typ_id`, `timestamp` FROM `AUTO_HISTORY` WHERE $where GROUP BY `typ_id` ORDER BY `timestamp` DESC LIMIT 10;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $list = "";
            for ($i = 1; $i <= $n; $i++) {
                $id = $db->result($r, $i - 1, "id");
                $typ_id = $db->result($r, $i - 1, "typ_id");
                $list .= "
                <li>
                    <div class=\"container\">
                        <div class=\"row\">
                            <div class=\"col-10\">
                                <a onclick=\"setCookie('auto_typ_id', '$typ_id'); location.reload();\">" . $this->getCarDescription($typ_id) . "</a>
                            </div>
                            <div class=\"col-2 text-right\">
                                <a onclick=\"dropAutoHistory('$id')\">&times;</a>
                            </div>
                        </div>
                    </div>
                </li>";
            }
        } else {
            $list = "{empty_history}";
        }
        $form = $this->getHtmlForm("garage/garage_history");
        $form = str_replace("{garage_history_list}", $list, $form);
        return $this->replaceLang($form);
    }

    /*
     * Delete Car History
     * */
    public function dropAutoHistory($history_id)
    {
        $history_id = $this->getUrlNumber($history_id);
        $db = DbSingleton::getTokoDb();
        if ($history_id == 0) {
            $user_id = $this->getUser();
            $client_id = $this->getClient();
            $cookie = $this->getSessionID();
            $where = ($user_id == 0) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";
        } else {
            $where = "`id` = '$history_id'";
        }
        $db->query("DELETE FROM `AUTO_HISTORY` WHERE $where;");
        return true;
    }

    public function showMfaCacheGroups($mfa_id, $model, $mfa_link, $model_link)
    {
        $mfa_id = $this->getUrlNumber($mfa_id);
        $model = $this->getUrlString($model);
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $where = "1";
        if ($model != "") {
            $where = "`model` = '$model'";
        }

        $r = $dbc->query("SELECT `group_id` FROM `EX_TABLE_TREE_AVAILABLE_MFA` WHERE `mfa_id` = $mfa_id AND $where GROUP BY `group_id`;");
        $n = $dbc->num_rows($r);
        $groups = [];
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $dbc->result($r, $i - 1, "group_id");
            $groups[] = $group_id;
        }
        $r2 = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_GROUP_EXIST` WHERE `STATUS_AUTO` = 1 OR `STATUS_AUTO` = 2;");
        $n2 = $db->num_rows($r2);
        $groups2 = [];
        for ($i = 1; $i <= $n2; $i++) {
            $group_id = $db->result($r2, $i - 1, "GROUP_ID");
            $groups2[] = $group_id;
        }
        $arr = array_merge($groups, $groups2);
        $arr = array_unique($arr);

        $hh = [];
        if (!empty($arr)) {
            $groups_str = implode(",", $arr);
            $r = $db->query("SELECT `HEAD_ID`, `CAT_ID`, `GROUP_ID` FROM `T2_TREE_HCG_EXIST` WHERE `GROUP_ID` IN ($groups_str);");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $head_id = $db->result($r, $i - 1, "HEAD_ID");
                $cat_id = $db->result($r, $i - 1, "CAT_ID");
                $group_id = $db->result($r, $i - 1, "GROUP_ID");
                $hh[$head_id][$cat_id][] = $group_id;
            }
        }

        $heads = [];
        $cats = [];
        $groups = [];

        foreach ($hh as $head_id => $cc) {
            $heads[] = $head_id;
            if (!empty($cc)) {
                $cats[] = 0;
            }
            foreach ($cc as $cat_id => $gg) {
                $cats[] = $cat_id;
                foreach ($gg as $group_id) {
                    $groups[] = $group_id;
                }
            }
        }

        $heads = array_unique($heads);
        $cats = array_unique($cats);
        $groups = array_unique($groups);

        $catalog = new CatalogueClass();

        if (empty($groups)) {
            $form = "";
        } else {
            $form = $catalog->getCatalogColList($mfa_link, $model_link, $heads, $cats, $groups);
        }

        return $form;
    }


    /*
     * get cars seo content
     * */
    public function getCarsSeoContent($mfa_link, $mod_link = "")
    {
        $form = $this->getHtmlForm("cars/seo_content");
        $mfa_id = $this->getMfaLink($mfa_link);
        if ($mfa_link == "") {
            $mfa_id = "";
        }
        $model = $this->getModLink($mod_link);
        if ($model == "") {
            $form = str_replace("{seo_list}", $this->getCarsModelList($mfa_id) . $this->showMfaCacheGroups($mfa_id, $model, $mfa_link, $mod_link), $form);
        } else {
            $form = str_replace("{seo_list}", $this->showMfaCacheGroups($mfa_id, $model, $mfa_link, $mod_link), $form);
        }
        $form = str_replace("{seo_header}", "", $form);
        return $form;
    }

    /*
     * get MAIN PAGE cars list
     * */
    public function getAutoMfaModelList()
    {
        $db = DbSingleton::getTokoDb();
        $mas = [];
        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_BRAND` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = $db->result($r, $i - 1, "MFA_ID");
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");
            $mas[$mfa_brand] = compact("mfa_id", "mfa_link");
        }

        $list = "";
        foreach ($mas as $mfa_brand => $values) {
            $mfa_id = $values["mfa_id"];
            $mfa_link = $values["mfa_link"];
            $list .= "
            <div>
                <a href=\"" . $this->getSiteLink() . "cars/$mfa_link/\">{details_on_cap} $mfa_brand</a>
            </div>";
            $list .= "
            <div class=\"seo-auto-list\">";
            $r = $db->query("SELECT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id GROUP BY `Model`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model = $db->result($r, $i - 1, "Model");
                $model_link = $db->result($r, $i - 1, "Model_Link");
                $list .= "
                <div class=\"seo-auto-list__item\">
                    <a href=\"" . $this->getSiteLink() . "cars/$mfa_link/$model_link/\">$mfa_brand $model</a>
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
     * get CARS seo list
     * */
    public function getCarsModelList($mfa_id_sel = "")
    {
        $db = DbSingleton::getTokoDb();

        $list = "";
        $where = ($mfa_id_sel != "") ? "AND `MFA_ID` = $mfa_id_sel" : "";
        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 $where ORDER BY `MFA_BRAND`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id = $db->result($r, $i - 1, "MFA_ID") + 0;
            $mfa_brand = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link = $db->result($r, $i - 1, "MFA_BRAND_LINK");

            if ($mfa_id_sel == "") {
                $list .= "
                <div>
                    <a href=\"" . $this->getSiteLink() . "cars/$mfa_link/\">{details_on_cap} $mfa_brand</a>
                </div>";
            } else {
                $list = "
                <div>
                    {details_on_cap} $mfa_brand
                </div>";
            }

            $list .= "
            <div class=\"seo-auto-list seo_details\">";
            $r2 = $db->query("SELECT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 GROUP BY `Model`;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $mod = $db->result($r2, $i2 - 1, "Model");
                $mod_link = $db->result($r2, $i2 - 1, "Model_Link");
                $list .= "
                <a class=\"seo-li\" href=\"" . $this->getSiteLink() . "cars/$mfa_link/$mod_link/\">
                    <span>$mfa_brand $mod</span>
                </a>";
            }
            $list .= "</div>";
        }

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        $form = str_replace("{seo_auto_title}", "", $form);
        $form = str_replace("{seo_auto_list}", $list, $form);

        return $form;
    }

    /*
     * get CARS seo meta tags
     * */
    public function getCarsMetaTags($mfa_id, $model, $h1_text)
    {
        $catalog = new CatalogueClass();
        $url_text = $this->getSiteLink() . $this->cars_link . "/";
        $car_pict = "";
        $imgData = $this->getAutoIMG($mfa_id, $model);
        if ($mfa_id > 0) {
            $mfa_link = $catalog->getManufactureLink($mfa_id);
            $url_text .= "$mfa_link/";
            $car_pict = $imgData["mfa_image"];
            $car_pict = "https://toko.ua/uploads/images/manufacturers/$car_pict";
            if ($model != "") {
                $model_link = $catalog->getModelLink($model);
                $url_text .= "$model_link/";
                $car_pict = $imgData["model_image"];
                $car_pict = "https://toko.ua/uploads/images/models/$car_pict";
            }
        }
        $form = $this->getHtmlForm("article/social");
        $form = str_replace("{h1_meta_tag}", $h1_text, $form);
        $form = str_replace("{url_meta_tag}", $url_text, $form);
        $form = str_replace("{main_image_cap}", $car_pict, $form);
        return $form;
    }

    public function getCarsTitle($mfa_id, $model)
    {
        $catalog = new CatalogueClass();
        $mfa_link = $catalog->getManufactureLink($mfa_id);
        $model_link = $catalog->getModelLink($model);
        list($mfa_text, $model_text) = $this->getAutoDescrLink($mfa_link, $model_link);
        $translit = $this->getCarManufTranslit($mfa_id, $model);
        return ($mfa_text == "")
            ? "{spare_parts_catalog_cap}"
            : $this->replaceLang("{details_on_cap} $mfa_text $model_text $translit");
    }

}