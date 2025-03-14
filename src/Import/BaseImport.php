<?php

declare(strict_types=1);

namespace App\Import;

use Pusher\Pusher;

class BaseImport
{
    private Pusher $pusher;

    public function __construct(Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    protected function sendSuccessPushMessage(string $message = "New conferences imported successfully")
    {
        $this->pusher->trigger(
            'notification',
            'success-import',
            $message
        );
    }

    protected function sendErrorPushMessage(string $errorMessage)
    {
        $this->pusher->trigger(
            'notification',
            'error-import',
            $errorMessage
        );
    }
}
