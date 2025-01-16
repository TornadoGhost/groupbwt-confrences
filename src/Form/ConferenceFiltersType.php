<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConferenceFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('report_number', IntegerType::class, [
            'label' => 'Number of reports',
            'attr' => [
                'min' => 0
            ]
        ]);
        $builder->add('start_date', DateTimeType::class, [
            'label' => 'Start Date',
            'widget' => 'single_text',
            'error_bubbling' => true,
        ]);
        $builder->add('end_date', DateTimeType::class, [
            'label' => 'End Date',
            'widget' => 'single_text',
            'error_bubbling' => true,
        ]);
        $builder->add('is_available', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'required' => false,
            'attr' => [
                'id' => 'filter-form'
            ],
            'csrf_protection' => false
        ]);
    }
}
