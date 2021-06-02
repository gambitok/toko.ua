<?php

function setCookies() {
    session_start();
    $catalogue = new CatalogueClass();
    $ses = session_id();
    if (empty($catalogue->getSessionID())) {
        setcookie("session_id", $ses, time() + (86400 * 30), "/");
    }
    return true;
}

//function getAccess() {
//    $db = DbSingleton::getTokoDb();
//    $list_ip = array();
//    $r = $db->query("SELECT `ip` FROM `ip_access`;");
//    $n = $db->num_rows($r);
//    for ($i = 1; $i <= $n; $i++) {
//        $ip = $db->result($r, $i - 1, "ip");
//        array_push($list_ip, $ip);
//    }
//    return $list_ip;
//}

function getContent($content) {
    $menu = new MenuClass();
    $shop = new ShopClass();
    $profile = new ProfileClass();
    $automan = new AutoClass();
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $actual_full_link = "<link rel=\"canonical\" href=\"$actual_link\"/>";
    if (strpos($actual_link,"?") !== false) {
        // $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
        $actual_full_link = "";
    }
    $content = str_replace("{canonical_link}", $actual_link, $content);
    $content = str_replace("{canonical_full_link}", $actual_full_link, $content);
    $content = str_replace("{contacts_bottom}", $menu->showContactsBottom(), $content);
    $content = str_replace("{basket_count}", $shop->countBasket()[0], $content);
    $content = str_replace("{basket_style}", $shop->countBasket()[1], $content);
    $content = str_replace("{garage_style}", "", $content);
    $content = str_replace("{garage_status}", $automan->getGarageAutoCount(), $content);
    $content = str_replace("{basket_summ}", $shop->countSummBasket(), $content);
    $content = str_replace("{profile_mobile}", $profile->getProfileInfoMobile(), $content);
    $content = str_replace("{list_social}", "<ul>" . getPhpContent("/tpl/menu/social_icons.php") . "</ul>", $content);
    $content = str_replace("{info_title}", "", $content);
    $content = str_replace("{lang_list}", "", $content);
    $content = str_replace("<h1></h1>", "<h1>" . getTitle(getPath()) . "</h1>", $content);
    return $content;
}

function checkLangVariable($variable) { $db = DbSingleton::getTokoDb();
    $r = $db->query("SELECT * FROM `new_lang_wd` WHERE `variable` = '$variable' LIMIT 1;");
    $n = $db->num_rows($r);
    return ($n > 0);
}

function getTitle($path) {
    $language = new LangClass();
    $path = str_replace("/", "", $path);
    $prefix = getMoreTitle($path);
    $title = ($path != "") ? "$prefix" : "{site_title}";
    return $language->replaceLangData($title);
}

