<?php

namespace App\Service;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class ConferenceService extends BaseService
{
    const COUNT_PER_PAGE = 15;
    private ConferenceRepository $conferenceRepository;
    protected UrlGeneratorInterface $urlGenerator;

    public function __construct(
        ConferenceRepository $conferenceRepository,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function getAllConferencesWithFiltersPaginate(
        int  $countPerPage,
        int  $currentPage = 1,
        ?int $userId = null,
        ?array $filters = []
    ): Pagerfanta
    {
        $queryResult = $this->conferenceRepository->getAllConferencesWithFiltersPaginate($userId, $filters);

        $adapter = new QueryAdapter($queryResult);
        $conferences = new Pagerfanta($adapter);

        $conferences->setMaxPerPage($countPerPage);
        $conferences->setCurrentPage($currentPage);

        return $conferences;
    }

    public function getAllConferencesWithFiltersPaginateApi(
        int  $countPerPage,
        int  $currentPage = 1,
        ?int $userId = null,
        ?array $filters = []
    ): array
    {
        $pagerfanta = $this->getAllConferencesWithFiltersPaginate($countPerPage, $currentPage, $userId, $filters);

        $conferences = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $conferences[] = $result;
        }

        // TODO: Reread how to make response with pagination https://restfulapi.net/api-pagination-sorting-filtering/
        return [
            'data' => $conferences,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($conferences),
            'current_page' => $pagerfanta->getCurrentPage(),
            'first_page_url' => $this->urlGenerator->generate('api_conferences_index', ['page' => 1]),
            'last_page' => $pagerfanta->getNbPages(),
            'last_page_url' => $this->urlGenerator->generate('api_conferences_index', [
                'page' => $pagerfanta->getNbPages()]
            ),
            'next_page_url' =>
                $pagerfanta->hasNextPage()
                    ? $this->urlGenerator->generate('api_conferences_index', [
                        'page' => $pagerfanta->getNextPage()
                ])
                    : null,
            'path' => $this->urlGenerator->generate('api_conferences_index'),
            'per_page' => $countPerPage,
            'prev_page_url' =>
                $pagerfanta->hasPreviousPage()
                    ? $this->urlGenerator->generate('api_conferences_index', [
                        'page' => $pagerfanta->getPreviousPage()
                ])
                    : null,
            'to' => $pagerfanta->getCurrentPageResults()->count(),
        ];
    }

    public function addUserToConference(Conference $conference, UserInterface $user): void
    {
        $this->conferenceRepository->addUserToConference($conference, $user);
    }

    public function removeUserFromConference(Conference $conference, UserInterface $user): void
    {
        $this->conferenceRepository->removeUserFromConference($conference, $user);
    }

    public function formPreparation(Request $request, Conference $conference, FormInterface $form): FormInterface
    {
        $latitude = $conference->getAddress()[0] ?? null;
        $longitude = $conference->getAddress()[1] ?? null;

        $form = $this->setCustomDataForForm($form, ['latitude' => $latitude, 'longitude' => $longitude]);
        $form->handleRequest($request);

        return $form;
    }

    public function saveFormChanges(FormInterface $form, Conference $conference): Conference
    {
        $latitude = $form->get('latitude')->getData();
        $longitude = $form->get('longitude')->getData();

        $address = [$latitude, $longitude];
        return $this->conferenceRepository->saveEditFormChanges($conference, $address);
    }

    public function setCustomDataForForm(FormInterface $form, array $fieldsData = []): FormInterface
    {
        if (!$fieldsData) {
            return $form;
        }

        foreach ($fieldsData as $field => $value) {
            $form->get($field)->setData($value);
        }

        return $form;
    }

    public function findParticipantByUserId(int $userId, int $conferenceId): ?array
    {
        return $this->conferenceRepository->findParticipantByUserId($userId, $conferenceId);
    }

    public function delete(Conference $conference): void
    {
        $this->conferenceRepository->delete($conference);
    }
}
