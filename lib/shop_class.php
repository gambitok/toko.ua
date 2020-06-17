<?php

class ShopClass {

    use Helper;
    use Variables;

    function showBasketForm($cur=null) { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $showform=new FormClass; $exrate=new ExRateClass; $cat=new CatalogueClass; $language=new LangClass;
        $sum=0; $sum_total=0; $disabled=$brow=$bprow=""; $location="stayInOrder();"; $location_fast="stayInOrder();";
        if ($cur==null || $cur=="NaN") $cur=1;
        $cur_cap=$exrate->getKoursSymbol($cur); $t_point=$client->getTpoint(); $where=$client->getClientWhere(); $client_id=$client->getClient()[0];
        setcookie("currency", $cur); $_SESSION["currency"]=$cur; $count=$count_total=0;
        $r=$db->query("SELECT * FROM `basket` WHERE $where ORDER BY `date_create` DESC;"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $brand_id = $db->result($r, $i - 1, "brand_id");
                $art_name=$this->getArticleDispl($art_id);
                $brand_name=$this->getBrandName($brand_id);
                $text=$this->getArticleName($art_id);
                $suppl_id = $db->result($r, $i - 1, "suppl_id");
                $amount = $db->result($r, $i - 1, "amount");
                $price = $db->result($r, $i - 1, "price");
                $date1 = $db->result($r, $i - 1, "date_create");
                $date2 = date("Y-m-d H:i:s");
                $storage_id = $db->result($r, $i - 1, "storage_id");
                $stock = $db->result($r, $i - 1, "stock");
                list($delivery_info,, $delivery_short_info) = $cat->getTpointDeliveryInfo($t_point,$storage_id);
                if ($suppl_id!=0) list($delivery_info,, $delivery_short_info) = $cat->getTpointSupplDeliveryInfo($t_point,$suppl_id,$storage_id);
                $status = $db->result($r, $i - 1, "status");
                $status_checked = $db->result($r, $i - 1, "status_checked");

                $price = $exrate->getKoursPrice($price, $cur);

                if ($cur==1) $price = $client->getClientPriceRounding($client_id, $price);
                $full_price = $price * $amount;
                if ($cur==1) $full_price = $client->getClientPriceRounding($client_id, $full_price);

                if ($status_checked) $sum+=$full_price; $sum_total+=$full_price;
                if ($status_checked) $count+=$amount; $count_total+=$amount;

                if (!($cat->checkActionPrice($art_id))) $action=""; else {
                    list(,$action_amount,$action_price)=$cat->checkActionPrice($art_id);
                    $action_price = $exrate->getKoursFromUSA($action_price, $cur);
                    if ($suppl_id==0) $true_price=$cat->getArticlePrice($art_id); else $true_price=$cat->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
                    $true_price = round($exrate->getKoursPrice($true_price, $cur),2);
                    if ($amount>=$action_amount) {
                        $true_cap="<br><span class=\"span-outline\">$true_price $cur_cap</span>";
                        $true_clr="";
                    } else {
                        $true_cap="";
                        $true_clr="color:lightcoral!important;";
                    }
                    $action="$true_cap
                    <br>
                    <span style=\"font-size:1.5em; $true_clr\" class=\"span-green tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" 
                        title=\"{price_cap} $action_price $cur_cap, {from_cap} $action_amount {amount_abbr}.\">
                        <i class=\"fa fa-box-open\"></i>
                    </span>";
                }

                $format_date1 = date("d.m.y H:i", strtotime($date1));
                $format_date2 = date("d.m.y H:i", strtotime($date2));
                $format_name=$this->getFormatAticle($art_name);
                $format_brand=$this->getFormatAticle($brand_name);

                $prefix=$language->getLangPrefix();
                $link="https://toko.ua$prefix/article/$format_name/$format_brand/$art_id/";
                $amount_field="count_".$art_id."_".$storage_id;

                $location="location.href='https://toko.ua$prefix/order/';";
                $location_fast="finishFastOrder('input_phone2');";

                if ($showform->getCountryFlag($brand_id)!=false) {
                    list($flag,$country_name)=$showform->getCountryFlag($brand_id);
                    $flag="<img class=\"flag flag-$flag flag-search\">";
                    $country_name="{brand_manuf}: $country_name";
                } else {
                    $flag=""; $country_name="";
                }

                if ($status_checked) $checked="checked=\"checked\""; else $checked="";
                if ($this->checkStatusBasket()) $disabled=""; else $disabled="disabled";

                $brow.=$this->getHtmlForm("basket/basket_card");
                $brow=str_replace("{art_id}", $art_id, $brow);
                $brow=str_replace("{art_name}", $art_name, $brow);
                $brow=str_replace("{brand_id}", $brand_id, $brow);
                $brow=str_replace("{brand_name}", $brand_name, $brow);
                $brow=str_replace("{suppl_id}", $suppl_id, $brow);
                $brow=str_replace("{text}", $text, $brow);
                $brow=str_replace("{amount}", $amount, $brow);
                $brow=str_replace("{price}", $price, $brow);
                $brow=str_replace("{date1}", $format_date1, $brow);
                $brow=str_replace("{date2}", $format_date2, $brow);
                $brow=str_replace("{delivery_info}", $delivery_info, $brow);
                $brow=str_replace("{delivery_short_info}", $delivery_short_info, $brow);
                $brow=str_replace("{storage_id}", $storage_id, $brow);
                $brow=str_replace("{stock}", $stock, $brow);
                $brow=str_replace("{status}", $status, $brow);
                $brow=str_replace("{status_checked}", $status_checked, $brow);
                $brow=str_replace("{full_price}", $full_price, $brow);
                $brow=str_replace("{disabled}", $disabled, $brow);
                $brow=str_replace("{checked}", $checked, $brow);
                $brow=str_replace("{link}", $link, $brow);
                $brow=str_replace("{flag}", $flag, $brow);
                $brow=str_replace("{country_name}", $country_name, $brow);
                $brow=str_replace("{amount_field}", $amount_field, $brow);
                $brow=str_replace("{action}", $action, $brow);
                $brow=str_replace("{product_image}", $cat->getBasketArticlePhoto($art_id), $brow);
                $brow=str_replace("{cash_abr}", $cur_cap, $brow);

                $bprow.=$this->getHtmlForm("basket/basket_phone_card");
                $bprow=str_replace("{art_id}", $art_id, $bprow);
                $bprow=str_replace("{art_name}", $art_name, $bprow);
                $bprow=str_replace("{brand_id}", $brand_id, $bprow);
                $bprow=str_replace("{brand_name}", $brand_name, $bprow);
                $bprow=str_replace("{suppl_id}", $suppl_id, $bprow);
                $bprow=str_replace("{text}", $text, $bprow);
                $bprow=str_replace("{amount}", $amount, $bprow);
                $bprow=str_replace("{price}", $price, $bprow);
                $bprow=str_replace("{date1}", $format_date1, $bprow);
                $bprow=str_replace("{date2}", $format_date2, $bprow);
                $bprow=str_replace("{delivery_info}", $delivery_info, $bprow);
                $bprow=str_replace("{delivery_short_info}", $delivery_short_info, $bprow);
                $bprow=str_replace("{storage_id}", $storage_id, $bprow);
                $bprow=str_replace("{stock}", $stock, $bprow);
                $bprow=str_replace("{status}", $status, $bprow);
                $bprow=str_replace("{status_checked}", $status_checked, $bprow);
                $bprow=str_replace("{full_price}", $full_price, $bprow);
                $bprow=str_replace("{disabled}", $disabled, $bprow);
                $bprow=str_replace("{checked}", $checked, $bprow);
                $bprow=str_replace("{link}", $link, $bprow);
                $bprow=str_replace("{flag}", $flag, $bprow);
                $bprow=str_replace("{country_name}", $country_name, $bprow);
                $bprow=str_replace("{amount_field}", $amount_field, $bprow);
                $bprow=str_replace("{action}", $action, $bprow);
                $bprow=str_replace("{cash_abr}", $cur_cap, $bprow);
            }
        } else {
            $brow="<div class=\"row align-items-center\"><div class=\"col-12\"><p class=\"text-center mar0\"><br>{basket_empty}</p><br></div></div>";
            $bprow="";
        }

