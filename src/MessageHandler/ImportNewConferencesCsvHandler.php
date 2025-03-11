<?php

namespace App\MessageHandler;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Message\ImportNewConferencesCsv;
use App\Service\ConferenceService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportNewConferencesCsvHandler implements MessageHandlerInterface
{
    private FormFactoryInterface $form;
    private ConferenceService $conferenceService;

    public function __construct(
        FormFactoryInterface $form,
        ConferenceService $conferenceService
    )
    {
        $this->form = $form;
        $this->conferenceService = $conferenceService;
    }

    public function __invoke(ImportNewConferencesCsv $csvData): void
    {
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
            }

            /*return $this->conferenceService->getFormErrors($form);

            if (!empty($errors)) {
                return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
            }*/
        }
    }
}
