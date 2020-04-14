<?php

$linka=findLinks();
$w=$linka[1];
$template_id=$template->getTemplateID($w);
$page=$_GET["page"]; $page!=NULL ?: $page=1;
$result=explode($w."/", $_SERVER["REQUEST_URI"], 2); $link=ltrim($result[1]);

if ($w=="") {
    $form=$catalogue->getHtmlForm("template/templates");
    $form=str_replace("{select_group}", $catalogue->showCatalogueTemplates(), $form);
    $form=$catalogue->replaceLang($form);
    $content=str_replace("{main_window}", $form, $content);
} else {
    $content=str_replace("{main_window}", $template_id=="" ? $parameters->getHtmlForm("404") : $parameters->showProductsForm($template_id, $page, $link), $content);
}
