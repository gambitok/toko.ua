<?php

$linka=findLinks();
$w=$linka[1];

if ($w=="" || $w=="finddetail" || $w=="findtec" || $w=="findmodel" || $w=="auto") {
    header("Location: /catalog/", TRUE, 301);
}

if ($w=="search") {
    $result = explode($w . "/", $_SERVER["REQUEST_URI"], 2); $link = ltrim($result[1]);
    header("Location: /search/$link", TRUE, 301);
}

if ($w=="article") {
    $result = explode($w . "/", $_SERVER["REQUEST_URI"], 2); $link = ltrim($result[1]);
    header("Location: /article/$link", TRUE, 301);
}

if ($w=="filter") {
    $template_id=$linka[2];
    $template_link=$pattern->getTemplateLink($template_id);
    header("Location: /products/$template_link", TRUE, 301);
}

//$article_nr_search=$linka[2];
//$brand=$linka[3];
//$art_id=$linka[4];
//$mfa=$linka[2];
//$mod=$linka[3];
//$mod_id=$linka[4];

//if ($w=="") {
//    $content=str_replace("{main_window}", $automan->showCatalogueBlock(), $content);
//}
//
//if ($w=="auto") {
//    $content=str_replace("{main_window}", $automan->showCatalogueBlock($mfa, $mod, $mod_id), $content);
//}

//if ($w=="search") {
//    if ($article_nr_search=="") {
//        $content=str_replace("{main_window}", $catalogue->getHtmlForm("search_unknown"), $content);
//    } else {
//        $content=str_replace("{main_window}", $catalogue->getHtmlForm("search"), $content);
//        if ($brand=="") {
//            $content=str_replace("{search}", $catalogue->searchNumber($article_nr_search), $content);
//        } else {
//            $content=str_replace("{search}", $catalogue->showCatalogueList($article_nr_search, $brand), $content);
//        }
//    }
//}

//if ($w=="article") {
//    $content=str_replace("{main_window}", $showform->showArticle($art_id), $content);
//}

//if ($w=="templates") {
//    $content=str_replace("{main_window}", "<div class=\"content\">".$catalogue->showCatalogueTemplates()."</div>", $content);
//}

//if ($w=="filter") {
//    $template_id=$template->getTemplateID($linka[2]);
//    $page=$_GET["page"]; $page!=NULL ?: $page=1;
//    $result=explode($linka[2]."/", $_SERVER["REQUEST_URI"], 2); $link=ltrim($result[1]);
//
//    if ($template_id==1 && $linka[3]=="") { //lampi
//
//        $form=$showform->showLightBulbForm();
//        $form=$showform->getParentTemplateForm($template_id);
//
//    } else {
//
//        $currentPageFilters = $template->getAllFilters($template_id);
//        $currentPageProducts = $template->getAllProducts($template_id);
//        $activeFilters = $template->getTemplateLinkParams($template_id,$link);
//        $activeProducts = $template->getActiveProducts();
//
//        list($products,$filters) = $template->initProductsForm($activeFilters,$currentPageFilters,$activeProducts,$page,$template->getProductOnPage(),$template_id);
//
//        $form=$template->getHtmlForm("template");
//
//        $form=str_replace("{template_filters}",$filters,$form);
//        $form=str_replace("{template_products}",$products,$form);
//
//        $form=str_replace("{template_name}",$template->getTemplateName($template_id),$form);
//        $form=str_replace("{template_id}",$template_id,$form);
//
//        $form=str_replace("{json_filters}",json_encode($currentPageFilters),$form);
//        $form=str_replace("{json_active_filters}",json_encode($activeFilters),$form);
//        $form=str_replace("{json_products}",json_encode($currentPageProducts),$form);
//        $form=str_replace("{json_active_products}",json_encode($template->getActiveProducts()),$form);
//
//    }
//
//    $content=str_replace("{main_window}", $form, $content);
//}

//if ($w=="findtec") {
//	$content=str_replace("{main_window}", $catalogue->techDetails(), $content);
//}

//if ($w=="findmodel") {
//    if ($mfa!="" && $mod!="" && $mod_id!="" && $typ_id!="") {
//        $content=str_replace("{main_window}", $catalogue->techModels(), $content);
//        $showform->insertAutoHistory();
//    } else {
//        $content=str_replace("{main_window}", $automan->showCatalogueBlock(), $content);
//    }
//}

//if ($w=="finddetail") {
//    $str_id = $linka[3];$str_level = $linka[4];$str_id_parrent = $linka[5];$str_year = $linka[6];$str_manufacture = $linka[7];$str_model = $linka[8];$str_modelid = $linka[9];$str_group = $linka[10];
//    if ($str_id!="" && $str_level!="" && $str_id_parrent!="")
//        $content=str_replace("{main_window}", $catalogue->techSearchDetails($str_id,$str_level,$str_id_parrent,$str_year,$str_manufacture,$str_model,$str_modelid,$str_group), $content);
//}

//if ($w=="findauto") {
//	$content=str_replace("{main_window}", $automan->autoDetails($auto), $content);
//}

//if ($w=="findmanuf") {
//    $head_id = $linka[2];
//    $content=str_replace("{main_window}", $catalogue->getManufactureDetails($head_id), $content);
//}

//if ($w=="info") { //недопрацьовано
//	$content=str_replace("{main_window}", $showform->showAutoInfo($linka[2]), $content);
//}