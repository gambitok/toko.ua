<?php

class ClientClass
{

    var $status_user = 1;
    var $status_user_retail = 145;
    var $default_client_id = 26;
    var $default_user = 0;
    var $default_tpoint = 2;
    var $default_currency = 1;
    var $default_client_category = 140;

    use Helper;
    use Variables;

    function getClient() {
        if ($_COOKIE["client_id"]!="") $_SESSION["client_id"] = $_COOKIE["client_id"];
        if ($_COOKIE["user"]!="") $_SESSION["user"] = $_COOKIE["user"];

        if ($_SESSION["client_id"]=="" || $_SESSION["client_id"]==0 || $_SESSION["client_id"]==NULL) {
            $_SESSION["client_id"] = $this->default_client_id;
            $_SESSION["user"] = $this->default_user;
        }

        $client_id = $_SESSION["client_id"];
        $user_id = $_SESSION["user"];

        $client_id = $this->getUrlNumber($client_id);
        $user_id = $this->getUrlNumber($user_id);

        return array($client_id, $user_id);
    }

    function getDefaultStorageID($tpoint_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `storage_id` FROM `T_POINT_STORAGE` WHERE `tpoint_id`='$tpoint_id' AND `default`=1 LIMIT 1;");
        $storage_id = $db->result($r, 0, "storage_id");
        return $storage_id;
    }

    function getClientByUser($user_id) { $db = DbSingleton::getDbm();
        $client_id = 0;
        $r = $db->query("SELECT `client_id` FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' AND `status`=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $client_id = $db->result($r,0,"client_id");
        return $client_id;
    }

    function getClientName($user_id, $client_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `name` FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' AND `client_id`='$client_id' AND `status`=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT `name` FROM `A_CLIENTS_USERS_RETAIL` WHERE `id`='$user_id' AND `client_id`='$client_id' AND `status`=$this->status_user_retail LIMIT 1;");
        }
        $name = $db->result($r, 0, "name");
        return $name;
    }

    function getClientWhere() {
        $user_id = $this->getUser();
        $cookie_id = $_COOKIE["session_id"];
        $user_id==0 ? $result="`cookie_id`='$cookie_id' AND `client_id`='0'" : $result="`client_id`='$user_id'";
        return $result;
    }

    // only for A_CLIENTS_USERS
    function getClientPriceList() { $db = DbSingleton::getDbm();
        list($client_id, $user_id) = $this->getClient();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' AND `client_id`='$client_id' AND `status`=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        $price_status = $db->result($r, 0, "price_status");
        $n==0 || $price_status==0 ? $status=false : $status=true;
        return $status;
    }

    // only for A_CLIENTS_USERS
    function getClientCheckList() { $db = DbSingleton::getDbm();
        list($client_id, $user_id) = $this->getClient();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' AND `client_id`='$client_id' AND `status`=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        $n==0 ? $status = false : $status = true;
        return $status;
    }

    function checkUnRegClient() {
        $user_id = $this->getUser();
        $user_id==0 ? $result = true : $result = false;
        return $result;
    }

