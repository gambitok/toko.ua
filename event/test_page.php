<?php

$list = $catalogue->searchListCatalog2("4032068,5579987,100036544,562112,565996,563784", 1);

$content = str_replace("{main_window}", $list, $content);

//$arr = $catalogue->getTempSearch("4032068,5579987,100036544,562112,565996,563784");

//var_dump($arr);

//$list = "
//<table class='table'>";
//foreach ($arr as $art_id => $values) {
//    $list .= "
//    <tr><td colspan='6'>art_id: $art_id</td></tr>
//    <tr>
//        <td>ART_ID</td>
//        <td>ART_NR</td>
//        <td>ART_NM</td>
//        <td>BRD_ID</td>
//        <td>BRD_NM</td>
//        <td>STOCK</td>
//        <td>SUPPL</td>
//        <td>STORA</td>
//        <td>PRICE</td>
//        <td>RETUR</td>
//        <td>DELIV</td>
//    </tr>";
//
//    $stock          = $values["stock"];
//    $suppl_id       = $values["suppl_id"];
//    $storage_id     = $values["storage_id"];
//    $price          = $values["price"];
//    $return         = $values["return_delay"];
//    $delivery_days  = $values["delivery_days"];
//    $brand_id  = $values["brand_id"];
//    $brand_name  = $values["brand_name"];
//    $article_nr_displ  = $values["article_nr_displ"];
//    $article_name  = $values["article_name"];
//
//    $list .= "
//    <tr>
//        <td>$art_id</td>
//        <td>$article_nr_displ</td>
//        <td>$article_name</td>
//        <td>$brand_id</td>
//        <td>$brand_name</td>
//        <td>$stock</td>
//        <td>$suppl_id</td>
//        <td>$storage_id</td>
//        <td>$price</td>
//        <td>$return</td>
//        <td>$delivery_days</td>
//    </tr>";
//}
//$list .= "</table>";

//$list = "
//<table class='table'>";
//foreach ($arr as $art_id => $values) {
//    $list .= "
//    <tr><td colspan='6'>art_id: $art_id</td></tr>
//    <tr>
//        <td>STOCK</td>
//        <td>SUPPL</td>
//        <td>STORA</td>
//        <td>PRICE</td>
//        <td>RETUR</td>
//        <td>DELIV</td>
//    </tr>";
//
//    foreach ($values as $value) {
//        $stock          = $value["stock"];
//        $suppl_id       = $value["suppl_id"];
//        $storage_id     = $value["storage_id"];
//        $price          = $value["price"];
//        $return         = $value["return_delay"];
//        $delivery_days  = $value["delivery_days"];
//
//        $list .= "
//        <tr>
//            <td>$stock</td>
//            <td>$suppl_id</td>
//            <td>$storage_id</td>
//            <td>$price</td>
//            <td>$return</td>
//            <td>$delivery_days</td>
//        </tr>";
//    }
//}
//$list .= "</table>";

