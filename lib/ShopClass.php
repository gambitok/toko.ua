<?php

use LisDev\Delivery\NovaPoshtaApi2;

class ShopClass extends CatalogueClass
{

    /*
     * show basket form
     * */
    public function showBasketForm($cur = null)
    {
        $db = DbSingleton::getTokoDb();
        $exrate = new ExRateClass();
        $client = new ClientClass();
        $showform = new FormClass();

        $disabled       = "";
        $location       = "stayInOrder();";
        $location_fast  = "stayInOrder();";
        $sum_checked    = $sum_total = $count_checked = 0;
        $client_id      = $this->getClient();
        $where          = $client->getClientWhere();

        $cur = $this->getUrlNumber($cur);
        if (empty($cur) || $cur === "NaN") {
            $cur = 1;
        }
        setcookie("currency", $cur);
        $_SESSION["currency"] = $cur;

        $r = $db->query("SELECT * FROM `basket` WHERE $where ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $brow           = "";
            $bprow          = "";
            $location       = "location.href='" . $this->getSiteLink() . "$this->order_link/';";
            $location_fast  = "letsFinishOrder('input_phone2', 0);";

            for ($i = 1; $i <= $n; $i++) {
                $art_id         = (int)$db->result($r, $i - 1, "art_id");
                $brand_id       = (int)$db->result($r, $i - 1, "brand_id");
                $suppl_id       = (int)$db->result($r, $i - 1, "suppl_id");
                $amount         = (int)$db->result($r, $i - 1, "amount");
                $stock          = (int)$db->result($r, $i - 1, "stock");
                $storage_id     = (int)$db->result($r, $i - 1, "storage_id");
                $date_create    = $db->result($r, $i - 1, "date_create");
                $status         = (int)$db->result($r, $i - 1, "status");
                $status_checked = (int)$db->result($r, $i - 1, "status_checked");
                $price          = (float)$db->result($r, $i - 1, "price");

                // PRICE
                $price = $exrate->getKoursPrice($price, $cur);
                if ($cur === 1) {
                    $price = $client->getClientPriceRounding($client_id, $price);
                }
                $full_price = $price * $amount;
                if ($cur === 1) {
                    $full_price = $client->getClientPriceRounding($client_id, $full_price);
                }

                $data       = compact("art_id", "brand_id", "suppl_id", "amount", "price", "full_price", "stock", "storage_id", "date_create", "status", "status_checked", "cur");
                $brow       .= $this->showBasketRows($data);
                $bprow      .= $this->showBasketRows($data, 1);
                $sum_total  += $full_price;

                if ($status_checked) {
                    $sum_checked += $full_price;
                    ++$count_checked;
                }
            }
        } else {
            $brow = "
            <div class=\"row align-items-center\">
                <div class=\"col-12\">
                    <p class=\"text-center mar0\"><br>
                    {basket_empty}</p><br>
                </div>
            </div>";
            $bprow = "";
        }

        $checked_status = "";
        $total_style = "";
        if ($sum_checked === $sum_total) {
            $checked_status = "checked=\"checked\"";
            $total_style = "d-none";
        }

        $validate_class = ($client->getClientPhone() === "") ? "non_accept fa-times-circle" : "accept fa-check-circle";
        $location_ff    = ($count_checked > 0) ? $location_fast : "alert('" . $this->replaceLang("{chose_all_in_basket}") . "');";

        $table_basket = $this->getHtmlForm("basket/basket_form");
        $table_basket = str_replace(array("{basket_rows}", "{checked_status}", "{basket_phone_rows}", "{sum}", "{sum_total}", "{count}", "{total_style}", "{location}", "{location_fast}", "{currency}", "{cur_cap}", "{disabled}", "{basket_proposed}", "{user_phone}", "{validate_class}"), array($brow, $checked_status, $bprow, $sum_checked, $sum_total, $count_checked, $total_style, $location, $location_ff, $showform->getCurrencyForm($cur, 1), $this->getSymbolExrate($cur), $disabled, $this->getProposedArts(), $client->getClientPhone(), $validate_class), $table_basket);

        $table_basket = $this->replaceLang($table_basket);

        if ($n === 0) {
            $table_basket = $this->replaceLang($this->getHtmlForm("basket/basket_error"));
        }

        return $table_basket;
    }

    public function showBasketRows($data, $visible = 0)
    {
        $showform = new FormClass();

        $art_id         = $data["art_id"];
        $brand_id       = $data["brand_id"];
        $suppl_id       = $data["suppl_id"];
        $amount         = $data["amount"];
        $stock          = $data["stock"];
        $storage_id     = $data["storage_id"];
        $price          = $data["price"];
        $full_price     = $data["full_price"];
        $date_create    = $data["date_create"];
        $status         = $data["status"];
        $status_checked = $data["status_checked"];
        $cur            = $data["cur"];
        $art_nr_ds      = $this->getArticleDispl($art_id);
        $brand_name     = $this->getBrandName($brand_id);

        // DELIVERY
        $tpoint_id = $this->getTpointID();
        if ($suppl_id === 0) {
            $deliveryData = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
        } else {
            $deliveryData = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
        }

        // FLAGS
        $flagData   = $showform->getCountryFlag($brand_id);
        $country_nm = $flag =  "";
        if ($flagData !== false) {
            $flag       = "<img class=\"flag flag-" . $flagData["flag"] . " flag-search\">";
            $country_nm = "{brand_manuf}: " . $flagData["country"];
        }

        if (!$visible) {
            $form = $this->getHtmlForm("basket/basket_card");
        } else {
            $form = $this->getHtmlForm("basket/basket_phone_card");
        }

        $delivery_info  = str_replace('"', "", $deliveryData["info"]);
        $date_start     = date("d.m.y H:i", strtotime($date_create));
        $date_end       = date("d.m.y H:i", strtotime(date("Y-m-d H:i:s")));
        $disabled       = ($this->checkStatusBasket()) ? "" : "disabled";
        $checked        = ($status_checked) ? "checked=\"checked\"" : "";
        $site_link      = $this->getSiteLink() . "$this->products_link/" . $this->getFormatAticle($art_nr_ds) . "-" . $this->getBrandLink($brand_id) . "-" . "$art_id/";
        $amount_field   = "count_" . $art_id . "_" . $storage_id;

        $form = str_replace(array("{art_id}", "{art_name}", "{brand_id}", "{brand_name}", "{suppl_id}", "{text}", "{amount}", "{price}", "{date1}", "{date2}", "{delivery_info}", "{delivery_short_info}", "{storage_id}", "{stock}", "{status}", "{status_checked}", "{full_price}", "{disabled}", "{checked}", "{link}", "{flag}", "{country_name}", "{amount_field}", "{action}", "{cash_abr}", "{product_image}"), array($art_id, $art_nr_ds, $brand_id, $brand_name, $suppl_id, $this->getArticleName($art_id), $amount, $price, $date_start, $date_end, $delivery_info, $deliveryData["short"], $storage_id, $stock, $status, $status_checked, $full_price, $disabled, $checked, $site_link, $flag, $country_nm, $amount_field, $this->getClientAction($art_id, $suppl_id, $storage_id, $amount, $cur), $this->getSymbolExrate($cur), $this->getBasketArticlePhoto($art_id)), $form);

        return $form;
    }

    public function getBasketArticlePhoto($art_id): string
    {
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID` = $art_id AND `ACTIVE` = 1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;");
        $n = $db->num_rows($r);
        $photo_name = $db->result($r, 0, "PHOTO_NAME");
        $photo_src  = "https://toko.ua/uploads/images/catalogue/$photo_name";

        if ($n === 0) {
            $photo_src = "https://toko.ua/$this->noPhoto";
        }

        return $photo_src;
    }

    /*
     * get client action information
     * */
    public function getClientAction($art_id, $suppl_id, $storage_id, $amount, $cur): string
    {
        $exrate = new ExRateClass();
        $cur_cap = $this->getSymbolExrate($cur);
        $action = "";

        if ($this->checkActionPrice($art_id)) {
            list(, $action_amount, $action_price) = $this->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, $cur);
            $true_price = ($suppl_id === 0)
                ? $this->getArticlePrice($art_id)
                : $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
            $true_price = round($exrate->getKoursPrice($true_price, $cur), 2);

            if ($amount >= $action_amount) {
                $true_cap = "<br><span class=\"span-outline\">$true_price $cur_cap</span>";
                $true_clr = "";
            } else {
                $true_cap = "";
                $true_clr = "color:lightcoral!important;";
            }

            $action = "
            $true_cap<br>
            <span style=\"font-size: 1.5em; $true_clr\" class=\"span-green tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" 
                title=\"{price_cap} $action_price $cur_cap, {from_cap} $action_amount {amount_abbr}.\">
                <i class=\"fa fa-box-open\"></i>
            </span>";
        }

        return $action;
    }

    /*
     * get basket items list
     * */
    public function getBasketArts(): string
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $where = $client->getClientWhere();
        $r = $db->query("SELECT `art_id` FROM `basket` WHERE $where ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $arts = [];
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $arts[] = $art_id;
            }
            $arts = implode(",", $arts);
        } else {
            $arts = "";
        }

        return $arts;
    }

    /*
     * show Proposed Arts
     * */
    public function getProposedArts()
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $arts = $this->getBasketArts();
        $where_arts = ($arts !== "") ? " AND `ART_ID` NOT IN ($arts)" : "";
        $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES_PROPOSED` WHERE `STATUS` = 1 $where_arts;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $list .= $this->getProposedArtsCard($art_id);
        }

