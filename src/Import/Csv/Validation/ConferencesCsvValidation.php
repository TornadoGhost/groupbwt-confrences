<?php

declare(strict_types=1);

namespace App\Import\Csv\Validation;

use App\Import\Csv\Validation\Contract\CsvValidationInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class ConferencesCsvValidation implements CsvValidationInterface
{
    // TODO: validation not done, need to be finished
    public function validate(array $csvData): ?array
    {
        $itemConstraints = new Assert\Collection([
            'fields' => [
                'title' => [
                    new NotBlank([
                        'message' => 'The title - {{ value }} is missing'
                    ]),
                    new NotNull([
                        'message' => 'The title should be not null'
                    ]),
                    new Length([
                        'min' => '2',
                        'minMessage' => 'The title - {{ value }}, should be at least {{ limit }} characters',
                        'max' => '255',
                        'maxMessage' => 'The title should be not longer than {{ limit }} characters',
                    ])
                ],
                'startedAt' => [
                    new NotBlank([
                        'message' => 'The start date - {{ value }}, cannot be blank',
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value - {{ value }}, is not a valid start date',
                    ]),
                ],
                'endedAt' => [
                    new NotBlank([
                        'message' => 'The end date cannot be blank',
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value {{ value }} is not a valid end date',
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

        $violations = Validation::createValidator()->validate($csvData, $constraints);

        if (count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                preg_match_all('/\[(.*?)\]/', $violation->getPropertyPath(), $matches);
                list($row, $field) = $matches[1];
                $row += 2;
                $errors[] = 'Row №' . $row . ', field:"' . $field . '"' . ": " . $violation->getMessage() . "\n";
            }

            return $errors;
        }

        return null;
    }
}
