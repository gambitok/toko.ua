<?php

$arr = $catalogue->getTempSearch("4032068,5579987,100036544,562112,565996,563784");

$list = "
<table class='table'>";
foreach ($arr as $art_id => $values) {
    $list .= "
    <tr><td colspan='6'>art_id: $art_id</td></tr>
    <tr>
        <td>STOCK</td>
        <td>SUPPL</td>
        <td>STORA</td>
        <td>PRICE</td>
        <td>RETUR</td>
        <td>DELIV</td>
    </tr>";

    foreach ($values as $value) {
        $stock          = $value["stock"];
        $suppl_id       = $value["suppl_id"];
        $storage_id     = $value["storage_id"];
        $price          = $value["price"];
        $return         = $value["return_delay"];
        $delivery_days  = $value["delivery_days"];

        $list .= "
        <tr>
            <td>$stock</td>
            <td>$suppl_id</td>
            <td>$storage_id</td>
            <td>$price</td>
            <td>$return</td>
            <td>$delivery_days</td>
        </tr>";
    }
}
$list .= "</table>";

print $list;