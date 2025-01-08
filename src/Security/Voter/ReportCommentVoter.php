<?php

namespace App\Security\Voter;

use App\Entity\ReportComment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportCommentVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof ReportComment;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var ReportComment $comment */
        $comment = $subject;

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        if ($attribute === self::EDIT) {
            $commentTime = $comment->getCreatedAt()->modify("+10 minutes");
            $now =  new \DateTime();

            if ($comment->getUser() === $user && $commentTime >= $now) {
                return true;
            }

            return false;
        }

        return $comment->getUser() === $user;
    }
}
