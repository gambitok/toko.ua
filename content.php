<?php

define('RDD', dirname (__FILE__));
date_default_timezone_set("Europe/Kiev");

include_once (RDD."/lib/access.php");
require_once (RDD."/lib/helper.php");
require_once (RDD."/lib/variables.php");
require_once (RDD."/lib/DbSingleton.php");
require_once (RDD."/lib/form_class.php");
require_once (RDD."/lib/catalogue_class.php");
require_once (RDD."/lib/products_class.php");
require_once (RDD."/lib/menu_class.php");
require_once (RDD."/lib/shop_class.php");
require_once (RDD."/lib/client_class.php");
require_once (RDD."/lib/profile_class.php");
require_once (RDD."/lib/lang_class.php");
require_once (RDD."/lib/exrate_class.php");
require_once (RDD."/lib/auto_class.php");
require_once (RDD."/lib/parameters_class.php");
require_once (RDD."/lib/search_class.php");
require_once (RDD."/lib/pattern_class.php");
require_once (RDD."/lib/parts_class.php");
require_once (RDD."/js/JsHttpRequest/JsHttpRequest.php");
require_once (RDD."/lib/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php");
$JsHttpRequest = new JsHttpRequest("windows-1251");

session_start();
$catalog=new CatalogueClass; $menu=new MenuClass; $client=new ClientClass; $lang=new LangClass; $shop=new ShopClass;
$showform=new FormClass; $automan=new AutoClass; $profile=new ProfileClass;
$prod=new ProductsClass; $parameters=new ParametersClass; $search=new SearchClass;

/*=== PROFILE ====*/

if ($_REQUEST["w"]=="showProfileAccount"){$GLOBALS['_RESULT'] = array("content"=>$profile->showProfileAccount());}

if ($_REQUEST["w"]=="showProfilePrice"){$GLOBALS['_RESULT'] = array("content"=>$profile->showPriceList());}

if ($_REQUEST["w"]=="getPriceList"){$GLOBALS['_RESULT'] = array("content"=>$profile->getPriceList());}

if ($_REQUEST["w"]=="setPriceList"){$GLOBALS['_RESULT'] = array("content"=>$profile->setPriceList());}

if ($_REQUEST["w"]=="showProfileOrders"){$GLOBALS['_RESULT'] = array("content"=>$profile->showProfileOrders());}

if ($_REQUEST["w"]=="showProfileOrdersArts"){$GLOBALS['_RESULT'] = array("content"=>$profile->showProfileOrdersArts($_REQUEST["dp_id"], $_REQUEST["order_id"]));}

//if ($_REQUEST["w"]=="showProfileBasketForm"){$GLOBALS['_RESULT'] = array("content"=>$shop->showMiniBasketForm()[0]);}

if ($_REQUEST["w"]=="showBasketMinForm"){$GLOBALS['_RESULT'] = array("content"=>$shop->showBasketForm());}

