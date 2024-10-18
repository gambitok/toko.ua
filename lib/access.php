<?php

function setCookies(): bool
{
    session_start();
    $catalogue = new CatalogueClass();
    $ses = session_id();
    if (empty($catalogue->getSessionID())) {
        setcookie("session_id", $ses, time() + (86400 * 30), "/");
    }

    return true;
}

function getSiteCurrentLink(): array
{
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $actual_link = str_replace(array("/uk/", "/en/"), "/", $actual_link);

    $ru = $uk = $en = $actual_link;
    $uk = str_replace("https://toko.ua/", "https://toko.ua/uk/", $uk);
    $en = str_replace("https://toko.ua/", "https://toko.ua/en/", $en);

    return compact("ru", "uk", "en");
}

function getContent($content)
{
    $menu       = new MenuClass();
    $shop       = new ShopClass();
    $profile    = new ProfileClass();
    $autoObj    = new AutoClass();

    $actual_link        = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $basketData         = $shop->countBasket();

    $path = getPath();
    if ($path === 'catalog') {
        $actual_link = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    }
    $actual_full_link   = "<link rel=\"canonical\" href=\"$actual_link\"/>";

    return str_replace(
        array("{canonical_link}", "{canonical_full_link}", "{contacts_bottom}", "{basket_count}", "{basket_style}", "{garage_style}", "{garage_status}", "{basket_sum}", "{profile_mobile}", "{list_social}", "{info_title}", "{lang_list}", "<h1></h1>"),
        array($actual_link, $actual_full_link, $menu->showContactsBottom(), $basketData[0], $basketData[1], "", $autoObj->getGarageAutoCount(), $shop->countSumBasket(), $profile->getProfileInfoMobile(), "<ul>" . getPhpContent("/tpl/menu/social_icons.php") . "</ul>", "", "", "<h1>" . getTitle(getPath()) . "</h1>"), 
    $content);
}

function checkLangVariable($variable): bool
{
    $db = DbSingleton::getTokoDb();
    $r = $db->query("SELECT 1 FROM `new_lang_wd` WHERE `variable` = '$variable' LIMIT 1;");
    $n = $db->num_rows($r);

    return ($n > 0);
}

function getMetaTag(): string
{
    return '
	<meta property="og:type" content="website" />
	<meta property="og:title" content="{site_title}" />
	<meta property="og:url" content="{canonical_link}" />
	<meta property="og:description" content="{site_description}" />
	<meta property="og:image" content="/favicon.png" />
	<meta property="og:site_name" content="{internet_shop} toko.ua" />
	';
}

function getTitle($path)
{
    $language = new LangClass();
    $path = str_replace("/", "", $path);
    $title = ($path !== "") ? getMoreTitle($path) : "{site_title}";

    return $language->replaceLangData($title);
}

function getMoreTitle($path): string
{
    $autoObj = new AutoClass();
    $catalogue = new CatalogueClass();
    $httpHost = findLinks();

    if ($path === "search") {
        $art_search = $catalogue->getUrlString($httpHost[1]);
        $art_search = rawurldecode($art_search);
        $art_search = $catalogue->getIconv($art_search);
        $brand_link = $catalogue->getUrlString($httpHost[2]);
        $brand_id   = ($brand_link !== "") ? $catalogue->getCatalogueBrandID($brand_link) : 0;

        if ($art_search === "") {
            $predTitle = "{site_title_short}";
        } elseif (empty($brand_id)) {
            $predTitle = "{search_results} $art_search | {site_title_short}";
        } else {
            $art_id     = $catalogue->getArticleId($art_search, $brand_id);
            $art_name   = $catalogue->getArticleName($art_id);
            $brand_name = $catalogue->getBrandName($brand_id);
            $art_search = strtoupper($art_search);
            $predTitle   = "$brand_name $art_search - $art_name | {site_title_short}";
        }
    }
    elseif ($path === "cars") {
        $mfa_link = $catalogue->getUrlString($httpHost[1]);
        $mod_link = $catalogue->getUrlString($httpHost[2]);

        if ($mfa_link === "") {
            $predTitle = "{site_cars_h1} {seo_site_toko}";
        } else {
            list($mfa_brand, $model_text) = $autoObj->getAutoDescriptionLink($mfa_link, $mod_link);
            list($mfa_id, $model) = $autoObj->getAutoIdsLink($mfa_link, $mod_link);
            $textTranslate = $autoObj->getCarManufactureTranslate($mfa_id, $model);

            $mm = "$mfa_brand $model_text";
            if ($textTranslate !== "") {
                $mm .= " $textTranslate";
            }

            $predTitle = "{details_on_cap}";
            ($mm === "") ?: $predTitle .= " $mm";
            $postfix = $catalogue->replaceLang("{seo_title_lvl3}");
            $postfix = str_replace("{title_lvl1}", $predTitle, $postfix);
            $predTitle = "$predTitle - $postfix";
        }
    }
    elseif (checkLangVariable("site_$path")) {
        $predTitle = "{site_$path} - {seo_title}";
    } else {
        $predTitle = "{seo_404_title}";
    }

    if ($path === "uk" || $path === "en") {
        $predTitle = "{site_title}";
    }

    return $predTitle;
}

