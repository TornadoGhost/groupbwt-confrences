<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Import\Csv\ConferencesCsv;
use App\Message\ImportNewConferencesCsv;
use App\Service\NotificationService;
use Pusher\Pusher;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportNewConferencesCsvHandler implements MessageHandlerInterface
{
    private ConferencesCsv $conferencesCsv;
    private Pusher $pusher;
    private NotificationService $notificationService;

    public function __construct(
        ConferencesCsv      $conferencesCsv,
        Pusher              $pusher,
        NotificationService $notificationService
    )
    {
        $this->conferencesCsv = $conferencesCsv;
        $this->pusher = $pusher;
        $this->notificationService = $notificationService;
    }

    public function __invoke(ImportNewConferencesCsv $importData): void
    {
        $errorMessage = $this->conferencesCsv->import($importData->getData());

        // TODO: maybe move to the service class
        // TODO: remove two different events, add only one for both
        if ($errorMessage !== null) {
            $this->notificationService->pushNotification(
                'error-import',
                'Import failed',
                $errorMessage,
                $importData->getUser()->getId()
            );
        } else {
            $this->notificationService->pushNotification(
                'success-import',
                'Import done',
                'Your data has imported successfully',
                $importData->getUser()->getId()
            );
        }
    }
}
