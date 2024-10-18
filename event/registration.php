<?php

global $client, $profile, $content;
$phone      = $_POST["reg_phone"];
$name       = $_POST["reg_name"];
$email      = $_POST["reg_email"];
$password   = $_POST["reg_password"];
$category   = $_POST["reg_category"];
$salePoint  = $_POST["reg_tpoint"];
$city       = $_POST["user_city"];
$mailing    = isset($_POST["reg_mailing"]) ? 1 : 0;

$fields = "
NAME :$name<br>
PHONE: $phone<br>
EMAIL: $email<br>
PASS: $password<br>
CATEG: $category<br>
TPOINT: $salePoint<br>
CITY: $city<br>
MAILINT: $mailing";

if ($client->checkUnRegClient()) {
    $message = "";
    $ses_phone      = $phone;

    $form = $profile->showRegistrationForm();

    if (empty($ses_phone)) {
        $message = "";
    } elseif ($client->checkRegClient($ses_phone) === false) {

        $ses_captcha = $_POST["captcha_code"];

        $form = $client->getHtmlForm("reg_captcha");

        if (empty($ses_captcha)) {
            $message = "";
        } elseif ((empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) !== 0)) {
            //INCORRECT CAPTCHA
            $message = "{wrong_captcha_cap}!";
        } else {
            $message = "{done}";
            $form = $client->getHtmlForm("profile/registration_done");

            $client->saveRegistration($phone, $password, $email, $name, $category, $city, $salePoint, $mailing);
        }
    } else {
        $message = "{user_already_logged}!";
    }

    $form = str_replace("{reg_phone}", $phone, $form);
    $form = str_replace("{reg_name}", $name, $form);
    $form = str_replace("{reg_email}", $email, $form);
    $form = str_replace("{reg_password}", $password, $form);
    $form = str_replace("{reg_category}", $category, $form);
    $form = str_replace("{reg_tpoint}", $salePoint, $form);
    $form = str_replace("{user_city}", $city, $form);
    $form = str_replace("{reg_mailing}", $mailing, $form);
    $form = str_replace("{message}", (!empty($message)) ? $client->getHtmlTag("label", $message, ['class' => 'alert-danger']) : $message, $form);

    $content = str_replace("{main_window}", $form, $content);

} else {
    require_once("profile.php");
}
