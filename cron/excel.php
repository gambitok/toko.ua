<?php
define('RDD', dirname (__FILE__));
error_reporting(0);
@ini_set('display_errors', false);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
@ini_set('display_errors', true);
date_default_timezone_set("Europe/Kiev");

$user = $_GET["user"];

$filename = scandir(RDD . "/../uploads/$user")[2];

$filename = RDD . "/../uploads/$user/" . $filename;

$row = 1;
$d = [];
if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        $row++;
        for ($c=0; $c < $num; $c++) {

            $col = iconv("windows-1251", "UTF-8", $data[$c]);

            $d[$row][] = $col;
        }
    }
    fclose($handle);
}
//    $data = array(
//        array(0 => "Gary Riley", 1 => "gary@hotmail.com", 2 => "Male", 3 => "United Kingdom"),
//        array(0 => "Edward Siu", 1 => "siu.edward@gmail.com", 2 => "Male", 3 => "Switzerland"),
//        array(0 => "Betty Simons", 1 => "simons@example.com", 2 => "Female", 3 => "Australia"),
//        array(0 => "Frances Lieberman", 1 => "lieberman@gmail.com", 2 => "Female", 3 => "United Kingdom")
//    );

$data = $d;

    function filterData(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    }

// Excel file name for download
    $fileName = "codexworld_export_data-" . date('Ymd') . ".xlsx";

// Headers for download
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header("Content-Type: application/vnd.ms-excel");

    $flag = false;
    foreach ($data as $row) {
        if (!$flag) {
            // display column names as first row
            echo implode("\t", array_keys($row)) . "\n";
            $flag = true;
        }
        // filter data
        array_walk($row, 'filterData');
        echo implode("\t", array_values($row)) . "\n";
    }


exit;