<?php
require_once "lib/class.phpmailer.php";

$mail = new PHPMailer();
try {
    $mail->isMail();
    $mail->addReplyTo('smartkidbeltukraine@gmail.com', 'smartkidbeltukraine');
    $mail->addAddress("gambitokgd@yahoo.com");
    $mail->Subject ="smartkidbelt";
    $mail->msgHTML("Hello world");
    $mail->send();
}  catch (Exception $e) { }

if(!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message has been sent";
}