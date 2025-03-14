<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Import\Csv\ConferencesCsv;
use App\Message\ImportNewConferencesCsv;
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
