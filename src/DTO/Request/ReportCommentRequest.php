<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ReportCommentRequest
{
    /**
     * @Assert\Type("string")
     * @Assert\Length(min="2")
     */
    private string $content;

    public function __construct(
        string $content
    )
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
