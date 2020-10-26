<?php

function setCookies() {
    session_start();
    $ses = session_id();
    if (!isset($_COOKIE["session_id"])) {
        setcookie("session_id", $ses, time() + (86400 * 30), "/"); // 86400 = 1 day
    }
    return true;
}

function getAccess() { $db=DbSingleton::getTokoDb();
    $list_ip = array();
    $r = $db->query("SELECT `ip` FROM `ip_access`;");
    $n = $db->num_rows($r);
    for ($i=1; $i<=$n; $i++){
        $ip = $db->result($r, $i-1, "ip");
        array_push($list_ip, $ip);
    }
    return $list_ip;
}

function getContent($content) {
    $menu = new MenuClass; $shop = new ShopClass; $profile = new ProfileClass; $automan = new AutoClass;
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (strpos($actual_link,"?") !== false) {
        $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
    }
    $content = str_replace("{info_row}", "", $content);
    $content = str_replace("{canonical_link}", $actual_link, $content);
    $content = str_replace("{contacts_bottom}", $menu->showContactsBottom(), $content);
    $content = str_replace("{basket_count}", $shop->countBasket()[0], $content);
    $content = str_replace("{basket_style}", $shop->countBasket()[1], $content);
    $content = str_replace("{garage_style}", $shop->countGarage(), $content);
    $content = str_replace("{garage_status}", $automan->getGarageAutoCount()[0], $content);
    $content = str_replace("{garage_style}", $automan->getGarageAutoCount()[1], $content);
    $content = str_replace("{basket_summ}", $shop->countSummBasket(), $content);
    $content = str_replace("{profile_mobile}", $profile->getProfileInfoMobile(), $content);
    $content = str_replace("{list_social}", getPhpContent("/tpl/menu/social_icons.php"), $content);
    $content = str_replace("{info}", "", $content);
    $content = str_replace("{info_title}", "", $content);
    $content = str_replace("{info2}", "", $content);
    $content = str_replace("{info3}", "", $content);
    $content = str_replace("{info2_more}", "", $content);
    $content = str_replace("{brand_info}", "", $content);
    $content = str_replace("{art_info}", "", $content);
    $content = str_replace("{lang_list}", "", $content);
    $content = str_replace("<h1></h1>", "<h1>".getTitle(getPath())."</h1>", $content);
    return $content;
}

function checkLangVariable($variable) { $db = DbSingleton::getTokoDb();
    $r = $db->query("SELECT * FROM `new_lang_wd` WHERE `variable`='$variable' LIMIT 1;"); $n = $db->num_rows($r);
    return ($n > 0);
}

function getTitle($path) {
    $language = new LangClass;
    $path = str_replace("/","",$path);
    $prefix = getMoreTitle($path);
    $title = ($path != "") ? "$prefix" : "{site_title}";
    $title = $language->replaceLang($title);
    return $title;
}

