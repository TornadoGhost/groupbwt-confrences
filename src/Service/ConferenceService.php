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
    private ConferenceRepository $conferenceRepository;

    public function __construct(
        ConferenceRepository $conferenceRepository
    )
    {
        $this->conferenceRepository = $conferenceRepository;
    }

    /**
     * @param User|UserInterface $user
     */
    public function getAllConferenceWithSpecificUserPaginate(
        $user,
        int $maxPerPage = 10,
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

        $conferences->setMaxPerPage($maxPerPage);
        $conferences->setCurrentPage($currentPage);

        return $conferences;
    }


    /**
     * @param User|UserInterface $user
     */
    public function addUserToConference(Conference $conference, $user): void
    {
        $this->conferenceRepository->addUserToConference($conference, $user);
    }

    /**
     * @param User|UserInterface $user
     */
    public function removeUserFromConference(Conference $conference, $user): void
    {
        $this->conferenceRepository->removeUserFromConference($conference, $user);
    }
}
