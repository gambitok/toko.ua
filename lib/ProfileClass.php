<?php

class ProfileClass extends ClientClass
{

    public $page_sign_in    = "signin/";
    public $page_profile    = "profile/orders/";
    public $page_registration   = "registration/";
    public $page_news   = "news/";

    /*
     * get profile left navigation
     * */
    public function getProfileClientInfo()
    {
        list($client_id, $user_id) = $this->getClientData();
        $name = $this->getClientInfo($client_id, $user_id)["name"];

        return ($user_id === 0)
            ? false
            : "{hello_cap}, " . $this->getHtmlTag("a", $name, ['href' => $this->getSiteLink() . $this->page_profile . "/"]);
    }

    /*
     * get profile right navigation
     * */
    public function getProfileInfo()
    {
        $form = str_replace("{reg_link}", $this->getSiteLink() . $this->page_registration, $this->getHtmlForm("menu/profile_nav"));

        if ($this->getUser() === 0) {
            $form = str_replace("{reg_login}", "none", $form);
        } else {
            $form = str_replace("{reg_logout}", "none", $form);
        }

        return str_replace(array("{reg_login}", "{reg_logout}"), "", $form);
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
            $categories[] = $db->result($r, $i - 1, "client_category");
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
        $counter = ($n > 0) ? $this->getHtmlTag("span", "($n)", ['class' => 'authorization-item__counter']) : "";

        $info = "";

        if ($user_id > 0 && ($n1 > 0 || $n2 > 0)) {
            $link = $this->getSiteLink() . "special_offers/";
            $info = str_replace(array("{link}", "{counter}"), array($link, $counter), $this->getHtmlForm("special_offers/info"));
        }

        return $info;
    }

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

        $counter = ($user_id > 0 && $n > 0) ? $this->getHtmlTag("span", "($n)", ['class' => 'authorization-item__counter']) : "";
        $link = $this->getSiteLink() . $this->page_news;