        $form = $this->getHtmlForm("orders/proposed");
        $form = str_replace("{proposed_range}", $list, $form);
        $form = $this->replaceLang($form);
        if ($n === 0) {
            $form = "";
        }

        return $form;
    }

    /*
     * show Proposed Arts Line
     * */
    public function getProposedArtsCard($art_id)
    {
        $showform = new FormClass();

        $articleData    = $showform->getArticleInfo($art_id);
        $art_nr_ds      = $articleData["article_nr_displ"];
        $format_name    = $this->getFormatAticle($art_nr_ds);
        $brand_link     = $this->getBrandLink($articleData["brand_id"]);
        $proposed_link  = $this->getSiteLink() . "$this->products_link/$format_name-$brand_link-$art_id/";

        $form = $this->getHtmlForm("orders/proposed_card");
        $form = str_replace(array("{basket}", "{article_nr_displ}", "{name}", "{brand_name}", "{price}", "{image}", "{currency}", "{page_proposed_link}"), array($articleData["basket"], $art_nr_ds, $articleData["article_name"], $articleData["brand_name"], $articleData["price"], $showform->getArticleActivePhoto($art_id), $articleData["currency"], $proposed_link), $form);

        return $form;
    }

    /*
     * set article to basket
     * */
    public function moveToBasket($art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id): array
    {
        $art_id     = $this->getUrlNumber($art_id);
        $brand_id   = $this->getUrlNumber($brand_id);
        $amount     = $this->getUrlNumber($amount);
        $stock      = $this->getUrlNumber($stock);
        $storage_id = $this->getUrlNumber($storage_id);
        $suppl_id   = $this->getUrlNumber($suppl_id);

        $db         = DbSingleton::getTokoDb();
        $client     = new ClientClass();
        $exrate     = new ExRateClass();
        $showform   = new FormClass();

        $user_id    = $this->getUser();
        $where      = $client->getClientWhere();
        $cookie     = $this->getSessionID();
        $date_time  = date("Y-m-d H:i:s");
        $old_amount = $status_action = 0;
        $art_name   = $this->getArticleDispl($art_id);

        $r = $db->query("SELECT `amount` FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
        $n = $db->num_rows($r);
        $price = $this->getArticlePrice($art_id);

        if ($suppl_id !== 0) {
            $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
        }

        if ($this->checkActionPrice($art_id)) {
            list($action_id, $action_amount, $action_price) = $this->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, 1); // to UAH
            if ($amount >= $action_amount) {
                $status_action = $action_id;
                $price = $action_price;
            }
        }

        list($delivery_days, $delivery_short_info) = $showform->getDeliveryData($this->getTpointID(), $storage_id, $suppl_id);
        $delivery_short_info = $this->replaceLang($delivery_short_info);

        if ($n > 0) {
            $r2 = $db->query("SELECT `amount` FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
            $cur_stock = $db->result($r2, 0, "amount");

            if ($stock < ($cur_stock + $amount)) {
                $amount = $stock;
            } else {
                $old_amount = (int)$db->result($r, 0, "amount");
                $amount += $old_amount;
            }
            $db->query("UPDATE `basket` SET `amount` = '$amount', `status_action` = '$status_action' WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
        } else {
            $db->query("INSERT INTO `basket` (`art_id`, `brand_id`, `amount`, `price`, `stock`, `delivery`, `client_id`, `cookie_id`, `date_create`, `storage_id`, `delivery_info`, `suppl_id`,`status_action`,`status`) 
            VALUES ($art_id, $brand_id, '$amount', $price, '$stock', '$delivery_days', '$user_id', '$cookie', '$date_time', '$storage_id', '$delivery_short_info', '$suppl_id', '$status_action', '0');");
        }
        $amount_cap = ($amount > 0) ? $this->replaceLang("{site_basket}: $amount {amount_abbr}.") : "";

        return array($old_amount, $art_name, $amount_cap);
    }

    /*
     * basket selected item amount
     * */
    public function getBasketArticleAmount($art_id, $storage_id): int
    {
        $art_id     = $this->getUrlNumber($art_id);
        $storage_id = $this->getUrlNumber($storage_id);

        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $where = $client->getClientWhere();
        $r = $db->query("SELECT `amount` FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0) ? (int)$db->result($r, 0, "amount") : 0;
    }

    /*
     * remove item from basket
     * */
    public function deleteFromBasket($art_id, $storage_id): bool
    {
        $art_id     = $this->getUrlNumber($art_id);
        $storage_id = $this->getUrlNumber($storage_id);

        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $where = $client->getClientWhere();
        $db->query("DELETE FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where;");

        return true;
    }

    /*
     * get in basket at least one checked item
     * */
    public function checkStatusBasket(): bool
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $where = $client->getClientWhere();
        $r = $db->query("SELECT 1 FROM `basket` WHERE $where AND `status_checked` = 1;");
        $n = $db->num_rows($r);

        return ($n > 0);
    }

    /*
     * check item in basket
     * */
    public function checkBasketItem($art_id, $storage_id, $status): bool
    {
        $art_id     = $this->getUrlNumber($art_id);
        $storage_id = $this->getUrlNumber($storage_id);
        $status     = $this->getUrlNumber($status);

        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $where = $client->getClientWhere();
        $db->query("UPDATE `basket` SET `status_checked` = $status WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where;");

        return true;
    }

    /*
     * update basket form
     * */
    public function updateBasketForm($art_id, $amount, $storage_id): bool
    {
        $art_id     = $this->getUrlNumber($art_id);
        $amount     = $this->getUrlNumber($amount);
        $storage_id = $this->getUrlNumber($storage_id);

        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $where = $client->getClientWhere();
        $status_action = 0;
        if ($this->checkActionPrice($art_id)) {
            list($action_id, $action_amount) = $this->checkActionPrice($art_id);
            if ($amount >= $action_amount) {
                $status_action = $action_id;
            }
        }

        $db->query("UPDATE `basket` SET `amount` = '$amount', `status_action` = '$status_action' WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where;");

        return true;
    }

    /*
     * count basket items
     * */
    public function countBasket(): array
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $where = $client->getClientWhere();
        $r = $db->query("SELECT COUNT(`id`) as count_basket FROM `basket` WHERE $where;");
        $count = (int)$db->result($r, 0, "count_basket");

        if ($count === 0) {
            $label = "";
            $style = "tool-status-hidden";
        } else {
            $label = $count;
            $style = "";
        }

        return array($label, $style);
    }

    /*
     * get summ basket
     * */
    public function countSummBasket(): string
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();
        $where = $client->getClientWhere();
        $summary = 0;

        $r = $db->query("SELECT `price`, `amount` FROM `basket` WHERE $where;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $price  = (float)$db->result($r, $i - 1, "price");
                $price  = $exrate->getKoursPrice($price, $this->getCurrentExrate());
                $stock  = (int)$db->result($r, $i - 1, "amount");
                $sum    = $price * $stock;
                $summary += $sum;
            }
        } else {
            $summary = 0;
        }

        $cur_cap = $this->getSymbolExrate($this->getCurrentExrate());
        $summary .= " $cur_cap";

        return $summary;
    }

    /*
     * update basket in order
     * if client bonus checked
     * */
    public function updateOrderBasket(): bool
    {
        $dbt = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();

        $client_id  = $this->getClient();
        $where      = $client->getClientWhere();
        $cur        = $this->getCurrentExrate();
        $bonus_summ = $this->getBonusSumm($client_id);

        if ($bonus_summ > 0) {
            $order_sum = $this->getOrderSummCur();
            $r = $dbt->query("SELECT `id`, `price` FROM `basket` WHERE $where AND `status_checked` = 1;");
            $n = $dbt->num_rows($r);

            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $id     = $dbt->result($r, $i - 1, "id") + 0;
                    $price  = $dbt->result($r, $i - 1, "price");
                    $price  = $exrate->getKoursPrice($price, $cur);
                    if ($cur === 1) {
                        $price = $client->getClientPriceRounding($client_id, $price);
                    }

                    $discountData = $this->getBonusDiscount($order_sum, $bonus_summ, $price);
                    $discount = abs($discountData["discount"]);
                    $real_discount = abs($discountData["real_discount"]);
                    $this->updateBonusClient($discount);
                    $dbt->query("UPDATE `basket` SET `price` = '$price', `discount` = '$real_discount' WHERE `id` = $id;");
                }
            }
        }

        return true;
    }

    /*
     * update client bonus
     * */
    public function updateBonusClient($discount): bool
    {
        $db = DbSingleton::getDbm();
        $client_id = $this->getClient();
        $db->query("UPDATE A_CLIENTS SET `bonus_balance` = `bonus_balance` - $discount WHERE `id` = $client_id LIMIT 1;");

        return true;
    }

    /*
     * finish order form
     * get summ order
     * */
    public function finishOrderBasket($order_id)
    {
        $db = DbSingleton::getDbm();
        $dbt = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $sum = 0;
        $where = $client->getClientWhere();

        $r = $dbt->query("SELECT `id`, `art_id`, `brand_id`, `amount`, `price`, `discount`, `suppl_id`, `storage_id`, `status_action` FROM `basket` WHERE $where AND `status_checked` = 1;");
        $n = $dbt->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id             = $dbt->result($r, $i - 1, "id");
            $art_id         = $dbt->result($r, $i - 1, "art_id");
            $brand_id       = $dbt->result($r, $i - 1, "brand_id");
            $amount         = $dbt->result($r, $i - 1, "amount");
            $price          = $dbt->result($r, $i - 1, "price");
            $discount       = $dbt->result($r, $i - 1, "discount");
            $suppl_id       = $dbt->result($r, $i - 1, "suppl_id");
            $storage_id     = $dbt->result($r, $i - 1, "storage_id");
            $status_action  = $dbt->result($r, $i - 1, "status_action");
            $full_price     = $price * $amount;
            $sum            += $full_price;

            $rmax = $db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;");
            $max = (int)$db->result($rmax, 0, "max_order_str") + 1;
            $db->query("INSERT INTO `orders_str_new` (`id`, `order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `discount`, `status_action`) 
            VALUES ('$max', '$order_id', '$suppl_id', '$storage_id', '$art_id', '$brand_id', '$amount', $price, '$full_price', '$discount', '$status_action');");
            $dbt->query("DELETE FROM `basket` WHERE `id` = $id;");
        }

        if ($order_id > 0 && $n > 0) {
            $delivery_sum = $this->setDeliveryIndex($order_id);
            $sum += $delivery_sum;
        }

        return $sum;
    }

    /*
     * ADD DELIVERY INDEX
     * */
    public function setDeliveryIndex($order_id)
    {
        $db = DbSingleton::getDbm();
        $client = new ClientClass();

        $price = $delivery_id = 0;
        $r = $db->query("SELECT `order_info_id`, `tpoint_id`, `client_id`, `client_user_id` FROM `orders_new` WHERE `ID` = $order_id LIMIT 1;");
        $order_info_id  = (int)$db->result($r, 0, "order_info_id") + 0;
        $tpoint_id      = (int)$db->result($r, 0, "tpoint_id");
        $client_id      = (int)$db->result($r, 0, "client_id");
        $user_id        = (int)$db->result($r, 0, "client_user_id");

        $r = $db->query("SELECT `DELIVERY_ID` FROM `ORDERS_CLIENT_INFO` WHERE `ID` = $order_info_id LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $delivery_id = (int)$db->result($r, 0, "DELIVERY_ID");
            $db->query("UPDATE `ORDERS_CLIENT_INFO` SET `CLIENT_ID` = $client_id, `USER_ID` = $user_id WHERE `ID` = $order_info_id LIMIT 1;");
        } else {
            $r2 = $db->query("SELECT `ID` FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID` = $client_id AND `USER_ID` = $user_id AND `DELIVERY_ID` = $delivery_id LIMIT 1;");
            $n2 = $db->num_rows($r2);

            if ($n2 === 0) {
                $db->query("INSERT INTO `ORDERS_CLIENT_INFO` (`CLIENT_ID`, `USER_ID`, `STATUS`) VALUES ($client_id, $user_id, 1);");
            }
        }

        if (in_array($delivery_id, [4, 5], true) && ($client->checkRetailClientCategory($client_id))) {
            list($art_id, $brand_id, $storage_id, $price) = $this->getDeliveryIndex($delivery_id, $tpoint_id);
            $rmax = $db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;");
            $max = (int)$db->result($rmax, 0, "max_order_str") + 1;
            $db->query("INSERT INTO `orders_str_new` (`id`, `order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
            VALUES ('$max', '$order_id', '0', '$storage_id', '$art_id', '$brand_id', '1', $price, '$price', '0');");
        }

        return $price;
    }

    /*
     * GET Delivery index
     * */
    public function getDeliveryIndex($delivery_id, $tpoint_id): array
    {
        $client = new ClientClass();
        $art_id = $brand_id = $storage_id = $price = 0;

        if (in_array($delivery_id, [4, 5], true)) {

            if ($delivery_id === 4) {
                $art_id = 100060075; // NOVA POSHTA
            }

            if ($delivery_id === 5) {
                $art_id = 100060076; // NOVA POSHTA KURER
            }

            $brand_id   = $this->getArticleBrand($art_id);
            $storage_id = $client->getDefaultStorageID($tpoint_id);
            $price      = $this->getArticlePrice($art_id);
        }

        return array($art_id, $brand_id, $storage_id, $price);
    }

    /*
     * get order summ
     * */
    public function getOrderSumm($order_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `price_summ` FROM `orders_new` WHERE `id` = $order_id LIMIT 1;");
        $n = $db->num_rows($r);

        return ($n > 0) ? $db->result($r, 0, "price_summ") : 0;
    }

    /*
     * GET CLIENT DELIVERY DATA (by CITY, USER_ID)
     * */
    public function getUserSavedData($user_id, $city_id): array
    {
        $user_id = $this->getUrlNumber($user_id);
        $city_id = $this->getUrlNumber($city_id);
        $db = DbSingleton::getDbm();
        $client = new ClientClass();

        if (empty($user_id) || $user_id === "undefined") {
            $user_id = $this->getUser();
        }
        $client_id = $client->getClientByUser($user_id);
        $list = "";
        $status = $info_id = $id = 0;

        if ($user_id > 0) {
            $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID` = $client_id AND `USER_ID` = $user_id AND `CITY_ID` = $city_id AND `STATUS` = 1;");
            $n = (int)$db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $id             = $db->result($r, $i - 1, "ID");
                $delivery_id    = $db->result($r, $i - 1, "DELIVERY_ID");
                $payment_id     = $db->result($r, $i - 1, "PAYMENT_ID");
                $street         = $db->result($r, $i - 1, "DEL_STREET");
                $house          = $db->result($r, $i - 1, "DEL_HOUSE");
                $porch          = $db->result($r, $i - 1, "DEL_PORCH");
                $dep_text       = $db->result($r, $i - 1, "DEL_DEPARTMENT_TEXT");
                $express        = $db->result($r, $i - 1, "DEL_EXPRESS");
                $express_info   = $db->result($r, $i - 1, "DEL_EXPRESS_INFO");
                $delivery_text  = $this->getDeliveryCaption($delivery_id);
                $payment_text   = $this->getPaymentCaption($payment_id);
                $delivery_info  = $this->getDeliveryInfoCaption($delivery_id, $street, $house, $porch, $dep_text, $express, $express_info);

                if ($delivery_info !== "") {
                    $delivery_info = "($delivery_info)";
                }

                $list .= "
                <li class=\"orders-user__item\">
                    <a onclick=\"setClientOrderInfo('$id');\">$i. $delivery_text $delivery_info <br> $payment_text</a>
                    <a onclick=\"dropClientOrderInfo('$id');\">&times;</a>
                </li>";
            }

            if ($n === 1) {
                $status     = 1;
                $info_id    = $id;
            }

            if ($n > 0) {
                $list = "
                <div class=\"orders-user\">
                    <p class=\"orders-user__title\">{saved_address}:</p>
                    <a class=\"orders-user__toggle\" onclick=\"ordersUserToggle();\"><i class=\"fa fa-chevron-down\"></i></a>
                    <div id=\"user_saved_info_list\">" . $list . "</div>
                </div>";
            }
        }

        $list = $this->replaceLang($list);

        return array($status, $list, $info_id);
    }

    /*
     * Get Success Order Form
     * */
    public function getOrderContentForm($order_id, $user_id, $user_status)
    {
        $client = new ClientClass();

        if ($user_status === 0) {
            $form = $this->getHtmlForm("orders/done");
        } else {
            $form = $this->getHtmlForm("orders/done_retail");
        }

        $userData = $client->getClientInfo($client->getClientByUser($user_id), $user_id);
        $form = str_replace(array("{order_id}", "{order_user_id}", "{user_phone}", "{user_name}", "{user_email}", "{user_pass}"), array($order_id, $user_id, $userData["phone"], $userData["name"], $userData["email"], $userData["password"]), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * GET Order Form
     * */
    public function getOrderForm()
    {
        $client = new ClientClass();
        $user_id = $this->getUser();
        $user_name = $user_phone = $user_email = "";
        $user_city = 0;
        $status = false;

        if ($user_id > 0) {
            list($user_name, $user_phone, $user_email, $user_city) = $client->getClientUserData($user_id);

            if ($user_id > 0 && $user_phone !== "" && $user_name !== "" && $user_city !== "") {
                $status = true;
            }
        }

        $form = $this->getHtmlForm("orders/form");
        $form = str_replace(array("{order_user_id}", "{order_delivery}", "{order_payment}", "{user_city_main_list}", "{user_name}", "{user_phone}", "{user_email}", "{basket_range}", "{order_user_status}", "{user_name_disable}"), array($user_id, $this->getOrderDelivery(), $this->getOrderPayment(), $this->getCitiesMainSelect($user_city), $user_name, $user_phone, $user_email, $this->getBasketOrder(), $status, ($user_id > 0) ? "disabled" : ""), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * GET Order Delivery Form
     * */
    public function getOrderDelivery()
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();

        $form = $this->getHtmlForm("orders/delivery");
        $form = str_replace(array("{tpoint_address}", "{express_delivery_list}"), array($client->getTpointAddress($client->getTpointUser($this->getClient())), $this->getDeliveryExpressList()), $form);

        $r = $db->query("SELECT `ID`, `TEXT`, `TYPE`, `STATUS` FROM `T2_DELIVERY` WHERE 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id         = $db->result($r, $i - 1, "ID");
            $text       = $db->result($r, $i - 1, "TEXT");
            $type       = (int)$db->result($r, $i - 1, "TYPE");
            $status     = $db->result($r, $i - 1, "STATUS");
            $display    = (!$status) ? "none" : "";

            $free = "";
            if ($type === 1) {
                $free = "({free_cap})";
            }
            if ($type === 2) {
                $free = "({carrier_conditions})";
            }

            $form = str_replace(array("{delivery_status_$id}", "{delivery_text_$id}", "{delivery_free_$id}"), array($display, $text, $free), $form);
        }

        return $form;
    }

    /*
     * GET Order Payment Form
     * */
    public function getOrderPayment()
    {
        $db = DbSingleton::getTokoDb();
        $form = $this->getHtmlForm("orders/payment");
        $r = $db->query("SELECT `ID`, `TEXT`, `STATUS` FROM `T2_PAYMENT` WHERE 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id         = $db->result($r, $i - 1, "ID");
            $text       = $db->result($r, $i - 1, "TEXT");
            $status     = $db->result($r, $i - 1, "STATUS");
            $display    = (!$status) ? "none" : "";

            $form = str_replace(array("{payment_status_$id}", "{payment_text_$id}"), array($display, $text), $form);
        }

        return $form;
    }

    /*
     * GET AJAX Order Delivery Form
     * */
    public function getOrderDeliveryBlock($delivery_id, $city_id): int
    {
        $db = DbSingleton::getDbm();
        $delivery_id = $this->getUrlNumber($delivery_id);
        $city_id = $this->getUrlNumber($city_id);
        $result = 0;
        $r = $db->query("SELECT `VALID_TYPE_MAIN`, `VALID_TYPE_OTHER` FROM `orders_valid_delivery` WHERE `DELIVERY_ID` = $delivery_id LIMIT 1;");
        $valid_main = $db->result($r, 0, "VALID_TYPE_MAIN");
        $valid_other = $db->result($r, 0, "VALID_TYPE_OTHER");

        if (in_array($city_id, [10108, 24861])) { // MAIN CITTIES
            if ($valid_main) {
                $result = 1;
            }
        } elseif ($valid_other) {
            $result = 1;
        }

        return $result;
    }

    /*
     * GET AJAX Order Delivery Form
     * */
    public function getOrderPaymentBlock($payment_id, $delivery_id): int
    {
        $db = DbSingleton::getDbm();
        $payment_id = $this->getUrlNumber($payment_id);
        $delivery_id = $this->getUrlNumber($delivery_id);
        $del_types_1 = [1, 2, 3];
        $del_types_2 = [4, 5, 6];

        $result = 0;
        $r = $db->query("SELECT `VALID_TYPE` FROM `orders_valid_payment` WHERE `PAYMENT_ID` = $payment_id LIMIT 1;");
        $valid = (int)$db->result($r, 0, "VALID_TYPE");

        if ($valid === 0) {
            $result = 1;
        }

        if (($valid === 1) && in_array($delivery_id, $del_types_1)) {
            $result = 1;
        }

        if (($valid === 2) && in_array($delivery_id, $del_types_2)) {
            $result = 1;
        }

        return $result;
    }

    /*
     * SET City Address
     * */
    public function setCityAddress($city_id): string
    {
        $city_id = $this->getUrlNumber($city_id);
        $client = new ClientClass();
        $cities = [24861, 10108];
        $city_address = "";

        if (in_array($city_id, $cities)) {
            $tpoint_id = 0;

            if ($city_id === 24861) {
                $tpoint_id = 1;
            }

            if ($city_id === 10108) {
                $tpoint_id = 2;
            }

            $city_address = $this->getCityName($city_id) . " - " . $client->getTpointAddress($tpoint_id);
        }

        return $city_address;
    }

    /*
     * GET Delivery Express
     * */
    public function getDeliveryExpressList()
    {
        $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT `ID`, `TEXT` FROM `T2_DELIVERY_EXPRESS` WHERE 1 ORDER BY `ID` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id     = $db->result($r, $i - 1, "ID");
            $text   = $db->result($r, $i - 1, "TEXT");
            $list   .= "
            <option value=\"$id\">$text</option>";
        }

        $list = $this->replaceLang($list);

        return $list;
    }

    /*
     * order form validation
     * */
    public function validOrder($name, $phone, $city, $delivery, $delivery_type, $payment, $email, $comment)
    {
        $name       = $this->getNameString($name);
        $phone      = $this->getNameString($phone);
        $email      = $this->getNameString($email);
        $comment    = $this->getNameString($comment);
        $city       = $this->getUrlNumber($city);
        $delivery   = $this->getUrlNumber($delivery);
        $payment    = $this->getUrlNumber($payment);

        $delivery_type_text = "";
        $street             = $delivery_type["street"];
        $house              = $delivery_type["house"];
        $porch              = $delivery_type["porch"];
        $department         = $delivery_type["department"];
        $del_express        = $delivery_type["delivery_express"];
        $del_express_dep    = $delivery_type["delivery_express_department"];

        if ($porch !== "") {
            $porch = ", {entrance_cap} $porch";
        }

        if (($street !== "undefined") && ($house !== "undefined")) {
            $delivery_type_text .= "<div>{address_cap}: {street_cap} $street, {house_cap} $house $porch</div>";
        }

        if ($department !== "undefined" && $department !== "0") {
            $delivery_type_text .= "<div>{department_cap}: $department</div>";
        }

        if ($del_express !== "undefined") {
            $delivery_express_text  = $this->getDepartmentExpressName($del_express);
            $delivery_type_text     .= "<div>{delivery_type_7}: $delivery_express_text</div>";
        }

        if ($del_express_dep !== "undefined") {
            $delivery_type_text .= "<div>{department_cap}: $del_express_dep</div>";
        }

        if ($delivery === 1) {
            $tpoint_address     = $this->setCityAddress($city);
            $delivery_type_text = "<div>$tpoint_address</div>";
        }

        $form = $this->getHtmlForm("orders/confirm");
        $form = str_replace(array("{order_name}", "{order_phone}", "{order_city}", "{order_delivery}", "{order_delivery_type}", "{order_payment}", "{order_email}", "{order_comment}"), array($name, $phone, $this->getCityName($city), $this->getDeliveryName($delivery), $delivery_type_text, $this->getPaymentName($payment), ($email === "") ? "{absent_cap}" : $email, ($comment === "") ? "{absent_cap}" : $comment), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    public function letsFinishOrder($phone, $dataArticle): array
    {
        $client = new ClientClass();

        if ($client->validateOperator($phone)) {
            $dataReg = $client->validateRegistration($phone);

            if (!$dataReg[0]) {

                if (!empty($dataArticle)) {
                    $art_id     = $dataArticle["art_id"];
                    $brand_id   = $dataArticle["brand_id"];
                    $amount     = $dataArticle["count"];
                    $stock      = $dataArticle["stock"];
                    $storage_id = $dataArticle["storage_id"];
                    $suppl_id   = $dataArticle["suppl_id"];

                    $res = $this->saveFastOrderBasket($phone, $art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id);
                } else {

                    $res = $this->saveFastOrder($phone);
                }

                $answer = 1;
                $err = $res;
            } else {
                $answer = 2;
                $err = "{user_already_logged}!<br>{phone_cap}: " . $dataReg[1];
            }
        } else {
            $answer = 3;
            $err = "{check_phone_data}!";
        }

        $err = $this->replaceLang($err);

        return array($answer, $err);
    }

    public function saveFastOrderBasket($phone, $art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id): string
    {
        $basket_amount = $this->getBasketArticleAmount($art_id, $storage_id);
        if ($basket_amount === 0) {
            $this->moveToBasket($art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id);
        }

        return $this->saveFastOrder($phone);
    }

    /*
     * finish Fast Order
     * */
    public function saveFastOrder($phone): string
    {
        $client = new ClientClass();
        $phone = $client->formatValidPhone($phone);
        list(, $user_id) = $client->getAuthorizedUser($phone);
        $client_id = $client->getClientByUser($user_id);
        $user_status = 0;

        // CREATE CLIENT
        if (empty($user_id)) {
            $clientData     = $client->addRetailClient($this->getClient(), $phone);
            $client_id      = $clientData["client_id"];
            $user_id        = $clientData["user_id"];
            $user_status    = 1;
        }

        $tpoint_id  = $this->getTpointID();
        $cookie     = $this->getSessionID();
        $cash_id    = $client->getClientCurrency($client_id);

        // CREATE ORDER
        $order_id = $this->saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, "", "", $phone, 0, "", 0, 0);

        return $this->getSiteLink() . "order/?order_id=$order_id&user_id=$user_id&user_status=$user_status/";
    }

    public function addFastOrder($phone, $art_id, $brand_id, $suppl_id, $storage_id, $amount): string
    {
        $db = DbSingleton::getDbm();
        $exrate = new ExrateClass();
        $client = new ClientClass();

        $phone = $client->formatValidPhone($phone);
        list(, $user_id) = $client->getAuthorizedUser($phone);
        $client_id = $client->getClientByUser($user_id);
        $user_status = 0;

        // CREATE CLIENT
        if (empty($user_id)) {
            $clientData     = $client->addRetailClient($this->getClient(), $phone);
            $client_id      = $clientData["client_id"];
            $user_id        = $clientData["user_id"];
            $user_status    = 1;
        }

        $tpoint_id  = $this->getTpointID();
        $cookie     = $this->getSessionID();
        $cash_id    = $client->getClientCurrency($client_id);

        $status_action = 0;

        $r = $db->query("SELECT MAX(`ID`) as maxim FROM `orders_new`;");
        $order_id = (int)$db->result($r, 0, "maxim") + 1;

        $price = $this->getArticlePrice($art_id);
        if ((int)$suppl_id !== 0) {
            $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
        }

        if ($this->checkActionPrice($art_id)) {
            list($action_id, $action_amount, $action_price) = $this->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, 1); // to UAH

            if ($amount >= $action_amount) {
                $status_action = $action_id;
                $price = $action_price;
            }
        }

        $summ = $price * $amount;

        $db->query("INSERT INTO `orders_new` (`id`, `client_id`, `client_user_id`, `cookie_id`, `tpoint_id`, `cash_id`, `name`, `email`, `phone`, `region`, `comment`, `order_info_id`, `price_summ`) 
        VALUES ($order_id, $client_id, $user_id, '$cookie', $tpoint_id, $cash_id, '', '', '$phone', '', '', 0, '$summ');");

        $db->query("INSERT INTO `orders_str_new` (`order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
        VALUES ($order_id, $suppl_id, $storage_id, $art_id, $brand_id, '$amount', '$price', '$summ', $status_action);");

        return $this->getSiteLink() . "order/?order_id=$order_id&user_id=$user_id&user_status=$user_status/";
    }

    /*
     * save order form
     * */
    public function saveOrder($user_id, $name, $phone, $city_id, $delivery_id, $delivery_type, $payment_id, $email, $comment, $recipient_name, $recipient_phone, $bonus_status = 0): array
    {
        $client = new ClientClass();

        $phone              = $client->formatValidPhone($phone);
        $name               = $this->getUrlString($name);
        $email              = $this->getUrlString($email);
        $comment            = $this->getUrlString($comment);
        $recipient_name     = $this->getUrlString($recipient_name);
        $recipient_phone    = $client->formatValidPhone($recipient_phone);
        $recipient_phone    = ($recipient_phone === 0 || $recipient_phone === "0") ? "" : $recipient_phone;

        if (empty($user_id) || $user_id === "undefined") {
            $user_id    = $this->getUser();
            $client_id  = $this->getClient();
        } else {
            $client_id = $client->getClientByUser($user_id);
        }

        $tpoint_id      = $this->getTpointID();
        $cookie         = $this->getSessionID();
        $cash_id        = $client->getClientCurrency($client_id);
        $user_status    = 0;

        $street             = $delivery_type["street"];
        $house              = $delivery_type["house"];
        $porch              = $delivery_type["porch"];
        $department_id      = $delivery_type["department_id"];
        $department_text    = ($delivery_type["department"] === "0" || $delivery_type["department"] === 0) ? "" : $delivery_type["department"];
        $delivery_express   = $delivery_type["delivery_express"];
        $express_info       = $delivery_type["delivery_express_department"];

        $delivery_info = [
            "street"        => $street,
            "house"         => $house,
            "porch"         => $porch,
            "department"    => $department_id,
            "express"       => $delivery_express,
            "express_info"  => $express_info
        ];

        // CREATE CLIENT
        if ((int)$user_id === 0) {
            $tpoint_client_id   = $client_id;
            $clientData         = $client->addRetailClient($tpoint_client_id, $phone, $name, $city_id, $email);
            $client_id          = $clientData["client_id"];
            $user_id            = $clientData["user_id"];
            $user_status        = 1;
        }

        // CREATE CLIENT ORDER INFO
        $order_info_id = $this->saveClientOrderInfo($client_id, $user_id, $city_id, $delivery_id, $department_text, $payment_id, $delivery_info, $recipient_name, $recipient_phone);

        // CREATE ORDER
        $order_id = $this->saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, $name, $email, $phone, $city_id, $comment, $order_info_id, $bonus_status);

        return array($order_id, $user_id, $user_status);
    }

    /*
     * create order
     * create order str
     * check client bonus
     * finish basket
     * */
    public function saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, $name, $email, $phone, $city_id, $comment, $order_info_id, $bonus_status): int
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT MAX(`ID`) as maxim FROM `orders_new`;");
        $order_id = (int)$db->result($r, 0, "maxim") + 1;

        $db->query("INSERT INTO `orders_new` (`id`, `client_id`, `client_user_id`, `cookie_id`, `tpoint_id`, `cash_id`, `name`, `email`, `phone`, `region`, `comment`, `order_info_id`, `price_summ`) 
        VALUES ($order_id, $client_id, $user_id, '$cookie', $tpoint_id, $cash_id, '$name', '$email', '$phone', '$city_id', '$comment', $order_info_id, 0);");
        if ($bonus_status) {
            $this->updateOrderBasket();
        }

        $order_sum = $this->finishOrderBasket($order_id);
        $db->query("UPDATE `orders_new` SET `price_summ` = '$order_sum' WHERE `id` = $order_id LIMIT 1;");

        return $order_id;
    }

    /*
     * save user data
     * login user
     * */
    public function saveOrderClient($user_id, $name, $email, $pass): string
    {
        $db = DbSingleton::getDbm();
        $client = new ClientClass();

        $user_id    = $this->getUrlNumber($user_id);
        $name       = $this->getNameString($name);
        $email      = $this->getNameString($email);
        $pass       = $this->getNameString($pass);

        $db->query("UPDATE `A_CLIENTS_USERS` SET `name` = '$name', `email` = '$email', `pass` = '$pass' WHERE `id` = $user_id LIMIT 1;");
        $client->loginOrderClient($user_id);

        return $this->getSiteLink() . "profile/orders/";
    }

    /*
     * get order info
     * delivery
     * payment
     * recipient
     * */
    public function saveClientOrderInfo($client_id, $user_id, $city_id, $delivery_id, $department_text, $payment_id, $delivery_info = [], $recipient_name = "", $recipient_phone = "")
    {
        $db = DbSingleton::getDbm();

        $street     = $delivery_info["street"];
        $house      = $delivery_info["house"];
        $porch      = $delivery_info["porch"];
        $department = $delivery_info["department"];
        $express    = $delivery_info["express"];
        $express_in = $delivery_info["express_info"];
        $street     = ($street === "undefined") ? "" : $street;
        $house      = ($house === "undefined") ? "" : $house;
        $porch      = ($porch === "undefined") ? "" : $porch;
        $express_in = ($express_in === "undefined") ? "" : $express_in;
        $department_text = ($department_text === "undefined" || $department_text === "0" || $department_text === 0) ? "" : $department_text;

        $r = $db->query("SELECT `ID` FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID` = $client_id AND `USER_ID` = $user_id AND `CITY_ID` = $city_id AND `DELIVERY_ID` = $delivery_id AND `PAYMENT_ID` = $payment_id AND `DEL_STREET` = '$street' AND `DEL_HOUSE` = '$house' AND `DEL_PORCH` = '$porch' AND `DEL_DEPARTMENT` = '$department' AND `DEL_EXPRESS` = $express AND `DEL_EXPRESS_INFO` = '$express_in' LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n === 0) {
            $r2 = $db->query("SELECT `ID` FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID` = $client_id AND `USER_ID` = $user_id AND `DELIVERY_ID` = $delivery_id AND `PAYMENT_ID` = $payment_id AND `CITY_ID` = $city_id LIMIT 1;");
            $n2 = $db->num_rows($r2);

            if ($n2 === 0) {
                $r = $db->query("SELECT MAX(`ID`) as maxim FROM `ORDERS_CLIENT_INFO`;");
                $order_info_id = (int)$db->result($r, 0, "maxim") + 1;

                $db->query("INSERT INTO `ORDERS_CLIENT_INFO` (`ID`, `CLIENT_ID`, `USER_ID`, `CITY_ID`, `DELIVERY_ID`, `PAYMENT_ID`, `DEL_NAME`, `DEL_PHONE`, `DEL_STREET`, `DEL_HOUSE`, `DEL_PORCH`, `DEL_DEPARTMENT`, `DEL_DEPARTMENT_TEXT`, `DEL_EXPRESS`, `DEL_EXPRESS_INFO`) 
                VALUES ($order_info_id, $client_id, $user_id, $city_id, $delivery_id, $payment_id, '$recipient_name', '$recipient_phone', '$street', '$house', '$porch', '$department', '$department_text', $express, '$express_in');");
            } else {
                $order_info_id = $db->result($r2, 0, "ID");
            }
          } else {
            $order_info_id = $db->result($r, 0, "ID");
        }

        return $order_info_id;
    }

    /*
     * get delivery info captions
     * */
    public function getDeliveryInfoCaption($delivery_id, $street, $house, $porch, $department_text, $express, $express_info)
    {
        $info = "";
        switch ($delivery_id) {
            case 3:
            {
                $info = "{address_cap}: {street_cap} $street, {house_cap} $house";
                break;
            }
            case 2:
            case 5:
            {
                if ($porch !== "") {
                    $porch = ", {entrance_cap} $porch";
                }
                $info = "{address_cap}: {street_cap} $street, {house_cap} $house $porch";
                break;
            }
            case 4:
            case 6:
            {
                $info = $department_text;
                break;
            }
            case 7:
            {
                $delivery_express_text = $this->getDepartmentExpressName($express);
                $info = "{delivery_type_7}: $delivery_express_text, {department_cap}: $express_info";
                break;
            }
            case 1:
            default:
            {
                break;
            }
        }

        return $info;
    }

    /*
     * get delivery caption
     * */
    public function getDeliveryCaption($delivery_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `T2_DELIVERY` WHERE `ID` = $delivery_id LIMIT 1;");
        $text = $db->result($r, 0, "TEXT");
        $text = $this->replaceLang($text);

        return $text;
    }

    /*
     * get payment caption
     * */
    public function getPaymentCaption($payment_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT` FROM `T2_PAYMENT` WHERE `ID` = $payment_id LIMIT 1;");
        $text = $db->result($r, 0, "TEXT");
        $text = $this->replaceLang($text);

        return $text;
    }

    /*
     * drop client order info
     * */
    public function dropClientOrderInfo($id): bool
    {
        $id = $this->getUrlNumber($id);
        $db = DbSingleton::getDbm();
        $db->query("UPDATE `ORDERS_CLIENT_INFO` SET `STATUS` = 0 WHERE `ID` = $id;");

        return true;
    }

    /*
     * add client order info
     * */
    public function setClientOrderInfo($id): array
    {
        $id = $this->getUrlNumber($id);
        $db = DbSingleton::getDbm();

        $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `ID` = $id AND `STATUS` = 1;");
        $city_id            = $db->result($r, 0, "CITY_ID");
        $delivery_id        = $db->result($r, 0, "DELIVERY_ID");
        $payment_id         = $db->result($r, 0, "PAYMENT_ID");
        $street             = $db->result($r, 0, "DEL_STREET");
        $house              = $db->result($r, 0, "DEL_HOUSE");
        $porch              = $db->result($r, 0, "DEL_PORCH");
        $department         = $db->result($r, 0, "DEL_DEPARTMENT");
        $express            = $db->result($r, 0, "DEL_EXPRESS");
        $express_info       = $db->result($r, 0, "DEL_EXPRESS_INFO");
        $recipient_name     = $db->result($r, 0, "DEL_NAME");
        $recipient_phone    = $db->result($r, 0, "DEL_PHONE");
        $delivery_info      = compact("street", "house", "porch", "department", "express", "express_info");

        return
            array(
                "city_id"           => $city_id,
                "delivery_id"       => $delivery_id,
                "payment_id"        => $payment_id,
                "delivery_info"     => $delivery_info,
                "recipient_name"    => $recipient_name,
                "recipient_phone"   => $recipient_phone
            );
    }

    /*
     * delivery fields validation
     * */
    public function validDeliveryFields($delivery, $delivery_type): array
    {
        $delivery       = $this->getUrlNumber($delivery);
        $result         = true;
        $fields         = [];
        $street         = $delivery_type["street"];
        $house          = $delivery_type["house"];
        $department     = $delivery_type["department"];
        $department_id  = $delivery_type["department_id"];
        $del_ex         = $delivery_type["delivery_express"];
        $del_ex_dep     = $delivery_type["delivery_express_department"];

        switch ($delivery) {
            case 4:
            {
                if (empty($department_id)) {
                    $fields[] = "department";
                    $result = false;
                }
                break;
            }
            case 2:
            case 3:
            case 5:
            {
                if (empty($street) || empty($house)) {
                    if (empty($street)) {
                        $fields[] = "street";
                    }
                    if (empty($house)) {
                        $fields[] = "house";
                    }
                    $result = false;
                }
                break;
            }
            case 6:
            {
                if (empty($department)) {
                    $fields[] = "department";
                    $result = false;
                }
                break;
            }
            case 7:
            {
                if ((empty($del_ex_dep)) || (empty($del_ex))) {
                    if (empty($del_ex_dep)) {
                        $fields[] = "delivery_express_department";
                    }
                    if (empty($del_ex)) {
                        $fields[] = "delivery_express";
                    }
                    $result = false;
                }
                break;
            }
            case 1:
            default:
            {
                break;
            }
        }

        return array($result, $fields);
    }

    /*
     * get order total form
     * */
    public function getOrderTotal($total): string
    {
        $cur        = $this->getCurrentExrate();
        $cur_cap    = $this->getSymbolExrate($cur);

        return "
        <div class=\"cart-table-row cart-table-row-offset\">
            <div class=\"cart-table-cell cart-table-cell__label\">{total_cap}</div>
            <div class=\"cart-table-cell cart-table-cell__price\">$total $cur_cap</div>
        </div>";
    }

    /*
     * hide order info
     * */
    public function hideOrderInfo($name, $phone, $city)
    {
        $list = "
        <span>$name, $phone, $city</span> <a onclick=\"editFields();\">{edit_cap}</a>";
        $list = $this->replaceLang($list);

        return $list;
    }

    /*
     * get order basket form
     * */
    public function getBasketOrder($delivery_id = 0, $bonus_status = 0)
    {
        $delivery_id    = $this->getUrlNumber($delivery_id);
        $bonus_status   = $this->getUrlNumber($bonus_status);

        $cur            = $this->getCurrentExrate();
        $cur_cap        = $this->getSymbolExrate($cur);

        $bonus_summ = $this->getBonusSumm($this->getClient());
        list($basket_range, $basket_total, $bonus_total) = $this->getBasketOrderRange($bonus_status, $bonus_summ);

        list($delivery_total, $delivery_total_text) = $this->getDeliveryPrice($delivery_id);

        $basket_total_full  = $basket_total;
        $basket_total -= $bonus_total;

        $basket_total_cap = $basket_total . " $cur_cap";
        if ($bonus_total > 0) {
            $basket_total_cap = "
            <span class=\"span-red\" style=\"text-decoration: line-through\">
                $basket_total_full $cur_cap
            </span><br>" . $basket_total_cap;
        }

        $total = $basket_total + $delivery_total;

        $form = $this->getHtmlForm("orders/basket");
        if ($delivery_id === 0) {
            $form = str_replace(array("{basket_order_delivery_price}", "{basket_order_price}", "{basket_button_status}"), array("", "", "none"), $form);
        }

        $basket_client_bonus = ($bonus_summ > 0) ? $this->showClientBonusOrder($bonus_status, $bonus_total) : "";

        $form = str_replace(array("{basket_content}", "{basket_full_price}", "{basket_order_delivery_price}", "{basket_order_price}", "{basket_button_status}", "{basket_client_bonus}"), array($basket_range, $basket_total_cap, $delivery_total_text, $this->getOrderTotal($total), "", $basket_client_bonus), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * get client bonus summ
     * */
    public function getBonusSumm($client_id)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT `bonus_balance` FROM `A_CLIENTS` WHERE `id` = $client_id LIMIT 1;");

        return $db->result($r, 0, "bonus_balance");
    }

    /*
     * show client bonus form
     * */
    public function showClientBonusOrder($bonus_status, $bonus_total)
    {
        $bonus_summ     = $this->getBonusSumm($this->getClient());
        $checked        = ($bonus_status) ? "checked='checked'" : "";
        $bonus_checked  = ($bonus_status) ? "- $bonus_total {uah_cap}" : "";

        $form = $this->getHtmlForm("bonus/status");
        $form = str_replace(array("{checked}", "{bonus_summ}", "{bonus_checked}"), array($checked, $bonus_summ, $bonus_checked), $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    /*
     * get delivery price
     * */
    public function getDeliveryPrice($delivery_id): array
    {
        $exrate = new ExRateClass();
        $client = new ClientClass();

        $cur    = $this->getCurrentExrate();
        $price  = $price_cur = 0;

        // NOVA POSHTA
        if ($delivery_id === 4) {
            $price = $this->getArticlePrice(100060075); // NP
        }

        if ($delivery_id === 5) {
            $price = $this->getArticlePrice(100060076); // NP KURER
        }

        if ($price > 0) {
            $price_cur = $exrate->getKoursFromUAH($price, $cur);
            $price_cur = $client->getClientPriceRounding($this->getClient(), $price_cur);
        }

        if ($price_cur > 0) {
            $del_cap = "$price_cur " . $this->getSymbolExrate($cur);
        } else {
            $del_cap = "{free_cap}";
        }

        // carrier tariff
        if (in_array($delivery_id, [6, 7], true)) {
            $price = 0;
            $del_cap = "{carrier_conditions}";
        }

        if (!$client->checkRetailClientCategory($this->getClient())) {
            $price = 0;
            $del_cap = "{carrier_conditions}";
        }

        $del_cap = $this->replaceLang($del_cap);

        $list = "
        <div class=\"cart-table-row cart-table-row-offset\">
            <div class=\"cart-table-cell cart-table-cell__label\">{delivery_cap}</div>
            <div class=\"cart-table-cell cart-table-cell__price\">$del_cap</div>
        </div>";

        return array($price, $list);
    }

    /*
     * get client bonus discount price
     * */
    public function getBonusDiscount($order_sum, $bonus_summ, $price): array
    {
        // 10% procent fixed
        $procent        = 10;
        // max promegut
        $max_prom       = $order_sum * ($procent / 100);
        // max vosmojnoe
        $max_discount   = ($max_prom <= $bonus_summ) ? $max_prom : $bonus_summ;
        // discount procent
        $price_procent  = round($price / $order_sum * 100);
        // discount price
        $discount       = floor($price_procent * $max_discount / 100);
        // price with discount
        $price_discount = ceil($price - $discount);
        // real discount procent
        $real_discount  = round((($price_discount / $price) - 1) * 100, 2);

        return array(
            "discount"          => $discount,
            "price_discount"    => $price_discount,
            "real_discount"     => $real_discount
        );
    }

    public function getOrderSummCur()
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();

        $cur        = $this->getCurrentExrate();
        $where      = $client->getClientWhere();
        $order_sum  = 0;

        $r = $db->query("SELECT `amount`, `price` FROM `basket` WHERE $where AND `status_checked` = 1 ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $amount = $db->result($r, $i - 1, "amount");
                $price  = $db->result($r, $i - 1, "price");
                $price  = $exrate->getKoursPrice($price, $cur);

                if ($cur === 1) {
                    $price = $client->getClientPriceRounding($this->getClient(), $price);
                }

                $full_price = $price * $amount;
                $order_sum  += $full_price;
            }
        }

        return $order_sum;
    }

    /*
     * get basket order form
     * */
    public function getBasketOrderRange($bonus_status, $bonus_summ): array
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();
        $showform = new FormClass();

        $client_id  = $this->getClient();
        $where      = $client->getClientWhere();
        $cur        = $this->getCurrentExrate();
        $cur_cap    = $this->getSymbolExrate($cur);
        $sum_total  = $bonus_total = 0;
        $list       = "";

        $r = $db->query("SELECT `art_id`, `brand_id`, `price`, `amount` FROM `basket` WHERE $where AND `status_checked` = 1 ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $art_id     = $db->result($r, $i - 1, "art_id");
                $brand_id   = $db->result($r, $i - 1, "brand_id");
                $price      = $db->result($r, $i - 1, "price");
                $amount     = $db->result($r, $i - 1, "amount");
                $art_nr_ds  = $this->getArticleDispl($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $art_name   = $this->getArticleName($art_id);
                $price      = $exrate->getKoursPrice($price, $cur);

                if ($cur === 1) {
                    $price = $client->getClientPriceRounding($client_id, $price);
                }

                $full_price = $price * $amount;

                if ($cur === 1) {
                    $full_price = $client->getClientPriceRounding($client_id, $full_price);
                }

                $sum_total  += $full_price;
                $name       = "$art_name $brand_name ($art_nr_ds)";
                $img        = $showform->getArticleActivePhoto($art_id);
                $price_cap  = "$full_price $cur_cap";

                if ($bonus_status) {
                    $discountData   = $this->getBonusDiscount($this->getOrderSummCur(), $bonus_summ, $full_price);
                    $discount       = $discountData["discount"];
                    $price_discount = $discountData["price_discount"];
                    $real_discount  = $discountData["real_discount"];
                    $price_cap      = "<span>$full_price $cur_cap</span>";
                    $bonus_total    += $discount;

                    if ($full_price !== $price_discount) {
                        $price_cap = "
                        <span class=\"span-red\" style=\"text-decoration: line-through;\">
                            $full_price $cur_cap ($real_discount%)
                        </span><br />
                        <span>$price_discount $cur_cap</span>";
                    }
                }

                $list .= "
                <div class=\"cart-table-row\">
                    <div class=\"cart-table-cell cart-table-cell__photo\"><img src=\"$img\" alt=\"$name\"></div>
                    <div class=\"cart-table-cell cart-table-cell__text\">
                        <div class=\"cart-table-cell cart-table-cell__name\">$name</div>
                        <div class=\"cart-table-cell cart-table-cell__summ\">
                            <div class=\"cart-table-cell cart-table-cell__amount\">$amount {amount_abbr}.</div>
                            <div class=\"cart-table-cell cart-table-cell__summary\">$price_cap</div>
                        </div>
                    </div>
                </div>";
            }
        } else {
            $list = "
            <div class=\"cart-table-row\">{empty_cap}</div>";
        }

        $list = $this->replaceLang($list);

        return array($list, $sum_total, $bonus_total);
    }

    /*
     * get delivery express department
     * */
    public function setDeliveryExpressDepartment($delivery_express): string
    {
        $delivery_express = $this->getUrlNumber($delivery_express);
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `TEXT_TYPE` FROM `T2_DELIVERY_EXPRESS` WHERE `ID` = $delivery_express LIMIT 1;");
        $text_type = $db->result($r, 0, "TEXT_TYPE");
        $text_type = $this->replaceLang($text_type);

        return $text_type . ":";
    }

    /*
     * get NP cities select
     * */
    public function getCitiesMainSelect($user_city = 0): string
    {
        $user_city = $this->getUrlNumber($user_city);
        $db = DbSingleton::getTokoDb();

        $lang_id = $this->getLanguage();
        $postfix = $where_user_city = $list = "";

        if ($lang_id === 1 || $lang_id === 3) {
            $postfix = "_RU";
        }

        if ($user_city > 0) {
            $where_user_city = "OR `CITY_ID` = $user_city";
        }

        $r = $db->query("SELECT * FROM `T2_LOCATION` WHERE `REGION_NAME` = '' $where_user_city ORDER BY `CITY_NAME$postfix` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_id        = $db->result($r, $i - 1, "CITY_ID");
            $city_name1     = $db->result($r, $i - 1, "CITY_NAME_CLEAR");
            $city_name2     = $db->result($r, $i - 1, "CITY_NAME_CLEAR_RU");
            $city_name      = $db->result($r, $i - 1, "CITY_NAME_CLEAR$postfix");
            $city_name_foo  = "$city_name1 - $city_name2";
            $sel            = ($user_city > 0 && $user_city === $city_id) ? "selected" : "";

            $list .= "
            <option value=\"$city_id\" data-foo=\"$city_name\" $sel>$city_name_foo</option>";
        }

        return $list;
    }

    /*
     * get location cities by text input
     * */
    public function getCityVal($search_text): array
    {
        $search_text = $this->getNameString($search_text);
        $db = DbSingleton::getTokoDb();

        $lang_id = $this->getLanguage();
        $mas = [];
        $postfix = "";

        if ($lang_id === 1 || $lang_id === 3) {
            $postfix = "_RU";
        }

        $r = $db->query("SELECT * FROM `T2_LOCATION` 
        WHERE `CITY_NAME_CLEAR` LIKE \"$search_text%\" OR `CITY_NAME_CLEAR_RU` LIKE \"$search_text%\" ORDER BY `CITY_NAME$postfix`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_id        = $db->result($r, $i - 1, "CITY_ID");
            $city_name      = $db->result($r, $i - 1, "CITY_NAME");
            $city_name_ru   = $db->result($r, $i - 1, "CITY_NAME_RU");
            $region_name    = $db->result($r, $i - 1, "REGION_NAME");
            $region_name_ru = $db->result($r, $i - 1, "REGION_NAME_RU");
            $state_name     = $db->result($r, $i - 1, "STATE_NAME");
            $state_name_ru  = $db->result($r, $i - 1, "STATE_NAME_RU");
            $value_foo      = "$city_name ($state_name обл., $region_name р-он) - $city_name_ru ($state_name_ru обл., $region_name_ru р-он)";
            $city_cap       = "$city_name ($state_name обл., $region_name р-он)";

            if ($lang_id === 1 || $lang_id === 3) {
                $city_cap = "$city_name_ru ($state_name_ru обл., $region_name_ru р-он)";
            }

            $mas[$i] = ["id" => $city_id, "value" => $value_foo, "data-foo" => $city_cap];
        }

        return $mas;
    }

    /*
     * get NP cities from location city_id
     * */
    public function setCityNPVal($city_id): string
    {
        $city_id = $this->getUrlNumber($city_id);
        $db = DbSingleton::getTokoDb();

        $r = $db->query("SELECT `CITY_NAME_CLEAR`, `NEWPOST_AREA` FROM `T2_LOCATION` WHERE `CITY_ID` = $city_id LIMIT 1;");
        $city_name  = $db->result($r, 0, "CITY_NAME_CLEAR");
        $state_name = $db->result($r, 0, "NEWPOST_AREA");

        $list = "";
        $r = $db->query("SELECT `CITY_REF`, `CITY_NAME`, `AREA_NAME` FROM `T2_CITY_NOVA` WHERE `CITY_NAME` LIKE \"$city_name%\" AND `AREA_NAME` LIKE \"$state_name%\";");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $ref        = $db->result($r, $i - 1, "CITY_REF");
            $name       = $db->result($r, $i - 1, "CITY_NAME");
            $area_name  = $db->result($r, $i - 1, "AREA_NAME");

            $list .= "
            <option value=\"$ref\">$name ($area_name)</option>";
        }

        return $list;
    }

    /*
     * get NP city department
     * */
    public function setCityDepartments($city_ref, $department_ref = ""): array
    {
        $city_ref = $this->getNameString($city_ref);
        $list_np = $this->getNovaPoshtaWarehousesSelect($city_ref, $department_ref);
        $list_up = "
        <option value=\"0\">{not_chosen}</option>";

        return array($list_np, $list_up);
    }

    /*
     * get NP departments
     * */
    public function getNovaPoshtaWarehousesSelect($ref, $department_ref)
    {
        $list   = $this->replaceLang("<option value=\"0\">{not_chosen}</option>");
        $np     = new NovaPoshtaApi2('e52c020f392e0da179684b87cdbbbf05');
        $arr    = $np->getWarehouses($ref)['data'];

        foreach ($arr as $val) {
            $name       = iconv("UTF-8", "windows-1251", $val["Description"]);
            $war_ref    = $val["Ref"];
            $sel        = ($war_ref === $department_ref) ? "selected" : "";

            $list .= "
            <option value=\"$war_ref\" $sel>$name</option>";
        }

        return $list;
    }


    public function getSearchCityForm()
    {
        $form = $this->getHtmlForm("orders/city_dropdown");
        $form = str_replace(array("{selected_id}", "{selected_name}", "{select_list}"), array(0, "-{not_chosen}-", $this->searchCityMain()), $form);

        return $form;
    }

    public function searchCityMain(): string
    {
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $postfix = "";

        if ($lang_id === 1 || $lang_id === 3) {
            $postfix = "_RU";
        }
        $list = "";

        $r = $db->query("SELECT * FROM `T2_LOCATION` WHERE `STATUS` = 1 ORDER BY `CITY_NAME_CLEAR_RU` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_id        = $db->result($r, $i - 1, "CITY_ID");
            $city_name1     = $db->result($r, $i - 1, "CITY_NAME_CLEAR");
            $city_name2     = $db->result($r, $i - 1, "CITY_NAME_CLEAR_RU");
            $region_name    = $db->result($r, $i - 1, "REGION_NAME");
            $region_name2   = $db->result($r, $i - 1, "REGION_NAME_RU");
            $state_name     = $db->result($r, $i - 1, "STATE_NAME");
            $state_name2    = $db->result($r, $i - 1, "STATE_NAME_RU");

            if ($region_name === "") {
                $city_name  = $db->result($r, $i - 1, "CITY_NAME_CLEAR$postfix");
                $city_fname = "$city_name1 $city_name2";
            } else {
                $city_name  = $db->result($r, $i - 1, "CITY_NAME_CLEAR$postfix") . " ($state_name обл., $region_name р-он)";
                $city_fname = "$city_name1 ($state_name обл., $region_name р-он) - $city_name2 ($state_name2 обл., $region_name2 р-он)";
            }

            $list .= "
            <li class=\"select3-list__item\" data-id=\"$city_id\" data-text=\"$city_fname\" data-name=\"$city_name\" onclick=\"selectCity(this);\">$city_name</li>";
        }

        return $list;
    }

    public function searchCity($text)
    {
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $postfix = "";

        if ($lang_id === 1 || $lang_id === 3) {
            $postfix = "_RU";
        }

        $list = "";
        $text = $this->getNameString($text);

        $r = $db->query("SELECT * FROM `T2_LOCATION` WHERE `CITY_NAME_CLEAR` LIKE \"$text%\" OR `CITY_NAME_CLEAR_RU` LIKE \"$text%\" ORDER BY `STATUS` DESC, `CITY_ID` ASC;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $city_id        = $db->result($r, $i - 1, "CITY_ID");
                $city_name1     = $db->result($r, $i - 1, "CITY_NAME_CLEAR");
                $city_name2     = $db->result($r, $i - 1, "CITY_NAME_CLEAR_RU");
                $region_name    = $db->result($r, $i - 1, "REGION_NAME");
                $region_name2   = $db->result($r, $i - 1, "REGION_NAME_RU");
                $state_name     = $db->result($r, $i - 1, "STATE_NAME");
                $state_name2    = $db->result($r, $i - 1, "STATE_NAME_RU");

                if ($region_name === "") {
                    $city_name  = $db->result($r, $i - 1, "CITY_NAME_CLEAR$postfix");
                    $city_fname = "$city_name1 $city_name2";
                } else {
                    $city_name  = $db->result($r, $i - 1, "CITY_NAME_CLEAR$postfix") . " ($state_name обл., $region_name р-он)";
                    $city_fname = "$city_name1 ($state_name обл., $region_name р-он) - $city_name2 ($state_name2 обл., $region_name2 р-он)";
                }

                $list .= "
                <li class=\"select3-list__item\" data-id=\"$city_id\" data-text=\"$city_fname\" data-name=\"$city_name\" onclick=\"selectCity(this);\">$city_name</li>";
            }
        } else {
            $list = $this->replaceLang("<li class=\"select3-list__item\">-{nothing_found}-</li>");
        }

        return $list;
    }

}
