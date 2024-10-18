<?php

$content = "";

require_once (RDD . "/vendor/autoload.php");

$max_row_count = 5;

use Shuchkin\SimpleXLSX;
$xlsxObj = new SimpleXLSX();

function getFileCsv($inputFileName, $max_row_count = PHP_INT_MAX): array
{
    $rows = [];
    $csvData = file_get_contents($inputFileName);
    $lines = explode(PHP_EOL, $csvData, $max_row_count);
    foreach ($lines as $line) {
        $rows[] = str_getcsv($line, ";");
    }

    return $rows;
}
function getFileXls($inputFileName, $max_row_count = 0): array
{
    $rows = [];
    try {
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    try {
        $sheet = $objPHPExcel->getSheet();

        if ($max_row_count === 0) {
            $highestRow = $sheet->getHighestRow();
        } else {
            $highestRow = $max_row_count;
        }
        $highestColumn = $sheet->getHighestColumn();

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL,
                TRUE,
                FALSE);
            $rows[] = $rowData[0];
        }
    } catch (PHPExcel_Exception $e) {
        print $e->getMessage();
    }

    return $rows;
}
function getFileXlsx($xlsxObj, $inputFileName, $max_row_count = 0): array
{
    $rows = [];
    $xlsx = $xlsxObj->parse($inputFileName);

    if ($max_row_count === 0) {
        $list = $xlsx->rows();
    } else {
        $list = $xlsx->rows(0, $max_row_count);
    }

    foreach ($list as $row) {
        $rows[] = $row;
    }

    return $rows;
}
//function processString($str, $maxCount) {
//    $arr = explode(",", $str, $maxCount);
//
//    $elem = $arr[$maxCount - 1][0];
//
//    $arr[$maxCount - 1] = $elem;
//
//    return $arr;
//}
//
//$result = processString("1,2,3,4,5,6,7,8,9,10", 5);
//
//print_r($result);

$inputFileName = '/var/www/gmail-serve/files/1247/08-22-2024_1225486cdd60ea0045eb7a6ec44c54d29ed402.xlsx';
//$inputFileName = '/var/www/gmail-serve/files/1247/08-22-2024_12230237a749d808e46495a8da1e5352d03cae.xls';
//$inputFileName = '/var/www/gmail-serve/files/1247/08-22-2024_06480384d9ee44e457ddef7f2c4f25dc8fa865.csv';

$rows = getFileXlsx($xlsxObj, $inputFileName);
//$rows = getFileXls($inputFileName);
//$rows = getFileCsv($inputFileName);

print_r($rows);