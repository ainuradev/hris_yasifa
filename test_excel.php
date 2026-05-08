<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Test');
    $writer = new Xlsx($spreadsheet);
    $writer->save('hello world.xlsx');
    echo "Excel write success\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
