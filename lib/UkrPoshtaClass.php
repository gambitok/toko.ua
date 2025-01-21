<?php

class UkrPoshtaClass extends CatalogueClass
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
            $arr[$value["REGION_ID"]] = $value["REGION_UA"];
        }

        return $arr;
    }

    public function getCitiesList($region_id): array
    {
        $data = $this->connect("address-classifier-ws/", "get_city_by_region_id_and_district_id_and_city_ua", ["region_id" => $region_id]);
        $arr = [];
        foreach ($data["Entry"] as $value) {
            $arr[$value["CITY_ID"]] = $value["CITY_UA"];
        }

        return $arr;
    }

    public function getCitiesListAll(): array
    {
        $data = $this->connect("address-classifier-ws/", "get_city_by_region_id_and_district_id_and_city_ua");
        $arr = [];
        foreach ($data["Entry"] as $value) {
            $arr[] = [
                'CITY_ID'   => $value['CITY_ID'],
                'CITY_UA'   => $value["CITY_UA"],
                'REGION_ID' => $value['REGION_ID']];
        }

        return $arr;
    }

//    public function getDistrictsList($city_id): array
//    {
//        $data = $this->connect("address-classifier-ws/", "get_postoffices_by_postindex", ["poCityId" => $city_id]);
//
//        $arr = [];
//        foreach ($data["Entry"] as $value) {
//            $arr[$value["ID"]] = $value["PO_LONG"];
//        }
//
//        return $arr;
//    }

    public function getDistrictsListAll(): array
    {
        $data = $this->connect("address-classifier-ws/", "get_postoffices_by_postindex");

        $arr = [];
        foreach ($data["Entry"] as $value) {
            $district_name = $value["PO_LONG"];
            $district_name = str_replace('"', "", $district_name);
            $arr[] = [
                'DISTRICT_ID'   => $value['ID'],
                'DISTRICT_NAME' => $district_name,
                'CITY_ID'       => $value['PDCITY_ID']];
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

    public function add_table($data, $table_name, $city_id): int
    {
        $db = DbSingleton::getTokoDb();

        foreach($data as $id => $name) {
            $db->query("INSERT INTO `$table_name` (`ID`, `CITY_ID`, `NAME`) VALUES ($id, $city_id, \"$name\");");
        }

        return count($data);
    }

//    public function write_file($data, $filename = "up.csv"): string
//    {
//        $file = fopen(RDD . "/../files/$filename", 'wb') or die("error");
//
//        foreach ($data as $key => $value) {
//            print "$key, $value";
//            fputcsv($file, array($key, $value), ";");
//        }
//
//        fclose($file);
//
//        return "done";
//    }
//
//    public function open_file($filename): string
//    {
//        $list = "";
//        if (($handle = fopen(RDD . "/files/$filename", 'rb')) !== FALSE) {
//            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
//                foreach ($data as $cValue) {
//                    $d = explode(";", $cValue);
//                    $list .= $d[0] . " - " . $d[1] . "<br />\n";
//                }
//            }
//            fclose($handle);
//        }
//
//        return $list;
//    }

}