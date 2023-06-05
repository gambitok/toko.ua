<?php

class ClientClass
{

    use Helper;
    use Variables;

    public $status_user             = 1;
    public $status_user_retail      = 145;
    public $default_client_id       = 26;
//    public $default_client_id       = 10;
    public $default_user            = 0;
    public $default_tpoint          = 1;
    public $default_currency        = 1;
    public $default_client_category = 140;
    public $vin_len                 = 17;
    public $max_history_count       = 10;
    public $default_lang_id         = 1;

    /*
     * get client data
     * */
    public function getClientData(): array
    {
        $cookie_client_id = $this->getUrlNumber($_COOKIE["client_id"]);

        if ($cookie_client_id !== "") {
            $_SESSION["client_id"] = $cookie_client_id;
        }

        $cookie_user_id = $this->getUrlNumber($_COOKIE["user_id"]);

        if ($cookie_user_id !== "") {
            $_SESSION["user_id"] = $cookie_user_id;
        }

        $session_client_id = $this->getUrlNumber($_SESSION["client_id"]);

        if (empty($session_client_id)) {
            $_SESSION["client_id"]  = $this->default_client_id;
            $_SESSION["user_id"]    = $this->default_user;
        }

        $client_id  = $this->getUrlNumber($_SESSION["client_id"]);
        $user_id    = $this->getUrlNumber($_SESSION["user_id"]);

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

        if ($n === 0) {
            $r = $db->query("SELECT `name` FROM `A_CLIENTS_USERS_RETAIL` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user_retail LIMIT 1;");
        }

        return $db->result($r, 0, "name");
    }

    /*
     * get Client where (cookie and client_id)
     * */
    public function getClientWhere(): string
    {
        $user_id    = $this->getUser();
        $cookie_id  = $this->getSessionID();

        return (empty($user_id))
            ? "`cookie_id` = '$cookie_id' AND `client_id` = 0"
            : "`client_id` = $user_id";
    }

