<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class IndexReportRequest
{
    /**
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private ?string $startTime;

    /**
     * @Assert\DateTime(format="Y-m-d H:i")
     */
    private ?string $endTime;

    /**
     * @Assert\Type("numeric")
     */
    private ?string $duration;

    public function __construct(
        ?string $startTime,
        ?string $endTime,
        ?string $duration
    )
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->duration = $duration ? trim($duration) : null;
    }


    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }
}