if ($_REQUEST["w"]=="setCityDepartments"){$GLOBALS['_RESULT'] = array("content"=>$shop->setCityDepartments($_REQUEST["city_ref"]));}
if ($_REQUEST["w"]=="getOrderDeliveryBlock"){$GLOBALS['_RESULT'] = array("content"=>$shop->getOrderDeliveryBlock($_REQUEST["delivery_id"], $_REQUEST["city_id"]));}
if ($_REQUEST["w"]=="getOrderPaymentBlock"){$GLOBALS['_RESULT'] = array("content"=>$shop->getOrderPaymentBlock($_REQUEST["payment_id"], $_REQUEST["delivery_id"]));}
if ($_REQUEST["w"]=="validOrder"){$GLOBALS['_RESULT'] = array("content"=>$shop->validOrder($_REQUEST["name"],$_REQUEST["phone"],$_REQUEST["city"],$_REQUEST["delivery"],$_REQUEST["delivery_type"],$_REQUEST["payment"],$_REQUEST["email"],$_REQUEST["comment"]));}
if ($_REQUEST["w"]=="saveOrder"){$GLOBALS['_RESULT'] = array("content"=>$shop->saveOrder($_REQUEST["name"],$_REQUEST["phone"],$_REQUEST["city"],$_REQUEST["delivery"],$_REQUEST["delivery_type"],$_REQUEST["payment"],$_REQUEST["email"],$_REQUEST["comment"]));}
if ($_REQUEST["w"]=="validDeliveryFields"){$GLOBALS['_RESULT'] = array("content"=>$shop->validDeliveryFields($_REQUEST["delivery"],$_REQUEST["delivery_type"]));}
if ($_REQUEST["w"]=="getBasketOrder"){$GLOBALS['_RESULT'] = array("content"=>$shop->getBasketOrder($_REQUEST["delivery_id"]));}
if ($_REQUEST["w"]=="setDeliveryExpressDepartment"){$GLOBALS['_RESULT'] = array("content"=>$shop->setDeliveryExpressDepartment($_REQUEST["delivery_express"]));}
if ($_REQUEST["w"]=="setClientOrderInfo"){$GLOBALS['_RESULT'] = array("content"=>$shop->setClientOrderInfo($_REQUEST["id"]));}
if ($_REQUEST["w"]=="dropClientOrderInfo"){$GLOBALS['_RESULT'] = array("content"=>$shop->dropClientOrderInfo($_REQUEST["id"]));}

if ($_REQUEST["w"]=="getUserSavedData"){ list($status, $list, $info_id)=$shop->getUserSavedData($_REQUEST["city"]); $GLOBALS['_RESULT'] = array("status"=>$status,"list"=>$list,"info_id"=>$info_id);}

if ($_REQUEST["w"]=="getCityVal"){$GLOBALS['_RESULT'] = array("content"=>$shop->getCityVal($_REQUEST["search_text"]));}
if ($_REQUEST["w"]=="setCityNPVal"){$GLOBALS['_RESULT'] = array("content"=>$shop->setCityNPVal($_REQUEST["city_id"]));}
if ($_REQUEST["w"]=="setCityAddress"){$GLOBALS['_RESULT'] = array("content"=>$shop->setCityAddress($_REQUEST["city_id"]));}
if ($_REQUEST["w"]=="hideOrderInfo"){$GLOBALS['_RESULT'] = array("content"=>$shop->hideOrderInfo($_REQUEST["name"],$_REQUEST["phone"],$_REQUEST["city"]));}

/*=== CATALOG ====*/

if ($_REQUEST["w"]=="getCatalogueLink"){$GLOBALS['_RESULT'] = array("content"=>$catalog->getCatalogueLink($_REQUEST["article_nr_search"]));}

if ($_REQUEST["w"]=="tab_auto"){$GLOBALS['_RESULT'] = array("content"=>$automan->showTabCatalogueManufacture($_REQUEST["year"]));}

if ($_REQUEST["w"]=="tab_model"){$GLOBALS['_RESULT'] = array("content"=>$automan->showTabCatalogueModel($_REQUEST["auto"], $_REQUEST["year"]));}

if ($_REQUEST["w"]=="tab_modelid"){$GLOBALS['_RESULT'] = array("content"=>$automan->showTabCatalogueModelId($_REQUEST["model"], $_REQUEST["auto"], $_REQUEST["year"]));}

if ($_REQUEST["w"]=="tab_group"){$GLOBALS['_RESULT'] = array("content"=>$automan->showTabCatalogueGroup($_REQUEST["modelid"], $_REQUEST["model"], $_REQUEST["auto"], $_REQUEST["year"]));}

/*=== GARAGE ====*/

if ($_REQUEST["w"]=="addToGarage"){$GLOBALS['_RESULT'] = array("content"=>$automan->addToGarage($_REQUEST["typ_id"]));}

if ($_REQUEST["w"]=="showAutoGarage"){$GLOBALS['_RESULT'] = array("content"=>$automan->showGarageForm());}

if ($_REQUEST["w"]=="updateChosenAutoGarage"){$GLOBALS['_RESULT'] = array("content"=>$automan->updateChosenAutoGarage($_REQUEST["auto_id"]));}

if ($_REQUEST["w"]=="deleteAutoGarage"){$GLOBALS['_RESULT'] = array("content"=>$automan->deleteAutoGarage($_REQUEST["auto_id"]));}

