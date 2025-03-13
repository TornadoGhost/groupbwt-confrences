<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Import\Csv\ConferencesCsv;
use App\Import\Csv\Validation\ConferencesCsvValidation;
use App\Message\ImportNewConferencesCsv;
use App\Service\ConferenceService;
use Doctrine\ORM\EntityManagerInterface;
use Pusher\Pusher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ImportNewConferencesCsvHandler implements MessageHandlerInterface
{
    private ConferencesCsv $conferencesCsv;


    public function __construct(
        ConferencesCsv $conferencesCsv
    )
    {
        $this->conferencesCsv = $conferencesCsv;
    }

    public function __invoke(ImportNewConferencesCsv $csvData): void
    {
        $this->conferencesCsv->import($csvData->getData());
    }
}
