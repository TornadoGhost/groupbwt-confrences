<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Type as UserType;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'name@example.com'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The email should be not empty',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'The email should be at least {{ limit }} characters',
                        'max' => 100,
                        'maxMessage' => 'The email should be not longer than {{ limit }} characters'
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address'
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => '**********',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'attr' => [
                    'placeholder' => 'Firstname example',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The firstname should be not empty',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'The firstname should be at least {{ limit }} characters',
                        'max' => 100,
                        'maxMessage' => 'The firstname should be not longer than {{ limit }} characters'
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'attr' => [
                    'placeholder' => 'Lastname example'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The lastname should be not empty',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'The lastname should be at least {{ limit }} characters',
                        'max' => 100,
                        'maxMessage' => 'The lastname should be not longer than {{ limit }} characters'
                    ]),
                ],
            ])
            ->add('birthdate', BirthdayType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Start date cannot be blank.',
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value {{ value }} is not a valid date.',
                    ]),
                ]
            ])
            ->add('country', CountryType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a country.',
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone number',
                'attr' => [
                    'class' => 'phone-input'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The phone number should be not empty',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'The phone number should be at least {{ limit }} characters',
                        'max' => 20,
                        'maxMessage' => 'The phone number should be not longer than {{ limit }} characters'
                    ]),
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => UserType::class,
                'choice_label' => 'name',
                'placeholder' => 'Select type',
                'choice_loader' => $options['test_choice_loader'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a user type.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'test_choice_loader' => null,
        ]);
    }
}
