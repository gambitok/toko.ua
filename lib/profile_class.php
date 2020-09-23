<?php

class ProfileClass extends ClientClass
{

    use Helper;
    use Variables;

    var $page_signin = "/signin";
    var $page_profile = "/profile/orders/";
    var $page_registration = "/registration";

    function getProfileClientInfo() {
        list($client_id, $user_id) = $this->getClient();
        $name = $this->getClientInfo($client_id, $user_id)["name"];
        $user_id==0
            ? $info = false
            : $info = "{hello_cap}, <a href=\"$this->page_profile\">".$name."</a>";
        return $info;
    }

    function getProfileInfo() {
        if ($this->getUser()==0) $info = "<li><a href=\"$this->page_registration\">
            <span class=\"fas fa-user-plus\"></span><span> {registration}</span>
        </a></li>
        <li><a class=\"pointer\" onClick=\"showLoginForm();\">
            <span class=\"fas fa-sign-in-alt\"></span><span> {login}</span>
        </a></li>";
        else $info = "<li><a href=\"#\" class=\"pointer\" onClick=\"logoutForm();\">
            <span class=\"fas fa-sign-out-alt\"></span><span> {logout}</span>
        </a></li>";
        return $info;
    }

    function getSpecialOffers() { $db = DbSingleton::getDbm();
        $prefix = $this->getLangPrefix();
        $user_id = $this->getUser(); $client_id = $this->getClient(); $categories = [];

        $r = $db->query("SELECT * FROM `A_CLIENTS` WHERE `id`='$client_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $category_id = $db->result($r, $i-1, "client_category");
            array_push($categories, $category_id);
        }
        $categories = implode(",", $categories);

        $r = $db->query("SELECT acl.* FROM `ACTION_CLIENTS_LIST` acl 
            LEFT OUTER JOIN `ACTION_CLIENTS` ac ON (ac.id=acl.action_id)
        WHERE acl.client_id='$client_id' AND ac.status=1;"); $n1 = $db->num_rows($r);

        $r = $db->query("SELECT acc.* FROM `ACTION_CLIENTS_CATEGORY` acc 
            LEFT OUTER JOIN `ACTION_CLIENTS` ac ON (ac.id=acc.action_id)
        WHERE acc.category_id IN ($categories) AND ac.status=1;"); $n2 = $db->num_rows($r);

        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' LIMIT 1;");
        $update_actions = $db->result($r, 0, "update_actions");

        $r = $db->query("SELECT * FROM `ACTION_CLIENTS` WHERE `timestamp`>'$update_actions 00:00:00' AND `status`=1;"); $n = $db->num_rows($r);
        if ($n>0) $counter = "<span class=\"span-red\">($n)</span>"; else $counter = "";

        if ($user_id>0 && ($n1>0 || $n2>0)) {
            $info = "<li>
                <a href=\"$prefix/special_offers/\">
                    <span class=\"fas fa-box-open\"></span> <span>{special_offers_cap} $counter</span>
                </a>
            </li>";
        } else $info = "";
        return $info;
    }

    function getNewsInfo() { $db = DbSingleton::getDbm(); $dbt = DbSingleton::getTokoDb();
        $prefix = $this->getLangPrefix();
        $user_id = $this->getUser(); $lang = $this->getLanguage(); if ($lang!=1) $lang = 5;
        $r = $db->query("SELECT * FROM `A_CLIENTS_USERS` WHERE `id`='$user_id' LIMIT 1;");
        $update_news = $db->result($r,0,"update_news");
        $r = $dbt->query("SELECT * FROM `news` WHERE `data`>'$update_news' AND `lang_id`='$lang' AND `status`=1;"); $n = $dbt->num_rows($r);
        if ($user_id>0 && $n>0) $counter = "<span class=\"span-red\">($n)</span>"; else $counter = "";
        $info = "
        <li>
            <a href=\"$prefix/news/\" class=\"pointer\">
                <span class=\"fas fa-newspaper\"></span><span> {news_cap} $counter</span>
            </a>
        </li>";
        return $info;
    }

    function getProfileInfoMobile() {
        $this->getUser()==0
            ? $info = "<a href=\"$this->page_signin\">{authorization}</a>"
            : $info = "<a href=\"$this->page_profile\">{profile}</a>";
        $info = $this->replaceLang($info);
        return $info;
    }

    function showProfileForm() {
        list($client_id, $user_id) = $this->getClient();
        $form = $this->getHtmlForm("profile/profile");
        $name = $this->getClientInfo($client_id, $user_id)["name"];
        $form = str_replace("{client_name}", $name, $form);
        $form = str_replace("{client_id}", $client_id, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function showProfileAccount() {
        $menu = new MenuClass;
        list($client_id, $user_id) = $this->getClient();
        $form = $this->getHtmlForm("profile/profile_account");
        $clientData = $this->getClientInfo($client_id, $user_id);
        $form = str_replace("{client_id}", $user_id, $form);
        $form = str_replace("{client_phone}", $clientData["phone"], $form);
        $form = str_replace("{client_password}", $clientData["password"], $form);
        $form = str_replace("{client_email}", $clientData["email"], $form);
        $form = str_replace("{client_name}", $clientData["name"], $form);
        $form = str_replace("{type_form}", $menu->showTypeForm($clientData["type"]), $form);
        $form = str_replace("{client_country}", $clientData["country"], $form);
        $form = str_replace("{region_form}", $menu->getRegionForm($clientData["region"]), $form);
        $form = str_replace("{client_city}", $clientData["city"], $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function checkDpStrExist($dp_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `J_DP_STR` WHERE `dp_id`='$dp_id';"); $n = $db->num_rows($r);
        if ($n>0) return true; else return false;
    }

    function checkDpBug($dp_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `J_DP_STR` WHERE `dp_id`='$dp_id';"); $n = $db->num_rows($r); $k = 0;
        for ($i=1; $i<=$n; $i++) {
            $amount_bug = $db->result($r, $i-1, "amount_bug");
            if ($amount_bug>0) $k++;
        }
        if ($k>0) return true; else return false;
    }

    function checkSelectDpBug($dp_id) { $db = DbSingleton::getDbm();
        $select_arr = []; $k = 0;
        $r = $db->query("SELECT * FROM `J_SELECT` WHERE `parrent_doc_id`='$dp_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $select_id = $db->result($r, $i-1, "id");
            array_push($select_arr, $select_id);
        }
        $select_str = implode(",", $select_arr);
        if (!empty($select_arr)) {
            $r = $db->query("SELECT * FROM `J_SELECT_STR` WHERE `select_id` IN ('$select_str') AND `status`=0;"); $n = $db->num_rows($r);
            for ($i=1; $i<=$n; $i++) {
                $amount_bug = $db->result($r, $i-1, "amount_bug");
                if ($amount_bug>0) $k++;
            }
        }
        if ($k>0) return true; else return false;
    }

    function checkSelectStrDpBug($dp_id, $art_id) { $db = DbSingleton::getDbm();
        $select_arr = []; $k = 0;
        $r = $db->query("SELECT * FROM `J_SELECT` WHERE `parrent_doc_id`='$dp_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $select_id = $db->result($r, $i-1, "id");
            array_push($select_arr, $select_id);
        }
        $select_str = implode(",", $select_arr);

        if (!empty($select_arr)) {
            $r = $db->query("SELECT * FROM `J_SELECT_STR` WHERE `select_id` IN ('$select_str') AND `art_id`='$art_id';");
            for ($i=1; $i<=$n; $i++) {
                $amount_bug = $db->result($r, $i-1, "amount_bug");
                if ($amount_bug>0) $k++;
            }
        }

        if ($k>0) return true; else return false;
    }

    function closeOrderArtUpdate($dp_id, $art_id, $order_id) { $db = DbSingleton::getDbm();
        $select_arr = []; $list = "";
        $r = $db->query("SELECT * FROM `J_SELECT` WHERE `parrent_doc_id`='$dp_id';"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $select_id = $db->result($r, $i-1, "id");
            array_push($select_arr, $select_id);
        }
        $select_str = implode(",", $select_arr);
        if (!empty($select_arr)) {
            $r = $db->query("SELECT * FROM `J_SELECT_STR` WHERE `select_id` IN ('$select_str') AND `art_id`='$art_id';");
            $amount = intval($db->result($r, 0, "amount"));
            $amount_bug = intval($db->result($r, 0, "amount_bug"));
            $amount_collect = intval($db->result($r, 0, "amount_collect"));
            $storage_select_string = "";
            if ($amount_bug>0) {
                $r = $db->query("SELECT * FROM `J_SELECT_STR_BUG` WHERE `select_id` IN ('$select_str') AND `art_id`='$art_id';"); $n = $db->num_rows($r);
                for ($i=1; $i<=$n; $i++) {
                    $storage_select_bug = $db->result($r, $i-1, "storage_select_bug");
                    $storage_select_cap = $this->getManualNameCaption("storage_select_bug", $storage_select_bug);
                    $amount_select_bug = $db->result($r, $i-1, "amount_bug");
                    $storage_select_string.="$storage_select_cap: $amount_select_bug {amount_abbr}. <br>";
                }
            }
            $list = "
            {your_order}: $amount {amount_abbr}. <br>
            {rejection_cap}: $amount_bug {amount_abbr}. <br>
            <span style=\"color: red;\">$storage_select_string</span>
            {shipped_cap}: $amount_collect {amount_abbr}. <br>
            <input id=\"order_id\" type=\"hidden\" value=\"$order_id\">";
        }
        $list = $this->replaceLang($list);
        return $list;
    }

    function updateOrderArt($order_str_id) { $db = DbSingleton::getDbm();
        $db->query("UPDATE `orders_str_new` SET `status_visible`=0 WHERE `id`='$order_str_id';");
        $r = $db->query("SELECT `order_id` FROM `orders_str_new` WHERE `id`='$order_str_id' LIMIT 1;");
        $order_id = $db->result($r, 0, "order_id");
        $r = $db->query("SELECT MAX(`status_visible`) as maxim FROM `orders_str_new` WHERE `order_id`='$order_id';");
        $status = $db->result($r, 0, "maxim");
        if ($status==0) $db->query("UPDATE `orders_new` SET `status_visible`=1 WHERE `id`='$order_id';");
        return $status;
    }

    function getDpByOrder($order_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `dp_id` FROM `orders_new` WHERE `id`='$order_id'");
        $dp_id = $db->result($r, 0, "dp_id");
        return $dp_id;
    }

    function getDpStatus($dp_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `status_visible` FROM `orders_new` WHERE `dp_id`='$dp_id';");
        $status=$db->result($r, 0, "status_visible");
        return $status;
    }

    function checkOrderUser($order_id, $user_id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT COUNT(`id`) as kilk FROM `orders_new` WHERE `id`='$order_id' AND `client_user_id`='$user_id';");
        $kilk = $db->result($r, 0, "kilk");
        return $kilk;
    }

    function getDpClient() { $db = DbSingleton::getDbm();
        $user_id = $this->getUser(); $dp_arr = [];
        $rr = $db->query("SELECT `dp_id` FROM `orders_new` WHERE `client_user_id`='$user_id' AND `dp_id`!=0;"); $nn = $db->num_rows($rr);
        for ($ii=1; $ii<=$nn; $ii++) {
            $dp_id = $db->result($rr, $ii-1, "dp_id");
            $dp_str = explode(",", $dp_id);
            for ($j=0; $j<count($dp_str); $j++) {
                $dp_value = $dp_str[$j];
                array_push($dp_arr, $dp_value);
            }
        }
        return $dp_arr;
    }

    function showProfileOrders() { $db = DbSingleton::getDbm();
        $kours = new ExRateClass;
        $form = $this->getHtmlForm("profile/profile_orders");
        $user_id = $this->getUser();
        $where = "`client_user_id`='$user_id'";
        $k = $summ = 0; $list = "";

        $rr = $db->query("SELECT `dp_id` FROM `orders_new` WHERE $where AND `dp_id`!=0;"); $nn = $db->num_rows($rr);
        for ($ii=1; $ii<=$nn; $ii++) {
            $dp_id = $db->result($rr, $ii-1, "dp_id");
            $dp_arr = explode(",", $dp_id);
            $prefix=$id=$name=$date=$city_name=$delivery_type=$payment_type=$price_summ=$cash_name=$status_type=$bg_bug="";

            for ($j=0; $j<count($dp_arr); $j++) {
                $dp_value = $dp_arr[$j];
                $r = $db->query("SELECT dp.*, si.summ as summ_sale FROM `J_DP` dp 
                    LEFT OUTER JOIN `J_SALE_INVOICE` si on si.dp_id=dp.id
                WHERE dp.id='$dp_value';");

                if ($this->checkDpStrExist($dp_value)) {
                    $id.=$db->result($r, 0, "id").",";
                    $prefix=$db->result($r, 0, "prefix");
                    $dp_user_id=$db->result($r, 0, "user_id");
                    $client_id=$db->result($r, 0, "client_id");
                    $date.=$db->result($r, 0, "time_stamp")."\n";
                    $name.=$this->getClientName($dp_user_id, $client_id)."\n";
                    $city = $this->getClientInfo($client_id, $dp_user_id)["city"];
                    $delivery = $db->result($r, 0, "delivery_type_id");
                    $payment=0;
                    $status = $db->result($r, 0, "status_dp");
                    $cash_id = $db->result($r, 0, "cash_id");
                    $summ_sale = $db->result($r, 0, "summ_sale");
                    $status_visible = $db->result($r, 0, "status_visible");
                    $price_summ+=floatval($summ_sale);
                    if ($price_summ==0) $price_summ+=floatval($db->result($r, 0, "summ"));
                    $summ = $db->result($r, 0, "summ");
                    $city_name.=$city."\n";
                    $delivery_type.=$this->getManualName($delivery)."\n";
                    $payment_type.=$this->getManualName($payment)."\n";
                    $status_type.=$this->getManualName($status)."\n";
                    $cash_name.=$kours->getKoursCaption($cash_id)."\n";
                    if ($this->checkSelectDpBug($dp_value) && $status_visible==1) $k++;
                }
            }
            $id = rtrim($id, ",");

            if ($k>0) { $bg_bug = "bg-warning"; } else { $bg_bug = ""; } $k = 0;

            $price_summ = number_format($price_summ, 2, '.', '');

            if ($summ>0 && $id!="") {
                $list.="<tr class=\"$bg_bug pointer\" onclick='showProfileOrdersArts(\"$id\",\"\")'>
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

        $r2 = $db->query("SELECT * FROM `orders_new` WHERE $where AND `dp_id`=0 AND `status`=1;"); $n2 = $db->num_rows($r2);
        for ($i=1; $i<=$n2; $i++) {
            $id = $db->result($r2, $i-1, "id");
            $name = $db->result($r2, $i-1, "name");
            $date = $db->result($r2, $i-1, "data");
            $city = $db->result($r2, $i-1, "region");
            $delivery = $db->result($r2, $i-1, "delivery");
            $payment = $db->result($r2, $i-1, "payment");
            $price_summ = $db->result($r2, $i-1, "price_summ");
            $cash_id = $db->result($r2, $i-1, "cash_id");
            $price_summ = $kours->getKoursFromUAH($price_summ, $cash_id);
            $city_name = $this->getCityName($city);
            $delivery_type = $this->getManualName($delivery);
            $payment_type = $this->getManualName($payment);
            $status_type = '{order_in_queue}';
            $cash_name = $kours->getKoursCaption($cash_id);
            $list.="<tr class='pointer' onclick='showProfileOrdersArts(\"\",\"$id\")'>
                <td>{order_cap} #$id</td>
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

        $form = str_replace("{orders_range}", $list, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function showProfileOrdersArts($dp_check, $order_check) { $db = DbSingleton::getDbm();
        $kours = new ExRateClass;
        $list = "";
        $form = $this->getHtmlForm("profile/profile_orders_arts");
        $user_id = $this->getUser(); $client_id = $this->getClient();

        if ($dp_check != "") $dp_arr = explode(",", $dp_check); else $dp_arr = $this->getDpClient();

        //Dp orders arts
        if ($order_check=="") {
            for ($jj=0; $jj<count($dp_arr); $jj++) { $nedp = false;
                $dp_value = $dp_arr[$jj];
                if ($dp_check != "") $where_dp_client = "WHERE `id`='$dp_value' AND `client_id`='$client_id'"; else $where_dp_client = "WHERE `client_id`='$client_id'";
                $r = $db->query("SELECT * FROM `J_DP` $where_dp_client;"); $ndp = $db->num_rows($r);

                if ($ndp>0) {
                    $dp_id = $db->result($r, 0, "id");
                    if ($dp_check!="") $where_dp = "WHERE dp.dp_id='$dp_id' AND ord.dp_str_id!=0"; else {
                        $dp_id = $dp_arr[$jj];
                        $where_dp = "WHERE dp.dp_id='$dp_id' AND ord.dp_str_id!=0";
                    }
                    $prefix = $db->result($r, 0, "prefix");

                    $rstr = $db->query("SELECT dp.*, ord.order_id, ord.id as order_str_id 
                    FROM `orders_str_new` ord
                        LEFT OUTER JOIN `J_DP_STR` dp on dp.id=ord.dp_str_id
                    $where_dp GROUP BY dp.art_id;"); $nstr = $db->num_rows($rstr);

                    if ($nstr==0) {
                        $rstr = $db->query("SELECT * FROM `J_DP_STR` WHERE `dp_id`='$dp_id' GROUP BY `art_id`;");
                        $nstr = $db->num_rows($rstr);
                        $nedp = true;
                    }

                    for ($j=1; $j<=$nstr; $j++) {
                        $order_id = $db->result($rstr, $j - 1, "order_id");
                        $order_str_id = $db->result($rstr, $j - 1, "order_str_id");
                        $article_nr_displ = $db->result($rstr, $j - 1, "article_nr_displ");
                        $art_id = $db->result($rstr, $j - 1, "art_id");
                        $brand_id = $db->result($rstr, $j - 1, "brand_id");
                        $brand_name = $this->getBrandName($brand_id);
                        $amount = intval($db->result($rstr, $j - 1, "amount"));
                        $amount_collect = intval($db->result($rstr, $j - 1, "amount_collect"));
                        $summ = $db->result($rstr, $j - 1, "summ"); $price = round($summ / $amount,2);
                        $status_dps = $db->result($rstr, $j - 1, "status_dps");
                        $status_dps = $this->getManualName($status_dps);
                        $status_visible = $db->result($rstr, $j - 1, "status_visible");

                        if ($this->checkSelectStrDpBug($dp_id, $art_id) > 0 && $status_visible == 1) {
                            $db->query("UPDATE `orders_str_new` SET `status_visible`=1 WHERE `id`='$order_str_id';");
                            $btn_bug = "<button class=\"btn-basket pointer\" onclick=\"closeOrderArtUpdate($dp_id, $art_id, $order_str_id);\"><span class=\"fas fa-eye\"></span></button>";
                            $bg_bug = "bg-warning";
                        } else {
                            $btn_bug = "";
                            $bg_bug = "";
                        }

                        $price = number_format($price, 2, '.', '');
                        $summ = number_format($summ, 2, '.', '');

                        if ($this->checkSelectStrDpBug($dp_id, $art_id) > 0) $amount_text = "$amount_collect ($amount)"; else $amount_text = $amount;

                        if ($nedp) {
                            $list.="<tr class=\"$bg_bug\">
                                <td>$prefix-$dp_id</td>
                                <td>$article_nr_displ</td>
                                <td>$brand_name</td>
                                <td><p class=\"text-center\">$amount_text</p> $btn_bug</td>
                                <td>$price</td>
                                <td>$summ</td>
                                <td>$status_dps</td>
                            </tr>";
                        }

                        if ($this->checkOrderUser($order_id, $user_id)>0) {
                            $list.="<tr class=\"$bg_bug\">
                                <td>$prefix-$dp_id</td>
                                <td>$article_nr_displ</td>
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
        if ($dp_check=="") {

            if ($order_check!="") $where_order = "AND `id`='$order_check'"; else $where_order = "";
            $r = $db->query("SELECT * FROM `orders_new` WHERE `client_user_id`='$user_id' AND `dp_id`=0 AND `status`=1 $where_order;"); $n = $db->num_rows($r);
            for ($i=1; $i<=$n; $i++) {
                $order_id = $db->result($r, $i-1, "id");
                $cash_id = $db->result($r, $i-1, "cash_id");
                $rstr = $db->query("SELECT * FROM `orders_str_new` WHERE `order_id`='$order_id';"); $nstr = $db->num_rows($rstr);
                for ($j=1; $j<=$nstr; $j++) {
                    $art_id = $db->result($rstr, $j-1, "art_id");
                    $brand_id = $db->result($rstr, $j-1, "brand_id");
                    $article_nr_displ = $this->getArticleDispl($art_id);
                    $brand_name = $this->getBrandName($brand_id);
                    $amount = intval($db->result($rstr, $j-1, "amount"));
                    $price = $db->result($rstr, $j-1, "price");
                    $price = $kours->getKoursFromUAH($price, $cash_id);
                    $summ = $db->result($rstr, $j-1, "summ");
                    $summ = $kours->getKoursFromUAH($summ, $cash_id);
                    $status_dps = "{making_order_cap}";
                    $list.="<tr>
                        <td>Order-$order_id</td>
                        <td>$article_nr_displ</td>
                        <td>$brand_name</td>
                        <td><p class=\"text-center\">$amount</p></td>
                        <td>$price</td>
                        <td>$summ</td>
                        <td>$status_dps</td>
                    </tr>";
                }
            }
        }

        $form = str_replace("{orders_range}", $list, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function showProfileCheck($data_from="", $data_to="") { $db = DbSingleton::getDbm();
        $kours = new ExRateClass;
        if ($data_from==0 || $data_from=="") $data_from=date("Y-m-01");
        if ($data_to==0 || $data_to=="") $data_to=date("Y-m-d");

        $balans_after=0; $saldo_end=0; $saldo_end=number_format((float)$saldo_end, 2, '.', '');
        $list=""; $client_id=$this->getClient();
        $r = $db->query("SELECT b.*, mc.abr as cash_name, pmc.abr 
        FROM `B_CLIENT_BALANS_JOURNAL` b 
			LEFT OUTER JOIN `CASH` mc on mc.id=b.cash_id 
			LEFT OUTER JOIN `CASH` pmc on pmc.id=b.pay_cash_id 
        WHERE b.client_id='$client_id' AND b.data>='$data_from 00:00:00' AND b.data<='$data_to 23:59:59' ORDER BY b.id ASC;"); $n = $db->num_rows($r);

        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $data=$db->result($r,$i-1,"data");
                $cash_name=$db->result($r,$i-1,"cash_name");
                $summ=round($db->result($r,$i-1,"summ"),2);
                $deb_kre=$db->result($r,$i-1,"deb_kre");
                $balans_before=$db->result($r,$i-1,"balans_before");
                $balans_after=$db->result($r,$i-1,"balans_after");
                $doc_type_id=$db->result($r,$i-1,"doc_type_id");
                $doc_id=$db->result($r,$i-1,"doc_id");
                $pay_cash_name=$db->result($r,$i-1,"pmc.abr");
                $pay_summ=$db->result($r,$i-1,"pay_summ");

                $document_name="";
                if ($doc_type_id==1){ $document_name=$this->getSaleInvoiceName($doc_id); }
                if ($doc_type_id==2){
                    list($jpay_doc_type_id,$document_name)=$this->getJPayName($doc_id);
                    if ($jpay_doc_type_id==99) {$summ="";}
                }
                if ($doc_type_id==3){ $document_name=$this->getJPayName($doc_id)[1]; }
                if ($doc_type_id==5){ $document_name=$this->getBackClientsName($doc_id); }

                $debit=""; $kredit="";
                if ($deb_kre==1){
                    $debit=$summ;
                    $saldo_end-=$debit;
                }
                if ($deb_kre==2){
                    $kredit=$summ;
                    $saldo_end+=$kredit;
                }
                $list.="<tr align=\"center\">
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
            $saldo_end=round($balans_after,2);

        } else $list="<tr><td class=\"text-center\" colspan=\"9\">".$this->err1."</td></tr></table>";

        if ($n==0) {
            $r=$db->query("SELECT * FROM `B_CLIENT_BALANS_JOURNAL` WHERE `client_id`='$client_id' ORDER BY `data` DESC LIMIT 1;");
            $balans_after=$db->result($r,0,"balans_after");
            $saldo_end=round($balans_after,2);
        }

        $form=$this->getHtmlForm("profile/profile_check");
        $saldo_start_cap = $saldo_end_cap = "";

        $client_cash_id = $this->getClientCurrency($client_id);
        list($saldo_start, $saldo_cash_id,) = $this->getClientBalansPeriodStart($client_id,$client_cash_id,$data_from,0);

        $saldo_data_start=date("Y-m-01");
        if ($saldo_start<0) $saldo_start_cap=" (<span class=\"span-red\">{debt_cap}</span>)";
        else if ($saldo_start>0) $saldo_start_cap=" (<span class=\"span-green\">{prepayment}</span>)";
        $form=str_replace("{saldo_start_data}",$saldo_start." ".$kours->getKoursCaption($saldo_cash_id).$saldo_start_cap,$form);
        $form=str_replace("{saldo_start_date}",$saldo_data_start,$form);

        $saldo_data_end=date("Y-m-d");
        if ($saldo_end<0) $saldo_end_cap=" (<span class=\"span-red\">{debt_cap}</span>)";
        else if ($saldo_end>0) $saldo_end_cap=" (<span class=\"span-green\">{prepayment}</span>)";
        $form=str_replace("{saldo_end_data}", $saldo_end." ".$kours->getKoursCaption($saldo_cash_id).$saldo_end_cap, $form);
        $form=str_replace("{saldo_end_date}", $saldo_data_end, $form);
        $form=str_replace("{profile_check_range}", $list, $form);
        $form=$this->replaceLang($form);
        return $form;
    }

    function getClientBalansPeriodStart($client_id, $cash_id, $data_from, $recursion) { $db=DbSingleton::getDbm();
        $saldo_start=0; $saldo_data_start=$data_from;
        $r = $db->query("SELECT * FROM `B_CLIENT_BALANS_PERIOD` WHERE `client_id`='$client_id' AND `data_start`='".date("Y-m-01",strtotime($data_from))."' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==1) {
            $saldo_start=$db->result($r,0,"saldo_start");
            $cash_id=$db->result($r,0,"cash_id");
            $saldo_data_start=$db->result($r,0,"data_start");
        }
        if ($n==0) {
            $recursion+=1;
            if ($recursion<12) {
                $data_from=date("Y-m-01",strtotime("$data_from -1 month"));
                list($saldo_start,, $saldo_data_start) = $this->getClientBalansPeriodStart($client_id, $cash_id, $data_from, $recursion);
            } else {
                $data_main_start=date("Y-m-01",strtotime("$data_from"));
                $db->query("INSERT INTO `B_CLIENT_BALANS_PERIOD` (`client_id`,`cash_id`,`saldo_start`,`data_start`,`active`) 
                VALUES ('$client_id','$cash_id','0','$data_main_start','1');");
                $data_plus_month=date("Y-m-d", strtotime("$data_main_start +1 month"));
                $data_from=date("Y-m-01",strtotime("$data_plus_month"));
                $recursion-=2;
                list($saldo_start,, $saldo_data_start) = $this->getClientBalansPeriodStart($client_id, $cash_id, $data_from, $recursion);
            }
        }
        return array($saldo_start, $cash_id, $saldo_data_start);
    }

    // Cron
    function setPriceList() { $db=DbSingleton::getDbm();
        $user_id = $this->getUser(); $date = date("Y-m-d H:i:s"); $date_format = date("Y-m-d_H-i-s");
        $filename = "TOKO_GROUP_price-list_".$user_id."_".$date_format.".csv";
        $r = $db->query("SELECT `status` FROM `cron_task_prices` WHERE `user_id`='$user_id' AND `status`=1;"); $n = $db->num_rows($r);
        if ($n>0) return "forming..."; else {
            $db->query("INSERT INTO `cron_task_prices` (`user_id`,`filename`,`date`,`status`) VALUES ('$user_id','$filename','$date',1);");
            $text = "date-start: ".$date;
            return $text;
        }
    }

    function getPriceList() {
        $catalogue = new CatalogueClass;
        $user_id = $this->getUser();
        $date = date("Y-m-d_H-i-s"); $csv = "";
        $filedir = $user_id."/".$user_id."_price-list_".$date.".csv";
        $filename = $user_id."_price-list_".$date.".csv";
        $list = $catalogue->getPriceList();

        foreach ($list as $record) {
            foreach ($record as $rec) {
                $csv.= $rec.';';
            }
            $csv.="\n";
        }

        if (!file_exists(RDD."/uploads/$user_id")) {
            mkdir(RDD."/uploads/$user_id", 0777, true);
        } else {
            if (file_exists(RDD."/uploads/$user_id/")) {
                foreach (glob(RDD."/uploads/$user_id/*") as $file) {
                    unlink($file);
                }
            }
        }
        $csv_handler = fopen (RDD."/uploads/$filedir",'w') or die("Can't create file");
        fwrite($csv_handler, $csv);
        fclose($csv_handler);
        return array($filename, $filedir);
    }

	function showPriceList() { $db = DbSingleton::getDbm();
        $user_id = $this->getUser();
		$form = $this->getHtmlForm("profile/profile_price_list");
		$disable = "disabled"; $visible = "style=\"display:none;\""; $history_form = "";
		$r = $db->query("SELECT * FROM `cron_task_prices` WHERE `user_id`='$user_id' and `status`=1;"); $n = $db->num_rows($r);
		if ($n==0) { $disable = ""; $visible = ""; }
		
        $filename = scandir(RDD."/uploads/$user_id")[2];
        if ($filename!="") {
            $file = "$this->uploads/$user_id/".$filename;
            $list = "<a class=\"btn btn-primary\" href=\"$file\" download $visible><span class='fa fa-download'></span> Download $filename</a><br>";
        }
        else $list = "";
	    $r = $db->query("SELECT * FROM `cron_task_prices` WHERE `user_id`='$user_id' ORDER BY `date` DESC;"); $n = $db->num_rows($r);
	    if ($n>0) {
            $table = "";
			for ($i=1; $i<=$n; $i++) {
				$filename = $db->result($r, $i-1, "filename");
				$date = $db->result($r, $i-1, "date");
				$date_end = $db->result($r, $i-1, "date_end");
				$status = $db->result($r, $i-1, "status"); $status_name = $this->getStatusProfilePrice($status);
				if ($status==2 && $i==1) $current = "style=\"background:#f1f1f1;\""; else $current = "";
                $table.="<tr $current>
					<td>$filename</td>
					<td>$date</td>
					<td>$date_end</td>
					<td>$status_name</td>
				</tr>";
			}
            $history_form = $this->getHtmlForm("profile/profile_price_table");
            $history_form = str_replace("{price_range}", $table, $history_form);
		}
		$form = str_replace("{price_download}", $list, $form);
		$form = str_replace("{price_disabled}", $disable, $form);
		$form = str_replace("{price_history}", $history_form, $form);
		$form = $this->replaceLang($form);
        return $form;
	}
	
	function getStatusProfilePrice($status) {
		switch($status) {
			case 1: $text = "{status_on}"; break;
			case 2: $text = "{status_off}"; break;
			default: $text = ""; break;
		}
        $text = $this->replaceLang($text);
		return $text;
	}

    function showRegistrationForm() {
        $menu = new MenuClass; $shop = new ShopClass;
        $form = $this->getHtmlForm("profile/registration");
        $form = str_replace("{type_form}", $menu->showTypeForm(), $form);
        $form = str_replace("{region_form}", $menu->getRegionForm(), $form);
        $form = str_replace("{category_options}", $this->getManualOptions("customers_categories"), $form);
        $form = str_replace("{tpoint_options}", $this->getRegionSelect(), $form);
        $form = str_replace("{user_city_main_list}", $shop->getCitiesMainSelect(), $form);
        return $form;
    }

    function getRegionSelect() { $db = DbSingleton::getDbm();
        $options = "";
        $r = $db->query("SELECT * FROM `T_POINT` WHERE `status`=1 ORDER BY `full_name` ASC;"); $n = $db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "id");
            $region = $db->result($r, $i-1, "full_name");
            $address = $db->result($r, $i-1, "address");
            $options.="<option value=\"$id\">$region ($address)</option>";
        }
        return $options;
    }

}