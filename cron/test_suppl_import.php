<?php

error_reporting(E_ERROR);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
define('RDD', dirname(__FILE__));
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '4096M');

require_once (RDD . "/../vendor/autoload.php");

$max_row_count = 5;

use Shuchkin\SimpleXLSX;
$xlsxObj = new SimpleXLSX();

$db = DbSingleton::getDbm();
$client = new ClientClass();

$r = $db->query("SELECT `ID`, `SUPPL_ID`, `FILENAME`, `STATUS_COUNTER` FROM `IMPORT_SUPPLIER_FILES` WHERE `STATUS` = 1;");
$n = $db->num_rows($r);

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

if ($n > 0) {

    for ($i = 1; $i <= $n; $i++) {
        $id         = (int)$db->result($r, $i - 1, "ID");
        $suppl_id   = (int)$db->result($r, $i - 1, "SUPPL_ID");
        $fileName   = $db->result($r, $i - 1, "FILENAME");
        $counter    = (int)$db->result($r, $i - 1, "STATUS_COUNTER");

        if ($suppl_id > 0) {

            $db->query("UPDATE `IMPORT_SUPPLIER_FILES` SET `STATUS` = 0 WHERE `ID` = $id LIMIT 1;");

            $start = microtime(true);

            $inputFileName = '/var/www/gmail-serve/files/' . $suppl_id . '/' . $fileName;

            $answer = 0;
            $err = "";

            if (file_exists($inputFileName)) {

                $inputFileType = (pathinfo($inputFileName))['extension'];

                require_once RDD . '/../PHPExcel/Classes/PHPExcel/IOFactory.php';
                require_once RDD . '/../PHPExcel/Classes/PHPExcel.php';

                if (!in_array(strtolower($inputFileType), ['csv', 'xls', 'xlsx'])) {
                    $n = strrpos($inputFileName, ".");
                    $inputFileType = ($n === false) ? "" : substr($inputFileName, $n + 1);
                }

                $inputFileType = strtolower($inputFileType);

                if (in_array($inputFileType, ['csv', 'xls', 'xlsx'])) {

                    $headerRows = [];

                    if ($inputFileType === "xlsx") {
                        $headerRows = getFileXlsx($xlsxObj, $inputFileName, $max_row_count);
                    }

                    if ($inputFileType === "xls") {
                        $headerRows = getFileXls($inputFileName, $max_row_count);
                    }

                    if ($inputFileType === "csv") {
                        $headerRows = getFileCsv($inputFileName, $max_row_count);
                    }

                    $template = $client->getSupplPriceTemplate($suppl_id);

                    // lines exist
                    if (!empty($headerRows)) {

                        // template file exist
                        if (!empty($template)) {
                            $templateRow = $template['header_row'];

                            // header row number written in the file
                            if ($templateRow > 0) {
                                $templateRow = $templateRow - 1;
                                $arr = $headerRows[$templateRow];
                                $templateColumns = $template['columns'];

                                // header line exist
                                if (!empty($arr)) {
                                    $count_success = 0;
                                    $count_error = "";

                                    foreach ($templateColumns as $key => $value) {
                                        $k = $key - 1;

                                        $v1 = trim($arr[$k]);
                                        $v2 = trim($value['text']);

                                        // columns matched
                                        if ($v1 === $v2) {
                                            $count_success++;
                                        } else {
                                            $count_error .= 'column ' . $value['type'] . ($value['storage_id'] ?: '') . ': ' . $v1 . '!=' . $v2 . '; ';
                                        }
                                    }

                                    // all columns are matched
                                    if ($count_success === count($templateColumns)) {

                                        $rows = [];

                                        if ($inputFileType === "xlsx") {
                                            $rows = getFileXlsx($xlsxObj, $inputFileName);
                                        }

                                        if ($inputFileType === "xls") {
                                            $rows = getFileXls($inputFileName);
                                        }

                                        if ($inputFileType === "csv") {
                                            $rows = getFileCsv($inputFileName);
                                        }

                                        $dataProcess = $client->finishSupplPriceImport($inputFileName, $suppl_id, $template, $rows);

                                        if ($dataProcess['answer'] === 1) {
                                            $answer = 1;
                                            $err = "";
                                            unlink($inputFileName);
                                        } else {
                                            $err = $dataProcess['error'];
                                        }
                                    } else {
                                        $err = "Pattern mismatch: " . $count_error;
                                    }
                                } else {
                                    $err = "File header row doesnt exist!";
                                }
                            } else {
                                $err = "Template header row doesnt exist!";
                            }
                        } else {
                            $err = "Template file doesnt exist!";
                        }
                    } else {
                        $err = "File is empty!";
                    }
                } else {
                    $err = "File data format is wrong!";
                }
            } else {
                $err = "File $inputFileName doesnt exists!";
            }

            print 'File ' . $inputFileName . ' processed!' . "\n" . ($answer === 0 ? 'Error: ' : 'Success! ') . $err . "\n";

            $time = microtime(true) - $start;

            /*
             * STATUS_PROCESS = 0: Error, added to `IMPORT_SUPPLIER_ERROR
             * STATUS_PROCESS = 1: Success processed
             */
            if ($answer === 0) {
                $db->query("INSERT INTO `IMPORT_SUPPLIER_ERROR` (`SUPPL_ID`, `TEXT`, `STATUS`, `TIMEMAN`) VALUES ($suppl_id, '$err', 1, '$time');");

                if ($counter >= 1) {
                    $db->query("UPDATE `IMPORT_SUPPLIER_FILES` SET `STATUS` = 0, `STATUS_PROCESS` = 0, `TIMEMAN` = '$time' WHERE `ID` = $id LIMIT 1;");
                    unlink($inputFileName);
                } else {
                    $status_counter = $counter + 1;
                    $db->query("UPDATE `IMPORT_SUPPLIER_FILES` SET `STATUS_COUNTER` = $status_counter, `TIMEMAN` = '$time' WHERE `ID` = $id LIMIT 1;");
                }
            }

            if ($answer === 1) {
                $db->query("UPDATE `IMPORT_SUPPLIER_FILES` SET `STATUS` = 0, `STATUS_PROCESS` = 1, `TIMEMAN` = '$time' WHERE `ID` = $id LIMIT 1;");
            }

        }
    }
}
