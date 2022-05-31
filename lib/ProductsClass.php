<?php

class ProductsClass extends CatalogueClass
{

    public function getCarsSelectUser($mfa_link = "", $mod_link = "", $group_id = 0)
    {
        if ($this->getCookieAuto() > 0) {
            $form = $this->getCarsGarage();
        } else {
            $form = $this->getCarsSearch($mfa_link, $mod_link, $group_id);
        }

        return $form;
    }

    public function getCarsForm($mfa_link, $mod_link): array
    {
        $automan = new AutoClass();

        $status = 1;
        $title  = $descr = $meta_tag = "";

        list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
        $h1 = $automan->getCarsTitle($mfa_id, $model);

        $form = $this->getHtmlForm("cars/form");
        $form = str_replace(array("{cars_h1}", "{cars_list}", "{cars_seo}"), array($h1, $this->getCarsSearch($mfa_link, $mod_link), $automan->getCarsSeoContent($mfa_link, $mod_link)), $form);

        $automan->getCarsMetaTags($mfa_id, $model, $h1);
        if ($mfa_id > 0) {
            $title = $this->replaceLang("{site_cars_mfa}");
            $descr = $this->replaceLang("{site_cars_mfa_description}");
            if ($model !== "") {
                $title = $this->replaceLang("{site_cars_model}");
                $descr = $this->replaceLang("{site_cars_model_description}");
            }
            $title = str_replace("{h1_text}", $h1, $title);
            $descr = str_replace("{h1_text}", $h1, $descr);
        }

        if ($mfa_id === 0 && $mfa_link !== "") {
            $status = 0;
        }
        if ($mod_link !== "" && $model === "") {
            $status = 0;
        }

        return compact("form", "title", "descr", "meta_tag", "status");
    }

    public function getCarsSearch($mfa_link = "", $mod_link = "", $group_id = 0)
    {
        $form = $this->getHtmlForm("cars/cars");

        if ($mfa_link !== "") {
            $automan    = new AutoClass();
            $mfa_id     = $automan->getMfaLink($mfa_link);
            $mfa_brand  = $automan->getMfaBrand($mfa_id);
            $list_model = $this->getCarsSearchContent("manuf", $mfa_id, $group_id)[0];

            $form = str_replace(array("{cars_models}", "{selected_manuf}", "{cars_manufacturer}"), array($list_model, $mfa_id, $mfa_brand), $form);

            if ($mod_link !== "") {
                $model = $automan->getModLink($mod_link);
                $cars_content = $this->getCarsSearchContent("model", $mfa_id . "_" . $model, $group_id)[0];

                $form = str_replace(array("{cars_years}", "{selected_model}", "{cars_model}", "{active_nav}"), array($cars_content, $mfa_id . "_" . $model, $model, "years"), $form);
            }

            $form = str_replace("{active_nav}", "model", $form);
        }

        $form = str_replace(array("{cars_manufactures}", "{selected_manuf}", "{selected_model}", "{active_nav}"), array($this->getCarsSearchContent()[0], 0, 0, ""), $form);

        return $this->replaceLang($form);
    }

    public function getYearsForm($date_start, $date_end, $mfa_id, $model): string
    {
        $min_date_start = 1947;
        $max_date_end = 2020;

        if ($date_end !== "" && (int)$date_end !== 0) {
            $date_end = substr($date_end, 0, -2) . "";
        } else {
            $date_end = $max_date_end;
        }
        if ($date_start !== "" && (int)$date_start !== 0) {
            $date_start = substr($date_start, 0, -2) . "";
        } else {
            $date_start = $min_date_start;
        }

        $mas = [];
        for($i = $date_end; $i >= $date_start; $i--) {
            $mas[] = $i;
        }

        $headers = [];
        foreach($mas as $val) {
            $item = floor($val / 10) * 10;
            if (!in_array($item, $headers, true)) {
                $headers[] = $item;
            }
        }

        $res = [];
        foreach ($headers as $head) {
            foreach ($mas as $val) {
                if (($val - $head) < 10 && ($val - $head) >= 0) {
                    $res[$head][] = $val;
                }
            }
        }

        $form = "";
        foreach ($res as $key => $value) {
            $form .= "
            <div class=\"cars-tab__block-item-years\"><div class=\"cars-tab__block-item cars-tab__block-item-min cars-tab__block-item-min-disabled\">$key - e</div>";
            foreach ($value as $year) {
                $year_cap = $mfa_id . "_" . $model . "_" . $year;
                $form .= "
                <div class=\"cars-tab__block-item cars-tab__block-item-min\" data-url=\"years/$year_cap\" onclick=\"toggleCarsTab(this)\">$year</div>";
            }
            $form .= "</div>";
        }

        return $form;
    }