if ($_REQUEST["w"]=="showGarageForm"){$GLOBALS['_RESULT'] = array("content"=>$automan->showGarageForm());}

if ($_REQUEST["w"]=="updateGarageStatus"){$GLOBALS['_RESULT'] = array("content"=>$automan->getGarageAutoCount());}

if ($_REQUEST["w"]=="showAutoHistory"){$GLOBALS['_RESULT'] = array("content"=>$automan->showAutoHistory());}

if ($_REQUEST["w"]=="dropAutoHistory"){$GLOBALS['_RESULT'] = array("content"=>$automan->dropAutoHistory($_REQUEST["history_id"]));}

/*=== CATALOG FILTER ====*/

if ($_REQUEST["w"]=="show_catalogue_filter_all"){$GLOBALS['_RESULT'] = array("content"=>$catalog->showCatalogueListFilter($_REQUEST["art"], $_REQUEST["brand"], $_REQUEST["bb"], $_REQUEST["text"], $_REQUEST["cur"], $_REQUEST["price"], $_REQUEST["deliv"], $_REQUEST["order"]));}

if ($_REQUEST["w"]=="show_model_filter_all"){$GLOBALS['_RESULT'] = array("content"=>$catalog->techModelsFilters($_REQUEST["art"], $_REQUEST["brand"], $_REQUEST["bb"], $_REQUEST["text"], $_REQUEST["cur"], $_REQUEST["price"], $_REQUEST["deliv"], $_REQUEST["order"]));}

/*=== MENU ====*/

if ($_REQUEST["w"]=="saveSellerForm"){$GLOBALS['_RESULT'] = array("content"=>$menu->saveSellerForm($_REQUEST["company"],$_REQUEST["name"],$_REQUEST["phone"],$_REQUEST["email"],$_REQUEST["city_id"],$_REQUEST["comment"]));}

if ($_REQUEST["w"]=="getSellerImage"){$GLOBALS['_RESULT'] = array("content"=>$menu->getSellerImage());}

if ($_REQUEST["w"]=="getRegionSelect"){ $GLOBALS['_RESULT'] = array("content"=>$menu->getRegionSelect());}

/*=== MODALS ====*/

if ($_REQUEST["w"]=="loadApplicModels2"){$GLOBALS['_RESULT'] = array("content"=>$showform->getApplModelTCD($_REQUEST["art_id_tcd"], $_REQUEST["manufacture"]));}

if ($_REQUEST["w"]=="loadApplicModelsInfo2"){$GLOBALS['_RESULT'] = array("content"=>$showform->getApplModelInfoTCD($_REQUEST["art_id"], $_REQUEST["typ_id"]));}

if ($_REQUEST["w"]=="showInfoForm"){$GLOBALS['_RESULT'] = array("content"=>$showform->showInfoForm($_REQUEST["art_id"]));}

if ($_REQUEST["w"]=="showPhotoForm"){$GLOBALS['_RESULT'] = array("content"=>$showform->showPhotoGallery($_REQUEST["ref"]));}

if ($_REQUEST["w"]=="showBrandForm"){$GLOBALS['_RESULT'] = array("content"=>$showform->showBrandForm($_REQUEST["brand"]));}

if ($_REQUEST["w"]=="showHistoryList"){$GLOBALS['_RESULT'] = array("content"=>$showform->showHistoryList());}

if ($_REQUEST["w"]=="deleteHistoryItem"){$GLOBALS['_RESULT'] = array("content"=>$showform->deleteHistoryItem($_REQUEST["history_id"]));}

/*=== CLIENT ====*/

if ($_REQUEST["w"]=="setTpoint"){$GLOBALS['_RESULT'] = array("content"=>$client->setTpoint($_REQUEST["id"]));}

if ($_REQUEST["w"]=="loginClient"){$GLOBALS['_RESULT'] = array("content"=>$client->loginClient($_REQUEST["login"], $_REQUEST["password"]));}

if ($_REQUEST["w"]=="logoutClient"){$GLOBALS['_RESULT'] = array("content"=>$client->logoutClient());}

