<?php

use LisDev\Delivery\NovaPoshtaApi2;

class ShopClass extends CatalogueClass
{

    use Helper;
    use Variables;

    /*
     * show basket form
     * */
    public function showBasketForm($cur = null)
    {
        $db = DbSingleton::getTokoDb();
        $exrate = new ExRateClass();
        $client = new ClientClass();
        $showform = new FormClass();

        $disabled = $form = $form = "";
        $location = "stayInOrder();";
        $location_fast = "stayInOrder();";
        $sum_checked = $sum_total = $count_checked = 0;

        $client_id = $this->getClient();
        $where = $client->getClientWhere();

        $cur = $this->getUrlNumber($cur);
        if ($cur == null || $cur == "NaN") {
            $cur = 1;
        }
        setcookie("currency", $cur);
        $_SESSION["currency"] = $cur;

        $r = $db->query("SELECT * FROM `basket` WHERE $where ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            $brow = "";
            $bprow = "";
            $location = "location.href='" . $this->getSiteLink() . "$this->order_link/';";
            $location_fast = "finishFastOrder('input_phone2');";
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $brand_id = $db->result($r, $i - 1, "brand_id");
                $suppl_id = $db->result($r, $i - 1, "suppl_id");
                $amount = $db->result($r, $i - 1, "amount");
                $stock = $db->result($r, $i - 1, "stock");
                $storage_id = $db->result($r, $i - 1, "storage_id");
                $date_create = $db->result($r, $i - 1, "date_create");
                $status = $db->result($r, $i - 1, "status");
                $status_checked = $db->result($r, $i - 1, "status_checked");
                $price = $db->result($r, $i - 1, "price");

                // PRICE
                $price = $exrate->getKoursPrice($price, $cur);
                if ($cur == 1) {
                    $price = $client->getClientPriceRounding($client_id, $price);
                }
                $full_price = $price * $amount;
                if ($cur == 1) {
                    $full_price = $client->getClientPriceRounding($client_id, $full_price);
                }

                $data = compact("art_id", "brand_id", "suppl_id", "amount", "price", "full_price", "stock", "storage_id", "date_create", "status", "status_checked", "cur");
                $brow .= $this->showBasketRows($data);
                $bprow .= $this->showBasketRows($data, 1);
                $sum_total += $full_price;
                if ($status_checked) {
                    $sum_checked += $full_price;
                    $count_checked += 1;
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

        $table_basket = $this->getHtmlForm("basket/basket_form");
        $table_basket = str_replace("{basket_rows}", $brow, $table_basket);
        $table_basket = str_replace("{checked_status}", ($sum_checked == $sum_total) ? "checked=\"checked\"" : "", $table_basket);
        $table_basket = str_replace("{basket_phone_rows}", $bprow, $table_basket);
        $table_basket = str_replace("{sum}", $sum_checked, $table_basket);
        $table_basket = str_replace("{sum_total}", $sum_total, $table_basket);
        $table_basket = str_replace("{count}", $count_checked, $table_basket);
        $table_basket = str_replace("{total_style}", ($sum_checked == $sum_total) ? "d-none" : "", $table_basket);
        $table_basket = str_replace("{location}", $location, $table_basket);
        $mss = $this->replaceLang("{chose_all_in_basket}");
        $table_basket = str_replace("{location_fast}", ($count_checked > 0) ? $location_fast : "alert('$mss');", $table_basket);
        $table_basket = str_replace("{currency}", $showform->getCurrencyForm($cur, 1), $table_basket);
        $table_basket = str_replace("{cur_cap}", $this->getSymbolExrate($cur), $table_basket);
        $table_basket = str_replace("{disabled}", $disabled, $table_basket);
        $table_basket = str_replace("{basket_proposed}", $this->getProposedArts(), $table_basket);
        $table_basket = str_replace("{user_phone}", $client->getClientPhone(), $table_basket);
        $table_basket = str_replace("{validate_class}", ($client->getClientPhone() == "") ? "non_accept fa-times-circle" : "accept fa-check-circle", $table_basket);

        $table_basket = $this->replaceLang($table_basket);

        if ($n == 0) {
            $table_basket = $this->replaceLang($this->getHtmlForm("basket/basket_error"));
        }

        return $table_basket;
    }

    public function showBasketRows($data, $visible = 0)
    {
        $showform = new FormClass();

        $art_id = $data["art_id"];
        $brand_id = $data["brand_id"];
        $suppl_id = $data["suppl_id"];
        $amount = $data["amount"];
        $stock = $data["stock"];
        $storage_id = $data["storage_id"];
        $price = $data["price"];
        $full_price = $data["full_price"];
        $date_create = $data["date_create"];
        $status = $data["status"];
        $status_checked = $data["status_checked"];
        $cur = $data["cur"];
        $article_nr_displ = $this->getArticleDispl($art_id);
        $brand_name = $this->getBrandName($brand_id);

        // DELIVERY
        $tpoint_id = $this->getTpointID();
        if ($suppl_id == 0) {
            $deliveryData = $this->getTpointDeliveryInfo($tpoint_id, $storage_id);
        } else {
            $deliveryData = $this->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
        }

        $flagData = $showform->getCountryFlag($brand_id);

        // FLAGS
        $flag = "";
        $country_name = "";
        if ($flagData != false) {
            $flag = "<img class=\"flag flag-" . $flagData["flag"] . " flag-search\">";
            $country_name = "{brand_manuf}: " . $flagData["country"];
        }

        if (!$visible) {
            $form = $this->getHtmlForm("basket/basket_card");
        } else {
            $form = $this->getHtmlForm("basket/basket_phone_card");
        }
        $form = str_replace("{art_id}", $art_id, $form);
        $form = str_replace("{art_name}", $article_nr_displ, $form);
        $form = str_replace("{brand_id}", $brand_id, $form);
        $form = str_replace("{brand_name}", $brand_name, $form);
        $form = str_replace("{suppl_id}", $suppl_id, $form);
        $form = str_replace("{text}", $this->getArticleName($art_id), $form);
        $form = str_replace("{amount}", $amount, $form);
        $form = str_replace("{price}", $price, $form);
        $form = str_replace("{date1}", date("d.m.y H:i", strtotime($date_create)), $form);
        $form = str_replace("{date2}", date("d.m.y H:i", strtotime(date("Y-m-d H:i:s"))), $form);
        $delivery_info = str_replace('"', "", $deliveryData["info"]);
        $form = str_replace("{delivery_info}", $delivery_info, $form);
        $form = str_replace("{delivery_short_info}", $deliveryData["short"], $form);
        $form = str_replace("{storage_id}", $storage_id, $form);
        $form = str_replace("{stock}", $stock, $form);
        $form = str_replace("{status}", $status, $form);
        $form = str_replace("{status_checked}", $status_checked, $form);
        $form = str_replace("{full_price}", $full_price, $form);
        $form = str_replace("{disabled}", ($this->checkStatusBasket()) ? "" : "disabled", $form);
        $form = str_replace("{checked}", ($status_checked) ? "checked=\"checked\"" : "", $form);
        $form = str_replace("{link}", $this->getSiteLink() . "$this->article_link/" . $this->getFormatAticle($article_nr_displ) . "/" . $this->getBrandLink($brand_id) . "/$art_id/", $form);
        $form = str_replace("{flag}", $flag, $form);
        $form = str_replace("{country_name}", $country_name, $form);
        $form = str_replace("{amount_field}", "count_" . $art_id . "_" . $storage_id, $form);
        $form = str_replace("{action}", $this->getClientAction($art_id, $suppl_id, $storage_id, $amount, $cur), $form);
        $form = str_replace("{cash_abr}", $this->getSymbolExrate($cur), $form);
        $form = str_replace("{product_image}", $this->getBasketArticlePhoto($art_id), $form);
        return $form;
    }

    public function getBasketArticlePhoto($art_id)
    {
        $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT `PHOTO_NAME` FROM `T2_PHOTOS` WHERE `ART_ID` = $art_id AND `ACTIVE` = 1 ORDER BY `MAIN` DESC, `PHOTO_NAME` ASC LIMIT 1;");
        $n = $db->num_rows($r);
        $photo_name = $db->result($r, 0, "PHOTO_NAME");
        $photo_src = "https://toko.ua/uploads/images/catalogue/$photo_name";
        if ($n == 0) {
            $photo_src = "https://toko.ua/$this->noPhoto";
        }
        return $photo_src;
    }

    /*
     * get client action information
     * */
    public function getClientAction($art_id, $suppl_id, $storage_id, $amount, $cur)
    {
        $exrate = new ExRateClass();
        $cur_cap = $this->getSymbolExrate($cur);
        if (!($this->checkActionPrice($art_id))) {
            $action = "";
        } else {
            list(, $action_amount, $action_price) = $this->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, $cur);
            $true_price = ($suppl_id == 0)
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
    public function getBasketArts()
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
                array_push($arts, $art_id);
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
        $where_arts = ($arts != "") ? " AND `ART_ID` NOT IN ($arts)" : "";
        $r = $db->query("SELECT `ART_ID` FROM `T2_ARTICLES_PROPOSED` WHERE `STATUS` = 1 $where_arts;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $list .= $this->getProposedArtsCard($art_id);
        }
        $form = $this->getHtmlForm("orders/proposed");
        $form = str_replace("{proposed_range}", $list, $form);
        $form = $this->replaceLang($form);
        if ($n == 0) {
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
        $article = $showform->getArticleInfo($art_id);
        $article_nr_displ = $article["article_nr_displ"];
        $brand_id = $article["brand_id"];
        $brand_name = $article["brand_name"];
        $article_name = $article["article_name"];
        $price = $article["price"];
        $basket = $article["basket"];
        $currency = $article["currency"];
        $format_name = $this->getFormatAticle($article_nr_displ);
        $brand_link = $this->getBrandLink($brand_id);
        $form = $this->getHtmlForm("orders/proposed_card");
        $form = str_replace("{basket}", $basket, $form);
        $form = str_replace("{article_nr_displ}", $article_nr_displ, $form);
        $form = str_replace("{name}", $article_name, $form);
        $form = str_replace("{brand_name}", $brand_name, $form);
        $form = str_replace("{price}", $price, $form);
        $form = str_replace("{image}", $showform->getArticleActivePhoto($art_id), $form);
        $form = str_replace("{currency}", $currency, $form);
        $form = str_replace("{page_proposed_link}", $this->getSiteLink() . "$this->article_link/$format_name/$brand_link/$art_id/", $form);
        return $form;
    }

    /*
     * set article to basket
     * */
    public function moveToBasket($art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $brand_id = $this->getUrlNumber($brand_id);
        $amount = $this->getUrlNumber($amount);
        $stock = $this->getUrlNumber($stock);
        $storage_id = $this->getUrlNumber($storage_id);
        $suppl_id = $this->getUrlNumber($suppl_id);

        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();
        $showform = new FormClass();
        $user_id = $this->getUser();
        $where = $client->getClientWhere();
        $cookie = $this->getSessionID();
        $date_time = date("Y-m-d H:i:s");
        $old_amount = $status_action = 0;
        $art_name = $this->getArticleDispl($art_id);

        $r = $db->query("SELECT `amount` FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
        $n = $db->num_rows($r);
        $price = $this->getArticlePrice($art_id);
        if ($suppl_id != 0) {
            $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
        }

        if (!($this->checkActionPrice($art_id))) {
        } else {
            list($action_id, $action_amount, $action_price) = $this->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, 1); // to UAH
            if ($amount >= $action_amount) {
                $status_action = $action_id;
                $price = $action_price;
            }
        }

        $tpoint_id = $this->getTpointID();
        list($delivery_days, $delivery_short_info) = $showform->getDeliveryData($tpoint_id, $storage_id, $suppl_id);
        $delivery_short_info = $this->replaceLang($delivery_short_info);

        if ($n > 0) {
            $r2 = $db->query("SELECT `amount` FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
            $cur_stock = $db->result($r2, 0, "amount");
            if ($stock < ($cur_stock + $amount)) {
                $amount = $stock;
            } else {
                $old_amount = intval($db->result($r, 0, "amount"));
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
    public function getBasketArticleAmount($art_id, $storage_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $storage_id = $this->getUrlNumber($storage_id);
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $where = $client->getClientWhere();
        $r = $db->query("SELECT `amount` FROM `basket` WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where LIMIT 1;");
        $n = $db->num_rows($r);
        return ($n > 0) ? $db->result($r, 0, "amount") : 0;
    }

    /*
     * remove item from basket
     * */
    public function deleteFromBasket($art_id, $storage_id)
    {
        $art_id = $this->getUrlNumber($art_id);
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
    public function checkStatusBasket()
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $where = $client->getClientWhere();
        $r = $db->query("SELECT * FROM `basket` WHERE $where AND `status_checked` = 1;");
        $n = $db->num_rows($r);
        return ($n > 0);
    }

    /*
     * check item in basket
     * */
    public function checkBasketItem($art_id, $storage_id, $status)
    {
        $art_id = $this->getUrlNumber($art_id);
        $storage_id = $this->getUrlNumber($storage_id);
        $status = $this->getUrlNumber($status);
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $where = $client->getClientWhere();
        $db->query("UPDATE `basket` SET `status_checked` = $status WHERE `art_id` = $art_id AND `storage_id` = $storage_id AND $where;");
        return true;
    }

    /*
     * update basket form
     * */
    public function updateBasketForm($art_id, $amount, $storage_id)
    {
        $art_id = $this->getUrlNumber($art_id);
        $amount = $this->getUrlNumber($amount);
        $storage_id = $this->getUrlNumber($storage_id);
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $where = $client->getClientWhere();
        $status_action = 0;
        if (!($this->checkActionPrice($art_id))) {
        } else {
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
    public function countBasket()
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $where = $client->getClientWhere();
        $r = $db->query("SELECT COUNT(`id`) as count_basket FROM `basket` WHERE $where;");
        $count = $db->result($r, 0, "count_basket");
        if ($count == 0) {
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
    public function countSummBasket()
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
                $price = floatval($db->result($r, $i - 1, "price"));
                $price = $exrate->getKoursPrice($price, $this->getCurrentExrate());
                $stock = intval($db->result($r, $i - 1, "amount"));
                $sum = $price * $stock;
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
    public function updateOrderBasket()
    {
        $dbt = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();
        $client_id = $this->getClient();
        $where = $client->getClientWhere();
        $cur = $this->getCurrentExrate();
        $bonus_summ = $this->getBonusSumm($client_id);

        if ($bonus_summ > 0) {
            $order_sum = $this->getOrderSummCur();
            $r = $dbt->query("SELECT * FROM `basket` WHERE $where AND `status_checked` = 1;");
            $n = $dbt->num_rows($r);
            if ($n > 0) {
                for ($i = 1; $i <= $n; $i++) {
                    $id = $dbt->result($r, $i - 1, "id") + 0;
                    $price = $dbt->result($r, $i - 1, "price");
                    $price = $exrate->getKoursPrice($price, $cur);
                    if ($cur == 1) {
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
    public function updateBonusClient($discount)
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
        $r = $dbt->query("SELECT * FROM `basket` WHERE $where AND `status_checked` = 1;");
        $n = $dbt->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $dbt->result($r, $i - 1, "id") + 0;
            $art_id = $dbt->result($r, $i - 1, "art_id");
            $brand_id = $dbt->result($r, $i - 1, "brand_id");
            $amount = $dbt->result($r, $i - 1, "amount");
            $price = $dbt->result($r, $i - 1, "price");
            $discount = $dbt->result($r, $i - 1, "discount");
            $suppl_id = $dbt->result($r, $i - 1, "suppl_id");
            $storage_id = $dbt->result($r, $i - 1, "storage_id");
            $status_action = $db->result($r, $i - 1, "status_action");
            $full_price = $price * $amount;
            $sum += $full_price;
            $rmax = $db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;");
            $max = intval($db->result($rmax, 0, "max_order_str")) + 1;
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
        $price = 0; $delivery_id = 0;
        $client = new ClientClass();
        $r = $db->query("SELECT `order_info_id`, `tpoint_id`, `client_id`, `client_user_id` FROM `orders_new` WHERE `ID` = $order_id LIMIT 1;");
        $order_info_id = $db->result($r, 0, "order_info_id") + 0;
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        $client_id = $db->result($r, 0, "client_id");
        $user_id = $db->result($r, 0, "client_user_id");
        $r = $db->query("SELECT `DELIVERY_ID` FROM `ORDERS_CLIENT_INFO` WHERE `ID` = $order_info_id LIMIT 1;");
        $n = $db->num_rows($r);

        if ($n > 0) {
            $delivery_id = $db->result($r, 0, "DELIVERY_ID");
            $db->query("UPDATE `ORDERS_CLIENT_INFO` SET `CLIENT_ID` = $client_id, `USER_ID` = $user_id WHERE `ID` = $order_info_id LIMIT 1;");
        } else {
            $db->query("INSERT INTO `ORDERS_CLIENT_INFO` (`CLIENT_ID`, `USER_ID`, `STATUS`) VALUES ($client_id, $user_id, 1);");
        }

        if (in_array($delivery_id, [4, 5]) && ($client->checkRetailClientCategory($client_id))) {
            list($art_id, $brand_id, $storage_id, $price) = $this->getDeliveryIndex($delivery_id, $tpoint_id);
            $rmax = $db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;");
            $max = intval($db->result($rmax, 0, "max_order_str")) + 1;
            $db->query("INSERT INTO `orders_str_new` (`id`, `order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
            VALUES ('$max', '$order_id', '0', '$storage_id', '$art_id', '$brand_id', '1', $price, '$price', '0');");
        }
        return $price;
    }

    /*
     * GET Delivery index
     * */
    public function getDeliveryIndex($delivery_id, $tpoint_id)
    {
        $client = new ClientClass();
        $art_id = 0;
        $brand_id = 0;
        $storage_id = 0;
        $price = 0;
        if (in_array($delivery_id, [4, 5])) {
            if ($delivery_id == 4) {
                $art_id = 100060075; // NOVA POSHTA
            }
            if ($delivery_id == 5) {
                $art_id = 100060076; // NOVA POSHTA KURER
            }
            $brand_id = $this->getArticleBrand($art_id);
            $storage_id = $client->getDefaultStorageID($tpoint_id);
            $price = $this->getArticlePrice($art_id);
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
    public function getUserSavedData($user_id, $city_id)
    {
        $user_id = $this->getUrlNumber($user_id);
        $city_id = $this->getUrlNumber($city_id);
        $db = DbSingleton::getDbm();
        $client = new ClientClass();
        if ($user_id == 0 || $user_id == "" || $user_id == "undefined") {
            $user_id = $this->getUser();
        }
        $client_id = $client->getClientByUser($user_id);
        $list = "";
        $status = $info_id = $id = 0;
        if ($user_id > 0) {
            $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID` = $client_id AND `USER_ID` = $user_id AND `CITY_ID` = $city_id AND `STATUS` = 1;");
            $n = $db->num_rows($r);
            for ($i = 1; $i <= $n; $i++) {
                $id = $db->result($r, $i - 1, "ID");
                $delivery_id = $db->result($r, $i - 1, "DELIVERY_ID");
                $payment_id = $db->result($r, $i - 1, "PAYMENT_ID");
                $street = $db->result($r, $i - 1, "DEL_STREET");
                $house = $db->result($r, $i - 1, "DEL_HOUSE");
                $porch = $db->result($r, $i - 1, "DEL_PORCH");
                $department_text = $db->result($r, $i - 1, "DEL_DEPARTMENT_TEXT");
                $express = $db->result($r, $i - 1, "DEL_EXPRESS");
                $express_info = $db->result($r, $i - 1, "DEL_EXPRESS_INFO");
                $delivery_text = $this->getDeliveryCaption($delivery_id);
                $payment_text = $this->getPaymentCaption($payment_id);
                $delivery_info = $this->getDeliveryInfoCaption($delivery_id, $street, $house, $porch, $department_text, $express, $express_info);
                if ($delivery_info != "") {
                    $delivery_info = "($delivery_info)";
                }
                $list .= "<li class=\"orders-user__item\">
                    <a onclick=\"setClientOrderInfo('$id');\">$i. $delivery_text $delivery_info <br> $payment_text</a>
                    <a onclick=\"dropClientOrderInfo('$id');\">&times;</a>
                </li>";
            }
            if ($n == 1) {
                $status = 1;
                $info_id = $id;
            }
            if ($n > 0) {
                $list = "<div class=\"orders-user\">
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
        if ($user_status == 0) {
            $form = $this->getHtmlForm("orders/done");
        } else {
            $form = $this->getHtmlForm("orders/done_retail");
        }

        $userData = $client->getClientInfo($client->getClientByUser($user_id), $user_id);
        $form = str_replace("{order_id}", $order_id, $form);
        $form = str_replace("{order_user_id}", $user_id, $form);
        $form = str_replace("{user_phone}", $userData["phone"], $form);
        $form = str_replace("{user_name}", $userData["name"], $form);
        $form = str_replace("{user_email}", $userData["email"], $form);
        $form = str_replace("{user_pass}", $userData["password"], $form);
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
            if ($user_id > 0 && $user_phone != "" && $user_name != "" && $user_city != "") {
                $status = true;
            }
        }
        $form = $this->getHtmlForm("orders/form");
        $form = str_replace("{order_user_id}", $user_id, $form);
        $form = str_replace("{order_delivery}", $this->getOrderDelivery(), $form);
        $form = str_replace("{order_payment}", $this->getOrderPayment(), $form);
        $form = str_replace("{user_city_main_list}", $this->getCitiesMainSelect($user_city), $form);
        $form = str_replace("{user_name}", $user_name, $form);
        $form = str_replace("{user_phone}", $user_phone, $form);
        $form = str_replace("{user_email}", $user_email, $form);
        $form = str_replace("{basket_range}", $this->getBasketOrder(), $form);
        $form = str_replace("{order_user_status}", $status, $form);
        $form = str_replace("{user_name_disable}", ($user_id > 0) ? "disabled" : "", $form);
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
        $form = str_replace("{tpoint_address}", $client->getTpointAddress($client->getTpointUser($this->getClient())), $form);
        $form = str_replace("{express_delivery_list}", $this->getDeliveryExpressList(), $form);
        $r = $db->query("SELECT `ID`, `TEXT`, `TYPE`, `STATUS` FROM `T2_DELIVERY` WHERE 1;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $id = $db->result($r, $i - 1, "ID");
            $text = $db->result($r, $i - 1, "TEXT");
            $type = $db->result($r, $i - 1, "TYPE");
            $status = $db->result($r, $i - 1, "STATUS");
            $display = (!$status) ? "none" : "";
            $free = "";
            if ($type == 1) {
                $free = "({free_cap})";
            }
            if ($type == 2) {
                $free = "({carrier_conditions})";
            }
            $form = str_replace("{delivery_status_$id}", $display, $form);
            $form = str_replace("{delivery_text_$id}", $text, $form);
            $form = str_replace("{delivery_free_$id}", $free, $form);
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
            $id = $db->result($r, $i - 1, "ID");
            $text = $db->result($r, $i - 1, "TEXT");
            $status = $db->result($r, $i - 1, "STATUS");
            $display = (!$status) ? "none" : "";
            $form = str_replace("{payment_status_$id}", $display, $form);
            $form = str_replace("{payment_text_$id}", $text, $form);
        }
        return $form;
    }

    /*
     * GET AJAX Order Delivery Form
     * */
    public function getOrderDeliveryBlock($delivery_id, $city_id)
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
        }
        elseif ($valid_other) {
            $result = 1;
        }
        return $result;
    }

    /*
     * GET AJAX Order Delivery Form
     * */
    public function getOrderPaymentBlock($payment_id, $delivery_id)
    {
        $db = DbSingleton::getDbm();
        $payment_id = $this->getUrlNumber($payment_id);
        $delivery_id = $this->getUrlNumber($delivery_id);
        $result = 0;
        $del_types_1 = [1, 2, 3];
        $del_types_2 = [4, 5, 6];
        $r = $db->query("SELECT `VALID_TYPE` FROM `orders_valid_payment` WHERE `PAYMENT_ID` = $payment_id LIMIT 1;");
        $valid = $db->result($r, 0, "VALID_TYPE");
        if ($valid == 0) {
            $result = 1;
        }
        if ($valid == 1) {
            if (in_array($delivery_id, $del_types_1)) {
                $result = 1;
            }
        }
        if ($valid == 2) {
            if (in_array($delivery_id, $del_types_2)) {
                $result = 1;
            }
        }
        return $result;
    }

    /*
     * SET City Address
     * */
    public function setCityAddress($city_id)
    {
        $city_id = $this->getUrlNumber($city_id);
        $client = new ClientClass();
        $cities = [24861, 10108];
        $city_address = "";
        if (in_array($city_id, $cities)) {
            $tpoint_id = 0;
            if ($city_id == 24861) {
                $tpoint_id = 1;
            }
            if ($city_id == 10108) {
                $tpoint_id = 2;
            }
            $city_name = $this->getCityName($city_id);
            $city_address = $city_name . " - " . $client->getTpointAddress($tpoint_id);
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
            $id = $db->result($r, $i - 1, "ID");
            $text = $db->result($r, $i - 1, "TEXT");
            $list .= "
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
        $name = $this->getNameString($name);
        $phone = $this->getNameString($phone);
        $email = $this->getNameString($email);
        $comment = $this->getNameString($comment);
        $city = $this->getUrlNumber($city);
        $delivery = $this->getUrlNumber($delivery);
        $payment = $this->getUrlNumber($payment);

        $delivery_type_text = "";
        $street = $delivery_type["street"];
        $house = $delivery_type["house"];
        $porch = $delivery_type["porch"];
        $department = $delivery_type["department"];
        $delivery_express = $delivery_type["delivery_express"];
        $delivery_express_department = $delivery_type["delivery_express_department"];

        if ($porch != "") {
            $porch = ", {entrance_cap} $porch";
        }
        if (($street != "undefined") && ($house != "undefined")) {
            $delivery_type_text .= "<div>{address_cap}: {street_cap} $street, {house_cap} $house $porch</div>";
        }
        if ($department != "undefined" && $department != "0") {
            $delivery_type_text .= "<div>{department_cap}: $department</div>";
        }
        if ($delivery_express != "undefined") {
            $delivery_express_text = $this->getDepartmentExpressName($delivery_express);
            $delivery_type_text .= "<div>{delivery_type_7}: $delivery_express_text</div>";
        }
        if ($delivery_express_department != "undefined") {
            $delivery_type_text .= "<div>{department_cap}: $delivery_express_department</div>";
        }

        if ($delivery == 1) {
            $tpoint_address = $this->setCityAddress($city);
            $delivery_type_text = "<div>$tpoint_address</div>";
        }

        $form = $this->getHtmlForm("orders/confirm");
        $form = str_replace("{order_name}", $name, $form);
        $form = str_replace("{order_phone}", $phone, $form);
        $form = str_replace("{order_city}", $this->getCityName($city), $form);
        $form = str_replace("{order_delivery}", $this->getDeliveryName($delivery), $form);
        $form = str_replace("{order_delivery_type}", $delivery_type_text, $form);
        $form = str_replace("{order_payment}", $this->getPaymentName($payment), $form);
        $form = str_replace("{order_email}", ($email == "") ? "{absent_cap}" : $email, $form);
        $form = str_replace("{order_comment}", ($comment == "") ? "{absent_cap}" : $comment, $form);
        $form = $this->replaceLang($form);

        return $form;
    }

    public function saveFastOrderBasket($phone, $art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id)
    {
        $basket_amount = $this->getBasketArticleAmount($art_id, $storage_id);
        if ($basket_amount == 0) {
            $this->moveToBasket($art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id);
        }
        $link = $this->saveFastOrder($phone);
        return $link;
    }

    /*
     * finish Fast Order
     * */
    public function saveFastOrder($phone)
    {
        $client = new ClientClass();
        $phone = $client->formatValidPhone($phone);
        list(, $user_id) = $client->getAuthorizedUser($phone);
        $client_id = $client->getClientByUser($user_id);
        $user_status = 0;
        // CREATE CLIENT
        if ($user_id == 0) {
            $clientData = $client->addRetailClient($this->getClient(), $phone);
            $client_id = $clientData["client_id"];
            $user_id = $clientData["user_id"];
            $user_status = 1;
        }
        $tpoint_id = $this->getTpointID();
        $cookie = $this->getSessionID();
        $cash_id = intval($client->getClientCurrency($client_id));

        // CREATE ORDER
        $order_id = $this->saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, "", "", $phone, 0, "", 0, 0);

        return $this->getSiteLink() . "order/?order_id=$order_id&user_id=$user_id&user_status=$user_status/";
    }

    public function addFastOrder($phone, $art_id, $brand_id, $suppl_id, $storage_id, $amount)
    {
        $client = new ClientClass();
        $phone = $client->formatValidPhone($phone);
        list(, $user_id) = $client->getAuthorizedUser($phone);
        $client_id = $client->getClientByUser($user_id);
        $user_status = 0;
        // CREATE CLIENT
        if ($user_id == 0) {
            $clientData = $client->addRetailClient($this->getClient(), $phone);
            $client_id = $clientData["client_id"];
            $user_id = $clientData["user_id"];
            $user_status = 1;
        }
        $tpoint_id = $this->getTpointID();
        $cookie = $this->getSessionID();
        $cash_id = intval($client->getClientCurrency($client_id));

        $db = DbSingleton::getDbm();
        $exrate = new ExrateClass();
        $status_action = 0;

        $r = $db->query("SELECT MAX(`ID`) as maxim FROM `orders_new`;");
        $order_id = intval($db->result($r, 0, "maxim")) + 1;

        $price = $this->getArticlePrice($art_id);
        if ($suppl_id != 0) {
            $price = $this->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
        }

        if (!($this->checkActionPrice($art_id))) {
        } else {
            list($action_id, $action_amount, $action_price) = $this->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, 1); // to UAH
            if ($amount >= $action_amount) {
                $status_action = $action_id;
                $price = $action_price;
            }
        }

        $summ = $price * $amount;

        $db->query("INSERT INTO `orders_new` 
            (`id`, `client_id`, `client_user_id`, `cookie_id`, `tpoint_id`, `cash_id`, `name`, `email`, `phone`, `region`, `comment`, `order_info_id`, `price_summ`) 
        VALUES 
            ($order_id, $client_id, $user_id, '$cookie', $tpoint_id, $cash_id, '', '', '$phone', '', '', 0, '$summ');");

        $db->query("INSERT INTO `orders_str_new` 
            (`order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
        VALUES 
            ($order_id, $suppl_id, $storage_id, $art_id, $brand_id, '$amount', '$price', '$summ', $status_action);");

        return $this->getSiteLink() . "order/?order_id=$order_id&user_id=$user_id&user_status=$user_status/";
    }

    /*
     * save order form
     * */
    public function saveOrder($user_id, $name, $phone, $city_id, $delivery_id, $delivery_type, $payment_id, $email, $comment, $recipient_name, $recipient_phone, $bonus_status = 0)
    {
        $client = new ClientClass();

        $phone = $client->formatValidPhone($phone);
        $name = $this->getUrlString($name);
        $email = $this->getUrlString($email);
        $comment = $this->getUrlString($comment);
        $recipient_name = $this->getUrlString($recipient_name);
        $recipient_phone = $client->formatValidPhone($recipient_phone);

        if ($user_id == 0 || $user_id == "" || $user_id == "undefined") {
            $user_id = $this->getUser();
            $client_id = $this->getClient();
        } else {
            $client_id = $client->getClientByUser($user_id);
        }
        $tpoint_id = $this->getTpointID();
        $cookie = $this->getSessionID();
        $cash_id = intval($client->getClientCurrency($client_id));
        $user_status = 0;

        $street = $delivery_type["street"];
        $house = $delivery_type["house"];
        $porch = $delivery_type["porch"];
        $department_id = $delivery_type["department_id"];
        $department_text = $delivery_type["department"];
        $delivery_express = $delivery_type["delivery_express"];
        $delivery_express_department = $delivery_type["delivery_express_department"];
        $delivery_info = ["street" => $street, "house" => $house, "porch" => $porch, "department" => $department_id, "express" => $delivery_express, "express_info" => $delivery_express_department];

        // CREATE CLIENT
        if ($user_id == 0) {
            $tpoint_client_id = $client_id;
            $clientData = $client->addRetailClient($tpoint_client_id, $phone, $name, $city_id, $email);
            $client_id = $clientData["client_id"];
            $user_id = $clientData["user_id"];
            $user_status = 1;
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
    public function saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, $name, $email, $phone, $city_id, $comment, $order_info_id, $bonus_status)
    {
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT MAX(`ID`) as maxim FROM `orders_new`;");
        $order_id = intval($db->result($r, 0, "maxim")) + 1;
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
    public function saveOrderClient($user_id, $name, $email, $pass)
    {
        $user_id = $this->getUrlNumber($user_id);
        $name = $this->getNameString($name);
        $email = $this->getNameString($email);
        $pass = $this->getNameString($pass);
        $db = DbSingleton::getDbm();
        $client = new ClientClass();
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
        $street = $delivery_info["street"];
        $house = $delivery_info["house"];
        $porch = $delivery_info["porch"];
        $department = $delivery_info["department"];
        $express = $delivery_info["express"];
        $express_info = $delivery_info["express_info"];

        if ($street == "undefined") $street = "";
        if ($house == "undefined") $house = "";
        if ($porch == "undefined") $porch = "";
        if ($express_info == "undefined") $express_info = "";

        $r = $db->query("SELECT `ID` FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID` = $client_id AND `USER_ID` = $user_id AND `CITY_ID` = $city_id AND `DELIVERY_ID` = $delivery_id AND `PAYMENT_ID` = $payment_id AND `DEL_STREET` = '$street' AND `DEL_HOUSE` = '$house' AND `DEL_PORCH` = '$porch' AND `DEL_DEPARTMENT` = '$department' AND `DEL_EXPRESS` = $express AND `DEL_EXPRESS_INFO` = '$express_info' LIMIT 1;");
        $n = $db->num_rows($r);
        if ($n == 0) {
            $r = $db->query("SELECT MAX(`ID`) as maxim FROM `ORDERS_CLIENT_INFO`;");
            $order_info_id = intval($db->result($r, 0, "maxim")) + 1;
            $db->query("INSERT INTO `ORDERS_CLIENT_INFO` (`ID`, `CLIENT_ID`, `USER_ID`, `CITY_ID`, `DELIVERY_ID`, `PAYMENT_ID`, `DEL_NAME`, `DEL_PHONE`, `DEL_STREET`, `DEL_HOUSE`, `DEL_PORCH`, `DEL_DEPARTMENT`, `DEL_DEPARTMENT_TEXT`, `DEL_EXPRESS`, `DEL_EXPRESS_INFO`) 
            VALUES ($order_info_id, $client_id, $user_id, $city_id, $delivery_id, $payment_id, '$recipient_name', '$recipient_phone', '$street', '$house', '$porch', '$department', '$department_text', $express, '$express_info');");
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
                if ($porch != "") {
                    $porch = ", {entrance_cap} $porch";
                }
                $info = "{address_cap}: {street_cap} $street, {house_cap} $house $porch";
                break;
            }
            case 4:
            case 6:
            {
                $info = "$department_text";
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
    public function dropClientOrderInfo($id)
    {
        $id = $this->getUrlNumber($id);
        $db = DbSingleton::getDbm();
        $db->query("UPDATE `ORDERS_CLIENT_INFO` SET `STATUS` = 0 WHERE `ID` = $id;");
        return true;
    }

    /*
     * add client order info
     * */
    public function setClientOrderInfo($id)
    {
        $id = $this->getUrlNumber($id);
        $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `ID` = $id AND `STATUS` = 1;");
        $city_id = $db->result($r, 0, "CITY_ID");
        $delivery_id = $db->result($r, 0, "DELIVERY_ID");
        $payment_id = $db->result($r, 0, "PAYMENT_ID");
        $street = $db->result($r, 0, "DEL_STREET");
        $house = $db->result($r, 0, "DEL_HOUSE");
        $porch = $db->result($r, 0, "DEL_PORCH");
        $department = $db->result($r, 0, "DEL_DEPARTMENT");
        $express = $db->result($r, 0, "DEL_EXPRESS");
        $express_info = $db->result($r, 0, "DEL_EXPRESS_INFO");
        $recipient_name = $db->result($r, 0, "DEL_NAME");
        $recipient_phone = $db->result($r, 0, "DEL_PHONE");
        $delivery_info = compact("street", "house", "porch", "department", "express", "express_info");
        return
            array(
                "city_id" => $city_id,
                "delivery_id" => $delivery_id,
                "payment_id" => $payment_id,
                "delivery_info" => $delivery_info,
                "recipient_name" => $recipient_name,
                "recipient_phone" => $recipient_phone
            );
    }

    /*
     * delivery fields validation
     * */
    public function validDeliveryFields($delivery, $delivery_type)
    {
        $delivery = $this->getUrlNumber($delivery);
        $result = true;
        $fields = [];
        $street = $delivery_type["street"];
        $house = $delivery_type["house"];
        $department = $delivery_type["department"];
        $department_id = $delivery_type["department_id"]; // department ID
        $delivery_express = $delivery_type["delivery_express"]; // express ID
        $delivery_express_department = $delivery_type["delivery_express_department"];
        switch ($delivery) {
            case 4:
            {
                if ($department_id == "0" || $department_id == "undefined") {
                    if ($department_id == "0" || $department_id == "undefined") {
                        array_push($fields, "department");
                    }
                    $result = false;
                }
                break;
            }
            case 2:
            case 3:
            case 5:
            {
                if (($street == "" || $street == "undefined") || ($house == "" || $house == "undefined")) {
                    if ($street == "" || $street == "undefined") {
                        array_push($fields, "street");
                    }
                    if ($house == "" || $house == "undefined") {
                        array_push($fields, "house");
                    }
                    $result = false;
                }
                break;
            }
            case 6:
            {
                if ($department == "0" || $department == "undefined") {
                    if ($department == "0" || $department == "undefined") {
                        array_push($fields, "department");
                    }
                    $result = false;
                }
                break;
            }
            case 7:
            {
                if (($delivery_express_department == "" || $delivery_express_department == "undefined") || ($delivery_express == "0" || $delivery_express == "undefined")) {
                    if ($delivery_express_department == "" || $delivery_express_department == "undefined") {
                        array_push($fields, "delivery_express_department");
                    }
                    if ($delivery_express == "0" || $delivery_express == "undefined") {
                        array_push($fields, "delivery_express");
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
    public function getOrderTotal($total)
    {
        $cur = $this->getCurrentExrate();
        $cur_cap = $this->getSymbolExrate($cur);
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
        $delivery_id = $this->getUrlNumber($delivery_id);
        $bonus_status = $this->getUrlNumber($bonus_status);

        $cur = $this->getCurrentExrate();
        $cur_cap = $this->getSymbolExrate($cur);

        $bonus_summ = $this->getBonusSumm($this->getClient());
        list($basket_range, $basket_total, $bonus_total) = $this->getBasketOrderRange($bonus_status, $bonus_summ);

        list($delivery_total, $delivery_total_text) = $this->getDeliveryPrice($delivery_id);

        $basket_total_full = $basket_total;
        $basket_total = $basket_total - $bonus_total;

        $basket_total_cap = $basket_total . " $cur_cap";
        if ($bonus_total > 0) {
            $basket_total_cap = "
            <span class=\"span-red\" style=\"text-decoration: line-through\">
                $basket_total_full $cur_cap
            </span><br>" . $basket_total_cap;
        }

        $total = $basket_total + $delivery_total;

        $form = $this->getHtmlForm("orders/basket");
        if ($delivery_id == 0) {
            $form = str_replace("{basket_order_delivery_price}", "", $form);
            $form = str_replace("{basket_order_price}", "", $form);
            $form = str_replace("{basket_button_status}", "none", $form);
        }
        $form = str_replace("{basket_content}", $basket_range, $form);
        $form = str_replace("{basket_full_price}", $basket_total_cap, $form);
        $form = str_replace("{basket_order_delivery_price}", $delivery_total_text, $form);
        $form = str_replace("{basket_order_price}", $this->getOrderTotal($total), $form);
        $form = str_replace("{basket_button_status}", "", $form);
        $form = str_replace("{basket_client_bonus}", ($bonus_summ > 0) ? $this->showClientBonusOrder($bonus_status, $bonus_total) : "", $form);
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
        $bonus_summ = $this->getBonusSumm($this->getClient());
        $checked = ($bonus_status) ? "checked='checked'" : "";
        $bonus_checked = ($bonus_status) ? "- $bonus_total {uah_cap}" : "";
        $form = $this->getHtmlForm("bonus/status");
        $form = str_replace("{checked}", $checked, $form);
        $form = str_replace("{bonus_summ}", $bonus_summ, $form);
        $form = str_replace("{bonus_checked}", $bonus_checked, $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    /*
     * get delivery price
     * */
    public function getDeliveryPrice($delivery_id)
    {
        $exrate = new ExRateClass();
        $client = new ClientClass();
        $cur = $this->getCurrentExrate();
        $cur_cap = $this->getSymbolExrate($cur);
        $price = $price_cur = 0;

        // NOVA POSHTA
        if ($delivery_id == 4) {
            $price = $this->getArticlePrice(100060075); // NP
        }
        if ($delivery_id == 5) {
            $price = $this->getArticlePrice(100060076); // NP KURER
        }

        if ($price > 0) {
            $price_cur = $exrate->getKoursFromUAH($price, $cur);
            $price_cur = $client->getClientPriceRounding($this->getClient(), $price_cur);
        }

        if ($price_cur > 0) {
            $del_cap = "$price_cur $cur_cap";
        } else {
            $del_cap = "{free_cap}";
        }

        // carrier tariff
        if (in_array($delivery_id, [6, 7])) {
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
    public function getBonusDiscount($order_sum, $bonus_summ, $price)
    {
        // 10% procent fixed
        $procent = 10;
        // max promegut
        $max_prom = $order_sum * ($procent / 100);
        // max vosmojnoe
        $max_discount = ($max_prom <= $bonus_summ) ? $max_prom : $bonus_summ;
        // discount procent
        $price_procent = round($price / $order_sum * 100);
        // discount price
        $discount = floor($price_procent * $max_discount / 100);
        // price with discount
        $price_discount = ceil($price - $discount);
        // real discount procent
        $real_discount = round((($price_discount / $price) - 1) * 100, 2);

        return array("discount" => $discount, "price_discount" => $price_discount, "real_discount" => $real_discount);
    }

    public function getOrderSummCur()
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();
        $client_id = $this->getClient();
        $cur = $this->getCurrentExrate();
        $where = $client->getClientWhere();
        $order_sum = 0;
        $r = $db->query("SELECT `amount`, `price` FROM `basket` WHERE $where AND `status_checked` = 1 ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $amount = $db->result($r, $i - 1, "amount");
                $price = $db->result($r, $i - 1, "price");
                $price = $exrate->getKoursPrice($price, $cur);
                if ($cur == 1) {
                    $price = $client->getClientPriceRounding($client_id, $price);
                }
                $full_price = $price * $amount;
                $order_sum += $full_price;
            }
        }
        return $order_sum;
    }

    /*
     * get basket order form
     * */
    public function getBasketOrderRange($bonus_status, $bonus_summ)
    {
        $db = DbSingleton::getTokoDb();
        $client = new ClientClass();
        $exrate = new ExRateClass();
        $showform = new FormClass();
        $client_id = $this->getClient();
        $where = $client->getClientWhere();
        $cur = $this->getCurrentExrate();
        $cur_cap = $this->getSymbolExrate($cur);
        $list = "";
        $sum_total = $bonus_total = $order_sum = 0;
        $order_sum = $this->getOrderSummCur();
        $r = $db->query("SELECT * FROM `basket` WHERE $where AND `status_checked` = 1 ORDER BY `date_create` DESC;");
        $n = $db->num_rows($r);
        if ($n > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $brand_id = $db->result($r, $i - 1, "brand_id");
                $price = $db->result($r, $i - 1, "price");
                $amount = $db->result($r, $i - 1, "amount");
                $article_nr_displ = $this->getArticleDispl($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $article_name = $this->getArticleName($art_id);
                $price = $exrate->getKoursPrice($price, $cur);
                if ($cur == 1) {
                    $price = $client->getClientPriceRounding($client_id, $price);
                }
                $full_price = $price * $amount;
                if ($cur == 1) {
                    $full_price = $client->getClientPriceRounding($client_id, $full_price);
                }
                $sum_total += $full_price;
                $name = "$article_name $brand_name ($article_nr_displ)";
                $img = $showform->getArticleActivePhoto($art_id);
                $price_cap = "$full_price $cur_cap";
                if ($bonus_status) {
                    $discountData = $this->getBonusDiscount($order_sum, $bonus_summ, $full_price);
                    $discount = $discountData["discount"];
                    $price_discount = $discountData["price_discount"];
                    $real_discount = $discountData["real_discount"];
                    $price_cap = "<span>$full_price $cur_cap</span>";
                    $bonus_total += $discount;
                    if ($full_price != $price_discount) {
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
    public function setDeliveryExpressDepartment($delivery_express)
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
    public function getCitiesMainSelect($user_city = 0)
    {
        $user_city = $this->getUrlNumber($user_city);
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $postfix = $where_user_city = $list = "";
        if ($lang_id == 1 || $lang_id == 3) {
            $postfix = "_RU";
        }
        if ($user_city > 0) {
            $where_user_city = "OR `CITY_ID` = $user_city";
        }
        $r = $db->query("SELECT `CITY_ID`, `CITY_NAME_CLEAR$postfix` FROM `T2_LOCATION` WHERE `REGION_NAME` = '' $where_user_city ORDER BY `CITY_NAME$postfix` ASC;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_id = $db->result($r, $i - 1, "CITY_ID");
            $city_name = $db->result($r, $i - 1, "CITY_NAME_CLEAR$postfix");
            $sel = ($user_city > 0 && $user_city == $city_id) ? "selected" : "";
            $list .= "
            <option value=\"$city_id\" $sel>$city_name</option>";
        }
        return $list;
    }

    /*
     * get location cities by text input
     * */
    public function getCityVal($search_text)
    {
        $search_text = $this->getNameString($search_text);
        $db = DbSingleton::getTokoDb();
        $lang_id = $this->getLanguage();
        $mas = [];
        $postfix = "";
        if ($lang_id == 1 || $lang_id == 3) {
            $postfix = "_RU";
        }
        $r = $db->query("SELECT * FROM `T2_LOCATION` WHERE `CITY_NAME_CLEAR$postfix` LIKE \"$search_text%\" ORDER BY `CITY_NAME$postfix`;");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $city_id = $db->result($r, $i - 1, "CITY_ID");
            $city_name = $db->result($r, $i - 1, "CITY_NAME$postfix");
            $region_name = $db->result($r, $i - 1, "REGION_NAME$postfix");
            $state_name = $db->result($r, $i - 1, "STATE_NAME$postfix");
            $city_cap = "$city_name ($state_name обл., $region_name р-он)";
            $mas[$i] = ["id" => $city_id, "value" => $city_cap];
        }
        return $mas;
    }

    /*
     * get NP cities from location city_id
     * */
    public function setCityNPVal($city_id)
    {
        $city_id = $this->getUrlNumber($city_id);
        $db = DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT `CITY_NAME_CLEAR`, `NEWPOST_AREA` FROM `T2_LOCATION` WHERE `CITY_ID` = $city_id LIMIT 1;");
        $city_name = $db->result($r, 0, "CITY_NAME_CLEAR");
        $state_name = $db->result($r, 0, "NEWPOST_AREA");
        $r = $db->query("SELECT `CITY_REF`, `CITY_NAME`, `AREA_NAME` FROM `T2_CITY_NOVA` WHERE `CITY_NAME` LIKE \"$city_name%\" AND `AREA_NAME` LIKE \"$state_name%\";");
        $n = $db->num_rows($r);
        for ($i = 1; $i <= $n; $i++) {
            $ref = $db->result($r, $i - 1, "CITY_REF");
            $name = $db->result($r, $i - 1, "CITY_NAME");
            $area_name = $db->result($r, $i - 1, "AREA_NAME");
            $list .= "
            <option value=\"$ref\">$name ($area_name)</option>";
        }
        return $list;
    }

    /*
     * get NP city department
     * */
    public function setCityDepartments($city_ref, $department_ref = "")
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
        $list = "<option value=\"0\">{not_chosen}</option>";
        $list = $this->replaceLang($list);
        $np = new NovaPoshtaApi2('4a18892255b3c9a8e7ef4813c790e75f');
        $arr = $np->getWarehouses($ref)['data'];
        foreach ($arr as $val) {
            $name = iconv("UTF-8", "windows-1251", $val["Description"]);
            $war_ref = $val["Ref"];
            if ($war_ref == $department_ref) {
                $sel = "selected";
            } else {
                $sel = "";
            }
            $list .= "
            <option value=\"$war_ref\" $sel>$name</option>";
        }
        return $list;
    }

}