        return str_replace(array("{link}", "{counter}"), array($link, $counter), $this->getHtmlForm("news/info"));
    }

    public function getProfileInfoMobile()
    {
        $info = ($this->getUser() === 0)
            ? $this->getHtmlTag("a", "{authorization}", ['href' => $this->getSiteLink() . "$this->page_sign_in/"])
            : $this->getHtmlTag("a", "{profile}", ['href' => $this->getSiteLink() . "$this->page_profile/"]);

        return $this->replaceLang($info);
    }

    public function showProfileForm()
    {
        list($client_id, $user_id) = $this->getClientData();
        $name = $this->getClientInfo($client_id, $user_id)["name"];

        $form = $this->getHtmlForm("profile/profile");
        $form = str_replace(array("{client_name}", "{client_id}"), array($name, $client_id), $form);

        return $this->replaceLang($form);
    }

    public function getClientBonus($client_id): bool
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT 1 FROM `T2_BONUS_CLIENT` WHERE `CLIENT_ID` = $client_id;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    public function showClientBonus($client_id)
    {
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT `bonus_balance` FROM `A_CLIENTS` WHERE `id` = $client_id;");
        $n = $db->num_rows($r);
        $form = "";

        if ($n > 0) {
            $form = str_replace("{bonus_profile}", $db->result($r, 0, "bonus_balance"), $this->getHtmlForm("profile/profile_bonus"));
        }

        return $form;
    }

    public function showProfileAccount()
    {
        $menu = new MenuClass();
        list($client_id, $user_id) = $this->getClientData();
        $clientData = $this->getClientInfo($client_id, $user_id);

        $form = str_replace(
            array("{client_id}", "{client_phone}", "{client_password}", "{client_email}", "{client_name}", "{type_form}", "{client_country}", "{region_form}", "{client_city}", "{bonus_user}"),
            array($user_id, $clientData["phone"], $clientData["password"], $clientData["email"], $clientData["name"], $menu->showTypeForm($clientData["type"]), $clientData["country"], $menu->getRegionForm($clientData["region"]), $clientData["city"], $this->getClientBonus($client_id) ? $this->showClientBonus($client_id) : ""),
        $this->getHtmlForm("profile/profile_account"));

        return $this->replaceLang($form);
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

    public function checkSelectDpBug($db, $dp_id): bool
    {
        $dp_id = $this->getUrlNumber($dp_id);

        $select_arr = [];
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

            $list = str_replace(
                array("{amount}", "{amount_bug}", "{storage_sel_string}", "{amount_collect}", "{order_id}"),
                array($amount, $amount_bug, $storage_sel_string, $amount_collect, $order_id),
            $this->getHtmlForm("orders/art_update"));
        }

        return $this->replaceLang($list);
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
    public function checkOrderUser($db, $order_id, $user_id): int
    {
        $order_id   = $this->getUrlNumber($order_id);
        $user_id    = $this->getUrlNumber($user_id);
        $r = $db->query("SELECT COUNT(`id`) as count_ids FROM `orders_new` WHERE `id` = $order_id AND `client_user_id` = $user_id;");

        return (int)$db->result($r, 0, "count_ids");
    }

    /*
     * get DP array from orders
     * */
    public function getDpClient($db): array
    {
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

    public function showProfileOrders()
    {
        $db = DbSingleton::getDbm();
        $exRate = new ExRateClass();
        $client = new ClientClass();

        $user_id    = $this->getUser();
        $k          = $summary = 0;
        $list       = "";
        $date_sel   = date("Y-m-d H:i:s", (strtotime("-1 year" , strtotime(date("Y-m-d H:i:s")))));

        $rr = $db->query("SELECT `dp_id` FROM `orders_new` WHERE `client_user_id` = $user_id AND `dp_id` > 0 AND `data` > '$date_sel' ORDER BY `data` DESC;");
        $nn = $db->num_rows($rr);
        for ($ii = 1; $ii <= $nn; $ii++) {
            $dp_id  = $db->result($rr, $ii - 1, "dp_id");
            $dp_arr = explode(",", $dp_id);
            $prefix = $id = $name = $date = $city_name = $delivery_type = $payment_type = $price_summary = $cash_name = $status_type = "";

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
                    $name           .= $client->getClientName($db, $dp_user_id, $client_id) . "\n";
                    $city           = $this->getClientInfo($client_id, $dp_user_id)["city"];
                    $delivery       = $db->result($r, 0, "delivery_type_id");
                    $status         = $db->result($r, 0, "status_dp");
                    $cash_id        = $db->result($r, 0, "cash_id");
                    $summary_sale   = $db->result($r, 0, "summ");
                    $status_visible = (int)$db->result($r, 0, "status_visible");
                    $price_summary  += (float)$summary_sale;
                    $summary        = $db->result($r, 0, "summ");
                    $city_name      .= $city . "\n";
                    $delivery_type  .= $this->getManualName($delivery) . "\n";
                    $payment_type   .= $this->getManualName(0) . "\n";
                    $status_type    .= $this->getManualName($status) . "\n";
                    $cash_name      .= $exRate->getExRateCaption($cash_id) . "\n";

                    if ($price_summary === 0) {
                        $price_summary += (float)$db->result($r, 0, "summ");
                    }

                    if ($status_visible === 1 && $this->checkSelectDpBug($db, $dp_value)) {
                        $k++;
                    }
                }
            }

            $bg_bug     = ($k > 0) ? "bg-warning" : "";
            $k          = 0;
            $id         = rtrim($id, ",");
            $price_summary = number_format($price_summary, 2, '.', '');

            if ($summary > 0 && $id !== "") {
                $list .= str_replace(
                    array("{param1}", "{param2}", "{id}", "{bg_bug}", "{prefix}", "{name}", "{date}", "{city_name}", "{delivery_type}", "{payment_type}", "{price_summary}", "{cash_name}", "{status_type}"),
                    array($id, "", $id, $bg_bug, $prefix . "-", $name, $date, $city_name, $delivery_type, $payment_type, $price_summary, $cash_name, $status_type),
                $this->getHtmlForm("profile/orders"));
            }
        }

        $r2 = $db->query("SELECT * FROM `orders_new` WHERE `client_user_id` = $user_id AND `dp_id` = 0 AND `status` = 1 AND `data` > '$date_sel' ORDER BY `data` DESC;");
        $n2 = $db->num_rows($r2);
        for ($i = 1; $i <= $n2; $i++) {
            $id             = $db->result($r2, $i - 1, "id");
            $name           = $db->result($r2, $i - 1, "name");
            $date           = $db->result($r2, $i - 1, "data");
            $cash_id        = $db->result($r2, $i - 1, "cash_id");
            $price_summary  = $exRate->getExRateFromUAH($db->result($r2, $i - 1, "price_summ"), $cash_id);
            $city_name      = $this->getCityName($db->result($r2, $i - 1, "region"));
            $delivery_type  = $this->getManualName($db->result($r2, $i - 1, "delivery"));
            $payment_type   = $this->getManualName($db->result($r2, $i - 1, "payment"));
            $cash_name      = $exRate->getExRateCaption($cash_id);

            $list .= str_replace(
                array("{param1}", "{param2}", "{id}", "{bg_bug}", "{prefix}", "{name}", "{date}", "{city_name}", "{delivery_type}", "{payment_type}", "{price_summary}", "{cash_name}", "{status_type}"),
                array("", $id, $id, "", "{order_cap} #", $name, $date, $city_name, $delivery_type, $payment_type, $price_summary, $cash_name, "{order_in_queue}"),
            $this->getHtmlForm("profile/orders"));
        }

        $form = str_replace("{orders_range}", $list, $this->getHtmlForm("profile/profile_orders"));

        return $this->replaceLang($form);
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
        $exRate = new ExRateClass();

        $list       = "";
        $user_id    = $this->getUser();
        $client_id  = $this->getClient();
        $date_sel   = date("Y-m-d H:i:s", (strtotime("-15 day" , strtotime(date("Y-m-d H:i:s")))));
        $dp_arr     = (!empty($dp_check)) ? explode(",", $dp_check) : $this->getDpClient($db);

        // Dp orders arts
        if (empty($order_check)) {
            foreach ($dp_arr as $jjValue) {
                $dp_status = false;
                $dp_value = $jjValue;
                $where_dp_client = ($dp_check !== "") ? "WHERE `id` = '$dp_value' AND `client_id` = $client_id" : "WHERE `client_id` = $client_id";

                $r = $db->query("SELECT `id`, `prefix` FROM `J_DP` $where_dp_client;");
                $ndp = $db->num_rows($r);

                if ($ndp > 0) {
                    $dp_id  = $db->result($r, 0, "id") + 0;
                    $prefix = $db->result($r, 0, "prefix");

                    $where_dp = "WHERE dp.`dp_id` = $dp_id AND ord.`dp_str_id` != 0";

                    $r_str = $db->query("SELECT dp.*, ord.order_id, ord.id as order_str_id 
                    FROM `orders_str_new` ord
                        LEFT OUTER JOIN `J_DP_STR` dp ON (dp.id = ord.dp_str_id)
                    $where_dp 
                    GROUP BY dp.art_id;");
                    $n_str = $db->num_rows($r_str);

                    if ($n_str === 0) {
                        $r_str = $db->query("SELECT * FROM `J_DP_STR` WHERE `dp_id` = $dp_id GROUP BY `art_id`;");
                        $n_str = $db->num_rows($r_str);
                        $dp_status = true;
                    }

                    for ($j = 1; $j <= $n_str; $j++) {
                        $order_id       = $db->result($r_str, $j - 1, "order_id");
                        $order_str_id   = $db->result($r_str, $j - 1, "order_str_id") + 0;
                        $art_nr_ds      = $db->result($r_str, $j - 1, "article_nr_displ");
                        $art_id         = $db->result($r_str, $j - 1, "art_id");
                        $brand_id       = $db->result($r_str, $j - 1, "brand_id");
                        $amount         = (int)$db->result($r_str, $j - 1, "amount");
                        $amount_collect = (int)$db->result($r_str, $j - 1, "amount_collect");
                        $summary        = $db->result($r_str, $j - 1, "summ");
                        $status_dps     = $db->result($r_str, $j - 1, "status_dps");
                        $status_visible = (int)$db->result($r_str, $j - 1, "status_visible");
                        $price          = round($summary / $amount, 2);
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
                        $summary = number_format($summary, 2, '.', '');

                        $amount_text = ($this->checkSelectStrDpBug($dp_id, $art_id) > 0)
                            ? "$amount_collect ($amount)"
                            : $amount;

                        if ($dp_status || $this->checkOrderUser($db, $order_id, $user_id) > 0) {
                            $list .= str_replace(
                                array("{bg_bug}", "{prefix}", "{dp_id}", "{art_nr_ds}", "{brand_name}", "{amount_text}", "{btn_bug}", "{price}", "{summary}", "{status_dps}"),
                                array($bg_bug, $prefix . "-", $dp_id, $art_nr_ds, $brand_name, $amount_text, $btn_bug, $price, $summary, $status_dps),
                            $this->getHtmlForm("profile/dp"));
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
                    $summary    = $db->result($r_str, $j - 1, "summ");
                    $art_nr_ds  = $this->getArticleDisplay($art_id);
                    $brand_name = $this->getBrandName($brand_id);
                    $price      = $exRate->getExRateFromUAH($price, $cash_id);
                    $summary    = $exRate->getExRateFromUAH($summary, $cash_id);

                    $list .= str_replace(
                        array("{bg_bug}", "{prefix}", "{dp_id}", "{art_nr_ds}", "{brand_name}", "{amount_text}", "{btn_bug}", "{price}", "{summary}", "{status_dps}"),
                        array("", "{order_cap} #", $order_id, $art_nr_ds, $brand_name, $amount, "", $price, $summary, "{making_order_cap}"),
                    $this->getHtmlForm("profile/dp"));
                }
            }
        }

        $form = str_replace("{orders_range}", $list, $this->getHtmlForm("profile/profile_orders_arts"));

        return $this->replaceLang($form);
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

        $form = "";

        if ($table !== "") {
            $table .= "_STR";

            $r = $db->query("SELECT * FROM `$table` WHERE `$table_str` = $doc_id;");
            $n = $db->num_rows($r);

            $idd = "td-" . $td_id;
            $list = "";

            for ($i = 1; $i <= $n; $i++) {
                $art_id     = $db->result($r, $i - 1, "art_id");
                $art_nr_ds  = $db->result($r, $i - 1, "article_nr_displ");
                $art_name   = $this->getArticleName($art_id);
                $amount     = $db->result($r, $i - 1, "amount");
                $price      = $db->result($r, $i - 1, $price_str);
                $sum        = $db->result($r, $i - 1, "summ");

                $list .= str_replace(
                    array("{id}", "{art_nr_ds}", "{art_name}", "{amount}", "{price}", "{sum}"),
                    array($i, $art_nr_ds, $art_name, $amount, $price, $sum),
                $this->getHtmlForm("profile/docs"));
            }

            $form = str_replace(array("{id}", "{list}"), array($idd, $list), $this->getHtmlForm("profile/table"));

            $form = str_replace(
                array("{id}", "{art_nr_ds}", "{art_name}", "{amount}", "{price}", "{sum}"),
                array("#", "{art_cap}", "{caption_cap}", "{amount_cap}", "{price_cap}", "{summ_cap}"),
            $this->getHtmlForm("profile/docs")) . $form;
        }

        return $this->replaceLang($form);
    }

    public function showProfileCheckForm($data_from = "", $data_to = "")
    {
        $db = DbSingleton::getDbm();
        $exRate = new ExRateClass();
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
        $balanceAfter   = $balanceNetEnd = 0;
        $balanceNetEnd  = number_format((float)$balanceNetEnd, 2, '.', '');
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
                $summary        = round($db->result($r, $i - 1, "summ"), 2);
                $deb_kre        = (int)$db->result($r, $i - 1, "deb_kre");
                $balanceBefore  = $db->result($r, $i - 1, "balans_before");
                $balanceAfter   = $db->result($r, $i - 1, "balans_after");
                $doc_type_id    = (int)$db->result($r, $i - 1, "doc_type_id");
                $doc_id         = $db->result($r, $i - 1, "doc_id");
                $pay_cash_name  = $db->result($r, $i - 1, "cash_abr");
                $pay_summary    = $db->result($r, $i - 1, "pay_summ");
                $document_name  = "";

                if ($doc_type_id === 1) {
                    $document_name = $this->getSaleInvoiceName($doc_id);
                }

                if ($doc_type_id === 2) {
                    list($j_pay_doc_type_id, $document_name) = $this->getJPayName($db, $doc_id);

                    if ($j_pay_doc_type_id === 99) {
                        $summary = "";
                    }
                }

                if ($doc_type_id === 3) {
                    $document_name = $this->getJPayName($db, $doc_id)[1];
                }

                if ($doc_type_id === 5) {
                    $document_name = $this->getBackClientsName($doc_id);
                }

                $debit = $credit = "";

                if ($deb_kre === 1) {
                    $debit = $summary;
                }

                if ($deb_kre === 2) {
                    $credit = $summary;
                }

                $onclick = "showProfileDocs($i, $doc_id, $doc_type_id);";

                $list .= str_replace(
                    array("{num}", "{on_click}", "{date}", "{cash_name}", "{balance_before}", "{balance_after}", "{debit}", "{credit}", "{doc_name}", "{pay_sum}", "{pay_name}"),
                    array($i, $onclick, $data, $cash_name, $balanceBefore, $balanceAfter, $debit, $credit, $document_name, $pay_summary, $pay_cash_name),
                $this->getHtmlForm("profile/profile_table"));

                $list .= "";
            }

            $balanceNetEnd = round($balanceAfter, 2);

        } else {
            $list = str_replace("{message}", $this->err1, $this->getHtmlForm("profile/profile_table_error"));
        }

        if ($n === 0) {
            $r = $db->query("SELECT `balans_after` FROM `B_CLIENT_BALANS_JOURNAL` WHERE `client_id` = $client_id ORDER BY `data` DESC LIMIT 1;");
            $balanceAfter = $db->result($r, 0, "balans_after");
            $balanceNetEnd = round($balanceAfter, 2);
        }

        $form = $this->getHtmlForm("profile/profile_check");
        $balanceNetStart_cap = $balanceNetEnd_cap = "";

        $client_cash_id = $client->getClientCurrency($client_id);
        list($balanceNetStart, $balanceNetCash) = $this->getClientBalancePeriodStart($db, $client_id, $client_cash_id, $data_from, 0);

        $balanceNetDate_start = date("Y-m-01");

        if ($balanceNetStart < 0) {
            $balanceNetStart_cap = " (" . $this->getHtmlTag("span", "{debt_cap}", ['class' => 'span-red']) . ")";
        }

        elseif ($balanceNetStart > 0) {
            $balanceNetStart_cap = " (" . $this->getHtmlTag("span", "{prepayment}", ['class' => 'span-green']) . ")";
        }

        $form = str_replace(
            array("{saldo_start_data}", "{saldo_start_date}"),
            array($balanceNetStart . " " . $exRate->getExRateCaption($balanceNetCash) . $balanceNetStart_cap, $balanceNetDate_start),
        $form);

        $balanceNetDate_end = date("Y-m-d");

        if ($balanceNetEnd < 0) {
            $balanceNetEnd_cap = " (" . $this->getHtmlTag("span", "{debt_cap}", ['class' => 'span-red']) . ")";
        }

        elseif ($balanceNetEnd > 0) {
            $balanceNetEnd_cap = " (" . $this->getHtmlTag("span", "{prepayment}", ['class' => 'span-green']) . ")";
        }

        $form = str_replace(
            array("{saldo_end_data}", "{saldo_end_date}", "{profile_check_range}"),
            array($balanceNetEnd . " " . $exRate->getExRateCaption($balanceNetCash) . $balanceNetEnd_cap, $balanceNetDate_end, $list),
        $form);

        return $this->replaceLang($form);
    }

    public function getClientBalancePeriodStart($db, $client_id, $cash_id_sel, $data_from, $recursion): array
    {
        $cash_id = 1;
        $balanceNetStart = 0;
        $balanceNetDate_start = $data_from;

        $r = $db->query("SELECT `saldo_start`, `cash_id`, `data_start` FROM `B_CLIENT_BALANS_PERIOD` WHERE `client_id` = $client_id AND `data_start` = '" . date("Y-m-01", strtotime($data_from)) . "' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 1) {
            $balanceNetStart = $db->result($r, 0, "saldo_start");
            $cash_id = $db->result($r, 0, "cash_id");
            $balanceNetDate_start = $db->result($r, 0, "data_start");
        }

        if ($n === 0) {
            $recursion++;

            if ($recursion < 12) {
                $data_from = date("Y-m-01", strtotime("$data_from -1 month"));
            } else {
                $data_main_start = date("Y-m-01", strtotime($data_from));
                $db->query("INSERT INTO `B_CLIENT_BALANS_PERIOD` (`client_id`,`cash_id`,`saldo_start`,`data_start`,`active`) 
                VALUES ('$client_id','$cash_id_sel','0','$data_main_start','1');");
                $data_plus_month = date("Y-m-d", strtotime("$data_main_start +1 month"));
                $data_from = date("Y-m-01", strtotime($data_plus_month));
                $recursion -= 2;
            }

            list($balanceNetStart, , $balanceNetDate_start) = $this->getClientBalancePeriodStart($db, $client_id, $cash_id_sel, $data_from, $recursion);

            $cash_id = $cash_id_sel;
        }

        return array($balanceNetStart, $cash_id, $balanceNetDate_start);
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
        $file_dir   = $user_id . "/" . $user_id . "_price-list_" . $date . ".csv";
        $filename   = $user_id . "_price-list_" . $date . ".csv";
        $list       = $catalogue->getPriceList();

        foreach ($list as $record) {
            foreach ($record as $rec) {
                $csv .= $rec . ';';
            }
            $csv .= "\n";
        }

        $this->extractedFile($user_id, $file_dir, $csv);

        return array($filename, $file_dir);
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
            $list = str_replace(array("{file}", "{visible}", "{filename}"), array($file, $visible, $filename), $this->getHtmlForm("price/list"));
            $list_excel = str_replace(array("{user_id}", "{visible}"), array($user_id, $visible), $this->getHtmlForm("price/excel"));
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

                $table .= str_replace(array("{current}", "{filename}", "{date}", "{date_end}", "{status_nm}"), array($current, $filename, $date, $date_end, $status_nm), $this->getHtmlForm("price/table"));
            }

            $history_fr = str_replace("{price_range}", $table, $this->getHtmlForm("profile/profile_price_table"));
        }

        $form = str_replace(
            array("{price_download}", "{price_download_excel}", "{price_disabled}", "{price_history}"),
            array($list, $list_excel, $disable, $history_fr),
        $this->getHtmlForm("profile/profile_price_list"));

        return $this->replaceLang($form);
    }

    public function getStatusProfilePrice($status)
    {
        return $this->replaceLang(($status === 2) ? "{status_off}" : "{status_on}");
    }

    public function showRegistrationForm()
    {
        $menu = new MenuClass();
        $shop = new ShopClass();

        return str_replace(
            array("{type_form}", "{region_form}", "{category_options}", "{sale_point_options}", "{user_city_main_list}"),
            array($menu->showTypeForm(), $menu->getRegionForm(), $this->getManualOptions("customers_categories"), $this->getRegionSelectProfile(), $shop->getCitiesMainSelect()),
        $this->getHtmlForm("profile/registration"));
    }

    public function getRegionSelectProfile(): string
    {
        $db = DbSingleton::getDbm();
        $list = "";

        $r = $db->query("SELECT `id`, `full_name`, `address` FROM `T_POINT` WHERE `status` = 1 ORDER BY `full_name`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id     = $db->result($r, $i - 1, "id");
            $region = $db->result($r, $i - 1, "full_name");
            $address = $db->result($r, $i - 1, "address");

            $list .= str_replace(
                array("{value}", "{name}", "{checked}"),
                array($id, "$region ($address)", ""),
            $this->getHtmlForm("helper/select_option"));
        }

        return $list;
    }

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

                $this->extractedFile($user_id, $filename, $csv);
                $date_end = date("Y-m-d H:i:s");
                $dbm->query("UPDATE `cron_task_prices` SET `status` = 2, `date_end` = '$date_end' WHERE `user_id` = $user_id AND `status` = 1;");
            }
        }

        return true;
    }

    /**
     * @param $user_id
     * @param string $filename
     * @param string $csv
     * @return void
     */
    public function extractedFile($user_id, string $filename, string $csv)
    {
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
    }

}