if ($_REQUEST["w"]=="saveProfile"){$GLOBALS['_RESULT'] = array("content"=>$client->updateProfile($_REQUEST["phone"], $_REQUEST["pass"], $_REQUEST["email"], $_REQUEST["name"]));}

if ($_REQUEST["w"]=="saveRegistration"){$GLOBALS['_RESULT'] = array("content"=>$client->saveRegistration($_REQUEST["phone"], $_REQUEST["pass"], $_REQUEST["email"], $_REQUEST["name"],$_REQUEST["client_category"],$_REQUEST["city_id"],$_REQUEST["tpoint_id"],$_REQUEST["mailing"]));}

if ($_REQUEST["w"]=="check_reg_client"){$GLOBALS['_RESULT'] = array("content"=>$client->checkRegClient($_REQUEST["phone"], $_REQUEST["type"]));}
if ($_REQUEST["w"]=="validateOperator"){$GLOBALS['_RESULT'] = array("content"=>$client->validateOperator($_REQUEST["phone"]));}

if ($_REQUEST["w"]=="recoverPassword"){$GLOBALS['_RESULT'] = array("content"=>$client->recoverPassword($_REQUEST["phone"]));}

if ($_REQUEST["w"]=="validatePhone"){$GLOBALS['_RESULT'] = array("content"=>$client->validatePhone($_REQUEST["phone"]));}

if ($_REQUEST["w"]=="endValidation"){$GLOBALS['_RESULT'] = array("content"=>$client->endValidation($_REQUEST["phone"], $_REQUEST["password"]));}

if ($_REQUEST["w"]=="toggleProductView"){$GLOBALS['_RESULT'] = array("content"=>$client->toggleProductView($_REQUEST["ds"]));}

if ($_REQUEST["w"]=="showProfileCheckForm"){$GLOBALS['_RESULT'] = array("content"=>$profile->showProfileCheck($_REQUEST["data_start"], $_REQUEST["data_end"]));}

/*=== LANGUAGE ====*/

if ($_REQUEST["w"]=="changeLangAlert"){$GLOBALS['_RESULT'] = array("content"=>$lang->changeLangAlert($_REQUEST["message"], $_REQUEST["title"]));}

if ($_REQUEST["w"]=="setSiteLang"){$GLOBALS['_RESULT'] = array("content"=>$lang->setSiteLang($_REQUEST["id"]));}

//if ($_REQUEST["w"]=="selectLang"){$GLOBALS['_RESULT'] = array("content"=>$lang->setLanguage($_REQUEST["id"]));}
//
//if ($_REQUEST["w"]=="selectLangText"){$GLOBALS['_RESULT'] = array("content"=>$menu->getLanguageSelect($_REQUEST["id"]));}

if ($_REQUEST["w"]=="changeLangJs"){$GLOBALS['_RESULT'] = array("content"=>$lang->changeLangJs($_REQUEST["text"]));}

/*=== SHOP ====*/

if ($_REQUEST["w"]=="moveToBasket"){ list($old_amount, $art_name, $basket_count)=$shop->moveToBasket($_REQUEST["art_id"],$_REQUEST["brand_id"],$_REQUEST["count"],$_REQUEST["stock"],$_REQUEST["storage_id"],$_REQUEST["suppl_id"]); $GLOBALS['_RESULT'] = array("old_amount"=>$old_amount,"art_name"=>$art_name,"basket_count"=>$basket_count);}

if ($_REQUEST["w"]=="deleteFromBasket"){ $GLOBALS['_RESULT'] = array("content"=>$shop->deleteFromBasket($_REQUEST["art_id"], $_REQUEST["storage_id"]));}

if ($_REQUEST["w"]=="checkBasketItem"){ $GLOBALS['_RESULT'] = array("content"=>$shop->checkBasketItem($_REQUEST["art_id"], $_REQUEST["storage_id"], $_REQUEST["status"]));}

