<?php

$content = str_replace("{main_window}", $shop->getHtmlForm("test"), $content);

//$arr = [1264];
//foreach ($arr as $client_id) {
//    $message = $client->dropClient($client_id);
//    print $message;
//}

//$content = str_replace("{detail_form}", $automan->getDetailsList(), $content);
//$content = str_replace("{auto_form}", $automan->getAutoModList(), $content);

//$r=$db->query("SELECT * FROM `T2_CATALOGUES_VALUES` WHERE 1;"); $n=$db->num_rows($r);
//for ($i=1;$i<=$n;$i++) {
//    $value_id=$db->result($r,$i-1,"VALUE_ID");
//    $param_value=$db->result($r,$i-1,"PARAM_VALUE");
//    $format_name = $catalogue->formatCustomUrlText($param_value);
//    $db->query("UPDATE `T2_CATALOGUES_VALUES` SET `VALUE_LINK`='$format_name' WHERE `VALUE_ID`='$value_id' LIMIT 1;");
//}
//
//echo $n;

//var_dump($template->getTemplateLinkParams(1));


