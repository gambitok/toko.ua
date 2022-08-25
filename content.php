<?php

define('RDD', __DIR__);
date_default_timezone_set("Europe/Kiev");

require_once (RDD . "/vendor/autoload.php");                  // init classes
require_once (RDD . "/lib/access.php");                       // get access site
require_once (RDD . "/js/JsHttpRequest/JsHttpRequest.php");   // ajax requests
require_once (RDD . "/lib/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php");

session_start();
$JsHttpRequest  = new JsHttpRequest("windows-1251");
$catalog        = new CatalogueClass();
$search         = new SearchClass();
$menu           = new MenuClass();
$client         = new ClientClass();
$lang           = new LangClass();
$shop           = new ShopClass();
$showform       = new FormClass();
$automan        = new AutoClass();
$profile        = new ProfileClass();
$prod           = new ProductsClass();

/*==== PROFILE ====*/

if ($_REQUEST["w"] === "showProfileAccount") {
    $GLOBALS['_RESULT'] = array("content" => $profile->showProfileAccount());
}

if ($_REQUEST["w"] === "showProfilePrice") {
    $GLOBALS['_RESULT'] = array("content" => $profile->showPriceList());
}

if ($_REQUEST["w"] === "setPriceList") {
    $GLOBALS['_RESULT'] = array("content" => $profile->setPriceList());
}

if ($_REQUEST["w"] === "showProfileOrders") {
    $GLOBALS['_RESULT'] = array("content" => $profile->showProfileOrders());
}

if ($_REQUEST["w"] === "showProfileOrdersArts") {
    $GLOBALS['_RESULT'] = array("content" => $profile->showProfileOrdersArts($_REQUEST["dp_id"], $_REQUEST["order_id"]));
}

if ($_REQUEST["w"] === "showProfileDocs") {
    $GLOBALS['_RESULT'] = array("content" => $profile->showProfileDocs($_REQUEST["td_id"], $_REQUEST["doc_id"], $_REQUEST["doc_type_id"]));
}

if ($_REQUEST["w"] === "showBasketMinForm") {
    $GLOBALS['_RESULT'] = array("content" => $shop->showBasketForm());
}

if ($_REQUEST["w"] === "shortSearchList") {
    $GLOBALS['_RESULT'] = array("content" => $shop->shortSearchList($_REQUEST["art_id"]));
}

if ($_REQUEST["w"] === "getOriginalNumbers") {
    $GLOBALS['_RESULT'] = array("content" => $showform->getOriginalNumbers($_REQUEST["art_id"]));
}

if ($_REQUEST["w"] === "getArticleApplicableForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->getArticleApplicableForm($_REQUEST["art_id"]));
}

if ($_REQUEST["w"] === "setCityDepartments") {
    $GLOBALS['_RESULT'] = array("content" => $shop->setCityDepartments($_REQUEST["city_ref"], $_REQUEST["department_ref"]));
}

if ($_REQUEST["w"] === "getOrderDeliveryBlock") {
    $GLOBALS['_RESULT'] = array("content" => $shop->getOrderDeliveryBlock($_REQUEST["delivery_id"], $_REQUEST["city_id"]));
}

if ($_REQUEST["w"] === "getOrderPaymentBlock") {
    $GLOBALS['_RESULT'] = array("content" => $shop->getOrderPaymentBlock($_REQUEST["payment_id"], $_REQUEST["delivery_id"]));
}

if ($_REQUEST["w"] === "validOrder") {
    $GLOBALS['_RESULT'] = array("content" => $shop->validOrder($_REQUEST["name"], $_REQUEST["phone"], $_REQUEST["city"], $_REQUEST["delivery"], $_REQUEST["delivery_type"], $_REQUEST["payment"], $_REQUEST["email"], $_REQUEST["comment"]));
}

if ($_REQUEST["w"] === "saveOrder") {
    $GLOBALS['_RESULT'] = array("content" => $shop->saveOrder($_REQUEST["user_id"], $_REQUEST["name"], $_REQUEST["phone"], $_REQUEST["city"], $_REQUEST["delivery"], $_REQUEST["delivery_type"], $_REQUEST["payment"], $_REQUEST["email"], $_REQUEST["comment"], $_REQUEST["recipient_name"], $_REQUEST["recipient_phone"], $_REQUEST["bonus_status"]));
}

if ($_REQUEST["w"] === "validDeliveryFields") {
    $GLOBALS['_RESULT'] = array("content" => $shop->validDeliveryFields($_REQUEST["delivery"], $_REQUEST["delivery_type"]));
}

