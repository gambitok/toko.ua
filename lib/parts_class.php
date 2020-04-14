<?php

class PartsClass extends CatalogueClass {

    use Helper;
    use Variables;
    public $products_on_page=25;

    function getHeadFromStr($str_id) { $db = DbSingleton::getTokoDb();
        $r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD_STR` WHERE `STR_ID`='$str_id' LIMIT 1;");
        $head_id=$db->result($r,0,"HEAD_ID");
        return $head_id;
    }

    function getInitForm($str_id) {
        $result = $this->initPartsTable($str_id);
        $form = "<div class='content'>
            $result
        </div>";
        return $form;
    }

    function initPartsTable($str_id) {

        $db = DbSingleton::getTokoDb();
        $dbc = DbSingleton::getTokoCacheDb();

        if ($this->checkTable($str_id)>0) $dbc->query("UPDATE `XX_TABLE_TREE_$str_id` SET `status`=0 WHERE 1;");

        $arts = [];
        $r = $db->query("SELECT `ART_ID` FROM `T2_TREE` WHERE `STR_ID`='$str_id' GROUP BY `ART_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id = $db->result($r,$i-1,"ART_ID");
            $arts[$i]["art_id"]=$art_id;
        }

        foreach ($arts as $key=>$values) {
            $art_id = $values["art_id"];

            $r=$db->query("SELECT t2a.ART_ID, t2a.BRAND_ID, t2asc.AMOUNT
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc ON t2asc.ART_ID=t2a.ART_ID
            WHERE t2a.ART_ID IN ($art_id) AND (t2asc.AMOUNT!=NULL OR t2asc.AMOUNT!=0) 
            GROUP BY t2a.ART_ID, t2asc.STORAGE_ID
            UNION ALL
            SELECT t2a.ART_ID, t2a.BRAND_ID, t2si.stock_suppl
            FROM `T2_ARTICLES` t2a
                LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si ON (t2si.art_id=t2a.ART_ID AND t2si.status=1)
            WHERE t2a.ART_ID IN ($art_id) AND (t2si.stock_suppl!=NULL OR t2si.stock_suppl!=0)
            GROUP BY t2a.ART_ID, t2si.client_storage_id;");

            $stock=$db->num_rows($r);
            $brand_id=$db->result($r,0,"BRAND_ID");

            $arts[$key]["brand_id"]=$brand_id;

            if ($stock==0) {
                unset($arts[$key]);
            }
        }

        $dbc->query("CREATE TABLE IF NOT EXISTS `XX_TABLE_TREE_$str_id` 
        (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `art_id` INT(100) NOT NULL,
            `brand_id` INT(100),
            `status` TINYINT(2),
            PRIMARY KEY (`id`)
        ) ENGINE = MYISAM;");

        $count_add=0; $count_upd=0;
        foreach ($arts as $key=>$values) {
            $art_id = $values["art_id"];
            $brand_id = $values["brand_id"];
            $r=$dbc->query("SELECT COUNT(`ART_ID`) as count_art FROM `XX_TABLE_TREE_$str_id` WHERE `ART_ID`='$art_id';"); $n=$dbc->result($r,0,"count_art")+0;
            if ($n==0) {
                $dbc->query("INSERT INTO `XX_TABLE_TREE_$str_id` (`art_id`, `brand_id`, `status`) VALUES ('$art_id', '$brand_id', 1);");
                $count_add++;
            } else {
                $dbc->query("UPDATE `XX_TABLE_TREE_$str_id` SET `status`=1 WHERE `ART_ID`='$art_id';");
                $count_upd++;
            }
        }

        $r=$dbc->query("SELECT COUNT(*) as count_nulls FROM `XX_TABLE_TREE_$str_id` WHERE `status`=0"); $count_del=$dbc->result($r,0,"count_nulls")+0;
        $dbc->query("DELETE FROM `XX_TABLE_TREE_$str_id` WHERE `status`=0;");

        return "UPDATED: $count_upd, ADDED: $count_add, DELETED: $count_del";
    }

    function showPartsForm() {
        $form=$this->getHtmlForm("parts");
        $list=$this->getPartsList();
        $form=str_replace("{parts_name}","{spare_parts_catalog_cap}",$form);
        $form=str_replace("{parts_list}",$list,$form);
        return $form;
    }

    function getPartsList() { $db = DbSingleton::getTokoDb();
        $list="<ul>";
        $r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD` WHERE `STATUS`=1;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $head_id = $db->result($r,$i-1,"HEAD_ID");
            $name = $db->result($r,$i-1,"TEX_RU");
            $head_list = $this->getPartsStrList($head_id);
            $list.="<li>
                <b>$name</b>
                $head_list
            </li>";
        }
        $list.="</ul>";
        return $list;
    }

    function getPartsStrList($head_id) { $db = DbSingleton::getTokoDb();
        $list="<ul>";
        $r=$db->query("SELECT * FROM `T2_GROUP_TREE_HEAD_STR` WHERE `HEAD_ID`='$head_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $str_id = $db->result($r,$i-1,"STR_ID");
            $name = $db->result($r,$i-1,"TEX_RU");
            $link = $db->result($r,$i-1,"TEX_LINK");
            $check = $this->checkTable($str_id);
            if ($check>0) {
                $check_form="<span class='span-red'><i class='fa fa-edit'></i> UPDATE</span>";
                $col="($check)";
            } else {
                $check_form="<span class='span-grey'><i class='fa fa-download'></i> CREATE</span>";
                $col="";
            }
            $list.="<li>
                <a href='/parts/init/$str_id/'>
                    $check_form
                </a>
                <a href='/parts/$link/'>$name $col</a>
            </li>";
        }
        $list.="</ul>";
        return $list;
    }

    function checkTable($str_id) { $dbc = DbSingleton::getTokoCacheDb();
        $r=$dbc->query("SHOW TABLES LIKE 'XX_TABLE_TREE_$str_id';"); $n=$dbc->num_rows($r);
        if ($n>0) {
            $r=$dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `XX_TABLE_TREE_$str_id` WHERE 1;");
            $n=$dbc->result($r,0,"col_arts");
        }
        return $n;
    }

    function getPartsCount($str_id, $brandy) { $dbc = DbSingleton::getTokoCacheDb();
        $where_brands="";
        if (!empty($brandy)) {
            //$search=new SearchClass;
            //$brand_list=$search->getBrandsList($brandy);
            //if ($brand_list!="")
            $brand_list=implode(",", $brandy);
            if ($brand_list!="") $where_brands="WHERE `brand_id` IN ($brand_list)";
        }
        $r=$dbc->query("SHOW TABLES LIKE 'XX_TABLE_TREE_$str_id';"); $n=$dbc->num_rows($r);
        if ($n>0) {
            $r=$dbc->query("SELECT COUNT(`art_id`) as col_arts FROM `XX_TABLE_TREE_$str_id` $where_brands;");
            $n=$dbc->result($r,0,"col_arts");
        }
        return $n;
    }

    function showPartsCatalogue($str_id, $page=1, $brandy=[]) { $dbc = DbSingleton::getTokoCacheDb();
        $automan=new AutoClass;
        $limit = $this->getSearchLimit($page);
        $str_text = $automan->getStrNewDescr($str_id); if ($str_text=="") $str_text = $automan->getStrDescr($str_id);

        $where_brands="";
        if (!empty($brandy)) {
            $brand_list=implode(",", $brandy);
            if ($brand_list!="") $where_brands="WHERE `brand_id` IN ($brand_list)";
        }

        $r=$dbc->query("SELECT * FROM `XX_TABLE_TREE_$str_id` $where_brands $limit;"); $n=$dbc->num_rows($r); $arts=[];
        for ($i=1;$i<=$n;$i++) {
            $art_id=$dbc->result($r,$i-1,"art_id");
            array_push($arts, $art_id);
        }

        $where_arts=implode(",",array_unique($arts));
        list($list,,$filters,,$brands)=$this->searchList($where_arts, 1, 1);//

        $form=$this->getHtmlForm("parts_list");
        $form=str_replace("{parts_name}",$str_text,$form);
        $form=str_replace("{parts_list}",$list,$form);

        return array($form,$filters,$brands);
    }

    function getPartsBrandForm($str_id) { $dbc = DbSingleton::getTokoCacheDb();
        $list="<ul class=\"list-inline\">";
        $r=$dbc->query("SELECT `brand_id` FROM `XX_TABLE_TREE_$str_id` GROUP BY `brand_id`;"); $n=$dbc->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $brand_id=$dbc->result($r,$i-1,"brand_id");
            $brand_name=$this->getBrandName($brand_id);
            $brand_link=$this->getBrandLink($brand_id);
            $label="<i class=\"far fa-square\"></i>";
            if (!empty($brands_ch)) {
                if (in_array($brand_id,$brands_ch)) $label="<i class=\"fa fa-check-square\"></i>";
            }
            $list.="<li><a class=\"pointer\" href=\"?brandy=$brand_link\">$label $brand_name</a></li>";
        }
        $list.="</ul>";
        return $list;
    }

    function initPartsArts($str_id) { $dbc=DbSingleton::getTokoCacheDb();
        $art_ids=[];

        $r=$dbc->query("SELECT * FROM `XX_TABLE_TREE_$str_id` WHERE 1;"); $n=$dbc->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id = $dbc->result($r,$i-1,"art_id");
            array_push($art_ids, $art_id);
        }

        $where_arts = implode(",", $art_ids);

        return $where_arts;
    }

    function getSearchLimit($page) {
        $count = $this->products_on_page;
        $off = $count * $page - $count;
        $off>=0 ? $limit = " LIMIT $count OFFSET $off" : $limit = "";
        return $limit;
    }

    function getPartsPaginationForm($n, $page) {
        $count = $this->products_on_page;
        $pages_count = ceil($n / $count);
        if ($n<$count) $pages_count=1;
        $pagination="";

        $min_count = 5;
        $max_count = $pages_count-$min_count+1;
        $pred_page = $page-1; $next_page = $page+1;
        if ($page==1) $disabled_pred="disabled"; else $disabled_pred="";
        if ($page==$pages_count) $disabled_next="disabled"; else $disabled_next="";

        if ($pages_count>5) {

            if ($page<$min_count) {
                for ($i=1; $i<=$min_count; $i++) {
                    $i==$page ? $active="active" : $active="";
                    $pagination.="<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
                }
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

            if ($page>$max_count) {
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                for ($i=$max_count; $i<=$pages_count; $i++) {
                    $i==$page ? $active="active" : $active="";
                    $pagination.="<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
                }
            }

            if ($page>=$min_count && $page<=$max_count) {
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=1\">1</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";

                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pred_page\">$pred_page</a></li>";
                $pagination.="<li class=\"page-item active\"><a class=\"page-link\" href=\"?page=$page\">$page</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$next_page\">$next_page</a></li>";

                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"#\">...</a></li>";
                $pagination.="<li class=\"page-item\"><a class=\"page-link\" href=\"?page=$pages_count\">$pages_count</a></li>";
            }

        } else {
            for ($i=1; $i<=$pages_count; $i++) {
                $i==$page ? $active="active" : $active="";
                $pagination.="<li class=\"page-item $active\"><a class=\"page-link\" href=\"?page=$i\">$i</a></li>";
            }
        }

        $list="<div class=\"row\">
            <nav aria-label=\"Page navigation\" class=\"img-center\" style='margin-top: 2em'>
                <ul class=\"pagination\">
                    <li class=\"page-item $disabled_pred\"><a class=\"page-link\" href=\"?page=$pred_page\"><i class='fa fa-chevron-left'></i> <span class='span-media'>{previous_cap}</span></a></li>
                    $pagination
                    <li class=\"page-item $disabled_next\"><a class=\"page-link\" href=\"?page=$next_page\"><span class='span-media'>{next_cap}</span> <i class='fa fa-chevron-right'></i></a></li>
                </ul>
            </nav>
        </div>";

        if ($pages_count==1) $list="";

        $list=$this->replaceLang($list);

        return $list;
    }

}