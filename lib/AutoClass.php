<?php

class AutoClass extends CatalogueClass
{

    public $history_limit_on_page = 10;

    /*
     * get text translate of selected car
     * */
    public function getCarManufactureTranslate($mfa_id, $model = "", $status = 0): string
    {
        $lang_id = $this->getLanguage();
        $mfa_id = $this->getUrlNumber($mfa_id);
        $model = $this->getUrlString($model); 
        $model_translate = "";

        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_BRAND_TRANSLIT_RU`, `MFA_BRAND_TRANSLIT_UA` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        $mfa_translate = $db->result($r, 0, "MFA_BRAND_TRANSLIT_RU");
        $mfa_translate_ua = $db->result($r, 0, "MFA_BRAND_TRANSLIT_UA");

        if ($lang_id === 2) {
            $mfa_translate = $mfa_translate_ua;
        }

        $text = (!empty($mfa_translate)) ? "($mfa_translate)" : "";

        if (!empty($model)) {
            $r = $db->query("SELECT `Model_TRANSLIT_RU`, `Model_TRANSLIT_UA` FROM `T_models` WHERE `Model` = '$model' AND `Model_TRANSLIT_RU` != '' LIMIT 1;");
            $model_translate = $db->result($r, 0, "Model_TRANSLIT_RU");
            $model_translate_ua = $db->result($r, 0, "Model_TRANSLIT_UA");

            if ($lang_id === 2) {
                $model_translate = $model_translate_ua;
            }

            $text = (!empty($model_translate)) ? "($mfa_translate $model_translate)" : $text;
        }

        if ($status === 1) {
            $text = str_replace("(", "", $text);
            $text = str_replace(")", "", $text);
        }
        if ($status === 2) {
            $text = "$mfa_translate $model_translate";
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
        $model_id   = $db->result($r, 0, "TYP_MOD_ID") + 0;
        $typ_cap    = $db->result($r, 0, "TYP_TEXT");

        $r = $db->query("SELECT `MOD_MFA_ID`, `TEX_TEXT` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
        $mfa_id     = $db->result($r, 0, "MOD_MFA_ID") + 0;
        $mod_cap    = $db->result($r, 0, "TEX_TEXT");

        $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
        $mfa_cap    = $db->result($r, 0, "MFA_BRAND");
        $car_cap    = "$mfa_cap $mod_cap $typ_cap";

        if (empty($typ_id)) {
            $car_cap = $this->replaceLang("{choose_spare}");
        }

        return $car_cap;
    }

    /*
     * get car mfa, model, model_id ids
     * from TYP ID
     * */
    public function getCarInfo($typ_id): array
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `TYP_MOD_ID` FROM `T_types` WHERE `TYP_ID` = $typ_id LIMIT 1;");
        $model_id = (int)$db->result($r, 0, "TYP_MOD_ID");

        $r = $db->query("SELECT `MOD_MFA_ID`, `Model` FROM `T_models` WHERE `MOD_ID` = $model_id LIMIT 1;");
        $mfa_id = (int)$db->result($r, 0, "MOD_MFA_ID");
        $model  = $db->result($r, 0, "Model");

        return array($mfa_id, $model, $model_id);
    }

    /*
     * get car mfa, model, model_id, typ text
     * from IDs
     * */
    public function getAutoDescription($mfa_id, $model = "", $model_id = 0, $typ_id = 0): array
    {
        $mfa_id     = $this->getUrlNumber($mfa_id);
        $model      = $this->getUrlString($model);
        $model_id   = $this->getUrlNumber($model_id);
        $typ_id     = $this->getUrlNumber($typ_id);

        $db = DbSingleton::getTokoDb();
        $mfa_text = $model_text = $model_id_text = $typ_text = "";

        if ($mfa_id > 0) {
            $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
            $mfa_text = $db->result($r, 0, "MFA_BRAND");
        }

        if ($model !== "") {
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
    public function getAutoDescriptionLink($mfa_link, $model_link): array
    {
        $mfa_link   = $this->getUrlString($mfa_link);
        $model_link = $this->getUrlString($model_link);

        $db = DbSingleton::getTokoDb();
        $mfa_brand = $model = "";

        if (!empty($mfa_link)) {
            $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' LIMIT 1;");
            $mfa_brand = $db->result($r, 0, "MFA_BRAND");
        }

        if (!empty($model_link)) {
            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link` = '$model_link' LIMIT 1;");
            $model = $db->result($r, 0, "Model");
        }

