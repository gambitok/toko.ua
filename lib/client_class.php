<?php

class ClientClass
{

    use Helper;
    use Variables;

    public $status_user = 1;
    public $status_user_retail = 145;
    public $default_client_id = 26;
    public $default_user = 0;
    public $default_tpoint = 2;
    public $default_currency = 1;
    public $default_client_category = 140;
    public $vin_len = 17;
    public $max_history_count = 10;
    public $default_lang_id = 1;

    /*
     * get client data
     * */
    public function getClientData()
    {
        $cookie_client_id = $this->getUrlNumber($_COOKIE["client_id"]);
        if ($cookie_client_id != "") {
            $_SESSION["client_id"] = $cookie_client_id;
        }

        $cookie_user_id = $this->getUrlNumber($_COOKIE["user_id"]);
        if ($cookie_user_id != "") {
            $_SESSION["user_id"] = $cookie_user_id;
        }

        $session_client_id = $this->getUrlNumber($_SESSION["client_id"]);
        if ($session_client_id == "" || $session_client_id == 0 || $session_client_id == NULL) {
            $_SESSION["client_id"] = $this->default_client_id;
            $_SESSION["user_id"] = $this->default_user;
        }

        $client_id = $_SESSION["client_id"];
        $user_id = $_SESSION["user_id"];

        $client_id = $this->getUrlNumber($client_id);
        $user_id = $this->getUrlNumber($user_id);

        return array($client_id, $user_id);
    }

    /*
     * get default storage from t_point_id
     * */
    public function getDefaultStorageID($tpoint_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `storage_id` FROM `T_POINT_STORAGE` WHERE `tpoint_id` = $tpoint_id AND `default` = 1 LIMIT 1;");
        return $db->result($r, 0, "storage_id");
    }

    /*
     * get client from user_id
     * */
    public function getClientByUser($user_id)
    {
        $db = DbSingleton::getDbm();
        $client_id = 0;
        $r = $db->query("SELECT `client_id` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $client_id = $db->result($r, 0, "client_id");
        }
        return $client_id;
    }