function getMoreTitle($path) {
    $automan = new AutoClass();
    $cat = new CatalogueClass();
    $menu = new MenuClass();

    $linka = findLinks();
    $pretitle = "";

    if ($path == "search") {
        $article_nr_search = $cat->getUrlString($linka[1]);
        $brand_link = $cat->getUrlString($linka[2]);
        $brand_id = ($brand_link != "") ? $cat->getCatalogueBrandID($brand_link) : 0;
        if ($article_nr_search == "") {
            $pretitle = "{site_title_short}";
        } elseif ($brand_id == 0) {
            $pretitle = "{search_results} $article_nr_search | {site_title_short}";
        } else {
            $art_id = $cat->getArticleId($article_nr_search, $brand_id);
            $art_name = $cat->getArticleName($art_id);
            $brand_name = $cat->getBrandName($brand_id);
            $article_nr_search = strtoupper($article_nr_search);
            $pretitle = "$brand_name $article_nr_search - $art_name | {site_title_short}";
        }
    }
    elseif ($path == "article") {
        $art_id = $cat->getUrlNumber($linka[3]);
        $article_nr_search = $cat->getArticleDispl($art_id);
        $brand_id = $cat->getArticleBrand($art_id);
        $article_nr_search = strtoupper($article_nr_search);
        $brand_name = $cat->getBrandName($brand_id);
        $brand_name = strtoupper($brand_name);
        $art_name = $cat->getArticleName($art_id);
        $pretitle = "$art_name $brand_name $article_nr_search - {seo_title_article}";
        $pretitle = ltrim($pretitle," ");
    }
    elseif ($path == "cars") {
        $mfa_link = $cat->getUrlString($linka[1]);
        $mod_link = $cat->getUrlString($linka[2]);
        if ($mfa_link == ""){
            $pretitle = "{site_catalog} - {seo_details_title}";
        } else {
            list($mfa_brand, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
            list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
            $translit = $automan->getCarManufTranslit($mfa_id, $model);
            if ($mfa_link != "") {
                $mm = "$mfa_brand $model_text";
                if ($translit != "") {
                    $mm .= " $translit";
                }
            } else {
                $mm = "";
            }
            $pretitle = "{details_on_cap}";
            ($mm == "") ?: $pretitle .= " $mm";
            $postfix = $cat->replaceLang("{seo_title_lvl3}");
            $postfix = str_replace("{title_lvl1}", $pretitle, $postfix);
            $pretitle = "$pretitle - $postfix";
        }
    }
    elseif ($path == "news") {
        if ($cat->getUrlString($linka[1]) == "") {
            $pretitle = "{site_$path} - {seo_state_title}";
        }
        if ($cat->getUrlString($linka[1]) == "state") {
            $state_link = $cat->getUrlString($linka[2]);
            $state_name = $menu->getNewsStateTitle($state_link);
            $pretitle = "$state_name - {seo_state_title}";
        }
    }
    elseif ($path == "reviews") {
        if ($cat->getUrlString($linka[1]) == "") {
            $pretitle = "{site_$path} - {seo_state_title}";
        }
        if ($cat->getUrlString($linka[1]) == "state") {
            $state_link = $cat->getUrlString($linka[2]);
            $state_name = $menu->getReviewStateTitle($state_link);
            $pretitle = "$state_name - {seo_state_title}";
        }
    }
    elseif (checkLangVariable("site_$path")) {
        $pretitle = "{site_$path} - {seo_title}";
    } else {
        $pretitle = "{seo_404_title}";
    }

    if ($path == "uk" || $path == "en") {
        $pretitle = "{site_title}";
    }

    return $pretitle;
}

function printBreadcrumbs($path) {
    $cat = new CatalogueClass();
    $menu = new MenuClass();
    $bread = findLinks();
    $section = $path;
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (strpos($actual_link,"?") !== false) {
        $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
    }

    $a_home = "<a href=\"" . $cat->getSiteLink() . "\" title=\"{seo_site_toko}\">{seo_shop_toko}</a>";
    $a_section = "<a href=\"" . $cat->getSiteLink() . "$section/\">{site_$section}</a>";
    $h_section = "{site_$section}";

    $list = "";
    $b_arr = [];
    $b_arr[1] = ["name" => "{seo_site_toko}", "item" => $cat->getSiteLink()];

    switch ($section) {
        case "search" : {
            $article_nr_search = $cat->getUrlString($bread[1]);
            $info = $article_nr_search;
            $pretitle = "$a_home > {search_cap} > {search_results} $info";
            break;
        }
        case "article" : {
            $art_id = $cat->getUrlNumber($bread[3]);
            $info = $cat->getArticleText($art_id);
            $back = "<a href=\"" . $cat->getSiteLink() . "$cat->catalog_link/\">{site_catalog}</a>";
            $pretitle = "$a_home > $back > $info";
            $b_arr[2] = ["name" => "{site_catalog}", "item" => "" . $cat->getSiteLink() . "$cat->catalog_link/"];
            $b_arr[3] = ["name" => "$info", "item" => "$actual_link"];
            break;
        }
        case "news" : {
            $b_arr[2] = ["name" => "$h_section", "item" => "" . $cat->getSiteLink() . "news/"];
            if ($cat->getUrlString($bread[1]) == "state") {
                $state_link = $cat->getUrlString($bread[2]);
                $state_name = $menu->getNewsStateTitle($state_link);
                $info = "$a_section > " . $state_name;
                $b_arr[3] = ["name" => $state_name, "item" => "$actual_link"];
            } else {
                $info = "$h_section";
            }
            $pretitle = "$a_home > $info";
            break;
        }
        case "reviews" : {
            $b_arr[2] = ["name" => "$h_section", "item" => "" . $cat->getSiteLink() . "reviews/"];
            if ($cat->getUrlString($bread[1]) == "state") {
                $state_link = $cat->getUrlString($bread[2]);
                $state_name = $menu->getReviewStateTitle($state_link);
                $info = "$a_section > " . $state_name;
                $b_arr[3] = ["name" => $state_name, "item" => "$actual_link"];
            } else {
                $info = "$h_section";
            }
            $pretitle = "$a_home > $info";
            break;
        }
        case "contacts" :
        case "signin" :
        case "registration" :
        case "profile" :
        case "sell" :
        case "special_offers" :
        case "basket" :
        case "order" : {
            $pretitle = "$a_home > $h_section";
            $b_arr[2] = ["name" => "$h_section", "item" => "$actual_link"];
            break;
        }
        default : {
            $pretitle = "";
            break;
        }
    }

    $form = "";
    if ($pretitle != "") {
        $form = getHtmlForm("menu/breadcrumbs");
        $form = str_replace("{bread_text}", $pretitle, $form);
    }
    $form = $cat->replaceLang($form);

    foreach ($b_arr as $key => $val) {
        $title = $val["name"];
        $link = $val["item"];
        $list .= "
        {
            \"@type\": \"ListItem\",
            \"position\": $key,
            \"name\": \"$title\",
            \"item\": \"$link\"
        },";
    }
    $list = rtrim($list, ",");

    $script = "";
    if (count($b_arr) > 1) {
        $script = "
        <script type=\"application/ld+json\">
        {
            \"@context\": \"http://schema.org\",
            \"@type\": \"BreadcrumbList\",
            \"itemListElement\": [
                $list
            ]
        }
        </script>";
    }

    return array($form, $script);
}

