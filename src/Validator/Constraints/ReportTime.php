<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReportTime extends Constraint
{
    public string $startAfterEndMessage = 'The start time must be before the end time';
    public string $tooShortMessage = 'The report can not be less than 15 minutes';
    public string $tooLongMessage = 'The report can not be longer than 60 minutes';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
