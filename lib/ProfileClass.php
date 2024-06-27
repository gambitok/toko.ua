<?php

class ProfileClass extends ClientClass
{

    public $page_signin         = "signin/";
    public $page_profile        = "profile/orders/";
    public $page_registration   = "registration/";

    /*
     * get profile left navigation
     * */
    public function getProfileClientInfo()
    {
        $client = new ClientClass();
        list($client_id, $user_id) = $client->getClientData();
        $name = $client->getClientInfo($client_id, $user_id)["name"];

        return ($user_id === 0)
            ? false
            : "{hello_cap}, <a href=\"" . $this->getSiteLink() . "$this->page_profile\">" . $name . "</a>";
    }

    /*
     * get profile right navigation
     * */
    public function getProfileInfo()
    {
        $form = $this->getHtmlForm("menu/profile_nav");
        $form = str_replace("{reg_link}", $this->getSiteLink() . $this->page_registration, $form);

        if ($this->getUser() === 0) {
            $form = str_replace("{reg_login}", "none", $form);
        } else {
            $form = str_replace("{reg_logout}", "none", $form);
        }
        $form = str_replace(array("{reg_login}", "{reg_logout}"), "", $form);

        return $form;
    }

    /*
     * get special offers navigation
     * */
    public function getSpecialOffers(): string
    {
        $db = DbSingleton::getDbm();

        $user_id    = $this->getUser();
        $client_id  = $this->getClient();
        $categories = [];

        $r = $db->query("SELECT `client_category` FROM `A_CLIENTS` WHERE `id` = $client_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $category_id = $db->result($r, $i - 1, "client_category");
            $categories[] = $category_id;
        }
        $categories = implode(",", $categories);

        $r = $db->query("SELECT acl.* 
        FROM `ACTION_CLIENTS_LIST` acl 
            LEFT JOIN `ACTION_CLIENTS` ac ON (ac.id = acl.action_id)
        WHERE acl.client_id = $client_id AND ac.status = 1;");
        $n1 = $db->num_rows($r);

        $r = $db->query("SELECT acc.* 
        FROM `ACTION_CLIENTS_CATEGORY` acc 
            LEFT JOIN `ACTION_CLIENTS` ac ON (ac.id = acc.action_id)
        WHERE acc.category_id IN ($categories) AND ac.status = 1;");
        $n2 = $db->num_rows($r);

        $r = $db->query("SELECT `update_actions` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $update_actions = $db->result($r, 0, "update_actions");

        $r = $db->query("SELECT 1 FROM `ACTION_CLIENTS` WHERE `timestamp` > '$update_actions 00:00:00' AND `status` = 1;");
        $n = $db->num_rows($r);

        $counter = ($n > 0) ? "<span class=\"authorization-item__counter\">($n)</span>" : "";

        $info = "";
        if ($user_id > 0 && ($n1 > 0 || $n2 > 0)) {
            $info = "
            <li class=\"authorization-item\">
                <a href=\"" . $this->getSiteLink() . "special_offers/\">
                    <span class=\"fas fa-box-open\"></span> <span>{special_offers_cap} $counter</span>
                </a>
            </li>";
        }

        return $info;
    }

    /*
     * get news navigation
     * */
    public function getNewsInfo(): string
    {
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();

        $user_id = $this->getUser();
        $lang_id = $this->getLanguage();
        if ($lang_id !== 1) {
            $lang_id = 5;
        }

        $r = $db->query("SELECT `update_news` FROM `A_CLIENTS_USERS` WHERE `id` = $user_id LIMIT 1;");
        $update_news = $db->result($r, 0, "update_news");

        $r = $dbt->query("SELECT COUNT(`id`) as count_ids FROM `news` WHERE `data` > '$update_news' AND `lang_id` = $lang_id AND `status` = 1;");
        $n = (int)$dbt->result($r, 0, "count_ids");

        $counter = ($user_id > 0 && $n > 0) ? "<span class=\"authorization-item__counter\">($n)</span>" : "";

        return "
        <li class=\"authorization-item\">
            <a href=\"" . $this->getSiteLink() . "news/\">
                <span class=\"fas fa-newspaper\"></span><span> {news_cap} $counter</span>
            </a>
        </li>";
    }

    /*
     * get profile mobile navigation
     * */
    public function getProfileInfoMobile()
    {
        $info = ($this->getUser() === 0)
            ? "<a href=\"" . $this->getSiteLink() . "$this->page_signin\">{authorization}</a>"
            : "<a href=\"" . $this->getSiteLink() . "$this->page_profile\">{profile}</a>";
        $info = $this->replaceLang($info);

        return $info;
    }

    /*
     * show profile form
     * */
    public function showProfileForm()
    {
        $client = new ClientClass();
        list($client_id, $user_id) = $client->getClientData();
        $name = $client->getClientInfo($client_id, $user_id)["name"];

        $form = $this->getHtmlForm("profile/profile");
        $form = str_replace(array("{client_name}", "{client_id}"), array($name, $client_id), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * check if client have bonus
     * */
    public function getClientBonus($client_id): bool
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT 1 FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID` = $client_id;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    /*
     * show profile bonus form
     * */
    public function showClientBonus($client_id)
    {
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT `bonus_balance` FROM `A_CLIENTS` WHERE `id` = $client_id;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $summ = $db->result($r, 0, "bonus_balance");
            $form = $this->getHtmlForm("profile/profile_bonus");
            $form = str_replace("{bonus_profile}", $summ, $form);
        } else {
            $form = "";
        }

        return $form;
    }

    /*
     * show profile `account` form
     * */
    public function showProfileAccount()
    {
        $menu = new MenuClass();
        $client = new ClientClass();
        list($client_id, $user_id) = $client->getClientData();
        $clientData = $client->getClientInfo($client_id, $user_id);

        $form = $this->getHtmlForm("profile/profile_account");
        $form = str_replace(array("{client_id}", "{client_phone}", "{client_password}", "{client_email}", "{client_name}", "{type_form}", "{client_country}", "{region_form}", "{client_city}", "{bonus_user}"), array($user_id, $clientData["phone"], $clientData["password"], $clientData["email"], $clientData["name"], $menu->showTypeForm($clientData["type"]), $clientData["country"], $menu->getRegionForm($clientData["region"]), $clientData["city"], $this->getClientBonus($client_id) ? $this->showClientBonus($client_id) : ""), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    public function checkDpStrExist($dp_id): bool
    {
        $dp_id = $this->getUrlNumber($dp_id);
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT 1 FROM `J_DP_STR` WHERE `dp_id` = $dp_id;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    public function getSelectDpData($dp_id): string
    {
        $select_arr = [];
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT `id` FROM `J_SELECT` WHERE `parrent_doc_id` = $dp_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $select_id = $db->result($r, $i - 1, "id");
            $select_arr[] = $select_id;
        }

        return implode(",", $select_arr);
    }

    public function checkSelectDpBug($dp_id): bool
    {
        $dp_id = $this->getUrlNumber($dp_id);

        $select_arr = [];
        $db = DbSingleton::getDbm();
        $k = 0;
        $select_str = $this->getSelectDpData($dp_id);

        if (!empty($select_arr)) {
            $r = $db->query("SELECT `amount_bug` FROM `J_SELECT_STR` WHERE `select_id` IN ('$select_str') AND `status` = 0;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $amount_bug = $db->result($r, $i - 1, "amount_bug");

                if ($amount_bug > 0) {
                    $k++;
                }
            }
        }

        return ($k > 0);
    }

    public function checkSelectStrDpBug($dp_id, $art_id): bool
    {
        $dp_id  = $this->getUrlNumber($dp_id);
        $art_id = $this->getUrlNumber($art_id);

        $db = DbSingleton::getDbm();
        $k = 0;
        $select_str = $this->getSelectDpData($dp_id);
        $select_arr = [];

        if (!empty($select_arr)) {
            $r = $db->query("SELECT `amount_bug` FROM `J_SELECT_STR` WHERE `select_id` IN ('$select_str') AND `art_id` = $art_id;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $amount_bug = $db->result($r, $i - 1, "amount_bug");

                if ($amount_bug > 0) {
                    $k++;
                }
            }
        }

        return ($k > 0);
    }

    public function closeOrderArtUpdate($dp_id, $art_id, $order_id)
    {
        $dp_id      = $this->getUrlNumber($dp_id);
        $art_id     = $this->getUrlNumber($art_id);
        $order_id   = $this->getUrlNumber($order_id);

        $db = DbSingleton::getDbm();

        $select_arr = [];
        $list = "";

        $r = $db->query("SELECT `id` FROM `J_SELECT` WHERE `parrent_doc_id` = $dp_id;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $select_id = $db->result($r, $i - 1, "id");
            $select_arr[] = $select_id;
        }

        $select_str = implode(",", $select_arr);
        if (!empty($select_arr)) {
            $r = $db->query("SELECT `amount`, `amount_bug`, `amount_collect` FROM `J_SELECT_STR` WHERE `select_id` IN ('$select_str') AND `art_id` = $art_id;");
            $amount             = (int)$db->result($r, 0, "amount");
            $amount_bug         = (int)$db->result($r, 0, "amount_bug");
            $amount_collect     = (int)$db->result($r, 0, "amount_collect");
            $storage_sel_string = "";

            if ($amount_bug > 0) {
                $r = $db->query("SELECT `storage_select_bug`, `amount_bug` FROM `J_SELECT_STR_BUG` WHERE `select_id` IN ('$select_str') AND `art_id` = $art_id;");
                $n = $db->num_rows($r);
                for ($i = 1; $i <= $n; $i++) {
                    $storage_select_bug     = $db->result($r, $i - 1, "storage_select_bug");
                    $storage_select_cap     = $this->getManualNameCaption("storage_select_bug", $storage_select_bug);
                    $amount_select_bug      = $db->result($r, $i - 1, "amount_bug");
                    $storage_sel_string     .= "$storage_select_cap: $amount_select_bug {amount_abbr}. <br>";
                }
            }

            $list = "
            {your_order}: $amount {amount_abbr}. <br>
            {rejection_cap}: $amount_bug {amount_abbr}. <br>
            <span style=\"color: red;\">$storage_sel_string</span>
            {shipped_cap}: $amount_collect {amount_abbr}. <br>
            <input id=\"order_id\" type=\"hidden\" value=\"$order_id\">";
        }

        $list = $this->replaceLang($list);

        return $list;
    }

    /*
     * update order str status
     * */
    public function updateOrderArt($order_str_id)
    {
        $order_str_id = $this->getUrlNumber($order_str_id);
        $db = DbSingleton::getDbm();

        $db->query("UPDATE `orders_str_new` SET `status_visible` = 0 WHERE `id` = $order_str_id;");

        $r = $db->query("SELECT `order_id` FROM `orders_str_new` WHERE `id` = $order_str_id LIMIT 1;");
        $order_id = $db->result($r, 0, "order_id");

        $r = $db->query("SELECT MAX(`status_visible`) as maxim FROM `orders_str_new` WHERE `order_id` = $order_id;");
        $status = (int)$db->result($r, 0, "maxim");

        if ($status === 0) {
            $db->query("UPDATE `orders_new` SET `status_visible` = 1 WHERE `id` = $order_id;");
        }

        return $status;
    }

    /*
     * check user order amount
     * */
    public function checkOrderUser($order_id, $user_id): int
    {
        $order_id   = $this->getUrlNumber($order_id);
        $user_id    = $this->getUrlNumber($user_id);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT COUNT(`id`) as kilk FROM `orders_new` WHERE `id` = $order_id AND `client_user_id` = $user_id;");

        return (int)$db->result($r, 0, "kilk");
    }

    /*
     * get DP array from orders
     * */
    public function getDpClient(): array
    {
        $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
        $dp_arr = [];

        $rr = $db->query("SELECT `dp_id` FROM `orders_new` WHERE `client_user_id` = $user_id AND `dp_id` > 0;");
        $nn = $db->num_rows($rr);
        for ($ii = 1; $ii <= $nn; $ii++) {
            $dp_id  = $db->result($rr, $ii - 1, "dp_id");
            $dp_str = explode(",", $dp_id);

            foreach ($dp_str as $jValue) {
                $dp_arr[] = $jValue;
            }
        }

        return $dp_arr;
    }

    /*
     * show orders (DP) in client profile
     * */
    public function showProfileOrders()
    {
        $db = DbSingleton::getDbm();
        $kours = new ExRateClass();
        $client = new ClientClass();

        $form       = $this->getHtmlForm("profile/profile_orders");
        $user_id    = $this->getUser();
        $k          = $summ = 0;
        $list       = "";
        $date_sel   = date("Y-m-d H:i:s", (strtotime("-15 day" , strtotime(date("Y-m-d H:i:s")))));

        $rr = $db->query("SELECT `dp_id` FROM `orders_new` WHERE `client_user_id` = $user_id AND `dp_id` > 0 AND `data` > '$date_sel' ORDER BY `data` DESC;");
        $nn = $db->num_rows($rr);
        for ($ii = 1; $ii <= $nn; $ii++) {
            $dp_id  = $db->result($rr, $ii - 1, "dp_id");
            $dp_arr = explode(",", $dp_id);
            $prefix = $id = $name = $date = $city_name = $delivery_type = $payment_type = $price_summ = $cash_name = $status_type = $bg_bug = "";

            foreach ($dp_arr as $jValue) {
                $dp_value = (int)$jValue;

                $r = $db->query("SELECT dp.*, si.summ as summ_sale 
                FROM `J_DP` dp 
                    LEFT OUTER JOIN `J_SALE_INVOICE` si ON (si.dp_id = dp.id)
                WHERE dp.id = $dp_value;");

                if ($this->checkDpStrExist($dp_value)) {
                    $id             .= $db->result($r, 0, "id") . ",";
                    $prefix         = $db->result($r, 0, "prefix");
                    $dp_user_id     = $db->result($r, 0, "user_id");
                    $client_id      = $db->result($r, 0, "client_id");
                    $date           .= $db->result($r, 0, "time_stamp") . "\n";
                    $name           .= $client->getClientName($dp_user_id, $client_id) . "\n";
                    $city           = $client->getClientInfo($client_id, $dp_user_id)["city"];
                    $delivery       = $db->result($r, 0, "delivery_type_id");
                    $status         = $db->result($r, 0, "status_dp");
                    $cash_id        = $db->result($r, 0, "cash_id");
                    //$summ_sale      = $db->result($r, 0, "summ_sale");
                    $summ_sale      = $db->result($r, 0, "summ");
                    $status_visible = (int)$db->result($r, 0, "status_visible");
                    $price_summ     += (float)$summ_sale;
                    $summ           = $db->result($r, 0, "summ");
                    $city_name      .= $city . "\n";
                    $delivery_type  .= $this->getManualName($delivery) . "\n";
                    $payment_type   .= $this->getManualName(0) . "\n";
                    $status_type    .= $this->getManualName($status) . "\n";
                    $cash_name      .= $kours->getKoursCaption($cash_id) . "\n";

                    if ($price_summ === 0) {
                        $price_summ += (float)$db->result($r, 0, "summ");
                    }

                    if ($status_visible === 1 &&$this->checkSelectDpBug($dp_value)) {
                        $k++;
                    }
                }
            }

            $bg_bug     = ($k > 0) ? "bg-warning" : "";
            $k          = 0;
            $id         = rtrim($id, ",");
            $price_summ = number_format($price_summ, 2, '.', '');

            if ($summ > 0 && $id !== "") {
                $list .= "
                <tr class=\"$bg_bug pointer\" onclick=\"showProfileOrdersArts('$id','')\">
                    <td>$prefix-$id</td>
                    <td>$name</td>
                    <td>$date</td>
                    <td>$city_name</td>
                    <td>$delivery_type</td>
                    <td>$payment_type</td>
                    <td>$price_summ</td>
                    <td>$cash_name</td>
                    <td>$status_type</td>
                </tr>";
            }
        }

        $r2 = $db->query("SELECT * FROM `orders_new` WHERE `client_user_id` = $user_id AND `dp_id` = 0 AND `status` = 1 AND `data` > '$date_sel' ORDER BY `data` DESC;");
        $n2 = $db->num_rows($r2);
        for ($i = 1; $i <= $n2; $i++) {
            $id         = $db->result($r2, $i - 1, "id");
            $name       = $db->result($r2, $i - 1, "name");
            $date       = $db->result($r2, $i - 1, "data");
            $city       = $db->result($r2, $i - 1, "region");
            $delivery   = $db->result($r2, $i - 1, "delivery");
            $payment    = $db->result($r2, $i - 1, "payment");
            $price_summ = $db->result($r2, $i - 1, "price_summ");
            $cash_id    = $db->result($r2, $i - 1, "cash_id");
            $price_summ = $kours->getKoursFromUAH($price_summ, $cash_id);
            $city_name  = $this->getCityName($city);
            $del_tp     = $this->getManualName($delivery);
            $pay_tp     = $this->getManualName($payment);
            $cash_name  = $kours->getKoursCaption($cash_id);

            $list .= "
            <tr class=\"pointer\" onclick=\"showProfileOrdersArts('','$id')\">
                <td>{order_cap} #$id</td>
                <td>$name</td>
                <td>$date</td>
                <td>$city_name</td>
                <td>$del_tp</td>
                <td>$pay_tp</td>
                <td>$price_summ</td>
                <td>$cash_name</td>
                <td>{order_in_queue}</td>
            </tr>";
        }

        $form = str_replace("{orders_range}", $list, $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * show orders (DP) items in client profile
     * dp_check: dp_id
     * order_check:
     * */
    public function showProfileOrdersArts($dp_check, $order_check)
    {
        $dp_check       = $this->getUrlNumber($dp_check);
        $order_check    = $this->getUrlNumber($order_check);

        $db = DbSingleton::getDbm();
        $kours = new ExRateClass();

        $list       = "";
        $form       = $this->getHtmlForm("profile/profile_orders_arts");
        $user_id    = $this->getUser();
        $client_id  = $this->getClient();
        $date_sel   = date("Y-m-d H:i:s", (strtotime("-15 day" , strtotime(date("Y-m-d H:i:s")))));
        $dp_arr     = (!empty($dp_check)) ? explode(",", $dp_check) : $this->getDpClient();

        // Dp orders arts
        if (empty($order_check)) {
            foreach ($dp_arr as $jjValue) {
                $nedp = false;
                $dp_value = $jjValue;
                $where_dp_client = ($dp_check !== "") ? "WHERE `id` = '$dp_value' AND `client_id` = $client_id" : "WHERE `client_id` = $client_id";

                $r = $db->query("SELECT `id`, `prefix` FROM `J_DP` $where_dp_client;");
                $ndp = $db->num_rows($r);

                if ($ndp > 0) {
                    $dp_id  = $db->result($r, 0, "id") + 0;
                    $prefix = $db->result($r, 0, "prefix");

                    if ($dp_check !== "") {
                        $where_dp = "WHERE dp.`dp_id` = $dp_id AND ord.`dp_str_id` != 0";
                    } else {
                        $dp_id = $jjValue;
                        $where_dp = "WHERE dp.`dp_id` = $dp_id AND ord.`dp_str_id` != 0";
                    }

                    $r_str = $db->query("SELECT dp.*, ord.order_id, ord.id as order_str_id 
                    FROM `orders_str_new` ord
                        LEFT OUTER JOIN `J_DP_STR` dp ON (dp.id = ord.dp_str_id)
                    $where_dp 
                    GROUP BY dp.art_id;");
                    $n_str = $db->num_rows($r_str);

                    if ($n_str === 0) {
                        $r_str = $db->query("SELECT * FROM `J_DP_STR` WHERE `dp_id` = $dp_id GROUP BY `art_id`;");
                        $n_str = $db->num_rows($r_str);
                        $nedp = true;
                    }

                    for ($j = 1; $j <= $n_str; $j++) {
                        $order_id       = $db->result($r_str, $j - 1, "order_id");
                        $order_str_id   = $db->result($r_str, $j - 1, "order_str_id") + 0;
                        $art_nr_ds      = $db->result($r_str, $j - 1, "article_nr_displ");
                        $art_id         = $db->result($r_str, $j - 1, "art_id");
                        $brand_id       = $db->result($r_str, $j - 1, "brand_id");
                        $amount         = (int)$db->result($r_str, $j - 1, "amount");
                        $amount_collect = (int)$db->result($r_str, $j - 1, "amount_collect");
                        $summ           = $db->result($r_str, $j - 1, "summ");
                        $status_dps     = $db->result($r_str, $j - 1, "status_dps");
                        $status_visible = (int)$db->result($r_str, $j - 1, "status_visible");
                        $price          = round($summ / $amount, 2);
                        $status_dps     = $this->getManualName($status_dps);
                        $brand_name     = $this->getBrandName($brand_id);

                        if ($status_visible === 1 && $this->checkSelectStrDpBug($dp_id, $art_id) > 0) {
                            $db->query("UPDATE `orders_str_new` SET `status_visible` = 1 WHERE `id` = $order_str_id;");
                            $btn_bug = "<button class=\"btn-basket\" onclick=\"closeOrderArtUpdate('$dp_id', '$art_id', '$order_str_id');\"><span class=\"fas fa-eye\"></span></button>";
                            $bg_bug = "bg-warning";
                        } else {
                            $btn_bug = "";
                            $bg_bug = "";
                        }

                        $price = number_format($price, 2, '.', '');
                        $summ = number_format($summ, 2, '.', '');

                        $amount_text = ($this->checkSelectStrDpBug($dp_id, $art_id) > 0) ? "$amount_collect ($amount)" : $amount;

                        if ($nedp) {
                            $list .= "
                            <tr class=\"$bg_bug\">
                                <td>$prefix-$dp_id</td>
                                <td>$art_nr_ds</td>
                                <td>$brand_name</td>
                                <td><p class=\"text-center\">$amount_text</p> $btn_bug</td>
                                <td>$price</td>
                                <td>$summ</td>
                                <td>$status_dps</td>
                            </tr>";
                        }

                        if ($this->checkOrderUser($order_id, $user_id) > 0) {
                            $list .= "
                            <tr class=\"$bg_bug\">
                                <td>$prefix-$dp_id</td>
                                <td>$art_nr_ds</td>
                                <td>$brand_name</td>
                                <td><p class=\"text-center\">$amount_text</p> $btn_bug</td>
                                <td>$price</td>
                                <td>$summ</td>
                                <td>$status_dps</td>
                            </tr>";
                        }
                    }
                }
            }
        }

        // Site orders arts
        if (empty($dp_check)) {
            $where_order = (!empty($order_check)) ? "AND `id` = '$order_check'" : "";

            $r = $db->query("SELECT `id`, `cash_id` FROM `orders_new` WHERE `client_user_id` = $user_id AND `dp_id` = 0 AND `status` = 1 $where_order AND `data` > '$date_sel' ORDER BY `data` DESC;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $order_id   = $db->result($r, $i - 1, "id") + 0;
                $cash_id    = $db->result($r, $i - 1, "cash_id");

                $r_str = $db->query("SELECT `art_id`, `brand_id`, `amount`, `price`, `summ` FROM `orders_str_new` WHERE `order_id` = $order_id;");
                $n_str = $db->num_rows($r_str);
                for ($j = 1; $j <= $n_str; $j++) {
                    $art_id     = $db->result($r_str, $j - 1, "art_id");
                    $brand_id   = $db->result($r_str, $j - 1, "brand_id");
                    $amount     = (int)$db->result($r_str, $j - 1, "amount");
                    $price      = $db->result($r_str, $j - 1, "price");
                    $summ       = $db->result($r_str, $j - 1, "summ");
                    $art_nr_ds  = $this->getArticleDispl($art_id);
                    $brand_name = $this->getBrandName($brand_id);
                    $price      = $kours->getKoursFromUAH($price, $cash_id);
                    $summ       = $kours->getKoursFromUAH($summ, $cash_id);

                    $list .= "
                    <tr>
                        <td>{order_cap} #$order_id</td>
                        <td>$art_nr_ds</td>
                        <td>$brand_name</td>
                        <td><p class=\"text-center\">$amount</p></td>
                        <td>$price</td>
                        <td>$summ</td>
                        <td>{making_order_cap}</td>
                    </tr>";
                }
            }
        }

        $form = str_replace("{orders_range}", $list, $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * doc_type: 1-income, 2-move, 3-sale, 4-back client, 5-back suppl, 6-write_off
     * */
    public function showProfileDocs($td_id, $doc_id, $doc_type_id)
    {
        $db = DbSingleton::getDbm();
        switch ($doc_type_id) {
            case 1: {
                $table = "J_SALE_INVOICE";
                $table_str = "invoice_id";
                $price_str = "price_end";
                break;
            }
            case 5: {
                $table = "J_BACK_CLIENTS";
                $table_str = "back_id";
                $price_str = "price";
                break;
            }
            default: {
                $table = "";
                $table_str = "";
                $price_str = "price";
                break;
            }
        }

        $list = "";

        if ($table !== "") {
            $table .= "_STR";

            $r = $db->query("SELECT * FROM `$table` WHERE `$table_str` = $doc_id;");
            $n = $db->num_rows($r);

            $idd = "td-" . $td_id;

            $list .= "<tr id='$idd'><td colspan='9'><table>
            <tr style='background: #e1e7ec'>
                <td>#</td>
                <td>{art_cap}</td>
                <td>{caption_cap}</td>
                <td>{amount_cap}</td>
                <td>{price_cap}</td>
                <td>{summ_cap}</td>
            </tr>";

            for ($i = 1; $i <= $n; $i++) {
                $art_id     = $db->result($r, $i - 1, "art_id");
                $art_nr_ds  = $db->result($r, $i - 1, "article_nr_displ");
                $art_name   = $this->getArticleName($art_id);
                $amount     = $db->result($r, $i - 1, "amount");
                $price      = $db->result($r, $i - 1, $price_str);
                $sum        = $db->result($r, $i - 1, "summ");

                $list .= "
                <tr style='background: #e1e7ec'>
                    <td>$i</td>
                    <td>$art_nr_ds</td>
                    <td>$art_name</td>
                    <td>$amount</td>
                    <td>$price</td>
                    <td>$sum</td>
                </tr>";
            }

            $list .= "</table></td></tr>";
        }

        $list = $this->replaceLang($list);

        return $list;
    }

    public function showProfileCheckForm($data_from = "", $data_to = "")
    {
        $db = DbSingleton::getDbm();
        $kours = new ExRateClass();
        $client = new ClientClass();

        $data_from  = $this->getNameString($data_from);
        $data_to    = $this->getNameString($data_to);

        if ((int)$data_from === 0 || $data_from === "") {
            $data_from = date("Y-m-01");
        }

        if ((int)$data_to === 0 || $data_to === "") {
            $data_to = date("Y-m-d");
        }

        $client_id      = $this->getClient();
        $balans_after   = 0;
        $saldo_end      = 0;
        $saldo_end      = number_format((float)$saldo_end, 2, '.', '');
        $list           = "";

        $r = $db->query("SELECT b.*, mc.abr as cash_name, pmc.abr as cash_abr
        FROM `B_CLIENT_BALANS_JOURNAL` b 
			LEFT JOIN `CASH` mc ON (mc.id = b.cash_id) 
			LEFT JOIN `CASH` pmc ON (pmc.id = b.pay_cash_id) 
        WHERE b.client_id = $client_id AND b.data >= '$data_from 00:00:00' AND b.data <= '$data_to 23:59:59' 
        ORDER BY b.id;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $data           = $db->result($r, $i - 1, "data");
                $cash_name      = $db->result($r, $i - 1, "cash_name");
                $summ           = round($db->result($r, $i - 1, "summ"), 2);
                $deb_kre        = (int)$db->result($r, $i - 1, "deb_kre");
                $balans_before  = $db->result($r, $i - 1, "balans_before");
                $balans_after   = $db->result($r, $i - 1, "balans_after");
                $doc_type_id    = (int)$db->result($r, $i - 1, "doc_type_id");
                $doc_id         = $db->result($r, $i - 1, "doc_id");
                $pay_cash_name  = $db->result($r, $i - 1, "cash_abr");
                $pay_summ       = $db->result($r, $i - 1, "pay_summ");
                $document_name  = "";

                if ($doc_type_id === 1) {
                    $document_name = $this->getSaleInvoiceName($doc_id);
                }

                if ($doc_type_id === 2) {
                    list($jpay_doc_type_id, $document_name) = $this->getJPayName($doc_id);
                    if ($jpay_doc_type_id === 99) {
                        $summ = "";
                    }
                }

                if ($doc_type_id === 3) {
                    $document_name = $this->getJPayName($doc_id)[1];
                }

                if ($doc_type_id === 5) {
                    $document_name = $this->getBackClientsName($doc_id);
                }

                $debit = $kredit = "";
                if ($deb_kre === 1) {
                    $debit = $summ;
                    $saldo_end -= $debit;
                }
                if ($deb_kre === 2) {
                    $kredit = $summ;
                    $saldo_end += $kredit;
                }

                $onclick = "showProfileDocs($i, $doc_id, $doc_type_id);";
                $list .= "
                <tr id=\"tr-$i\" class=\"text-center pointer\" onclick=\"$onclick\">
                    <td>$i</td>
                    <td>$data</td>
                    <td>$cash_name</td>
                    <td>$balans_before</td>
                    <td>$debit</td>
                    <td>$kredit</td>
                    <td>$balans_after</td>
                    <td>$document_name</td>
                    <td>$pay_summ $pay_cash_name</td>
                </tr>";
            }

            $saldo_end = round($balans_after, 2);

        } else {
            $list = "
            <tr>
                <td class=\"text-center\" colspan=\"9\">" . $this->err1 . "</td>
            </tr>
            </table>";
        }

        if ($n === 0) {
            $r = $db->query("SELECT `balans_after` FROM `B_CLIENT_BALANS_JOURNAL` WHERE `client_id` = $client_id ORDER BY `data` DESC LIMIT 1;");
            $balans_after = $db->result($r, 0, "balans_after");
            $saldo_end = round($balans_after, 2);
        }

        $form = $this->getHtmlForm("profile/profile_check");
        $saldo_start_cap = $saldo_end_cap = "";

        $client_cash_id = $client->getClientCurrency($client_id);
        list($saldo_start, $saldo_cash_id) = $this->getClientBalansPeriodStart($client_id, $client_cash_id, $data_from, 0);

        $saldo_data_start = date("Y-m-01");
        if ($saldo_start < 0) {
            $saldo_start_cap = " (<span class=\"span-red\">{debt_cap}</span>)";
        }
        elseif ($saldo_start > 0) {
            $saldo_start_cap = " (<span class=\"span-green\">{prepayment}</span>)";
        }

        $form = str_replace(array("{saldo_start_data}", "{saldo_start_date}"), array($saldo_start . " " . $kours->getKoursCaption($saldo_cash_id) . $saldo_start_cap, $saldo_data_start), $form);

        $saldo_data_end = date("Y-m-d");
        if ($saldo_end < 0) {
            $saldo_end_cap = " (<span class=\"span-red\">{debt_cap}</span>)";
        }
        elseif ($saldo_end > 0) {
            $saldo_end_cap = " (<span class=\"span-green\">{prepayment}</span>)";
        }

        $form = str_replace(array("{saldo_end_data}", "{saldo_end_date}", "{profile_check_range}"), array($saldo_end . " " . $kours->getKoursCaption($saldo_cash_id) . $saldo_end_cap, $saldo_data_end, $list), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    public function getClientBalansPeriodStart($client_id, $cash_id_sel, $data_from, $recursion): array
    {
        $db = DbSingleton::getDbm();
        $cash_id = 1;
        $saldo_start = 0;
        $saldo_data_start = $data_from;

        $r = $db->query("SELECT `saldo_start`, `cash_id`, `data_start` FROM `B_CLIENT_BALANS_PERIOD` WHERE `client_id` = $client_id AND `data_start` = '" . date("Y-m-01", strtotime($data_from)) . "' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $saldo_start = $db->result($r, 0, "saldo_start");
            $cash_id = $db->result($r, 0, "cash_id");
            $saldo_data_start = $db->result($r, 0, "data_start");
        }

        if ($n === 0) {
            $recursion++;
            if ($recursion < 12) {
                $data_from = date("Y-m-01", strtotime("$data_from -1 month"));
                list($saldo_start, , $saldo_data_start) = $this->getClientBalansPeriodStart($client_id, $cash_id_sel, $data_from, $recursion);
            } else {
                $data_main_start = date("Y-m-01", strtotime($data_from));
                $db->query("INSERT INTO `B_CLIENT_BALANS_PERIOD` (`client_id`,`cash_id`,`saldo_start`,`data_start`,`active`) 
                VALUES ('$client_id','$cash_id_sel','0','$data_main_start','1');");
                $data_plus_month = date("Y-m-d", strtotime("$data_main_start +1 month"));
                $data_from = date("Y-m-01", strtotime($data_plus_month));
                $recursion -= 2;
                list($saldo_start, , $saldo_data_start) = $this->getClientBalansPeriodStart($client_id, $cash_id_sel, $data_from, $recursion);
            }

            $cash_id = $cash_id_sel;
        }

        return array($saldo_start, $cash_id, $saldo_data_start);
    }

    /*
     * set price list cron
     * */
    public function setPriceList(): string
    {
        $db = DbSingleton::getDbm();

        $user_id    = $this->getUser();
        $date       = date("Y-m-d H:i:s");
        $date_fr    = date("Y-m-d_H-i-s");
        $filename   = "TOKO_GROUP_price-list_" . $user_id . "_" . $date_fr . ".csv";

        $r = $db->query("SELECT `status` FROM `cron_task_prices` WHERE `user_id` = $user_id AND `status` = 1;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            return "forming...";
        }

        $db->query("INSERT INTO `cron_task_prices` (`user_id`, `filename`, `date`, `status`) VALUES ($user_id, '$filename', '$date', 1);");

        return "date-start: " . $date;
    }

    public function getPriceProfileList(): array
    {
        $catalogue = new CatalogueClass();

        $user_id    = $this->getUser();
        $csv        = "";
        $date       = date("Y-m-d_H-i-s");
        $filedir    = $user_id . "/" . $user_id . "_price-list_" . $date . ".csv";
        $filename   = $user_id . "_price-list_" . $date . ".csv";
        $list       = $catalogue->getPriceList();

        foreach ($list as $record) {
            foreach ($record as $rec) {
                $csv .= $rec . ';';
            }
            $csv .= "\n";
        }
        if (!file_exists(RDD . "/uploads/$user_id")) {
            if (!mkdir($concurrentDirectory = RDD . "/uploads/$user_id", 0777, true) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        elseif (file_exists(RDD . "/uploads/$user_id/")) {
            foreach (glob(RDD . "/uploads/$user_id/*") as $file) {
                unlink($file);
            }
        }
        $csv_handler = fopen(RDD . "/uploads/$filedir", 'wb') or die("Can't create file");
        fwrite($csv_handler, $csv);
        fclose($csv_handler);

        return array($filename, $filedir);
    }

    public function showPriceList()
    {
        $db = DbSingleton::getDbm();

        $user_id    = $this->getUser();
        $disable    = "disabled";
        $visible    = "style=\"display:none;\"";
        $history_fr = "";

        $r = $db->query("SELECT 1 FROM `cron_task_prices` WHERE `user_id` = $user_id AND `status` = 1;");
        $n = $db->num_rows($r);
        if ($n === 0) {
            $disable = "";
            $visible = "";
        }

        $filename = scandir(RDD . "/uploads/$user_id")[2];

        if ($filename !== "") {
            $file = "$this->uploads/$user_id/" . $filename;
            $list = "
            <a class=\"btn btn-primary\" href=\"$file\" download $visible><span class='fa fa-download'></span> Download $filename</a><br>";
            $list_excel = "
            <a class=\"btn btn-primary\" href=\"https://toko.ua/cron/excel.php/?user=$user_id\"  $visible><span class='fa fa-download'></span> Download Excel </a><br>";
        } else {
            $list = "";
            $list_excel = "";
        }

        $r = $db->query("SELECT `filename`, `date`, `date_end`, `status` FROM `cron_task_prices` WHERE `user_id` = $user_id ORDER BY `date` DESC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $table = "";
            for ($i = 1; $i <= $n; $i++) {
                $filename   = $db->result($r, $i - 1, "filename");
                $date       = $db->result($r, $i - 1, "date");
                $date_end   = $db->result($r, $i - 1, "date_end");
                $status     = (int)$db->result($r, $i - 1, "status");
                $status_nm  = $this->getStatusProfilePrice($status);
                $current    = ($status === 2 && $i === 1) ? "style=\"background:#f1f1f1;\"" : "";

                if ($date_end === "0000-00-00 00:00:00") {
                    $date_end = "-";
                }

                $table .= "
                <tr $current>
					<td>$filename</td>
					<td>$date</td>
					<td>$date_end</td>
					<td>$status_nm</td>
				</tr>";
            }

            $history_fr = $this->getHtmlForm("profile/profile_price_table");
            $history_fr = str_replace("{price_range}", $table, $history_fr);
        }

        $form = $this->getHtmlForm("profile/profile_price_list");
        $form = str_replace(array("{price_download}", "{price_download_excel}", "{price_disabled}", "{price_history}"), array($list, $list_excel, $disable, $history_fr), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * get profile status
     * */
    public function getStatusProfilePrice($status)
    {
        $text = ($status === 2) ? "{status_off}" : "{status_on}";
        $text = $this->replaceLang($text);

        return $text;
    }

    /*
     * show registration form
     * */
    public function showRegistrationForm()
    {
        $menu = new MenuClass();
        $shop = new ShopClass();
        $form = $this->getHtmlForm("profile/registration");
        $form = str_replace(array("{type_form}", "{region_form}", "{category_options}", "{tpoint_options}", "{user_city_main_list}"), array($menu->showTypeForm(), $menu->getRegionForm(), $this->getManualOptions("customers_categories"), $this->getRegionSelectProfile(), $shop->getCitiesMainSelect()), $form);

        return $form;
    }

    public function getRegionSelectProfile(): string
    {
        $db = DbSingleton::getDbm();
        $options = "";

        $r = $db->query("SELECT `id`, `full_name`, `address` FROM `T_POINT` WHERE `status` = 1 ORDER BY `full_name`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id     = $db->result($r, $i - 1, "id");
            $region = $db->result($r, $i - 1, "full_name");
            $address = $db->result($r, $i - 1, "address");

            $options .= "
            <option value=\"$id\">$region ($address)</option>";
        }

        return $options;
    }

    /*
    * download prices
    * */
    public function downloadPrices(): bool
    {
        $db = DbSingleton::getTokoDb();
        $dbm = DbSingleton::getDbm();

        $r = $dbm->query("SELECT `user_id`, `date`, `filename` FROM `cron_task_prices` WHERE `status` = 1;");
        $n = $dbm->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $user_id    = $db->result($r, $i - 1, "user_id");
                $filename   = $user_id . "/" . $dbm->result($r, $i - 1, "filename");

                $csv = "";
                foreach ($this->getPriceProfileList() as $record) {
                    $csv .= $record . ';';
                    $csv .= "\n";
                }

                if (!file_exists(RDD . "/uploads/$user_id")) {
                    if (!mkdir($concurrentDirectory = RDD . "/uploads/$user_id", 0777, true) && !is_dir($concurrentDirectory)) {
                        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                    }
                }
                elseif (file_exists(RDD . "/uploads/$user_id/")) {
                    foreach (glob(RDD . "/uploads/$user_id/*") as $file) {
                        unlink($file);
                    }
                }

                $csv_handler = fopen(RDD . "/uploads/$filename", 'wb') or die("Can't create file");
                fwrite($csv_handler, $csv);
                fclose($csv_handler);
                $date_end = date("Y-m-d H:i:s");
                $dbm->query("UPDATE `cron_task_prices` SET `status` = 2, `date_end` = '$date_end' WHERE `user_id` = $user_id AND `status` = 1;");
            }
        }

        return true;
    }

}