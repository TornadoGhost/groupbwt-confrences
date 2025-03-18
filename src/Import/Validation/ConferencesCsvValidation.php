<?php

declare(strict_types=1);

namespace App\Import\Validation;

use App\Import\Validation\Contracts\ConferencesCsvValidationInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

class ConferencesCsvValidation implements ConferencesCsvValidationInterface
{
    const REQUITED_TITLES = [
        'title',
        'startedAt',
        'endedAt',
        'latitude',
        'longitude',
        'country',
    ];

    // TODO: validation not done, need to be finished
    public function validate(array $csvData): ?string
    {
        $titleErrorMessage = $this->titlesValidation($csvData);
        if ($titleErrorMessage !== null) {
            return $titleErrorMessage;
        }

        $recordsViolations = $this->recordsValidation($csvData);
        if (count($recordsViolations) > 0) {
            $errors = [];

            foreach ($recordsViolations as $violation) {
                preg_match_all('/\[(.*?)]/', $violation->getPropertyPath(), $matches);
                list($row, $field) = $matches[1];
                $row += 2;
                $errors[] = 'Row №' . $row . ', field "' . $field . '"' . ": " . $violation->getMessage();
            }

            return 'Records errors: ' . implode("; ", $errors);
        }

        return null;
    }

    private function titlesValidation(array $csvData): ?string
    {
        $titles = array_keys($csvData[0]);
        $notInListTitles = [];

        foreach ($titles as $title) {
            if (!in_array($title, self::REQUITED_TITLES)) {
                $notInListTitles[] = $title;
            }
        }

        if (!empty($notInListTitles)) {
            $notInListTitles = implode(', ', $notInListTitles);
            $titles = implode(', ', self::REQUITED_TITLES);

            return 'Founded wrong titles: ' . $notInListTitles . '. ' . 'Required fields: ' . $titles;
        }

        return null;
    }

    private function recordsValidation(array $csvData): ConstraintViolationListInterface
    {
        $itemConstraints = new Assert\Collection([
            'fields' => [
                'title' => [
                    new NotBlank([
                        'message' => 'The title is missing'
                    ]),
                    new NotNull([
                        'message' => 'The title should be not null'
                    ]),
                    new Length([
                        'min' => '2',
                        'minMessage' => 'The title {{ value }}, should be at least {{ limit }} characters',
                        'max' => '255',
                        'maxMessage' => 'The title should be not longer than {{ limit }} characters',
                    ])
                ],
                'startedAt' => [
                    new NotBlank([
                        'message' => 'The start date {{ value }} cannot be blank',
                    ]),
                    new Assert\DateTime([
                        'format' => 'Y-m-d H:i',
                        'message' => 'The value {{ value }}, is not a valid start date, right format is "Y-m-d H:i"',
                    ]),
                ],
                'endedAt' => [
                    new NotBlank([
                        'message' => 'The end date cannot be blank',
                    ]),
                    new Assert\DateTime([
                        'format' => 'Y-m-d H:i',
                        'message' => 'The value {{ value }} is not a valid end date, right format is "Y-m-d H:i"',
                    ]),
                ],
                'latitude' => [
                    new NotBlank(),
                    new NotNull([
                        'message' => 'The Latitude should not be null'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'The Latitude should be at least {{ limit }} characters',
                        'max' => 10,
                        'maxMessage' => 'The Latitude should be less than {{ limit }} characters',
                    ]),
                ],
                'longitude' => [
                    new NotBlank(),
                    new NotNull([
                        'message' => 'The Longitude should not be null'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'The Longitude should be at least {{ limit }} characters',
                        'max' => 10,
                        'maxMessage' => 'The Longitude should be less than {{ limit }} characters',
                    ]),
                ],
                'country' => [
                    new NotBlank([
                        'message' => 'Please select a country.',
                    ])
                ],
            ],
            'allowExtraFields' => false,
            'allowMissingFields' => false
        ]);

        $constraints = new Assert\All([
            'constraints' => [$itemConstraints]
        ]);

        return Validation::createValidator()->validate($csvData, $constraints);
    }
}
