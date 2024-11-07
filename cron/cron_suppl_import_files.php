<?php

error_reporting(E_ERROR);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
define('RDD', dirname(__FILE__));
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set("Europe/Kiev");
ini_set('memory_limit', '4096M');

require_once (RDD . "/../vendor/autoload.php");

use Shuchkin\SimpleXLSX;

$db = DbSingleton::getDbm();
$client = new ClientClass();

$r = $db->query("SELECT `ID`, `SUPPL_ID`, `FILENAME`, `STATUS_COUNTER` FROM `IMPORT_SUPPLIER_FILES` WHERE `STATUS` = 1;");
$n = $db->num_rows($r);

if ($n > 0) {

    for ($i = 1; $i <= $n; $i++) {
        $id         = (int)$db->result($r, $i - 1, "ID");
        $suppl_id   = (int)$db->result($r, $i - 1, "SUPPL_ID");
        $fileName   = $db->result($r, $i - 1, "FILENAME");

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

                try {
                    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($inputFileName);
                } catch (Exception $e) {
                    die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
                }

                if (!in_array(strtolower($inputFileType), ['csv', 'xls', 'xlsx'])) {
                    $n = strrpos($inputFileName, ".");
                    $inputFileType = ($n === false) ? "" : substr($inputFileName, $n + 1);
                }

                $inputFileType = strtolower($inputFileType);

                if (in_array($inputFileType, ['csv', 'xls', 'xlsx'])) {

                    $rows = [];

                    if ($inputFileType === "xlsx") {
                        $xlsx = SimpleXLSX::parse($inputFileName);
                        foreach ($xlsx->rows() as $row) {
                            $rows[] = $row;
                        }
                    }

                    if ($inputFileType === "xls") {
                        try {
                            $sheet = $objPHPExcel->getSheet(0);
                            $highestRow = $sheet->getHighestRow();
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
                    }

                    if ($inputFileType === "csv") {
                        $csvData = file_get_contents($inputFileName);
                        $lines = explode(PHP_EOL, $csvData);
                        foreach ($lines as $line) {
                            //$rows[] = str_getcsv($line, ";", '"');

                            $rows[] = str_getcsv($line, ";");
                        }
                    }

                    $template = $client->getSupplPriceTemplate($suppl_id);

                    // template: json файл з шаблоном контрагента
                    // row: массив з файлу xls/csv

                    if (!empty($rows)) {

                        if (!empty($template)) {
                            $template_header_row = $template['header_row'];

                            if ($template_header_row > 0) {
                                --$template_header_row;
                                $rows_header = $rows[$template_header_row];
                                $template_columns = $template['columns'];

                                if (!empty($rows_header)) {
                                    $count_success = 0;
                                    $count_error = "";
                                    $arr = [];

                                    $arr['start_row']   = $template['start_row'];
                                    $arr['currency']    = $template['currency'];
                                    $arr['header_row']  = $template['header_row'];
                                    $arr['columns']     = [];

                                    foreach ($template_columns as $key => $value) {
                                        $text = $value['text'];
                                        $text = trim($text);
                                        $text = str_replace(array("\n", " "), "", $text);
                                        $temp_count_success = $count_success;

                                        foreach ($rows_header as $k => $v) {
                                            $s1 = $v;
                                            $s1 = trim($s1);
                                            $s1 = str_replace(array("\n", " "), "", $s1);
                                            
                                            if ($s1 === $text) {
                                                $arr['columns'][$key] = $value;
                                                $count_success++;
                                                break;
                                            }
                                        }

                                        if ($count_success > $temp_count_success) {
                                            $temp_count_success = 0;
                                        } else {
                                            $count_error .= 'column ' . $text . ' not found(updated); ';
                                        }
                                    }

                                    if ($count_success === count($template_columns)) {

                                        $dataProcess = $client->finishSupplPriceImport($inputFileName, $suppl_id, $arr, $rows);

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
                                    $err = "Template without header!";
                                }
                            } else {
                                $err = "Template without `header_row`!";
                            }
                        } else {
                            $err = "Empty template!";
                        }
                    } else {
                        $err = "Empty file!";
                    }

                } else {
                    $err = "Wrong data format!";
                }

            } else {
                $err = "The file $inputFileName does not exists";
            }

            print 'File ' . $inputFileName . ' processed!' . "\n" . ($answer === 0 ? 'Error: ' : 'Success! ') . $err . "\n";

            $time = microtime(true) - $start;

            /*
             * STATUS_PROCESS = 0: Error, added to `IMPORT_SUPPLIER_ERROR
             * STATUS_PROCESS = 1: Success processed
             */
            if ($answer === 0) {
                $db->query("INSERT INTO `IMPORT_SUPPLIER_ERROR` (`SUPPL_ID`, `TEXT`, `STATUS`, `TIMEMAN`) VALUES ($suppl_id, '$err', 1, '$time');");

                $db->query("UPDATE `IMPORT_SUPPLIER_FILES` SET `STATUS` = 0, `STATUS_PROCESS` = 0, `TIMEMAN` = '$time' WHERE `ID` = $id LIMIT 1;");
                unlink($inputFileName);
            }

            if ($answer === 1) {
                $db->query("UPDATE `IMPORT_SUPPLIER_FILES` SET `STATUS` = 0, `STATUS_PROCESS` = 1, `TIMEMAN` = '$time' WHERE `ID` = $id LIMIT 1;");
                unlink($inputFileName);
            }

            unlink($inputFileName);

        }

    }
}
