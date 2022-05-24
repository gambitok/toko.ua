<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
header('Content-Type: text/html; charset=windows-1251');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

require_once (RDD . "/lib/Traits/Helper.php");
require_once (RDD . "/lib/Traits/Variables.php");
require_once (RDD . "/lib/mysql_class.php");
require_once (RDD . "/lib/LangClass.php");

$mail = new PHPMailer(true);

$host   = "smtp.office365.com";
$name   = "toko.robot@outlook.com";
$pass   = "Qwerty456852z";
$email  = "gambitokgd@gmail.com";
$date   = date("Y-m-d H:i:s");

$cname  = "TOKO GROUP";
$title  = "TOKO GROUP - {price_cap}";
$html   =  "
    <p>Доброго дня, 1</p>
    <p>У доданому файлі знаходиться актуальний прайс-лист станом на - $date</p>
    <p>З повагою ТОКО ГРУП.</p>
    <small>Якщо Ви не хочете отримувати новини такого типу в майбутньому, натисніть <a href='https://toko.ua/price_mailing/1/$user/'>тут</a>.</small><br>
    <small>ТОКО ГРУП ТОВ, ІПН:403029222256, ЄДРПОУ:40302920</small>";

$fname  = "price.csv";
$path   = RDD . "/PHPMailer/test.csv";

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
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
