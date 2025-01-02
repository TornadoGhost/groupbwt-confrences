<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('start_time', TimeType::class, [
            'hours' => range($options['start_time']->format('H'), $options['end_time']->format('H')),
            'attr' => [
                'class' => 'mr-sm-3'
            ],
        ]);
        $builder->add('end_time', TimeType::class, [
            'hours' => range($options['start_time']->format('H'), $options['end_time']->format('H')),
            'attr' => [
                'class' => 'mr-sm-3'
            ]
        ]);
        $builder->add('duration', ChoiceType::class, [
            'choices' => array_combine(range(1, 59), range(1, 59)),
            'label' => 'Duration',
            'label_attr' => [
                'class' => 'col-form-label '
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'required' => false,
            'attr' => [
                'id' => 'filter-form',
                'class' => 'd-sm-flex border-bottom mb-3'
            ],
            'start_time' => null,
            'end_time' => null
        ]);
    }
}
