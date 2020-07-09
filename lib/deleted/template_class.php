<?php

class TemplateClass {

    use Helper;
    use Variables;

    protected $_activeProducts = [];
    protected $_currentPageFilters = [];
    protected $_activeFilters = [];
    protected $_currentPage = 0;
    protected $_countPages = 0;
    protected $_productOnPage = 25;
    protected $_clearActiveFilters = 0;

    public function getActiveProducts() {
        return $this->_activeProducts;
    }

    public function getProductOnPage() {
        return $this->_productOnPage;
    }

    /* get ALL PRODUCTS
     * product art_id with param+values
     * */
    public function getAllProducts($template_id)
    {
        $db=DbSingleton::getTokoDb();
        $cat=new CatalogueClass;
        $products=[];

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' GROUP BY `ART_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $brand_id=$this->getArticleBrand($art_id);
            $r2=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n2=$db->num_rows($r2);
            for ($j=1;$j<=$n2;$j++) {
                $param_id=$db->result($r2,$j-1,"PARAM_ID");
                $products[$art_id][0]=[0,$brand_id];
                $products[$art_id][$param_id]=[0];
            }
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            array_push($products[$art_id][$param_id],$value_id);
        }

        foreach ($products as $art_id=>$product) {
            $validate_art_count=0; $max_price_art=0;
            list($suppl_array,$storage_array,$stock_array,$last)=$this->getCatalogueSearchParams($art_id);
            for ($j=1;$j<=$last;$j++) {
                $suppl_id=$suppl_array[$j];
                $storage_id=$storage_array[$j];
                $stock = $stock_array[$j];
                if ($suppl_id==0) $price = $cat->getArticlePrice($art_id); else $price = $cat->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
                if ($price>0 && $stock>0) {
                    if ($price>$max_price_art) $max_price_art=$price;
                    $validate_art_count++;
                }
            }
            if ($validate_art_count==0) {
                unset($products[$art_id]);
            }
        }

        $products=array_reverse($products,true);

