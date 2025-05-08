<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class IndexReportCommentRequest
{
    /**
     * @Assert\Type("numeric")
     * @Assert\Positive
     */
    private ?string $page;

    public function __construct(
        ?string $page
    )
    {
        $this->page = $page;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }
}