    function getClientAutoGarage($client_id, $user_id) { $db=DbSingleton::getTokoDb();
        $where = "`client_id`='$client_id' AND `user_id`='$user_id'"; $typ_id = "";
        $r = $db->query("SELECT * FROM `AUTO_GARAGE` WHERE $where AND `status`=1 LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $typ_id = $db->result($r,0,"typ_id");
        return $typ_id;
    }

    // format phone for Authorization
    function formatPhone($phone) {
        $phone = $this->formatValidPhone($phone);
        $number = strlen($phone) - 9;
        if ($number>0) $phone = substr($phone, $number);
        $phone_arr = [];
        array_push($phone_arr, $phone);
        $format_phone = "0$phone"; array_push($phone_arr, "$format_phone");
        $format_phone = "80$phone"; array_push($phone_arr, "$format_phone");
        $format_phone = "380$phone"; array_push($phone_arr, "$format_phone");
        $format_phone = "+380$phone"; array_push($phone_arr, "$format_phone");
        $phone_list = "'".implode("','", $phone_arr)."'";
        return $phone_list;
    }

    function formatValidPhone($phone) {
        $phone = str_replace(str_split("()+- "), "", $phone);
        $phone = substr($phone, -10);
        return $phone;
    }

    function loginOrderClient($user_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' LIMIT 1;");

        $client_id = $db->result($r, 0, "client_id");
        $cash_id = $this->getClientCurrency($client_id);

        $_SESSION["user"] = $user_id;
        $_SESSION["client_id"] = $client_id;
        $_SESSION["currency"] = $cash_id;
        $_SESSION["tpoint"] = $this->getTpoint($client_id);
        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        setcookie("user", $user_id, time() + (86400 * 30), "/");
        setcookie("currency", $cash_id, time() + (86400 * 30), "/");
        setcookie("tpoint_id", $this->getTpoint($client_id), time() + (86400 * 30), "/");
        setcookie("auto_typ_id", $this->getClientAutoGarage($client_id, $user_id), time() + (86400 * 30), "/");

        return true;
    }

    function loginClient($phone, $password) { $db = DbSingleton::getDbm();
        $phone_list = $this->formatPhone($this->getUrlString($phone));
        $password = $this->getUrlString($password);

        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `pass`='$password' AND `phone` IN ($phone_list) AND `status`=$this->status_user LIMIT 1;"); $n = $db->num_rows($r); $n2 = 0;
        if ($n==0) {
            $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `pass`='$password' AND `phone` IN ($phone_list) AND `status`=$this->status_user_retail LIMIT 1;"); $n2 = $db->num_rows($r);
        }

        $n==0 && $n2==0 ? $user_id = false : $user_id = $db->result($r, 0, "id");
        $client_id = $db->result($r, 0, "client_id");
        $cash_id = $this->getClientCurrency($client_id);

        $_SESSION["user"] = $user_id;
        $_SESSION["client_id"] = $client_id;
        $_SESSION["currency"] = $cash_id;
        $_SESSION["tpoint"] = $this->getTpoint($client_id);
        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        setcookie("user", $user_id, time() + (86400 * 30), "/");
        setcookie("currency", $cash_id, time() + (86400 * 30), "/");
        setcookie("tpoint_id", $this->getTpoint($client_id), time() + (86400 * 30), "/");
        setcookie("auto_typ_id", $this->getClientAutoGarage($client_id, $user_id), time() + (86400 * 30), "/");
        $this->moveFromBasketToClient();
        return $user_id;
    }

    function logoutClient() {
        $_SESSION["client_id"] = $this->default_client_id;    // Retail Client
        $_SESSION["user"] = $this->default_user;              // Retail User
        $_SESSION["currency"] = $this->default_currency;      // UAH Currency
        $_SESSION["tpoint"] = $this->default_tpoint;          // KHM City
        setcookie("tpoint_id", "", time() - 3600);
        setcookie("client_id", "", time() - 3600);
        setcookie("user", "", time() - 3600);
        setcookie("currency", $this->default_currency);
        setcookie("action_status", "", time() - 3600, "/");
        setcookie("auto_typ_id", "", time() - 3600, "/");
        return true;
    }

    function moveFromBasketToClient() { $db = DbSingleton::getTokoDb();
        $user_id = $this->getUser();
        $cookie = $_COOKIE["session_id"];
        $r = $db->query("SELECT * FROM `basket` WHERE `cookie_id`='$cookie' AND `client_id`=0;"); $n = $db->num_rows($r);
        if ($n>0) {
            $db->query("UPDATE `basket` SET `client_id`='$user_id' WHERE `cookie_id`='$cookie' AND `client_id`=0;");
            // need to add group with amount
        }
        return;
    }

    function getClientCity($client_id) { $db = DbSingleton::getDbm();
        $client_id = $this->getUrlNumber($client_id);
        $r = $db->query("SELECT `city` FROM `A_CLIENTS` WHERE `id`='$client_id';");
        $city = $db->result($r, 0, "city");
        return $city;
    }

    function getClientId($user_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `client_id` FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' LIMIT 1;");
        $client_id = $db->result($r, 0, "client_id");
        return $client_id;
    }

    function getClientInfo($client_id, $user_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT acu.name as user_name, acu.email as user_email, acu.phone as user_phone, acu.pass, acu.client_id, acu.status as user_status, ac.* 
        FROM `A_CLIENTS` ac
            LEFT OUTER JOIN `A_CLIENTS_USERS` acu ON (acu.client_id=ac.id)
        WHERE acu.id='$user_id' AND acu.client_id='$client_id' AND acu.status=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT acu.name as user_name, acu.email as user_email, acu.phone as user_phone, acu.pass, acu.status as user_status, acu.client_category, acu.client_id, ac.* 
            FROM `A_CLIENTS` ac
                LEFT OUTER JOIN `A_CLIENTS_USERS_RETAIL` acu ON (acu.client_id=ac.id)
            WHERE acu.id='$user_id' AND acu.client_id='$client_id' AND acu.status=$this->status_user_retail LIMIT 1;");
        }
        $phone = $db->result($r,0,"user_phone");
        $password = $db->result($r,0,"pass");
        $email = $db->result($r,0,"user_email");
        $name = $db->result($r,0,"user_name");
        $type = $db->result($r,0,"org_type");
        $country = $db->result($r,0,"country"); $country = $this->getCountryName($country);
        $region = $db->result($r,0,"state");
        $city = $db->result($r,0,"city"); $city = $this->getCityName($city);

        if ($user_id==0) {
            $name = "{not_chosen}";
        }
        return array("phone"=>$phone, "password"=>$password, "email"=>$email, "name"=>$name, "type"=>$type, "country"=>$country, "region"=>$region, "city"=>$city);
    }

