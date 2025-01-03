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
            'widget' => 'choice',
            'html5' => true,
            'attr' => [
                'class' => 'mr-sm-3'
            ]
        ]);
        $builder->add('end_time', TimeType::class, [
            'hours' => range($options['start_time']->format('H'), $options['end_time']->format('H')),
            'data' => (new \DateTime())->setTime((int)$options['end_time']->format('H'), (int)$options['end_time']->format('i')),
            'attr' => [
                'class' => 'mr-sm-3'
            ]
        ]);
        $builder->add('duration', ChoiceType::class, [
            'choices' => ['15min' => 15,'30min' => 30,'45min' => 45,'60min' => 60],
            'placeholder' => 'Select duration time',
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
