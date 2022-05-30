<?php

define("RDD", __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set("display_errors", true);
date_default_timezone_set("Europe/Kiev");
header("Content-Type: text/html; charset=windows-1251");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require RDD . "/PHPMailer/Exception.php";
require RDD . "/PHPMailer/PHPMailer.php";
require RDD . "/PHPMailer/SMTP.php";

require_once (RDD . "/lib/mysql_class.php");
require_once (RDD . "/lib/DbSingleton.php");
require_once (RDD . "/lib/Traits/Helper.php");
require_once (RDD . "/lib/Traits/Variables.php");
require_once (RDD . "/lib/LangClass.php");
require_once (RDD . "/lib/CatalogueClass.php");
require_once (RDD . "/lib/ClientClass.php");
require_once (RDD . "/lib/ProfileClass.php");
require_once (RDD . "/lib/ExRateClass.php");
require_once (RDD . "/lib/class.phpmailer.php");

$catalogue = new CatalogueClass();

$dbm = DbSingleton::getDbm();

$user = 15;

$r = $dbm->query("SELECT `name`, `email` FROM `A_CLIENTS_USERS` WHERE `id` = $user;");
$n = $dbm->num_rows($r);

$user_name  = $dbm->result($r, 0, "name");
$user_email = $dbm->result($r, 0, "email");
$filedata   = "TOKO_GROUP_price-list_" . $user . "_" . date("Y-m-d_H-i-s") . ".csv";
$filename   = $user . "/" . $filedata;
$list       = $catalogue->getPriceList($user);

$csv = "";
foreach ($list as $record) {
    foreach ($record as $rec) {
        $csv .= $rec . ';';
    }
    $csv .= "\n";
}

if (!file_exists(RDD . "/uploads/mailing/$user")) {
    mkdir(RDD . "/uploads/mailing/$user", 0777, true);
} elseif (file_exists(RDD . "/uploads/mailing/$user/")) {
    foreach (glob(RDD . "/uploads/mailing/$user/*") as $file) {
        unlink($file);
    }
}

$csv_handler = fopen(RDD . "/uploads/mailing/$filename", 'w') or die("Can't create file");
fwrite($csv_handler, $csv);
fclose($csv_handler);

$mail = new PHPMailer(true);
$mail->CharSet = 'windows-1251';

$host   = "smtp.office365.com";
$name   = "toko.robot@outlook.com";
$pass   = "Qwerty456852z";
$email  = $user_email;
$date   = date("Y-m-d H:i:s");

$cname  = "TOKO GROUP";
$title  = "TOKO GROUP - прайс";
$html   =  "
    <p>Доброго дня, $user_name</p>
    <p>У доданому файлі знаходиться актуальний прайс-лист станом на - $date</p>
    <p>З повагою ТОКО ГРУП.</p>
    <small>Якщо Ви не хочете отримувати новини такого типу в майбутньому, натисніть <a href='https://toko.ua/price_mailing/1/$user/'>тут</a>.</small><br>
    <small>ТОКО ГРУП ТОВ, ІПН:403029222256, ЄДРПОУ:40302920</small>";

$fname  = "price.csv";
$path   = RDD . "/uploads/mailing/$filename";

try {
    $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = $host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $name;
    $mail->Password   = $pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($name, $cname);
    $mail->addAddress($email);
    $mail->addReplyTo($name, $cname);

    $mail->isHTML(true);
    $mail->Subject = $title;
    $mail->Body    = $html;
    $mail->addAttachment($path, $fname);

    $mail->send();
    echo "Message has been sent";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