function getHtmlForm($name) {
    $form = "";
    $form_htm = RDD . "/tpl/$name.htm";
    if (file_exists("$form_htm")) {
        $form = file_get_contents($form_htm);
    }
    // $form = iconv("UTF-8", "windows-1251", $form);
    return $form;
}

function getDescription($path) {
    $language = new LangClass();
    $cat = new CatalogueClass();
    $linka = findLinks();
    $path = str_replace("/", "", $path);
    $prefix = getMoreTitle($path);
    $description = ($path != "") ? "{seo_description} $prefix {seo_description2}" : "{seo_description} {seo_description2}";
    if ($path == "article") {
        $art_id = $linka[3];
        $article_nr_search = $cat->getArticleDispl($art_id);
        $brand_id = $cat->getArticleBrand($art_id);
        $article_nr_search = strtoupper($article_nr_search);
        $brand_name = $cat->getBrandName($brand_id);
        $brand_name = strtoupper($brand_name);
        $art_name = $cat->getArticleName($art_id);
        $description = "$art_name $brand_name $article_nr_search - {seo_description_article}";
        $description = ltrim($description, " ");
    }
    if ($path == "catalog") {
        $description = "{seo_description} {seo_description2}";
    }
    $description = $language->replaceLangData($description);
    ($cat->getUrlNumber($_GET['page']) == 0) ?: $description = "";
    return $description;
}

function getKeywords($path) {
    $language = new LangClass();
    $cat = new CatalogueClass();
    $path = str_replace("/", "", $path);
    $prefix = getMoreTitle($path);
    $keywords = ($path != "") ? "$prefix" : "{site_keywords}";
    $keywords = $language->replaceLangData($keywords);
    ($cat->getUrlNumber($_GET['page']) == 0) ?: $keywords = "";
    return $keywords;
}

function getSiteLang($lang_id_sel = 0) {
    $language = new LangClass();
    //$lang_id = $language->getLanguageData();
    if ($lang_id_sel == 0) {
        $lang_id = $language->getLanguage();
    } else {
        $lang_id = $lang_id_sel;
    }
    $lang_html = "ru";
    if ($lang_id == 1) {
        $lang_html = "ru";
    }
    if ($lang_id == 2) {
        $lang_html = "uk";
    }
    if ($lang_id == 3) {
        $lang_html = "en";
    }
    return $lang_html;
}

function getPhpContent($file) {
    ob_start();
    $file = RDD . $file;
    if (file_exists($file)) {
        include($file);
        $contents = ob_get_contents();
        ob_end_clean();
    } else {
        $contents = "File not exist!";
    }
    return $contents;
}

function translateContent($content) { $db = DbSingleton::getTokoDb();
    $language = new LangClass();
    $r = $db->query("SELECT `variable` FROM `new_lang_wd`;");
    $n = $db->num_rows($r);
    for ($i = 1; $i <= $n; $i++) {
        $code = $db->result($r, $i - 1, "variable");
        $word = $language->getLanguageName($code);
        //$word = iconv("windows-1251", "UTF-8", $word);
        $content = str_replace("{" . $code . "}", $word, $content);
    }
    return $content;
}

function getPath() {
    $url = findUrl();
    $path = findPath();
    if ($path == "") {
        $path = $url;
    }
    return $path;
}