        return array($mfa_brand, $model);
    }

    /*
     * get car mfa & model ids
     * from LINK
     * */
    public function getAutoIdsLink($mfa_link, $model_link): array
    {
        $mfa_link   = $this->getUrlString($mfa_link);
        $model_link = $this->getUrlString($model_link);

        $db = DbSingleton::getTokoDb();
        $mfa_id = $model = "";

        if (!empty($mfa_link)) {
            $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' LIMIT 1;");
            $mfa_id = (int)$db->result($r, 0, "MFA_ID");
        }

        if (!empty($model_link)) {
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
    public function getMfaLink($mfa_link): int
    {
        $mfa_link = $this->getUrlString($mfa_link);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK` = '$mfa_link' LIMIT 1;");
        return (int)$db->result($r, 0, "MFA_ID");
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
        $r = $db->query("SELECT `TEX_TEXT` FROM `T_models` WHERE `MOD_ID` = $mod_id LIMIT 1;");
        return $db->result($r, 0, "TEX_TEXT");
    }

    /*
     * get car model name
     * from LINK
     * */
    public function getModIdLink($mod_id_link): int
    {
        $mod_id_link = $this->getUrlString($mod_id_link);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MOD_ID` FROM `T_models` WHERE `TEX_TEXT_link` = '$mod_id_link' LIMIT 1;");
        return (int)$db->result($r, 0, "MOD_ID");
    }

    /*
     * get car mfa, model, model_id images
     * from ID
     * */
    public function getAutoIMG($mfa_id, $model, $model_id = 0): array
    {
        $mfa_id     = $this->getUrlNumber($mfa_id);
        $model      = $this->getUrlString($model);
        $model_id   = $this->getUrlNumber($model_id);

        $db = DbSingleton::getTokoDb();
        $mfa_image = $model_image = $model_id_image = "";

        if ($mfa_id > 0) {
            $r = $db->query("SELECT `LOGO` FROM `T_manufacturers` WHERE `MFA_ID` = $mfa_id LIMIT 1;");
            $mfa_image = $db->result($r, 0, "LOGO");
        }

        if ($model !== "") {
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
    public function getGroupInfo($typ_id): string
    {
        $typ_id = $this->getUrlNumber($typ_id);
        $db = DbSingleton::getTokoDb();
        $text = "";

        $r = $db->query("SELECT `TYP_KW_FROM`, `TYP_HP_FROM`, `TYP_CCM`, `FUEL_ID`, `ENG_Cod`, `TYP_MMT_TEXT`,
        IF (TYP_PCON_START = 0, '', TYP_PCON_START) AS TYP_PCON_START,
        IF (TYP_PCON_END = 0, '', TYP_PCON_END) AS TYP_PCON_END
        FROM `T_types` WHERE `TYP_ID` = $typ_id AND `ACTIVE` = 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $kw_from    = $db->result($r, 0, "TYP_KW_FROM");
            $hp_from    = $db->result($r, 0, "TYP_HP_FROM");
            $ccm        = $db->result($r, 0, "TYP_CCM");
            $fuel       = $db->result($r, 0, "FUEL_ID");
            $eng_cod    = $db->result($r, 0, "ENG_Cod");
            $full_name  = $db->result($r, 0, "TYP_MMT_TEXT");
            $d_start    = $db->result($r, 0, "TYP_PCON_START");
            $d_end      = $db->result($r, 0, "TYP_PCON_END");
            $fuel       = $this->getFuelName($fuel);

            if (mb_strlen($d_start) === 6) {
                $d_start = substr($d_start, 0, 4) . "." . substr($d_start, 4, 2);
            }

            if (mb_strlen($d_end) === 6) {
                $d_end = substr($d_end, 0, 4) . "." . substr($d_end, 4, 2);
            }

            $text = "$full_name ($d_start - $d_end)<br>$fuel, $hp_from {horse_power_cap} / $kw_from {kilo_wat_cap}, $ccm cm3, $eng_cod";
        }

        return $text;
    }

    /*
     * get garage chosen form
     * */
    public function getChosenAutoGarage($client_id, $user_id, $sel_type_id = 0)
    {
        $db = DbSingleton::getTokoDb();
        $cookie_id = $this->getSessionID();
        $where = (empty($user_id)) ? "`client_id` = $client_id AND `cookie_id` = '$cookie_id'" : "`client_id` = $client_id AND `user_id` = $user_id";

        $r = $db->query("SELECT `typ_id` FROM `AUTO_GARAGE` WHERE $where AND `status` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $typ_id     = $db->result($r, 0, "typ_id");
            $typ_text   = $this->getGroupInfo($typ_id);

            if ($sel_type_id > 0) {
                $typ_id = $sel_type_id;
            }

            list($mfa_id, $model, $model_id) = $this->getCarInfo($typ_id);
            list($mfa_cap, , $model_id_cap) = $this->getAutoDescription($mfa_id, $model, $model_id, $typ_id);
            $model_id_image = $this->getAutoIMG($mfa_id, $model, $model_id)["model_id_image"];

            $auto_form = $this->getHtmlForm("garage/garage_selected");
            $auto_form = str_replace(array("{manufacture_cap}", "{model_id_cap}", "{models_img}", "{typ_text}"), array($mfa_cap, $model_id_cap, $model_id_image, $typ_text), $auto_form);
        } else {
            $auto_form = "{choose_auto_first}";
        }

        return $this->replaceLang($auto_form);
    }

    /*
     * delete garage item
     * */
    public function deleteAutoGarage($auto_id): bool
    {
        $auto_id = $this->getUrlNumber($auto_id);
        $db = DbSingleton::getTokoDb();
        $db->query("DELETE FROM `AUTO_GARAGE` WHERE `id` = $auto_id;");

        $client_id  = $this->getClient();
        $user_id    = $this->getUser();
        $cookie_id  = $this->getSessionID();
        $where      = (empty($user_id)) ? "`client_id` = $client_id AND `cookie_id` = '$cookie_id'" : "`client_id` = $client_id AND `user_id` = $user_id";

        $r = $db->query("SELECT `id`, `typ_id` FROM `AUTO_GARAGE` WHERE $where ORDER BY `timestamp` DESC LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n === 0) {
            setcookie("auto_typ_id", "", time() - 3600, "/");
            return false;
        }

        $id     = (int)$db->result($r, 0, "id");
        $typ_id = $db->result($r, 0, "typ_id");
        $db->query("UPDATE `AUTO_GARAGE` SET `status` = 1 WHERE `id` = $id LIMIT 1;");
        setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");

        return true;
    }

    /*
     * show garage form
     * */
    public function showGarageForm()
    {
        $db = DbSingleton::getTokoDb();

        $form       = $this->getHtmlForm("garage/garage");
        $list       = $auto_form = "";
        $client_id  = $this->getClient();
        $user_id    = $this->getUser();
        $cookie_id  = $this->getSessionID();
        $where      = (empty($user_id)) ? "`client_id` = $client_id AND `cookie_id` = '$cookie_id'" : "`client_id` = $client_id AND `user_id` = $user_id";

        $r = $db->query("SELECT `id`, `typ_id` FROM `AUTO_GARAGE` WHERE $where;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $id     = (int)$db->result($r, $i - 1, "id");
                $typ_id = (int)$db->result($r, $i - 1, "typ_id");

                list($mfa_id, $model, $model_id) = $this->getCarInfo($typ_id);

                if ($typ_id !== $this->getCookieAuto()) {
                    $status_cap     = "{select_cap}";
                    $status_disable = "";
                    $status_btn     = "onclick='showGarageForm($typ_id);'";
                } else {
                    $status_cap     = "{unselect_cap}";
                    $status_disable = "disabled";
                    $status_btn     = "";
                }
                list($mfa_cap, , $model_id_cap, $typ_text) = $this->getAutoDescription($mfa_id, $model, $model_id, $typ_id);

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

            $sel_typ_id = 0;
            if ($this->getCookieAuto() > 0) {
                $sel_typ_id = $this->getCookieAuto();
            }

            $auto_form = $this->getChosenAutoGarage($this->getClient(), $this->getUser(), $sel_typ_id);
        }

        $products = new ProductsClass();
        $form = str_replace(array("{garage_list}", "{auto_form}", "{cars_garage_content}"), array($list, $auto_form, $products->showCarsForm()), $form);

        if ($n === 0) {
            $form = $this->getHtmlForm("error/404_garage");
        }

        return $this->replaceLang($form);
    }

    /*
     * add item to garage
     * */
    public function addToGarage($typ_id): array
    {
        $db = DbSingleton::getTokoDb();

        $text       = "";
        $typ_id     = $this->getUrlNumber($typ_id);
        $client_id  = $this->getClient();
        $user_id    = $this->getUser();
        $cookie_id  = $this->getSessionID();
        $max_auto   = 5;
        $where      = (empty($user_id)) ? "`client_id` = $client_id AND `cookie_id` = '$cookie_id'" : "`client_id` = $client_id AND `user_id` = $user_id";

        list($mfa_id, $model, $model_id) = $this->getCarInfo($typ_id);

        if ($mfa_id > 0 && $model !== "" && $model_id > 0 && $typ_id > 0) {
            $count = $this->getGarageAutoCount();

            if ($count <= $max_auto) {
                $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `AUTO_GARAGE` WHERE $where AND `typ_id` = $typ_id;");
                $n = (int)$db->result($r, 0, "count_ids");

                if ($n === 0) {
                    $rs = $db->query("SELECT `id` FROM `AUTO_GARAGE` WHERE $where AND `status` = 1;");
                    $ns = $db->num_rows($rs);
                    for ($i = 1; $i <= $ns; $i++) {
                        $id = $db->result($rs, $i - 1, "id");
                        $db->query("UPDATE `AUTO_GARAGE` SET `status` = 0 WHERE `id` = $id;");
                    }

                    $db->query("INSERT INTO `AUTO_GARAGE` (`client_id`, `user_id`, `cookie_id`, `typ_id`, `status`) VALUES ($client_id, $user_id, '$cookie_id', $typ_id, 1);");
                    list($mfa_cap, , $model_id_cap, $typ_text) = $this->getAutoDescription($mfa_id, $model, $model_id, $typ_id);
                    setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");
                    $result = true;
                    $text   = $this->replaceLang("{auto_cap} $mfa_cap $model_id_cap $typ_text {garage_added}");
                } else {
                    $result = true;
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return array($result, $text);
    }

    /*
     * get amount of garage items
     * */
    public function getGarageAutoCount()
    {
        $db = DbSingleton::getTokoDb();

        $client_id  = $this->getClient();
        $user_id    = $this->getUser();
        $cookie_id  = $this->getSessionID();
        $where      = (empty($user_id)) ? "`client_id` = $client_id AND `cookie_id` = '$cookie_id'" : "`client_id` = $client_id AND `user_id` = $user_id";

        $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `AUTO_GARAGE` WHERE $where;");
        $n = (int)$db->result($r, 0, "count_ids");

        return ($n > 0) ? $n : "!";
    }

    /*
     * check user auto
     * */
    public function checkUserGarage($typ_id): bool
    {
        $db = DbSingleton::getTokoDb();

        $client_id  = $this->getClient();
        $user_id    = $this->getUser();
        $cookie     = $this->getSessionID();
        $where      = (empty($user_id)) ? "`client_id` = $client_id AND `cookie_id` = '$cookie'" : "`client_id` = $client_id AND `user_id` = $user_id";

        $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `AUTO_GARAGE` WHERE $where AND `typ_id` = $typ_id;");
        $n = (int)$db->result($r, 0, "count_ids");

        return ($n === 0);
    }

    /*
    * Show Car History
    * */
    public function showAutoHistory()
    {
        $db = DbSingleton::getTokoDb();

        $user_id    = $this->getUser();
        $client_id  = $this->getClient();
        $cookie     = $this->getSessionID();
        $where      = (empty($user_id)) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";

        $r = $db->query("SELECT DISTINCT `id`, `typ_id`, `timestamp` 
        FROM `AUTO_HISTORY` 
        WHERE $where 
        ORDER BY `timestamp` DESC LIMIT $this->history_limit_on_page");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $list = "";
            for ($i = 1; $i <= $n; $i++) {
                $id     = $db->result($r, $i - 1, "id");
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

    public function addGarageHistory($typ_id): bool
    {
        $db = DbSingleton::getTokoDb();
        $typ_id = $this->getUrlNumber($typ_id);

        if ($typ_id > 0) {
            $client_id = $this->getClient();
            $user_id = $this->getUser();
            $cookie_id  = $this->getSessionID();

            $db->query("INSERT INTO `AUTO_HISTORY` (`client_id`, `client_user_id`, `cookie_id`, `typ_id`) VALUES ($client_id, $user_id, '$cookie_id', $typ_id);");
        }

        return true;
    }

    /*
     * Delete Car History
     * */
    public function dropAutoHistory($history_id): bool
    {
        $history_id = $this->getUrlNumber($history_id);
        $db = DbSingleton::getTokoDb();

        if (empty($history_id)) {
            $user_id    = $this->getUser();
            $client_id  = $this->getClient();
            $cookie     = $this->getSessionID();
            $where      = (empty($user_id)) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";
        } else {
            $where = "`id` = '$history_id'";
        }

        $db->query("DELETE FROM `AUTO_HISTORY` WHERE $where;");

        return true;
    }

    public function getCatalogCacheCol($mfa_id, $model, $mfa_link, $model_link): string
    {
        $dbc = DbSingleton::getTokoCacheDb();

        $where_mfa = "1";
        if (($mfa_link !== "") && $mfa_id > 0) {
            $where_mfa = " `mfa_id` = $mfa_id";
            if ($model !== "") {
                $where_mfa .= " AND `model` = '$model'";
            }
        }

        $groups = [];
        $r = $dbc->query("SELECT DISTINCT `group_id` FROM `EX_TABLE_AVAILABLE_MFA` WHERE $where_mfa ;");
        $n = $dbc->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $group_id = $dbc->result($r, $i - 1, "group_id");
            $groups[] = $group_id;
        }

        $arr = $this->getTreeHCGList($groups);

        return $this->getCatalogCacheColShow($arr, $mfa_link, $model_link);
    }

    public function getTreeHCGList($groups): array
    {
        $db = DbSingleton::getTokoDb();
        $groups_str = implode(",", $groups);
        if (empty($groups_str)) {
            $groups_str = 0;
        }
        $arr = [];
        $r = $db->query("SELECT `HEAD_ID`, `CAT_ID`, `GROUP_ID`, `POPULAR` FROM `T2_TREE_HCG_EXIST` WHERE `GROUP_ID` IN ($groups_str);");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $head_id    = $db->result($r, $i - 1, "HEAD_ID");
            $cat_id     = $db->result($r, $i - 1, "CAT_ID");
            $group_id   = $db->result($r, $i - 1, "GROUP_ID");
            $popular    = (int)$db->result($r, $i - 1, "POPULAR");
            $head_stat  = $this->getHeadRowStatus($head_id);

            if ($head_stat) {
                $arr[$head_id][$cat_id][] = $group_id;
                if ($popular === 1) {
                    $arr[$head_id][0][] = $group_id;
                }
            }
        }

        return $arr;
    }

    public function getCatalogCacheColShow($arr, $mfa_link, $model_link, $brand_id = 0): string
    {
        $list = "";
        $no_photo = $this->noPhoto;

        $brand_name = "";
        if ($brand_id > 0) {
            $brand_name = $this->getBrandName($brand_id);
        }

        foreach ($arr as $head_id => $cats) {
            $head_name  = $this->getHeadRowName($head_id);
            $head_img   = $this->getHeadRowImage($head_id);
            $head_text  = $this->getHeadRowText($head_id);
            $head_link  = $this->getHeadRowLink($head_id);

            $list .= "
            <div class=\"tree-heads__item\">
                <input type=\"checkbox\" id=\"toggle-head-$head_id\">
                <label for=\"toggle-head-$head_id\">
                    <div id=\"tree_head-$head_id\" class=\"tree-heads__item-header\">
                        <div class=\"tree-heads__item-text\">
                            <div class=\"tree-heads__item-title\">
                                $head_name $brand_name
                            </div>
                            <div class=\"tree-heads__item-descr\">
                                $head_text
                            </div>
                        </div>
                        <div class=\"tree-heads__item-image\">
                            <img data-src=\"/uploads/images/group_tree_head/$head_img\" class=\"lazy\" alt=\"$head_name\" src=\"$no_photo\">
                        </div>
                    </div>
                </label>
                <div class=\"tree-cat\" style=\"display: none;\">";

            ksort($cats);

            foreach ($cats as $cat_id => $groups) {
                $catData    = $this->getCatRowData($cat_id);
                $cat_name   = $catData["cat_name"];
                $cat_link   = $catData["cat_link"];

                $link = "
                <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$head_link/$cat_link/\">$cat_name $brand_name</a>";

                if (empty($cat_id)) {
                    $link = "
                    <span>
                        <span style=\"color: #f44438; margin-right: 5px;\">&bull;</span>
                        $cat_name $brand_name 
                    </span>";
                }

                $list .= "
                <div class=\"tree-cat__item\">
                    <div class=\"tree-cat__item-title\">
                        $link
                    </div>
                    <div class=\"tree-group\">";

                foreach ($groups as $group_id) {
                    $group_name = $this->getGroupRowText($group_id);
                    $group_link = $this->getGroupRowLink($group_id);
                    $group_img  = $this->getGroupRowImage($group_id);
                    $status_typ = $this->getGroupRowStatusAuto($group_id);

                    $link = "";

                    if ($brand_id > 0) {
                        $brand_link = $this->getBrandLink($brand_id);
                        $link .= "brandy=$brand_link/";
                    }

                    if (($status_typ !== 2) && $mfa_link !== "") {
                        $link = "auto/";
                        if ($brand_id > 0) {
                            $brand_link = $this->getBrandLink($brand_id);
                            $link = "brandy=$brand_link/";
                        }
                        $link .= "$mfa_link/";
                        if ($model_link !== "") {
                            $link .= "$model_link/";
                        }
                    }

                    $list .= "
                    <a href=\"" . $this->getSiteLink() . "$this->catalog_link/$group_link/$link\" class=\"tree-group__item\">
                        <div class=\"tree-group__item-image\">
                            <img data-src=\"/images/tree-group/$group_img\" class=\"lazy\" alt=\"$group_name $brand_name\">
                        </div>
                        <div class=\"tree-group__item-text\">
                            <span>$group_name $brand_name</span>
                        </div>
                    </a>";
                }

                $list .= "
                </div></div>";
            }

            $list .= "
            </div></div>";
        }

        return $list;
    }

    /*
     * get cars seo content
     * */
    public function getCarsSeoContent($mfa_link, $mod_link = "")
    {
        $form = "";
        $mfa_id = $this->getMfaLink($mfa_link);
        $mfa_id = (empty($mfa_link)) ? "" : $mfa_id;
        $model  = $this->getModLink($mod_link);
        $group_list = $this->getCatalogCacheCol($mfa_id, $model, $mfa_link, $mod_link);

        $mfa_list = $group_list;
        if (empty($model)) {
            $mfa_list = $this->getCarsModelList($mfa_id) . $group_list;
        }

        if (!empty($mfa_list)) {
            $form = $this->getHtmlForm("cars/seo_content");
            $form = str_replace(array("{seo_list}", "{seo_header}"), array($mfa_list, ""), $form);
        }

        return $form;
    }

    /*
     * get MAIN PAGE cars list
     * */
    public function getAutoMfaModelList()
    {
        $db = DbSingleton::getTokoDb();
        $mas = [];
        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_BRAND`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id     = $db->result($r, $i - 1, "MFA_ID");
            $mfa_brand  = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link   = $db->result($r, $i - 1, "MFA_BRAND_LINK");

            $mas[$mfa_brand] = compact("mfa_id", "mfa_link");
        }

        $list = "";
        foreach ($mas as $mfa_brand => $values) {
            $mfa_id     = $values["mfa_id"];
            $mfa_link   = $values["mfa_link"];

            $list .= "
            <div>
                <a href=\"" . $this->getSiteLink() . "cars/$mfa_link/\">{details_on_cap} $mfa_brand</a>
            </div>
            <div class=\"seo-auto-list\">";

            $r = $db->query("SELECT DISTINCT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model      = $db->result($r, $i - 1, "Model");
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
        
        return str_replace(
            array("{seo_auto_title}", "{seo_auto_list}", "{seo_auto_letters}"), 
            array("", $list, ""), 
        $form);
    }

    /*
     * get CARS seo list
     * */
    public function getCarsModelList($mfa_id_sel = "")
    {
        $db = DbSingleton::getTokoDb();

        $list = "";
        $where = (!empty($mfa_id_sel)) ? "AND `MFA_ID` = $mfa_id_sel" : "";

        $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND`, `MFA_BRAND_LINK` FROM `T_manufacturers` WHERE `ACTIVE` = 1 $where ORDER BY `MFA_BRAND`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $mfa_id     = $db->result($r, $i - 1, "MFA_ID") + 0;
            $mfa_brand  = $db->result($r, $i - 1, "MFA_BRAND");
            $mfa_link   = $db->result($r, $i - 1, "MFA_BRAND_LINK");

            if (empty($mfa_id_sel)) {
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

            $r2 = $db->query("SELECT DISTINCT `Model`, `Model_Link` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1;");
            $n2 = $db->num_rows($r2);
            for ($i2 = 1; $i2 <= $n2; $i2++) {
                $model      = $db->result($r2, $i2 - 1, "Model");
                $mod_link   = $db->result($r2, $i2 - 1, "Model_Link");

                $list .= "
                <a class=\"seo-li\" href=\"" . $this->getSiteLink() . "cars/$mfa_link/$mod_link/\">
                    <span>$mfa_brand $model</span>
                </a>";
            }

            $list .= "
            </div>";
        }

        $form = $this->getHtmlForm("catalog_exist/seo_content_auto");
        
        return str_replace(
            array("{seo_auto_title}", "{seo_auto_list}", "{seo_auto_letters}"), 
            array("", $list, ""), 
        $form);
    }

    /*
     * get CARS seo meta tags
     * */
    public function getCarsMetaTags($mfa_id, $model, $h1_text)
    {
        $catalog = new CatalogueClass();

        $url_text = $this->getSiteLink() . $this->cars_link . "/";
        $car_pict = "";

        if ($mfa_id > 0) {
            $car_pict   = "https://toko.ua/uploads/images/manufacturers/$car_pict";
            $mfa_link   = $catalog->getManufactureLink($mfa_id);
            $url_text   .= "$mfa_link/";

            if (!empty($model)) {
                $car_pict   = "https://toko.ua/uploads/images/models/$car_pict";
                $model_link = $catalog->getModelLink($model);
                $url_text   .= "$model_link/";
            }
        }

        $form = $this->getHtmlForm("article/social");
        
        return str_replace(
            array("{h1_meta_tag}", "{url_meta_tag}", "{main_image_cap}"), 
            array($h1_text, $url_text, $car_pict), 
        $form);
    }

    public function getCarsTitle($mfa_id, $model)
    {
        $catalog = new CatalogueClass();

        $mfa_link   = $catalog->getManufactureLink($mfa_id);
        $model_link = $catalog->getModelLink($model);

        list($mfa_text, $model_text) = $this->getAutoDescriptionLink($mfa_link, $model_link);

        return (empty($mfa_text))
            ? "{site_cars_h1}"
            : $this->replaceLang("{details_on_cap} $mfa_text $model_text " . $this->getCarManufactureTranslate($mfa_id, $model));
    }

}