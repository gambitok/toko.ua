<?php

class ShopClass {

    use Helper;
    use Variables;

    function showBasketForm($cur=null) { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $showform=new FormClass; $exrate=new ExRateClass; $catalogue=new CatalogueClass; $language=new LangClass;
        $sum = 0; $sum_total = 0; $count = 0;
        $disabled=$brow=$bprow=""; $location="stayInOrder();"; $location_fast="stayInOrder();"; if ($cur==null || $cur=="NaN") $cur=1;
        $client_id = $this->getClient(); $tpoint_id = $client->getTpoint(); $where = $client->getClientWhere();
        setcookie("currency", $cur); $_SESSION["currency"] = $cur;  $prefix = $language->getLangPrefix();

        $r = $db->query("SELECT * FROM `basket` WHERE $where ORDER BY `date_create` DESC;"); $n = $db->num_rows($r);
        if ($n>0) {
            for ($i=1; $i<=$n; $i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $brand_id = $db->result($r, $i - 1, "brand_id");
                $art_name = $this->getArticleDispl($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $text = $this->getArticleName($art_id);
                $suppl_id = $db->result($r, $i - 1, "suppl_id");
                $amount = $db->result($r, $i - 1, "amount");
                $price = $db->result($r, $i - 1, "price");
                $date_create = $db->result($r, $i - 1, "date_create");
                $storage_id = $db->result($r, $i - 1, "storage_id");
                $stock = $db->result($r, $i - 1, "stock");
                $status = $db->result($r, $i - 1, "status");
                $status_checked = $db->result($r, $i - 1, "status_checked");

                // DELIVERY
                if ($suppl_id==0) $deliveryData = $catalogue->getTpointDeliveryInfo($tpoint_id, $storage_id); else $deliveryData = $catalogue->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
                $delivery_info = $deliveryData["info"];
                $delivery_short_info = $deliveryData["short"];

                // PRICE
                $price = $exrate->getKoursPrice($price, $cur);
                if ($cur==1) $price = $client->getClientPriceRounding($client_id, $price);
                $full_price = $price * $amount;
                if ($cur==1) $full_price = $client->getClientPriceRounding($client_id, $full_price);

                $sum_total+=$full_price;
                if ($status_checked) {
                    $sum+=$full_price;
                    $count+=1;
                }

                $action = $this->getClientAction($art_id, $suppl_id, $storage_id, $amount, $cur);

                $format_date1 = date("d.m.y H:i", strtotime($date_create));
                $format_date2 = date("d.m.y H:i", strtotime(date("Y-m-d H:i:s")));
                $format_name = $this->getFormatAticle($art_name);
                $format_brand = $this->getFormatAticle($brand_name);

                $location = "location.href='https://toko.ua$prefix/order/';";
                $location_fast = "finishFastOrder('input_phone2');";

                $flagData = $showform->getCountryFlag($brand_id);
                if ($flagData!=false) {
                    $flag = "<img class=\"flag flag-".$flagData["flag"]." flag-search\">";
                    $country_name = "{brand_manuf}: ".$flagData["country"];
                } else {
                    $flag = ""; $country_name = "";
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
                $brow=str_replace("{link}", "https://toko.ua$prefix/article/$format_name/$format_brand/$art_id/", $brow);
                $brow=str_replace("{flag}", $flag, $brow);
                $brow=str_replace("{country_name}", $country_name, $brow);
                $brow=str_replace("{amount_field}", "count_".$art_id."_".$storage_id, $brow);
                $brow=str_replace("{action}", $action, $brow);
                $brow=str_replace("{product_image}", $catalogue->getBasketArticlePhoto($art_id), $brow);
                $brow=str_replace("{cash_abr}", $exrate->getKoursSymbol($cur), $brow);

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
                $bprow=str_replace("{link}", "https://toko.ua$prefix/article/$format_name/$format_brand/$art_id/", $bprow);
                $bprow=str_replace("{flag}", $flag, $bprow);
                $bprow=str_replace("{country_name}", $country_name, $bprow);
                $bprow=str_replace("{amount_field}", "count_".$art_id."_".$storage_id, $bprow);
                $bprow=str_replace("{action}", $action, $bprow);
                $bprow=str_replace("{cash_abr}", $exrate->getKoursSymbol($cur), $bprow);
            }
        } else {
            $brow = "<div class=\"row align-items-center\"><div class=\"col-12\"><p class=\"text-center mar0\"><br>{basket_empty}</p><br></div></div>";
            $bprow = "";
        }

        $table_basket=$this->getHtmlForm("basket/basket_form");
        $table_basket=str_replace("{basket_rows}", $brow, $table_basket);
        $table_basket=str_replace("{checked_status}", $sum==$sum_total ? "checked=\"checked\"" : "", $table_basket);
        $table_basket=str_replace("{basket_phone_rows}", $bprow, $table_basket);
        $table_basket=str_replace("{sum}", $sum, $table_basket);
        $table_basket=str_replace("{sum_total}", $sum_total, $table_basket);
        $table_basket=str_replace("{count}", $count, $table_basket);
        $table_basket=str_replace("{total_style}", $sum==$sum_total ? "d-none" : "", $table_basket);
        $table_basket=str_replace("{location}", $location, $table_basket);
        $table_basket=str_replace("{location_fast}", $location_fast, $table_basket);
        $table_basket=str_replace("{currency}", $showform->getCurrencyForm(4, 0, $cur), $table_basket);
        $table_basket=str_replace("{cur_cap}", $exrate->getKoursSymbol($cur), $table_basket);
        $table_basket=str_replace("{disabled}", $disabled, $table_basket);
        $table_basket=str_replace("{basket_proposed}", $this->getProposedArts(), $table_basket);
        $table_basket=str_replace("{user_phone}", $client->getClientPhone(), $table_basket);
        $table_basket=str_replace("{validate_class}", $client->getClientPhone()=="" ? "non_accept fa-times-circle" : "accept fa-check-circle", $table_basket);

        $table_basket = $this->replaceLang($table_basket);

        if ($n==0) $table_basket=$this->replaceLang($this->getHtmlForm("basket/basket_error"));

        return $table_basket;
    }

    function getClientAction($art_id, $suppl_id, $storage_id, $amount, $cur) {
        $catalogue = new CatalogueClass; $exrate = new ExRateClass;
        $cur_cap = $exrate->getKoursSymbol($cur);
        if (!($catalogue->checkActionPrice($art_id))) $action = ""; else {
            list(, $action_amount, $action_price) = $catalogue->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price, $cur);
            if ($suppl_id==0) $true_price = $catalogue->getArticlePrice($art_id); else $true_price = $catalogue->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
            $true_price = round($exrate->getKoursPrice($true_price, $cur),2);
            if ($amount>=$action_amount) {
                $true_cap = "<br><span class=\"span-outline\">$true_price $cur_cap</span>";
                $true_clr = "";
            } else {
                $true_cap = "";
                $true_clr = "color:lightcoral!important;";
            }
            $action = "$true_cap<br>
            <span style=\"font-size: 1.5em; $true_clr\" class=\"span-green tooltips\" data-toggle=\"tooltip\" data-placement=\"bottom\" 
                title=\"{price_cap} $action_price $cur_cap, {from_cap} $action_amount {amount_abbr}.\">
                <i class=\"fa fa-box-open\"></i>
            </span>";
        }
        return $action;
    }

    function getBasketArts() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $where=$client->getClientWhere();
        $r=$db->query("SELECT * FROM `basket` WHERE $where ORDER BY `date_create` DESC;"); $n=$db->num_rows($r);
        if ($n>0) {
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

    function getProposedArts() { $db = DbSingleton::getTokoDb();
        $list = ""; $where_arts = "";
        $arts = $this->getBasketArts();
        if ($arts!="") {
            $where_arts = " AND `ART_ID` NOT IN ($arts)";
        }
        $r=$db->query("SELECT * FROM `T2_ARTICLES_PROPOSED` WHERE `STATUS`=1 $where_arts;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id = $db->result($r, $i - 1, "ART_ID");
            $list.=$this->getProposedArtsCard($art_id);
        }
        $form = $this->getHtmlForm("orders/proposed");
        $form = str_replace("{proposed_range}", $list, $form);
        $form = $this->replaceLang($form);
        if ($n==0) $form="";
        return $form;
    }

    function getProposedArtsCard($art_id) {
        $catalogue=new CatalogueClass; $language=new LangClass; $showform=new FormClass;

        $form = $this->getHtmlForm("orders/proposed_card");

        $article = $showform->getArticleInfo($art_id);
        $article_nr_displ = $article["article_nr_displ"];
        $brand_id = $article["brand_id"];
        $brand_name = $article["brand_name"];
        $article_name = $article["text"];
        $stock = $article["stock"];
        $price = $article["price"];
        $basket = $article["basket"];
        $currency = $article["currency"];
        $img = $showform->getArticleActivePhoto($art_id);
        $prefix = $language->getLangPrefix();
        $format_name = $catalogue->getFormatAticle($article_nr_displ);
        $format_brand = $catalogue->getFormatBrand($brand_name);

        $form = str_replace("{art_id}", $art_id, $form);
        $form = str_replace("{brand_id}", $brand_id, $form);
        $form = str_replace("{real_stock}", $stock, $form);
        $form = str_replace("{basket}", $basket, $form);

        $form = str_replace("{article_nr_displ}", $article_nr_displ, $form);
        $form = str_replace("{name}", $article_name, $form);
        $form = str_replace("{brand_name}", $brand_name, $form);
        $form = str_replace("{price}", $price, $form);
        $form = str_replace("{image}", $img, $form);
        $form = str_replace("{prefix}", $prefix, $form);
        $form = str_replace("{format_name}", $format_name, $form);
        $form = str_replace("{format_brand}", $format_brand, $form);
        $form = str_replace("{currency}", $currency, $form);

        return $form;
    }

    function showMiniBasketForm() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $exrate=new ExRateClass; $showform=new FormClass; $language=new LangClass;
        $client_id=$this->getClient(); $cur=$client->getClientCurrency($client_id); $where=$client->getClientWhere();
        $sum=0;

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
            for ($i=1; $i<=$n; $i++) {
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

                $flagData = $showform->getCountryFlag($brand_id);
                if ($flagData!=false) {
                    $flag = $flagData["flag"];
                    $country_name = $flagData["country"];

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
        } else $bcontent="<div id=\"basket_block\" class=\"content\">{basket_empty}</div>";

        $form=$this->getHtmlForm("basket/basket");
        $form=str_replace("{basket_block}", "", $form);
        $form=str_replace("{basket_content}", $bcontent, $form);
        $form=$this->replaceLang($form);
        return array($form, $sum);
    }

    function moveToBasket($art_id, $brand_id, $amount, $stock, $storage_id, $suppl_id) { $db=DbSingleton::getTokoDb();
        $client=new ClientClass; $catalogue=new CatalogueClass; $exrate=new ExRateClass; $cat=new CatalogueClass;
        $user_id=$this->getUser(); $where=$client->getClientWhere(); $tpoint_id=$client->getTpoint();
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

        $deliveryData = $cat->getTpointDeliveryInfo($tpoint_id, $storage_id);
        $delivery_days = $deliveryData["days"];
        $delivery_short_info = $deliveryData["short"];
        if ($suppl_id!=0) {
            $deliveryData = $cat->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
            $delivery_days = $deliveryData["days"];
            $delivery_short_info = $deliveryData["short"];
        }
        $delivery_short_info = $this->replaceLang($delivery_short_info);

        if ($n>0) {
            $r2 = $db->query("SELECT * FROM `basket` WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;");
            $cur_stock = $db->result($r2, 0, "amount");
            if ($stock < ($cur_stock + $amount)) {
                $amount = $stock;
            } else {
                $old_amount = intval($db->result($r, 0, "amount"));
                $amount+=$old_amount;
            }
            $db->query("UPDATE `basket` SET `amount`='$amount', `status_action`='$status_action' WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;");
        } else {
            $db->query("INSERT INTO `basket` (`art_id`, `brand_id`, `amount`, `price`, `stock`, `delivery`, `client_id`, `cookie_id`, `date_create`, `storage_id`, `delivery_info`, `suppl_id`,`status_action`,`status`) 
            VALUES ('$art_id', '$brand_id', '$amount', $price, '$stock', '$delivery_days', '$user_id', '$cookie', '$date_time', '$storage_id', '$delivery_short_info', '$suppl_id', '$status_action', '0');");
        }
        if ($amount>0) $amount_cap = $this->replaceLang("{site_basket}: $amount {amount_abbr}."); else $amount_cap = "";
        return array($old_amount, $art_name, $amount_cap);
    }

    function getBasketArticleAmount($art_id, $storage_id) { $db=DbSingleton::getTokoDb();
        $client = new ClientClass; $where = $client->getClientWhere();
        $r = $db->query("SELECT * FROM `basket` WHERE `art_id`='$art_id' AND `storage_id`='$storage_id' AND $where LIMIT 1;");
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
        $client=new ClientClass; $where=$client->getClientWhere();
        $r=$db->query("SELECT * FROM `basket` WHERE $where;"); $n=$db->num_rows($r);
        $n>0 ? $list=$n : $list="";
        $list=="" ? $style="none" : $style="";
        return array($list, $style);
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
        $tpoint_id=$client->getTpoint();
        $price=$catalogue->getArticlePrice($art_id);
        if ($suppl_id!=0) $price=$catalogue->getArticleSupplPrice($art_id, $suppl_id, $storage_id);
        if (!($catalogue->checkActionPrice($art_id))) {} else {
            list(,$action_amount,$action_price)=$catalogue->checkActionPrice($art_id);
            $action_price = $exrate->getKoursFromUSA($action_price,1);
            if ($amount>=$action_amount) {$price=$action_price;}
        }
        if ($suppl_id==0) {
            $deliveryData = $catalogue->getTpointDeliveryInfo($tpoint_id, $storage_id);
        } else {
            $deliveryData = $catalogue->getTpointSupplDeliveryInfo($tpoint_id, $suppl_id, $storage_id);
        }
         $dd = $deliveryData["days"];
        $stock = $this->getArticleStock($art_id, $storage_id);
        if ($stock==0 || $stock=="") $stock = $this->getArticleSupplStock($art_id, $suppl_id, $storage_id);
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
        list($basket, $price)=$this->showMiniBasketForm(); list($client_id, $user_id)=$client->getClient();
        $cur=$client->getClientCurrency($client_id); $cur_cap=$exrate->getKoursSymbol($cur);

        $orderClientData = $client->getOrderInfo($client_id, $user_id);
        $phone = $orderClientData["phone"];
        $email = $orderClientData["email"];
        $name = $orderClientData["name"];
        $city = $orderClientData["city"];
        $tpoint_id = $client->getTpointUser($client_id);
        $city_range = $showform->showCityFormSelected("", $city);

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
        $form=str_replace("{user_value}",$user_id,$form);
        $form=str_replace("{tpoint_value}",$tpoint_id,$form);
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

    function finishOrder($client_id, $user_id, $tpoint_id, $name, $phone, $region, $email, $delivery, $delivery_info, $payment, $payment_info, $carrier_id) { $db = DbSingleton::getDbm();
        $client=new ClientClass;
        if ($payment=="") $payment=117;
		if ($delivery=="") $delivery=118;
		if ($carrier_id=="") $carrier_id=0;
        if ($client_id=="undefined") $client_id = $this->getClient();
        if ($user_id=="undefined") $user_id = $this->getUser();
        $cookie=$_COOKIE["session_id"]; $cash_id=intval($client->getClientCurrency($client_id));

        $r = $db->query("SELECT MAX(`id`) as max_order FROM `orders_new`;"); $max = intval($db->result($r,0,"max_order"))+1;
        $sum = $this->finishOrderBasket($max); $new_client = $user_id;

        if ($user_id==0) {
            if ($region=="") $region = 0;
            if ($tpoint_id==0) $tpoint_id = $client->getTpoint();
            $new_client = $client->regClientRetail($tpoint_id, $name, $phone, $region, $email);
            $this->addNewRetailAddressForm($new_client, $delivery_info);
        } else {
            $orderClientData = $client->getOrderInfo($client_id, $user_id);
            $phone = $orderClientData["phone"];
            $email = $orderClientData["email"];
            $name = $orderClientData["name"];
            $tpoint_id = $client->getTpointUser($client_id);
        }

        $db->query("INSERT INTO `orders_new` (`id`, `client_id`, `client_user_id`, `cookie_id`, `tpoint_id`, `cash_id`, `name`, `email`, `phone`, `region`, `delivery`, `carrier_id`, `delivery_info`, `payment`, `payment_info`, `price_summ`) 
        VALUES ($max, $client_id, $user_id, '$cookie', $tpoint_id, $cash_id, '$name', '$email', '$phone', '$region', $delivery, $carrier_id, '$delivery_info', $payment, '$payment_info', $sum);");
        return array($max, $new_client);
    }

    function addNewAdressForm($client_id, $address) { $db=DbSingleton::getDbm();
        $user_id = $this->getUser();
        $answer = "";
        if ($client_id>0 && $address!="") {
            if ($user_id!=0 && $user_id!="0") $db->query("INSERT INTO `A_CLIENTS_USERS_ADDRESS` (`client_id`, `address`) VALUES ('$client_id', '$address');");
            $answer=1;
        }
        return $answer;
    }

    function addNewRetailAddressForm($client_id, $address) { $db=DbSingleton::getDbm();
        $answer="";
        if ($client_id>0 && $address!="") {
            $db->query("INSERT INTO `A_CLIENTS_USERS_ADDRESS` (`client_id`, `address`, `type_id`) VALUES ('$client_id', '$address', '2');");
            $answer=1;
        }
        return $answer;
    }

    // BASKET to ORDER
    function finishOrderBasket($order_id) { $db=DbSingleton::getDbm(); $dbt=DbSingleton::getTokoDb();
        $client = new ClientClass;
        $sum = 0; $where = $client->getClientWhere();
        $r = $dbt->query("SELECT * FROM `basket` WHERE $where AND `status_checked`=1;"); $n = $dbt->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $dbt->result($r, $i - 1, "id");
            $art_id = $dbt->result($r, $i - 1, "art_id");
            $brand_id = $dbt->result($r, $i - 1, "brand_id");
            $amount = $dbt->result($r, $i - 1, "amount");
            $price = $dbt->result($r, $i - 1, "price");
            $suppl_id = $dbt->result($r, $i - 1, "suppl_id");
            $storage_id = $dbt->result($r, $i - 1, "storage_id");
            $status_action = $db->result($r, $i - 1, "status_action");
            $full_price = $price * $amount;
            $sum+=$full_price;
            $rmax = $db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;"); $max = intval($db->result($rmax,0,"max_order_str")) + 1;
            $db->query("INSERT INTO `orders_str_new` (`id`, `order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
            VALUES ('$max', '$order_id', '$suppl_id', '$storage_id', '$art_id', '$brand_id', '$amount', $price, '$full_price', '$status_action');");
            $dbt->query("DELETE FROM `basket` WHERE `id`='$id';");
        }
        if ($order_id>0 && $n>0) {
            $delivery_sum = $this->setDeliveryIndex($order_id);
            $sum+=$delivery_sum;
        }
        return $sum;
    }

    /*==== DELIVERY INDEX ADD ====*/
    function setDeliveryIndex($order_id) { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        $price = 0;
        $r = $db->query("SELECT * FROM `orders_new` WHERE `ID`='$order_id' LIMIT 1;");
        $order_info_id = $db->result($r, 0, "order_info_id");
        $tpoint_id = $db->result($r, 0, "tpoint_id");
        $client_id = $db->result($r, 0, "client_id");
        $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `ID`='$order_info_id' LIMIT 1;");
        $delivery_id = $db->result($r, 0, "DELIVERY_ID");
        if (in_array($delivery_id, [4,5]) && ($client->checkRetailClientCategory($client_id))) {
            list($art_id, $brand_id, $storage_id, $price) = $this->getDeliveryIndex($delivery_id, $tpoint_id);
            $rmax = $db->query("SELECT MAX(`id`) AS max_order_str FROM `orders_str_new`;"); $max = intval($db->result($rmax,0,"max_order_str")) + 1;
            $db->query("INSERT INTO `orders_str_new` (`id`, `order_id`, `suppl_id`, `storage_id`, `art_id`, `brand_id`, `amount`, `price`, `summ`, `status_action`) 
            VALUES ('$max', '$order_id', '0', '$storage_id', '$art_id', '$brand_id', '1', $price, '$price', '0');");
        }
        return $price;
    }
    function getDeliveryIndex($delivery_id, $tpoint_id) {
        $cat = new CatalogueClass;
        $client = new ClientClass;
        $art_id = 0;
        $brand_id = 0;
        $storage_id = 0;
        $price = 0;
        if (in_array($delivery_id, [4,5])) {
            if ($delivery_id==4) {
                $art_id = 100060075; // NOVA POSHTA
            }
            if ($delivery_id==5) {
                $art_id = 100060076; // NOVA POSHTA KURER
            }
            $brand_id = $this->getArticleBrand($art_id);
            $storage_id = $client->getDefaultStorageID($tpoint_id);
            $price = $cat->getArticlePrice($art_id);
        }
        return array($art_id, $brand_id, $storage_id, $price);
    }

    // GET ORDER SUM
    function getOrderSumm($order_id) { $db=DbSingleton::getDbm();
        $summ = 0;
        $r = $db->query("SELECT * FROM `orders_new` WHERE `id`='$order_id' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n>0) $summ = $db->result($r, 0, "price_summ");
        return $summ;
    }

    function showRegistrationSuccessForm($order_id, $user_id) {
        $client = new ClientClass;
        $client_id = $this->getClient();
        $login = $logout = "none";
        $form = $this->getHtmlForm("order/order_success");
        $form = str_replace("{order_id}", $order_id, $form);
        $form = str_replace("{client_id}", $user_id, $form);
        $clientData = $client->getClientInfo($client_id, $user_id);
        $phone = $clientData["phone"];
        $email = $clientData["email"];
        $name = $clientData["name"];
        if ($client->checkUnRegClient()) $logout="dflex"; else $login="dflex";
        $form = str_replace("{input_name}", $name, $form);
        $form = str_replace("{input_phone}", $phone, $form);
        $form = str_replace("{input_email}", $email, $form);
        $form = str_replace("{status_login}", $login, $form);
        $form = str_replace("{status_logout}", $logout, $form);
        return $form;
    }

    // GET CLIENT DELIVERY DATA (by CITY, USER_ID)
    function getUserSavedData($user_id, $city_id) { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        if ($user_id==0 || $user_id=="" || $user_id=="undefined") $user_id = $this->getUser();
        $client_id = $client->getClientByUser($user_id);

        $list = ""; $status = 0; $info_id = 0; $id = 0;
        if ($user_id>0) {
            $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID`='$client_id' AND `USER_ID`='$user_id' AND `CITY_ID`='$city_id' AND `STATUS`=1;"); $n = $db->num_rows($r);
            for ($i=1; $i<=$n; $i++) {
                $id = $db->result($r, $i - 1, "ID");
                $delivery_id = $db->result($r, $i - 1, "DELIVERY_ID"); $delivery_text = $this->getDeliveryCaption($delivery_id);
                $payment_id = $db->result($r, $i - 1, "PAYMENT_ID"); $payment_text = $this->getPaymentCaption($payment_id);
                $street = $db->result($r, $i - 1, "DEL_STREET");
                $house = $db->result($r, $i - 1, "DEL_HOUSE");
                $porch = $db->result($r, $i - 1, "DEL_PORCH");
                $department_text = $db->result($r, $i - 1, "DEL_DEPARTMENT_TEXT");
                $express = $db->result($r, $i - 1, "DEL_EXPRESS");
                $express_info = $db->result($r, $i - 1, "DEL_EXPRESS_INFO");
                $delivery_info = $this->getDeliveryInfoCaption($delivery_id, $street, $house, $porch, $department_text, $express, $express_info);
                if ($delivery_info!="") $delivery_info = "($delivery_info)";
                $list.="<li class=\"orders-user__item\">
                    <a onclick='setClientOrderInfo($id);'>$i. $delivery_text $delivery_info <br> $payment_text</a>
                    <a onclick='dropClientOrderInfo($id)'><i class='fa fa-times'></i></a>
                </li>";
            }
            if ($n==1) {
                $status = 1;
                $info_id = $id;
            }
            if ($n>0) {
                $list = "<div class=\"orders-user\">
                    <p class=\"orders-user__title\">{saved_address}:</p>
                    <a class=\"orders-user__toggle\" onclick=\"ordersUserToggle();\"><i class=\"fa fa-chevron-down\"></i></a>
                    <div id=\"user_saved_info_list\">".$list."</div>
                </div>";
            }
        }
        $list = $this->replaceLang($list);
        return array($status, $list, $info_id);
    }

    /*==== NEW ORDER FORM ====*/

    function getOrderContentForm($order_id, $user_id, $user_status) {

        if ($user_status==0) {
            $form = $this->getHtmlForm("orders/done");
        } else {
            $form = $this->getHtmlForm("orders/done_retail");
        }

        $form = str_replace("{order_id}", $order_id, $form);
        $form = str_replace("{order_user_id}", $user_id, $form);

        $client = new ClientClass;
        $userData = $client->getClientInfo($client->getClientId($user_id), $user_id);

        $form = str_replace("{user_phone}", $userData["phone"], $form);
        $form = str_replace("{user_name}", $userData["name"], $form);
        $form = str_replace("{user_email}", $userData["email"], $form);
        $form = str_replace("{user_pass}", $userData["password"], $form);

        $form = $this->replaceLang($form);

        return $form;
    }

    function getOrderForm() {
        $user_city = 0;
        $user_name = "";
        $user_phone = "";
        $user_email = "";
        $user_id = $this->getUser();
        if ($user_id>0) {
            list($user_name, $user_phone, $user_email, $user_city) = $this->getClientUserData($user_id);
        }

        $form=$this->getHtmlForm("orders/form");
        $form=str_replace("{order_user_id}", $user_id, $form);
        $form=str_replace("{order_delivery}", $this->getOrderDelivery(), $form);
        $form=str_replace("{order_payment}", $this->getOrderPayment(), $form);
        $form=str_replace("{user_city_main_list}", $this->getCitiesMainSelect($user_city), $form);
        $form=str_replace("{user_name}", $user_name, $form);
        $form=str_replace("{user_phone}", $user_phone, $form);
        $form=str_replace("{user_email}", $user_email, $form);
        $form=str_replace("{basket_range}", $this->getBasketOrder(), $form);
        $form=$this->replaceLang($form);
        return $form;
    }

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

    function getOrderDelivery() { $db = DbSingleton::getTokoDb();
        $client=new ClientClass; $tpoint_id=$client->getTpointUser($client->getClient()[0]);
        $form=$this->getHtmlForm("orders/delivery");
        $form=str_replace("{tpoint_address}", $client->getTpointAddress($tpoint_id), $form);
        $form=str_replace("{express_delivery_list}", $this->getDeliveryExpressList(), $form);

        $r = $db->query("SELECT * FROM `T2_DELIVERY`;"); $n = $db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $id = $db->result($r, $i - 1, "ID");
            $text = $db->result($r, $i - 1, "TEXT");
            $type = $db->result($r, $i - 1, "TYPE");
            $status = $db->result($r, $i - 1, "STATUS");
            $display=""; if (!$status) $display="none";
            $free="";
            if ($type==1) $free="({free_cap})";
            if ($type==2) $free="({carrier_conditions})";
            $form = str_replace("{delivery_status_$id}", $display, $form);
            $form = str_replace("{delivery_text_$id}", $text, $form);
            $form = str_replace("{delivery_free_$id}", $free, $form);
        }
        return $form;
    }

    function setCityDepartments($city_ref) {
        $list_up = "<option value='0'>{not_chosen}</option>";
        $list_np = $this->getNovaPoshtaWarehousesSelect($city_ref);
        return array($list_np, $list_up);
    }

    function getOrderPayment() { $db = DbSingleton::getTokoDb();
        $form=$this->getHtmlForm("orders/payment");
        $r = $db->query("SELECT * FROM `T2_PAYMENT`;"); $n = $db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $id = $db->result($r, $i - 1, "ID");
            $text = $db->result($r, $i - 1, "TEXT");
            $status = $db->result($r, $i - 1, "STATUS");
            $display=""; if (!$status) $display="none";
            $form = str_replace("{payment_status_$id}", $display, $form);
            $form = str_replace("{payment_text_$id}", $text, $form);
        }
        return $form;
    }

    function getOrderDeliveryBlock($delivery_id, $city_id) { $db = DbSingleton::getDbm();
        $result = 0;
        $r = $db->query("SELECT * FROM `orders_valid_delivery` WHERE `DELIVERY_ID`='$delivery_id' LIMIT 1;");
        $valid_main = $db->result($r, 0, "VALID_TYPE_MAIN");
        $valid_other = $db->result($r, 0, "VALID_TYPE_OTHER");

        // MAIN CITTIES
        if (in_array($city_id, [10108, 24861])) {
            if ($valid_main) {
                $result = 1;
            }
        }
        // OTHER CITTIES
        else {
            if ($valid_other) {
                $result = 1;
            }
        }

        return $result;
    }

    function getOrderPaymentBlock($payment_id, $delivery_id) { $db = DbSingleton::getDbm();
        $result = 0;
        $del_types_1 = [1, 2, 3];
        $del_types_2 = [4, 5, 6];
        $r = $db->query("SELECT * FROM `orders_valid_payment` WHERE `PAYMENT_ID`='$payment_id' LIMIT 1;");
        $valid = $db->result($r, 0, "VALID_TYPE");
        if ($valid==0) {
            $result = 1;
        }
        if ($valid==1) {
            if (in_array($delivery_id, $del_types_1)) {
                $result = 1;
            }
        }
        if ($valid==2) {
            if (in_array($delivery_id, $del_types_2)) {
                $result = 1;
            }
        }
        return $result;
    }

    function setCityAddress($city_id) {
        $client=new ClientClass;
        $cities = [24861, 10108]; $city_address="";
        if (in_array($city_id, $cities)) {
            $tpoint_id=0;
            if ($city_id==24861) $tpoint_id=1;
            if ($city_id==10108) $tpoint_id=2;
            $city_name = $this->getCityName($city_id);
            $city_address = $city_name." - ".$client->getTpointAddress($tpoint_id);
        }
        return $city_address;
    }

    function getDeliveryExpressList() { $db = DbSingleton::getTokoDb();
        $list = "";
        $r=$db->query("SELECT * FROM `T2_DELIVERY_EXPRESS` ORDER BY `ID` ASC;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $id = $db->result($r, $i-1, "ID");
            $text = $db->result($r, $i-1, "TEXT");
            $list.="<option value='$id'>$text</option>";
        }
        $list = $this->replaceLang($list);
        return $list;
    }

    function getDepartmentExpressName($delivery_express) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_DELIVERY_EXPRESS` WHERE `ID`='$delivery_express' LIMIT 1;");
        $text = $db->result($r, 0, "TEXT");
        $text = $this->replaceLang($text);
        return $text;
    }

    function validOrder($name, $phone, $city, $delivery, $delivery_type, $payment, $email, $comment) {
        $delivery_type_text = "";
        $street = $delivery_type["street"];
        $house = $delivery_type["house"];
        $porch = $delivery_type["porch"];
        $department = $delivery_type["department"];
        $delivery_express = $delivery_type["delivery_express"]; // express ID
        $delivery_express_department = $delivery_type["delivery_express_department"];

        $department_text = $department;
        $delivery_express_text = $this->getDepartmentExpressName($delivery_express);

        if ($porch!="") $porch = ", {entrance_cap} $porch";
        if (($street!="undefined") && ($house!="undefined")) $delivery_type_text.="<div>{address_cap}: {street_cap} $street, {house_cap} $house $porch</div>";
        if ($department!="undefined" && $department!="0") $delivery_type_text.="<div>{department_cap}: $department_text</div>";
        if ($delivery_express!="undefined") $delivery_type_text.="<div>{delivery_type_7}: $delivery_express_text</div>";
        if ($delivery_express_department!="undefined") $delivery_type_text.="<div>{department_cap}: $delivery_express_department</div>";

        $city_text = $this->getCityName($city);
        $delivery_text = $this->getDeliveryName($delivery);
        $payment_text = $this->getPaymentName($payment);
        if ($email=="") $email="{absent_cap}";
        if ($comment=="") $comment="{absent_cap}";

        if ($delivery==1) {
            $tpoint_address = $this->setCityAddress($city);
            $delivery_type_text="<div>$tpoint_address</div>";
        }

        $form=$this->getHtmlForm("orders/confirm");
        $form=str_replace("{order_name}", $name, $form);
        $form=str_replace("{order_phone}", $phone, $form);
        $form=str_replace("{order_city}", $city_text, $form);
        $form=str_replace("{order_delivery}", $delivery_text, $form);
        $form=str_replace("{order_delivery_type}", $delivery_type_text, $form);
        $form=str_replace("{order_payment}", $payment_text, $form);
        $form=str_replace("{order_email}", $email, $form);
        $form=str_replace("{order_comment}", $comment, $form);
        $form=$this->replaceLang($form);

        return $form;
    }

    function saveFastOrder($phone) {
        $client = new ClientClass;

        list(, $user_id) = $client->getAuthorizedUser($phone);
        $client_id = $client->getClientByUser($user_id);
        $user_status = 0;

        // CREATE CLIENT
        if ($user_id==0) {
            list($client_id, $user_id) = $client->addRetailClient($this->getClient(), $phone);
            $user_status = 1;
        }

        $tpoint_id = $client->getTpoint();
        $cookie = $_COOKIE["session_id"];
        $cash_id = intval($client->getClientCurrency($client_id));

        // CREATE CLIENT ORDER INFO
//        $delivery_info = ["street"=>"", "house"=>"", "porch"=>"", "department"=>0, "express"=>0, "express_info"=>""];
//        $order_info_id = $this->saveClientOrderInfo($client_id, $user_id, 0, 0, "", 0, $delivery_info, "", "");

        // CREATE ORDER
        $order_id = $this->saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, "", "", $phone, 0, "", 0);

        return array($order_id, $user_id, $user_status);
    }

    function saveOrder($user_id, $name, $phone, $city_id, $delivery_id, $delivery_type, $payment_id, $email, $comment, $recipient_name, $recipient_phone) {
        $client = new ClientClass;
        if ($user_id==0 || $user_id=="" || $user_id=="undefined") {
            $user_id = $this->getUser();
            $client_id = $this->getClient();
        } else {
            $client_id = $client->getClientByUser($user_id);
        }
        $tpoint_id = $client->getTpoint();
        $cookie = $_COOKIE["session_id"];
        $cash_id = intval($client->getClientCurrency($client_id));
        $user_status = 0;

        $street = $delivery_type["street"];
        $house = $delivery_type["house"];
        $porch = $delivery_type["porch"];
        $department_id = $delivery_type["department_id"];
        $department_text = $delivery_type["department"];
        $delivery_express = $delivery_type["delivery_express"];
        $delivery_express_department = $delivery_type["delivery_express_department"];
        $delivery_info = ["street"=>$street, "house"=>$house, "porch"=>$porch, "department"=>$department_id, "express"=>$delivery_express, "express_info"=>$delivery_express_department];

        // CREATE CLIENT
        if ($user_id==0) {
            $tpoint_client_id = $client_id;
            list($client_id, $user_id) = $client->addRetailClient($tpoint_client_id, $phone, $name, $city_id, $email);
            $user_status = 1;
        }

        // CREATE CLIENT ORDER INFO
        $order_info_id = $this->saveClientOrderInfo($client_id, $user_id, $city_id, $delivery_id, $department_text, $payment_id, $delivery_info, $recipient_name, $recipient_phone);

        // CREATE ORDER
        $order_id = $this->saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, $name, $email, $phone, $city_id, $comment, $order_info_id);

        return array($order_id, $user_id, $user_status);
    }

    function updateOrderSum($order_id, $order_sum) { $db = DbSingleton::getDbm();
        $db->query("UPDATE `orders_new` SET `price_summ`='$order_sum' WHERE `id`='$order_id' LIMIT 1;");
        return true;
    }

    function saveClientOrder($client_id, $user_id, $cookie, $tpoint_id, $cash_id, $name, $email, $phone, $city_id, $comment, $order_info_id) { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        $phone = $client->formatValidPhone($phone);
        // CREATE ORDER
        $r = $db->query("SELECT MAX(`ID`) as maxim FROM `orders_new`;"); $order_id = intval($db->result($r,0,"maxim")) + 1;
        $db->query("INSERT INTO `orders_new` (`id`, `client_id`, `client_user_id`, `cookie_id`, `tpoint_id`, `cash_id`, `name`, `email`, `phone`, `region`, `comment`, `order_info_id`, `price_summ`) 
        VALUES ($order_id, $client_id, $user_id, '$cookie', $tpoint_id, $cash_id, '$name', '$email', '$phone', '$city_id', '$comment', $order_info_id, 0);");
        // CREATE ORDER STR
        $order_sum = $this->finishOrderBasket($order_id);
        // SET ORDER SUM
        $this->updateOrderSum($order_id, $order_sum);
        return $order_id;
    }

    function saveOrderClient($user_id, $name, $email, $pass) { $db = DbSingleton::getDbm();
        $client = new ClientClass;
        $db->query("UPDATE `A_CLIENTS_USERS` SET `name`='$name', `email`='$email', `pass`='$pass' WHERE `id`='$user_id' LIMIT 1;");
        $client->loginOrderClient($user_id);
        return true;
    }

    function saveClientOrderInfo($client_id, $user_id, $city_id, $delivery_id, $department_text, $payment_id, $delivery_info=[], $recipient_name="", $recipient_phone="") { $db = DbSingleton::getDbm();
        $street = $delivery_info["street"];
        $house = $delivery_info["house"];
        $porch = $delivery_info["porch"];
        $department = $delivery_info["department"];
        $express = $delivery_info["express"];
        $express_info = $delivery_info["express_info"];

        if ($street=="undefined") $street="";
        if ($house=="undefined") $house="";
        if ($porch=="undefined") $porch="";
        if ($express_info=="undefined") $express_info="";

        $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `CLIENT_ID`=$client_id AND `USER_ID`=$user_id AND `CITY_ID`=$city_id AND `DELIVERY_ID`=$delivery_id AND `PAYMENT_ID`=$payment_id AND `DEL_STREET`='$street' AND `DEL_HOUSE`='$house' AND `DEL_PORCH`='$porch' AND `DEL_DEPARTMENT`='$department' AND `DEL_EXPRESS`=$express AND `DEL_EXPRESS_INFO`='$express_info' LIMIT 1;"); $n = $db->num_rows($r);
        if ($n==0) {
            $r = $db->query("SELECT MAX(`ID`) as maxim FROM `ORDERS_CLIENT_INFO`;"); $order_info_id = intval($db->result($r,0,"maxim")) + 1;
            $db->query("INSERT INTO `ORDERS_CLIENT_INFO` (`ID`, `CLIENT_ID`, `USER_ID`, `CITY_ID`, `DELIVERY_ID`, `PAYMENT_ID`, `DEL_NAME`, `DEL_PHONE`, `DEL_STREET`, `DEL_HOUSE`, `DEL_PORCH`, `DEL_DEPARTMENT`, `DEL_DEPARTMENT_TEXT`, `DEL_EXPRESS`, `DEL_EXPRESS_INFO`) 
            VALUES ($order_info_id, $client_id, $user_id, $city_id, $delivery_id, $payment_id, '$recipient_name', '$recipient_phone', '$street', '$house', '$porch', '$department', '$department_text', $express, '$express_info');");
        } else {
            $order_info_id = $db->result($r, 0, "ID");
        }

        return $order_info_id;
    }

    function getDeliveryInfoCaption($delivery_id, $street, $house, $porch, $department_text, $express, $express_info) {
        $info = "";

        switch ($delivery_id) {
            case 1: {
                break;
            }
            case 2: {
                if ($porch!="") $porch = ", {entrance_cap} $porch";
                $info = "{address_cap}: {street_cap} $street, {house_cap} $house $porch";
                break;
            }
            case 3: {
                $info = "{address_cap}: {street_cap} $street, {house_cap} $house";
                break;
            }
            case 4: {
                $info = "$department_text";
                break;
            }
            case 5: {
                if ($porch!="") $porch = ", {entrance_cap} $porch";
                $info = "{address_cap}: {street_cap} $street, {house_cap} $house $porch";
                break;
            }
            case 6: {
                $info = "$department_text";
                break;
            }
            case 7: {
                $delivery_express_text = $this->getDepartmentExpressName($express);
                $info = "{delivery_type_7}: $delivery_express_text, {department_cap}: $express_info";
                break;
            }
            default: {
                break;
            }
        }

        return $info;
    }

    function getDeliveryCaption($delivery_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_DELIVERY` WHERE `ID`='$delivery_id' LIMIT 1;");
        $text = $db->result($r, 0, "TEXT");
        $text = $this->replaceLang($text);
        return $text;
    }

    function getPaymentCaption($payment_id) { $db = DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_PAYMENT` WHERE `ID`='$payment_id' LIMIT 1;");
        $text = $db->result($r, 0, "TEXT");
        $text = $this->replaceLang($text);
        return $text;
    }

    function dropClientOrderInfo($id) { $db = DbSingleton::getDbm();
        $db->query("UPDATE `ORDERS_CLIENT_INFO` SET `STATUS`=0 WHERE `ID`='$id';");
        return true;
    }

    function setClientOrderInfo($id) { $db = DbSingleton::getDbm();
        $r = $db->query("SELECT * FROM `ORDERS_CLIENT_INFO` WHERE `ID`='$id' AND `STATUS`=1;");

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

        $delivery_info = ["street"=>$street, "house"=>$house, "porch"=>$porch, "department"=>$department, "express"=>$express, "express_info"=>$express_info];

        return array("city_id"=>$city_id, "delivery_id"=>$delivery_id, "payment_id"=>$payment_id, "delivery_info"=>$delivery_info, "recipient_name"=>$recipient_name, "recipient_phone"=>$recipient_phone);
    }

    function validDeliveryFields($delivery, $delivery_type) {
        $result = true;
        $fields = [];
        $street = $delivery_type["street"];
        $house = $delivery_type["house"];
        $department = $delivery_type["department"];
        $department_id = $delivery_type["department_id"]; // department ID
        $delivery_express = $delivery_type["delivery_express"]; // express ID
        $delivery_express_department = $delivery_type["delivery_express_department"];

        switch ($delivery) {
            case 1: {
                break;
            }
            case 2: {
                if (($street=="" || $street=="undefined") || ($house=="" || $house=="undefined")) {
                    if ($street=="" || $street=="undefined") array_push($fields, "street");
                    if ($house=="" || $house=="undefined") array_push($fields, "house");
                    $result = false;
                }
                break;
            }
            case 3: {
                if (($street=="" || $street=="undefined") || ($house=="" || $house=="undefined")) {
                    if ($street=="" || $street=="undefined") array_push($fields, "street");
                    if ($house=="" || $house=="undefined") array_push($fields, "house");
                    $result = false;
                }
                break;
            }
            case 4: {
                if ($department_id=="0" || $department_id=="undefined") {
                    if ($department_id=="0" || $department_id=="undefined") array_push($fields, "department");
                    $result = false;
                }
                break;
            }
            case 5: {
                if (($street=="" || $street=="undefined") || ($house=="" || $house=="undefined")) {
                    if ($street=="" || $street=="undefined") array_push($fields, "street");
                    if ($house=="" || $house=="undefined") array_push($fields, "house");
                    $result = false;
                }
                break;
            }
            case 6: {
                if ($department=="0" || $department=="undefined") {
                    if ($department=="0" || $department=="undefined") array_push($fields, "department");
                    $result = false;
                }
                break;
            }
            case 7: {
                if (($delivery_express_department=="" || $delivery_express_department=="undefined") || ($delivery_express=="0" || $delivery_express=="undefined")) {
                    if ($delivery_express_department=="" || $delivery_express_department=="undefined") array_push($fields, "delivery_express_department");
                    if ($delivery_express=="0" || $delivery_express=="undefined") array_push($fields, "delivery_express");
                    $result = false;
                }
                break;
            }
            default: {
                break;
            }
        }

        return array($result, $fields);
    }

    function getOrderTotal($total) {
        $exrate = new ExRateClass; $cur = $exrate->getCurrentKours(); $cur_cap = $exrate->getKoursSymbol($cur);
        $list = "<div class=\"cart-table-row cart-table-row-offset\">
            <div class=\"cart-table-cell cart-table-cell__label\">{total_cap}</div>
            <div class=\"cart-table-cell cart-table-cell__price\">$total $cur_cap</div>
        </div>";
        return $list;
    }

    function hideOrderInfo($name, $phone, $city) {
        $list = "<span>$name, $phone, $city</span> <a onclick=\"editFields();\">{edit_cap}</a>";
        $list = $this->replaceLang($list);
        return $list;
    }

    function getBasketOrder($delivery_id = 0) {
        $exrate = new ExRateClass;
        $cur = $exrate->getCurrentKours(); $cur_cap = $exrate->getKoursSymbol($cur);
        $form = $this->getHtmlForm("orders/basket");
        list($basket_range, $basket_total) = $this->getBasketOrderRange();

        list($delivery_total, $delivery_total_text) = $this->getDeliveryPrice($delivery_id);

        $total = $basket_total + $delivery_total;
        if ($delivery_id==0) {
            $form = str_replace("{basket_order_delivery_price}", "", $form);
            $form = str_replace("{basket_order_price}", "", $form);
            $form = str_replace("{basket_button_status}", "none", $form);
        }
        $form = str_replace("{basket_content}", $basket_range, $form);
        $form = str_replace("{basket_full_price}", $basket_total." $cur_cap", $form);
        $form = str_replace("{basket_order_delivery_price}", $delivery_total_text, $form);
        $form = str_replace("{basket_order_price}", $this->getOrderTotal($total), $form);
        $form = str_replace("{basket_button_status}", "", $form);
        $form = $this->replaceLang($form);
        return $form;
    }

    function getDeliveryPrice($delivery_id) {
        $exrate = new ExRateClass; $client = new ClientClass; $cat = new CatalogueClass;
        $cur = $exrate->getCurrentKours(); $cur_cap = $exrate->getKoursSymbol($cur);
        $price = 0; $price_cur = 0;

        // NOVA POSHTA
        if ($delivery_id==4) {
            $price = $cat->getArticlePrice(100060075); // NP
        }
        if ($delivery_id==5) {
            $price = $cat->getArticlePrice(100060076); // NP KURER
        }

        if ($price>0) {
            $price_cur = $exrate->getKoursFromUAH($price, $cur);
            $price_cur = $client->getClientPriceRounding($this->getClient(), $price_cur);
        }

        if ($price_cur>0) {
            $del_cap = "$price_cur $cur_cap";
        } else {
            $del_cap = "{free_cap}";
        }

        // carrier tariff
        if (in_array($delivery_id, [6,7])) {
            $price = 0;
            $del_cap = "{carrier_conditions}";
        }

        if (!$client->checkRetailClientCategory($this->getClient())) {
            $price = 0;
            $del_cap = "{carrier_conditions}";
        }

        $del_cap = $this->replaceLang($del_cap);
        $list="<div class=\"cart-table-row cart-table-row-offset\">
            <div class=\"cart-table-cell cart-table-cell__label\">{delivery_cap}</div>
            <div class=\"cart-table-cell cart-table-cell__price\">$del_cap</div>
        </div>";

        return array($price, $list);
    }

    function getBasketOrderRange() { $db=DbSingleton::getTokoDb();
        $client=new ClientClass; $exrate=new ExRateClass; $showform=new FormClass;
        $client_id=$client->getClient()[0]; $where=$client->getClientWhere();
        $cur=$exrate->getCurrentKours(); $cur_cap=$exrate->getKoursSymbol($cur);
        $list=""; $sum_total=0;
        $r = $db->query("SELECT * FROM `basket` WHERE $where AND `status_checked`=1 ORDER BY `date_create` DESC;"); $n = $db->num_rows($r);
        if ($n>0) {
            for ($i = 1; $i <= $n; $i++) {
                $art_id = $db->result($r, $i - 1, "art_id");
                $brand_id = $db->result($r, $i - 1, "brand_id");
                $art_name = $this->getArticleDispl($art_id);
                $brand_name = $this->getBrandName($brand_id);
                $text = $this->getArticleName($art_id);
                $amount = $db->result($r, $i - 1, "amount");
                $price = $db->result($r, $i - 1, "price");
                $price = $exrate->getKoursPrice($price, $cur);
                if ($cur==1) $price = $client->getClientPriceRounding($client_id, $price);
                $full_price = $price * $amount;
                if ($cur==1) $full_price = $client->getClientPriceRounding($client_id, $full_price);
                $sum_total+=$full_price;
                $name = "$text $brand_name ($art_name)";
                $img = $showform->getArticleActivePhoto($art_id);
                $list.="<div class=\"cart-table-row\">
                    <div class=\"cart-table-cell cart-table-cell__photo\"><img src=\"$img\" alt=\"$name\"></div>
                    <div class=\"cart-table-cell cart-table-cell__text\">
                        <div class=\"cart-table-cell cart-table-cell__name\">$name</div>
                        <div class=\"cart-table-cell cart-table-cell__summ\">
                            <div class=\"cart-table-cell cart-table-cell__amount\">$amount {amount_abbr}.</div>
                            <div class=\"cart-table-cell cart-table-cell__summary\">$full_price $cur_cap</div>
                        </div>
                    </div>
                </div>";
            }
        } else {
            $list = "<div class=\"cart-table-row\">{empty_cap}</div>";
        }
        $list = $this->replaceLang($list);
        return array($list, $sum_total);
    }

    function setDeliveryExpressDepartment($delivery_express) { $db=DbSingleton::getTokoDb();
        $r = $db->query("SELECT * FROM `T2_DELIVERY_EXPRESS` WHERE `ID`='$delivery_express' LIMIT 1;");
        $text_type = $db->result($r, 0, "TEXT_TYPE");
        $text_type = $this->replaceLang($text_type);
        return $text_type.":";
    }

    /*==== /NEW ORDER FORM ====*/

    /*==== NOVA POSHTA API ====*/
    function getCitiesMainSelect($user_city = 0) { $db=DbSingleton::getTokoDb();
        $language = new LangClass;
        $lang_id = $language->getLanguage();
        $postfix = ""; $where_user_city = "";
        if ($lang_id==1 || $lang_id==3) $postfix = "_RU";
        $list = "";
        if ($user_city>0) {
            $where_user_city = "OR `CITY_ID`='$user_city'";
        }

        $r=$db->query("SELECT * FROM `T2_LOCATION` WHERE `REGION_NAME`='' $where_user_city ORDER BY `CITY_NAME$postfix` ASC;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $city_id = $db->result($r, $i-1, "CITY_ID");
            $city_name = $db->result($r, $i-1, "CITY_NAME_CLEAR$postfix");
            if ($user_city>0 && $user_city==$city_id) $sel = "selected"; else $sel = "";
            $list.="<option value='$city_id' $sel>$city_name</option>";
        }
        return $list;
    }

    function getCityVal($search_text) { $db=DbSingleton::getTokoDb();
        $language = new LangClass;
        $lang_id = $language->getLanguage();
        $mas = [];
        $postfix = "";
        if ($lang_id==1 || $lang_id==3) $postfix = "_RU";
        $r=$db->query("SELECT * FROM `T2_LOCATION` WHERE `CITY_NAME_CLEAR$postfix` LIKE '$search_text%' ORDER BY `CITY_NAME$postfix`;"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $city_id = $db->result($r, $i-1, "CITY_ID");
            $city_name = $db->result($r, $i-1, "CITY_NAME$postfix");
            $region_name = $db->result($r, $i-1, "REGION_NAME$postfix");
            $state_name = $db->result($r, $i-1, "STATE_NAME$postfix");
            $city_cap = "$city_name ($state_name обл., $region_name р-он)";
            $mas[$i] = ["id"=>$city_id, "value"=>$city_cap];
        }
        return $mas;
    }

    function setCityNPVal($city_id) { $db=DbSingleton::getTokoDb();
        $list = "";
        $r = $db->query("SELECT * FROM `T2_LOCATION` WHERE `CITY_ID`='$city_id' LIMIT 1;");
        $city_name = $db->result($r, 0, "CITY_NAME_CLEAR");
        $state_name = $db->result($r, 0, "NEWPOST_AREA");
        $r = $db->query("SELECT * FROM `T2_CITY_NOVA` WHERE `CITY_NAME` LIKE '$city_name%' AND `AREA_NAME` LIKE '$state_name%';"); $n=$db->num_rows($r);
        for ($i=1; $i<=$n; $i++) {
            $ref = $db->result($r, $i - 1, "CITY_REF");
            $name = $db->result($r, $i - 1, "CITY_NAME");
            $area_name = $db->result($r, $i - 1, "AREA_NAME");
            $list.="<option value='$ref'>$name ($area_name)</option>";
        }
        return $list;
    }

    function getNovaPoshtaCitiesSelect() {
        $list = "";
        $np = new \LisDev\Delivery\NovaPoshtaApi2('656d2934ac1411fdb377a1d6de96fd92');
        $arr = $np->getCities()['data'];
        foreach ($arr as $val) {
            $name = iconv("UTF-8", "windows-1251", $val["Description"]);
            $ref = $val["Ref"];
            $list.="<option value='$ref'>$name</option>";
        }
        return $list;
    }

    function getNovaPoshtaWarehousesSelect($ref) {
        $list = "<option value=\"0\">{not_chosen}</option>";
        $list = $this->replaceLang($list);
        $np = new \LisDev\Delivery\NovaPoshtaApi2('656d2934ac1411fdb377a1d6de96fd92');
        $arr = $np->getWarehouses($ref)['data'];
        foreach ($arr as $val) {
            $name = iconv("UTF-8", "windows-1251", $val["Description"]);
            $war_ref = $val["Ref"];
            $list.="<option value='$war_ref'>$name</option>";
        }
        return $list;
    }

    /*==== /NOVA POSHTA API ====*/

}