function findPath() {
//    $language = new LangClass();
//    $language->setLangID(1);
	$link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    if (substr($link, -1) != "/") {
        $link .= "/";
    }
	$link = parse_url($link);
	$url = substr($link["path"], 1);
	$pos = strpos($url, "/");
    if ($pos) {
        $path = substr($url, 0, $pos + 1);
        $cur_path = substr($path, 0, -1);
        if ($cur_path == "uk" || $cur_path == "en") {
//            if ($cur_path == "uk") {
//                $language->setLangID(2);
//            }
//            if ($cur_path == "en") {
//                $language->setLangID(3);
//            }
            $url = str_replace_first($path, "", $url);
            $pos = strpos($url, "/");
        }
        $path = substr($url, 0, $pos);
        $res = ($path != null) ? $path : $url;
    } else {
        $res = "";
    }
	return $res;
}

function findUrl()
{
	$link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	$link = parse_url($link);
    return $link["path"];
}

function findNoIndex()
{
    $link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

    $result = 0;
    $arr = ["?utm_", "?sort=", "?gclid=", "?UAH", "?RUR", "?WMZ", "?USD"];
    foreach ($arr as $findme) {
        $pos = strripos($link, $findme);
        if ($pos !== false) {
            $result++;
        }
    }
    return ($result > 0);
}

function findLanguage()
{
    $postfix = "";
    $link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    if (strpos($link, "/uk/") !== false) {
        $postfix = "uk";
    }
    if (strpos($link, "/en/") !== false) {
        $postfix = "en";
    }
    return $postfix;
}

function findLanguageID($postfix) {
    $language_id = 1;
    if ($postfix == "uk") {
        $language_id = 2;
    }
    if ($postfix == "en") {
        $language_id = 3;
    }
    return $language_id;
}

function findLinks()
{
//    $language = new LangClass();
//    $language->setLangID(1);
	$link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	if (substr($link, -1) != "/") {
	    $link .= "/";
    }
	$link = parse_url($link);
    $durl = substr($link["path"], 1);
	$i = 0;
	$linka = [];
	while ($durl != "") {
		$pos = strpos($durl, "/");
		if ($pos) {
            $path = substr($durl,0, $pos + 1);
            $durl = str_replace_first($path, "", $durl);
            $cur_path = substr($path, 0, -1);
            if ($cur_path == "uk" || $cur_path == "en") {
//                if ($cur_path == "uk") {
//                    $language->setLangID(2);
//                }
//                if ($cur_path == "en") {
//                    $language->setLangID(3);
//                }
                $i = 0;
            } else {
                $linka[$i] = $cur_path;
                $i++;
            }
		} else break;
	}
	return $linka;
}

function str_replace_first($from, $to, $content) {
    $from = "/" . preg_quote($from, "/") . "/";
    return preg_replace($from, $to, $content, 1);
}

function getSeoText($seo_text) {
    $form = getHtmlForm("menu/seo_text");
    $form = str_replace("{seo_text}", $seo_text, $form);
    return $form;
}

function getSeoTextForm()
{
    $db = DbSingleton::getTokoDb();
    $form = "";
    $query = "";
    $linka = findLinks();
    $router = $linka[0];
    $str_linka = $linka;
    unset($str_linka[0]);
    $str_linka = implode("/", $str_linka);
    $catalogue = new CatalogueClass();
    $page = $catalogue->getUrlNumber($_GET["page"]);
    $postfix = $catalogue->getLangPostfix($catalogue->getLanguage());
    if ($router == "") {
        $query = "SELECT `CONTENT_$postfix` FROM `T2_SEO_TEXT` WHERE `ROUTER` = '/' LIMIT 1;";
    }
    if ($router == "cars") {
        $link = $str_linka;
        $query = "SELECT `CONTENT_$postfix` FROM `T2_SEO_TEXT` WHERE `ROUTER` = 'cars' AND `LINK` = '$link' LIMIT 1;";
    }
    if ($router == "catalog") {
        $link = $str_linka;
        $query = "SELECT `CONTENT_$postfix` FROM `T2_SEO_TEXT` WHERE `ROUTER` = 'catalog' AND `LINK` = '$link' LIMIT 1;";
    }
    $r = $db->query($query);
    $n = $db->num_rows($r);
    if ($n > 0) {
        if ($page <= 1) {
            $text = $db->result($r, 0, "CONTENT_$postfix");
            if ($text != "") {
                $form = getSeoText($text);
            }
        }
    }
    return $form;
}

