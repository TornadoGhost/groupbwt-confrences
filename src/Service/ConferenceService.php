<?php

namespace App\Service;

use App\Entity\Conference;
use App\Entity\User;
use App\Repository\ConferenceRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Security\Core\User\UserInterface;

class ConferenceService
{
    const COUNT_PER_PAGE = 15;
    private ConferenceRepository $conferenceRepository;

    public function __construct(
        ConferenceRepository $conferenceRepository
    )
    {
        $this->conferenceRepository = $conferenceRepository;
    }

    public function getAllConferenceWithSpecificUserPaginate(
        ?int $userId,
        int $countPerPage,
        int $currentPage = 1
    ): Pagerfanta
    {
        if ($user) {
            $userId = $user->getId();
        } else {
            $userId = null;
        }

        $queryResult = $this->conferenceRepository->getAllConferencesWithSpecificUser($userId);

        $adapter = new QueryAdapter($queryResult);
        $conferences = new Pagerfanta($adapter);

        $conferences->setMaxPerPage($countPerPage);
        $conferences->setCurrentPage($currentPage);

        return $conferences;
    }

    public function addUserToConference(Conference $conference, UserInterface $user): void
    {
        $this->conferenceRepository->addUserToConference($conference, $user);
    }

    public function removeUserFromConference(Conference $conference, UserInterface $user): void
    {
        $this->conferenceRepository->removeUserFromConference($conference, $user);
    }
}