function printBreadcrumbs($path): array
{
    $catalogue = new CatalogueClass();
    $menu = new MenuClass();
    $bread = findLinks();

    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (strpos($actual_link,"?") !== false) {
        $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
    }

    $icon = "<span> > </span>";
    $a_home = "
    <li class=\"cat-products-bread__item\" typeof=\"v:Breadcrumb\">
        <a href=\"" . $catalogue->getSiteLink() . "\" rel=\"v:url\" property=\"v:title\" title=\"{seo_site_toko}\">{seo_shop_toko}</a>
    </li>";
    $a_section = "
    <li class=\"cat-products-bread__item\" typeof=\"v:Breadcrumb\">
        <a href=\"" . $catalogue->getSiteLink() . "$path/\" rel=\"v:url\" property=\"v:title\">{site_$path}</a>
    </li>";
    $h_section = "{site_$path}";

    $list = "";
    $b_arr = [];
    $b_arr[1] = [
        "name" => "{seo_site_toko}",
        "item" => $catalogue->getSiteLink()
    ];

    switch ($path) {
        case "brands": {
            $brand_link = $bread[1];
            $b_arr[2] = [
                "name" => $h_section,
                "item" => $catalogue->getSiteLink() . "brands/"
            ];
            $predTitle = "$a_home $icon $h_section";

            if (!empty($brand_link)) {
                $brand_id = $catalogue->getBrandNameLink($brand_link);
                $brand_name = $catalogue->getBrandName($brand_id);
                $b_arr[3] = [
                    "name" => $brand_name,
                    "item" => $catalogue->getSiteLink() . "brands/" . $brand_link . "/"
                ];
                $predTitle = $a_home . $icon . "<li class=\"cat-products-bread__item\" typeof=\"v:Breadcrumb\">
                    <a href=\"https://toko.ua/brands/\" rel=\"v:url\" property=\"v:title\">$h_section</a>
                </li>" . $icon . $brand_name;
            }
            break;
        }
        case "search" : {
            $art_search  = $catalogue->getUrlString($bread[1]);
            $art_search  = rawurldecode($art_search);
            $art_search  = $catalogue->getIconv($art_search);
            $info        = $art_search;
            $predTitle    = "$a_home $icon {search_cap} $icon {search_results} $info";
            break;
        }
        case "news" : {
            $h_section = $catalogue->replaceLang($h_section);
            $h_section = str_replace("{h1_text}", "{news_cap}", $h_section);
            $b_arr[2] = [
                "name" => $h_section,
                "item" => $catalogue->getSiteLink() . "news/"
            ];

            if ($catalogue->getUrlString($bread[1]) === "state") {
                $a_section = $catalogue->replaceLang($a_section);
                $a_section = str_replace("{h1_text}", "{news_cap}", $a_section);
                $state_link = $catalogue->getUrlString($bread[2]);
                $state_name = $menu->getNewsStateTitle($state_link);
                $info = "$a_section $icon " . $state_name;
                $b_arr[3] = [
                    "name" => $state_name,
                    "item" => $actual_link
                ];
            } else {
                $info = $h_section;
            }
            $predTitle = "$a_home $icon $info";
            break;
        }
        case "reviews" : {
            $h_section = $catalogue->replaceLang($h_section);
            $h_section = str_replace("{h1_text}", "{review_state_cap}", $h_section);
            $b_arr[2] = [
                "name" => $h_section,
                "item" => $catalogue->getSiteLink() . "reviews/"
            ];

            if ($catalogue->getUrlString($bread[1]) === "state") {
                $a_section = $catalogue->replaceLang($a_section);
                $a_section = str_replace("{h1_text}", "{review_state_cap}", $a_section);
                $state_link = $catalogue->getUrlString($bread[2]);
                $state_name = $menu->getReviewStateTitle($state_link);
                $info = "$a_section $icon " . $state_name;
                $b_arr[3] = [
                    "name" => $state_name,
                    "item" => $actual_link
                ];
            } else {
                $info = $h_section;
            }
            $predTitle = "$a_home $icon $info";
            break;
        }
        case "order" : {
            $predTitle = "$a_home $icon $h_section";
            $b_arr[2] = [
                "name" => $h_section,
                "item" => $actual_link
            ];
            break;
        }
        default : {
            $predTitle = "";
            break;
        }
    }

    $form = "";

    if ($predTitle !== "") {
        $form = getHtmlForm("menu/breadcrumbs");
        $form = str_replace("{bread_text}", $predTitle, $form);
    }
    $form = $catalogue->replaceLang($form);

    foreach ($b_arr as $key => $val) {
        $title  = $val["name"];
        $link   = $val["item"];
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

function getHtmlForm($name)
{
    $form = "";
    $form_htm = RDD . "/tpl/$name.htm";

    if (file_exists($form_htm)) {
        $form = file_get_contents($form_htm);
    }

    return $form;
}

function getDescription($path)
{
    $language = new LangClass();
    $catalogue = new CatalogueClass();
    $httpHost = findLinks();
    $path = str_replace("/", "", $path);
    $prefix = "";
    $description = ($path !== "")
        ? "{seo_description} $prefix {seo_description2}"
        : "{seo_description} {seo_description2}";

    if ($path === "cars") {
        $description = "{site_cars_description}";
    }

    if ($path === "article") {
        $art_id = $httpHost[3];
        $art_search = $catalogue->getArticleDisplay($art_id);
        $brand_id   = $catalogue->getArticleBrand($art_id);
        $art_search = strtoupper($art_search);
        $brand_name = $catalogue->getBrandName($brand_id);
        $brand_name = strtoupper($brand_name);
        $art_name   = $catalogue->getArticleName($art_id);
        $description      = "$art_name $brand_name $art_search - {seo_description_article}";
        $description      = ltrim($description, " ");
    }

    if ($path === "brands") {
        $description = "{site_brands_description}";
    }

    if ($path === "catalog") {
        $description = "{seo_description} {seo_description2}";
    }

    $description = $language->replaceLangData($description);
    ($catalogue->getUrlNumber($_GET['page']) === 0) ?: $description = "";

    return $description;
}

function getKeywords($path)
{
    $language = new LangClass();
    $catalogue = new CatalogueClass();
    $path = str_replace("/", "", $path);
    $prefix = "";
    $keywords = ($path !== "") ? $prefix : "{site_keywords}";
    $keywords = $language->replaceLangData($keywords);
    ($catalogue->getUrlNumber($_GET['page']) === 0) ?: $keywords = "";

    return $keywords;
}

function getSiteLang($lang_id_sel = 0): string
{
    $language = new LangClass();

    if ($lang_id_sel === 0) {
        $lang_id = $language->getLanguage();
    } else {
        $lang_id = $lang_id_sel;
    }

    $lang_html = "ru";

    if ($lang_id === 2) {
        $lang_html = "uk";
    }

    if ($lang_id === 3) {
        $lang_html = "en";
    }

    return $lang_html;
}

function getPhpContent($file)
{
    ob_start();
    $file = RDD . $file;

    if (file_exists($file)) {
        include($file);
        $contents = ob_get_clean();
    } else {
        $contents = "File not exist!";
    }

    return $contents;
}

function replaceLangVariables($content)
{
    $site_link = getSiteCurrentLink();
    return str_replace(
        array("{site_link_ru}", "{site_link_uk}", "{site_link_en}"),
        array($site_link["ru"], $site_link["uk"], $site_link["en"]),
    $content);
}

function translateContent($content)
{
    $db = DbSingleton::getTokoDb();
    $language = new LangClass();
    $r = $db->query("SELECT `variable` FROM `new_lang_wd`;");
    $n = $db->num_rows($r);
    for ($i = 1; $i <= $n; $i++) {
        $code = $db->result($r, $i - 1, "variable");
        $word = $language->getLanguageName($code);
        $content = str_replace("{" . $code . "}", $word, $content);
    }

    return $content;
}

function getPath()
{
    $url = findUrl();
    $path = findPath();
    return (empty($path)) ? $url : $path;
}

function findPath()
{
	$link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

    if (substr($link, -1) !== "/") {
        $link .= "/";
    }

	$link = parse_url($link);
	$url = substr($link["path"], 1);
	$pos = strpos($url, "/");

    if ($pos) {
        $path = substr($url, 0, $pos + 1);
        $cur_path = substr($path, 0, -1);

        if ($cur_path === "uk" || $cur_path === "en") {
            $url = str_replace_first($path, "", $url);
            $pos = strpos($url, "/");
        }
        $path = substr($url, 0, $pos);
        $res = $path ?? $url;
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

function findNoIndex(): bool
{
    $link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $result = 0;
    $arr = ["?utm_", "?sort=", "?gclid=", "?UAH", "?RUR", "?WMZ", "?USD"];

    foreach ($arr as $a) {
        $pos = strripos($link, $a);

        if ($pos !== false) {
            $result++;
        }
    }

    return ($result > 0);
}

function findLanguage(): string
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

function findLanguageID($postfix): int
{
    $language_id = 1;

    if ($postfix === "uk") {
        $language_id = 2;
    }

    if ($postfix === "en") {
        $language_id = 3;
    }

    return $language_id;
}

function findLinks(): array
{
	$link = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

    if (substr($link, -1) !== "/") {
	    $link .= "/";
    }
	$link = parse_url($link);
    $sub_url = substr($link["path"], 1);
	$i = 0;
	$httpHost = [];
    $c = 0;

    while ($sub_url !== "") {
		$pos = strpos($sub_url, "/");

		if ($pos) {
            $path = substr($sub_url, 0, $pos + 1);
            $sub_url = str_replace_first($path, "", $sub_url);
            $cur_path = substr($path, 0, -1);

            if ($cur_path === "uk" || $cur_path === "en") {
                $c++;

                if ($c === 1) {
                    $i = 0;
                } else {
                    $httpHost[$i] = $cur_path;
                    $i++;
                }
            } else {
                $httpHost[$i] = $cur_path;
                $i++;
            }
		} else {
            break;
        }
	}

	return $httpHost;
}

function str_replace_first($from, $to, $content)
{
    $from = "/" . preg_quote($from, "/") . "/";

    return preg_replace($from, $to, $content, 1);
}

function getSeoText($seo_text)
{
    $form = getHtmlForm("menu/seo_text");

    return str_replace("{seo_text}", $seo_text, $form);
}

function getSeoTitleData()
{
    $dbe = DbSingleton::getTokoEmojiDb();
    $httpHost = findLinks();
    $router = $httpHost[0];
    $httpHostString = $httpHost;
    unset($httpHostString[0]);
    $httpHostString = implode("/", $httpHostString);
    $catalogue = new CatalogueClass();
    $postfix = $catalogue->getLangPostfix($catalogue->getLanguage());

    if ($router === "" || $router === NULL) {
        $router = "/";
        $get_link = "";
    } else {
        $get_link = " AND `LINK` = '$httpHostString'";
    }

    $r = $dbe->query("SELECT `TITLE_" . $postfix . "`, `DESCR_" . $postfix . "` FROM `T2_SEO_TITLE` WHERE `ROUTER` = '$router' AND `STATUS_AUTO` = 0 $get_link LIMIT 1;");
    $n = (int)$dbe->num_rows($r);

    if ($n === 0) {
        $seoTitleLink = explode("/", $httpHostString)[0];
        $r = $dbe->query("SELECT `TITLE_" . $postfix . "`, `DESCR_" . $postfix . "` FROM `T2_SEO_TITLE` WHERE `ROUTER` = '$router' AND `STATUS_AUTO` = 1 AND `LINK` = '$seoTitleLink' LIMIT 1;");
        $n = $dbe->num_rows($r);
    }
    if ($n > 0) {
        $title = $dbe->result($r, 0, "TITLE_$postfix");
        $description = $dbe->result($r, 0, "DESCR_$postfix");
    } else {
        return false;
    }

    return array($title, $description);
}

function getSeoTextForm()
{
    $db = DbSingleton::getTokoDb();
    $form = "";
    $query = "";
    $httpHost = findLinks();
    $router = $httpHost[0];
    $httpHostString = $httpHost;
    unset($httpHostString[0]);
    $httpHostString = implode("/", $httpHostString);
    $catalogue = new CatalogueClass();
    $page = $catalogue->getUrlNumber($_GET["page"]);
    $postfix = $catalogue->getLangPostfix($catalogue->getLanguage());

    if ($router === "" || $router === NULL) {
        $query = "SELECT `CONTENT_" . $postfix . "` FROM `T2_SEO_TEXT` WHERE `ROUTER` = '/' LIMIT 1;";
    }

    if ($router === "cars") {
        $link = $httpHostString;
        $query = "SELECT `CONTENT_" . $postfix . "` FROM `T2_SEO_TEXT` WHERE `ROUTER` = 'cars' AND `LINK` = '$link' LIMIT 1;";
    }

    if ($router === "catalog") {
        $link = $httpHostString;
        $city_link = $catalogue->getUrlString($_GET["city"]);
        if (($city_link !== "") && $catalogue->checkCityLink($city_link)) {
            $link .= "/?city=$city_link";
        }
        $link = ltrim($link, "/");

        $query = "SELECT `CONTENT_" . $postfix . "` FROM `T2_SEO_TEXT` WHERE `ROUTER` = 'catalog' AND `LINK` = '$link' LIMIT 1;";
    }

    $r = $db->query($query);
    $n = $db->num_rows($r);
    if (($n > 0) && $page <= 1) {
        $text = $db->result($r, 0, "CONTENT_$postfix");
        if ($text !== "") {
            $form = getSeoText($text);
        }
    }

    return $form;
}
