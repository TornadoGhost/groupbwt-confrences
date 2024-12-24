<?php

namespace App\Form;

use App\Entity\Report;
use App\Repository\ReportRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ReportType extends AbstractType
{
    private $reportRepository;

    public function __construct(
        ReportRepository $reportRepository
    )
    {
        $this->reportRepository = $reportRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter title',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The title should be not empty'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'The title should be at least {{ limit }} characters',
                        'max' => 255,
                        'maxMessage' => 'The title should be not longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('startedAt', DateTimeType::class, [
                'data' => $options['conference_start'],
                'widget' => 'single_text',
                'label' => 'Start time',
                'attr' => [
                    'min' => $options['conference_start']->format('Y-m-d\TH:i'),
                    'max' => $options['conference_end']->format('Y-m-d\TH:i')
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The start time should be not empty'
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value {{ value }} is not a valid date',
                    ]),
                    new LessThanOrEqual([
                        'value' => $options['conference_end'],
                        'message' => "The time must not be earlier than {$options['conference_start']->format('d-m-Y\TH:i')}",
                    ]),
                    new GreaterThanOrEqual([
                        'value' => $options['conference_start'],
                        'message' => "The time must not be latter than {$options['conference_end']->format('d-m-Y\TH:i')}",
                    ]),
                ]
            ])
            ->add('endedAt', DateTimeType::class, [
                'data' => $options['conference_end'],
                'widget' => 'single_text',
                'label' => 'End time',
                'attr' => [
                    'min' => $options['conference_start']->format('Y-m-d\TH:i'),
                    'max' => $options['conference_end']->format('Y-m-d\TH:i')
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The end time should be not empty'
                    ]),
                    new Type([
                        'type' => \DateTimeInterface::class,
                        'message' => 'The value {{ value }} is not a valid date',
                    ]),
                    new LessThanOrEqual([
                        'value' => $options['conference_end'],
                        'message' => "The time must not be earlier than {$options['conference_start']->format('d-m-Y\TH:i')}",
                    ]),
                    new GreaterThanOrEqual([
                        'value' => $options['conference_start'],
                        'message' => "The time must not be latter than {$options['conference_end']->format('d-m-Y\TH:i')}",
                    ]),
                ]
            ])
            ->add('description', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter description',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'The description should be not empty'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'The description should be at least {{ limit }} characters'
                    ])
                ]
            ])
            ->add('document', FileType::class, [
                'label' => 'Presentation',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'maxSizeMessage' => 'File should be not bigger than 10 mb',
                        'mimeTypes' => [
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PPT or PPTX file.',
                    ])
                ]
            ])
            ->add('conference', HiddenType::class, [
                'data' => $options['conference_id'],
                'mapped' => false
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            $startTime = $data->getStartedAt();
            $endTime = $data->getEndedAt();
            $conferenceId = $options['conference_id'];

            if ($startTime >= $endTime) {
                $form->get('startedAt')->addError(new FormError(
                    'The start time must be before the end time'
                ));
            }

            if ($endTime->getTimestamp() - $startTime->getTimestamp() > 3600) {
                $form->get('endedAt')->addError(new FormError(
                    'A report can not be longer than 60 minutes'
                ));
            }

            $overlappingReport = $this->reportRepository->findOverlappingReport($startTime, $endTime, $conferenceId);
            if (!empty($overlappingReport)) {
                $form
                    ->get('startedAt')
                    ->addError(new FormError(
                        'The report time overlaps with the second report. Closest available start time is '
                        . $overlappingReport->format('Y-m-d H:i:s')
                    ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'required' => false,
            'conference_id' => null,
            'conference_start' => null,
            'conference_end' => null
        ]);
    }
}
