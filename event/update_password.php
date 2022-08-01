<?php

session_start();

if ($client->checkUnRegClient()) {
    $ses_phone = $_POST["recover_phone"];
    $ses_password = "";
    $message = "";
    $form = $client->getHtmlForm("recover/recover_phone");

    if (empty($ses_phone)) {
        // EMPTY PHONE
        $message = "";
    } else {
        if ($client->checkRegClient($ses_phone) !== false) {
            $ses_captcha = $_POST["captcha_code"];
            $form = $client->getHtmlForm("recover_captcha");

            if (empty($ses_captcha)) {
                // EMPTY CAPTCHA
                $message = "";
            } else {
                if (empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) !== 0) {
                    //INCORRECT CAPTCHA
                    $message = "incorrect captcha!";
                } else {
                    // DONE CAPTCHA
                    $ses_password = (string)$_POST["recover_password"];
                    $new_password = (string)'0000';
                    $form = $client->getHtmlForm("recover/recover_password");

                    if (empty($ses_password) || empty($new_password)) {
                        // EMPTY PASSWORD
                        $message = "";
                    } else {
                        if ($ses_password === $new_password) {
                            // DONE
                            $form = $client->getHtmlForm("recover/recover_done");
                        } else {
                            //INCORRECT PASSWORD
                            $message = "incorrect password!";
                        }
                    }
                }
            }
        } else {
            // INCORRECT PHONE
            $message = "incorrect phone!";
        }
    }

    $form = str_replace("{form_phone}", $ses_phone, $form);
    $form = str_replace("{form_password}", $ses_password, $form);
    $form = str_replace("{message}", $message, $form);
    $content = str_replace("{main_window}", $form, $content);

} else {
    // EMPTY USER
    require_once("profile.php");
}