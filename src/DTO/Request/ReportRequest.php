<?php

namespace App\DTO\Request;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * @AppAssert\ReportTime
 * @AppAssert\ReportOverlap
 */
class ReportRequest
{
    /**
     * @Assert\Type("string")
     * @Assert\Length(min="2", max="255")
     */
    private string $title;

    /**
     * @Assert\Type("string")
     * @Assert\Length(min="2")
     */
    private string $description;

    /**
     * @Assert\DateTime(format = "Y-m-d H:i")
     */
    private string $startedAt;

    /**
     * @Assert\DateTime(format = "Y-m-d H:i")
     */
    private string $endedAt;

    /**
     * @Assert\File(
     *     maxSize="10M",
     *     maxSizeMessage="The file should be not bigger than 10 mb",
     *     mimeTypes={
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation"
     *     },
     *     mimeTypesMessage="Please upload a valid PPT or PPTX file."
     * )
     */
    private ?UploadedFile $document;

    public function __construct(
        string $title, $description, $startedAt, $endedAt,
        ?UploadedFile $document
    )
    {
        $this->title = $title ? trim($title) : null;
        $this->description = $description ? trim($description) : null;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
        $this->document = $document;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStartedAt(): string
    {
        return $this->startedAt;
    }

    public function getEndedAt(): string
    {
        return $this->endedAt;
    }

    public function getDocument(): ?UploadedFile
    {
        return $this->document;
    }
}
