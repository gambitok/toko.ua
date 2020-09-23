<?php

class AutoClass extends CatalogueClass
{

    use Helper;
    use Variables;

    function getStrNewLink($str_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_LINK` FROM `T2_GROUP_TREE_STR` WHERE `STR_ID`='$str_id' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT `TEX_LINK` FROM `T2_GROUP_TREE` WHERE `STR_ID`='$str_id' LIMIT 1;");
        }
        $str_text = $db->result($r, 0, "TEX_LINK");
        return $str_text;
    }

    function getStrNewLinkStr($str_link) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `STR_ID` FROM `T2_GROUP_TREE_STR` WHERE `TEX_LINK`='$str_link' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT `STR_ID` FROM `T2_GROUP_TREE` WHERE `TEX_LINK`='$str_link' LIMIT 1;");
        }
        $str_id = $db->result($r, 0, "STR_ID");
        return $str_id;
    }

    function getHeadNewLinkStr($head_link) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_GROUP_TREE_HEAD` WHERE `TEX_LINK`='$head_link' LIMIT 1;");
        $head_id = $db->result($r, 0, "HEAD_ID");
        return $head_id;
    }

    function getCatNewLinkStr($head_id, $cat_link) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `CAT_ID` FROM `T2_GROUP_TREE_CATEGORY` WHERE `TEX_LINK`='$cat_link' AND `HEAD_ID`='$head_id' LIMIT 1;");
        $cat_id = $db->result($r, 0, "CAT_ID");
        return $cat_id;
    }

    function getHeadStr($str_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `HEAD_ID` FROM `T2_GROUP_TREE_STR` WHERE `STR_ID`='$str_id' LIMIT 1;");  $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT `HEAD_ID` FROM `T2_GROUP_TREE` WHERE `STR_ID`='$str_id' LIMIT 1;");
        }
        $head_id = $db->result($r, 0, "HEAD_ID");
        return $head_id;
    }

    function getCarLink($typ_id, $str_id) {
        $prefix = $this->getLangPrefix();
        if ($typ_id>0 && $str_id>0) {
            list($mfa, $model) = $this->getCarDescriptionAll($typ_id);
            $str_text = $this->getStrNewLink($str_id);
            $link = "https://toko.ua$prefix/catalog/$str_text/$mfa/$model/";
        } else {
            $str_text = $this->getStrNewLink($str_id);
            $link = "https://toko.ua$prefix/catalog/$str_text/";
        }
        return $link;
    }

    function getCarDescriptionAll($typ_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' LIMIT 1;");
        $mod_id = $db->result($r, 0, "TYP_MOD_ID");
        $r = $db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        $mfa_id = $db->result($r, 0, "MOD_MFA_ID");
        $mod_link = $db->result($r, 0, "Model_Link");
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
        $mfa_link = $db->result($r, 0, "MFA_BRAND_LINK");
        return array($mfa_link, $mod_link);
    }

    function getCarDescription($typ_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' LIMIT 1;");
        $mod_id = $db->result($r, 0, "TYP_MOD_ID");
        $typ_cap = $db->result($r, 0, "TYP_TEXT");
        $r = $db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        $mfa_id = $db->result($r, 0, "MOD_MFA_ID");
        $mod_cap = $db->result($r, 0, "TEX_TEXT");
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
        $mfa_cap = $db->result($r, 0, "MFA_BRAND");
        $car_cap = "$mfa_cap $mod_cap $typ_cap";
        if ($typ_id=="") $car_cap = $this->replaceLang("{choose_spare}");
        return $car_cap;
    }

    function getCarInfo($typ_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' LIMIT 1;");
        $mod_id = $db->result($r,0,"TYP_MOD_ID");
        $r = $db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        $mfa_id = $db->result($r,0,"MOD_MFA_ID");
        $model = $db->result($r,0,"Model");
        return array($mfa_id, $model, $mod_id);
    }

    function getCookieCarInfo($typ_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' LIMIT 1;");
        $mod_id = $db->result($r,0,"TYP_MOD_ID");
        $r = $db->query("SELECT * FROM `T_models` WHERE `MOD_ID`='$mod_id' LIMIT 1;");
        $mfa_id = $db->result($r,0,"MOD_MFA_ID");
        $model_link = $db->result($r,0,"Model_Link");
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
        $mfa_link = $db->result($r,0,"MFA_BRAND_LINK");
        return array("mfa_link"=>$mfa_link, "model_link"=>$model_link);
    }

    function getAutoDescr($mf, $ml="", $mi="", $gr="") { $db = DbSingleton::getTokoDb();
        $manufacture = $model = $modelid = $group = "";
        $ml = $this->getUrlString($ml);
        if ($mf>0 && is_numeric($mf)) {$r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID`='$mf' LIMIT 1;"); $manufacture = $db->result($r, 0, "MFA_BRAND");}
        if ($ml!="") {$r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model`='$ml' LIMIT 1;"); $model = $db->result($r, 0, "Model");}
        if ($mi>0 && is_numeric($mi)) {$r = $db->query("SELECT `TEX_TEXT` FROM `T_models` WHERE `MOD_ID`='$mi' LIMIT 1;"); $modelid = $db->result($r, 0, "TEX_TEXT");}
        if ($gr>0 && is_numeric($gr)) {$r = $db->query("SELECT `TYP_TEXT` FROM `T_types` WHERE `TYP_ID`='$gr' AND `ACTIVE`=1 LIMIT 1;"); $group = $db->result($r, 0, "TYP_TEXT");}
        return array ($manufacture, $model, $modelid, $group);
    }

    function getAutoDescrLink($mf, $ml) { $db = DbSingleton::getTokoDb();
        $mfa_brand = $model = "";
        if ($mf!="") {$r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK`='$mf' LIMIT 1;"); $mfa_brand = $db->result($r, 0, "MFA_BRAND");}
        if ($ml!="") {$r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link`='$ml' LIMIT 1;"); $model = $db->result($r, 0, "Model");}
        return array($mfa_brand, $model);
    }

    function getMfaBrand($mfa_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_BRAND` FROM `T_manufacturers` WHERE `MFA_ID`='$mfa_id' LIMIT 1;");
        $mfa_brand = $db->result($r, 0, "MFA_BRAND");
        return $mfa_brand;
    }

    function getAutoModelIdLink($model_id_link) { $db = DbSingleton::getTokoDb();
        $text = $model_id = "";
        if ($model_id_link!="") {
            $r = $db->query("SELECT `MOD_ID`, `TEX_TEXT` FROM `T_models` WHERE `TEX_TEXT_link`='$model_id_link' LIMIT 1;");
            $model_id = $db->result($r, 0, "MOD_ID");
            $text = $db->result($r, 0, "TEX_TEXT");
        }
        return array("text"=>$text, "model_id"=>$model_id);
    }

    function getAutoIdsLink($mf, $ml) { $db = DbSingleton::getTokoDb();
        $mfa_id = $model = "";
        if ($mf!="") {$r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK`='$mf' LIMIT 1;"); $mfa_id = $db->result($r,0,"MFA_ID");}
        if ($ml!="") {$r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link`='$ml' LIMIT 1;"); $model = $db->result($r,0,"Model");}
        return array ($mfa_id, $model);
    }

    function getAutoIMG($mf, $ml, $mi) { $db = DbSingleton::getTokoDb();
        $manufacture = $model = $modelid = "";
        $ml = $this->getUrlString($ml);
        if ($mf>0 && is_numeric($mf)) {$r = $db->query("SELECT `LOGO` FROM `T_manufacturers` WHERE `MFA_ID`='$mf' LIMIT 1;"); $manufacture = $db->result($r,0,"LOGO");}
        if ($ml!="") {$r = $db->query("SELECT `Car_pict` FROM `T_models` WHERE `Model`='$ml' LIMIT 1;"); $model = $db->result($r,0,"Car_pict");}
        if ($mi>0 && is_numeric($mf)) {$r = $db->query("SELECT `Car_pict` FROM `T_models` WHERE `MOD_ID`='$mi' LIMIT 1;"); $modelid = $db->result($r,0,"Car_pict");}
        return array ("mfa_image"=>$manufacture, "model_image"=>$model, "model_id_image"=>$modelid);
    }

    function getStrDescr($str_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `DISP_TEXT` FROM `T2_GROUP_TREE` WHERE `STR_ID`='$str_id' AND `STR_ID`!=0 LIMIT 1;"); $n = $db->num_rows($r); $text = "";
        if ($n>0) $text = $db->result($r, 0, "DISP_TEXT");
        return $text;
    }

    function getHeadNewDescr($head_id) { $db = DbSingleton::getTokoDb();
        $language = new LangClass;
        $lang_id = $this->getLanguage(); $prefix = $language->getTexCapLanguage($lang_id);
        $head_id = $this->getUrlNumber($head_id);
        $TEX_TEXT = ""; $TEX_LINK = "";
        $r = $db->query("SELECT `TEX_$prefix`, `TEX_LINK` FROM `T2_GROUP_TREE_HEAD` WHERE `HEAD_ID`=$head_id LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) {
            $TEX_TEXT = $db->result($r, 0, "TEX_$prefix");
            $TEX_LINK = $db->result($r, 0, "TEX_LINK");
        }
        return array($TEX_TEXT, $TEX_LINK);
    }

    function getCatNewDescr($cat_id) { $db = DbSingleton::getTokoDb();
        $language = new LangClass;
        $lang_id = $this->getLanguage(); $prefix = $language->getTexCapLanguage($lang_id);
        $cat_id = $this->getUrlNumber($cat_id);
        $TEX_TEXT = ""; $TEX_LINK = "";
        $r = $db->query("SELECT `TEX_$prefix`, `TEX_LINK` FROM `T2_GROUP_TREE_CATEGORY` WHERE `CAT_ID`=$cat_id LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) {
            $TEX_TEXT = $db->result($r,0,"TEX_$prefix");
            $TEX_LINK = $db->result($r,0,"TEX_LINK");
        }
        return array($TEX_TEXT, $TEX_LINK);
    }

    function getStrNewDescr($str_id) { $db = DbSingleton::getTokoDb();
        $language = new LangClass;
        $lang_id = $this->getLanguage(); $prefix = $language->getTexCapLanguage($lang_id);
        $str_id = $this->getUrlNumber($str_id);
        $TEX_TEXT = "";
        $r = $db->query("SELECT `TEX_$prefix` FROM `T2_GROUP_TREE_STR` WHERE `STR_ID`=$str_id AND `STR_ID`!=0 LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) {
            $TEX_TEXT = $db->result($r,0,"TEX_$prefix");
        }
        return $TEX_TEXT;
    }

    function getStrParams($str_id) { $db = DbSingleton::getTokoDb();
        $str_id = $this->getUrlNumber($str_id);
        $r = $db->query("SELECT * FROM `T2_GROUP_TREE` WHERE `STR_ID`=$str_id LIMIT 1;");
        $str_level = $db->result($r, 0, "STR_LEVEL");
        $str_id_parrent = $db->result($r, 0, "STR_ID_PARENT");
        return array($str_level, $str_id_parrent);
    }

    function getAutoStrData() {
        define('RDD', dirname (__FILE__));
        $linka = findLinks();
        $str_id = $linka[6];
        $str_level = $linka[7];
        $str_id_parrent = $linka[8];
        $_SESSION["str_id"] = $str_id;
        $_SESSION["str_level"] = $str_level;
        $_SESSION["str_id_parrent"] = $str_id_parrent;
        return array($str_id, $str_level, $str_id_parrent);
    }

    function setAutoData($manufacture, $model, $modelid, $group, $str_id, $str_level = 0, $str_id_parrent = 0) {
        $_SESSION["manufacture"] = $manufacture;
        $_SESSION["model"] = $model;
        $_SESSION["modelid"] = $modelid;
        $_SESSION["group"] = $group;
        $_SESSION["str_id"] = $str_id;
        $_SESSION["str_level"] = $str_level;
        $_SESSION["str_id_parrent"] = $str_id_parrent;
        return true;
    }

    /*
     * Get GROUP text info
     * */
    function getGroupInfo($typ_id) { $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_ID`='$typ_id' AND `ACTIVE`=1;"); $n = $db->num_rows($r);
        if ($n>0) {
            $kw_from = $db->result($r,0,"TYP_KW_FROM");
            $hp_from = $db->result($r,0,"TYP_HP_FROM");
            $ccm = $db->result($r,0,"TYP_CCM");
            $d_start = $db->result($r,0,"TYP_PCON_START");
            $d_end = $db->result($r,0,"TYP_PCON_END");
            $fuel = $db->result($r,0,"FUEL_ID"); $fuel = $this->getFuelName($fuel);
            $eng_cod = $db->result($r,0,"ENG_Cod");
            $full_name = $db->result($r,0,"TYP_MMT_TEXT");
            if ($d_start==0) $d_start = "";
            if (strlen($d_start)==6) $d_start = substr($d_start,0,4).".".substr($d_start,4,2);
            if ($d_end==0) $d_end = "";
            if (strlen($d_end)==6) $d_end = substr($d_end,0,4).".".substr($d_end,4,2);
            $d_end_true = $d_end;
            $list = "$full_name ($d_start - $d_end_true)<br>$fuel, $hp_from {horse_power_cap} / $kw_from {kilo_wat_cap}, $ccm cm3, $eng_cod";
        }
        return $list;
    }

    /*==== GARAGE ====*/
    function getChosenAutoGarage($client_id, $user_id) { $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $cookie = $_COOKIE["session_id"];
        if ($user_id==0) $where = "`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `user_id`='$user_id'";
        $r = $db->query("SELECT `typ_id` FROM `AUTO_GARAGE` WHERE $where AND `status`=1 LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) {
            $typ_id = $db->result($r, 0, "typ_id");
            $typ_text = $this->getGroupInfo($typ_id);
            list($manufacture, $model, $model_id) = $this->getCarInfo($typ_id);
            list($manufacture_cap, , $model_id_cap,) = $this->getAutoDescr($manufacture, $model, $model_id, $typ_id);
            $models_img = $this->getAutoIMG($manufacture,$model,$model_id)["model_id_image"];
            $auto_form = $this->getHtmlForm("garage/garage_selected");
            $auto_form = str_replace("{manufacture_cap}", $manufacture_cap, $auto_form);
            $auto_form = str_replace("{model_id_cap}", $model_id_cap, $auto_form);
            $auto_form = str_replace("{models_img}", $models_img, $auto_form);
            $auto_form = str_replace("{typ_text}", $typ_text, $auto_form);
            $auto_form = str_replace("{prefix}", $prefix, $auto_form);
        } else {
            $auto_form = "{choose_auto_first}";
        }
        $auto_form = $this->replaceLang($auto_form);
        return $auto_form;
    }

    function updateChosenAutoGarage($auto_id) { $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient(); $user_id = $this->getUser(); $cookie = $_COOKIE["session_id"];
        if ($user_id==0) $where = "`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `user_id`='$user_id'";
        $r = $db->query("SELECT `id`, `typ_id` FROM `AUTO_GARAGE` WHERE $where;"); $n = $db->num_rows($r);
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $id = $db->result($r, $i-1, "id");
                $typ_id = $db->result($r, $i-1, "typ_id");
                if ($auto_id==$id) {
                    $db->query("UPDATE `AUTO_GARAGE` SET `status`=1 WHERE `id`=$id;");
                    setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");
                }
                else $db->query("UPDATE `AUTO_GARAGE` SET `status`=0 WHERE `id`=$id;");
            }
        }
        return true;
    }

    function deleteAutoGarage($auto_id) { $db = DbSingleton::getTokoDb();
        $db->query("DELETE FROM `AUTO_GARAGE` WHERE `id`=$auto_id;");
        $client_id = $this->getClient(); $user_id = $this->getUser(); $cookie = $_COOKIE["session_id"];
        if ($user_id==0) $where = "`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `user_id`='$user_id'";
        $r = $db->query("SELECT * FROM `AUTO_GARAGE` WHERE $where ORDER BY `timestamp` DESC LIMIT 1;"); $n=$db->num_rows($r);
        if ($n==0) {
            setcookie("auto_typ_id", "", time()-3600, "/");
            return false;
        } else {
            $id = $db->result($r,0,"id");
            $typ_id = $db->result($r,0,"typ_id");
            $db->query("UPDATE `AUTO_GARAGE` SET `status`=1 WHERE `id`='$id' LIMIT 1;");
            setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");
            return true;
        }
    }

    function showGarageForm() { $db = DbSingleton::getTokoDb();
        $form=$this->getHtmlForm("garage/garage");
        $list=$auto_form="";
        $client_id=$this->getClient(); $user_id=$this->getUser(); $cookie=$_COOKIE["session_id"];
        if ($user_id==0) $where="`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where="`client_id`='$client_id' AND `user_id`='$user_id'";
        $r=$db->query("SELECT `id`, `typ_id` FROM `AUTO_GARAGE` WHERE $where;"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $id=$db->result($r, $i-1, "id");
                $typ_id=$db->result($r, $i-1, "typ_id");
                list($manufacture, $model, $model_id) = $this->getCarInfo($typ_id);
                if ($typ_id!=$this->getCookieAuto()) {
                    $status_cap="{select_cap}";
                    $status_disable="";
                    $status_btn="onclick='updateChosenAutoGarage($id);'";
                } else {
                    $status_cap="{unselect_cap}";
                    $status_disable="disabled";
                    $status_btn="";
                }
                list($manufacture_cap,, $model_id_cap, $typ_text) = $this->getAutoDescr($manufacture, $model, $model_id, $typ_id);
                $list.="
                <li class=\"row garage-row\">
                    <div class=\"col-lg-6 col-12 garage-row__text\">
                        $manufacture_cap $model_id_cap <br>
                        <span>$typ_text</span>
                    </div>
                    <div class=\"col-lg-6 col-12 garage-row__buttons\"> 
                        <button $status_btn class=\"btn btn-primary\" $status_disable>$status_cap</button>
                        <button onclick=\"deleteAutoGarage($id);\" class=\"btn btn-primary\"><i class=\"fa fa-trash-alt\"></i></button>
                    </div>
                </li>";
            }
            $auto_form = $this->getChosenAutoGarage($client_id, $user_id);
        }
        $form = str_replace("{garage_list}", $list, $form);
        $form = str_replace("{auto_form}", $auto_form, $form);

        if ($n==0) $form = $this->getHtmlForm("error/404_garage");
        $form = $this->replaceLang($form);
        return $form;
    }

    function addToGarage($typ_id) { $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient(); $user_id = $this->getUser();
        list($manufacture, $model, $model_id) = $this->getCarInfo($typ_id);
        $cookie = $_COOKIE["session_id"]; $max_auto = 5;
        if ($user_id==0) $where = "`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `user_id`='$user_id'";
        if ($manufacture!="" && $model!="" && $model_id!="" && $typ_id!="") {
            $count = $this->getGarageAutoCount()[0];
            if ($count<=$max_auto) {
                $r = $db->query("SELECT * FROM `AUTO_GARAGE` WHERE $where AND `typ_id`=$typ_id;"); $n = $db->num_rows($r);
                if ($n==0) {
                    $rs = $db->query("SELECT * FROM `AUTO_GARAGE` WHERE $where AND `status`=1;"); $ns = $db->num_rows($rs);
                    for ($i=1; $i<=$ns; $i++) {
                        $id = $db->result($rs, $i-1, "id");
                        $db->query("UPDATE `AUTO_GARAGE` SET `status`=0 WHERE `id`=$id;");
                    }
                    $db->query("INSERT INTO `AUTO_GARAGE` (`client_id`, `user_id`, `cookie_id`, `typ_id`, `status`) VALUES ($client_id, $user_id, '$cookie', $typ_id, 1);");
                    list($manufacture_cap,, $model_id_cap, $typ_text) = $this->getAutoDescr($manufacture, $model, $model_id, $typ_id);
                    setcookie("auto_typ_id", $typ_id, time() + (86400 * 30), "/");
                    $result = $this->replaceLang("{auto_cap} $manufacture_cap $model_id_cap $typ_text {garage_added}");
                } else { $result = true; }
            } else { $result = false; }
        } else { $result = false; }
        return $result;
    }

    function getGarageAutoCount() { $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient(); $user_id = $this->getUser(); $cookie = $_COOKIE["session_id"];
        if ($user_id==0) $where = "`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `user_id`='$user_id'";
        $r = $db->query("SELECT * FROM `AUTO_GARAGE` WHERE $where;"); $n = $db->num_rows($r);
        $n>0 ? $list = $n : $list = "!";
        $list=="" ? $style = "none" : $style = "";
        return array($list, $style);
    }

    function checkUserGarage($typ_id) { $db = DbSingleton::getTokoDb();
        $client_id = $this->getClient(); $user_id = $this->getUser(); $cookie = $_COOKIE["session_id"];
        if ($user_id==0) $where = "`client_id`='$client_id' AND `cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `user_id`='$user_id'";
        $r = $db->query("SELECT * FROM `AUTO_GARAGE` WHERE $where AND `typ_id`=$typ_id;"); $n = $db->num_rows($r);
        if ($n == 0) return false; else return true;
    }

    /*==== HISTORY ====*/
    function insertAutoHistory($typ_id) { $db = DbSingleton::getTokoDb();
        $cookie = $_COOKIE["session_id"]; $date = date("Y-m-d H:i:s");
        $client_id = $this->getClient(); $user_id = $this->getUser(); $max_history_count = 10;
        if ($user_id==0) $where = "`cookie_id`='$cookie'"; else $where="`client_id`='$client_id' AND `client_user_id`='$user_id'";
        $r = $db->query("SELECT COUNT(`id`) as kilk FROM `AUTO_HISTORY` WHERE $where;"); $k = $db->result($r, 0, "kilk");
        if ($k>$max_history_count) {
            $r = $db->query("SELECT `id` FROM `AUTO_HISTORY` WHERE $where ORDER BY `timestamp` ASC LIMIT 1;");
            $id = $db->result($r,0,"id");
            $db->query("UPDATE `AUTO_HISTORY` SET `typ_id`='$typ_id' WHERE `id`='$id';");
        } else {
            $r = $db->query("SELECT `id` FROM `AUTO_HISTORY` WHERE $where AND `typ_id`='$typ_id';"); $n=$db->num_rows($r);
            if ($n>0)
                $db->query("UPDATE `AUTO_HISTORY` SET `timestamp`='$date' WHERE $where AND `typ_id`='$typ_id';");
            else
                $db->query("INSERT INTO `AUTO_HISTORY` (`client_id`, `client_user_id`, `cookie_id`, `typ_id`)
                VALUES ('$client_id', '$user_id', '$cookie', '$typ_id');");
        }
        return true;
    }

    function showAutoHistory() { $db = DbSingleton::getTokoDb();
        $cookie = $_COOKIE["session_id"];
        $user_id = $this->getUser(); $client_id = $this->getClient();
        if ($user_id==0) $where = "cookie_id='$cookie'"; else $where = "client_id='$client_id' AND client_user_id='$user_id'";
        $r = $db->query("SELECT `id`, `typ_id` FROM `AUTO_HISTORY`
        WHERE $where GROUP BY `typ_id` ORDER BY `timestamp` DESC LIMIT 10;"); $n = $db->num_rows($r);
        if ($n>0) {
            $list = "";
            for ($i=1; $i<=$n; $i++) {
                $id = $db->result($r,$i-1,"id");
                $typ_id = $db->result($r,$i-1,"typ_id");
                $list.="<li class=\"garage-history-block-list__item\">
                    <div class='container'>
                        <div class='row'>
                            <div class='col-10'>
                                <a onclick='setCookie(\"auto_typ_id\", $typ_id); location.reload();'>".$this->getCarDescription($typ_id)."</a>
                            </div>
                            <div class='col-2 text-right'>
                                <a onclick='dropAutoHistory($id)'><i class=\"fa fa-times\"></i></a>
                            </div>
                        </div>
                    </div>
                </li>";
            }
        } else $list = "{empty_history}";
        $form = $this->getHtmlForm("garage/garage_history");
        $form = str_replace("{garage_history_list}", $list, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function dropAutoHistory($history_id) { $db=DbSingleton::getTokoDb();
        if ($history_id=="") {
            $user_id = $this->getUser(); $client_id = $this->getClient(); $cookie = $_COOKIE["session_id"];
            if ($user_id==0) $where = "`cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `client_user_id`='$user_id'";
        } else {
            $where = "`id`='$history_id'";
        }
        $db->query("DELETE FROM `AUTO_HISTORY` WHERE $where");
        return true;
    }

    /*==== CARS VARIABLES ===*/
    function getMfaLink($mfa_link) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `MFA_ID` FROM `T_manufacturers` WHERE `MFA_BRAND_LINK`='$mfa_link' LIMIT 1;");
        $mfa_id = $db->result($r, 0, "MFA_ID");
        return $mfa_id;
    }

    function getModLink($mod_link) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `Model` FROM `T_models` WHERE `Model_Link`='$mod_link' LIMIT 1;");
        $model = $db->result($r, 0, "Model");
        return $model;
    }

    function getModIdLink($mod_id_link) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_TEXT` FROM `T_models` WHERE `MOD_ID`='$mod_id_link' LIMIT 1;");
        $text = $db->result($r, 0, "TEX_TEXT");
        return $text;
    }

    function getSeoContent($title, $mfa_link, $mod_link="") {
        $form = $this->getHtmlForm("seo_content");
        $mfa_id = $this->getMfaLink($mfa_link); if ($mfa_link=="") $mfa_id = "";
        $model = $this->getModLink($mod_link);
        if ($model=="") {
            $form = str_replace("{seo_list}", $this->getAutoModList($mfa_id).$this->getDetailsList("", "", $mfa_link, $mod_link), $form);
        } else {
            $form = str_replace("{seo_list}", $this->getDetailsList("", "", $mfa_link, $mod_link), $form);
        }
            $form = str_replace("{seo_header}", $title, $form);
        return $form;
    }

    function getAutoMfaModelList($str_id="", $active_filters="", $mfa="") { $db = DbSingleton::getTokoDb();
        $search = new SearchClass;
        $details_cap = "{details_on_cap}"; $title = ""; $link = "cars";
        if ($mfa!="") $where = " AND `MFA_ID`='$mfa'"; else $where = "";
        if ($str_id!="") {
            $details_cap = $this->getStrNewDescr($str_id);
            $str_link = $this->getStrNewLink($str_id);
            $h1_text = $this->getStaticH1("/catalog/$str_link/");
            if ($h1_text!="") $details_cap = $h1_text;
            $link = "catalog/$str_link";
            if ($active_filters!="") {
                $filters = $search->getFiltersTitle($active_filters);
                $details_cap.=" $filters";
            }
            if ($mfa!="") {
                $mfa_brand = $this->getMfaBrand($mfa);
                $title="<div><span class='title-b'>$details_cap {on_cap} {other_models} $mfa_brand</span></div>";
            }
            else $title = "<div><span class='title-b'>$details_cap</span></div>";
            $details_cap.=" {on_cap}";
        }
        $list = "<div class='seo_auto'>$title";
        $mas = [];
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 $where ORDER BY `MFA_BRAND` ASC;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $mfa_id = $db->result($r,$i-1,"MFA_ID");
            $mfa_brand = $db->result($r,$i-1,"MFA_BRAND");
            $mfa_link = $db->result($r,$i-1,"MFA_BRAND_LINK");
            $image = $db->result($r,$i-1,"LOGO");
            $mas[$mfa_brand] = ["mfa_id"=>$mfa_id, "link"=>$mfa_link, "logo"=>$image];
        }
        foreach ($mas as $mfa_brand => $values) {
            $mfa_id = $values["mfa_id"];
            $mfa_link = $values["link"];
            if ($mfa=="") $list.="<div class='title'><a href='https://toko.ua/$link/$mfa_link/'>$details_cap $mfa_brand</a></div>";
            $list.="<ul class='list-inline'>";
            $r = $db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;"); $n=$db->num_rows($r);
            for ($i=1; $i<=$n; $i++) {
                $model = $db->result($r, $i-1, "Model");
                $model_link = $db->result($r, $i-1, "Model_Link");
                $list.="<li><a href='https://toko.ua/$link/$mfa_link/$model_link/'>$mfa_brand $model</a></li>";
            }
            $list.="</ul>";
        }
        $list.="</div>";
        return $list;
    }

    function getAutoModList($mfa="", $str_id="", $active_filters="") { $db = DbSingleton::getTokoDb();
        $search = new SearchClass;
        $prefix = $this->getLangPrefix();

        if ($str_id!="") {
            $details_cap = $this->getStrNewDescr($str_id);
            $str_link = $this->getStrNewLink($str_id);
            $h1_text = $this->getStaticH1("/catalog/$str_link/");
            if ($h1_text!="") $details_cap = $h1_text;

            $link = "catalog/$str_link";
            if ($active_filters!="") {
                $filters = $search->getFiltersTitle($active_filters);
                $details_cap.=" $filters";
            }
            $details_cap.=" {on_cap}";
        } else {
            $details_cap = "{details_on_cap}";
            $link = "cars";
        }

        if ($mfa!="") $where = "AND `MFA_ID`='$mfa'"; else $where = "";
        $list = "<ul>";
        $r = $db->query("SELECT * FROM `T_manufacturers` WHERE `ACTIVE`=1 $where ORDER BY `MFA_BRAND`;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $mfa_id = $db->result($r,$i-1,"MFA_ID");
            $mfa_brand = $db->result($r,$i-1,"MFA_BRAND");
            $mfa_link = $db->result($r,$i-1,"MFA_BRAND_LINK");

            if ($mfa=="") {
                $list.="<li class='title'><span class='bold'><a href='https://toko.ua$prefix/$link/$mfa_link/'>$details_cap $mfa_brand</a></span>";
            } else {
                $list = "";
                $list.="<span class='title-b'>$details_cap $mfa_brand</span>";
            }
            $list.="<div class='seo_details'><div class='seo-ul'>";

            $r2 = $db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' GROUP BY `Model`;"); $n2 = $db->num_rows($r2);
            for ($i2=1; $i2<=$n2; $i2++) {
                $mod = $db->result($r2,$i2-1,"Model");
                $mod_link = $db->result($r2,$i2-1,"Model_Link");
                $list.="<a class='seo-li' href='https://toko.ua$prefix/$link/$mfa_link/$mod_link/'>
                    <span>$mfa_brand $mod</span>
                </a>";
            }

            $list.="</div></div>";
        }
        if ($mfa!="") $list.="</ul>";
        return $list;
    }

    function getAutoTypeList($mfa, $mod_id, $str_id="", $active_filters="") { $db = DbSingleton::getTokoDb();
        $search = new SearchClass;
        $mfa_text = $this->getMfaBrand($mfa);
        $mod_id_text = $this->getModIdLink($mod_id);
        $title = "$mfa_text $mod_id_text";
        $details_cap = "{details_on_cap}";

        if ($str_id!="") {
            $details_cap = $this->getStrNewDescr($str_id);
            $str_link = $this->getStrNewLink($str_id);
            $h1_text = $this->getStaticH1("/catalog/$str_link/");
            if ($h1_text!="") $details_cap=$h1_text;
            if ($active_filters!="") {
                $filters = $search->getFiltersTitle($active_filters);
                $details_cap.=" $filters";
            }
            $details_cap.=" {on_cap}";
        }

        $r = $db->query("SELECT * FROM `T_types` WHERE `TYP_MOD_ID`='$mod_id';"); $n = $db->num_rows($r);
        $list = "<span class='title-b'>$details_cap $title</span>";
        $list.="<div class=\"t_types\">";
        $mas = [];
        for ($i=1; $i<=$n; $i++) {
            $fuel_id = $db->result($r, $i - 1, "FUEL_ID");
            $typ_text = $db->result($r,$i-1,"TYP_TEXT");
            $kw_from = $db->result($r,$i-1,"TYP_KW_FROM");
            $hp_from = $db->result($r,$i-1,"TYP_HP_FROM");

            $link = "<span><b>$typ_text</b> ($hp_from {horse_power_cap}, $kw_from {kilo_wat_cap})</span>";
            $link = $this->replaceLang($link);
            if (empty($mas[$fuel_id])) $mas[$fuel_id] = [];
            $mas[$fuel_id][$i] = $link;
        }
        foreach ($mas as $fuel_id=>$types) {
            $fuel_name = $this->getFuelName($fuel_id);
            $list.="<div><span class=\"text-dark bold\">$fuel_name: </span>";
            foreach ($types as $typ) {
                $list.="$typ";
            }
            $list.="</div>";
        }
        $list.="</div>";
        return $list;
    }

    function getAutoModIDList($mfa, $mod, $str_id="", $active_filters="") { $db = DbSingleton::getTokoDb();
        $search = new SearchClass;
        $prefix = $this->getLangPrefix();
        $list = ""; $details_cap = "{all_type_models}"; $link = "";

        if ($str_id!="") {
            $details_cap = $this->getStrNewDescr($str_id);
            $str_link = $this->getStrNewLink($str_id);
            $h1_text = $this->getStaticH1("/catalog/$str_link/");
            if ($h1_text!="") $details_cap = $h1_text;

            $link = "catalog/$str_link";
            if ($active_filters!="") {
                $filters = $search->getFiltersTitle($active_filters);
                $details_cap.=" $filters";
            }
            $details_cap.=" {on_cap}";
        }

        $r = $db->query("SELECT mf.*, md.Model, md.Model_Link FROM `T_manufacturers` mf
            LEFT JOIN `T_models` md ON md.MOD_MFA_ID=mf.MFA_ID
        WHERE mf.`MFA_ID`='$mfa' AND md.`Model`='$mod' GROUP BY md.`Model`;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $mfa_id = $db->result($r,$i-1,"MFA_ID");
            $mfa_link = $db->result($r,$i-1,"MFA_BRAND_LINK");
            $mfa_brand = $db->result($r,$i-1,"MFA_BRAND");
            $model_text = $db->result($r,$i-1,"Model");
            $mod_link = $db->result($r,$i-1,"Model_Link");

            $list.="<span class='title-b'>$details_cap $mfa_brand $model_text</span>";
            $list.="<div class='seo_details'><div class='seo-ul'>";

            $r2 = $db->query("SELECT * FROM `T_models` WHERE `MOD_MFA_ID`='$mfa_id' AND `Model`='$mod' ORDER BY `MOD_PCON_START`;"); $n2 = $db->num_rows($r2);
            for ($i2=1; $i2<=$n2; $i2++) {
                $mod_id_link = $db->result($r2,$i2-1,"TEX_TEXT_link");
                $text = $db->result($r2,$i2-1,"TEX_TEXT");
                $image = $db->result($r2,$i2-1,"Car_pict");
                $path = "https://toko.ua/uploads/images/models/$image";
                $d_start = $db->result($r2,$i2-1,"MOD_PCON_START"); $d_start=substr($d_start,0,4);
                $d_end = $db->result($r2,$i2-1,"MOD_PCON_END"); $d_end=substr($d_end,0,4); if ($d_end==0) $d_end="{cur_time}";
                $list.="<a class='seo-li seo-li-id' href=\"https://toko.ua$prefix/$link/$mfa_link/$mod_link/$mod_id_link/\">
                    <div class='row mar0'>
                        <div class='col-4 pad0'><img src='$path' alt='$text' title='$text'></div>
                        <div class='col-8 '><span>$mfa_brand $text ($d_start - $d_end)</span></div>
                    </div>
                </a>";
            }
            $list.="</div></div>";
        }
        $list.=$this->getAutoMfaModelList($str_id, $active_filters, $mfa);

        return $list;
    }

    function getDetailsHeadImage($head_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_RU` FROM `T2_GROUP_TREE_HEAD` WHERE `STATUS`=1 AND `HEAD_ID`='$head_id' LIMIT 1;");
        $head_tex_text = $db->result($r, 0, "TEX_RU");
        $title = "<div class='tree-block-title__text'><div class='container pad0'><h1>$head_tex_text</h1></div></div>";
        $img = "$head_id.jpg";
        $list = "<div class='tree-block-title' style=\"background-image: url('/images/tree_head/$img');\">$title</div>";
        return $list;
    }

    // CATALOG / TO I FILTRI
    function getDetailsList($head, $category="", $mfa_link="", $mod_link="") { $db = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $where = ""; $where_category = "";
        if ($head!="") $where = "AND `HEAD_ID`='$head'";
        if ($category!="") $where_category = "AND `CAT_ID`='$category'";

        $list = "<div class='tree-block'>";

        $r3 = $db->query("SELECT * FROM `T2_GROUP_TREE_HEAD` WHERE `STATUS`=1 $where;"); $n3 = $db->num_rows($r3);
        for ($i3=1; $i3<=$n3; $i3++) {
            $head_id = $db->result($r3, $i3-1, "HEAD_ID");
            $head_tex_text = $db->result($r3, $i3-1, "TEX_RU");
            $head_tex_link = $db->result($r3, $i3-1, "TEX_LINK");
            $head!="" ? $title = "<div class='tree-block-title__text'><h1>$head_tex_text</h1></div>" : $title = "<span><a href='https://toko.ua$prefix/catalog/$head_tex_link/'>$head_tex_text</a></span>";
            $category=="" ?: $title = "";

            $list.="<div class='tree-item'>";

            $r2 = $db->query("SELECT * FROM `T2_GROUP_TREE_CATEGORY` WHERE `HEAD_ID`='$head_id' $where_category;"); $n2 = $db->num_rows($r2);
            for ($i2=1; $i2<=$n2; $i2++) {
                $cat_id = $db->result($r2, $i2-1, "CAT_ID");
                $cat_tex_text = $db->result($r2, $i2-1, "TEX_RU");
                $cat_tex_link = $db->result($r2, $i2-1, "TEX_LINK");
                $category!="" ? $title_cat = "<h1>$cat_tex_text</h1>" : $title_cat = "<a href='https://toko.ua$prefix/catalog/$head_tex_link/$cat_tex_link/'>$cat_tex_text</a>";

                $list.="<div class='tree-item-title'>$title_cat</div>";
                $list.="<div class='tree-item-list'>";

                $r = $db->query("SELECT * FROM `T2_GROUP_TREE_STR` WHERE `CAT_ID`='$cat_id' ORDER BY `TEX_RU` ASC;"); $n = $db->num_rows($r);
                for ($i=1; $i<=$n; $i++) {
                    $tex_text = $db->result($r,$i-1,"TEX_RU");
                    $tex_link = $db->result($r,$i-1,"TEX_LINK");
                    if ($mfa_link!="") $tex_link.="/$mfa_link";
                    if ($mod_link!="") $tex_link.="/$mod_link";
                    $list.="<div class='tree-item-list__element'>
                        <a href='https://toko.ua$prefix/catalog/$tex_link/'>
                            $tex_text
                        </a>
                    </div>";
                }
                $list.="</div>";
            }
            $list.="</div>";
        }
        $list.="</div>";
        return $list;
    }

}