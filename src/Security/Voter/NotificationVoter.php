<?php

namespace App\Security\Voter;

use App\Entity\Notification;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationVoter extends Voter
{
    private const EDIT = 'EDIT';
    private const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject)
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Notification;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Notification $notification */
        $notification = $subject;

        if ($attribute === self::EDIT) {
            return $notification->getUsers()->contains($user);
        }

        return false;
    }
}
