<?php

namespace App\Service;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

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

    public function prepareForm(Request $request, Conference $conference, FormInterface $form): FormInterface
    {
        $latitude = $conference->getAddress()[0] ?? null;
        $longitude = $conference->getAddress()[1] ?? null;

        $form = $this->setCustomDataForForm($form, ['latitude' => $latitude, 'longitude' => $longitude]);
        $form->handleRequest($request);

        return $form;
    }

    public function saveFormChanges(FormInterface $form, Conference $conference): void
    {
        $latitude = $form->get('latitude')->getData();
        $longitude = $form->get('longitude')->getData();

        $address = [$latitude, $longitude];
        $this->conferenceRepository->saveEditFormChanges($conference, $address);
    }

    protected function setCustomDataForForm(FormInterface $form, array $fieldsData = []): FormInterface
    {
        if (!$fieldsData) {
            return $form;
        }

        foreach ($fieldsData as $field => $value) {
            $form->get($field)->setData($value);
        }

        return $form;
    }
}