if ($_REQUEST["w"]=="finish_order"){ $GLOBALS['_RESULT'] = array("content"=>$shop->finishOrder($_REQUEST["client_id"],$_REQUEST["client_user_id"],$_REQUEST["tpoint_id"],$_REQUEST["name"],$_REQUEST["phone"],$_REQUEST["region"],$_REQUEST["email"],$_REQUEST["delivery"],$_REQUEST["delivery_info"],$_REQUEST["payment"],$_REQUEST["payment_info"],$_REQUEST["carrier_id"]));}

if ($_REQUEST["w"]=="finish_order_success"){ $GLOBALS['_RESULT'] = array("content"=>$client->saveClientRetail($_REQUEST["client_id"],$_REQUEST["pass"],$_REQUEST["order_id"],$_REQUEST["name"],$_REQUEST["phone"],$_REQUEST["email"]));}

if ($_REQUEST["w"]=="showBasketForm"){ $GLOBALS['_RESULT'] = array("content"=>$shop->showBasketForm($_REQUEST["cur"]));}

if ($_REQUEST["w"]=="updateBasketForm"){ $GLOBALS['_RESULT'] = array("content"=>$shop->updateBasketForm($_REQUEST["art_id"],$_REQUEST["count"],$_REQUEST["storage_id"]));}

if ($_REQUEST["w"]=="updateBasketStatus"){ $GLOBALS['_RESULT'] = array("content"=>$shop->countBasket());}

if ($_REQUEST["w"]=="get_city_list"){ $GLOBALS['_RESULT'] = array("content"=>$showform->showCityForm($_REQUEST["city_like"]));}

if ($_REQUEST["w"]=="closeOrderArtUpdate"){ $GLOBALS['_RESULT'] = array("content"=>$profile->closeOrderArtUpdate($_REQUEST["dp_id"],$_REQUEST["art_id"],$_REQUEST["order_id"]));}

if ($_REQUEST["w"]=="updateOrderArt"){ $GLOBALS['_RESULT'] = array("content"=>$profile->updateOrderArt($_REQUEST["order_id"]));}

/*==== CATALOG TRIGGER LIST ====*/

if ($_REQUEST["w"]=="triggerDetailCar"){ list($content,$header,$format,$skip_id,$title)=$catalog->triggerDetailCar($_REQUEST["type_id"],$_REQUEST["year"],$_REQUEST["manufacture"],$_REQUEST["model"],$_REQUEST["model_id"],$_REQUEST["group"],$_REQUEST["str_id"]); $GLOBALS['_RESULT'] = array("content"=>$content, "header"=>$header, "format"=>$format, "skip_id"=>$skip_id, "title"=>$title);}

if ($_REQUEST["w"]=="showHeadTemplate"){ list($content,$header,$footer)=$menu->showHeadTemplate($_REQUEST["head_id"]); $GLOBALS['_RESULT'] = array("content"=>$content,"header"=>$header,"footer"=>$footer);}

if ($_REQUEST["w"]=="getSpecialOffersList"){ $GLOBALS['_RESULT'] = array("content"=>$menu->getSpecialOffersList($_REQUEST["template_id"],$_REQUEST["update_actions"])[0]);}

if ($_REQUEST["w"]=="checkActionClients"){ $GLOBALS['_RESULT'] = array("content"=>$client->checkActionClients());}

if ($_REQUEST["w"]=="showManufactureDetails"){ $GLOBALS['_RESULT'] = array("content"=>$catalog->getGroupTreeStr($_REQUEST["head_id"],$_REQUEST["str_id_str"]));}

if ($_REQUEST["w"]=="showTabCatalogueAuto"){ $GLOBALS['_RESULT'] = array("content"=>$automan->showTabCatalogueAuto());}

if ($_REQUEST["w"]=="showHomeCars"){ $GLOBALS['_RESULT'] = array("content"=>$showform->showHomeCars());}

if ($_REQUEST["w"]=="showModalForm"){ $GLOBALS['_RESULT'] = array("content"=>$showform->showModalForm($_REQUEST["form"]));}

/*=== PRODUCTS ====*/

if ($_REQUEST["w"]=="showCarDetailsStr"){ $GLOBALS['_RESULT'] = array("content"=>$prod->showCarDetailsStr($_REQUEST["head_id"],$_REQUEST["str_id_str"]));}