if ($_REQUEST["w"] === "getBasketOrder") {
    $GLOBALS['_RESULT'] = array("content" => $shop->getBasketOrder($_REQUEST["delivery_id"], $_REQUEST["bonus_status"]));
}

if ($_REQUEST["w"] === "setDeliveryExpressDepartment") {
    $GLOBALS['_RESULT'] = array("content" => $shop->setDeliveryExpressDepartment($_REQUEST["delivery_express"]));
}

if ($_REQUEST["w"] === "setClientOrderInfo") {
    $GLOBALS['_RESULT'] = array("content" => $shop->setClientOrderInfo($_REQUEST["id"]));
}

if ($_REQUEST["w"] === "dropClientOrderInfo") {
    $GLOBALS['_RESULT'] = array("content" => $shop->dropClientOrderInfo($_REQUEST["id"]));
}

if ($_REQUEST["w"] === "saveOrderClient") {
    $GLOBALS['_RESULT'] = array("content" => $shop->saveOrderClient($_REQUEST["user_id"], $_REQUEST["name"], $_REQUEST["email"], $_REQUEST["pass"]));
}

if ($_REQUEST["w"] === "loginOrderClient") {
    $GLOBALS['_RESULT'] = array("content" => $client->loginOrderClient($_REQUEST["user_id"]));
}

if ($_REQUEST["w"] === "setClientRequest") {
    list($answer, $err) = $client->setClientRequest($_REQUEST["phone"], $_REQUEST["vin"], $_REQUEST["text"], $_REQUEST["status"]);
    $GLOBALS['_RESULT'] = array("answer" => $answer, "err" => $err);
}

if ($_REQUEST["w"] === "getUserSavedData") {
    list($status, $list, $info_id) = $shop->getUserSavedData($_REQUEST["user_id"], $_REQUEST["city"]);
    $GLOBALS['_RESULT'] = array("status" => $status, "list" => $list, "info_id" => $info_id);
}

if ($_REQUEST["w"] === "getCityVal") {
    $GLOBALS['_RESULT'] = array("content" => $shop->getCityVal($_REQUEST["search_text"]));
}

if ($_REQUEST["w"] === "setCityNPVal") {
    $GLOBALS['_RESULT'] = array("content" => $shop->setCityNPVal($_REQUEST["city_id"]));
}

if ($_REQUEST["w"] === "setCityAddress") {
    $GLOBALS['_RESULT'] = array("content" => $shop->setCityAddress($_REQUEST["city_id"]));
}

if ($_REQUEST["w"] === "hideOrderInfo") {
    $GLOBALS['_RESULT'] = array("content" => $shop->hideOrderInfo($_REQUEST["name"], $_REQUEST["phone"], $_REQUEST["city"]));
}

/*==== CATALOG ====*/

if ($_REQUEST["w"] === "showSearchDropdown") {
    $GLOBALS['_RESULT'] = array("content" => $search->showSearchDropdown($_REQUEST["text"]));
}

if ($_REQUEST["w"] === "showSearchDropdown2") {
    $GLOBALS['_RESULT'] = array("content" => $search->showSearchDropdown($_REQUEST["text_input"]));
}

if ($_REQUEST["w"] === "getCatalogueLink") {
    $GLOBALS['_RESULT'] = array("content" => $catalog->getCatalogueLink($_REQUEST["article_nr_search"]));
}

if ($_REQUEST["w"] === "searchCity") {
    $GLOBALS['_RESULT'] = array("content" => $shop->searchCity($_REQUEST["text"]));
}

if ($_REQUEST["w"] === "searchCityMain") {
    $GLOBALS['_RESULT'] = array("content" => $shop->searchCityMain());
}

if ($_REQUEST["w"] === "getCatalogListFilter") {
    $GLOBALS['_RESULT'] = array("content" => $catalog->getCatalogListFilter($_REQUEST["art"], $_REQUEST["brand"], $_REQUEST["bb"], $_REQUEST["price"], $_REQUEST["deliv"]));
}

/*==== GARAGE ====*/

if ($_REQUEST["w"] === "addToGarage") {
    $GLOBALS['_RESULT'] = array("content" => $automan->addToGarage($_REQUEST["typ_id"]));
}

if ($_REQUEST["w"] === "deleteAutoGarage") {
    $GLOBALS['_RESULT'] = array("content" => $automan->deleteAutoGarage($_REQUEST["auto_id"]));
}

