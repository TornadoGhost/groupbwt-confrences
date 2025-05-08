<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReportOverlap extends Constraint
{
    public string $overlapMessage = 'The report time overlaps with the second report. Closest available start time is {{ time }}';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

}
