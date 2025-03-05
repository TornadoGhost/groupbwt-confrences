<?php

namespace App\MessageHandler;

use App\Message\ConferenceEmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Mime\Email;

class ConferenceEmailNotificationHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(ConferenceEmailNotification $message)
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to($message->getReceiverEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($message->getSubject())
            ->text($message->getContent())
            ->html('<p>' . $message->getContent() . '</p>')
        ;

        $this->mailer->send($email);
    }
}
