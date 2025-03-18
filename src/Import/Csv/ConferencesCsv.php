<?php

declare(strict_types=1);

namespace App\Import\Csv;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Import\Validation\ConferencesCsvValidation;
use App\Import\Validation\Contracts\ConferencesCsvValidationInterface;
use App\Service\ConferenceService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Pusher\Pusher;
use Symfony\Component\Form\FormFactoryInterface;

class ConferencesCsv extends AbstractCsvImport
{
    private FormFactoryInterface $form;
    private ConferenceService $conferenceService;
    private EntityManagerInterface $em;
    private ConferencesCsvValidationInterface $csvValidation;

    public function __construct(
        FormFactoryInterface     $form,
        ConferenceService        $conferenceService,
        EntityManagerInterface   $em,
        ConferencesCsvValidation $csvValidation,
        Pusher                   $pusher
    )
    {
        parent::__construct($pusher);
        $this->form = $form;
        $this->conferenceService = $conferenceService;
        $this->em = $em;
        $this->csvValidation = $csvValidation;
    }

    public function import(array $data): ?string
    {
        $data = $this->formatCsvData($data);;
        $validationResult = $this->csvValidation->validate($data);

        if (!empty($validationResult)) {
            return $validationResult;
        }

        $this->em->getConnection()->beginTransaction();

        // TODO: check if this approach is good\right
        foreach ($data as $item) {
            $conference = new Conference();
            $form = $this->form->create(ConferenceType::class, $conference);
            $form->submit($item);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->conferenceService->saveFormChanges($conference,
                    [
                        'latitude' => $item['latitude'] ?? null,
                        'longitude' => $item['longitude'] ?? null,
                    ]
                );
            } else {
                $this->em->getConnection()->rollBack();

                // TODO: change message by showing which data is wrong
                return 'Import failed. [Wrong data]';
            }
        }

        $this->em->commit();

        return null;
    }
}