    function getOrderInfo($client_id, $user_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT acu.name, acu.email, acu.phone, ac.city 
        FROM `A_CLIENTS` ac
            LEFT OUTER JOIN `A_CLIENTS_USERS` acu ON (acu.client_id=ac.id)
        WHERE acu.id='$user_id' AND acu.client_id='$client_id' AND acu.status=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT acu.name, acu.email, acu.phone, acu.city_id as city 
            FROM `A_CLIENTS` ac
                LEFT OUTER JOIN `A_CLIENTS_USERS_RETAIL` acu ON (acu.client_id=ac.id)
            WHERE acu.id='$user_id' AND acu.client_id='$client_id' AND acu.status=$this->status_user_retail LIMIT 1;");
        }
        $phone = $db->result($r, 0, "phone");
        $email = $db->result($r, 0, "email");
        $name = $db->result($r, 0, "name");
        $city = $db->result($r, 0, "city");
        return array("phone"=>$phone, "email"=>$email, "name"=>$name, "city"=>$city);
    }

    function updateProfile($phone, $pass, $email, $name) { $db = DbSingleton::getDbm();
        $phone = $this->getUrlString($phone);
        $phone = $this->formatValidPhone($phone);
        $pass = $this->getUrlString($pass);
        $email = $this->getUrlString($email);
        $name = $this->getUrlString($name);
        list($client_id, $user_id) = $this->getClient();
        $db->query("UPDATE `A_CLIENTS_USERS` SET `phone`='$phone', `pass`='$pass', `email`='$email', `name`='$name' WHERE `id`='$user_id' AND `client_id`='$client_id';");
        $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `phone`='$phone', `pass`='$pass', `email`='$email', `name`='$name' WHERE `id`='$user_id' AND `client_id`='$client_id';");
        return true;
    }

    function saveRegistration($phone, $pass, $email, $name, $client_category, $city_id, $tpoint_id, $mailing) { $db = DbSingleton::getDbm();
        $phone = $this->formatValidPhone($this->getUrlString($phone));
        $pass = $this->getUrlString($pass);
        $email = $this->getUrlString($email);
        $name = $this->getUrlString($name);
        $client_category = $this->getUrlString($client_category);
        $city_id = $this->getUrlNumber($city_id);
        $tpoint_id = $this->getUrlNumber($tpoint_id);
        $mailing = $this->getUrlNumber($mailing); $mailing ? $mailing=1 : $mailing=0;
        $client_id = $this->getClientByTpoint($tpoint_id);
        $date = date("Y-m-d H:i:s");
        list($region, $state, $country) = $this->getLocationCity($city_id);
        if ($client_category=="") $client_category = 140;

        // REGISTRATION AS CLIENT
        if ($client_category==140) {
            $this->addRetailClient($client_id, $phone, $name, $city_id, $email, $pass, 140);
        }

        // REGISTRATION AS RETAIL
        else {
            $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone`='$phone' LIMIT 1;"); $n = $db->num_rows($r);
            if ($n==0) {
                $db->query("INSERT INTO `A_CLIENTS_USERS_RETAIL` (`name`, `email`, `phone`, `pass`, `client_id`, `client_category`, `data`, `country_id`, `state_id`, `region_id`, `city_id`, `mailing`, `status`) 
                VALUES ('$name', '$email', '$phone', '$pass', $client_id, '$client_category', '$date', $country, $state, $region, $city_id, $mailing, $this->status_user_retail);");
            } else {
                $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `pass`='$pass', `email`='$email', `name`='$name' WHERE `phone`='$phone' LIMIT 1;");
            }
        }

        return true;
    }

    function regClientRetail($tpoint_id, $name, $phone, $city, $email, $category=0) { $db = DbSingleton::getDbm();
        if ($category==0) $category = $this->default_client_category;
        $phone = $this->formatValidPhone($this->getUrlString($phone));
        $tpoint_id = $this->getUrlNumber($tpoint_id);
        $name = $this->getUrlString($name);
        $city = $this->getUrlString($city);
        $email = $this->getUrlString($email);
        $category = $this->getUrlString($category);
        $client_id = $this->getClientByTpoint($tpoint_id);
        $date = date("Y-m-d H:i:s");
        list($region, $state, $country)=$this->getLocationCity($city); if ($category=="") $category=140;
        $pass = $this->randomPassword();

        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone`='$phone' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $db->query("INSERT INTO `A_CLIENTS_USERS_RETAIL` (`name`, `email`, `phone`, `pass`, `country_id`, `state_id`, `region_id`, `city_id`, `client_id`, `data`, `status`, `client_category`) 
            VALUES ('$name', '$email', '$phone', '$pass', '$country', '$state', '$region', '$city', $client_id, '$date', $this->status_user_retail, '$category');");
            $r = $db->query("SELECT MAX(`id`) as max_client FROM `A_CLIENTS_USERS_RETAIL`;");
            $max = intval($db->result($r, 0, "max_client"));
        } else {
            $max = intval($db->result($r, 0, "id"));
        }

        return $max;
    }

    function saveClientRetail($client, $pass, $order_id, $name, $phone, $email) { $db = DbSingleton::getDbm();
        $phone = $this->getUrlString($phone);
        $phone = $this->formatValidPhone($phone);
        $client = $this->getUrlNumber($client);
        $pass = $this->getUrlString($pass);
        $order_id = $this->getUrlNumber($order_id);
        $name = $this->getUrlString($name);
        $email = $this->getUrlString($email);
        $pass!="" ?: $pass = $this->randomPassword();
        if ($phone!="") $update_phone = ", `phone`='$phone'"; else $update_phone = "";
        $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `pass`='$pass', `name`='$name' $update_phone, `email`='$email' WHERE `id`=$client;");
        $r = $db->query("SELECT `phone`, `pass`, `client_id` FROM `A_CLIENTS_USERS_RETAIL` WHERE `id`='$client';");
        $login = $db->result($r, 0, "phone");
        $password = $db->result($r, 0, "pass");
        $client_id = $db->result($r, 0, "client_id");
        $db->query("UPDATE `orders_new` SET `client_id`='$client_id', `client_user_id`='$client' WHERE `id`=$order_id;");
        return array($login, $password);
    }

    function getClientByTpoint($tpoint) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `client_id` FROM `T_POINT_CLIENTS_RETAIL` WHERE `tpoint_id`='$tpoint' AND `status`=1;");
        $client_id = $db->result($r,0,"client_id");
        return $client_id;
    }

    function getClientCurrency($client_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `cash_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$client_id' LIMIT 1;"); $n = $db->num_rows($r);
        $cash_id = $db->result($r,0,"cash_id");
        if ($n==0) $cash_id = $this->default_currency;
        return $cash_id;
    }

    function getTpoint($client_id = 0) { $db = DbSingleton::getDbm();
        if ($client_id==0) $client_id = $this->getClient()[0];
        $r = $db->query("SELECT `tpoint_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$client_id';");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        if ($tpoint_id=="" || $tpoint_id==0) $tpoint_id = $this->default_tpoint;
        return $tpoint_id;
    }

    function getTpointUser($client_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `tpoint_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$client_id';");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        if ($tpoint_id=="" || $tpoint_id==0) $tpoint_id = $this->default_tpoint;
        return $tpoint_id;
    }

    function setTpoint($tpoint_id) {
        $client_id = $this->getClientByTpoint($tpoint_id);
        $_SESSION["tpoint"] = $tpoint_id;
        $_SESSION["client_id"] = $client_id;
        setcookie("tpoint_id", $tpoint_id, time() + (86400 * 30), "/");
        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        return $tpoint_id;
    }

    function setTpointRetail() {
        $_SESSION["tpoint"]!="" ?: $_SESSION["tpoint"] = $this->default_tpoint;
        return true;
    }

    function getArticleStorageTPoint($storage_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `tpoint_id` FROM `T_POINT_STORAGE` WHERE `storage_id`='$storage_id' LIMIT 1;");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        $r = $db->query("SELECT `full_name` FROM `T_POINT` WHERE `id`='$tpoint_id' LIMIT 1;");
        $tpoint_name = $db->result($r, 0, "full_name");
        return $tpoint_name;
    }

    /*
     * checking user authorization in the system
     * Table: myparts_dba.`A_CLIENTS_USERS`, myparts_dba.`A_CLIENTS_USERS_RETAIL`
    */
    function checkRegClient($phone, $type = 0) { $db = DbSingleton::getDbm();
        $phone = $this->formatValidPhone($phone);
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `phone`='$phone' AND `status`=$this->status_user LIMIT 1;");
        $n = $db->num_rows($r); $n2 = 0;

        $client_phone = $db->result($r, 0, "phone");
        $client_pass = $db->result($r, 0, "pass");

        $n==0 && $n2==0 ? $res = false : $res = array($client_phone, $client_pass);
        if ($type==1) $res = false;

        // only for this user
        $user_id = $this->getUser();
        if ($user_id > 0) {
            $user_phone = $this->getClientPhone();
            if ($phone===$user_phone) $res = false;
        }

        return $res;
    }

    /*
     * check reg phone
     * */
    function checkRegistration($phone) { $db = DbSingleton::getDbm();
        $phone = $this->formatValidPhone($phone);
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `phone`='$phone' AND `status`=$this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n>0) return true; else return false;
    }

    /*
     * validation of phone numbers by Ukrainian operators
     * Table: toko_dba.`mobile_operators`
    */
    function validateOperator($phone) { $db = DbSingleton::getTokoDb();
        $result = false;
        $phone = $this->formatValidPhone($phone);
        $code = substr($phone, 0, 3);
        $r = $db->query("SELECT * FROM `mobile_operators` WHERE `OPERATOR_CODE`='$code' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $result = true;

        $user_id = $this->getUser();
        if ($user_id>0) {
            $user_phone = $this->getClientPhone();
            if ($phone!==$user_phone) $result = false;
        }

        return $result;
    }

    function getStorageByTpoint($tpoint_id) { $db = DbSingleton::getTokoDb();
        $storage_local = []; $storage_remote = [];
        $r = $db->query("SELECT `storage_id`, `local` FROM `T_POINT_STORAGE` WHERE `tpoint_id`='$tpoint_id' AND `status`=1;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $storage_id = $db->result($r, $i-1, "storage_id");
            $local = $db->result($r, $i-1, "local");
            $local=="41" ? array_push($storage_local, $storage_id) : array_push($storage_remote, $storage_id);
        }
        $storage_local = implode(",", $storage_local);
        $storage_remote = implode(",", $storage_remote);
        return array($storage_local, $storage_remote);
    }

    /*
     * select all tpoints except the specified one
     * Table: toko_dba.`T_POINT`
    */
    function getOtherTpoints($tpoint_id) { $db = DbSingleton::getTokoDb();
        $tpoint_array = [];
        $r = $db->query("SELECT `id` FROM `T_POINT` WHERE `status`=1 ORDER BY CASE WHEN `id`='$tpoint_id' THEN 0 ELSE 1 END;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $tpoint_id = $db->result($r, $i-1, "id");
            array_push($tpoint_array, $tpoint_id);
        }
        return $tpoint_array;
    }

    /*
     * getting tpoint address
     * Table: toko_dba.`T_POINT`
    */
    function getTPointAddress($tpoint_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `address` FROM `T_POINT` WHERE `id`='$tpoint_id' AND `status`=1 LIMIT 1;");
        $address = $db->result($r, 0, "address");
        return $address;
    }

    /*
     * getting city name by tpoint
     * Table: toko_dba.`T_POINT`
    */
    function getTPointCity($tpoint_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `city` FROM `T_POINT` WHERE `id`='$tpoint_id' AND `status`=1 LIMIT 1;");
        $city_id = $db->result($r, 0, "city");
        $city_name = $this->getCityName($city_id);
        return $city_name;
    }

    /*
     * getting storage address
     * Table: toko_dba.`STORAGE`
    */
    function getStorageAddress($storage_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `address` FROM `STORAGE` WHERE `id`='$storage_id' AND `status`=1 LIMIT 1;");
        $address = $db->result($r, 0, "address");
        return $address;
    }

    /*
     * getting city name by storage
     * Table: toko_dba.`STORAGE`
    */
    function getStorageCity($storage_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `city` FROM `STORAGE` WHERE `id`='$storage_id' AND `status`=1 LIMIT 1;");
        $city_id = $db->result($r, 0, "city");
        $city_name = $this->getCityName($city_id);
        return $city_name;
    }

    /*
     * get location variables by city
     * Table: myparts_dba.`T2_CITY`, myparts_dba.`T2_REGION`, myparts_dba.`T2_STATE`, myparts_dba.`T2_COUNTRIES`
     * */
    function getLocationCity($city_id) { $db = DbSingleton::getDbm();
        $region_id = 0; $state_id = 0; $country_id = 0;
        if ($city_id>0) {
            $r = $db->query("SELECT t2r.REGION_ID, t2s.STATE_ID, t2ct.COUNTRY_ID 
            FROM `T2_CITY` t2c
                LEFT OUTER JOIN `T2_REGION` t2r on t2r.REGION_ID=t2c.REGION_ID 
                LEFT OUTER JOIN `T2_STATE` t2s on t2s.STATE_ID=t2r.STATE_ID 
                LEFT OUTER JOIN `T2_COUNTRIES` t2ct on t2ct.COUNTRY_ID=t2s.COUNTRY_ID 
            WHERE t2c.CITY_ID='$city_id';");
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
    function recoverPassword($phone) { $db = DbSingleton::getDbm(); $dbt = DbSingleton::getTokoDb();
        $phone = $this->formatValidPhone($phone);
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `phone`='$phone' AND `status`=$this->status_user LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT * FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone`='$phone' AND `status`=$this->status_user_retail LIMIT 1;");
        }
        $password = $db->result($r, 0, "pass");
        $message = "Vash login: $phone, vash parol: $password. Spasibo, chto Vy s nami! (www.toko.ua)";
        $dbt->query("INSERT INTO `sms_journal` (`phone`, `sign`, `message`, `status`) VALUES ('$phone', 'TOKO.UA', '$message', '1');");
        $list = "<div class=\"col-12\">{sms_sent}</div>";
        $list = $this->replaceLang($list);
        return $list;
    }

    function validatePhone($phone) { $db = DbSingleton::getDbm(); $dbt = DbSingleton::getTokoDb();
        $password = rand(1000, 9999); $message = "Vvedite kod: $password";
        $db->query("INSERT INTO `phone_validation` (`phone`, `password`, `status`) VALUES ('$phone', '$password', '0');");
        $dbt->query("INSERT INTO `sms_journal` (`phone`, `sign`, `message`, `status`) VALUES ('$phone', 'TOKO.UA', '$message', '1');");
        return $password;
    }

    function endValidation($phone, $password) { $db = DbSingleton::getDbm();
        $phone = $this->formatValidPhone($phone);
        $r = $db->query("SELECT * FROM `phone_validation` WHERE `phone`='$phone' AND `password`='$password' AND `status`=0;"); $n = $db->num_rows($r);
        if ($n > 0) { $db->query("UPDATE `phone_validation` SET `status`=1 WHERE `phone`='$phone' AND `password`='$password' AND `status`=0;"); }
        $n > 0 ? $result = true : $result = false;
        return $result;
    }

    /*
     * Create CLIENT
     * Create WEB USER
     * Set CATEGORY
     * Set CONDITIONS
     * */
    function addRetailClient($tpoint_client_id, $phone, $name="", $city_id=0, $email="", $pass="", $category="") { $db = DbSingleton::getDbm();
        if ($name=="") $name = $phone;
        if ($pass=="") $pass = $this->randomPassword();
        if ($category=="") $category = $this->default_client_category;
        list($region_id, $state_id, $country_id) = $this->getLocationCity($city_id);
        $phone = $this->formatValidPhone($phone);

        $r = $db->query("SELECT MAX(`id`) as mid FROM `A_CLIENTS`;"); $client_id = 0 + $db->result($r,0,"mid") + 1;
        $db->query("INSERT INTO `A_CLIENTS` (`id`, `name`, `full_name`, `phone`, `email`, `country`, `state`, `region`, `city`, `client_category`, `rounding_price`) 
        VALUES ('$client_id', '$name', '$name', '$phone', '$email', '$country_id', '$state_id', '$region_id', '$city_id', '$category', 2);");

        $r = $db->query("SELECT MAX(`id`) as mid FROM `A_CLIENTS_USERS`;"); $user_id = 0 + $db->result($r,0,"mid") + 1;
        $db->query("INSERT INTO `A_CLIENTS_USERS` (`id`, `client_id`, `name`, `email`, `phone`, `pass`, `status`) 
        VALUES ('$user_id', '$client_id', '$name', '$email', '$phone', '$pass', 1);");

        $db->query("INSERT INTO `A_CLIENTS_CATEGORY` (`client_id`, `category_id`) VALUES ('$client_id', '1');");

        $this->moveClientsConditionsRetail($tpoint_client_id, $client_id);

        return array("client_id"=>$client_id, "user_id"=>$user_id);
    }

    /*
     * MOVE CLIENT CONDITION
     * */
    function moveClientsConditionsRetail($tpoint_client_id, $client_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$tpoint_client_id' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==1) {
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
     * getting the user type
     * default / retail
     * */
    function checkRetailClient($client_id) {
        if ($client_id==10 || $client_id==26) return true; else return false;
    }

    /*
     * check client category
     * */
    function checkRetailClientCategory($client_id) { $db=DbSingleton::getDbm();
        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id`='$client_id' LIMIT 1;");
        $client_category = $db->result($r, 0, "client_category");
        if ($client_category==140) return true; else return false;
    }

    /*
     * Users Count
     * */
    function getUsersCount() { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `status`=1;");
        $count = $db->num_rows($r);
        return $count;
    }

    /*
     * setting the type of display of goods
     * Session: 'display_status'
     * */
    function toggleProductView($ds) {
        session_start();
        $_SESSION["display_status"] = $ds;
        if ($ds!=0 && $ds!=1) $_SESSION["display_status"] = 0;
        return $_SESSION["display_status"];
    }

    /*
     * getting the type of display of goods
     * Session: 'display_status'
     * */
    function getProductView() {
        session_start();
        $ds = $_SESSION["display_status"];
        if ($ds!=0 && $ds!=1) $_SESSION["display_status"] = 0;
        return $_SESSION["display_status"];
    }

    /*
     * checking clients action status
     * Table: myparts_dba.`ACTION_CLIENTS_CATEGORY`
     * Cookie: 'action_status'
     * */
    function checkActionClients() { $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
        $categories = [];
        $r = $db->query("SELECT `category_id` FROM `ACTION_CLIENTS_CATEGORY`;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $category_id = $db->result($r, $i-1, "category_id");
            array_push($categories, $category_id);
        }
        $categories = implode(",", $categories);
        $r = $db->query("SELECT * FROM `A_CLIENTS` WHERE `id`='$user_id' AND `client_category` IN ($categories);"); $n = $db->num_rows($r);
        setcookie("action_status", "1", time() + (86400 * 30), "/");
        return $n;
    }

    /*
     * getting a rounded price value
     * Table: myparts_dba.`A_CLIENTS
     * */
    function getClientPriceRounding($client_id, $price) { $db = DbSingleton::getDbm();
        if ($client_id>0) {
            $r = $db->query("SELECT `rounding_price` FROM `A_CLIENTS` WHERE `id`='$client_id';"); $n = $db->num_rows($r);
            if ($n>0) {
                $rounding_price = $db->result($r, 0, "rounding_price");
                if ($rounding_price==1) $price = round($price * 100, -1) / 100;
                if ($rounding_price==2) $price = round($price);
            }
        }
        return $price;
    }

    /*
     * Check if user authorized
     * by PHONE
     * */
    function getAuthorizedUser($phone) { $db = DbSingleton::getDbm();
        $user_id = 0; $client_id = 0; $status = false;
        $phone = $this->formatValidPhone($phone);
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `phone`='$phone' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) {
            $user_id = $db->result($r, 0, "id");
            $client_id = $db->result($r, 0, "client_id");
        }
        if ($client_id>0) { // found client-phone
            if (!$this->checkRetailClientCategory($client_id)) { // found client-shop
                $status = true;
            }
        }
        return array($status, $user_id);
    }

    /*
     * get user phone
     * */
    function getClientPhone() { $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
        if ($user_id>0) {
            $r = $db->query("SELECT `phone` FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' LIMIT 1;");
            $phone = $db->result($r, 0, "phone");
        } else {
            $phone = "";
        }
        return $phone;
    }

    /*
     * get user history
     * */
    function getClientHistory() { $db = DbSingleton::getTokoDb();
        $history = []; $col = 0;
        $cookie = $_COOKIE["session_id"]; if ($cookie=="" || $cookie==NULL) $cookie = 0;
        list($client_id, $user_id) = $this->getClient();
        if ($user_id==0) $where = "`cookie_id`='$cookie'"; else $where = "`client_id`='$client_id' AND `client_user_id`='$user_id'";
        $r = $db->query("SELECT * FROM `CLIENT_HISTORY` WHERE $where GROUP BY `art_id` ORDER BY `data` DESC LIMIT 10;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "id");
            $art_name = $db->result($r, $i-1, "article_nr_displ");
            $brand_id = $db->result($r, $i-1, "brand_id");
            $brand_link = $this->getBrandLink($brand_id);
            $art_name = strtoupper($art_name);
            $brand = $this->getBrandName($brand_id);
            if ($brand!="") {
                $history[$col] = ["id"=>$id, "article_nr_displ"=>$art_name, "brand_id"=>$brand_id, "brand"=>$brand, "brand_link"=>$brand_link];
                $col++;
            }
        }
        return $history;
    }

    function dropClient($client_id) { $db = DbSingleton::getDbm();
        $db->query("DELETE FROM `A_CLIENTS` WHERE `id`='$client_id' LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_USERS` WHERE `client_id`='$client_id' LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_CATEGORY` WHERE `client_id`='$client_id' LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$client_id' LIMIT 1;");
        return "deleted client: #$client_id";
    }

    function getClientMarkupMin($client_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `markup_min` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id`='$client_id' LIMIT 1;");
        $markup_min = $db->result($r, 0, "markup_min");
        return $markup_min;
    }

    /*
     * Get User Data + User Order Info
     * */
    function getClientUserData($user_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' LIMIT 1;");
        $user_name = $db->result($r, 0, "name");
        $user_phone = $db->result($r, 0, "phone");
        $user_email = $db->result($r, 0, "email");
        $user_city = 0;
        $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `USER_ID`='$user_id' ORDER BY `ID` DESC LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $user_city = $db->result($r, 0, "CITY_ID");
        return array($user_name, $user_phone, $user_email, $user_city);
    }

    /*
     * CLIENT Requests
     * T2_QUESTIONS
     * */
    function setClientRequest($phone, $vin = "", $text = "") { $db = DbSingleton::getTokoDb();
        $data_create = date("Y-m-d H:i:s");
        $phone = $this->formatValidPhone($phone);
        if ($phone=="") {
            return false;
        } else {
            $db->query("INSERT INTO `T2_QUESTIONS` (`PHONE`, `VIN`, `TEXT` , `DATA_CREATE`) VALUES ('$phone', '$vin', '$text', '$data_create');");
            return true;
        }
    }

    /*
     * get client_id / user_id from PHONE
     * */
    function getClientUserbyPhone($phone) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS` WHERE `phone`='$phone' LIMIT 1;");
        $user_id = $db->result($r, 0, "id");
        $client_id = $db->result($r, 0, "client_id");
        return array("user_id"=>$user_id, "client_id"=>$client_id);
    }

    /*//
     * Client Bonus
     * */
    function checkClientBonus($client_id, $bonus = 1) { $db = DbSingleton::getDbm();
        $status = false;
        $r = $db->query("SELECT * FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID`='$client_id' AND `BONUS_ID`='$bonus' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n > 0) $status = true;
        return $status;
    }

    function addClientBonus($client_id, $bonus = 1) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID`='$client_id' AND `BONUS_ID`='$bonus' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n == 0) {
            $db->query("INSERT INTO `T2_BONUS_CLIENT` (`CLIENT_ID`, `BONUS_ID`) VALUES ('$client_id', '$bonus');");
        }
        return true;
    }

    function setClientBonus($client_id, $bonus = 1) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `SUMM` FROM `T2_BONUS` WHERE `ID`='$bonus' LIMIT 1;"); $n = $db->num_rows($r);
        $sum = $db->result($r, 0, "SUMM");
        if ($n > 0) {
            $db->query("UPDATE `A_CLIENTS` SET `bonus_balance` = `bonus_balance` + $sum WHERE `id`='$client_id' LIMIT 1;");
        }
        return $sum;
    }

    function getBonusCap($bonus = 1) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `T2_BONUS` WHERE `ID`='$bonus' LIMIT 1;");
        $text = $db->result($r, 0, "TEXT");
        return $text;
    }

    function finishBonusPhone($phone, $bonus = 1) {
        $client = new ClientClass;
        $form = $this->getHtmlForm("bonus/phone_done");

        // check reg CLIENT
        if ($client->checkRegClient($phone)) {
            $client_status = "already reg";
            // get CLIENT
            $clientData = $client->getClientUserbyPhone($phone);
            $client_id = $clientData["client_id"];
            $user_id = $clientData["user_id"];
            // check if roznica
            if ($client->checkRetailClientCategory($client_id)) {
                // check if have BONUS already
                if (!$client->checkClientBonus($client_id, $bonus)) {
                    // add BONUS
                    $client->addClientBonus($client_id, $bonus);
                } else {
                    $client_status = "already with bonus ):";
                }
            } else {
                $client_status = "already ne roznica ):";
            }
        } else {
            $client_status = "new client";
            // reg CLIENT
            $clientData = $this->addRetailClient($this->getClient(), $phone);
            $client_id = $clientData["client_id"];
            $user_id = $clientData["user_id"];
            // add BONUS
            $client->addClientBonus($client_id, $bonus);
        }

        // set BONUS
        $bonus_cap = $this->getBonusCap($bonus);
        $bonus_sum = $this->setClientBonus($client_id, $bonus);

        $form = str_replace("{bonus_cap}", $bonus_cap, $form);
        $form = str_replace("{bonus_summ}", $bonus_sum, $form);
        $form = str_replace("{client_status}", $client_status, $form);
        $form = str_replace("{user_id}", $user_id, $form);
        $form = str_replace("{client_id}", $client_id, $form);
        $form = str_replace("{bonus_phone}", $phone, $form);

        $form = $this->replaceLang($form);

        return $form;
    }

}