        return $products;
    }

    /* get ALL FILTERS
     * page filters param+values
     * */
    public function getAllFilters($template_id)
    {
        $db=DbSingleton::getTokoDb();
        $filters=[]; $filters["0"]=[];

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $filters[$param_id]=[];
        }

        $products=$this->getAllProducts($template_id); $where_arts="";
        foreach ($products as $art_id=>$product) {
            $brand_id=$this->getArticleBrand($art_id);
            if (!in_array($brand_id, $filters["0"])) array_push($filters["0"],$brand_id);
            $where_arts.="'$art_id',";
        }
        $where_arts=trim($where_arts,",");
        if ($where_arts!="") $where_arts=" AND ART_ID IN ($where_arts)"; else $where_arts="";

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' $where_arts GROUP BY `VALUE_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $value_id = $db->result($r, $i - 1, "VALUE_ID");
            array_push($filters[$param_id],$value_id);
        }

        return $filters;
    }

    /* init `Products`
     * ALL PRODUCTS (with params and price)
     * set _activeProducts
     * */
    public function initProducts($template_id)
    {
        $db=DbSingleton::getTokoDb();
        $cat=new CatalogueClass;

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' GROUP BY `ART_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $brand_id=$this->getArticleBrand($art_id);
            $r2=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n2=$db->num_rows($r2);
            for ($j=1;$j<=$n2;$j++) {
                $param_id=$db->result($r2,$j-1,"PARAM_ID");
                $this->_activeProducts[$art_id][0]=[0,$brand_id];
                $this->_activeProducts[$art_id][$param_id]=[0];
            }
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            array_push($this->_activeProducts[$art_id][$param_id],$value_id);
        }

        foreach ($this->_activeProducts as $art_id=>$product) {
            $validate_art_count=0; $max_price_art=0;
            list($suppl_array,$storage_array,$stock_array,$last)=$this->getCatalogueSearchParams($art_id);
            for ($j=1;$j<=$last;$j++) {
                $suppl_id = $suppl_array[$j];
                $storage_id = $storage_array[$j];
                $stock = $stock_array[$j];
                if ($suppl_id==0) $price = $cat->getArticlePrice($art_id); else $price = $cat->getArticleSupplPrice($art_id,$suppl_id,$storage_id);
                if ($price>0 && $stock>0) {
                    if ($price>$max_price_art) $max_price_art=$price;
                    $validate_art_count++;
                }
            }
            if ($validate_art_count==0) {
                unset($this->_activeProducts[$art_id]);
            }
        }

        $this->_activeProducts=array_reverse($this->_activeProducts,true);
    }

    /* print Products Form
     * check page number
     * get _activeProducts
     * */
    public function getCurrentProducts($template_id)
    {
        $cat=new CatalogueClass;
        $list=""; $where_art_id_str="";

        if (empty($this->_activeProducts)) { $this->initProducts($template_id); }

        $amountProducts=count($this->_activeProducts);

        if ($this->_currentPage==0) $this->_currentPage=1;

        $this->_countPages = ceil($amountProducts / $this->_productOnPage);

        $col=0; $max_page=$this->_currentPage*$this->_productOnPage; $min_page=$max_page-$this->_productOnPage+1;
        if ($max_page>$amountProducts) $max_page=$amountProducts;
        if ($max_page==0) $range_page="0"; else $range_page="$min_page-$max_page";
        $list.="<h2 style=\"font-size: 1em; color: darkgray; margin-bottom: 1em;\">{results_cap}: $range_page {of_cap} $amountProducts</h2>";

        foreach ($this->_activeProducts as $art_id=>$product) {$col++;
            if ($col<=$max_page && $col>=$min_page) {
                $where_art_id_str.="'$art_id'";$where_art_id_str.=",";
            }
        }

        $where_art_id_str=trim($where_art_id_str,",");

        list($list_arts,,,)=$cat->searchList($where_art_id_str, 1, 1);

        $list.="$list_arts <hr>";

        $list.=$this->getTemplatePagin($this->_countPages, $this->_currentPage);

        $list=$this->replaceLang($list);

        return $list;
    }

    /* print Filters
     * get _currentPageFilters
     * */
    public function getFilters($template_id,$cur_page_filters=null)
    {
        $mas=[]; $list=""; $amount_values=0; $amount_max=5;

        if (empty($this->_activeFilters)) { if (!empty($cur_page_filters)) $this->_activeFilters=$cur_page_filters; }

        if ((empty($this->_activeFilters) || $this->_activeFilters==NULL)) $this->initFilters($template_id);
        if (empty($this->_currentPageFilters)) $this->initFilters($template_id);

        foreach ($this->_currentPageFilters as $param_id=>$values) {$i=0;
            if (empty($mas[$param_id])) $mas[$param_id]=[];
            foreach ($values as $value) {$i++;
                if (in_array($value,$this->_activeFilters[$param_id])) $checked=1; else $checked=0;
                if (empty($mas[$param_id][$i])) $mas[$param_id][$i]=[];
                if ($param_id==0) $value_name=$this->getBrandName($value); else $value_name=$this->getCatalogueValueName($value, $template_id);
                $mas[$param_id][$i]=["value_id"=>$value,"value_name"=>$value_name,"checked"=>$checked];
            }
        }

        foreach ($mas as $param_id=>$values) {
            $vc_array_checked=[]; $vc_array_name=[];
            foreach ($values as $key => $row) {
                $vc_array_checked[$key] = $row["checked"];
                $vc_array_name[$key] = $row["value_name"];
            }
            array_multisort($vc_array_checked, SORT_DESC, $vc_array_name, SORT_ASC, $mas[$param_id]);
        }

        foreach ($mas as $param_id=>$values) {
            if (count($values)<=$amount_max) $style_more="height:auto;"; else $style_more="";
            if ($param_id==0) $param_name="{brands_cap}"; else $param_name=$this->getCatalogueParamName($param_id, $template_id);
            if (count($values)>1) $list.="<h2>$param_name</h2><ul id=\"param-$param_id\" class=\"list-inline template-list list-hide\" style=\"margin: 0; $style_more\">";
            foreach ($values as $value) {
                $value_id=$value["value_id"];
                $value_name=$value["value_name"];
                $checked=$value["checked"];
                if ($checked) {$label="<i class=\"fa fa-check-square\"></i>";$style="span-red";} else {$label="<i class=\"far fa-square\"></i>";$style="";}
                if ($value_id>0) {
                    $amount_values++;
                    $list.="<li><a class=\"$style\" onclick=\"addFilterTemplate($param_id,'$value_id');\">$label $value_name</a></li>";
                }
            }
            $amount_values=$amount_values-$amount_max;
            if ($amount_values<=0) $link_more=""; else $link_more="<a onclick=\"toggleListParams(this, $param_id);\"><span class=\"show\">{more_cap} $amount_values</span> <span class=\"none\">{hide_cap}</span></a>";
            $list.="</ul>$link_more";
            $amount_values=0;
        }

        $list=$this->replaceLang($list);
        return $list;
    }

    /* init Filters
     * filters from _activeProducts
     * set _currentPageFilters
     * */
    public function initFilters($template_id)
    {
        $db=DbSingleton::getTokoDb();
        $brands=[0];

        foreach ($this->_activeProducts as $art_id=>$product) {
            foreach ($product as $param=>$values) {
                foreach ($values as $value) {
                    if ($param==0) array_push($brands,$value);
                }
            }
        }

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        $brands=array_unique($brands);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $this->_currentPageFilters[0]=$brands;
            $this->_currentPageFilters[$param_id]=[];
        }

        $where_arts="";
        foreach ($this->_activeProducts as $art_id=>$product) {
            $where_arts.="'$art_id',";
        }
        $where_arts=trim($where_arts,",");
        if ($where_arts!="") $where_arts=" AND `ART_ID` IN ($where_arts)"; else $where_arts="";

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_ARTS` WHERE `TEMPLATE_ID`='$template_id' $where_arts GROUP BY `VALUE_ID`;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $value_id = $db->result($r, $i - 1, "VALUE_ID");
            if (!(in_array(0,$this->_currentPageFilters[$param_id]))) array_push($this->_currentPageFilters[$param_id],0);
            array_push($this->_currentPageFilters[$param_id],$value_id);
        }
    }

    /* print Form
     * get Products Form
     * get Filters Form
     * get _activeProducts
     * */
    public function initProductsForm($activeFilters,$currentPageFilters,$activeProducts,$page,$page_count,$template_id)
    {
        $activeProducts = json_decode($activeProducts);
        $activeProducts = (array)$activeProducts;
        foreach ($activeProducts as $key=>$product) {
            $activeProducts[$key] = (array)$product;
        }

        $this->_activeProducts=$activeProducts;
        $this->_currentPageFilters=$currentPageFilters;
        $this->_activeFilters=$activeFilters;
        $this->_currentPage=$page;
        $this->_productOnPage=$page_count;

        if (empty($activeProducts)) {$this->initProducts($template_id);}
        if (empty($currentPageFilters)) {$this->initFilters($template_id);}

        $this->_rebuildProducts();
        $this->_rebuildFilters($template_id);

        return array($this->getCurrentProducts($template_id),$this->getFilters($template_id),$this->_activeProducts);
    }

    /* print Form with Filters
     * get Products Form
     * get Filters Form
     * get _activeProducts
     * get _currentPageFilters
     * get Link of page
     * */
    public function addFilterTemplate($paramId,$statusFilter,$activeFilters,$currentPageFilters,$activeProducts,$template_id)
    {
        $activeProducts = json_decode($activeProducts);
        $activeProducts = (array)$activeProducts;
        foreach ($activeProducts as $key=>$product) {
            $activeProducts[$key] = (array)$product;
        }

        $this->_activeProducts=$activeProducts;
        $this->_currentPageFilters=$currentPageFilters;
        $this->_activeFilters=$activeFilters;

        if ($statusFilter==0) {
            $this->initProducts($template_id);
            $this->initFilters($template_id);
        }

        if (empty($activeProducts)) {$this->initProducts($template_id);}
        if (empty($currentPageFilters)) {$this->initFilters($template_id);}

        if ($statusFilter==1) $this->addActiveProducts($template_id);

        $this->_rebuildProducts();
        $this->_rebuildFilters($template_id,$paramId);

        $template_link=$this->getTemplateLink($template_id);
        $new_link="$template_link/";
        foreach ($this->_activeFilters as $param=>$values) {
            if ($param==0) {
                $brand_link="brandy";
                $new_link.="$brand_link/";
                foreach ($values as $value) {
                    $value_link=$this->getCatalogueBrandLink($value);
                    if ($value_link!="") $new_link.="$value_link/";
                }
            } else {
                $param_link=$this->getCatalogueParamLink($param);
                $new_link.="$param_link/";
                foreach ($values as $value) {
                    $value_link=$this->getCatalogueValueLink($value);
                    if ($value_link!="") $new_link.="$value_link/";
                }
            }
        }

        return array($this->getCurrentProducts($template_id),$this->getFilters($template_id),$this->_activeProducts,$this->_currentPageFilters,$this->_clearActiveFilters,$new_link);
    }

    /* set _activeProducts from _activeFilters
     * get _activeProducts
     * */
    protected function addActiveProducts($template_id)
    {
        $addFilter=[];
        foreach ($this->_activeFilters as $param=>$values) {
            if (count($values)>1) {
                foreach ($values as $value) {
                    if (empty($addFilter[$param])) $addFilter[$param]=[];
                    array_push($addFilter[$param],$value);
                }
            }
        }
        if (!empty($addFilter)) {
           $products=$this->getAllProducts($template_id);
           foreach ($products as $art_id=>$product) {$col_par=0;
               foreach ($product as $param=>$values) {$col_val=0;
                   foreach ($values as $value) {
                        if (in_array($value,$addFilter[$param])) $col_val++;
                   }
                   if ($col_val>0) $col_par++;
               }
               if ($col_par==count($addFilter)) $this->_activeProducts[$art_id]=$product;
           }
        }
    }

    /* from _activeProducts, _activeFilters
     * clear Form
     * get _currentPageFilters
     * */
    protected function _rebuildFilters($template_id,$param_id=0)
    {
        $newArts=[]; $newParams=[];
        $filters=$this->getAllFilters($template_id);

        foreach ($this->_activeProducts as $art_id=>$product) {$col_par=0;
            foreach ($product as $param => $values) {$col_val=0;
                foreach ($values as $value) {
                    if (in_array($value, $this->_activeFilters[$param])) $col_val++;
                }
                if ($col_val>0) $col_par++;
            }
            if ($col_par>0) array_push($newArts, $art_id);
        }

        $newArts=array_unique($newArts);

        foreach ($this->_activeProducts as $art_id=>$product) {
            if (in_array($art_id,$newArts)) {
                foreach ($product as $param => $values) {
                    if (empty($newParams[$param])) $newParams[$param]=[];
                    foreach ($values as $value) {
                        if (!in_array($value,$newParams[$param])){
                            array_push($newParams[$param],$value);
                        }
                    }
                }
            }
            // on remove, if current count of params = 1
            if (count($this->_activeFilters)==1) {
                foreach ($this->_activeFilters as $param=>$values) {
                    foreach ($filters[$param] as $value) {
                        if (!in_array($value,$newParams[$param])) {
                            array_push($newParams[$param],$value);
                        }
                    }
                }
            }
        }

        // save values of current param
        foreach ($this->_currentPageFilters as $param => $values) {
            foreach ($values as $value) {
                if ($param_id==$param)
                if (!in_array($value, $newParams[$param])) {
                    array_push($newParams[$param], $value);
                }
            }
        }

        // save values of active param
        foreach ($this->_activeFilters as $param => $values) {
            foreach ($this->_currentPageFilters[$param] as $value) {
                if (!in_array($value, $newParams[$param])) {
                    array_push($newParams[$param], $value);
                }
            }
        }

        $this->_currentPageFilters=$newParams;

        $col_par=0;
        foreach ($this->_currentPageFilters as $param=>$values) {
            if ($values==NULL) $col_par++;
        }

        if ($col_par==count($this->_currentPageFilters)) $this->clearFilters($template_id);
    }

    /* set _activeProducts from _activeFilters
     * get _activeProducts
     * */
    protected function _rebuildProducts()
    {
        foreach ($this->_activeProducts as $art_id=>$product) { $col_param=0;
            foreach ($product as $param => $values) { $col_val=0;
                foreach ($values as $value) {
                    if (in_array($value, $this->_activeFilters[$param])) $col_val++;
                }
                if ($col_val>0) $col_param++;
            }
            if ($col_param!=count($this->_activeFilters)) {
                unset($this->_activeProducts[$art_id]);
            }
        }
    }

    /* clear Form
     * unset _activeProducts, _activeFilters, _currentPageFilters
     * print Form
     * */
    function clearFilters($template_id)
    {
        $this->_activeProducts=[];
        $this->_activeFilters=[];
        $this->_currentPageFilters=[];
        $this->_clearActiveFilters=1;
        $template_link=$this->getTemplateLink($template_id);
        return array($this->getCurrentProducts($template_id), $this->getFilters($template_id), $template_link);
    }

    /*================================================================================================================*/

    function getCatalogueSearchParams($art_id)
    {
        $db=DbSingleton::getTokoDb();
        $suppl_array=$storage_array=$stock_array=[];
        $r=$db->query("SELECT t2asc.STORAGE_ID as storage_id, 0 as suppl_id, t2asc.AMOUNT
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_ARTICLES_STRORAGE` t2asc on t2asc.ART_ID=t2a.ART_ID
        WHERE t2a.ART_ID='$art_id' AND t2asc.STORAGE_ID>0 
        UNION ALL
        SELECT t2si.client_storage_id, t2si.suppl_id, t2si.stock_suppl
        FROM `T2_ARTICLES` t2a
            LEFT OUTER JOIN `T2_SUPPL_IMPORT` t2si on (t2si.art_id=t2a.ART_ID AND t2si.status=1)
        WHERE t2a.ART_ID='$art_id' AND t2si.client_storage_id>0 AND t2si.stock_suppl>0;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $suppl_id = $db->result($r,$i-1,"suppl_id");
            $suppl_array[$i]=$suppl_id;
            $storage_id = $db->result($r,$i-1,"storage_id");
            $storage_array[$i]=$storage_id;
            $stock = $db->result($r,$i-1,"AMOUNT");
            $stock_array[$i]=$stock;
        }
        return array($suppl_array,$storage_array,$stock_array,$n);
    }

    function getTemplatePagin($n,$ch=1)
    {
        $list="";
        for ($i=1;$i<=$n;$i++) {
            if ($i==$ch) $checked="pagin_checked"; else $checked="pagin_btn";
            $list.="<button class=\"btn $checked\" onclick=\"initProductsForm($i);\">$i</button>";
        }
        if ($n==1) $list="";
        return $list;
    }

    /*================================================================================================================*/

    function getTemplateLinkParams($template_id,$link) {
        $db=DbSingleton::getTokoDb();
        $params=["brandy"];

        $r=$db->query("SELECT * FROM `T2_CATALOGUES_PARAMS` WHERE `TEMPLATE_ID`='$template_id';"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_link=$db->result($r,$i-1,"PARAM_LINK");
            array_push($params,$param_link);
        }

        $arr=explode("/",$link);
        $values=[]; $cur_par="";
        foreach ($arr as $key=>$val) {
            if (in_array($val, $params)) {
                $values[$val]=[];
                $cur_par=$val;
            } else {
                $cur_val=$val;
                array_push($values[$cur_par],$cur_val);
            }
        }

        $values_ids=[];
        foreach ($values as $vpar=>$vval) {
            if ($vpar=="brandy") {
                $par_id=0;
                $values_ids[$par_id]=[];
                foreach ($vval as $vv) {
                    $val_id=$this->getCatalogueBrandID($vv);
                    array_push($values_ids[$par_id],$val_id);
                }
            } else {
                $par_id=$this->getCatalogueParamID($vpar);
                $values_ids[$par_id]=[];
                foreach ($vval as $vv) {
                    $val_id=$this->getCatalogueValueID($vv);
                    array_push($values_ids[$par_id],$val_id);
                }
            }
        }

        foreach ($values_ids as $param=>$values) {
            foreach ($values as $key=>$value) {
                if ($value==0 || empty($value)) unset($values_ids[$param][$key]);
            }
        }

        return $values_ids;
    }

}

