<?php

class Test
{
    protected $_activeProducts = [];

    protected $_currentPageFilters = [];

    protected $_activeFilters = [];

    public function getProducts()
    {
        $this->initProducts();
        $products=$this->_activeProducts;
        $db=DbSingleton::getTokoDb();

        $list="<table class='table'><tr><td>ART_ID</td>";
        $r=$db->query("select * from T2_CATALOGUES_PARAMS;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $param_name= $db->result($r, $i - 1, "PARAM_NAME");
            $list.="<td>$param_id. $param_name</td>";
        }
        $list.="</tr>";

        foreach ($products as $art_id=>$product) {
            $list.="<tr><td>$art_id</td>";
            foreach ($product as $param_id=>$values) {$value_str="";
                foreach ($values as $value_id) {
                    $value_str.="$value_id ";
                }
                $list.="<td>$value_str</td>";
            }
            $list.="</tr>";
        }
        $list.="</table>";
        return $list;
    }

    public function initProducts($art_ids='')
    {
        //тут забиваєш $this->_activeProducts
        $db=DbSingleton::getTokoDb();
        $products=[];

        if ($art_ids!="") $where_arts="where ART_ID in ($art_ids)"; else $where_arts="";

        $r=$db->query("select * from T2_CATALOGUES_ARTS $where_arts group by ART_ID;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $r2=$db->query("select * from T2_CATALOGUES_PARAMS;"); $n2=$db->num_rows($r2);
            for ($j=1;$j<=$n2;$j++) {
                $param_id=$db->result($r2,$j-1,"PARAM_ID");
                $products[$art_id][$param_id]=[];
            }
        }

        $r=$db->query("select * from T2_CATALOGUES_ARTS $where_arts;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $art_id=$db->result($r,$i-1,"ART_ID");
            $param_id=$db->result($r,$i-1,"PARAM_ID");
            $value_id=$db->result($r,$i-1,"VALUE_ID");
            array_push($products[$art_id][$param_id],$value_id);
        }

        $this->_activeProducts=$products;
        return $products;
    }

    public function initCurrentPageFilters($filters='')
    {
        //тут забиваєш $this->_currentPageFilters
        $db=DbSingleton::getTokoDb();
        $params=[];

        if ($filters!="") $where_values="and VALUES_ID in ($filters)"; else $where_values="";

        $r=$db->query("select * from T2_CATALOGUES_PARAMS;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $params[$param_id]=[];
        }

        $r=$db->query("select * from T2_CATALOGUES_VALUES $where_values;"); $n=$db->num_rows($r);
        for ($i=1;$i<=$n;$i++) {
            $param_id = $db->result($r, $i - 1, "PARAM_ID");
            $value_id = $db->result($r, $i - 1, "VALUE_ID");
            array_push($params[$param_id],$value_id);
        }

        $this->_currentPageFilters=$params;
        return $params;
    }

    public function getFilters()
    {
        $this->initCurrentPageFilters();
        $filters=$this->_currentPageFilters;

        $list="<div>";
        foreach ($filters as $param_id=>$values) {
            $list.="<h2>Param #$param_id</h2><ul>";
            foreach ($values as $value_id) {
                $list.="<li>Value #$value_id</li>";
            }
            $list.="</ul>";
        }
        $list.="</div>";

        return $list;
    }

    public function getActiveFilters()
    {
        return $this->_activeFilters;
    }

    public function getActiveProducts()
    {
        return $this->_activeProducts;
    }

    public function addFilter($newFilterName, $newFilterValue)
    {
        $this->_activeFilters[$newFilterName] = $newFilterValue;
        $this->_rebuildFilters();
        $this->_rebuildProducts();
    }

    protected function _rebuildFilters()
    {
        $this->_activeFilters = [];
        foreach ($this->_activeProducts as $product) {
            foreach ($this->_currentPageFilters as $param => $values) {
                foreach ($values as $value) {
                    if (in_array($value, $product[$param])) {
                        $this->_activeFilters[$param] = $values;
                        break;
                    }
                }
            }
        }
    }

    protected function _rebuildProducts()
    {
        foreach ($this->_activeProducts as $param => $values) {
            foreach ($values as $value) {
                if (!in_array($value, $this->_activeFilters[$param])) {
                    unset($this->_activeProducts[$param]);
                }
            }
        }
    }
}