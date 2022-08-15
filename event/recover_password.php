<?php

session_start();

if ($client->checkUnRegClient()) {
    $ses_phone      = $_POST["recover_phone"];
    $ses_password   = "";
    $message        = "";

    $form = $client->getHtmlForm("profile/recover_phone");
    if (empty($_SESSION["captcha_code"])) {
        $_SESSION["captcha_code_status"] = 0;
    }

    if (empty($ses_phone)) {
        $message = "";
    } elseif ($client->checkRegClient($ses_phone) !== false) {
        $ses_captcha = $_POST["captcha_code"];

        $form = $client->getHtmlForm("recover_captcha");

        if (empty($ses_captcha)) {
            $message = "";
        } elseif ((empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) !== 0)) {
            $message = "{wrong_captcha_cap}!";
            $_SESSION['captcha_code_status'] = 0;
        } else {
            $message = "";
            $form = $client->getHtmlForm("profile/recover_password_done");
            $client->recoverPassword($ses_phone);
        }

    } else {
        $message = "{wrong_phone_cap}!";
        $_SESSION["captcha_code_status"] = 0;
    }

    $form = str_replace(array("{form_phone}", "{form_password}", "{message}"), array($ses_phone, $ses_password, (!empty($message)) ? "<label class=\"alert-danger\">$message</label>" : $message), $form);
    $content = str_replace("{main_window}", $form, $content);


} else {
    require_once("profile.php");
}