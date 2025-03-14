<?php

namespace App\Import\Validation\Contracts;

interface ValidationInterface
{
    public function validate(array $csvData);
}
