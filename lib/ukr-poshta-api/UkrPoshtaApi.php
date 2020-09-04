<?php

class UkrPoshtaApi
{

    protected $key;

    public function __construct($key)
    {
        return $this
            ->setKey($key);
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    private function request($model, $method, $params = null)
    {

        $curl = curl_init();

        $params_str = "";
        foreach ($params as $key => $value) {
            $params_str .= "$key" . "=" . "$value";
        }
        if ($params_str!="") $params_str = "?" . $params_str;

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ukrposhta.ua/address-classifier-ws/get_postoffices_by_postindex?poCityId=10765",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer 7e769f5c-4ac8-32a8-bdf6-0f5bede5a204"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = new SimpleXMLElement($response);

//        $list="";
//        foreach ($response as $value) {
//            $postindex = $value->shortpdcitytype_ru->postindex;
//            $type = $value->type_short;
//            $address = $value->shortpdcitytype_ru->shortcitytype_en->address;
//
//            $postindex = iconv("UTF-8", "windows-1251", $postindex);
//            $type = iconv("UTF-8", "windows-1251", $type);
//            $address = iconv("UTF-8", "windows-1251", $address);
//
//            $list .= $postindex
//                . ", " . $type
//                . ", " . $address
//                . "\n"
//            ;
//        }
//
//        $list = iconv("UTF-8", "windows-1251", $list);

//        print_r($list);

//        var_dump(iconv("UTF-8", "windows-1251", $response));

        return $response;
    }

    public function getPostOffices($poCityId = 0)
    {
        return $this
            ->request('address-classifier-ws', 'get_postoffices_by_postindex', array(
                'poCityId' => $poCityId
            ));
    }

}