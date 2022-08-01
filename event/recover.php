<?php session_start();

$form = "";
$msg = "";

if (isset($_POST['Submit'])) {
    // code for check server side validation
    if (empty($_SESSION['captcha_code'] ) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) !== 0) {
        // Captcha verification is incorrect.
        $msg = "<span style='color:red'>The Validation code does not match!</span>";
        $form = $menu->getHtmlForm("test_cap");
    } else {
        // Captcha verification is Correct. Final Code Execute here!
        $form = $client->replaceLang($client->getHtmlForm("profile/recover_password_next"));
        $form = str_replace("{set_pass_recover}", $client->validatePhone($_POST["recover_phone"]), $form);
    }
}

if (empty($_POST['captcha_code'])) {
    $msg = "";
}

$form = str_replace("{form_phone}", $_POST["recover_phone"], $form);
$form = str_replace("{form_message}", $msg, $form);
$content = str_replace("{main_window}", $form, $content);
