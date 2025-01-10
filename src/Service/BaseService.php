<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;

class BaseService
{
    public function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->all() as $fieldName => $formField) {
            foreach ($formField->getErrors() as $error) {
                $errors[$fieldName][] = $error->getMessage();
            }
        }

        return $errors;
    }
}
