<?php

class UkrPoshtaClass
{
    protected $bearer_code;

    public function __construct($code)
    {
        $this->bearer_code = $code;
    }

    public function connect($method, $request = "", $params = [])
    {
        $header = "Content-Type: application/json
        Authorization: Bearer " . $this->bearer_code;

        $ch = curl_init($this->get_url($method, $request, $params));

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
//
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($ch);

        $xml        = simplexml_load_string($response);
        $json       = json_encode($xml);
        $response   = json_decode($json,TRUE);

        curl_close($ch);

        return $response;
    }

    public function get_url($method, $request, $params): string
    {
        $link = "https://ukrposhta.ua/";

        $params_str = "";
        if (!empty($params)) {
            foreach($params as $key => $values) {
                $prefix = ($key === array_key_first($params)) ? "?" : "&";
                $params_str .= $prefix . $key . "=" . $values;
            }
        }

        return $link . $method . $request . $params_str;
    }

    public function getRegionsList(): array
    {
        $data = $this->connect("address-classifier-ws/", "get_regions_by_region_ua");

        $arr = [];
        foreach ($data["Entry"] as $value) {
            $arr[$value["REGION_ID"]] = iconv("UTF-8", "windows-1251", $value["REGION_UA"]);
        }

        return $arr;
    }

    public function getCitiesList($region_id): array
    {
        $data = $this->connect("address-classifier-ws/", "get_city_by_region_id_and_district_id_and_city_ua", ["region_id" => $region_id]);
        $arr = [];
        foreach ($data["Entry"] as $value) {
            $arr[$value["CITY_ID"]] = iconv("UTF-8", "windows-1251", $value["CITY_UA"]);
        }

        return $arr;
    }

    public function getDistrictsList($city_id): array
    {
        $data = $this->connect("address-classifier-ws/", "get_postoffices_by_postindex", ["poCityId" => $city_id]);

        $arr = [];
        foreach ($data["Entry"] as $value) {
            $arr[$value["ID"]] = iconv("UTF-8", "windows-1251", $value["PO_LONG"]);
        }

        return $arr;
    }

    public function printList($data, $sel_id = 0): string
    {
        $list = "<option value='0'>-Not selected-</option>";
        foreach ($data as $key => $value) {
            $sel = ($key === $sel_id) ? "selected" : "";
            $list .= "<option value='$key' $sel>$value</option>";
        }

        return $list;
    }

}