if ($_REQUEST["w"] === "showGarageForm") {
    $GLOBALS['_RESULT'] = array("content" => $automan->showGarageForm());
}

if ($_REQUEST["w"] === "addGarageHistory") {
    $GLOBALS['_RESULT'] = array("content" => $automan->addGarageHistory($_REQUEST["sel_typ_id"]));
}

if ($_REQUEST["w"] === "getGarageAutoCount") {
    $GLOBALS['_RESULT'] = array("content" => $automan->getGarageAutoCount());
}

if ($_REQUEST["w"] === "showAutoHistory") {
    $GLOBALS['_RESULT'] = array("content" => $automan->showAutoHistory());
}

if ($_REQUEST["w"] === "dropAutoHistory") {
    $GLOBALS['_RESULT'] = array("content" => $automan->dropAutoHistory($_REQUEST["history_id"]));
}

/*==== MENU ====*/

if ($_REQUEST["w"] === "saveSellerForm") {
    $GLOBALS['_RESULT'] = array("content" => $menu->saveSellerForm($_REQUEST["company"], $_REQUEST["name"], $_REQUEST["phone"], $_REQUEST["email"], $_REQUEST["city_id"], $_REQUEST["comment"]));
}

if ($_REQUEST["w"] === "getSellerImage") {
    $GLOBALS['_RESULT'] = array("content" => $menu->getSellerImage());
}

if ($_REQUEST["w"] === "setTpoint") {
    $GLOBALS['_RESULT'] = array("content" => $client->setTpoint($_REQUEST["id"]));
}

if ($_REQUEST["w"] === "getMenuBar") {
    $GLOBALS['_RESULT'] = array("content" => $menu->getMenuBar(($_REQUEST["head_id"])));
}

/*==== MODALS ====*/

if ($_REQUEST["w"] === "getArticleApplModelForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->getArticleApplModelForm($_REQUEST["art_id"], $_REQUEST["mfa_id"]));
}

if ($_REQUEST["w"] === "getArticleApplModelInfoForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->getArticleApplModelInfoForm($_REQUEST["art_id"], $_REQUEST["typ_id"]));
}

if ($_REQUEST["w"] === "showInfoForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->showInfoForm($_REQUEST["art_id"]));
}

if ($_REQUEST["w"] === "showPhotoGallery") {
    $GLOBALS['_RESULT'] = array("content" => $showform->showPhotoGallery($_REQUEST["ref"]));
}

if ($_REQUEST["w"] === "showBrandForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->showBrandForm($_REQUEST["brand"]));
}

if ($_REQUEST["w"] === "deleteHistoryItem") {
    $GLOBALS['_RESULT'] = array("content" => $showform->deleteHistoryItem($_REQUEST["history_id"]));
}

if ($_REQUEST["w"] === "getBasketId") {
    $GLOBALS['_RESULT'] = array("content" => $showform->getBasketId($_REQUEST["art_id"], $_REQUEST["storage_id"]));
}
if ($_REQUEST["w"] === "updateBasketCount") {
    list($answer, $err, $new_amount) = $showform->updateBasketCount($_REQUEST["basket_id"], $_REQUEST["status"]);
    $GLOBALS['_RESULT'] = array("answer" => $answer, "err" => $err, "new_amount" => $new_amount);
}

if ($_REQUEST["w"] === "updateBasketCountChange") {
    list($answer, $err, $new_amount) = $showform->updateBasketCountChange($_REQUEST["basket_id"], $_REQUEST["amount"]);
    $GLOBALS['_RESULT'] = array("answer" => $answer, "err" => $err, "new_amount" => $new_amount);
}

/*==== CLIENT ====*/

if ($_REQUEST["w"] === "loginClient") {
    $GLOBALS['_RESULT'] = array("content" => $client->loginClient($_REQUEST["login"], $_REQUEST["password"]));
}

if ($_REQUEST["w"] === "logoutClient") {
    $GLOBALS['_RESULT'] = array("content" => $client->logoutClient());
}

if ($_REQUEST["w"] === "saveProfile") {
    $GLOBALS['_RESULT'] = array("content" => $client->saveProfile($_REQUEST["phone"], $_REQUEST["pass"], $_REQUEST["email"], $_REQUEST["name"]));
}

