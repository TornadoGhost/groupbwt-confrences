<?php

namespace App\Import\Csv\Validation\Contract;

interface CsvValidationInterface
{
    public function validate(array $csvData);
}
