<?php

namespace App\Message;

class ConferenceEmailNotification
{
    private string $content;
    private string $receiverEmail;
    private string $subject;

    public function __construct(
        string $receiverEmail,
        string $subject,
        string $content
    )
    {
        $this->receiverEmail = $receiverEmail;
        $this->subject = $subject;
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getReceiverEmail(): string
    {
        return $this->receiverEmail;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }
}