if ($_REQUEST["w"] === "saveRegistration") {
    $GLOBALS['_RESULT'] = array("content" => $client->saveRegistration($_REQUEST["phone"], $_REQUEST["pass"], $_REQUEST["email"], $_REQUEST["name"], $_REQUEST["client_category"], $_REQUEST["city_id"], $_REQUEST["tpoint_id"], $_REQUEST["mailing"]));
}

if ($_REQUEST["w"] === "checkRegClient") {
    $GLOBALS['_RESULT'] = array("content" => $client->checkRegClient($_REQUEST["phone"], $_REQUEST["type"]));
}

if ($_REQUEST["w"] === "validateOperator") {
    $GLOBALS['_RESULT'] = array("content" => $client->validateOperator($_REQUEST["phone"]));
}

if ($_REQUEST["w"] === "recoverPassword") {
    $GLOBALS['_RESULT'] = array("content" => $client->recoverPassword($_REQUEST["phone"]));
}

if ($_REQUEST["w"] === "validatePhone") {
    $GLOBALS['_RESULT'] = array("content" => $client->validatePhone($_REQUEST["phone"]));
}

if ($_REQUEST["w"] === "endValidation") {
    $GLOBALS['_RESULT'] = array("content" => $client->endValidation($_REQUEST["phone"], $_REQUEST["password"]));
}

//if ($_REQUEST["w"] === "toggleProductView") {
//    $GLOBALS['_RESULT'] = array("content" => $client->toggleProductView($_REQUEST["ds"]));
//}

if ($_REQUEST["w"] === "finishBonusPhone") {
    $GLOBALS['_RESULT'] = array("content" => $client->finishBonusPhone($_REQUEST["phone"], $_REQUEST["bonus"]));
}

if ($_REQUEST["w"] === "showProfileCheckForm") {
    $GLOBALS['_RESULT'] = array("content" => $profile->showProfileCheckForm($_REQUEST["data_start"], $_REQUEST["data_end"]));
}

/*==== LANGUAGE ====*/

if ($_REQUEST["w"] === "changeLangAlert") {
    $GLOBALS['_RESULT'] = array("content" => $lang->changeLangAlert($_REQUEST["message"], $_REQUEST["title"]));
}

if ($_REQUEST["w"] === "setSiteLang") {
    $GLOBALS['_RESULT'] = array("content" => $lang->setSiteLang($_REQUEST["id"]));
}

if ($_REQUEST["w"] === "changeLangJs") {
    $GLOBALS['_RESULT'] = array("content" => $lang->changeLangJs($_REQUEST["text"]));
}

/*==== SHOP ====*/

if ($_REQUEST["w"] === "moveToBasket") {
    list($old_amount, $art_name, $basket_count) = $shop->moveToBasket($_REQUEST["art_id"], $_REQUEST["brand_id"], $_REQUEST["count"], $_REQUEST["stock"], $_REQUEST["storage_id"], $_REQUEST["suppl_id"]);
    $GLOBALS['_RESULT'] = array("old_amount" => $old_amount, "art_name" => $art_name, "basket_count" => $basket_count);
}

if ($_REQUEST["w"] === "deleteFromBasket") {
    $GLOBALS['_RESULT'] = array("content" => $shop->deleteFromBasket($_REQUEST["art_id"], $_REQUEST["storage_id"]));
}

if ($_REQUEST["w"] === "checkBasketItem") {
    $GLOBALS['_RESULT'] = array("content" => $shop->checkBasketItem($_REQUEST["art_id"], $_REQUEST["storage_id"], $_REQUEST["status"]));
}

if ($_REQUEST["w"] === "saveFastOrder") {
    $GLOBALS['_RESULT'] = array("content" => $shop->saveFastOrder($_REQUEST["phone"]));
}

if ($_REQUEST["w"] === "letsFinishOrder") {
    list($answer, $err) = $shop->letsFinishOrder($_REQUEST["phone"], $_REQUEST["dataArticle"]);
    $GLOBALS['_RESULT'] = array("answer" => $answer, "err" => $err);
}

if ($_REQUEST["w"] === "saveFastOrderBasket") {
    $GLOBALS['_RESULT'] = array("content" => $shop->saveFastOrderBasket($_REQUEST["phone"], $_REQUEST["art_id"], $_REQUEST["brand_id"], $_REQUEST["count"], $_REQUEST["stock"], $_REQUEST["storage_id"], $_REQUEST["suppl_id"]));
}

if ($_REQUEST["w"] === "addFastOrder") {
    $GLOBALS['_RESULT'] = array("content" => $shop->addFastOrder($_REQUEST["phone"], $_REQUEST["art_id"], $_REQUEST["brand_id"], $_REQUEST["suppl_id"], $_REQUEST["storage_id"], $_REQUEST["amount"]));
}

