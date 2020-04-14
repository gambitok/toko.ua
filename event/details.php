<?php

$result=explode(findLinks()[0]."/", $_SERVER["REQUEST_URI"], 2); $link=ltrim($result[1]);
header("Location: /catalog/$link", TRUE, 301);

//$page=$_GET["page"]; $page!=NULL ?: $page=1;
//
//$linka=findLinks();
//$str_text=$linka[1];
//$mfa_link=$linka[2];
//$mod_link=$linka[3];
//
//$cookie_typ_id=$prod->getCookieAuto();
//$cookie_garage_id=$prod->getCookieGarage();
//
//$details_form=$prod->getHtmlForm("details_offers");
//
//$form1="";
//
//if ($str_text=="") { // choose details form
//
//    if ($cookie_garage_id!="") { // + garage form
//        list($mfa_link, $mod_link,,) = $automan->getCookieCarInfo($cookie_garage_id);
//        $car_content = $prod->showCarsSelectMin($str_text, $mfa_link, $mod_link);
//        $car_content_style="display:none;";
//        $car_garage_style="display:block;";
//        $form1 = $prod->getCarDetails($prod->getStrIds($cookie_garage_id), $cookie_garage_id);
//    } else {
//        $str_ids="";
//        if ($cookie_typ_id!="") {
//            list($mfa_link, $mod_link,,) = $automan->getCookieCarInfo($cookie_typ_id);
//            $car_content = $prod->showCarsSelectMin($str_text, $mfa_link, $mod_link);
//            $car_content_style="display:block;";
//            $car_garage_style="display:none;";
//            $str_ids = $prod->getStrIds($cookie_typ_id);
//        }
//        $form1 = $prod->getCarDetails($str_ids, $cookie_typ_id);
//    }
//
//} else { // choosen detail
//
//    if (!$automan->checkDetailLink($str_text)) $content=$automan->page404($content); else {
//
//        $str_id=$automan->getStrNewLinkStr($str_text);
//
//        if ($cookie_garage_id!="") { // if have garage
//            if ($mfa_link!="" || $mod_link!="") {
//                $car_content = $prod->showCarsSelectMin($str_text, $mfa_link, $mod_link);
//                $car_content_style="display:block;";
//                $car_garage_style="display:block;";
//                list($mfa_link2, $mod_link2,,) = $automan->getCookieCarInfo($cookie_garage_id);
//                if ($mfa_link2==$mfa_link && $mod_link2==$mod_link) {
//                    $form1 = $prod->techCarModels($cookie_garage_id, $str_id);
//                } else {
//                    $form1 = "";
//                }
//            } else {
//                list($mfa_link, $mod_link,,) = $automan->getCookieCarInfo($cookie_garage_id);
//                $car_content = $prod->showCarsSelectMin($str_text, $mfa_link, $mod_link);
//                $car_content_style="display:none;";
//                $car_garage_style="display:block;";
//                $form1 = $prod->techCarModels($cookie_garage_id, $str_id);
//            }
//        } else { // if have not garage
//
//            $car_content_style="display:block;";
//            $car_garage_style="display:none;";
//
//            if ($cookie_typ_id!="") { // if have cookie type
//
//                list($mfa_link2, $mod_link2,,) = $automan->getCookieCarInfo($cookie_typ_id);
//
//                if ($mfa_link!="" || $mod_link!="") {
//                    $car_content = $prod->showCarsSelectMin($str_text, $mfa_link, $mod_link);
//                    if ($mfa_link2==$mfa_link && $mod_link2==$mod_link) {
//                        $form1 = $prod->techCarModels($cookie_typ_id, $str_id);
//                    } else {
//                        $form1 = "";
//                    }
//                } else {
//                    $car_content = $prod->showCarsSelectMin($str_text, $mfa_link2, $mod_link2);
//                    $form1 = $prod->techCarModels($cookie_typ_id, $str_id);
//                }
//
//            } else { // if have not cookie type
//                $details_form = $search->showDetailsForm($details_form, $str_id, $page, $_GET["brandy"]);
//
//                if (!$automan->checkMfaAuto($mfa_link) && $mfa_link!="") $content=$automan->page404($content);
//                if (!$automan->checkModelAuto($mod_link) && $mod_link!="") $content=$automan->page404($content);
//
//                if ($mfa_link!="" && $mod_link!="") {
//                    $car_content = $prod->showCarsSelectMin($str_text, $mfa_link, $mod_link);
//                } else {
//                    $car_content = $prod->showCarsSelect($str_text, $mfa_link, $mod_link);
//                }
//                $form1 = $details_form;
//            }
//        }
//    }
//}
//
//$search_form=$prod->getHtmlForm("car_form_div");
//$search_form=str_replace("{car_garage}", $automan->showGarageFormMin(), $search_form);
//$search_form=str_replace("{car_content}", $car_content, $search_form);
//$search_form=str_replace("{car_content_style}", $car_content_style, $search_form);
//$search_form=str_replace("{car_garage_style}", $car_garage_style, $search_form);
//
//$content=str_replace("{main_auto_window}", $search_form, $content);
//$content=str_replace("{main_window}", $form1, $content);
