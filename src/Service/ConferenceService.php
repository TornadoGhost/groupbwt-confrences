<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Repository\ConferenceRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ConferenceService extends BaseService
{
    public const COUNT_PER_PAGE = 15;
    private ConferenceRepository $conferenceRepository;
    protected UrlGeneratorInterface $urlGenerator;
    private Export $export;


    public function __construct(
        ConferenceRepository  $conferenceRepository,
        UrlGeneratorInterface $urlGenerator,
        Export $export
    )
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->urlGenerator = $urlGenerator;
        $this->export = $export;
    }

    public function getAllConferencesWithFiltersPaginate(
        int    $countPerPage,
        int    $currentPage = 1,
        ?int   $userId = null,
        ?array $filters = []
    ): Pagerfanta
    {
        $queryResult = $this->conferenceRepository->getAllConferencesWithFiltersPaginate($userId, $filters);

        $adapter = new QueryAdapter($queryResult);

        // TODO: check
//        $adap = new ArrayAdapter($queryResult->getQuery()->getResult());

        $conferences = new Pagerfanta($adapter);

        $conferences->setMaxPerPage($countPerPage);
        $conferences->setCurrentPage($currentPage);

        return $conferences;
    }

    public function getAllConferencesWithFiltersPaginateApi(
        int    $countPerPage,
        int    $currentPage = 1,
        ?int   $userId = null,
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


    public function getAddressFromConference(Conference $conference): array
    {
        $latitude = $conference->getAddress()[0] ?? null;
        $longitude = $conference->getAddress()[1] ?? null;

        if ($latitude && $longitude) {
            return ['latitude' => $latitude, 'longitude' => $longitude];
        }

        return [];
    }

    /*public function exportCsv($testCsvHeaders, $testCsvData, $conference)
    {
        $this->export->exportCsv($testCsvHeaders, $testCsvData, 'conference_' . $conference->getStartedAt()->format('d-m-Y'));
    }*/

    public function saveFormChanges(Conference $conference, array $coordinates): Conference
    {
        if (empty($coordinates)) {
            throw new \UnexpectedValueException('Expected a non-empty array');
        }

        if (!array_key_exists('latitude', $coordinates) || !array_key_exists('longitude', $coordinates)) {
            throw new \InvalidArgumentException('The array does not have "latitude" or "longitude"');
        }

        $address = [$coordinates['latitude'], $coordinates['longitude']];

        return $this->conferenceRepository->saveEditFormChanges($conference, $address);
    }

    public function formatForExcel(Conference $conference): array
    {
        $array = [];
        $array['Reports'][] = ['Report Title', 'Report Speaker', 'Report Time'];
        $array['Conference'] = [
            ['Conference ID', 'Conference Title', 'Conference Coordinates'],
            [$conference->getId(), $conference->getTitle(), implode(', ',$conference->getAddress())]
        ];

        foreach ($conference->getReports() as $report) {
            $user = $report->getUser();
            $array['Reports'][] = [
                $report->getTitle(),
                $user->getFirstname() . ' ' . $user->getLastname(),
                $report->getStartedAt()->format('H:i') . '-' . $report->getEndedAt()->format('H:i')
            ];
        }

        return $array;
    }

    public function formatForPdf(Conference $conference): array
    {
        $array = [];

        $array['title'] = $conference->getTitle();
        $array['time'] =
            $conference->getStartedAt()->format('d M Y, H:i') . '-' .
            $conference->getEndedAt()->format('H:i');

        foreach ($conference->getReports() as $report) {
            $user = $report->getUser();
            $array['reports'][] = [
                'title' => $report->getTitle(),
                'time' => $report->getStartedAt()->format('H:i') . '-' . $report->getEndedAt()->format('H:i'),
                'speaker' => $user->getFirstname() . ' ' . $user->getLastname()
            ];
        }

        return $array;
    }

    public function addUserToConference(Conference $conference, UserInterface $user): void
    {
        $this->conferenceRepository->addUserToConference($conference, $user);
    }

    public function removeUserFromConference(Conference $conference, UserInterface $user): void
    {
        $this->conferenceRepository->removeUserFromConference($conference, $user);
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
