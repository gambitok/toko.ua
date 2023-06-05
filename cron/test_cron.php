<?php
//
//error_reporting(E_ERROR);
//@ini_set('display_errors', true);
//@ini_set('html_errors', false);
//define('RDD', dirname(__FILE__));
//header('Content-Type: text/html; charset=windows-1251');
//date_default_timezone_set("Europe/Kiev");
//ini_set('memory_limit', '4096M');
//libxml_disable_entity_loader(true);
//
//require_once (RDD . "/../vendor/autoload.php");
//
//use Shuchkin\SimpleXLSX;
//
//$db = DbSingleton::getDbm();
//$client = new ClientClass();
//
//$suppl_id = 760;
//$fileName = '05-22-2023_0410062a79ea27c279e471f4d180b08d62b00a.xlsx';
//$inputFileName = '/var/www/gmail-serve/files/' . $suppl_id . '/' . $fileName;
//
//$statusProcess = 0;
//$answer = 0;
//$err = "";
//
//$answer = 0;
//$err = "";
//
//if (file_exists($inputFileName)) {
//
//    $inputFileType = (pathinfo($inputFileName))['extension'];
//
//    require_once RDD . '/../PHPExcel/Classes/PHPExcel/IOFactory.php';
//    require_once RDD . '/../PHPExcel/Classes/PHPExcel.php';
//
//    try {
//
//        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
//        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
//        $objPHPExcel = $objReader->load($inputFileName);
//
//    } catch (Exception $e) {
//        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
//    }
//
//    $inputFileType = strtolower($inputFileType);
//
//    if ($inputFileType !== "csv" && $inputFileType !== "xls" && $inputFileType !== "xlsx") {
//        $n = strrpos($inputFileName, ".");
//        $inputFileType = ($n === false) ? "" : substr($inputFileName, $n + 1);
//    }
//
//    if ($inputFileType === "csv" || $inputFileType === "xls" || $inputFileType === "xlsx") {
//
//        $sheet = $objPHPExcel->getSheet(0);
//        $highestRow = $sheet->getHighestRow();
//        $highestColumn = $sheet->getHighestColumn();
//
//        $rows = [];
//
//        if ($inputFileType === "xlsx") {
//            $xlsx = SimpleXLSX::parse($inputFileName);
//            foreach ($xlsx->rows() as $row) {
//                $rows[] = $row;
//            }
//        }
//
//        if ($inputFileType === "xls") {
//            for ($row = 1; $row <= $highestRow; $row++) {
//                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
//                    NULL,
//                    TRUE,
//                    FALSE);
//                $rows[] = $rowData[0];
//            }
//        }
//
//        if ($inputFileType === "csv") {
//            $csvData = file_get_contents($inputFileName);
//            $lines = explode(PHP_EOL, $csvData);
//            foreach ($lines as $line) {
//                $rows[] = str_getcsv($line, ";");
//            }
//        }
//
//        print_r($rows);
//
//        $template = $client->getSupplPriceTemplate($suppl_id);
//
//        if (!empty($template)) {
//            $header_row = $template['header_row'];
//
//            if ($header_row > 0) {
//                $header_row = $header_row - 1;
//                $arr = $rows[$header_row];
//                $data = $template['columns'];
//
//                if (!empty($arr)) {
//                    $count_success = 0;
//                    $count_error = "";
//                    foreach ($data as $key => $value) {
//                        $k = $key - 1;
//
//                        if ($arr[$k] === $value['text']) {
//                            $count_success++;
//                        } else {
//                            $count_error .= "column: " . $value['type'] . ($value['storage_id'] ?: '') . '; ';
//                        }
//                    }
//                    if ($count_success === count($data)) {
//
//                        $answer = 1;
//                        $statusProcess = 1;
//                        $err = "";
//
//                    } else {
//                        $answer = 0;
//                        $err = "Pattern mismatch: " . $count_error;
//                    }
//                } else {
//                    $answer = 0;
//                    $err = "Template without header!";
//                }
//            } else {
//                $answer = 0;
//                $err = "Template without header or there is no template in the file!";
//            }
//        } else {
//            $answer = 0;
//            $err = "Empty template!";
//        }
//
//    } else {
//        $answer = 0;
//        $err = "Wrong data format!";
//    }
//
//} else {
//    $answer = 0;
//    $err = "The file $inputFileName does not exists";
//}
//
//print 'File ' . $inputFileName . ' processed!' . "\n";
//print ($answer === 0 ? 'Error: ' : 'Success! ');
//print $err . "\n";
//
//
//
//
//
//
