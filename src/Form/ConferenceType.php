<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Conference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class ConferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter conference title',
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'The title should be not null'
                    ]),
                    new Length([
                        'min' => '2',
                        'minMessage' => 'The title should be at least {{ limit }} characters',
                        'max' => '255',
                        'maxMessage' => 'The title should be not longer than {{ limit }} characters',
                    ])
                ]
            ])
            ->add('startedAt', DateTimeType::class, [
                'label' => 'Date of start',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'The start date cannot be blank',
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value {{ value }} is not a valid start date',
                    ]),
                ]
            ])
            ->add('endedAt', DateTimeType::class, [
                'label' => 'Date of end',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'The end date cannot be blank',
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value {{ value }} is not a valid end date',
                    ]),
                ]
            ])
            ->add('latitude', NumberType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter latitude',
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'The Latitude should not be null'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'The Latitude should be at least {{ limit }} characters',
                        'max' => 10,
                        'maxMessage' => 'The Latitude should be less than {{ limit }} characters',
                    ]),
                ]
            ])
            ->add('longitude', NumberType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Enter longitude',
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'The Longitude should not be null'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'The Longitude should be at least {{ limit }} characters',
                        'max' => 10,
                        'maxMessage' => 'The Longitude should be less than {{ limit }} characters',
                    ]),
                ]
            ])
            ->add('country', CountryType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a country.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conference::class,
            'csrf_protection' => false
        ]);
    }
}