        $table_basket=$this->getHtmlForm("basket/basket_form");
        $table_basket=str_replace("{basket_rows}", $brow, $table_basket);
        $table_basket=str_replace("{checked_status}", $sum==$sum_total ? "checked=\"checked\"" : "", $table_basket);
        $table_basket=str_replace("{basket_phone_rows}", $bprow, $table_basket);
        $table_basket=str_replace("{sum}", $sum, $table_basket);
        $table_basket=str_replace("{sum_total}", $sum_total, $table_basket);
        $table_basket=str_replace("{count}", $count, $table_basket);
        $table_basket=str_replace("{count_total}", $count_total, $table_basket);
        $table_basket=str_replace("{total_style}", $sum==$sum_total ? "d-none" : "", $table_basket);
        $table_basket=str_replace("{location}", $location, $table_basket);
        $table_basket=str_replace("{location_fast}", $location_fast, $table_basket);
        $table_basket=str_replace("{currency}", $showform->getCurrencyForm(4,0,$cur), $table_basket);
        $table_basket=str_replace("{cur_cap}", $exrate->getKoursSymbol($cur), $table_basket);
        $table_basket=str_replace("{disabled}", $disabled, $table_basket);

        $table_basket=$this->replaceLang($table_basket);
        return $table_basket;
    }

    function showMiniBasketForm() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $exrate=new ExRateClass; $showform=new FormClass; $language=new LangClass;
        $client_id=$client->getClient()[0]; $cur=$client->getClientCurrency($client_id);
        $where=$client->getClientWhere();
        $bempty="<div id=\"basket_block\" class=\"content\">{basket_empty}</div>"; $sum=0;
        $form=$this->getHtmlForm("basket/basket");
        $r=$db->query("SELECT * FROM `basket` WHERE $where AND `status_checked`=1;"); $n=$db->num_rows($r);
        if ($n>0) {
            $bcontent="<div><div class=\"row align-items-center basket-table-tbody\">
                <div class=\"col-2 col-lg-2\" title=\"{art_brand_name_cap}\">{caption_cap}</div>
                <div class=\"col-2 col-lg-2\">&nbsp</div>
                <div class=\"col-4 col-lg-4\">&nbsp</div>
                <div class=\"col-1 col-lg-1\">{del_time}</div>
                <div class=\"col-1 col-lg-1\">{count_cap}</div>
                <div class=\"col-1 col-lg-1\" title=\"{price_per_cap}\">{price_cap}</div>
                <div class=\"col-1 col-lg-1\">{total_cap}</div>
            </div>";
            for ($i=1;$i<=$n;$i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $brand_id = $db->result($r, $i - 1, "brand_id");
                $art_name=$this->getArticleDispl($art_id);
                $brand_name=$this->getBrandName($brand_id);
                $text=$this->getArticleName($art_id);
                $amount = $db->result($r, $i - 1, "amount");
                $price = $db->result($r, $i - 1, "price");
                $dd = $db->result($r, $i - 1, "delivery");
                $del = $db->result($r, $i - 1, "delivery_info");
                $status_checked = $db->result($r, $i - 1, "status_checked");
                if(!$client->checkUnRegClient()) {
                    $price=$exrate->getKoursPrice($price,$cur);
                }
                $full_price = $price * $amount;

                if ($cur==1) $price = $client->getClientPriceRounding($client_id, $price);
                if ($cur==1) $full_price = $client->getClientPriceRounding($client_id, $full_price);

                if ($status_checked) $sum+=$full_price;
                $format_name=$this->getFormatAticle($art_name);
                $format_brand=$this->getFormatBrand($brand_name);

                $prefix=$language->getLangPrefix();
                $link="https://toko.ua$prefix/article/$format_name/$format_brand/$art_id/";

                if ($showform->getCountryFlag($brand_id)!=false) {
                    list($flag,$country_name) = $showform->getCountryFlag($brand_id);
                    $flag="<img class=\"flag flag-$flag flag-search\">";
                    $country_name="{brand_manuf}: $country_name";
                } else {
                    $flag=""; $country_name="";
                }

                $bcontent.="<div class=\"row align-items-center basket-table-tbody\">
                    <div class=\"col-12 col-lg-2\"><a href=\"$link\">$art_name</a></div>
                    <div class=\"col-12 col-lg-2\" title=\"$country_name\">$flag $brand_name</div>
                    <div class=\"col-12 col-lg-4\">$text</div>
                    <div class=\"col-12 col-lg-1\" style='color: #606975'><span title=\"$del\"><i class=\"fas fa-clock\"></i> $dd {day_abbr}.</span></div>
                    <div class=\"col-12 col-lg-1\">$amount</div>
                    <div class=\"col-12 col-lg-1\">$price</div>
                    <div class=\"col-12 col-lg-1\">$full_price</div>
                </div>";
            }

            $bcontent.="</div>";
        } else $bcontent=$bempty;

        $form=str_replace("{basket_block}", "", $form);
        $form=str_replace("{basket_content}", $bcontent, $form);
        $form=$this->replaceLang($form);
        return array($form, $sum);
    }

    function moveToBasket($art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id) { $db=DbSingleton::getTokoDb();
        $client=new ClientClass; $catalogue=new CatalogueClass; $exrate=new ExRateClass; $cat=new CatalogueClass;
        $user=$client->getClient()[1]; $where=$client->getClientWhere(); $t_point=$client->getTpoint();
        $cookie=$_COOKIE["session_id"]; $date_time=date("Y-m-d H:i:s");
        $old_amount=0; $status_action=0;
        $art_name=$this->getArticleDispl($art_id);

        $r=$db->query("SELECT `amount` FROM `basket` WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;"); $n=$db->num_rows($r);
        $price=$catalogue->getArticlePrice($art_id);
        if ($suppl_id!=0) $price=$catalogue->getArticleSupplPrice($art_id, $suppl_id, $storage_id);

        if (!($catalogue->checkActionPrice($art_id))) {} else {
            list($action_id, $action_amount, $action_price) = $catalogue->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price,1); // to UAH
            if ($amount>=$action_amount) {
                $status_action=$action_id;
                $price=$action_price;
            }
        }

        // delivery
        list(, $delivery_days, $short_delivery_info) = $cat->getTpointDeliveryInfo($t_point, $storage_id);
        if ($suppl_id!=0) list(, $delivery_days, $short_delivery_info) = $cat->getTpointSupplDeliveryInfo($t_point, $suppl_id, $storage_id);
        $short_delivery_info=$this->replaceLang($short_delivery_info);

        if ($n>0) {
            $r2=$db->query("SELECT * FROM `basket` WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;");
            $cur_stock = $db->result($r2, 0, "amount");
            if ($stock < ($cur_stock + $amount)) {
                $amount=$stock;
            } else {
                $old_amount = intval($db->result($r, 0, "amount"));
                $amount+=$old_amount;
            }
            $db->query("UPDATE `basket` SET `amount`='$amount', `status_action`='$status_action' WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;");
        } else {
            $db->query("INSERT INTO `basket` (`art_id`, `brand_id`, `amount`, `price`, `stock`, `delivery`, `client_id`, `cookie_id`, `date_create`, `storage_id`, `delivery_info`, `suppl_id`,`status_action`,`status`) 
            VALUES ('$art_id', '$brand_id', '$amount', $price, '$stock', '$delivery_days', '$user', '$cookie', '$date_time', '$storage_id', '$short_delivery_info', '$suppl_id', '$status_action', '0');");
        }
        if ($amount>0) $amount_cap=$this->replaceLang("{site_basket}: $amount {amount_abbr}."); else $amount_cap="";
        return array($old_amount, $art_name, $amount_cap);
    }

    function getBasketArticleAmount($art_id, $storage_id) { $db=DbSingleton::getTokoDb();
        $client=new ClientClass; $where=$client->getClientWhere();
        $r=$db->query("SELECT * FROM `basket` WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;");
        $amount = $db->result($r, 0, "amount");
        return $amount;
    }

    function deleteFromBasket($art_id, $storage_id) { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $where=$client->getClientWhere();
        $db->query("DELETE FROM `basket` WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where;");
        return true;
    }

    function checkStatusBasket() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $where=$client->getClientWhere();
        $r=$db->query("SELECT * FROM `basket` WHERE $where AND `status_checked`=1;"); $n=$db->num_rows($r);
        $n>0 ? $result=true : $result=false;
        return $result;
    }

    function checkBasketItem($art_id, $storage_id, $status) { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $where=$client->getClientWhere();
        $db->query("UPDATE `basket` SET `status_checked`=$status WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where;");
        return true;
    }

    function updateBasketForm($art_id, $amount, $storage_id) { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $catalogue=new CatalogueClass;
        $where=$client->getClientWhere(); $status_action=0;
        if (!($catalogue->checkActionPrice($art_id))) {} else {
            list($action_id, $action_amount,)=$catalogue->checkActionPrice($art_id);
            if ($amount>=$action_amount) $status_action=$action_id;
        }
        $db->query("UPDATE `basket` SET `amount`='$amount', `status_action`='$status_action' WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where;");
        return true;
    }

    function countBasket() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass;
        $where=$client->getClientWhere();
        $r=$db->query("SELECT * FROM `basket` WHERE $where;"); $n=$db->num_rows($r);
        $n>0 ? $list=$n : $list="";
        $list=="" ? $style="none" : $style="";
        return array($list,$style);
    }

    function countSummBasket() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $exrate=new ExRateClass;
        $where=$client->getClientWhere(); $list=0;
        $r=$db->query("SELECT * FROM `basket` WHERE $where;"); $n=$db->num_rows($r);
        if ($n>0) {
            for ($i=1;$i<=$n;$i++) {
                $price=floatval($db->result($r, $i-1, "price"));
                $price=$exrate->getKoursPrice($price,$exrate->getCurrentKours());
                $stock=intval($db->result($r, $i-1, "amount"));
                $sum=$price*$stock;
                $list=$list+$sum;
            }
        } else $list=0;
        $cur_cap=$exrate->getKoursSymbol($exrate->getCurrentKours());
        $list.=" $cur_cap";
        return $list;
    }

     function getUpdateData($art_id, $suppl_id, $storage_id, $amount) {
        $catalogue=new CatalogueClass; $exrate=new ExRateClass; $client=new ClientClass;
        $t_point=$client->getTpoint();
        $price=$catalogue->getArticlePrice($art_id);
        if ($suppl_id!=0) $price=$catalogue->getArticleSupplPrice($art_id, $suppl_id, $storage_id);

        if (!($catalogue->checkActionPrice($art_id))) {} else {
            list(,$action_amount,$action_price)=$catalogue->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price,1);
            if ($amount>=$action_amount) {$price=$action_price;}
        }

        if ($suppl_id==0) {
            $ddData=$catalogue->getTpointDeliveryInfo($t_point, $storage_id);
            $dd=$ddData[1];
        } else {
            $ddSupplData=$catalogue->getTpointSupplDeliveryInfo($t_point, $suppl_id, $storage_id);
            $dd=$ddSupplData[1];
        }

        $stock=$this->getArticleStock($art_id, $storage_id);
        if ($stock==0 || $stock=="") $stock=$this->getArticleSupplStock($art_id, $suppl_id, $storage_id);
        return array(intval($dd), intval($stock), floatval($price));
     }

     function getClientDeliveryInfo($client_id) { $db=DbSingleton::getDbm();
        $client=new ClientClass;
        $true_client=$client->getClient(); $list="";
        if ($client->checkRetailClient($true_client)) $where_type=" AND `type_id`=1"; else $where_type=" AND `type_id`=2";
        $r=$db->query("SELECT * FROM `A_CLIENTS_USERS_ADDRESS` WHERE `client_id`='$client_id' $where_type;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $address = $db->result($r, $i-1, "address");
            $list.="<option value=\"$i\">$address</option>";
        }
        return $list;
     }

    function showOrderForm() {
        $client=new ClientClass; $menu=new MenuClass; $exrate=new ExRateClass; $showform=new FormClass;
        list($basket, $price)=$this->showMiniBasketForm(); list($client_id, $user)=$client->getClient();
        $cur=$client->getClientCurrency($client_id); $cur_cap=$exrate->getKoursSymbol($cur);

        list($phone, $email, $name, $city) = $client->getOrderInfo($client_id, $user);
        $t_point=$client->getTpointUser($client_id);
        $city_range=$showform->showCityFormSelected("",$city);

        if ($client->checkUnRegClient()) $valid="fas fa-times-circle non_accept validate_input"; else $valid="fas fa-check-circle accept validate_input";

        $delivery_range=$menu->getManualOptions("delivery_type");
        $carrier_id_range=$menu->getManualOptions("carrier_id");
        $payment_range=$menu->getManualOptions("payment");
        $delivery_info_select=$this->getClientDeliveryInfo($client_id);

        $form=$this->getHtmlForm("order/order");
        $form=str_replace("{show_basket}",$basket,$form);
        $form=str_replace("{full_price}",$price,$form);
        $form=str_replace("{currency_cap}",$cur_cap,$form);
        $form=str_replace("{name_value}",$name,$form);
        $form=str_replace("{phone_value}",$phone,$form);
        $form=str_replace("{email_value}",$email,$form);
        $form=str_replace("{valid}",$valid,$form);
        $form=str_replace("{client_value}",$client_id,$form);
        $form=str_replace("{user_value}",$user,$form);
        $form=str_replace("{tpoint_value}",$t_point,$form);
        $form=str_replace("{city_range}",$city_range,$form);
        $form=str_replace("{category_options}", $this->getManualOptions("customers_categories"), $form);
        $form=str_replace("{delivery_range}",$delivery_range,$form);
        $form=str_replace("{delivery_info_select}",$delivery_info_select,$form);
        $form=str_replace("{carrier_id_range}",$carrier_id_range,$form);
        $form=str_replace("{payment_range}",$payment_range,$form);

        if ($client->checkUnRegClient()) {
            $order_type_caption="{new_buyer}";
            $order_type_display="inline";
        } else {
            $order_type_caption="{check_the_data}";
            $order_type_display="none";
        }

        $form=str_replace("{order_type_caption}",$order_type_caption,$form);
        $form=str_replace("{order_type_display}",$order_type_display,$form);
        return $form;
    }

    function finishOrder($client_id, $user, $t_point, $name, $phone, $region, $email, $delivery, $delivery_info, $payment, $payment_info, $carrier_id) { $db = DbSingleton::getDbm();
        $client=new ClientClass;
        if ($payment=="") $payment=117;
		if ($delivery=="") $delivery=118;
		if ($carrier_id=="") $carrier_id=0;
        $cookie=$_COOKIE["session_id"]; $cash_id=intval($client->getClientCurrency($client_id));
        $r=$db->query("SELECT MAX(`id`) as max_order FROM `orders_new`;"); $max=intval($db->result($r,0,"max_order"))+1;
        $sum=$this->finishOrderBasket($max); $new_client=$user;

        if($client_id=="undefined") $client_id=$this->getClient();
        if($user=="undefined") $user=$this->getUser();

        if ($user!==0 && $user!=="0") {
            list($phone_client, $email_client, $name_client,)=$client->getOrderInfo($client_id, $user);
            $t_point=$client->getTpointUser($client_id);
            $phone=$phone_client;
            $email=$email_client;
            $name=$name_client;
        } else {
            if ($region=="") $region=0;
            if ($t_point==0) $t_point=$client->getTpoint();
            $category=140;
            $new_client=$client->regClientRetail($t_point, $name, $phone, $region, $email, $category);
            $this->addNewRetailAddressForm($new_client, $delivery_info);
        }
        $db->query("INSERT INTO `orders_new` (`id`,`client_id`,`client_user_id`,`cookie_id`,`tpoint_id`,`cash_id`,`name`,`email`,`phone`,`region`,`address`,`delivery`,`carrier_id`,`delivery_info`,`payment`,`payment_info`,`price_summ`,`status`) 
        VALUES ($max,$client_id,$user,'$cookie',$t_point,$cash_id,'$name','$email','$phone',$region,'',$delivery,$carrier_id,'$delivery_info',$payment,'$payment_info',$sum,1);");
        return array($max, $new_client);
    }

    function addNewAdressForm($client_id, $address) { $db=DbSingleton::getDbm();
        $client=new ClientClass;
        $user=$client->getClient()[1]; $answer="";
        if ($client_id>0 && $address!="") {
            if ($user!=0 && $user!="0") $db->query("INSERT INTO `A_CLIENTS_USERS_ADDRESS` (`client_id`,`address`) VALUES ('$client_id','$address');");
            $answer=1;
        }
        return $answer;
    }

    function addNewRetailAddressForm($client_id, $address) { $db=DbSingleton::getDbm();
        $answer="";
        if ($client_id>0 && $address!="") {
            $db->query("INSERT INTO `A_CLIENTS_USERS_ADDRESS` (`client_id`,`address`,`type_id`) VALUES ('$client_id','$address','2');");
            $answer=1;
        }
        return $answer;
    }

    function finishOrderBasket($order_id) { $db=DbSingleton::getDbm(); $dbt=DbSingleton::getTokoDb();
        $client=new ClientClass;
        $sum=0; $where_id=array(); $where=$client->getClientWhere();
        $r=$dbt->query("SELECT * FROM `basket` WHERE $where AND `status_checked`=1;"); $n=$dbt->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $id = $dbt->result($r, $i - 1, "id");
            array_push($where_id,$id);
            $art_id = $dbt->result($r, $i - 1, "art_id");
            $brand_id = $dbt->result($r, $i - 1, "brand_id");
            $amount = $dbt->result($r, $i - 1, "amount");
            $price = $dbt->result($r, $i - 1, "price");
            $suppl_id = $dbt->result($r, $i - 1, "suppl_id");
            $storage_id = $dbt->result($r, $i - 1, "storage_id");
            $status_checked = $db->result($r, $i - 1, "status_checked");
            $status_action = $db->result($r, $i - 1, "status_action");
            $full_price = $price * $amount;
            if ($status_checked) $sum+=$full_price;
            $rmax=$db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;"); $max=intval($db->result($rmax,0,"max_order_str"))+1;
            $db->query("INSERT INTO `orders_str_new` (`id`, `order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
            VALUES ('$max', '$order_id', '$suppl_id', '$storage_id', '$art_id', '$brand_id', '$amount', $price, '$full_price', '$status_action');");
        }
        if (empty($where_id)) $where_id_str="0"; else $where_id_str=implode(",",$where_id);
        $dbt->query("DELETE FROM `basket` WHERE `id` IN ($where_id_str);");
        return $sum;
    }

    function getOrderSumm($order_id) { $db=DbSingleton::getDbm();
        $summ=0;
        $r=$db->query("SELECT * FROM `orders_new` WHERE `id`='$order_id' LIMIT 1;"); $n=$db->num_rows($r);
        if ($n>0) $summ=$db->result($r, 0, "price_summ");
        return $summ;
    }

    function showRegistrationSuccessForm($order_id, $user) {
        $client=new ClientClass;
        $client_id=$client->getClient()[0];
        $login=$logout="none";
        $form=$this->getHtmlForm("order/order_success");
        $form=str_replace("{order_id}", $order_id, $form);
        $form=str_replace("{client_id}", $user, $form);
        list($phone,,$email,$name)=$client->infoClient($client_id,$user);
        if ($client->checkUnRegClient()) $logout="dflex"; else $login="dflex";
        $form=str_replace("{input_name}", $name, $form);
        $form=str_replace("{input_phone}", $phone, $form);
        $form=str_replace("{input_email}", $email, $form);
        $form=str_replace("{status_login}", $login, $form);
        $form=str_replace("{status_logout}", $logout, $form);
        return $form;
    }

    function addNewAddressForm($client_id, $address) {
        $result=1;
        return $result;
    }

    function getOrderForm() {
        $form=$this->getHtmlForm("orders/order");
        $form=str_replace("{order_delivery}", $this->getOrderDelivery(), $form);
        $form=str_replace("{order_payment}", $this->getOrderPayment(), $form);
        $form=$this->replaceLang($form);
        return $form;
    }

    function getOrderDelivery() {
        $client=new ClientClass; $tpoint_id=$client->getTpointUser($client->getClient()[0]);
        $form=$this->getHtmlForm("orders/delivery");
        $form=str_replace("{tpoint_address}", $client->getTpointAddress($tpoint_id), $form);
        return $form;
    }

    function setCityDepartments($city_id) {
        $list_np="";
        $list_up="";
        for ($i=1; $i<=5; $i++) {
            $list_np.="<option value='$i'>$city_id - $i</option>";
            $list_up.="<option value='$i'>$city_id - $i</option>";
        }
        return array($list_np, $list_up);
    }

    function getOrderPayment() {
        $form=$this->getHtmlForm("orders/payment");
        return $form;
    }

}