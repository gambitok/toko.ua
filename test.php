<?php
function searchList($where_art_id_str,$likeart,$type_filter,$likebrand) { $db = DbSingleton::getTokoDb();
    $kours=new ExRateClass;
    session_start(); $temp_key=session_id();
    $filters=$mas=$brands=array(); $list_brand=$suppl_id='';
    $filters['max_price']=$filters['max_stock']=$filters['max_dd']=$count=$main_brand=0;
    $cur=$_SESSION['currency']; $_SESSION['price_null']=true; $tpoint=$_SESSION['tpoint'];
    $linka=findLinks(); $template_id=$linka[2];

    list($error,$jsFilterModel,$list)=$this->getSearchMessages($type_filter,$template_id);

    if ($where_art_id_str!="") {
        // temporary table for results
        $this->createTemporarySearchTable($temp_key);
        // table results
        $r=$this->getSearchList($where_art_id_str,$likeart,$likebrand,"",""); $n=$db->num_rows($r);
        // header of table
        $list=$this->drawHeaderSearchList("",$type_filter);

        if ($n>0) {
            for ($i=1;$i<=$n;$i++){
                $art_id = $db->result($r,$i-1,"ART_ID");
                $brand_id = $db->result($r,$i-1,"BRAND_ID");
                $brand = $db->result($r,$i-1,"BRAND_NAME");
                $name = $db->result($r,$i-1,"ARTICLE_NR_DISPL");
                $text = $db->result($r,$i-1,"NAME");
                $return_days = $db->result($r,$i-1,"return_delay");
                $stock = intval($db->result($r,$i-1,"AMOUNT"));
                if ($stock==0) {
                    $stock = intval($db->result($r,$i-1,"stock_suppl"));
                    $suppl_id = $db->result($r,$i-1,"suppl_id");
                }
                $storage_id = $db->result($r,$i-1,"storage_id");
                if ($storage_id==0) $storage_id = $db->result($r,$i-1,"client_storage_id");
                // price
                $price = $this->getArticlePrice($art_id);
                if ($price==0) $price = $this->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
                $price = $kours->getKoursPrice($price,$cur);
                $filter_price = $price;
                // delivery
                list($del,$dd)=$this->getTpointDeliveryInfo($tpoint,$storage_id);
                if ($del=="") list($del,$dd)=$this->getTpointSupplDeliveryInfo($tpoint,$suppl_id,$storage_id);
                if ($del=="") $del="$this->err2";
                // filters
                if ($filter_price>$filters['max_price']) $filters['max_price'] = ceil($filter_price);
                if ($dd>$filters['max_dd']) $filters['max_dd'] = $dd;

                $format_name = str_replace(str_split('.,+-\/:*?"<>| '),"", $name); if ($stock=="") $stock=0;
                if (($name==$likeart || $format_name==$likeart)&&$brand_id==$likebrand) $status = 2; else
                    if ($suppl_id=='' || $suppl_id==0) $status = 1; else $status = 0;

                // repeat in requsted articles, when toko+suppl storages
                $rr=$db->query("select * from `TEMP_ARTICLES_$temp_key` where `art_id`=$art_id"); $nn=$db->num_rows($rr);
                if ($nn>0) {
                    for ($ii=1;$ii<=$nn;$ii++) {
                        $stock2 = $db->result($rr, $ii - 1, "stock");
                        $storage_id2 = $db->result($rr, $ii - 1, "storage_id");
                        $price2 = $db->result($rr, $ii - 1, "price");
                        if ($stock2==$stock && $storage_id2==$storage_id && $price2==$price) { $stock=0; $suppl_id=0;}
                    }
                }
                if ($price!=0 || (($name==$likeart || $format_name==$likeart)&&$brand_id==$likebrand)) {
                    if ($stock>0 || (($name==$likeart || $format_name==$likeart)&&$brand_id==$likebrand)) {
                        $db->query("INSERT INTO `TEMP_ARTICLES_$temp_key` (`art_id`, `name`, `brand_id`, `brand`, `text`, `del`, `stock`, `price`, `dd`, `suppl_id`, `return_days`, `status`,`storage_id`) VALUES ('$art_id', '$name', '$brand_id', '$brand', '$text', '$del', $stock, $price, '$dd', '$suppl_id', '$return_days', '$status', '$storage_id');");
                        if ($type_filter==1) { if ($i==1) $main_brand=$brand_id; }
                        if ($brand!="") {
                            $brands[$art_id]['brand'] = $brand;
                            $brands[$art_id]['brand_id'] = $brand_id;
                            if(!empty ($brands[$art_id]['price'])) { if($price<$brands[$art_id]['price']) $brands[$art_id]['price'] = $price; } else $brands[$art_id]['price'] = $price;
                        }
                    }
                }
            }

            $r=$db->query("SELECT * FROM `TEMP_ARTICLES_$temp_key` ORDER BY status desc,name;"); $n=$db->num_rows($r);

            if ($n==1) {
                $stock = $db->result($r, 0, "stock");
                $price = $db->result($r, 0, "price");
                if ($stock==0 && $price==0) {
                    $list="<div class='container'><div class='row'><h2>$this->err1</h2></div></div></div>";
                    // exit from search
                    return array($list, "", "", 0);
                }
            }

            for ($i=1;$i<=$n;$i++){
                $art_id = $db->result($r,$i-1,"art_id");
                $name = $db->result($r,$i-1,"name");
                $brand_id = $db->result($r,$i-1,"brand_id");
                $brand = $db->result($r,$i-1,"brand");
                $text = $db->result($r,$i-1,"text");
                $del = $db->result($r,$i-1,"del");
                $stock = $db->result($r,$i-1,"stock");
                $price = $db->result($r,$i-1,"price");
                $dd = $db->result($r,$i-1,"dd");
                $suppl_id = $db->result($r,$i-1,"suppl_id");
                $return_days = $db->result($r,$i-1,"return_days");
                $storage_id = $db->result($r,$i-1,"storage_id");
                $mas[$art_id][$i] = ["name"=>$name, "brand_id"=>$brand_id, "brand"=>$brand, "text"=>$text, "del"=>$del, "stock"=>$stock, "price"=>$price, "dd"=>$dd, "suppl_id"=>$suppl_id, "return_days"=>$return_days, "storage_id"=>$storage_id];
            }

            $db->query("DROP TEMPORARY TABLE IF EXISTS `TEMP_ARTICLES_$temp_key`;");

            // get filter brand list
            $list_brand=$this->getListBrand($brands,$main_brand,$cur,$jsFilterModel,[]);

            // delete empty stocks and prices
            $mas=$this->deleteEmptyPosition($mas);

            // sort by delivery and price
            foreach ($mas as $mas_key=>$mas_val) { $mas[$mas_key]=$this->multiSort($mas[$mas_key], 'dd', 'price'); }

            // sort like: first - min delivery, second - min price, else - default
            $mas=$this->sortByMinStock($mas);

            // show other storages
            list($ll,$class,$hide,$border,$none)=$this->showOtherStorages($mas,$cur);

            // show search list
            $list=$this->outSearchList($list,$error,$mas,$type_filter,$template_id,$likeart,$likebrand,$ll,$class,$hide,$border,$none,0);
        }
        $count=count($mas); if ($count<1) { $list="$error"; unset($list_brand); $list_brand=array(); unset($filters); $filters=array(); }
    }
    return array($list,$list_brand,$filters,$count);
}