<?php

namespace App\Serializer;

use DateTimeInterface;

class StartedAtConferenceCallback
{
    /**
     * @param null|string|DateTimeInterface $innerObject
     * @return DateTimeInterface|string|null
     */
    public function __invoke($innerObject)
    {
        if ($innerObject === null) {
            return null;
        }

        if (!($innerObject instanceof DateTimeInterface)) {
            return $innerObject;
        }

        return $innerObject->format('Y-m-d H:i');
    }
}
