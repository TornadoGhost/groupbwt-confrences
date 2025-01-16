<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RegistrationService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        UserRepository              $userRepository,
        UserPasswordHasherInterface $userPasswordHasher
    )
    {
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function saveNewUser(UserInterface $user, FormInterface $form): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );
        $getUserTypeName = $user->getType()->getName();

        $user->setRoles(['ROLE_' . strtoupper($getUserTypeName)]);

        $this->userRepository->saveUser($user);
    }

}