if ($_REQUEST["w"] === "showBasketForm") {
    $GLOBALS['_RESULT'] = array("content" => $shop->showBasketForm($_REQUEST["cur"]));
}

if ($_REQUEST["w"] === "getAuthorizedUser") {
    $GLOBALS['_RESULT'] = array("content" => $client->getAuthorizedUser($_REQUEST["phone"]));
}

if ($_REQUEST["w"] === "updateBasketForm") {
    $GLOBALS['_RESULT'] = array("content" => $shop->updateBasketForm($_REQUEST["art_id"], $_REQUEST["count"], $_REQUEST["storage_id"]));
}

if ($_REQUEST["w"] === "updateBasketStatus") {
    $GLOBALS['_RESULT'] = array("content" => $shop->countBasket());
}

if ($_REQUEST["w"] === "showCityForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->showCityForm($_REQUEST["city_like"]));
}

if ($_REQUEST["w"] === "closeOrderArtUpdate") {
    $GLOBALS['_RESULT'] = array("content" => $profile->closeOrderArtUpdate($_REQUEST["dp_id"], $_REQUEST["art_id"], $_REQUEST["order_id"]));
}

if ($_REQUEST["w"] === "updateOrderArt") {
    $GLOBALS['_RESULT'] = array("content" => $profile->updateOrderArt($_REQUEST["order_id"]));
}

/*==== CATALOG TRIGGER LIST ====*/

if ($_REQUEST["w"] === "getSpecialOffersList") {
    $GLOBALS['_RESULT'] = array("content" => $menu->getSpecialOffersList($_REQUEST["template_id"], $_REQUEST["update_actions"])[0]);
}

if ($_REQUEST["w"] === "checkActionClients") {
    $GLOBALS['_RESULT'] = array("content" => $client->checkActionClients());
}

if ($_REQUEST["w"] === "setClientRequestDone") {
    $GLOBALS['_RESULT'] = array("content" => $catalog->setClientRequestDone());
}

if ($_REQUEST["w"] === "showModalForm") {
    $GLOBALS['_RESULT'] = array("content" => $showform->showModalForm($_REQUEST["form"]));
}

/*==== HOME CARS ====*/

if ($_REQUEST["w"] === "getCarsSearchContent") {
    list($list, $title, $nav, $tab, $skip) = $prod->getCarsSearchContent($_REQUEST["type"], $_REQUEST["attr"], $_REQUEST["group_id"]);
    $GLOBALS['_RESULT'] = array("list" => $list, "title" => $title, "nav" => $nav, "tab" => $tab, "skip" => $skip);
}

if ($_REQUEST["w"] === "getCarsSelectUser") {
    $GLOBALS['_RESULT'] = array("content" => $prod->getCarsSelectUser($_REQUEST["mfa_link"], $_REQUEST["model_link"], $_REQUEST["group_id"]));
}

if ($_REQUEST["w"] === "getCarsSearch") {
    $GLOBALS['_RESULT'] = array("content" => $prod->getCarsSearch($_REQUEST["mfa_link"], $_REQUEST["model_link"], $_REQUEST["group_id"]));
}

if ($_REQUEST["w"] === "clearCarsBlock") {
    $GLOBALS['_RESULT'] = array("content" => $prod->clearCarsBlock($_REQUEST["sel_tab"], $_REQUEST["cur_tab"]));
}

if ($_REQUEST["w"] === "showCarsForm") {
    $GLOBALS['_RESULT'] = array("content" => $prod->showCarsForm());
}

if ($_REQUEST["w"] === "showCarsGarageForm") {
    $GLOBALS['_RESULT'] = array("content" => $prod->showCarsGarageForm());
}

/*==== MENU ====*/

if ($_REQUEST["w"] === "getHeaderContent") {
    $GLOBALS['_RESULT'] = array("content" => $catalog->getHeaderContent($_REQUEST["head_id"]));
}

if ($_REQUEST["w"] === "getGroupsListValues") {
    $GLOBALS['_RESULT'] = array("content" => $menu->getGroupsListValues($_REQUEST["group_id"]));
}

if ($_REQUEST["w"] === "getGroupsLinks") {
    $GLOBALS['_RESULT'] = array("content" => $menu->getGroupsLinks($_REQUEST["group_id"],$_REQUEST["param_id"],$_REQUEST["value_id"]));
}

