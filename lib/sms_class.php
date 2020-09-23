<?php

class sms
{

    function auth_sms(){
        $this->url = 'https://sms-sender.km.ua/api/xml.api2.php';
        $this->login = 'toko';
        $this->password = 'zaq1478963';
    }

    function send_xml($xml){
        $this->auth_sms();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <package login="' . $this->login . '" password="' . $this->password . '">
        '.$xml.'
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

    function correct_nomber($phone){
        $phone=str_replace("+", "", $phone);
        $phone=str_replace(" ", "", $phone);
        $phone=str_replace("-", "", $phone);
        $phone=str_replace("(", "", $phone);
        $phone=str_replace(")", "", $phone);
        if (strlen($phone)>=10 and strlen($phone)<13){
            if (strlen($phone)==10){$phone="+38".$phone;}
            if (strlen($phone)==11){$phone="+3".$phone;}
            if (strlen($phone)==12){$phone="+".$phone;}
            if (strlen($phone)==13){ return $phone; }
        }
        if (strlen($phone)==13){ return $phone; }
    }

    function send_sms($sign,$nomber,$message){
        $nomber=$this->correct_nomber($nomber);
        $xml='<sendsms>
        <message><![CDATA['.iconv("Windows-1251","UTF-8",$message).']]></message>
        <recipient phone="'.$nomber.'" sender="'.$sign.'" /></sendsms>';
        print_r($xml);
        $result=$this->send_xml($xml);
        print_r($result);
        $xml = simplexml_load_string ( $result );
        print_r($xml);
        $answer="Message sent!";
        return $answer;
    }

    function ShowSMSForm(){
        $form="";$form_htm=RD."/tpl/sms_form.htm";if (file_exists("$form_htm")){ $form = file_get_contents($form_htm);}
        return $form;
    }

    function ShowSMSMailerForm(){
        $form="";$form_htm=RD."/tpl/sms_mailer_form.htm";if (file_exists("$form_htm")){ $form = file_get_contents($form_htm);}
        //	$form=str_replace("{tarif-list}",$this->show_tarif_type(),$form);
        $form=str_replace("{driver-list}",$this->show_driver_list(),$form);
        return $form;
    }

    function getBalans(){
        $xml = '<balans option="get"></balans>';
        $result=$this->send_xml($xml);
        $xml = simplexml_load_string ( $result );
        return " ".$xml." ";
    }

    function show_tarif_type(){$db=DbSingleton::getTokoDb();
        $list="<table border='0' class='t18_e'>";
        $r=$db->query("select * from tarif_type order by id asc;");$n=$db->num_rows($r);$k=0;
        for ($i=1;$i<=$n;$i++){$k++;
            $id=$db->result($r,$i-1,"id");
            $caption=$db->result($r,$i-1,"caption");
            if ($k==1){$list.="<tr>";}
                $list.="<td><input type='checkbox' id='ttype$i' value='$id' />-$caption;</td>";
            if ($k==1){$list.="</tr>";$k=0;}
        }
        $list.="</table><input type='hidden' id='kolTarif' value='$n'>";
        return $list;
    }

    function show_driver_list(){$db=DbSingleton::getTokoDb();
        $list="<table border='0' class='t18_e'>";$firms=array("2"=>"700-300");
        $r=$db->query("select * from drivers where ison='1' order by firm,code,id asc;");$n=$db->num_rows($r);$k=0;
        for ($i=1;$i<=$n;$i++){$k++;
            $id=$db->result($r,$i-1,"id");
            $firm=$db->result($r,$i-1,"firm");
            $code=$db->result($r,$i-1,"code");
            $name=$db->result($r,$i-1,"name");
            if ($k==1){$list.="<tr>";}
                $list.="<td><input type='checkbox' id='smsdriver$i' value='$id' />".$firms[$firm]." ($code) $name;</td>";
            if ($k==1){$list.="</tr>";$k=0;}
        }$list.="</table><input type='hidden' id='kolDrivers' value='$n'>";
        return $list;
    }

    function send_sms_mailer($firms,$driversAll,$ttype,$drivers,$message){
        $xml='<sendsms><message><![CDATA['.iconv("Windows-1251","UTF-8",$message).']]></message>';
        foreach($firms as $firm){ $phones="";$sign=$this->getFirmSmsSign($firm);
            if ($driversAll==1){
                $phones.=$this->getDriversByTarif($firm,0);
            }
            /*if ($driversAll==0){
                foreach($ttype as $ttp){
                    $phones.=$this->getDriversByTarif($firm,$ttp);
                }
            }*/
            $phones=str_replace("-",",",$phones);$phones=str_replace("/",",",$phones);
            $phones=explode(",",$phones);
            if ($phones!=""){
                foreach ($phones as $phone){
                    if ($phone!=""){
                        $xml.='<recipient phone="'.$phone.'" sender="'.$sign.'" />';
                    }
                }
            }
        }
        foreach($drivers as $driver){
            list($phones,$sign)=$this->getDriverInfo($driver);
            $phones=str_replace("-",",",$phones);$phones=str_replace("/",",",$phones);
            $phones=explode(",",$phones);
            if ($phones!=""){
                foreach ($phones as $phone){
                    if ($phone!=""){
                        $xml.='<recipient phone="'.$phone.'" sender="'.$sign.'" />';
                    }
                }
            }
        }
        $xml.='</sendsms>';
        $result=$this->send_xml($xml);
        $xml=simplexml_load_string ($result);
        $answer="All ok";
        return $answer;
    }

    function getDriverInfo($id){$db=DbSingleton::getTokoDb();
        $r=$db->query("SELECT mob, firm FROM drivers WHERE id='$id' limit 0,1;");$n=$db->num_rows($r); $phones=""; $sign="";
        for ($i=1;$i<=$n;$i++){
            $phones=$db->result($r,$i-1,"mob");
            $firm=$db->result($r,$i-1,"firm");$sign=$this->getFirmSmsSign($firm);
        }
        return array($phones,$sign);
    }

    function getDriversByTarif($firm,$tarif){$db=DbSingleton::getTokoDb();
        $where="";if ($tarif!=0){$where=" and `type`='$tarif'";}
        $r=$db->query("SELECT * FROM firm_tarif WHERE firm='$firm' $where order by id asc;");$n=$db->num_rows($r); $phones="";
        for ($i=1;$i<=$n;$i++){
            $id=$db->result($r,$i-1,"id");
            $phones.=$this->getTarifDriversPhones($firm,$id);
        }
        return $phones;
    }

    function getTarifDriversPhones($firm,$tarif){$db=DbSingleton::getTokoDb();
        $data_from=date('Y-m-d',(time() - (60 * 24 * 60 * 60)));
        $where="";if ($tarif!=0){$where=" AND je.tarif='$tarif'";}
        $r=$db->query("SELECT je.driver, dr.mob FROM journal_efir je 
            INNER JOIN drivers dr on (dr.id=je.driver) 
        WHERE dr.firm='$firm' AND je.ison='1' $where AND je.data<=CURDATE() AND je.data>='$data_from' group by dr.id;");
        $n=$db->num_rows($r);$phones="";
        for ($i=1;$i<=$n;$i++){ $phones.=$db->result($r,$i-1,"mob").","; }
        return $phones;
    }

    function getFirmSmsSign($id){ $db=DbSingleton::getTokoDb();
        $r=$db->query("select sms_sign from firm where id='$id' limit 0,1;");$n=$db->num_rows($r);
        if ($n==1) {return $db->result($r,0,"sms_sign");}
        if ($n==0) {return "";}
    }

}
?>