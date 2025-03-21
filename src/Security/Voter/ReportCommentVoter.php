<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\ReportComment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportCommentVoter extends Voter
{
    private const EDIT = 'EDIT';
    private const DELETE = 'DELETE';

    private $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager)
    {
        $this->accessDecisionManager = $accessDecisionManager;
    }

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

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($attribute === self::EDIT) {
            $commentTime = $comment->getCreatedAt()->modify("+10 minutes");
            $now = new \DateTime();

            if ($comment->getUser() === $user && $commentTime >= $now) {
                return true;
            }

            return false;
        }

        return $comment->getUser() === $user;
    }
}
