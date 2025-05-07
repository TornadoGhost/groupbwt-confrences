<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

class IndexConferenceRequest
{
    /**
     * @Assert\Type("numeric")
     * @Assert\Positive
     */
    private ?string $page;

    /**
     * @Assert\Type("numeric")
     * @Assert\Positive
     */
    private ?string $reportNumber;

    /**
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private ?string $startDate;

    /**
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private ?string $endDate;

    /**
     * @AppAssert\BooleanType
     */
    private ?string $isAvailable;

    public function __construct(
        ?string $page,
        ?string $reportNumber,
        ?string $startDate,
        ?string $endDate,
        ?string $isAvailable
    )
    {
        $this->page = $page;
        $this->reportNumber = $reportNumber;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isAvailable = $isAvailable;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function getIsAvailable(): ?string
    {
        return $this->isAvailable;
    }

    public function getReportNumber(): ?string
    {
        return $this->reportNumber;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }
}
