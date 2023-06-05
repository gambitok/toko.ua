<?php

error_reporting(E_ERROR);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
header('Content-Type: text/html; charset=windows-1251');
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '2048M');

define('RDD', __DIR__);
require_once (RDD . "/vendor/autoload.php");

try {
    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($inputFileName);
} catch (Exception $e) {
    die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
}
//
//
//
//if (strtolower($inputFileType) !== "csv" && strtolower($inputFileType) !== "xls" && strtolower($inputFileType) !== "xlsx") {
//    $n = strrpos($inputFileName, ".");
//    $inputFileType = ($n === false) ? "" : substr($inputFileName, $n + 1);
//}
//
//if (strtolower($inputFileType) === "csv" || strtolower($inputFileType) === "xls" || strtolower($inputFileType) === "xlsx") {
//
//    $sheet = $objPHPExcel->getSheet(0);
//    $highestRow = $sheet->getHighestRow();
//    $highestColumn = $sheet->getHighestColumn();
//    $rows = [];
//
//    if (strtolower($inputFileType) === "xlsx") {
//        for ($row = 1; $row <= $highestRow; $row++) {
//            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
//                NULL,
//                TRUE,
//                FALSE);
//            $rows[] = $rowData;
//        }
//    }
//    if (strtolower($inputFileType) === "xls") {
//        for ($row = 1; $row <= $highestRow; $row++) {
//            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
//                NULL,
//                TRUE,
//                FALSE);
//            $rows[] = $rowData;
//        }
//    }
//    if (strtolower($inputFileType) === "csv") {
//        $csvData = file_get_contents($inputFileName);
//        $lines = explode(PHP_EOL, $csvData);
//        foreach ($lines as $line) {
//            $rows[][0] = str_getcsv($line, ";");
//        }
//    }
//
//}