    /*
     * only for A_CLIENTS_USERS
     * */
    public function getClientPriceList(): bool
    {
        $db = DbSingleton::getDbm();
        list($client_id, $user_id) = $this->getClientData();

        $r = $db->query("SELECT `price_status` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        $price_status = (int)$db->result($r, 0, "price_status");

        return !(($n === 0 || empty($price_status)));
    }

    /*
     * only for A_CLIENTS_USERS
     * */
    public function getClientCheckList(): bool
    {
        $db = DbSingleton::getDbm();
        list($client_id, $user_id) = $this->getClientData();

        $r = $db->query("SELECT 1 FROM `A_CLIENTS_USERS` WHERE `id` = $user_id AND `client_id` = $client_id AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);

        return !(($n === 0));
    }

    /*
     * check client registration
     * */
    public function checkUnRegClient(): bool
    {
        $user_id = $this->getUser();

        return ($user_id === 0);
    }

    /*
     * get auto from garage
     * */
    public function getClientAutoGarage($client_id, $user_id)
    {
        $client_id = $this->getUrlNumber($client_id);
        $user_id = $this->getUrlNumber($user_id);
        $db = DbSingleton::getTokoDb();
        $typ_id = "";
        $where  = "`client_id` = $client_id AND `user_id` = $user_id";

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
    public function formatPhone($phone): string
    {
        $phone = $this->formatValidPhone($phone);
        $number = strlen($phone) - 9;

        if ($number > 0) {
            $phone = substr($phone, $number);
        }

        $phone_arr      = [];
        $phone_arr[]    = $phone;
        $format_phone   = "0$phone";
        $phone_arr[]    = $format_phone;
        $format_phone   = "80$phone";
        $phone_arr[]    = $format_phone;
        $format_phone   = "380$phone";
        $phone_arr[]    = $format_phone;
        $format_phone   = "+380$phone";
        $phone_arr[]    = $format_phone;

        return "'" . implode("','", $phone_arr) . "'";
    }

    /*
     * valid phone number
     * */
    public function formatValidPhone($phone)
    {
        $phone = str_replace(str_split("()+- "), "", $phone);
        $phone = substr($phone, -10);

        return $phone;
    }

    /*
     * login client cookies
     * */
    public function loginOrderClient($user_id): string
    {
        $user_id = $this->getUrlNumber($user_id);
        $dbm = DbSingleton::getDbm();
        $r = $dbm->query("SELECT `client_id` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $client_id = $dbm->result($r, 0, "client_id");
        $this->setSessionUserData($client_id, $user_id);

        return $this->getSiteLink() . "profile/orders/";
    }

    /*
     * login client profile
     * */
    public function loginClient($phone, $password)
    {
        $db = DbSingleton::getDbm();

        $phone_list = $this->formatPhone($this->getUrlString($phone));
        $password   = $this->getUrlString($password);

        $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS` WHERE `pass` = '$password' AND `phone` IN ($phone_list) AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        $n2 = 0;
        if ($n === 0) {
            $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS_RETAIL` WHERE `pass` = '$password' AND `phone` IN ($phone_list) AND `status` = $this->status_user_retail LIMIT 1;");
            $n2 = $db->num_rows($r);
        }

        $user_id    = ($n === 0 && $n2 === 0) ? false : $db->result($r, 0, "id");
        $client_id  = $db->result($r, 0, "client_id");

        $this->setSessionUserData($client_id, $user_id);

        $this->moveFromBasketToClient();

        return $user_id;
    }

    public function setSessionUserData($client_id, $user_id): bool
    {
        $cash_id                = $this->getClientCurrency($client_id);
        $_SESSION["user_id"]    = $user_id;
        $_SESSION["client_id"]  = $client_id;
        $_SESSION["currency"]   = $cash_id;
        $_SESSION["tpoint_id"]  = $this->getTpoint($client_id);

        setcookie("client_id", $client_id, time() + (86400 * 30), "/");
        setcookie("user_id", $user_id, time() + (86400 * 30), "/");
        setcookie("currency", $cash_id, time() + (86400 * 30), "/");
        setcookie("tpoint_id", $this->getTpoint($client_id), time() + (86400 * 30), "/");
        setcookie("auto_typ_id", $this->getClientAutoGarage($client_id, $user_id), time() + (86400 * 30), "/");

        return true;
    }

    /*
     * logout client profile
     * */
    public function logoutClient(): bool
    {
        $_SESSION["client_id"]  = $this->default_client_id;
        $_SESSION["user_id"]    = $this->default_user;
        $_SESSION["tpoint_id"]  = $this->default_tpoint;
        $_SESSION["currency"]   = $this->default_currency;
        $_SESSION["lang_id"]    = $this->default_lang_id;

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
    public function moveFromBasketToClient(): bool
    {
        $db = DbSingleton::getTokoDb();
        $user_id = $this->getUser();
        $cookie = $this->getSessionID();
        $r = $db->query("SELECT 1 FROM `basket` WHERE `cookie_id` = '$cookie' AND `client_id` = 0;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $db->query("UPDATE `basket` SET `client_id` = $user_id WHERE `cookie_id` = '$cookie' AND `client_id` = 0;");
            // need to add group with amount
        }

        return true;
    }

    /*
     * get country name
     * from COUNTRY_ID
     * */
    public function getCountryName($country_id)
    {
        $country_id = $this->getUrlNumber($country_id);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `COUNTRY_NAME` FROM `T2_COUNTRIES` WHERE `COUNTRY_ID` = $country_id LIMIT 1;");

        return $db->result($r, 0, "COUNTRY_NAME");
    }

    /*
     * get client info
     * */
    public function getClientInfo($client_id, $user_id): array
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT acu.name as user_name, acu.email as user_email, acu.phone as user_phone, acu.pass, acu.client_id, acu.status as user_status, ac.* 
        FROM `A_CLIENTS` ac
            LEFT OUTER JOIN `A_CLIENTS_USERS` acu ON (acu.client_id=ac.id)
        WHERE acu.id = $user_id AND acu.client_id = $client_id AND acu.status = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 0) {
            $r = $db->query("SELECT acu.name as user_name, acu.email as user_email, acu.phone as user_phone, acu.pass, acu.status as user_status, acu.client_category, acu.client_id, ac.* 
            FROM `A_CLIENTS` ac
                LEFT OUTER JOIN `A_CLIENTS_USERS_RETAIL` acu ON (acu.client_id=ac.id)
            WHERE acu.id = $user_id AND acu.client_id = $client_id AND acu.status = $this->status_user_retail LIMIT 1;");
        }

        $phone      = $db->result($r, 0, "user_phone");
        $password   = $db->result($r, 0, "pass");
        $email      = $db->result($r, 0, "user_email");
        $name       = $db->result($r, 0, "user_name");
        $type       = $db->result($r, 0, "org_type");
        $country    = $this->getCountryName($db->result($r, 0, "country"));
        $region     = $db->result($r, 0, "state");
        $city       = $this->getCityName($db->result($r, 0, "city"));

        if (empty($user_id)) {
            $name = "{not_chosen}";
        }

        return compact("phone", "password", "email", "name", "type", "country", "region", "city");
    }

    /*
     * edit profile data
     * */
    public function saveProfile($phone, $pass, $email, $name): bool
    {
        $db = DbSingleton::getDbm();
        $phone  = $this->getUrlString($phone);
        $phone  = $this->formatValidPhone($phone);
        $pass   = $this->getUrlString($pass);
        $email  = $this->getUrlString($email);
        $name   = $this->getUrlString($name);

        list($client_id, $user_id) = $this->getClientData();
        $db->query("UPDATE `A_CLIENTS_USERS` SET `phone` = '$phone', `pass` = '$pass', `email` = '$email', `name` = '$name' WHERE `id` = $user_id AND `client_id` = $client_id;");
        $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `phone` = '$phone', `pass` = '$pass', `email` = '$email', `name` = '$name' WHERE `id` = $user_id AND `client_id` = $client_id;");

        return true;
    }

    /*
     * save registration
     * */
    public function saveRegistration($phone, $pass, $email, $name, $client_cat, $city_id, $tpoint_id, $mailing): bool
    {
        $db = DbSingleton::getDbm();

        $phone      = $this->formatValidPhone($this->getUrlString($phone));
        $pass       = $this->getUrlString($pass);
        $email      = $this->getUrlString($email);
        $name       = $this->getUrlString($name);
        $client_cat = $this->getUrlNumber($client_cat);
        $city_id    = $this->getUrlNumber($city_id);
        $tpoint_id  = $this->getUrlNumber($tpoint_id);
        $mailing    = $this->getUrlNumber($mailing);
        $mailing    = ($mailing) ? 1 : 0;
        $client_id  = $this->getClientByTpoint($tpoint_id);
        $date       = date("Y-m-d H:i:s");

        list($region, $state, $country) = $this->getLocationCity($city_id);
        if (empty($client_cat)) {
            $client_cat = $this->default_client_category;
        }

        if ($client_cat === $this->default_client_category) {
            // REGISTRATION AS CLIENT
            $this->addRetailClient($client_id, $phone, $name, $city_id, $email, $pass, $this->default_client_category);
        } else {
            // REGISTRATION AS RETAIL
            $r = $db->query("SELECT `id` FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone` = '$phone' LIMIT 1;");
            $n = $db->num_rows($r);

            if ($n === 0) {
                $db->query("INSERT INTO `A_CLIENTS_USERS_RETAIL` (`name`, `email`, `phone`, `pass`, `client_id`, `client_category`, `data`, `country_id`, `state_id`, `region_id`, `city_id`, `mailing`, `status`) 
                VALUES ('$name', '$email', '$phone', '$pass', $client_id, '$client_cat', '$date', $country, $state, $region, $city_id, $mailing, $this->status_user_retail);");
            } else {
                $db->query("UPDATE `A_CLIENTS_USERS_RETAIL` SET `pass` = '$pass', `email` = '$email', `name` = '$name' WHERE `phone` = '$phone' LIMIT 1;");
            }
        }

        return true;
    }

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
    public function getClientCurrency($client_id): int
    {
        $client_id = $this->getUrlNumber($client_id);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `cash_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");
        $n = $db->num_rows($r);
        $cash_id = (int)$db->result($r, 0, "cash_id");

        if ($n === 0) {
            $cash_id = $this->default_currency;
        }

        return $cash_id;
    }

    /*
     * set TPOINT
     * */
    public function setTpoint($tpoint_id): int
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
    public function getTpoint($client_id = 0): int
    {
        $db = DbSingleton::getDbm();
        if (empty($client_id)) {
            $client_id = $this->getClientData()[0];
        }

        $r = $db->query("SELECT `tpoint_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id;");
        $tpoint_id = (int)$db->result($r, 0, "tpoint_id");

        if (empty($tpoint_id)) {
            $tpoint_id = $this->default_tpoint;
        }

        return $tpoint_id;
    }

    /*
     * get TPOINT from CLIENT
     * */
    public function getTpointUser($client_id): int
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `tpoint_id` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id;");
        $tpoint_id = (int)$db->result($r, 0, "tpoint_id");

        if (empty($tpoint_id)) {
            $tpoint_id = $this->default_tpoint;
        }

        return $tpoint_id;
    }

    /*
     * set default retail tpoint
     * */
    public function setTpointRetail(): bool
    {
        (!empty($_SESSION["tpoint_id"])) ?: $_SESSION["tpoint_id"] = $this->default_tpoint;

        return true;
    }

    /*
     * get tpoint name from storage_id
     * */
    public function getArticleStorageTPoint($storage_id)
    {
        $storage_id = $this->getUrlNumber($storage_id);
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `tpoint_id` FROM `T_POINT_STORAGE` WHERE `storage_id` = $storage_id LIMIT 1;");
        $tpoint_id = $db->result($r, 0, "tpoint_id") + 0;

        $r = $db->query("SELECT `full_name` FROM `T_POINT` WHERE `id` = $tpoint_id LIMIT 1;");

        return $db->result($r, 0, "full_name");
    }

    public function validateRegistration($phone): array
    {
        $db = DbSingleton::getDbm();

        $answer = 0; $err = 0;
        $client_id = 0;

        $phone  = $this->formatValidPhone($phone);
        $r = $db->query("SELECT `phone`, `client_id` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $client_phone   = $db->result($r, 0, "phone");
            $client_id      = $db->result($r, 0, "client_id");
            $answer         = 1;
            $err            = $client_phone;
        }

        $user_id = $this->getUser();

        if ($user_id > 0) {
            $user_phone = $this->getClientPhone();

            if ($phone === $user_phone) {
                $answer = 0;
                $err = "";
            }
        }

        if ($user_id === 0) {
            $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id` = $client_id LIMIT 1;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $client_category = (int)$db->result($r, 0, "client_category");

                if ($client_category === $this->default_client_category) {     
                    $answer = 0;
                    $err = "";
                }
            }
        }

        return array($answer, $err);
    }

    /*
     * checking user authorization in the system
     * Table: myparts_dba.`A_CLIENTS_USERS`, myparts_dba.`A_CLIENTS_USERS_RETAIL`
     * false - if new user
    */
    public function checkRegClient($phone, $type = 0)
    {
        $db = DbSingleton::getDbm();

        $phone  = $this->formatValidPhone($phone);
        $type   = $this->getUrlNumber($type);

        $r = $db->query("SELECT `client_id`, `phone`, `pass` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);

        $client_id      = $db->result($r, 0, "client_id");
        $client_phone   = $db->result($r, 0, "phone");
        $client_pass    = $db->result($r, 0, "pass");

        $res = ($n === 0) ? false : array($client_phone, $client_pass);
        if ($type === 1) {
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
    public function checkRegistration($phone): bool
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT 1 FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    /*
     * validation of phone numbers by Ukrainian operators
     * Table: toko_dba.`mobile_operators`
    */
    public function validateOperator($phone): bool
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getTokoDb();
        $result = false;
        $code = substr($phone, 0, 3);
        $r = $db->query("SELECT 1 FROM `mobile_operators` WHERE `OPERATOR_CODE` = '$code' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $result = true;
        }

        $user_id = $this->getUser();
        if (($user_id > 0) && $phone !== $this->getClientPhone()) {
            $result = false;
        }

        return $result;
    }

    /*
     * get storage_id from Tpoint
     * */
    public function getStorageByTpoint($tpoint_id): array
    {
        $db = DbSingleton::getTokoDb();
        $storage_local = $storage_remote = [];
        $r = $db->query("SELECT `storage_id`, `local` FROM `T_POINT_STORAGE` WHERE `tpoint_id` = $tpoint_id AND `status` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $storage_id = $db->result($r, $i - 1, "storage_id");
            $local      = $db->result($r, $i - 1, "local");

            ($local === "41") ? array_push($storage_local, $storage_id) : array_push($storage_remote, $storage_id);
        }

        $storage_local  = implode(",", $storage_local);
        $storage_remote = implode(",", $storage_remote);

        if (empty($storage_local)) {
            $storage_local = 0;
        }

        if (empty($storage_remote)) {
            $storage_remote = 0;
        }

        return array($storage_local, $storage_remote);
    }

    /*
     * select all tpoint except the specified one
     * Table: toko_dba.`T_POINT`
    */
    public function getTpointOtherList($tpoint_id_sel): array
    {
        $db = DbSingleton::getTokoDb();
        $tpoint_array = [];
        $r = $db->query("SELECT `id` FROM `T_POINT` WHERE `status` = 1 ORDER BY CASE WHEN `id` = $tpoint_id_sel THEN 0 ELSE 1 END;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $tpoint_id = (int)$db->result($r, $i - 1, "id");
            $tpoint_array[] = $tpoint_id;
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
        $storage_id = $this->getUrlNumber($storage_id);
        $db = DbSingleton::getTokoDb();
        $storage_address = "";
        $r = $db->query("SELECT `address` FROM `STORAGE` WHERE `id` = $storage_id AND `status` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $storage_address = $db->result($r, 0, "address");
        }

        return $storage_address;
    }

    /*
     * getting city name by storage
     * Table: toko_dba.`STORAGE`
    */
    public function getStorageCity($storage_id)
    {
        $storage_id = $this->getUrlNumber($storage_id);
        $db = DbSingleton::getTokoDb();
        $city_name = "";
        $r = $db->query("SELECT `city` FROM `STORAGE` WHERE `id` = $storage_id AND `status` = 1 LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $city_name = $this->getCityName($db->result($r, 0, "city"));
        }

        return $city_name;
    }

    /*
     * get location variables by city
     * Table: myparts_dba.`T2_CITY`, myparts_dba.`T2_REGION`, myparts_dba.`T2_STATE`, myparts_dba.`T2_COUNTRIES`
     * */
    public function getLocationCity($city_id): array
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

            $region_id  = $db->result($r, 0, "REGION_ID");
            $state_id   = $db->result($r, 0, "STATE_ID");
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

        $r = $db->query("SELECT `pass` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' AND `status` = $this->status_user LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 0) {
            $r = $db->query("SELECT `pass` FROM `A_CLIENTS_USERS_RETAIL` WHERE `phone` = '$phone' AND `status` = $this->status_user_retail LIMIT 1;");
        }

        $password   = $db->result($r, 0, "pass");
        $message    = "Vash login: $phone, vash parol: $password. Spasibo, chto Vy s nami! (www.toko.ua)";

        $dbt->query("INSERT INTO `sms_journal` (`phone`, `sign`, `message`, `status`) VALUES ('$phone', 'TOKO.UA', '$message', '1');");

        return $this->replaceLang("<div class=\"col-12\">{sms_sent}</div>");
    }

    /*
     * send SMS validation
     * */
    public function validatePhone($phone, $ip = "", $captcha = ""): int
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();

        $password   = rand(1000, 9999);
        $message    = "Vvedite kod: $password";

        $db->query("INSERT INTO `phone_validation` (`phone`, `password`, `status`) VALUES ('$phone', '$password', '0');");
        $dbt->query("INSERT INTO `sms_journal` (`phone`, `sign`, `message`, `status`, `ip`, `captcha`) VALUES ('$phone', 'TOKO.UA', '$message', '1', '$ip', '$captcha');");

        return $password;
    }

    /*
     * finish phone validation
     * */
    public function endValidation($phone, $password): bool
    {
        $phone = $this->formatValidPhone($phone);
        $password = $this->getNameString($password);
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT 1 FROM `phone_validation` WHERE `phone` = '$phone' AND `password` = '$password' AND `status` = 0;");
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
    public function addRetailClient($tpoint_client_id, $phone, $name = "", $city_id = 0, $email = "", $pass = "", $client_category = 0): array
    {
        $db = DbSingleton::getDbm();

        if ($name === "") {
            $name = $phone;
        }

        if ($pass === "") {
            $pass = $this->randomPassword();
        }

        if (empty($client_category)) {
            $client_category = $this->default_client_category;
        }

        list($region_id, $state_id, $country_id) = $this->getLocationCity($city_id);
        $phone = $this->formatValidPhone($phone);

        $r = $db->query("SELECT MAX(`id`) as mid FROM `A_CLIENTS`;");
        $client_id = 0 + $db->result($r, 0, "mid") + 1;
        $db->query("INSERT INTO `A_CLIENTS` (`id`, `name`, `full_name`, `phone`, `email`, `country`, `state`, `region`, `city`, `client_category`, `rounding_price`) 
        VALUES ('$client_id', '$name', '$name', '$phone', '$email', '$country_id', '$state_id', '$region_id', '$city_id', $client_category, 2);");

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
    public function moveClientsConditionsRetail($tpoint_client_id, $client_id): bool
    {
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT * FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $tpoint_client_id LIMIT 1;");
        $n = (int)$db->num_rows($r);

        if ($n === 1) {
            $cash_id                = $db->result($r, 0, "cash_id");
            $country_cash_id        = $db->result($r, 0, "country_cash_id");
            $credit_cash_id         = $db->result($r, 0, "credit_cash_id");
            $payment_delay          = $db->result($r, 0, "payment_delay");
            $credit_limit           = $db->result($r, 0, "credit_limit");
            $credit_return          = $db->result($r, 0, "credit_return");
            $price_lvl              = $db->result($r, 0, "price_lvl");
            $margin_price_lvl       = $db->result($r, 0, "margin_price_lvl");
            $price_suppl_lvl        = $db->result($r, 0, "price_suppl_lvl");
            $margin_price_suppl_lvl = $db->result($r, 0, "margin_price_suppl_lvl");
            $tpoint_id              = $db->result($r, 0, "tpoint_id");
            $client_vat             = $db->result($r, 0, "client_vat");
            $doc_type_id            = $db->result($r, 0, "doc_type_id");

            $db->query("INSERT INTO `A_CLIENTS_CONDITIONS` (`client_id`, `cash_id`, `country_cash_id`, `credit_cash_id`, `payment_delay`, `credit_limit`, `credit_return`, `price_lvl`, `margin_price_lvl`, `price_suppl_lvl`, `margin_price_suppl_lvl`, `tpoint_id`, `client_vat`, `doc_type_id`) 
            VALUES ('$client_id', '$cash_id', '$country_cash_id', '$credit_cash_id', '$payment_delay', '$credit_limit', '$credit_return', '$price_lvl', '$margin_price_lvl', '$price_suppl_lvl', '$margin_price_suppl_lvl', '$tpoint_id', '$client_vat', '$doc_type_id');");
        }

        return true;
    }

    /*
     * check client category
     * check if client shop
     * */
    public function checkRetailClientCategory($client_id): bool
    {
        $db = DbSingleton::getDbm();
        $client_id = $this->getUrlNumber($client_id);

        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id` = $client_id LIMIT 1;");
        $client_category = (int)$db->result($r, 0, "client_category");

        return ($client_category === $this->default_client_category);
    }

    /*
     * get all users count
     * */
    public function getUsersCount(): int
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `id` FROM `A_CLIENTS_USERS` WHERE `status` = 1;");

        return $db->num_rows($r);
    }

    /*
     * checking clients action status
     * Table: myparts_dba.`ACTION_CLIENTS_CATEGORY`
     * Cookie: 'action_status'
     * */
    public function checkActionClients(): int
    {
        $db = DbSingleton::getDbm();
        $user_id = $this->getUser();

        $r = $db->query("SELECT `id` FROM `A_CLIENTS` WHERE `id` = $user_id AND `client_category` IN (SELECT `category_id` FROM `ACTION_CLIENTS_CATEGORY` WHERE 1);");
        $n = $db->num_rows($r);
        setcookie("action_status", "1", time() + (86400 * 30), "/");

        return $n;
    }

    /*
     * getting a rounded price value
     * Table: myparts_dba.`A_CLIENTS
     * */
    public function getClientPriceRounding($client_id, $price): float
    {
        $db = DbSingleton::getDbm();

        if ($client_id > 0) {
            $r = $db->query("SELECT `rounding_price` FROM `A_CLIENTS` WHERE `id` = $client_id;");
            $n = $db->num_rows($r);

            if ($n > 0) {
                $rounding_price = (int)$db->result($r, 0, "rounding_price");

                if ($rounding_price === 1) {
                    $price = round($price * 100, -1) / 100;
                }

                if ($rounding_price === 2) {
                    $price = round($price);
                }
            }
        }

        return (float)$price;
    }

    /*
     * Check if user authorized
     * by PHONE
     * */
    public function getAuthorizedUser($phone): array
    {
        $phone = $this->formatValidPhone($phone);
        $db = DbSingleton::getDbm();
        $user_id = $client_id = 0;
        $status = false;

        $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $user_id    = $db->result($r, 0, "id");
            $client_id  = $db->result($r, 0, "client_id");
        }

        // found client-phone
        if (($client_id > 0) && !$this->checkRetailClientCategory($client_id)) {
            $status = true;
        }

        return array($status, $user_id);
    }

    /*
     * get user phone
     * */
    public function getClientPhone(): string
    {
        $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
        $phone = "";

        if ($user_id > 0) {
            $r = $db->query("SELECT `phone` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
            $phone = (string)$db->result($r, 0, "phone");
        }

        return $phone;
    }

    /*
     * Add History
     * */
    public function insertHistory($article_nr_displ, $brand_id): bool
    {
        $db = DbSingleton::getTokoDb();
        session_start();

        $ses        = session_id();
        $cookie     = $this->getSessionID();
        $date       = date("Y-m-d H:i:s");
        $client_id  = $this->getClient();
        $user_id    = $this->getUser();
        $art_id     = $this->getArtID($article_nr_displ);

        if ($brand_id > 0) {
            $where = (empty($user_id)) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";

            $r = $db->query("SELECT COUNT(`id`) as kilk FROM `CLIENT_HISTORY` WHERE $where;");
            $k = (int)$db->result($r, 0, "kilk");

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
     * Add History by article
     * */
    public function insertArtsHistory($art_id): bool
    {
        $art_id = $this->getUrlNumber($art_id);
        $db = DbSingleton::getTokoDb();
        session_start();

        $ses        = session_id();
        $cookie     = $this->getSessionID();
        $date       = date("Y-m-d H:i:s");
        $client_id  = $this->getClient();
        $user_id    = $this->getUser();

        if ($art_id > 0) {
            $where = (empty($user_id)) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";

            $r = $db->query("SELECT COUNT(`id`) as kilk FROM `ARTS_HISTORY` WHERE $where;");
            $k = (int)$db->result($r, 0, "kilk");

            if ($k > $this->max_history_count) {
                $r = $db->query("SELECT `id` FROM `ARTS_HISTORY` WHERE $where ORDER BY `data` ASC LIMIT 1;");
                $id = (int)$db->result($r, 0, "id");
                $db->query("UPDATE `ARTS_HISTORY` SET `data` = '$date', `art_id` = $art_id WHERE `id` = $id;");
            } else {
                $r = $db->query("SELECT `id` FROM `ARTS_HISTORY` WHERE $where AND `art_id` = $art_id;");
                $n = $db->num_rows($r);

                if ($n > 0) {
                    $db->query("UPDATE `ARTS_HISTORY` SET `data` = '$date' WHERE $where AND `art_id` = $art_id;");
                } else {
                    $db->query("INSERT INTO `ARTS_HISTORY` (`client_id`, `client_user_id`, `ses_id`, `cookie_id`, `data`, `art_id`) 
                    VALUES ('$client_id', '$user_id', '$ses', '$cookie', '$date', $art_id);");
                }
            }
        }

        return true;
    }

    /*
     * get user history
     * */
    public function getClientHistory(): array
    {
        $db = DbSingleton::getTokoDb();
        $col = 0;
        $history = [];
        $cookie_id = $this->getSessionID();

        list($client_id, $user_id) = $this->getClientData();
        $where = (empty($user_id)) ? "`cookie_id` = '$cookie_id'" : "`client_id` = $client_id AND `client_user_id` = $user_id";

        $r = $db->query("SELECT `id`, `article_nr_displ`, `brand_id` FROM `CLIENT_HISTORY` WHERE $where GROUP BY `art_id` ORDER BY `data` DESC LIMIT 10;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id         = $db->result($r, $i - 1, "id");
            $art_nr_ds  = $db->result($r, $i - 1, "article_nr_displ");
            $brand_id   = $db->result($r, $i - 1, "brand_id");
            $brand_link = $this->getBrandLink($brand_id);
            $art_nr_ds  = strtoupper($art_nr_ds);
            $brand_name = $this->getBrandName($brand_id);

            if ($brand_name !== "") {
                $history[$col] = [
                    "id"                => $id,
                    "article_nr_displ"  => $art_nr_ds,
                    "brand_id"          => $brand_id,
                    "brand"             => $brand_name,
                    "brand_link"        => $brand_link
                ];
                $col++;
            }
        }

        return $history;
    }

    /*
     * get user history
     * */
    public function getArtsHistory(): array
    {
        $db = DbSingleton::getTokoDb();
        $col = 0;
        $history = [];
        $cookie = $this->getSessionID();

        if ($cookie === "" || $cookie === NULL) {
            $cookie = 0;
        }

        list($client_id, $user_id) = $this->getClientData();
        $where = (empty($user_id)) ? "`cookie_id` = '$cookie'" : "`client_id` = $client_id AND `client_user_id` = $user_id";

        $r = $db->query("SELECT `id`, `art_id` FROM `ARTS_HISTORY` WHERE $where GROUP BY `art_id` ORDER BY `data` DESC LIMIT 10;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id     = $db->result($r, $i - 1, "id");
            $art_id = $db->result($r, $i - 1, "art_id");

            if ($art_id > 0) {
                $history[$col] = [
                    "id"        => $id,
                    "art_id"    => $art_id
                ];
                $col++;
            }
        }

        return $history;
    }

    /*
     * Delete Client
     * */
    public function dropClient($client_id): string
    {
        $client_id = $this->getUrlNumber($client_id);
        $db = DbSingleton::getDbm();
        $db->query("DELETE FROM `A_CLIENTS` WHERE `id` = $client_id LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_USERS` WHERE `client_id` = $client_id LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_CATEGORY` WHERE `client_id` = $client_id LIMIT 1;");
        $db->query("DELETE FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");

        return "deleted client: #$client_id";
    }

    public function getClientMarkupMin($client_id): int
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `markup_min` FROM `A_CLIENTS_CONDITIONS` WHERE `client_id` = $client_id LIMIT 1;");

        return (int)$db->result($r, 0, "markup_min");
    }

    /*
     * Get User Data + User Order Info
     * */
    public function getClientUserData($user_id): array
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `name`, `phone`, `email` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $user_name  = $db->result($r, 0, "name");
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
    public function setClientRequest($phone, $vin = "", $text = "", $status = 0): array
    {
        $db = DbSingleton::getTokoDb();

        $phone      = $this->formatValidPhone($phone);
        $vin        = $this->getNameString($vin);
        $text       = $this->getNameString($text);
        $status     = $this->getUrlNumber($status);
        $date_start = date("Y-m-d H:i:s");

        if (($phone === "") || (strlen($vin) !== $this->vin_len && $status === 1) || (!$this->validateOperator($phone))) {
            $answer = false;
            if ($phone === "") {
                $err = "{phone_number_input}";
            } elseif ($vin === "") {
                $err = "{vin_number_input}";
            } elseif (!$this->validateOperator($phone)) {
                $err = "{sms_error_1}";
            } elseif ($status === 1 && strlen($vin) !== $this->vin_len) {
                $err = "{vin_error_1}";
            } else {
                $err = "{input_all_data}";
            }
        } else {
            $db->query("INSERT INTO `T2_QUESTIONS` (`PHONE`, `VIN`, `TEXT` , `DATA_CREATE`) VALUES ('$phone', '$vin', '$text', '$date_start');");
            $answer = true;
            $err = "";
        }

        return array($answer, $err);
    }

    /*
     * get client_id / user_id from PHONE
     * */
    public function getClientUserByPhone($phone): array
    {
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT `id`, `client_id` FROM `A_CLIENTS_USERS` WHERE `phone` = '$phone' LIMIT 1;");
        $user_id    = $db->result($r, 0, "id");
        $client_id  = $db->result($r, 0, "client_id");

        return array("user_id" => $user_id, "client_id" => $client_id);
    }

    /*
     * Client Bonus
     * */
    public function checkClientBonus($client_id, $bonus = 1): bool
    {
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT 1 FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID` = $client_id AND `BONUS_ID` = $bonus LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    /*
     * add Client bonus
     * if not exist
     * */
    public function addClientBonus($client_id, $bonus = 1): bool
    {
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT 1 FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID` = $client_id AND `BONUS_ID` = $bonus LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 0) {
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
     * set bonus on clients phone
     * if not used before
     * */
    public function finishBonusPhone($phone, $bonus = 1)
    {
        $phone = $this->formatValidPhone($phone);
        $bonus = $this->getUrlNumber($bonus);

        // check reg CLIENT
        if ($this->checkRegClient($phone)) {
            // get CLIENT
            $clientData = $this->getClientUserByPhone($phone);
            $client_id  = $clientData["client_id"];
            // check if retail
            // check if have BONUS already
            // add BONUS
            if ($this->checkRetailClientCategory($client_id) && !$this->checkClientBonus($client_id, $bonus)) {
                $this->addClientBonus($client_id, $bonus);
            }
        } else {
            // reg CLIENT
            $clientData = $this->addRetailClient($this->getClientData(), $phone);
            $client_id  = $clientData["client_id"];
            // add BONUS
            $this->addClientBonus($client_id, $bonus);
        }

        // set BONUS
        $bonus_sum = $this->setClientBonus($client_id, $bonus);

        $form = $this->getHtmlForm("bonus/phone_done");
        $form = str_replace(array("{bonus_summ}", "{bonus_phone}"), array($bonus_sum, $phone), $form);

        $form = $this->replaceLang($form);

        return $form;
    }

    public function getCashDataArray()
    {
        $db = DbSingleton::getDbm();
        $dat = array();
        $r = $db->query("SELECT * FROM `CASH` ORDER BY `name`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "id");
            $abr = $db->result($r, $i - 1, "abr");
            $abr2 = $db->result($r, $i - 1, "abr2");
            $name = $db->result($r, $i - 1, "name");
            $dat[$i]["id"] = $id;
            $dat[$i]["abr"] = $abr;
            $dat[$i]["abr2"] = $abr2;
            $dat[$i]["name"] = $name;
        }
        return $dat;
    }

    public function getSupplArtsList($suppl_id)
    {
        $db = DbSingleton::getTokoDb();
        $suppl_arts = [];
        $r = $db->query("SELECT `art_id` FROM `T2_SUPPL_IMPORT` WHERE `suppl_id` = $suppl_id GROUP BY `art_id`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "art_id");
            $suppl_arts[] = $art_id;
        }
        return $suppl_arts;
    }

    public function getSupplStorageArray($suppl_id)
    {
        $db = DbSingleton::getDbm();
        $suppl_id = intval($suppl_id);
        $st = array();
        $r = $db->query("SELECT `id`, `name` FROM `A_CLIENTS_STORAGE` WHERE `status` = 1 AND `client_id` = $suppl_id ORDER BY `name`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "id");
            $name = $db->result($r, $i - 1, "name");
            $st[$i]["id"] = $id;
            $st[$i]["name"] = $name;
        }
        return $st;
    }

    public function findCashID($suppl_cash, $cash_data)
    {
        $id = 0;
        foreach ($cash_data as $cash) {
            if ($cash["abr"] == $suppl_cash) {
                $id = $cash["id"];
                break;
            }
            if ($cash["abr2"] == $suppl_cash) {
                $id = $cash["id"];
                break;
            }
            if ($cash["name"] == $suppl_cash) {
                $id = $cash["id"];
                break;
            }
        }

        return $id;
    }

    public function getCacheStockArts($suppl_arts, $suppl_import_arts)
    {
        $db = DbSingleton::getTokoDb();

        // Ті, яких немає в $suppl_import_arts
        $suppl_delete_arts = array_diff($suppl_arts, $suppl_import_arts);
        $suppl_valid_arts = [];

        if (!empty($suppl_delete_arts)) {
            foreach ($suppl_delete_arts as $art_id) {
                $art_id = intval($art_id);
                $status = 0;
                $r1 = $db->query("SELECT * FROM `T2_SUPPL_IMPORT` WHERE `art_id` = $art_id;");
                $n1 = $db->num_rows($r1);
                if ($n1 > 0) {
                    $status = 1;
                }
                $r2 = $db->query("SELECT * FROM `T2_ARTICLES_STRORAGE` WHERE `ART_ID` = $art_id AND `AMOUNT` > 0;");
                $n2 = $db->num_rows($r2);
                if ($n2 > 0) {
                    $status = 1;
                }
                if ($status) {
                    $suppl_valid_arts[] = $art_id;
                }
            }
        }

        // Імпортовані + Валідні з видалених
        $suppl_cache_arts_update = array_merge($suppl_import_arts, $suppl_valid_arts);
        // Ті, яких немає в $suppl_cache_arts_update
        $suppl_cache_arts_delete = array_diff($suppl_arts, $suppl_cache_arts_update);

        return array($suppl_cache_arts_update, $suppl_cache_arts_delete);
    }

    public function getArtsGroupId($art_id)
    {
        $art_id = intval($art_id);
        $db = DbSingleton::getTokoDb();
        $group_id = 0;
        $r = $db->query("SELECT `GROUP_ID` FROM `T2_TREE_ARTS_EXIST` WHERE `ART_ID` = $art_id LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $group_id = $db->result($r, 0, "GROUP_ID");
        }
        return $group_id;
    }

    public function updateGroupCacheArts($suppl_cache_arts_update, $suppl_cache_arts_delete)
    {
        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();
        $answer = 0;
        $err = "Помилка запису";

        $arts_update = [];
        foreach ($suppl_cache_arts_update as $art_id) {
            $group_id = $this->getArtsGroupId($art_id);
            $arts_update[$group_id][] = $art_id;
        }

        foreach ($arts_update as $group_id => $arts) {
            $group_id = intval($group_id);
            $table = "EX_TABLE_TREE_$group_id";
            $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
            $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
            $rch1 = $dbc->query("SHOW TABLES LIKE '$table';");
            $nch1 = $dbc->num_rows($rch1);
            $rch2 = $dbc->query("SHOW TABLES LIKE '$table_params';");
            $nch2 = $dbc->num_rows($rch2);
            $rch3 = $dbc->query("SHOW TABLES LIKE '$table_mfa';");
            $nch3 = $dbc->num_rows($rch3);

            if ($nch1 > 0 && $nch2 > 0 && $nch3 > 0) {
                foreach ($arts as $art_id) {
                    $art_id = intval($art_id);
                    // TABLE
                    $r1 = $dbc->query("SELECT `art_id` FROM `$table` WHERE `art_id` = $art_id;");
                    $n1 = $dbc->num_rows($r1);
                    if ($n1 == 0) {
                        $rbr = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
                        $brand_id = intval($db->result($rbr, 0, "BRAND_ID"));
                        $dbc->query("INSERT INTO `$table` (`art_id`, `brand_id`, `status`) VALUES ($art_id, $brand_id, 1);");
                    }
                    // TABLE PARAMS
                    $r2 = $dbc->query("SELECT `art_id` FROM `$table_params` WHERE `art_id` = $art_id;");
                    $n2 = $dbc->num_rows($r2);
                    if ($n2 == 0) {
                        $rbr = $db->query("SELECT `BRAND_ID` FROM `T2_ARTICLES` WHERE `ART_ID` = $art_id LIMIT 1;");
                        $brand_id = intval($db->result($rbr, 0, "BRAND_ID"));
                        $arr = [];
                        $rpar = $db->query("SELECT `PARAM_ID`, `VALUE_ID` FROM `T2_TREE_ARTS_PARAMS_VALUE_EXIST` WHERE `GROUP_ID` = $group_id AND `ART_ID` = $art_id;");
                        $npar = $db->num_rows($rpar);
                        if ($npar > 0) {
                            for ($i = 1; $i <= $npar; $i++) {
                                $param_id = $db->result($rpar, $i - 1, "PARAM_ID");
                                $value_id = $db->result($rpar, $i - 1, "VALUE_ID");
                                $arr[$param_id][] = $value_id;
                            }
                            $column_name = [];
                            $column_value = [];
                            foreach ($arr as $param_id => $values) {
                                if ($param_id > 0 && !empty($values)) {
                                    $column_name[] = "`param_$param_id`";
                                    $column_value[] = "'" . implode(",", $values) . "'";
                                }
                            }
                            $column_set_name = implode(",", $column_name);
                            if ($column_set_name != "") $column_set_name = ", " . $column_set_name;
                            $column_set_value = implode(",", $column_value);
                            if ($column_set_value != "") $column_set_value = ", " . $column_set_value;

                            $dbc->query("INSERT INTO `$table_params` (`art_id`, `brand_id`, `status` $column_set_name) VALUES ($art_id, $brand_id, 1 $column_set_value);");
                        }
                    }
                    // TABLE MFA
                    $r3 = $dbc->query("SELECT `art_id` FROM `$table_mfa` WHERE `art_id` = $art_id;");
                    $n3 = $dbc->num_rows($r3);
                    if ($n3 == 0) {
                        $arr = [];
                        $rmfa = $db->query("SELECT tl.`ART_ID`, tm.MOD_MFA_ID, tm.Model 
                        FROM `T2_LINKS` tl
                            LEFT JOIN `T_types` tt ON (tt.TYP_ID = tl.TYP_ID)
                            LEFT JOIN `T_models` tm ON (tm.MOD_ID = tt.TYP_MOD_ID)
                        WHERE `ART_ID` = $art_id 
                        GROUP BY tl.ART_ID, tm.MOD_MFA_ID, tm.Model");
                        $nmfa = $db->num_rows($rmfa);
                        for ($i = 1; $i <= $nmfa; $i++) {
                            $art_id = $db->result($rmfa, $i - 1, "ART_ID");
                            $mfa_id = $db->result($rmfa, $i - 1, "MOD_MFA_ID");
                            $model = $db->result($rmfa, $i - 1, "Model");
                            if ($mfa_id > 0) {
                                $arr[$art_id][$mfa_id][] = $model;
                            }
                        }
                        foreach ($arr as $art_id => $mfas) {
                            foreach ($mfas as $mfa_id => $models) {
                                foreach ($models as $model) {
                                    $art_id = intval($art_id);
                                    $mfa_id = intval($mfa_id);
                                    $dbc->query("INSERT INTO `$table_mfa` (`art_id`, `mfa_id`, `model`, `status`) VALUES ($art_id, $mfa_id, \"$model\", 1);");
                                }
                            }
                        }
                    }
                }
            }
        }

        $arts_delete = [];
        foreach ($suppl_cache_arts_delete as $art_id) {
            $group_id = $this->getArtsGroupId($art_id);
            $arts_delete[$group_id][] = $art_id;
        }

        foreach ($arts_delete as $group_id => $arts) {
            $group_id = intval($group_id);
            $table = "EX_TABLE_TREE_$group_id";
            $table_mfa = "EX_TABLE_TREE_MFA_$group_id";
            $table_params = "EX_TABLE_TREE_PARAMS_$group_id";
            $rch1 = $dbc->query("SHOW TABLES LIKE '$table';");
            $nch1 = $dbc->num_rows($rch1);
            $rch2 = $dbc->query("SHOW TABLES LIKE '$table_params';");
            $nch2 = $dbc->num_rows($rch2);
            $rch3 = $dbc->query("SHOW TABLES LIKE '$table_mfa';");
            $nch3 = $dbc->num_rows($rch3);

            if ($nch1 > 0 && $nch2 > 0 && $nch3 > 0) {
                foreach ($arts as $art_id) {
                    $art_id = intval($art_id);
                    $r1 = $dbc->query("SELECT `art_id` FROM `$table` WHERE `art_id` = $art_id;");
                    $n1 = $dbc->num_rows($r1);
                    if ($n1 > 0) {
                        $dbc->query("DELETE FROM `$table` WHERE `art_id` = $art_id;");
                    }
                    $r2 = $dbc->query("SELECT `art_id` FROM `$table_params` WHERE `art_id` = $art_id;");
                    $n2 = $dbc->num_rows($r2);
                    if ($n2 > 0) {
                        $dbc->query("DELETE FROM `$table_params` WHERE `art_id` = $art_id;");
                    }
                    $r3 = $dbc->query("SELECT `art_id` FROM `$table_mfa` WHERE `art_id` = $art_id;");
                    $n3 = $dbc->num_rows($r3);
                    if ($n3 > 0) {
                        $dbc->query("DELETE FROM `$table_mfa` WHERE `art_id` = $art_id;");
                    }
                }
            }
        }

        return array($answer, $err);
    }

    public function getActingExRate()
    {
        $db = DbSingleton::getDbm();
        $exRate = [];

        $r = $db->query("SELECT jk.`kours_value`, jc.`abr` 
        FROM `J_KOURS` jk 
            LEFT JOIN `myparts_dba`.`CASH` jc ON jc.id = jk.cash_id 
        WHERE jk.`in_use` = 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $abr    = $db->result($r, $i - 1, "abr");
            $value  = $db->result($r, $i - 1, "kours_value");

            $exRate[$abr] = $value;
        }

        return $exRate;
    }

    public function getSupplPriceTemplate($suppl_id)
    {
        $path = "/var/www/portal.myparts.pro/uploads/templates/";
        $file = $path . $suppl_id . ".json";

        $data = file_get_contents($file);
        $data = json_decode($data, true);

        foreach ($data['columns'] as $key => $val) {
            foreach ($val as $k => $v) {
                $data['columns'][$key][$k] = $v;
            }
        }

        return $data;
    }

    public function finishSupplPriceImport($file_name, $file_path, $suppl_id, $template, $rows)
    {
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();
        $answer = 0;
        $err = "Помилка збереження даних!";
        $user_id = 0;
        $price = 0;
        $suppl_cash_id = 2;

        if ($suppl_id > 0) {
            $start_row = $template['start_row'];
            $currency = $template['currency'];
            $exRateData = $this->getActingExRate();
            $kours_usd = $exRateData['USD'];
            $kours_eur = $exRateData['EUR'];

            if (file_exists($file_path)) {
                $suppl_arts = $this->getSupplArtsList($suppl_id);
                $storages = $this->getSupplStorageArray($suppl_id);
                $kol_storages = count($storages);
                $suppl_storages_use = [];
                $cash_data = $this->getCashDataArray();
                $index = 0;
                $brand = 0;
                $cash = 0;
                $storage_str = "";

                foreach ($template['columns'] as $key => $val) {
                    if ($val['type'] === 'index') {
                        $index = $key;
                    }
                    if ($val['type'] === 'brand') {
                        $brand = $key;
                    }
                    if ($val['type'] === 'price') {
                        $price = $key;
                    }
                    if ($val['type'] === 'cash') {
                        $cash = $key;
                    }
                    if ($val['type'] === 'storage') {
                        $storage_id = $val['storage_id'];
                        $suppl_storages_use[$storage_id] = $key;
                        $storage_str .= "$storage_id,";
                    }
                }

                if ($storage_str != "") {
                    $storage_str = substr($storage_str, 0, -1);
                }

                if ($storage_str == "") {
                    $storage_str = 0;
                }

                $fna = explode(".", $file_name);
                $ft = count($fna);
                $file_type = $fna[$ft - 1];

                if ($currency > 0) {
                    $suppl_cash_id = $currency;
                }

                $dbt->query("INSERT INTO `T2_SUPPL_IMPORT_ARCHIVE` (`suppl_id`,`suppl_index`,`brand`,`art_id`,`price_suppl`,`cash_id`,`kours_usd`,`price_usd`,`client_storage_id`,`stock_suppl`,`data_update`,`status`,`return_delay`,`warranty_info`)
                SELECT `suppl_id`,`suppl_index`,`brand`,`art_id`,`price_suppl`,`cash_id`,`kours_usd`,`price_usd`,`client_storage_id`,`stock_suppl`,`data_update`,`status`,`return_delay`,`warranty_info`
                FROM `T2_SUPPL_IMPORT` WHERE `suppl_id` = $suppl_id AND `client_storage_id` IN ($storage_str);");

                // AND `client_storage_id` IN ($storage_str)
                $dbt->query("DELETE FROM `T2_SUPPL_IMPORT` WHERE `suppl_id` = $suppl_id;");

                $pkg_k = 0;
                $max_pkg = 50;
                $pkg = "";
                $krs = 0;

                if (!empty($rows)) {

                    foreach ($rows as $Key => $Row) {
                        $krs += 1;

                        if ($krs >= $start_row) {
                            $suppl_index = trim(iconv("UTF-8", "Windows-1251", $Row[$index - 1]));
                            $suppl_brand = trim(iconv("UTF-8", "Windows-1251", $Row[$brand - 1]));
                            $suppl_price = str_replace(",", ".", trim(iconv("UTF-8", "Windows-1251", $Row[$price - 1])));

                            if ($currency == 0) {
                                $suppl_cash = trim(iconv("UTF-8", "Windows-1251", $Row[$cash - 1]));
                                $suppl_cash_id = $this->findCashID($suppl_cash, $cash_data);
                            }
                            $price_usd = 0;
                            if ($suppl_cash_id == 2) {
                                $price_usd = $suppl_price;
                            }
                            if ($suppl_cash_id == 1) {
                                $price_usd = ($suppl_price / $kours_usd);
                            }
                            if ($suppl_cash_id == 3) {
                                $price_usd = ($suppl_price * $kours_eur / $kours_usd);
                            }

                            for ($s = 1; $s <= $kol_storages; $s++) {
                                $storage_id = $storages[$s]["id"];
                                $stokCellNom = $suppl_storages_use[$storage_id] - 1;
                                $suppl_stock = trim(iconv("UTF-8", "Windows-1251", preg_replace('/\D/', '', $Row[$stokCellNom])));

                                if ($suppl_stock > 0) {
                                    if ($pkg != "") {
                                        $pkg .= ",";
                                    }
                                    $pkg .= "($suppl_id, \"$suppl_index\", \"$suppl_brand\", '$suppl_price', '$suppl_cash_id', '$kours_usd', '$price_usd', '$storage_id', '$suppl_stock', CURDATE())";
                                    $pkg_k += 1;
                                    if ($pkg_k == $max_pkg) {
                                        $dbt->query("INSERT INTO `T2_SUPPL_IMPORT` (`suppl_id`, `suppl_index`, `brand`, `price_suppl`, `cash_id`, `kours_usd`, `price_usd`, `client_storage_id`, `stock_suppl`, `data_update`) VALUES $pkg;");
                                        $pkg = "";
                                        $pkg_k = 0;
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($pkg)) {
                        $dbt->query("INSERT INTO `T2_SUPPL_IMPORT` (`suppl_id`, `suppl_index`, `brand`, `price_suppl`, `cash_id`, `kours_usd`, `price_usd`, `client_storage_id`, `stock_suppl`, `data_update`) VALUES $pkg;");
                    }

                    $answer = 1;
                    $err = "";
                }

                if ($answer === 0) {
                    $err = "No file to import!";
                } else {
                    $db->query("INSERT INTO `cron_suppl_price_import` (`suppl_id`, `user_id`, `status`) VALUES ($suppl_id, '$user_id', '1');");

                    $suppl_import_arts = [];
                    $r = $dbt->query("SELECT `art_id`, `suppl_brand`, `suppl_index`, `return_delay` FROM `T2_SUPPL_ARTICLES_IMPORT` WHERE `suppl_id` = $suppl_id;");
                    $n = $dbt->num_rows($r);
                    for ($i = 1; $i <= $n; $i++) {
                        $art_id         = intval($dbt->result($r, $i - 1, "art_id"));
                        $suppl_brand    = $dbt->result($r, $i - 1, "suppl_brand");
                        $suppl_index    = $dbt->result($r, $i - 1, "suppl_index");
                        $return_delay   = $dbt->result($r, $i - 1, "return_delay");
                        $suppl_import_arts[] = $art_id;

                        $dbt->query("UPDATE `T2_SUPPL_IMPORT` SET `art_id` = $art_id, `return_delay` = '$return_delay'
                        WHERE `suppl_index` LIKE '$suppl_index' AND `suppl_id` = $suppl_id AND `brand` LIKE \"$suppl_brand\" AND `status` = 1;");
                    }

                    list($suppl_cache_arts_update, $suppl_cache_arts_delete) = $this->getCacheStockArts($suppl_arts, $suppl_import_arts);
                    $this->updateGroupCacheArts($suppl_cache_arts_update, $suppl_cache_arts_delete);
                }

            }
        }

        return array('answer' => $answer, 'error' => $err);
    }

}