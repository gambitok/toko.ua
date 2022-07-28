<?php session_start();

$form = "";

if (isset($_POST['Submit'])) {
    //var_dump($_POST['recover_phone']);
    // code for check server side validation
    if (empty($_SESSION['captcha_code'] ) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) !== 0) {
        // Captcha verification is incorrect.
        $msg = "<span style='color:red'>The Validation code does not match!</span>";
        $form = $menu->getHtmlForm("test_cap");
    } else {
        // Captcha verification is Correct. Final Code Execute here!
        $msg = "<span style='color:green'>The Validation code has been matched.</span>";
    }
}

$content = str_replace("{main_window}", $form, $content);
