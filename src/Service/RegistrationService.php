<?php

namespace App\Service;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RegistrationService
{
    private FormFactoryInterface $formFactory;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        FormFactoryInterface $formFactory,
        UserRepository  $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
    )
    {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function userFormPrep(Request $request, UserInterface $user): FormInterface
    {
        $form = $this->formFactory->create(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        return $form;
    }

    public function createNewUser(): User
    {
        return $this->userRepository->newUser();
    }

    public function saveNewUser(UserInterface $user, FormInterface $form)
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
