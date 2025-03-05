<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use App\Message\ConferenceEmailNotification;
use Symfony\Component\Messenger\MessageBusInterface;

class MailerService
{
    private MessageBusInterface $bus;

    public function __construct(
        MessageBusInterface $bus
    )
    {
        $this->bus = $bus;
    }

    public function sendEmail(): void
    {
        $this->bus->dispatch(new ConferenceEmailNotification(
            'new_test@example.com',
            'Time for Symfony Mailer Test!',
            'Sending emails is fun again!'
        ));
    }
}