function getMoreTitle($path) {
    $automan = new AutoClass; $cat = new CatalogueClass; $menu = new MenuClass; $search = new SearchClass; $prod = new ProductsClass; $pattern = new PatternClass;

    $linka = findLinks();
    $pretitle = "";

    if ($path == "search") {
        $article_nr_search = $linka[1];
        $brand_link = $linka[2];
        $brand_id = ($brand_link!="") ? $cat->getCatalogueBrandID($brand_link) : 0;
        if ($article_nr_search == "") {
            $pretitle = "{site_title_short}";
        } else {
            if ($brand_id == 0) {
                $pretitle = "{search_results} $article_nr_search | {site_title_short}";
            } else {
                $art_id = $cat->getArticleId($article_nr_search, $brand_id);
                $art_name = $cat->getArticleName($art_id);
                $brand_name = $cat->getBrandName($brand_id);
                $article_nr_search = strtoupper($article_nr_search);
                $pretitle = "$brand_name $article_nr_search - $art_name | {site_title_short}";
            }
        }
    }
    elseif ($path == "article") {
        $art_id = $linka[3];
        $article_nr_search = $cat->getArticleDispl($art_id);
        $brand_id = $cat->getArticleBrand($art_id);
        $article_nr_search = strtoupper($article_nr_search);
        $brand_name = $cat->getBrandName($brand_id);
        $brand_name = strtoupper($brand_name);
        $art_name = $cat->getArticleName($art_id);
        $pretitle = "$art_name $brand_name $article_nr_search - {seo_title_article}";
        $pretitle = ltrim($pretitle," ");
    }
    elseif ($path == "products") {
        if ($linka[1] == "") {
            $pretitle = "{professional_catalogs_sh}";
        } else {
            $template_id = $pattern->getTemplateID($linka[1]);
            if ($template_id == "") {
                $pretitle = "{seo_404_title}";
            } else {
                $pager = "";
                if ($_GET['page'] !== NULL && $_GET['page'] > 0) {
                    $pager = " - {pager_cap}".$_GET['page'];
                }
                $result = explode($linka[1]."/", $_SERVER["REQUEST_URI"], 2);
                $link = ltrim($result[1]);
                if ($link != "") {
                    $template_filter_name = $pattern->showTemplateTitle($template_id, $pattern->getTemplateLinkParams($template_id, $link));
                } else {
                    $template_filter_name = $pattern->getTemplateName($template_id);
                }
                $pretitle = "$template_filter_name $pager | {site_title_short}";
            }
        }
    }
    elseif ($path == "cars") {
        $mfa_link = $linka[1];
        $mod_link = $linka[2];
        if ($mfa_link == ""){
            $pretitle = "{site_catalog} - {seo_details_title}";
        } else {
            list($mfa_brand, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
            list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
            $translit = $prod->getCarManufTranslit($mfa_id, $model);
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
    elseif ($path == "catalog") {
        $pager = "";
        if ($_GET['page'] !== NULL && $_GET['page'] > 0) {
            $pager = "- {pager_cap}".$_GET['page'];
        }
        $result = explode($linka[0]."/", $_SERVER["REQUEST_URI"], 2);
        $link = ltrim($result[1]);
        $arr = explode("/", $link);
        $str_link = ""; $mfa_link = ""; $mod_link = "";
        if (!empty($arr[0])) $str_link = $arr[0]; $filters = "";
        if (!empty($arr[3])) ((strpos($arr[3], "=") !== false)) ? $filters = $arr[3] : $filters = "";
        if (!empty($arr[2])) ((strpos($arr[2], "=") !== false)) ? $filters = $arr[2] : $mod_link = $arr[2];
        if (!empty($arr[1])) ((strpos($arr[1], "=") !== false)) ? $filters = $arr[1] : $mfa_link = $arr[1];

        $filters_cap = "";
        if ($filters != "") {
            $brand_ids = $search->getActiveFilters($filters);
            foreach ($brand_ids[0] as $brand_id) {
                $brand_name = $search->getBrandName($brand_id);
                $filters_cap .= " $brand_name,";
            }
        }
        $filters_cap = rtrim($filters_cap, ",");
        $filters_cap = ltrim($filters_cap, " ");
        $str_id = $automan->getStrNewLinkStr($str_link);
        $str_text = $automan->getStrNewDescr($str_id);

        if ($str_id == "") {
            $head_id = $automan->getHeadNewLinkStr($str_link);
            list($head_text) = $automan->getHeadNewDescr($head_id);
            $cat_text = $linka[2];
            if ($cat_text == "") {
                $pretitle = "$head_text - {seo_title_lvl1}";
            } else {
                $cat_id = $automan->getCatNewLinkStr($head_id, $cat_text);
                list($cat_text) = $automan->getCatNewDescr($cat_id);
                $pretitle = "$cat_text - {seo_title_lvl1}";
            }
        } else {
            $head_id = $automan->getHeadStr($str_id);
            list($head_text) = $automan->getHeadNewDescr($head_id);

            $seo_title_lvl2 = $cat->replaceLang("{seo_title_lvl2}");
            $seo_title_lvl2 = str_replace("{title_lvl1}", $head_text, $seo_title_lvl2);

            list($mfa_brand, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
            list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
            $translit = $prod->getCarManufTranslit($mfa_id, $model);

            if ($mfa_link != "") {
                $mm = "{for_cap} $mfa_brand $model_text";
                if ($translit != "") {
                    $mm .= " $translit";
                }
            } else {
                $mm = "";
            }
            $pretitle = "$str_text";
            ($mm == "") ?: $pretitle .= " $mm";
            ($filters_cap == "") ?: $pretitle .= ": $filters_cap";
            ($pager == "") ?: $pretitle .= " $pager";
            $pretitle .= " - $seo_title_lvl2";
        }

        if ($str_link == "") {
            $pretitle = "{site_catalog} - {seo_details_title}";
        }
    }
    elseif ($path == "news") {
        if ($linka[1] == "") {
            $pretitle = "{site_$path} - {seo_state_title}";
        }
        if ($linka[1] == "state") {
            $state_name = $menu->getNewsStateTitle($linka[2]);
            $pretitle = "$state_name - {seo_state_title}";
        }
    }
    elseif ($path == "reviews") {
        if ($linka[1] == "") {
            $pretitle = "{site_$path} - {seo_state_title}";
        }
        if ($linka[1] == "state") {
            $state_name = $menu->getReviewStateTitle($linka[2]);
            $pretitle = "$state_name - {seo_state_title}";
        }
    }
    else {
        if (checkLangVariable("site_$path")) {
            $pretitle = "{site_$path} - {seo_title}";
        } else {
            $pretitle = "{seo_404_title}";
        }
    }

    if ($path == "uk" || $path == "en") {
        $pretitle = "{site_title}";
    }

    return $pretitle;
}

function printBreadcrumbs($path) {
    $cat = new CatalogueClass; $menu = new MenuClass; $pattern = new PatternClass; $automan = new AutoClass; $search = new SearchClass; $language = new LangClass;
    $prefix = $language->getLangPrefix();
    $bread = findLinks();
    $section = $path;
    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if (strpos($actual_link,"?") !== false) {
        $actual_link = substr($actual_link, 0, strpos($actual_link, "?"));
    }

    $a_home = "<a href=\"https://toko.ua$prefix/\" title=\"{seo_site_toko}\">{seo_shop_toko}</a>";
    $a_section = "<a href=\"https://toko.ua$prefix/$section/\">{site_$section}</a>";
    $h_section = "{site_$section}";

    $list = "";
    $b_arr = [];
    $b_arr[1] = ["name" => "{seo_site_toko}", "item" => "https://toko.ua$prefix/"];

    switch ($section) {
        case "search" : {
            $article_nr_search = $bread[1];
            $info = $article_nr_search;
            $pretitle = "$a_home > {search_cap} > {search_results} $info";
            break;
        }
        case "article" : {
            $art_id = $bread[3];
            $info = $cat->getArticleText($art_id);
            $back = "<a href=\"https://toko.ua$prefix/catalog/\">{site_catalog}</a>";
            $pretitle = "$a_home > $back > $info";
            $b_arr[2] = ["name" => "{site_catalog}", "item" => "https://toko.ua$prefix/catalog/"];
            $b_arr[3] = ["name" => "$info", "item" => "$actual_link"];
            break;
        }
        case "catalog" : {
            $linka = findLinks();
            $result = explode($linka[0]."/", $_SERVER["REQUEST_URI"], 2);
            $link = ltrim($result[1]);
            $arr = explode("/", $link);
            $filters = [];
            $str_text = $mfa_link = $mod_link = "";
            if (!empty($arr[0])) $str_text = $arr[0];
            if (!empty($arr[3])) ((strpos($arr[4], "=") !== false)) ? $filters = $arr[4] : $filters = "";
            if (!empty($arr[3])) ((strpos($arr[3], "=") !== false)) ? $filters = $arr[3] : $mod_id_link = $arr[3];
            if (!empty($arr[2])) ((strpos($arr[2], "=") !== false)) ? $filters = $arr[2] : $mod_link = $arr[2];
            if (!empty($arr[1])) ((strpos($arr[1], "=") !== false)) ? $filters = $arr[1] : $mfa_link = $arr[1];

            $brand_ids = $search->getActiveFilters($filters);
            $active_filters = $brand_ids[0];
            $filters_cap = $search->getFiltersTitle($active_filters,1);
            $filters_cap = str_replace(": ", "", $filters_cap);

            $str_id = $automan->getStrNewLinkStr($str_text);

            if ($str_id=="") {
                $head_id = $automan->getHeadNewLinkStr($str_text);
            } else {
                $head_id = $automan->getHeadStr($str_id);
            }
            list($head_text, $head_link) = $automan->getHeadNewDescr($head_id);

            if ($str_text == "") {
                $pretitle = "$a_home > $h_section";
                $b_arr[2] = ["name"=>"$h_section", "item"=>"$actual_link"];
            } else {
                $title = $automan->getStrNewDescr($str_id);
                if ($title == "") {
                    $title = $automan->getStrDescr($str_id);
                }

                $str_link = $automan->getStrNewLink($str_id);
                $h1_text = $cat->getStaticH1("/catalog/$str_link/");
                if ($h1_text != "") {
                    $title = $h1_text;
                }

                if ($str_id == "") {
                    $cat_text = $bread[2];
                    if ($cat_text=="") {
                        $pretitle = "$a_home > $a_section > $head_text";
                    } else {
                        $cat_id = $automan->getCatNewLinkStr($head_id, $cat_text);
                        list($cat_text) = $automan->getCatNewDescr($cat_id);
                        $back = "<a href='/catalog/$head_link/'>$head_text</a>";
                        $pretitle = "$a_home > $a_section > $back > $cat_text";
                    }
                } else {
                    list($mfa_brand, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);

                    if ($mfa_link == "") {
                        $back = "<a href='/catalog/$head_link/'>$head_text</a>";
                        $back_str = "<a href='/catalog/$str_link/'>$title</a>";
                        $pretitle = "$a_home > $a_section > $back > ";
                        if ($filters_cap != "") {
                            $pretitle .= " $back_str > $filters_cap";
                        } else {
                            $pretitle .= " $title";
                        }
                    } else {
                        if ($mod_link == "") {
                            $back = "<a href='/catalog/$head_link/'>$head_text</a>";
                            $back_str = "<a href='/catalog/$str_link/'>$title</a>";
                            $back_mfa_brand = "<a href='/catalog/$str_link/$mfa_link'>$mfa_brand</a>";
                            $pretitle = "$a_home > $a_section > $back > $back_str > ";
                            if ($filters_cap != "") {
                                $pretitle .= " $back_mfa_brand > $filters_cap";
                            } else {
                                $pretitle .= " $mfa_brand";
                            }
                        } else {
                            $back = "<a href='/catalog/$head_link/'>$head_text</a>";
                            $back_str = "<a href='/catalog/$str_link/'>$title</a>";
                            $back_mfa = "<a href='/catalog/$str_link/$mfa_link/'>$mfa_brand</a>";
                            $back_model_text = "<a href='/catalog/$str_link/$mfa_link/$mod_link'>$model_text</a>";
                            $pretitle = "$a_home > $a_section > $back > $back_str > $back_mfa > ";
                            if ($filters_cap != "") {
                                $pretitle.=" $back_model_text > $filters_cap";
                            } else {
                                $pretitle.=" $model_text";
                            }
                        }
                    }
                }

                $b_arr[2] = ["name" => "{site_catalog}", "item" => "https://toko.ua$prefix/catalog/"];
                $b_arr[3] = ["name" => "$title", "item" => "$actual_link"];
            }
            break;
        }
        case "products" : {
            $template_link = $bread[1];
            $template_id = $pattern->getTemplateID($template_link);
            $result = explode($template_link."/", $_SERVER["REQUEST_URI"], 2);
            $link = ltrim($result[1]);
            if ($template_link == "") {
                $pretitle = "$a_home > $h_section";
                $b_arr[2] = ["name" => "$h_section", "item" => "$actual_link"];
            } else {
                if ($link == "") {
                    $title = $pattern->showTemplateTitle($template_id, $pattern->getTemplateLinkParams($template_id, $link));
                    $pretitle = "$a_home > $a_section > $title";
                    $b_arr[2] = ["name" => "$h_section", "item" => "https://toko.ua$prefix/products/"];
                    $b_arr[3] = ["name" => "$title", "item" => "$actual_link"];
                } else {
                    $template_name = $pattern->getTemplateName($template_id);
                    $back = "<a href=\"https://toko.ua/$section/$template_link/\">$template_name</a>";
                    $title = $pattern->showTemplateTitle($template_id, $pattern->getTemplateLinkParams($template_id, $link));
                    $pretitle = "$a_home > $a_section > $back > $title";
                    $b_arr[2] = ["name" => "$h_section", "item" => "https://toko.ua$prefix/products/"];
                    $b_arr[3] = ["name" => "$template_name", "item" => "https://toko.ua$prefix/$section/$template_link/"];
                    $b_arr[4] = ["name" => "$title", "item" => "$actual_link"];
                }
            }
            break;
        }
        case "news" : {
            $b_arr[2] = ["name" => "$h_section", "item" => "https://toko.ua$prefix/news/"];
            if ($bread[1] == "state") {
                $state_name = $menu->getNewsStateTitle($bread[2]);
                $info = "$a_section > ".$state_name;
                $b_arr[3] = ["name" => $state_name, "item" => "$actual_link"];
            } else {
                $info = "$h_section";
            }
            $pretitle = "$a_home > $info";
            break;
        }
        case "reviews" : {
            $b_arr[2] = ["name" => "$h_section", "item" => "https://toko.ua$prefix/reviews/"];
            if ($bread[1] == "state") {
                $state_name = $menu->getReviewStateTitle($bread[2]);
                $info = "$a_section > ".$state_name;
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
    $form_htm = RDD."/tpl/$name.htm";
    if (file_exists("$form_htm")) { $form = file_get_contents($form_htm); }
    // $form = iconv("UTF-8", "windows-1251", $form);
    return $form;
}

function getDescription($path) {
    $language = new LangClass; $cat = new CatalogueClass; $search = new SearchClass; $prod = new ProductsClass; $automan = new AutoClass;
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
        $pager = "";
        if ($_GET['page'] !== NULL && $_GET['page'] > 0) {
            $pager = "- {pager_cap}".$_GET['page'];
        }

        $result = explode($linka[0]."/", $_SERVER["REQUEST_URI"], 2);
        $link = ltrim($result[1]);
        $arr = explode("/", $link);
        $str_link = ""; $mfa_link = ""; $mod_link = "";
        if (!empty($arr[0])) $str_link = $arr[0]; $filters = "";
        if (!empty($arr[3])) ((strpos($arr[3], "=") !== false)) ? $filters = $arr[3] : $filters = "";
        if (!empty($arr[2])) ((strpos($arr[2], "=") !== false)) ? $filters = $arr[2] : $mod_link = $arr[2];
        if (!empty($arr[1])) ((strpos($arr[1], "=") !== false)) ? $filters = $arr[1] : $mfa_link = $arr[1];

        $filters_cap = "";
        if ($filters != "") {
            $brand_ids = $search->getActiveFilters($filters);
            foreach ($brand_ids[0] as $brand_id) {
                $brand_name = $search->getBrandName($brand_id);
                $filters_cap .= " $brand_name,";
            }
        }
        $filters_cap = rtrim($filters_cap, ",");
        $filters_cap = ltrim($filters_cap, " ");
        $str_id = $automan->getStrNewLinkStr($str_link);
        $str_text = $automan->getStrNewDescr($str_id);

        if ($str_id == "") {

            $head_id = $automan->getHeadNewLinkStr($str_link);
            list($head_text) = $automan->getHeadNewDescr($head_id);

            $cat_text = $linka[2];
            if ($cat_text == "") {
                $h1 = "$head_text";
            } else {
                $cat_id = $automan->getCatNewLinkStr($head_id, $cat_text);
                list($cat_text) = $automan->getCatNewDescr($cat_id);
                $h1 = "$cat_text";
            }

        } else {

            list($mfa_brand, $model_text) = $automan->getAutoDescrLink($mfa_link, $mod_link);
            list($mfa_id, $model) = $automan->getAutoIdsLink($mfa_link, $mod_link);
            $translit = $prod->getCarManufTranslit($mfa_id, $model);

            if ($mfa_link != "") {
                $mm = "{for_cap} $mfa_brand $model_text";
                if ($translit != "") {
                    $mm .= " $translit";
                }
            } else {
                $mm = "";
            }
            $h1 = "$str_text";
            ($mm == "") ?: $h1 .= " $mm";
            ($filters_cap == "") ?: $h1 .= ": $filters_cap";
            ($pager == "") ?: $h1.=" $pager";

        }
        $description = "$h1 - {seo_description_catalog1}, $h1 {seo_description_catalog2}";

        // /catalog
        if ($str_link == "") {
            $description = "{seo_description} {seo_description2}";
        }

    }
    $description = $language->replaceLang($description);
    ($_GET["page"] == 0) ?: $description = "";
    return $description;
}

function getKeywords($path) {
    $language = new LangClass;
    $path = str_replace("/", "", $path);
    $prefix = getMoreTitle($path);
    $keywords = ($path != "") ? "$prefix" : "{site_keywords}";
    $keywords = $language->replaceLang($keywords);
    ($_GET["page"] == 0) ?: $keywords = "";
    return $keywords;
}

function getSiteLang() {
    $language = new LangClass;
    $lang_id = $language->getLanguage();
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
        include ($file);
        $contents = ob_get_contents();
        ob_end_clean();
    } else {
        $contents = "File not exist!";
    }
    return $contents;
}

function translateContent($content) { $db = DbSingleton::getTokoDb();
    $language = new LangClass;
    $r = $db->query("SELECT `variable` FROM `new_lang_wd`;");
    $n = $db->num_rows($r);
    for ($i=1; $i<=$n; $i++) {
        $code = $db->result($r, $i-1, "variable");
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
    session_start();
    $_SESSION["lang"] = 1;
	$link = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
    if (substr($link, -1) != "/") {
        $link .= "/";
    }
	$link = parse_url($link);
	$url = substr($link["path"],1);
	$pos = strpos($url,"/");
    if ($pos) {
        $path = substr($url,0,$pos+1);
        $cur_path = substr($path, 0, -1);
        if ($cur_path == "uk" || $cur_path == "en") {
            //if ($cur_path=="ru") $_SESSION["lang"]=1;
            if ($cur_path == "uk") {
                $_SESSION["lang"] = 2;
            }
            if ($cur_path == "en") {
                $_SESSION["lang"] = 3;
            }
            $url = str_replace_first($path, "", $url);
            $pos = strpos($url, "/");
            $path = substr($url, 0, $pos);
        } else {
            $path = substr($url, 0, $pos);
            //$res = ($path != null) ? $path : $url;
        }
        $res = ($path != null) ? $path : $url;
    } else {
        $res = "";
    }
	return $res;
}

function findUrl() {
	$link = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$link = parse_url($link);
    return $link["path"];
}

function findLinks() {
    session_start();
    $_SESSION["lang"] = 1;
	$link = "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	if (substr($link, -1) != "/") {
	    $link .= "/";
    }
	$link = parse_url($link);
    $durl = substr($link["path"],1);
	$i = 0;
	$linka = [];
	while ($durl != ""){
		$pos = strpos($durl,"/");
		if ($pos) {
            $path = substr($durl,0, $pos + 1);
            $durl = str_replace_first($path, "", $durl);
            $cur_path = substr($path, 0, -1);
            if ($cur_path == "uk" || $cur_path == "en") {
                if ($cur_path == "uk") {
                    $_SESSION["lang"] = 2;
                }
                if ($cur_path == "en") {
                    $_SESSION["lang"] = 3;
                }
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
    $from = "/".preg_quote($from, "/") . "/";
    return preg_replace($from, $to, $content, 1);
}

function getSeoText($seo_text) {
    $form = getHtmlForm("menu/seo_text");
    $form = str_replace("{seo_text}", $seo_text, $form);
    return $form;
}