    public function getCarsSearchContent($type = "", $value = "", $group_id = 0): array
    {
        $type   = $this->getNameString($type);
        $value  = $this->getNameString($value);

        $db = DbSingleton::getTokoDb();
        $automan = new AutoClass();

        $group_link = $this->getSiteLink() . "$this->catalog_link/" . $automan->getGroupRowLink($group_id) . "/";

        $list = $title = $nav = $tab = "";
        $skip = $n = 0;

        // MANUFACTURE
        if ($type === "") {
            $r = $db->query("SELECT `MFA_ID`, `MFA_BRAND` FROM `T_manufacturers` WHERE `ACTIVE` = 1 ORDER BY `MFA_BRAND`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $mfa_id     = $db->result($r, $i - 1, "MFA_ID");
                $mfa_brand  = $db->result($r, $i - 1, "MFA_BRAND");

                $list .= "
                <div data-url=\"manuf/$mfa_id\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$mfa_brand</div>";
            }

            $title  = "{auto_cap}";
            $nav    = "{auto_cap}";
            $tab    = "cars-tab1";
        }

        // MODEL
        if ($type === "manuf") {
            $mfa_id = $this->getUrlNumber($value);

            $r = $db->query("SELECT `Model` FROM `T_models` WHERE `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 GROUP BY `Model`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $model      = $db->result($r, $i - 1, "Model");
                $model_cap  = $mfa_id . "_" . $model;

                $list .= "
                <div data-url=\"model/$model_cap\" class=\"cars-tab__block-item\" onclick=\"toggleCarsTab(this)\">$model</div>";
            }

            $title  = $automan->getMfaBrand($mfa_id);
            $nav    = "manuf";
            $tab    = "cars-tab2";
        }

        // YEAR
        if ($type === "model") {
            list($mfa_id, $model) = explode("_", $value);

            $mfa_id = $this->getUrlNumber($mfa_id);
            $model  = $this->getUrlString($model);
            $n      = 1;

            $r = $db->query("SELECT MIN(`MOD_PCON_START`) as min_year, 
                CASE WHEN MIN(`MOD_PCON_END`)=0 THEN 0 ELSE MAX(`MOD_PCON_END`) END as max_year
            FROM `T_models` 
            WHERE `Model` = '$model' AND `MOD_MFA_ID` = $mfa_id;");
            $date_start = $db->result($r, 0, "min_year");
            $date_end   = $db->result($r, 0, "max_year");
            $list       .= $this->getYearsForm($date_start, $date_end, $mfa_id, $model);

            $title  = $model;
            $nav    = "model";
            $tab    = "cars-tab3";
        }

        // BODY (MODEL_ID)
        if ($type === "years") {
            list($mfa_id, $model, $year) = explode("_", $value);
            $mfa_id = $this->getUrlNumber($mfa_id);
            $model  = $this->getUrlString($model);
            $year   = $this->getUrlNumber($year);
            $where  = "AND 
                ((`MOD_PCON_END`>=" . $year . "00 AND `MOD_PCON_END`<=" . $year . "12)
                OR (`MOD_PCON_START`<=" . $year . "12 AND `MOD_PCON_END`>=" . $year . "00)
                OR (`MOD_PCON_START`<=" . $year . "12 AND `MOD_PCON_END`=0))";

            $r = $db->query("SELECT `MOD_ID`, `TEX_TEXT`, `Car_pict`,
            CASE WHEN MOD_PCON_START = 0 THEN '' ELSE MOD_PCON_START END AS MOD_PCON_START,
            CASE WHEN MOD_PCON_END = 0 THEN '{cur_time_min}' ELSE MOD_PCON_END END AS MOD_PCON_END 
            FROM `T_models` 
            WHERE `Model` = '$model' AND `MOD_MFA_ID` = $mfa_id AND `ACTIVE` = 1 $where;");
            $n = $db->num_rows($r);
            if ($n === 1) {
                $skip = $db->result($r, 0, "MOD_ID");
            }
            for ($i = 1; $i <= $n; $i++) {
                $mod_id     = $db->result($r, $i - 1, "MOD_ID") + 0;
                $tex_text   = $db->result($r, $i - 1, "TEX_TEXT");
                $image      = $db->result($r, $i - 1, "Car_pict");
                $d_start    = $db->result($r, $i - 1, "MOD_PCON_START");
                $d_start    = substr($d_start, 0, 4);
                $d_end      = $db->result($r, $i - 1, "MOD_PCON_END");
                $d_end      = substr($d_end, 0, 4);

                list($body_name, $body_path) = $this->getBodyCarImage($mod_id);

                $list .= "
                <div data-url=\"bodyc/$mod_id\" class=\"cars-tab__block-item cars-tab__block-item-body\" onclick=\"toggleCarsTab(this)\">
                    <div class=\"bodyc\">
                        <div class=\"bodyc-head\">
                            <div class=\"bodyc__title\">$tex_text</div>
                            <div class=\"bodyc__type\"><img src=\"$body_path\" alt=\"$body_name\" title=\"$body_name\"></div></div>
                        </div>    
                        <div class=\"bodyc-content\">
                            <div class=\"bodyc__descr\">
                                {model_number_type}: $body_name
                                <br>
                                {year_issue}: $d_start - $d_end
                            </div>
                            <div class=\"bodyc__image\">
                                <img src=\"https://toko.ua/uploads/images/models/$image\" alt=\"$tex_text\" title=\"$tex_text\">
                            </div>
                        </div>
                    </div>
                </div>";
            }

            $title  = $year;
            $nav    = "years";
            $tab    = "cars-tab4";
        }

        // ENGINE
        if ($type === "bodyc") {
            $mod_id = $this->getUrlNumber($value);
            $r = $db->query("SELECT COUNT(`TYP_ID`) as count_types, `TYP_ID`, `VOLUME_CM`, `FUEL_ID`, `TYP_KW_FROM`, `TYP_HP_FROM` 
            FROM `T_types` 
            WHERE `TYP_MOD_ID` = $mod_id AND `ACTIVE` = 1 
            GROUP BY `VOLUME_CM`, `FUEL_ID` 
            ORDER BY `VOLUME_CM`, `FUEL_ID`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $typ_id         = $db->result($r, $i - 1, "TYP_ID");
                $count_types    = $db->result($r, $i - 1, "count_types");
                $volume_cm      = $db->result($r, $i - 1, "VOLUME_CM");
                $fuel_id        = $db->result($r, $i - 1, "FUEL_ID");
                $fuel_text      = $this->getFuelName($fuel_id);
                $fuel_cap       = $mod_id . "_" . $volume_cm . "_" . $fuel_id;
                $onclick        = ($count_types === 1) ? "finishGarage('$typ_id', '$group_link')" : "toggleCarsTab(this)";
                $list .= "
                <div data-url=\"engin/$fuel_cap\" class=\"cars-tab__block-item\" onclick=\"$onclick\">$volume_cm $fuel_text</div>";
            }

            $title  = $this->getModIdText($mod_id);
            $nav    = "bodyc";
            $tab    = "cars-tab5";
        }

        // MODIFICATION
        if ($type === "engin") {
            list($mod_id, $volume_cm, $fuel_id) = explode("_", $value);
            $mod_id     = $this->getUrlNumber($mod_id);
            $volume_cm  = $this->getUrlString($volume_cm);
            $fuel_id    = $this->getUrlNumber($fuel_id);

            $r = $db->query("SELECT `TYP_ID`, `TYP_TEXT`, `TYP_KW_FROM`, `TYP_HP_FROM`, `ENG_Cod`,
            CASE WHEN TYP_PCON_START = 0 THEN '' ELSE TYP_PCON_START END AS TYP_PCON_START,
            CASE WHEN TYP_PCON_END = 0 THEN '{cur_time_min}' ELSE TYP_PCON_END END AS TYP_PCON_END 
            FROM `T_types` 
            WHERE `TYP_MOD_ID` = $mod_id AND `VOLUME_CM` = '$volume_cm' AND `FUEL_ID` = $fuel_id AND `ACTIVE` = 1 
            ORDER BY `TYP_HP_FROM`;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $typ_id     = $db->result($r, $i - 1, "TYP_ID");
                $typ_text   = $db->result($r, $i - 1, "TYP_TEXT");
                $kw_from    = $db->result($r, $i - 1, "TYP_KW_FROM");
                $hp_from    = $db->result($r, $i - 1, "TYP_HP_FROM");
                $d_start    = $db->result($r, $i - 1, "TYP_PCON_START");
                $d_end      = $db->result($r, $i - 1, "TYP_PCON_END");
                $eng_cod    = $db->result($r, $i - 1, "ENG_Cod");

                if (mb_strlen($d_start) === 6) {
                    $d_start = substr($d_start, 0, 4) . "." . substr($d_start, 4, 2);
                }
                if (mb_strlen($d_end) === 6) {
                    $d_end = substr($d_end, 0, 4) . "." . substr($d_end, 4, 2);
                }

                $list .= "
                <div class=\"cars-tab__block-item cars-tab__block-item-modif\"><a onclick=\"finishGarage('$typ_id', '$group_link')\">
                <b>$typ_text</b> 
                    <table>
                        <tr><td>{date_release}:</td><td class=\"text-right\">$d_start - $d_end</td></tr>
                        <tr><td>{engine_model}:</td><td class=\"text-right\">$eng_cod</td></tr>
                        <tr><td>{power_cap}:</td><td class=\"text-right\">$hp_from {horse_power_cap}, $kw_from {kilo_wat_cap}</td></tr>
                    </table>
                </a></div>";
            }
            $title  = $volume_cm . " " . $this->getFuelName($fuel_id);
            $nav    = "engin";
            $tab    = "cars-tab6";
        }

        // TYP SELECTED
        if ($type === "modif") {
            $typ_id = $value;
            $title  = $this->getTypIdText($typ_id);
            $nav    = "modif";
            $tab    = "cars-tab6";
        }

        if ($n === 0) {
            $list = "
            <div style=\"margin: 30px auto;\">{nothing_found}</div>";
        }

        $list = $this->replaceLang($list);

        return array($list, $title, $nav, $tab, $skip);
    }

    public function getBodyCarImage($mod_id): array
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `BODY_ID` FROM `T_types` WHERE `TYP_MOD_ID` = $mod_id LIMIT 1;");
        $body_id = $db->result($r, 0, "BODY_ID") + 0;

        $r = $db->query("SELECT `LOGO`, `TYPE_BODY` FROM `T_types_body_car` WHERE `BODY_ID` = $body_id AND `LANG_ID` = 16 LIMIT 1;");
        $image  = $db->result($r, 0, "LOGO");
        $name   = $db->result($r, 0, "TYPE_BODY");
        $path   = "https://toko.ua/uploads/images/body-types/$image";

        return array($name, $path);
    }

    public function getModIdText($mod_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEX_TEXT` FROM `T_models` WHERE `MOD_ID` = $mod_id LIMIT 1;");

        return $db->result($r, 0, "TEX_TEXT");
    }

    public function getTypIdText($typ_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TYP_TEXT` FROM `T_types` WHERE `TYP_ID` = $typ_id LIMIT 1;");

        return $db->result($r, 0, "TYP_TEXT");
    }

    public function clearCarsBlock($sel_tab, $cur_tab): array
    {
        $sel_tab    = $this->getUrlNumber($sel_tab);
        $cur_tab    = $this->getUrlNumber($cur_tab);
        $disabled   = "cars-nav__item-disabled";
        $hidden     = "cars-nav__item-hidden";

        if ($sel_tab === ($cur_tab + 1)) {
            $disabled   = "";
            $hidden     = "";
        }

        switch ($sel_tab) {
            case "2":
            {
                $class  = $disabled;
                $text   = "{cars_model}";
                break;
            }
            case "3":
            {
                $class  = $disabled;
                $text   = "{cars_year}";
                break;
            }
            case "4":
            {
                $class  = "$disabled $hidden";
                $text   = "{cars_body}";
                break;
            }
            case "5":
            {
                $class  = "$disabled $hidden";
                $text   = "{cars_engine}";
                break;
            }
            case "6":
            {
                $class  = "$disabled $hidden";
                $text   = "{cars_modification}";
                break;
            }
            case "1":
            default:
            {
                $class  = "";
                $text   = "{cars_manufacturer}";
                break;
            }
        }

        $text = $this->replaceLang($text);

        return array($class, $text);
    }

    /*
     * Get User Garage selected car in Catalog
     * */
    public function getCarsGarage()
    {
        $automan = new AutoClass();
        $auto_typ_id = $this->getCookieAuto();
        list($manufacture, $model, $model_id) = $automan->getCarInfo($auto_typ_id);
        list($manufacture_cap, , $model_id_cap,) = $automan->getAutoDescr($manufacture, $model, $model_id, $auto_typ_id);
        $models_img = $automan->getAutoIMG($manufacture, $model, $model_id)["model_id_image"];
        $garage_btn = ($auto_typ_id !== "" && !($automan->checkUserGarage($auto_typ_id))) ? "btn-img-disabled" : "";

        $form = $this->getHtmlForm("garage/selected");
        $form = str_replace(array("{typ_id}", "{manufacture_cap}", "{model_id_cap}", "{typ_text}", "{models_img}", "{garage_button}", "{typ_id}"), array($auto_typ_id, $manufacture_cap, $model_id_cap, $automan->getGroupInfo($auto_typ_id), $models_img, $garage_btn, $auto_typ_id), $form);

        return $this->replaceLang($form);
    }

    // Modal Cars Form
    public function showCarsForm()
    {
        return $this->getCarsSearch();
    }

    // Modal Cars Form
    public function showCarsGarageForm(): array
    {
        $form = $this->getCarsSearch();
        $auto_typ_id = $this->getCookieAuto();
        $status = 0;

        if ($auto_typ_id !== "") {
            $status = 1;
        }

        return array($form, $status);
    }

}
