<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Import\Csv\Validation\ConferencesCsvValidation;
use App\Message\ImportNewConferencesCsv;
use App\Service\ConferenceService;
use Doctrine\ORM\EntityManagerInterface;
use Pusher\Pusher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportNewConferencesCsvHandler implements MessageHandlerInterface
{
    private FormFactoryInterface $form;
    private ConferenceService $conferenceService;
    private EntityManagerInterface $em;
    private Pusher $pusher;
    private bool $importDone = true;
    private ConferencesCsvValidation $csvValidation;

    public function __construct(
        FormFactoryInterface     $form,
        ConferenceService        $conferenceService,
        EntityManagerInterface   $em,
        Pusher                   $pusher,
        ConferencesCsvValidation $csvValidation
    )
    {
        $this->form = $form;
        $this->conferenceService = $conferenceService;
        $this->em = $em;
        $this->pusher = $pusher;
        $this->csvValidation = $csvValidation;
    }

    public function __invoke(ImportNewConferencesCsv $csvData): void
    {
        $validationResult = $this->csvValidation->validate($csvData->getData());

        if (!empty($validationResult)) {
            $this->sendErrorPushMessage($validationResult);

            return;
        }

        $this->em->getConnection()->beginTransaction();

        // TODO: check if this approach is good\right
        foreach ($csvData->getData() as $data) {
            $conference = new Conference();
            $form = $this->form->create(ConferenceType::class, $conference);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->conferenceService->saveFormChanges($conference,
                    [
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                    ]
                );
            } else {
                $this->em->getConnection()->rollBack();
                $this->importDone = false;

                return;
                // TODO: change on notification with errors or add errors in a DB for future representing
                /*throw new Exception(
                    $this->conferenceService->getFormErrors($form),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );*/
            }
        }

        $this->em->commit();

        $this->sendPushMessage($this->importDone);
    }

    private function sendSuccessPushMessage(string $message = "New conferences imported successfully")
    {
        $this->pusher->trigger(
            'notification',
            'success-import',
            $message
        );
    }

    private function sendErrorPushMessage(array $errors)
    {
        $transformArrayToString = implode("; ", $errors);

        $this->pusher->trigger(
            'notification',
            'error-import',
            $transformArrayToString
        );
    }
}
