<?php

namespace App\Import\Csv;

use App\Import\Csv\Validation\Contract\CsvValidationInterface;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class ConferencesCsv
{
    public function getCsvData(string $filepath): array
    {
        $csvReader = new Csv();
        $spreadsheet = $csvReader->load($filepath);
        $data = [];

        foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Включаємо пусті клітинки
            $rowData = [];

            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();

                if (is_string($value)) {
                    $value = trim($value);
                }

                $rowData[] = $value;
            }

            $data[] = $rowData;
        }

        return $this->formatCsvData($data);
    }

    private function formatCsvData(array $csvData): array
    {
        $titles = $csvData[0];
        $newData = [];
        $iterator = 0;


        foreach ($csvData as $row) {
            $iterator += 1;

            if ($iterator === 1) {
                continue;
            }

            $rowData = [];

            foreach ($row as $key => $item) {
                $rowData[$titles[$key]] = $item;
            }

            $newData[] = $rowData;
        }

        return $newData;
    }
}
