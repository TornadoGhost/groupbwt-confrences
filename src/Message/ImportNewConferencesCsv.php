<?php

namespace App\Message;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class ImportNewConferencesCsv
{
    private array $data;
    private UserInterface $user;

    public function __construct(
        array $data,
        UserInterface $user
    )
    {
        $this->data = $data;
        $this->user = $user;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