    /*
     * get Client Name
     * */
    public function getClientName($user_id, $client_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `name` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            $r = $db->query("SELECT `name` FROM `A_CLIENTS_USERS_RETAIL` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user_retail LIMIT 1;");
        }
        return $db->result($r, 0, "name");
    }

    /*
     * get Client where (cookie and client_id)
     * */
    public function getClientWhere()
    {
        $user_id = $this->getUser();
        $cookie_id = $this->getSessionID();
        return ($user_id == 0) ? "`cookie_id` = '$cookie_id' AND `client_id` = 0" : "`client_id` = $user_id";
    }

    /*
     * only for A_CLIENTS_USERS
     * */
    public function getClientPriceList()
    {
        $db = DbSingleton::getDbm();
        list($client_id, $user_id) = $this->getClientData();
        $r = $db->query("SELECT `price_status` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        $price_status = $db->result($r, 0, "price_status");
        return !(($n == 0 || $price_status == 0));
    }

    /*
     * only for A_CLIENTS_USERS
     * */
    public function getClientCheckList()
    {
        $db = DbSingleton::getDbm();
        list($client_id, $user_id) = $this->getClientData();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        return !(($n == 0));
    }

    /*
     * check client registration
     * */
    public function checkUnRegClient()
    {
        $user_id = $this->getUser();
        return ($user_id == 0);
    }

    /*
     * get auto from garage
     * */
    public function getClientAutoGarage($client_id, $user_id)
    {
        $db = DbSingleton::getTokoDb();
        $typ_id = "";
        $where = "`client_id` = $client_id AND `user_id` = $user_id";
        $r = $db->query("SELECT `typ_id` FROM `AUTO_GARAGE` WHERE $where AND `status` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $typ_id = $db->result($r, 0, "typ_id");
        }
        return $typ_id;
    }

    /*
     * format phone for Authorization
     * */
    public function formatPhone($phone)
    {
        $phone = $this->formatValidPhone($phone);
        $number = strlen($phone) - 9;
        if ($number > 0) {
            $phone = substr($phone, $number);
        }
        $phone_arr = [];
        array_push($phone_arr, $phone);
        $format_phone = "0$phone";
        array_push($phone_arr, "$format_phone");
        $format_phone = "80$phone";
        array_push($phone_arr, "$format_phone");
        $format_phone = "380$phone";
        array_push($phone_arr, "$format_phone");
        $format_phone = "+380$phone";
        array_push($phone_arr, "$format_phone");
        return "'" . implode("','", $phone_arr) . "'";
    }

    /*
     * valid phone number
     * */
    public function formatValidPhone($phone)
    {
        $phone = str_replace(str_split("()+- "), "", $phone);
        $phone = substr($phone, -10);
        return $this->getUrlNumber($phone);
    }

    /*
     * login client cookies
     * */
    public function loginOrderClient($user_id)
    {
        $user_id = $this->getUrlNumber($user_id);
        $dbm = DbSingleton::getDbm();
        $r = $dbm->query("SELECT `client_id` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $client_id = $dbm->result($r, 0, "client_id");
        $cash_id = $this->getClientCurrency($client_id);
        $_SESSION["user_id"] = $user_id;
        $_SESSION["client_id"] = $client_id;
        $_SESSION["currency"] = $cash_id;
        $_SESSION["tpoint_id"] = $this->getTpoint($client_id);
        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        setcookie("user_id", $user_id, time() + (86400 * 30), "/");
        setcookie("currency", $cash_id, time() + (86400 * 30), "/");
        setcookie("tpoint_id", $this->getTpoint($client_id), time() + (86400 * 30), "/");
        setcookie("auto_typ_id", $this->getClientAutoGarage($client_id, $user_id), time() + (86400 * 30), "/");
        return $this->getSiteLink() . "profile/orders/";
    }

    /*
     * login client profile
     * */
    public function loginClient($phone, $password)
    {
        $phone_list = $this->formatPhone($this->getUrlString($phone));
        $password = $this->getUrlString($password);
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `pass` = '$password' AND `phone` IN ($phone_list) AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        $n2 = 0;
        if ($n == 0) {
            $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `pass` = '$password' AND `phone` IN ($phone_list) AND `status` = $this->status_user_retail LIMIT 1;");
            $n2 = $db->num_rows($r);
        }

        $user_id = ($n == 0 && $n2 == 0) ? false : $db->result($r, 0, "id");
        $client_id = $db->result($r, 0, "client_id");
        $cash_id = $this->getClientCurrency($client_id);

        $_SESSION["user_id"] = $user_id;
        $_SESSION["client_id"] = $client_id;
        $_SESSION["currency"] = $cash_id;
        $_SESSION["tpoint_id"] = $this->getTpoint($client_id);
        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        setcookie("user_id", $user_id, time() + (86400 * 30), "/");
        setcookie("currency", $cash_id, time() + (86400 * 30), "/");
        setcookie("tpoint_id", $this->getTpoint($client_id), time() + (86400 * 30), "/");
        setcookie("auto_typ_id", $this->getClientAutoGarage($client_id, $user_id), time() + (86400 * 30), "/");
        $this->moveFromBasketToClient();

        return $user_id;
    }

    /*
     * logout client profile
     * */
    public function logoutClient()
    {
        $_SESSION["client_id"] = $this->default_client_id;
        $_SESSION["user_id"] = $this->default_user;
        $_SESSION["tpoint_id"] = $this->default_tpoint;
        $_SESSION["currency"] = $this->default_currency;
        $_SESSION["lang_id"] = $this->default_lang_id;

        setcookie("client_id", "", time() - 3600);
        setcookie("user_id", "", time() - 3600);
        setcookie("tpoint_id", $this->default_tpoint, time() - 3600);
        setcookie("currency", $this->default_currency);
        setcookie("lang_id", $this->default_lang_id);

        setcookie("action_status", "", time() - 3600, "/");
        setcookie("auto_typ_id", "", time() - 3600, "/");
        return true;
    }

    /*
     * drop basket in order
     * */
    public function moveFromBasketToClient()
    {
        $db = DbSingleton::getTokoDb();
        $user_id = $this->getUser();
        $cookie = $this->getSessionID();
        $r = $db->query("SELECT * FROM `basket` WHERE `cookie_id` = '$cookie' AND `client_id` = 0;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $db->query("UPDATE `basket` SET `client_id` = $user_id WHERE `cookie_id` = '$cookie' AND `client_id` = 0;");
            // need to add group with amount
        }
        return true;
    }

    /*
     * get client info
     * */
    public function getClientInfo($client_id, $user_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT acu.name as user_name, acu.email as user_email, acu.phone as user_phone, acu.pass, acu.client_id, acu.status as user_status, ac.* 
        FROM `A_CLIENTS` ac
            LEFT OUTER JOIN `A_CLIENTS_USERS` acu ON (acu.client_id=ac.id)
        WHERE acu.id = $user_id AND acu.client_id = $client_id AND acu.status = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            $r = $db->query("SELECT acu.name as user_name, acu.email as user_email, acu.phone as user_phone, acu.pass, acu.status as user_status, acu.client_category, acu.client_id, ac.* 
            FROM `A_CLIENTS` ac
                LEFT OUTER JOIN `A_CLIENTS_USERS_RETAIL` acu ON (acu.client_id=ac.id)
            WHERE acu.id = $user_id AND acu.client_id = $client_id AND acu.status = $this->status_user_retail LIMIT 1;");
        }
        $phone = $db->result($r, 0, "user_phone");
        $password = $db->result($r, 0, "pass");
        $email = $db->result($r, 0, "user_email");
        $name = $db->result($r, 0, "user_name");
        $type = $db->result($r, 0, "org_type");
        $country = $db->result($r, 0, "country");
        $country = $this->getCountryName($country);
        $region = $db->result($r, 0, "state");
        $city = $db->result($r, 0, "city");
        $city = $this->getCityName($city);
        if ($user_id == 0) {
            $name = "{not_chosen}";
        }
        return compact("phone", "password", "email", "name", "type", "country", "region", "city");
    }

    /*
     * edit profile data
     * */
    public function updateProfile($phone, $pass, $email, $name)
    {
        $db = DbSingleton::getDbm();
        $phone = $this->getUrlString($phone);
        $phone = $this->formatValidPhone($phone);
        $pass = $this->getUrlString($pass);
        $email = $this->getUrlString($email);
        $name = $this->getUrlString($name);
        list($client_id, $user_id) = $this->getClientData();
        $db->query("UPDATE `A_CLIENTS_USERS` SET `phone` = '$phone', `pass` = '$pass', `email` = '$email', `name` = '$name' WHERE `id` = $user_id AND `client_id` = $client_id;");
        $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `phone` = '$phone', `pass` = '$pass', `email` = '$email', `name` = '$name' WHERE `id` = $user_id AND `client_id` = $client_id;");
        return true;
    }

    /*
     * save registration
     * */
    public function saveRegistration($phone, $pass, $email, $name, $client_category, $city_id, $tpoint_id, $mailing)
    {
        $db = DbSingleton::getDbm();
        $phone = $this->formatValidPhone($this->getUrlString($phone));
        $pass = $this->getUrlString($pass);
        $email = $this->getUrlString($email);
        $name = $this->getUrlString($name);
        $client_category = $this->getUrlString($client_category);
        $city_id = $this->getUrlNumber($city_id);
        $tpoint_id = $this->getUrlNumber($tpoint_id);
        $mailing = $this->getUrlNumber($mailing);
        $mailing = ($mailing) ? 1 : 0;
        $client_id = $this->getClientByTpoint($tpoint_id);
        $date = date("Y-m-d H:i:s");
        list($region, $state, $country) = $this->getLocationCity($city_id);
        if ($client_category == "") {
            $client_category = $this->default_client_category;
        }

        if ($client_category == $this->default_client_category) {
            // REGISTRATION AS CLIENT
            $this->addRetailClient($client_id, $phone, $name, $city_id, $email, $pass, $this->default_client_category);
        } else {
            // REGISTRATION AS RETAIL
            $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone` = '$phone' LIMIT 1;");
            $n = $db->num_rows($r);
            if ($n == 0) {
                $db->query("INSERT INTO `A_CLIENTS_USERS_RETAIL` (`name`, `email`, `phone`, `pass`, `client_id`, `client_category`, `data`, `country_id`, `state_id`, `region_id`, `city_id`, `mailing`, `status`) 
                VALUES ('$name', '$email', '$phone', '$pass', $client_id, '$client_category', '$date', $country, $state, $region, $city_id, $mailing, $this->status_user_retail);");
            } else {
                $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `pass` = '$pass', `email` = '$email', `name` = '$name' WHERE `phone` = '$phone' LIMIT 1;");
            }
        }
        return true;
    }

    /*
     * save Retail client
     * */
//    public function saveClientRetail($client_id_sel, $pass, $order_id, $name, $phone, $email)
//    {
//        $db = DbSingleton::getDbm();
//        $phone = $this->getUrlString($phone);
//        $phone = $this->formatValidPhone($phone);
//        $client_id_sel = $this->getUrlNumber($client_id_sel);
//        $pass = $this->getUrlString($pass);
//        $order_id = $this->getUrlNumber($order_id);
//        $name = $this->getUrlString($name);
//        $email = $this->getUrlString($email);
//        ($pass != "") ?: $pass = $this->randomPassword();
//        $update_phone = ($phone != "") ? ", `phone` = '$phone'" : "";
//        $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `pass` = '$pass', `name` = '$name' $update_phone, `email` = '$email' WHERE `id` = $client_id_sel;");
//        $r = $db->query("SELECT `phone`, `pass`, `client_id` FROM `A_CLIENTS_USERS_RETAIL` WHERE `id` = $client_id_sel;");
//        $login = $db->result($r, 0, "phone");
//        $password = $db->result($r, 0, "pass");
//        $client_id = $db->result($r, 0, "client_id");
//        $db->query("UPDATE `orders_new` SET `client_id` = $client_id, `client_user_id` = $client_id_sel WHERE `id` = $order_id;");
//        return array($login, $password);
//    }

    /*
     * get client from tpoint
     * */
    public function getClientByTpoint($tpoint_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `client_id` FROM `T_POINT_CLIENTS_RETAIL` WHERE `tpoint_id` = $tpoint_id AND `status` = 1;");
        return $db->result($r, 0, "client_id");
    }

    /*
     * get Client default currency
     * */
    public function getClientCurrency($client_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `cash_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");
        $n = $db->num_rows($r);
        $cash_id = $db->result($r, 0, "cash_id");
        if ($n == 0) {
            $cash_id = $this->default_currency;
        }
        return $cash_id;
    }

    /*
     * set TPOINT
     * */
    public function setTpoint($tpoint_id)
    {
        $tpoint_id = $this->getUrlNumber($tpoint_id);
        $client_id = $this->getClientByTpoint($tpoint_id);
        $_SESSION["tpoint_id"] = $tpoint_id;
        $_SESSION["client_id"] = $client_id;
        setcookie("tpoint_id", $tpoint_id, time() + (86400 * 30), "/");
        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        return $tpoint_id;
    }

    /*
     * get TPOINT
     * */
    public function getTpoint($client_id = 0)
    {
        $db = DbSingleton::getDbm();
        if ($client_id == 0) {
            $client_id = $this->getClientData()[0];
        }
        $r = $db->query("SELECT `tpoint_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id;");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        if ($tpoint_id == "" || $tpoint_id == 0) {
            $tpoint_id = $this->default_tpoint;
        }
        return $tpoint_id;
    }

    /*
     * get TPOINT from CLIENT
     * */
    public function getTpointUser($client_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `tpoint_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id;");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        if ($tpoint_id == "" || $tpoint_id == 0) {
            $tpoint_id = $this->default_tpoint;
        }
        return $tpoint_id;
    }

    /*
     * set default retail tpoint
     * */
    public function setTpointRetail()
    {
        ($_SESSION["tpoint_id"] != "") ?: $_SESSION["tpoint_id"] = $this->default_tpoint;
        return true;
    }

    /*
     * get tpoint name from storage_id
     * */
    public function getArticleStorageTPoint($storage_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `tpoint_id` FROM `T_POINT_STORAGE` WHERE `storage_id` = $storage_id LIMIT 1;");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        $r = $db->query("SELECT `full_name` FROM `T_POINT` WHERE `id` = $tpoint_id LIMIT 1;");
        return $db->result($r, 0, "full_name");
    }

    /*
     * checking user authorization in the system
     * Table: myparts_dba.`A_CLIENTS_USERS`, myparts_dba.`A_CLIENTS_USERS_RETAIL`
    */
    public function checkRegClient($phone, $type = 0)
    {
        $db = DbSingleton::getDbm();
        $phone = $this->formatValidPhone($phone);
        $type = $this->getUrlNumber($type);

        $r = $db->query("SELECT `client_id`, `phone`, `pass` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);

        $client_id = $db->result($r, 0, "client_id");
        $client_phone = $db->result($r, 0, "phone");
        $client_pass = $db->result($r, 0, "pass");

        $res = ($n == 0) ? false : array($client_phone, $client_pass);
        if ($type == 1) {
            $res = false;
        }

        if ($this->checkRetailClientCategory($client_id)) {
            $res = false;
        }

        // only for this user
        $user_id = $this->getUser();
        if ($user_id > 0) {
            $user_phone = $this->getClientPhone();
            if ($phone === $user_phone) {
                $res = false;
            }
        }

        return $res;
    }

    /*
     * check reg phone
     * */
    public function checkRegistration($phone)
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        return ($n > 0);
    }

    /*
     * validation of phone numbers by Ukrainian operators
     * Table: toko_dba.`mobile_operators`
    */
    public function validateOperator($phone)
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getTokoDb();
        $result = false;
        $code = substr($phone, 0, 3);
        $r = $db->query("SELECT * FROM `mobile_operators` WHERE `OPERATOR_CODE` = '$code' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $result = true;
        }
        $user_id = $this->getUser();
        if ($user_id > 0) {
            if ($phone !== $this->getClientPhone()) {
                $result = false;
            }
        }
        return $result;
    }

    /*
     * get storage_id from Tpoint
     * */
    public function getStorageByTpoint($tpoint_id)
    {
        $db = DbSingleton::getTokoDb();
        $storage_local = $storage_remote = [];
        $r = $db->query("SELECT `storage_id`, `local` FROM `T_POINT_STORAGE` WHERE `tpoint_id` = $tpoint_id AND `status` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $storage_id = $db->result($r, $i - 1, "storage_id");
            $local = $db->result($r, $i - 1, "local");
            ($local == "41") ? array_push($storage_local, $storage_id) : array_push($storage_remote, $storage_id);
        }
        $storage_local = implode(",", $storage_local);
        $storage_remote = implode(",", $storage_remote);
        return array($storage_local, $storage_remote);
    }

    /*
     * select all tpoints except the specified one
     * Table: toko_dba.`T_POINT`
    */
    public function getOtherTpoints($tpoint_id_sel)
    {
        $db = DbSingleton::getTokoDb();
        $tpoint_array = [];
        $r = $db->query("SELECT `id` FROM `T_POINT` WHERE `status` = 1 ORDER BY CASE WHEN `id` = $tpoint_id_sel THEN 0 ELSE 1 END;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $tpoint_id = $db->result($r, $i - 1, "id");
            array_push($tpoint_array, $tpoint_id);
        }
        return $tpoint_array;
    }

    /*
     * getting tpoint address
     * Table: toko_dba.`T_POINT`
    */
    public function getTPointAddress($tpoint_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `address` FROM `T_POINT` WHERE `id` = $tpoint_id AND `status` = 1 LIMIT 1;");
        return $db->result($r, 0, "address");
    }

    /*
     * getting city name by tpoint
     * Table: toko_dba.`T_POINT`
    */
    public function getTPointCity($tpoint_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `city` FROM `T_POINT` WHERE `id` = $tpoint_id AND `status` = 1 LIMIT 1;");
        $city_id = $db->result($r, 0, "city");
        return $this->getCityName($city_id);
    }

    /*
     * getting storage address
     * Table: toko_dba.`STORAGE`
    */
    public function getStorageAddress($storage_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `address` FROM `STORAGE` WHERE `id` = $storage_id AND `status` = 1 LIMIT 1;");
        return $db->result($r, 0, "address");
    }

    /*
     * getting city name by storage
     * Table: toko_dba.`STORAGE`
    */
    public function getStorageCity($storage_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `city` FROM `STORAGE` WHERE `id` = $storage_id AND `status` = 1 LIMIT 1;");
        $city_id = $db->result($r, 0, "city");
        return $this->getCityName($city_id);
    }

    /*
     * get location variables by city
     * Table: myparts_dba.`T2_CITY`, myparts_dba.`T2_REGION`, myparts_dba.`T2_STATE`, myparts_dba.`T2_COUNTRIES`
     * */
    public function getLocationCity($city_id)
    {
        $db = DbSingleton::getDbm();
        $region_id = $state_id = $country_id = 0;
        if ($city_id > 0) {
            $r = $db->query("SELECT t2r.REGION_ID, t2s.STATE_ID, t2ct.COUNTRY_ID 
            FROM `T2_CITY` t2c
                LEFT OUTER JOIN `T2_REGION` t2r ON (t2r.REGION_ID = t2c.REGION_ID) 
                LEFT OUTER JOIN `T2_STATE` t2s ON (t2s.STATE_ID = t2r.STATE_ID)
                LEFT OUTER JOIN `T2_COUNTRIES` t2ct ON (t2ct.COUNTRY_ID = t2s.COUNTRY_ID) 
            WHERE t2c.CITY_ID = $city_id;");
            $region_id = $db->result($r, 0, "REGION_ID");
            $state_id = $db->result($r, 0, "STATE_ID");
            $country_id = $db->result($r, 0, "COUNTRY_ID");
        }
        return array($region_id, $state_id, $country_id);
    }

    /*
     * user phone recovery
     * sending sms to user
     * Table: myparts_dba.`A_CLIENTS_USERS`, myparts_dba.`A_CLIENTS_USERS_RETAIL`
     * */
    public function recoverPassword($phone)
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone` = '$phone' AND `status` = $this->status_user_retail LIMIT 1;");
        }
        $password = $db->result($r, 0, "pass");
        $message = "Vash login: $phone, vash parol: $password. Spasibo, chto Vy s nami! (www.toko.ua)";
        $dbt->query("INSERT INTO `sms_journal` (`phone`, `sign`, `message`, `status`) VALUES ('$phone', 'TOKO.UA', '$message', '1');");
        $list = "<div class=\"col-12\">{sms_sent}</div>";
        $list = $this->replaceLang($list);
        return $list;
    }

    /*
     * send SMS validation
     * */
    public function validatePhone($phone)
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();
        $password = rand(1000, 9999);
        $message = "Vvedite kod: $password";
        $db->query("INSERT INTO `phone_validation` (`phone`, `password`, `status`) VALUES ('$phone', '$password', '0');");
        $dbt->query("INSERT INTO `sms_journal` (`phone`, `sign`, `message`, `status`) VALUES ('$phone', 'TOKO.UA', '$message', '1');");
        return $password;
    }

    /*
     * finish phone validation
     * */
    public function endValidation($phone, $password)
    {
        $phone = $this->formatValidPhone($phone);
        $password = $this->getNameString($password);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `phone_validation` WHERE `phone` = '$phone' AND `password` = '$password' AND `status` = 0;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $db->query("UPDATE `phone_validation` SET `status` = 1 WHERE `phone` = '$phone' AND `password` = '$password' AND `status` = 0;");
        }
        return ($n > 0);
    }

    /*
     * Create CLIENT
     * Create WEB USER
     * Set CATEGORY
     * Set CONDITIONS
     * */
    public function addRetailClient($tpoint_client_id, $phone, $name = "", $city_id = 0, $email = "", $pass = "", $category = "")
    {
        $db = DbSingleton::getDbm();
        if ($name == "") {
            $name = $phone;
        }
        if ($pass == "") {
            $pass = $this->randomPassword();
        }
        if ($category == "") {
            $category = $this->default_client_category;
        }
        list($region_id, $state_id, $country_id) = $this->getLocationCity($city_id);
        $phone = $this->formatValidPhone($phone);

        $r = $db->query("SELECT MAX(`id`) as mid FROM `A_CLIENTS`;");
        $client_id = 0 + $db->result($r, 0, "mid") + 1;
        $db->query("INSERT INTO `A_CLIENTS` (`id`, `name`, `full_name`, `phone`, `email`, `country`, `state`, `region`, `city`, `client_category`, `rounding_price`) 
        VALUES ('$client_id', '$name', '$name', '$phone', '$email', '$country_id', '$state_id', '$region_id', '$city_id', '$category', 2);");

        $r = $db->query("SELECT MAX(`id`) as mid FROM `A_CLIENTS_USERS`;");
        $user_id = 0 + $db->result($r, 0, "mid") + 1;
        $db->query("INSERT INTO `A_CLIENTS_USERS` (`id`, `client_id`, `name`, `email`, `phone`, `pass`, `status`) 
        VALUES ('$user_id', '$client_id', '$name', '$email', '$phone', '$pass', 1);");

        $db->query("INSERT INTO `A_CLIENTS_CATEGORY` (`client_id`, `category_id`) VALUES ('$client_id', '1');");

        $this->moveClientsConditionsRetail($tpoint_client_id, $client_id);

        return array("client_id" => $client_id, "user_id" => $user_id);
    }

    /*
     * MOVE CLIENT CONDITION
     * add new client from existing
     * */
    public function moveClientsConditionsRetail($tpoint_client_id, $client_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $tpoint_client_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 1) {
            $cash_id = $db->result($r, 0, "cash_id");
            $country_cash_id = $db->result($r, 0, "country_cash_id");
            $credit_cash_id = $db->result($r, 0, "credit_cash_id");
            $payment_delay = $db->result($r, 0, "payment_delay");
            $credit_limit = $db->result($r, 0, "credit_limit");
            $credit_return = $db->result($r, 0, "credit_return");
            $price_lvl = $db->result($r, 0, "price_lvl");
            $margin_price_lvl = $db->result($r, 0, "margin_price_lvl");
            $price_suppl_lvl = $db->result($r, 0, "price_suppl_lvl");
            $margin_price_suppl_lvl = $db->result($r, 0, "margin_price_suppl_lvl");
            $tpoint_id = $db->result($r, 0, "tpoint_id");
            $client_vat = $db->result($r, 0, "client_vat");
            $doc_type_id = $db->result($r, 0, "doc_type_id");
            $db->query("INSERT INTO `A_CLIENTS_CONDITIONS` (`client_id`, `cash_id`, `country_cash_id`, `credit_cash_id`, `payment_delay`, `credit_limit`, `credit_return`, `price_lvl`, `margin_price_lvl`, `price_suppl_lvl`, `margin_price_suppl_lvl`, `tpoint_id`, `client_vat`, `doc_type_id`) 
            VALUES ('$client_id', '$cash_id', '$country_cash_id', '$credit_cash_id', '$payment_delay', '$credit_limit', '$credit_return', '$price_lvl', '$margin_price_lvl', '$price_suppl_lvl', '$margin_price_suppl_lvl', '$tpoint_id', '$client_vat', '$doc_type_id');");
        }
        return true;
    }

    /*
     * check client category
     * check if client shop
     * */
    public function checkRetailClientCategory($client_id)
    {
        $db = DbSingleton::getDbm();
        $client_id = $this->getUrlNumber($client_id);
        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id` = $client_id LIMIT 1;");
        $client_category = $db->result($r, 0, "client_category");
        return ($client_category == $this->default_client_category);
    }

    /*
     * get all users count
     * */
    public function getUsersCount()
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `status` = 1;");
        return $db->num_rows($r);
    }

    /*
     * setting the type of display of goods
     * Session: 'display_status'
     * */
    public function toggleProductView($ds)
    {
        $ds = $this->getUrlNumber($ds);
        session_start();
        $_SESSION["display_status"] = $ds;
        if ($ds != 0 && $ds != 1) {
            $_SESSION["display_status"] = 0;
        }
        return $_SESSION["display_status"];
    }

    /*
     * getting the type of display of goods
     * Session: 'display_status'
     * 0: list view
     * 1: card view
     * */
    public function getProductView()
    {
        session_start();
        $ds = $_SESSION["display_status"];
        if ($ds != 0 && $ds != 1) {
            $_SESSION["display_status"] = 0;
        }
        return $_SESSION["display_status"];
    }

    /*
     * checking clients action status
     * Table: myparts_dba.`ACTION_CLIENTS_CATEGORY`
     * Cookie: 'action_status'
     * */
    public function checkActionClients()
    {
        $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
        $r = $db->query("SELECT * FROM `A_CLIENTS` WHERE `id` = $user_id AND `client_category` IN (SELECT `category_id` FROM `ACTION_CLIENTS_CATEGORY` WHERE 1);");
        $n = $db->num_rows($r);
        setcookie("action_status", "1", time() + (86400 * 30), "/");
        return $n;
    }

    /*
     * getting a rounded price value
     * Table: myparts_dba.`A_CLIENTS
     * */
    public function getClientPriceRounding($client_id, $price)
    {
        $db = DbSingleton::getDbm();
        if ($client_id > 0) {
            $r = $db->query("SELECT `rounding_price` FROM `A_CLIENTS` WHERE `id` = $client_id;");
            $n = $db->num_rows($r);
            if ($n > 0) {
                $rounding_price = $db->result($r, 0, "rounding_price");
                if ($rounding_price == 1) {
                    $price = round($price * 100, -1) / 100;
                }
                if ($rounding_price == 2) {
                    $price = round($price);
                }
            }
        }
        return $price;
    }

    /*
     * Check if user authorized
     * by PHONE
     * */
    public function getAuthorizedUser($phone)
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $user_id = $client_id = 0;
        $status = false;
        $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $user_id = $db->result($r, 0, "id");
            $client_id = $db->result($r, 0, "client_id");
        }
        // found client-phone
        if ($client_id > 0) {
            // found client-shop
            if (!$this->checkRetailClientCategory($client_id)) {
                $status = true;
            }
        }
        return array($status, $user_id);
    }

    /*
     * get user phone
     * */
    public function getClientPhone()
    {
        $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
        $phone = "";
        if ($user_id > 0) {
            $r = $db->query("SELECT `phone` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
            $phone = $db->result($r, 0, "phone");
        }
        return $phone;
    }

    /*
     * Add History
     * */
    public function insertHistory($article_nr_displ, $brand_id)
    {
        $db = DbSingleton::getTokoDb();
        session_start();
        $ses = session_id();
        $cookie = $this->getSessionID();
        $date = date("Y-m-d H:i:s");
        $client_id = $this->getClient();
        $user_id = $this->getUser();
        $art_id = $this->getArtID($article_nr_displ);
        if ($brand_id > 0) {
            $where = ($user_id == 0) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";
            $r = $db->query("SELECT COUNT(`id`) as kilk FROM `CLIENT_HISTORY` WHERE $where;");
            $k = $db->result($r, 0, "kilk");
            if ($k > $this->max_history_count) {
                $r = $db->query("SELECT `id` FROM `CLIENT_HISTORY` WHERE $where ORDER BY `data` ASC LIMIT 1;");
                $id = $db->result($r, 0, "id");
                $db->query("UPDATE `CLIENT_HISTORY` SET `data` = '$date', `article_nr_displ` = '$article_nr_displ', `brand_id` = $brand_id, `art_id` = $art_id WHERE `id` = $id;");
            } else {
                $r = $db->query("SELECT `id` FROM `CLIENT_HISTORY` WHERE $where AND `article_nr_displ` = '$article_nr_displ' AND `brand_id` = $brand_id;");
                $n = $db->num_rows($r);
                if ($n > 0) {
                    $db->query("UPDATE `CLIENT_HISTORY` SET `data` = '$date' WHERE $where AND `article_nr_displ` = '$article_nr_displ' AND `brand_id` = $brand_id;");
                } else {
                    $db->query("INSERT INTO `CLIENT_HISTORY` (`client_id`, `client_user_id`, `ses_id`, `cookie_id`, `article_nr_displ`, `brand_id`, `data`, `art_id`) 
                    VALUES ('$client_id', '$user_id', '$ses', '$cookie', '$article_nr_displ', '$brand_id', '$date', '$art_id');");
                }
            }
        }
        return true;
    }

    /*
     * get user history
     * */
    public function getClientHistory()
    {
        $db = DbSingleton::getTokoDb();
        $col = 0;
        $history = [];
        $cookie = $this->getSessionID();
        if ($cookie == "" || $cookie == NULL) {
            $cookie = 0;
        }
        list($client_id, $user_id) = $this->getClientData();
        $where = ($user_id == 0) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";
        $r = $db->query("SELECT `id`, `article_nr_displ`, `brand_id` FROM `CLIENT_HISTORY` WHERE $where GROUP BY `art_id` ORDER BY `data` DESC LIMIT 10;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "id");
            $article_nr_displ = $db->result($r, $i - 1, "article_nr_displ");
            $brand_id = $db->result($r, $i - 1, "brand_id");
            $brand_link = $this->getBrandLink($brand_id);
            $article_nr_displ = strtoupper($article_nr_displ);
            $brand_name = $this->getBrandName($brand_id);
            if ($brand_name != "") {
                $history[$col] =
                    [
                        "id" => $id,
                        "article_nr_displ" => $article_nr_displ,
                        "brand_id" => $brand_id,
                        "brand" => $brand_name,
                        "brand_link" => $brand_link
                    ];
                $col++;
            }
        }
        return $history;
    }

    /*
     * Delete Client
     * */
    public function dropClient($client_id)
    {
        $client_id = $this->getUrlNumber($client_id);
        $db = DbSingleton::getDbm();
        $db->query("DELETE FROM `A_CLIENTS` WHERE `id` = $client_id LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_USERS` WHERE `client_id` = $client_id LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_CATEGORY` WHERE `client_id` = $client_id LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");
        return "deleted client: #$client_id";
    }

    public function getClientMarkupMin($client_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `markup_min` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");
        return $db->result($r, 0, "markup_min");
    }

    /*
     * Get User Data + User Order Info
     * */
    public function getClientUserData($user_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `name`, `phone`, `email` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $user_name = $db->result($r, 0, "name");
        $user_phone = $db->result($r, 0, "phone");
        $user_email = $db->result($r, 0, "email");
        $r = $db->query("SELECT `CITY_ID` FROM `ORDERS_CLIENT_INFO` WHERE `USER_ID` = $user_id ORDER BY `ID` DESC LIMIT 1;");
        $n = $db->num_rows($r);
        $user_city = 0;
        if ($n > 0) {
            $user_city = $db->result($r, 0, "CITY_ID");
        }
        return array($user_name, $user_phone, $user_email, $user_city);
    }

    /*
     * CLIENT Requests
     * T2_QUESTIONS
     * */
    public function setClientRequest($phone, $vin = "", $text = "", $status = 0)
    {
        $phone = $this->formatValidPhone($phone);
        $vin = $this->getUrlNumber($vin);
        $text = $this->getNameString($text);
        $db = DbSingleton::getTokoDb();
        $data_create = date("Y-m-d H:i:s");
        if (($phone == "") || (strlen($vin) != $this->vin_len && $status == 1) || (!$this->validateOperator($phone))) {
            return false;
        } else {
            $db->query("INSERT INTO `T2_QUESTIONS` (`PHONE`, `VIN`, `TEXT` , `DATA_CREATE`) VALUES ('$phone', '$vin', '$text', '$data_create');");
            return true;
        }
    }

    /*
     * get client_id / user_id from PHONE
     * */
    public function getClientUserbyPhone($phone)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' LIMIT 1;");
        $user_id = $db->result($r, 0, "id");
        $client_id = $db->result($r, 0, "client_id");
        return array("user_id" => $user_id, "client_id" => $client_id);
    }

    /*
     * Client Bonus
     * */
    public function checkClientBonus($client_id, $bonus = 1)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID` = $client_id AND `BONUS_ID` = $bonus LIMIT 1;");
        $n = $db->num_rows($r);
        return ($n > 0);
    }

    /*
     * add Client bonus
     * if not exist
     * */
    public function addClientBonus($client_id, $bonus = 1)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID` = $client_id AND `BONUS_ID` = $bonus LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            $db->query("INSERT INTO `T2_BONUS_CLIENT` (`CLIENT_ID`, `BONUS_ID`) VALUES ($client_id, $bonus);");
        }
        return true;
    }

    /*
     * update client bonus
     * */
    public function setClientBonus($client_id, $bonus = 1)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `SUMM` FROM `T2_BONUS` WHERE `ID` = $bonus LIMIT 1;");
        $n = $db->num_rows($r);
        $sum = $db->result($r, 0, "SUMM");
        if ($n > 0) {
            $db->query("UPDATE `A_CLIENTS` SET `bonus_balance` = `bonus_balance` + $sum WHERE `id` = $client_id LIMIT 1;");
        }
        return $sum;
    }

    /*
     * set bouns on clients phone
     * if not used before
     * */
    public function finishBonusPhone($phone, $bonus = 1)
    {
        $phone = $this->formatValidPhone($phone);
        $bonus = $this->getUrlNumber($bonus);

        // check reg CLIENT
        if ($this->checkRegClient($phone)) {
            // get CLIENT
            $clientData = $this->getClientUserbyPhone($phone);
            $client_id = $clientData["client_id"];
            // check if roznica
            if ($this->checkRetailClientCategory($client_id)) {
                // check if have BONUS already
                if (!$this->checkClientBonus($client_id, $bonus)) {
                    // add BONUS
                    $this->addClientBonus($client_id, $bonus);
                }
            }
        } else {
            // reg CLIENT
            $clientData = $this->addRetailClient($this->getClientData(), $phone);
            $client_id = $clientData["client_id"];
            // add BONUS
            $this->addClientBonus($client_id, $bonus);
        }

        // set BONUS
        $bonus_sum = $this->setClientBonus($client_id, $bonus);

        $form = $this->getHtmlForm("bonus/phone_done");
        $form = str_replace("{bonus_summ}", $bonus_sum, $form);
        $form = str_replace("{bonus_phone}", $phone, $form);

        $form = $this->replaceLang($form);

        return $form;
    }

}