if ($_REQUEST["w"]=="techCarModels"){ $GLOBALS['_RESULT'] = array("content"=>$prod->techCarModels($_REQUEST["typ_id"],$_REQUEST["str_id"]));}

if ($_REQUEST["w"]=="techCarModelsFilter"){ $GLOBALS['_RESULT'] = array("content"=>$prod->techCarModelsFilter($_REQUEST["typ_id"],$_REQUEST["str_id"]));}

/*=== PARAMETERS ====*/

if ($_REQUEST["w"]=="showFiltersForm"){ $GLOBALS['_RESULT'] = array("content"=>$parameters->showFiltersForm($_REQUEST["template_id"],$_REQUEST["active_filters"]));}

if ($_REQUEST["w"]=="showFilterOptionsForm"){ $GLOBALS['_RESULT'] = array("content"=>$parameters->showFilterOptionsForm($_REQUEST["template_id"],$_REQUEST["page"],$_REQUEST["active_filters"]));}

/*==== HOME CARS ====*/

if ($_REQUEST["w"]=="getCarsSearchContent"){ list($list, $title, $nav, $tab) = $prod->getCarsSearchContent($_REQUEST["type"],$_REQUEST["attr"],$_REQUEST["str_id"]); $GLOBALS['_RESULT'] = array("list"=>$list, "title"=>$title, "nav"=>$nav, "tab"=>$tab);}

if ($_REQUEST["w"]=="clearCarsBlock"){ $GLOBALS['_RESULT'] = array("content"=>$prod->clearCarsBlock($_REQUEST["sel_tab"],$_REQUEST["cur_tab"]));}

if ($_REQUEST["w"]=="showCarsForm"){ $GLOBALS['_RESULT'] = array("content"=>$prod->showCarsForm());}

if ($_REQUEST["w"]=="showCarsForm2"){ $GLOBALS['_RESULT'] = array("content"=>$prod->showCarsForm2());}

if ($_REQUEST["w"]=="showCarsSelectedForm"){ $GLOBALS['_RESULT'] = array("content"=>$prod->showCarsSelectedForm());}

/*=== SEARCH ====*/

if ($_REQUEST["w"]=="showSearchParameters"){ $GLOBALS['_RESULT'] = array("content"=>$search->showSearchParameters($_REQUEST["str_id"],$_REQUEST["page"],$_REQUEST["active_filters"],$_REQUEST["type"]));}

//if ($_REQUEST["w"]=="showCarsSelectMin"){ $GLOBALS['_RESULT'] = array("content"=>$prod->showCarsSelectMin($_REQUEST["str_id"],$_REQUEST["mfa"],$_REQUEST["model"],$_REQUEST["year"],$_REQUEST["modelid"],$_REQUEST["typ_id"],$_REQUEST["fuel_id"],true));}

//if ($_REQUEST["w"]=="showCarsSelected"){ $GLOBALS['_RESULT'] = array("content"=>$prod->showCarsSelected($_REQUEST["mfa"],$_REQUEST["model"],$_REQUEST["year"],$_REQUEST["modelid"],$_REQUEST["typ_id"]));}

/*==== TEMPLATES ====*/
//if ($_REQUEST["w"]=="addFilterTemplate"){ $GLOBALS['_RESULT'] = array("content"=>$template->addFilterTemplate($_REQUEST["paramId"],$_REQUEST["statusFilters"],$_REQUEST["activeFilters"],$_REQUEST["currentPageFilters"],$_REQUEST["activeProducts"],$_REQUEST["template_id"]));}
//if ($_REQUEST["w"]=="initProductsForm"){ $GLOBALS['_RESULT'] = array("content"=>$template->initProductsForm($_REQUEST["activeFilters"],$_REQUEST["currentPageFilters"],$_REQUEST["activeProducts"],$_REQUEST["page"],$_REQUEST["page_count"],$_REQUEST["template_id"]));}
//if ($_REQUEST["w"]=="clearFilters"){ $GLOBALS['_RESULT'] = array("content"=>$template->clearFilters($_REQUEST["template_id"]));}

