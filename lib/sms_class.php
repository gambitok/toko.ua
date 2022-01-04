<?php

class SmsClass
{

    public $url         = 'https://sms-sender.km.ua/api/xml.api2.php';
    public $login       = 'toko';
    public $password    = 'zaq1478963';

    public function send_xml($xml)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <package login="' . $this->login . '" password="' . $this->password . '">
        ' . $xml . '
        </package>';
        print_r($xml);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($ch);
        print_r($result);
        return $result;
    }

    public function correct_nomber($phone)
    {
        $phone = str_replace("+", "", $phone);
        $phone = str_replace(" ", "", $phone);
        $phone = str_replace("-", "", $phone);
        $phone = str_replace("(", "", $phone);
        $phone = str_replace(")", "", $phone);
        if (strlen($phone) >= 10 && strlen($phone) < 13) {
            if (strlen($phone) == 10) { $phone = "+38" . $phone; }
            if (strlen($phone) == 11) { $phone = "+3" . $phone; }
            if (strlen($phone) == 12) { $phone = "+" . $phone; }
            if (strlen($phone) == 13) { return $phone; }
        }
        if (strlen($phone) == 13) {
            return $phone;
        } else {
            return false;
        }
    }

    public function send_sms($sign, $nomber, $message)
    {
        $nomber = $this->correct_nomber($nomber);
        $xml = '<sendsms>
        <message><![CDATA[' . iconv("Windows-1251", "UTF-8", $message) . ']]></message>
        <recipient phone="' . $nomber . '" sender="' . $sign . '" /></sendsms>';
        print_r($xml);
        $result = $this->send_xml($xml);
        print_r($result);
        $xml = simplexml_load_string($result);
        print_r($xml);
        return "Message sent!";
    }

}
