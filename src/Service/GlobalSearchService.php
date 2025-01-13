<?php

namespace App\Service;

use App\Repository\ConferenceRepository;
use App\Repository\ReportRepository;
use Symfony\Component\Serializer\SerializerInterface;

class GlobalSearchService
{
    protected const FILTER_TYPE_CONFERENCE = 'conference';
    protected const FILTER_TYPE_REPORT = 'report';

    protected ConferenceRepository $conferenceRepository;
    protected ReportRepository $reportRepository;
    protected SerializerInterface $serializer;

    public function __construct(
        ConferenceRepository $conferenceRepository,
        ReportRepository $reportRepository,
        SerializerInterface $serializer
    )
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->reportRepository = $reportRepository;
        $this->serializer = $serializer;
    }

    public function search($request): ?array
    {
        $conferences = [];
        $reports = [];

        if ($request->query->has('title')) {
            $title = $request->query->get('title');

            if ($request->query->has('type')) {
                $types = explode(',', $request->query->get('type'));

                if (in_array(self::FILTER_TYPE_CONFERENCE, $types)) {
                    $conferences = $this->fullTextSearchConferenceByTitle($title);
                }

                if (in_array(self::FILTER_TYPE_REPORT, $types)) {
                    $reports = $this->fullTextSearchReportByTitle($title);
                }
            }
        }

        $data = [];

        if (!empty($conferences)) {
            $data['conferences'] = $conferences;
        }

        if (!empty($reports)) {
            $data['reports'] = $reports;
        }

        return $data ?? null;
    }

    public function fullTextSearchConferenceByTitle(string $title): array
    {
        return $this->conferenceRepository->fullTextSearchByTitle($title);
    }
    public function fullTextSearchReportByTitle(string $title): ?array
    {
        return $this->reportRepository->fullTextSearchByTitle($title);
    }
}
