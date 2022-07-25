<?php

define('RDD', __DIR__);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");
header('Content-Type: text/html; charset=windows-1251');

print(RDD);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require RDD . "/../lib/Plugins/PHPMailer/Exception.php";
require RDD . "/../lib/Plugins/PHPMailer/PHPMailer.php";
require RDD . "/../lib/Plugins/PHPMailer/SMTP.php";

require_once (RDD . "/../lib/DbSingleton.php");
require_once (RDD . "/../lib/Traits/Helper.php");
require_once (RDD . "/../lib/Traits/Variables.php");
require_once (RDD . "/../lib/CatalogueClass.php");
require_once (RDD . "/../lib/ClientClass.php");
require_once (RDD . "/../lib/ProfileClass.php");
require_once (RDD . "/../lib/LangClass.php");
require_once (RDD . "/../lib/ExRateClass.php");

$catalogue = new CatalogueClass();

$dbm = DbSingleton::getDbm();

$r = $dbm->query("SELECT `id`, `name`, `email` FROM `A_CLIENTS_USERS` WHERE `export_status` = 1 AND `status` = 1;");
$n = $dbm->num_rows($r);

print("COUNT: " . $n . "\n");

for ($i = 1; $i <= $n; $i++) {
    $user       = $dbm->result($r, $i - 1, "id");
    $user_name  = $dbm->result($r, $i - 1, "name");
    $user_email = $dbm->result($r, $i - 1, "email");
    $filedata   = "TOKO_GROUP_price-list_" . $user . "_" . date("Y-m-d_H-i-s") . ".csv";
    $filename   = $user . "/" . $filedata;
    $list       = $catalogue->getPriceList($user);

    print($user_name . "\n");

    $csv = "";
    foreach ($list as $record) {
        foreach ($record as $rec) {
            $csv .= $rec . ';';
        }
        $csv .= "\n";
    }

    if (!file_exists(RDD . "/../uploads/mailing/$user")) {
        mkdir(RDD . "/../uploads/mailing/$user", 0777, true);
    } elseif (file_exists(RDD . "/../uploads/mailing/$user/")) {
        foreach (glob(RDD . "/../uploads/mailing/$user/*") as $file) {
            unlink($file);
        }
    }

    $csv_handler = fopen(RDD . "/../uploads/mailing/$filename", 'wb') or die("Can't create file");
    fwrite($csv_handler, $csv);
    fclose($csv_handler);

    $mail = new PHPMailer(true);
    $mail->CharSet = 'windows-1251';

    $host   = "smtp.yandex.ru";
    $name   = "robot@toko.ua";
    $pass   = "R0b0tB0b0t";
    $email  = $user_email;
    $date   = date("Y-m-d H:i:s");

    $cname  = "TOKO.ua";
    $title  = "Updated price list for today ($date)";

    $html = "
<p>Доброго дня, $user_name</p>

<p>У доданому файлі знаходиться актуальний прайс-лист станом на - $date</p>

<p>—-</p>
<p>З повагою,
служба доставки інформації TOKO.ua</p>

<p>TOKO GROUP LTD.</p>
<p>7, Post-Volynska, Kyiv, 03061, Ukraine</p>
<p>94/1, Prospect Mira, Khmelnitsky 29015, Ukraine</p>
<p>mobile phone #1: +38 097 080 30 60</p>
<p>mobile phone #2: +38 050 080 30 60</p>
<p>mobile phone #3: +38 093 080 30 60</p>
<p>e-mail: robot@toko.ua</p>

<p>Цей лист було надіслано на $user_email від robot@toko.ua.</p>
<p>Миттєве видалення за допомогою <a href='https://toko.ua/price_mailing/1/$user/'>SafeUnsubscribe</a>.</p>

<p>This email was sent to $user_email by robot@toko.ua.</p>
<p>Instant removal with <a href='https://toko.ua/price_mailing/1/$user/'>SafeUnsubscribe</a>.</p>";

    $fname  = "price.csv";
    $path   = RDD . "/../uploads/mailing/$filename";

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

}