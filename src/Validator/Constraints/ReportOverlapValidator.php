<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Repository\ReportRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReportOverlapValidator extends ConstraintValidator
{
    private ReportRepository $reportRepository;
    private RequestStack $requestStack;

    public function __construct(ReportRepository $reportRepository, RequestStack $requestStack)
    {
        $this->reportRepository = $reportRepository;
        $this->requestStack = $requestStack;
    }

    public function validate($value, Constraint $constraint): void
    {
        $start = \DateTime::createFromFormat('Y-m-d H:i', $value->getStartedAt());
        $end = \DateTime::createFromFormat('Y-m-d H:i', $value->getEndedAt());
        $conferenceId = $this->requestStack->getCurrentRequest()
            ->attributes
            ->get('conference')
            ->getId();

        if (!$start || !$end || !$conferenceId) {
            return;
        }

        $conflicting = $this->reportRepository->findOverlappingReport($start, $end, $conferenceId);

        if ($conflicting) {
            $this->context->buildViolation($constraint->overlapMessage)
                ->setParameter('{{ time }}', $conflicting->format('Y-m-d H:i:s'))
                ->atPath('startedAt')
                ->addViolation();
        }
    }
}
