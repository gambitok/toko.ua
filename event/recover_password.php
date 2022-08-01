<?php session_start();

if ($client->checkUnRegClient()) {

    $ses_phone = $_POST["recover_phone"];

    if (isset($_POST['Submit'])) {
        if (empty($_SESSION['recover_phone'] ) || strcasecmp($_SESSION['recover_phone'], $_POST['recover_phone']) !== 0) {
            $msg = "Done!";
        }
    }

    if (empty($ses_phone)) {
        $form = $menu->getHtmlForm("profile/recover_password");
        $form = str_replace(array("{ses_phone}", "{ses_message}"), array("", ""), $form);
    }

    if (!empty($ses_phone)) {
        if ($client->checkRegClient($ses_phone) !== false) {
            //next
            //open captcha = content main window
            include_once RDD . "/event/recover.php";
            $msg = "Validate phone";

            $form = "";

        } else {
            //err message
            $msg = "Error phone!";

            $form = $menu->getHtmlForm("profile/recover_password");
            $form = str_replace(array("{ses_phone}", "{ses_message}"), array($ses_phone, $msg), $form);
        }
    }

    $content = str_replace("{main_window}", $form, $content);

} else {
    require_once("profile.php");
}
