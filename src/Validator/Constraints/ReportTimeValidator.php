<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\DTO\Request\ReportRequest;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReportTimeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof ReportRequest) {
            return;
        }

        $start = \DateTime::createFromFormat('Y-m-d H:i', $value->getStartedAt());
        $end = \DateTime::createFromFormat('Y-m-d H:i', $value->getEndedAt());

        if (!$start || !$end) {
            return;
        }

        if ($start >= $end) {
            $this->context->buildViolation($constraint->startAfterEndMessage)
                ->atPath('startedAt')
                ->addViolation();
        }

        $duration = $end->getTimestamp() - $start->getTimestamp();

        if ($duration < 900) {
            $this->context->buildViolation($constraint->tooShortMessage)
                ->atPath('startedAt')
                ->addViolation();
        }

        if ($duration > 3600) {
            $this->context->buildViolation($constraint->tooLongMessage)
                ->atPath('endedAt')
                ->addViolation();
        }
    }
}
